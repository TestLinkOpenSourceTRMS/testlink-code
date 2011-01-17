<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 *
 * Migrate from 1.8.x tp 1.9.0
 *
 * tasks:
 * - create records on node_hierarchy for req_version
 *   getting new IDs.
 * - create records on node_hierarchy for tcsteps
 *   getting new IDs.
 * - Update IDs on ....
 * - Update project options
 * - ....
 *  
 * Included on installNewDB.php
 *
 * $Id: migrate_18_to_19.php,v 1.10.2.12 2011/01/17 11:06:24 mx-julian Exp $
 * Author: franciscom
 * 
 * @internal rev:
 *  20110117 - Julian - BUGID 4174 - do not show each migrated test case on migration log
 *	20101219 - franciscom - BUGID 4040 - Check for MSSQL to skip some operations THAT HAVE
 *										 to be done MANUALLY
 *
 *	20101218 - franciscom - BUGID 4040 - changed drop column strategy
 *	20101212 - franciscom - BUGID 4040
 *	20101119 - franciscom - fixed DROP COLUMN syntax for SQL SERVER
 *	20100911 - franciscom - migrate_cfield_links()
 *  20100707 - asimon - req spec type set to "1" like done for requirements
 *  20100705 - asimon - added migrate_user_assignments()
 *  20100701 - Julian - requirement type set to "1"
 * 	20100215 - havlatm - test project options
 *  20100119 - franciscom - migrate_req_specs() - drop title
 *	20100118 - franciscom - fixed bug on migrate_req_specs()
 */

// over this qty, the process will take a lot of time
define('CRITICAL_TC_SPECS_QTY',2000);
define('FEEDBACK_STEP',2500);
define('FULL_FEEDBACK',FALSE);
define('DBVERSION4MIG', 'DB 1.2');


/**
 * 
 *
 */
function migrate_18_to_19(&$dbHandler,$tableSet)
{
	// Need To Add Some Feedback
	echo '<b><br>-------------------------------------<br>'; 
	echo 'Data Migration Process STARTED<br>'; 
	echo '-------------------------------------<br></b>'; 

    migrate_requirements($dbHandler,$tableSet);
    migrate_req_specs($dbHandler,$tableSet);
    migrate_testcases($dbHandler,$tableSet);
    migrate_project_options($dbHandler,$tableSet);
    migrate_user_assignments($dbHandler, $tableSet);
    migrate_cfield_links($dbHandler, $tableSet);


	if( $dbHandler->dbType == 'mssql')
	{
		echo "<b><br>**********************************************************************************<br>";
		echo "IMPORTANT NOTICE FOR MSSQL USERS<br>";
		echo "**********************************************************************************<br>";
		echo "Some updates to DB SCHEMA HAS TO BE DONE manually due to <br>";
		echo "MSSQL Restrictions<br>";
		echo "ALTER TABLE /*prefix*/requirements ALTER req_doc_id VARCHAR(64)<br>";
		echo "ALTER TABLE /*prefix*/custom_fields ALTER COLUMN possible_values varchar(4000)<br>";
		echo "ALTER TABLE /*prefix*/custom_fields ALTER COLUMN default_value varchar(4000)<br>";
		echo "**********************************************************************************<br></b>";
	}
	echo '<br>-------------------------------------<br>'; 
	echo 'Data Migration Process Finished<br>'; 
	echo '-------------------------------------<br><br><br>'; 
}


/**
 * migrate_requirements
 */
function migrate_requirements(&$dbHandler,$tableSet)
{
	// do requirements exist?
	$sql = "SELECT id FROM {$tableSet['requirements']}";
 	$reqSet = $dbHandler->get_recordset($sql);
    echo 'Step - Requirements Migration - STARTED <br> ';
	if( !is_null($reqSet) && count($reqSet) > 0)
	{
		echo 'Working - Requirements Migration <br> ';
		$tree_mgr = new tree($dbHandler);
		$node_types_descr_id= $tree_mgr->get_available_node_types();
    	$node_types_id_descr=array_flip($node_types_descr_id);

        // STEP 1 - Populate in bulk mode req_versions table.
        //
        // ALL FIELDS
		// 1.8 id,srs_id,req_doc_id,title,scope,status,type,node_order,author_id,creation_ts,modifier_id,modification_ts"
		// 1.9 id,version,scope,status,type,expected_coverage,author_id,creation_ts,modifier_id,modification_ts" 

        // NEEDED FIELDS
		// 1.8 id,scope,status,type,author_id,creation_ts,modifier_id,modification_ts"
		// 1.9 id,scope,status,type,author_id,creation_ts,modifier_id,modification_ts,version,expected_coverage" 
		$sql = " INSERT INTO {$tableSet['req_versions']} " .
		       " (id,scope,status,type,author_id,creation_ts,modifier_id,modification_ts, " .
		       "  version,expected_coverage)  " .
		       " SELECT id,scope,status,type,author_id,creation_ts,modifier_id,modification_ts, " .
		       "           1 AS version, 1 AS expected_coverage " . 
		       " FROM {$tableSet['requirements']}";
        $dbHandler->exec_query($sql);
        
        // STEP 2 - Create nodes for req_versions on nodes_hierarchy table
        // Set requirement type to '1' as it was set to 'V' on 1.8 but has never been used
	    foreach($reqSet as $dummy => $req_info)
	    {
	    	$item_id = $tree_mgr->new_node($req_info['id'],$node_types_descr_id['requirement_version']);
	    	$sql = " UPDATE {$tableSet['req_versions']} " .
	    	       " SET id = {$item_id}, type = 1 WHERE id={$req_info['id']}";
            $dbHandler->exec_query($sql);
	    }

        // STEP 3 - Remove fields from requirements
        $adodbObj = $dbHandler->get_dbmgr_object();
        $colNames = $adodbObj->MetaColumnNames($tableSet['requirements']);

		$warning = "<b>{$tableSet['requirements']} - For MSSQL You NEED TO DROP " .  
		           "THESE COLUMNS manually: status,node_order,creation_ts</b>";
		if($dbHandler->dbType == 'mssql')
		{
        	$cols2drop = array("scope", "type", "author_id","modifier_id","modification_ts","title");
		}
		else
		{
			$warning = '';
        	$cols2drop = array("scope", "status", "type", "author_id","creation_ts",
            	               "modifier_id","modification_ts","node_order","title");
		}


        $cols2drop = array_flip($cols2drop);
        foreach($cols2drop as $colname => $dummy)
        {
        	if( !isset($colNames[strtoupper($colname)]) )
        	{
        		unset($cols2drop[$colname]);
        	}
        	else
        	{
        		$cols2drop[$colname] = " DROP COLUMN $colname ";
        	}
        }
        // $drop_clause = implode(",", $cols2drop);
		foreach($cols2drop as $stm)
		{
			$sql = "ALTER TABLE {$tableSet['requirements']} {$stm} ";
        	$dbHandler->exec_query($sql);
		}
	} 
	if( !is_null($warning) )
	{
		echo "$warning<br><br>";
	}
    echo 'Step - Requirements Migration - Finished !!! <br><br> ';

}


/**
 * migrate_req_specs
 */
function migrate_req_specs(&$dbHandler,$tableSet)
{
	echo 'Step - Requirements Specification Migration - STARTED<br> ';
	// get all requirements in system
	$sql = "SELECT * FROM {$tableSet['req_specs']}";
	$rs = $dbHandler->get_recordset($sql);
	
	// generate req spec doc ID
	// Set req spec type to '1' as it was set to n/N on 1.8 but has never been used
	if( !is_null($rs) && count($rs) > 0)
	{
		echo 'Working - Requirements Specification Migration<br> ';
		$keyset = array_keys($rs);
		foreach($keyset as $id)
		{
			$sql = " UPDATE {$tableSet['req_specs']} " .
				" SET doc_id = '" . "RSPEC-DOCID-" . $rs[$id]['id'] . "', type = 1 " .
				" WHERE id={$rs[$id]['id']} ";
			$dbHandler->exec_query($sql);
		}
	} 
	// STEP 3 - Remove fields from requirements
	$adodbObj = $dbHandler->get_dbmgr_object();
	$colNames = $adodbObj->MetaColumnNames($tableSet['req_specs']);
	$cols2drop = array("title");
	$cols2drop = array_flip($cols2drop);
	foreach($cols2drop as $colname => $dummy)
	{
		if( !isset($colNames[strtoupper($colname)]) )
		{
			unset($cols2drop[$colname]);
		}
		else
		{
			$cols2drop[$colname] = " DROP COLUMN $colname ";
		}
	}
	foreach($cols2drop as $stm)
	{
		$sql = "ALTER TABLE {$tableSet['req_specs']} {$stm} ";
    	$dbHandler->exec_query($sql);
	}
    echo 'Step - Requirements Migration - Finished !!! <br><br> ';
}


/**
 * Migrate project options to json format
 */
function migrate_project_options(&$dbHandler,$tableSet)
{
	echo 'Step - Test Project Options Migration -  STARTED<br> ';
	// get all projects in system
	$sql = "SELECT * FROM {$tableSet['testprojects']}";
	$rs = $dbHandler->get_recordset($sql);
	
	// Set new parameter
	if( !is_null($rs) && count($rs) > 0)
	{
		echo 'Working - Test Project Options Migration <br> ';
		$keyset = array_keys($rs);
		foreach($keyset as $id)
		{
		  	$options = new stdClass();
		  	$options->requirementsEnabled = $rs[$id]['option_reqs'];
	  		$options->testPriorityEnabled = $rs[$id]['option_priority'];
	  		$options->automationEnabled = $rs[$id]['option_automation'];
	  		$options->inventoryEnabled = FALSE;
	  		
	  		$serOptions = serialize($options);
			
			$sql = " UPDATE {$tableSet['testprojects']} SET" .
					" options='" .  $serOptions . "'" .
					" WHERE id=" . $rs[$id]['id'];
			$dbHandler->exec_query($sql);
		}
	} 
	
	// STEP 3 - Remove fields from project
	// Critic - we have CONSTRAINTS => we will not be able to delete columns
	//          if CONSTRAINTS exists.
	//
	// To avoid complex code we will inform user that 
	// he/she had to do this manually
	// $adodbObj = $dbHandler->get_dbmgr_object();
	// $colNames = $adodbObj->MetaColumnNames($tableSet['testprojects']);
	// $cols2drop = array('option_reqs','option_priority','option_automation');
	// $cols2drop = array_flip($cols2drop);
	// foreach($cols2drop as $colname => $dummy)
	// {
	// 	if( !isset($colNames[strtoupper($colname)]) )
	// 	{
	// 		unset($cols2drop[$colname]);
	// 	}
	// 	else
	// 	{
	// 		$cols2drop[$colname] = " DROP COLUMN $colname ";
	// 	}
	// }
	echo "<b><br><br>";
	echo "**********************************************************************************<br>";
	echo "******* ATTENTION!!!! *** <br> {$tableSet['testprojects']} <br> ";
	echo "Please DROP Manually COLUMNS with it's constraints (I'm sorry )<br> ";
	echo "'option_reqs','option_priority','option_automation'<br> ";
	echo "**********************************************************************************<br></b>";
    echo 'Step - Test Project Options Migration - Finished !!! <br><br> ';
}


/**
 * migrate_testcases
 */
function migrate_testcases(&$dbHandler,$tableSet)
{

// TL 1.8	
// --
// -- Table structure for table "tcversions"
// --
// CREATE TABLE "tcversions" (  
//   "id" BIGINT NOT NULL DEFAULT '0' REFERENCES nodes_hierarchy (id),
//   "tc_external_id" INT NULL,
//   "version" INTEGER NOT NULL DEFAULT '1',
//   "summary" TEXT NULL DEFAULT NULL,
//   "steps" TEXT NULL DEFAULT NULL,
//   "expected_results" TEXT NULL DEFAULT NULL,
//   "importance" INT2 NOT NULL DEFAULT '2',
//   "author_id" BIGINT NULL DEFAULT NULL REFERENCES users (id),
//   "creation_ts" TIMESTAMP NOT NULL DEFAULT now(),
//   "updater_id" BIGINT NULL DEFAULT NULL REFERENCES users (id),
//   "modification_ts" TIMESTAMP NULL,
//   "active" INT2 NOT NULL DEFAULT '1',
//   "is_open" INT2 NOT NULL DEFAULT '1',
//   "execution_type" INT2 NOT NULL DEFAULT '1',
//   PRIMARY KEY ("id")
// ); 
	
	
// TL 1.9
// --
// -- Table structure for table "tcversions"
// --
// CREATE TABLE /*prefix*/tcversions(  
//   "id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/nodes_hierarchy (id),
//   "tc_external_id" INT NULL,
//   "version" INTEGER NOT NULL DEFAULT '1',
//   "layout" INTEGER NOT NULL DEFAULT '1',
//   "summary" TEXT NULL DEFAULT NULL,
//   "preconditions" TEXT NULL DEFAULT NULL,
//   "importance" INT2 NOT NULL DEFAULT '2',
//   "author_id" BIGINT NULL DEFAULT NULL REFERENCES  /*prefix*/users (id),
//   "creation_ts" TIMESTAMP NOT NULL DEFAULT now(),
//   "updater_id" BIGINT NULL DEFAULT NULL REFERENCES  /*prefix*/users (id),
//   "modification_ts" TIMESTAMP NULL,
//   "active" INT2 NOT NULL DEFAULT '1',
//   "is_open" INT2 NOT NULL DEFAULT '1',
//   "execution_type" INT2 NOT NULL DEFAULT '1',
//   PRIMARY KEY ("id")
// ); 
// 
// 
// --
// -- Table structure for table "tcsteps"
// --
// CREATE TABLE /*prefix*/tcsteps (  
//   "id" BIGINT NOT NULL DEFAULT '0' REFERENCES /*prefix*/nodes_hierarchy (id),
//   "step_number" INT NOT NULL DEFAULT '1',
//   "actions" TEXT NULL DEFAULT NULL,
//   "expected_results" TEXT NULL DEFAULT NULL,
//   "active" INT2 NOT NULL DEFAULT '1',
//   "execution_type" INT2 NOT NULL DEFAULT '1',
//   PRIMARY KEY ("id")
// ); 

	// do test cases exist?
	$sql = " SELECT TCV.id,NHTC.name AS name FROM {$tableSet['tcversions']} TCV " .
		   " JOIN {$tableSet['nodes_hierarchy']} NHV ON NHV.id=TCV.id " .
		   " JOIN {$tableSet['nodes_hierarchy']} NHTC ON NHTC.id=NHV.parent_id ";
	
	echo 'Step - Test Case Migration - STARTED <br> ';
	$itemSet = $dbHandler->get_recordset($sql);

	if( !is_null($itemSet) && count($itemSet) > 0)
	{
		echo 'Working - Test Case Migration <br> ';
		$tree_mgr = new tree($dbHandler);
		$node_types_descr_id= $tree_mgr->get_available_node_types();
    	$node_types_id_descr=array_flip($node_types_descr_id);

        // STEP 1 - Populate in bulk mode tcsteps table.
        //
        // ALL FIELDS - tcversions 1.8
        // 1.8 id,tc_external_id,version,summary,steps,expected_results,importance,author_id,creation_ts,updater_id,modification_ts,active,is_open,execution_type,
        // 1.9 id,tc_external_id,version,summary,importance,author_id,creation_ts,updater_id,modification_ts,active,is_open,execution_type,
        //     
        // 1.9 tcsteps
        // id,step_number,actions,expected_results,active,execution_type
        // 
        // NEEDED FIELDS
		$sql = " INSERT INTO {$tableSet['tcsteps']} " .
		       " (id,actions,expected_results,active,execution_type) " .
		       " SELECT id,steps,expected_results,active,execution_type " .
		       " FROM {$tableSet['tcversions']}";
        $dbHandler->exec_query($sql);
        
        // STEP 2 - Create nodes for tcsteps on nodes_hierarchy table
	    foreach($itemSet as $dummy => $item_info)
	    {
        	
	    	// BUGID 4174 - do not show each migrated test case on migration log
	    	//echo "Test Case:" . $item_info['name'] . " Version DBID: {$item_info['id']} <br>";
	    	
	    	$item_id = $tree_mgr->new_node($item_info['id'],$node_types_descr_id['testcase_step']);
	    	if( $item_id > 0 )
	    	{
	    		$sql = " UPDATE {$tableSet['tcsteps']} " .
	    	       	   " SET id = {$item_id} WHERE id={$item_info['id']}";
            	$dbHandler->exec_query($sql);
            }
            else
            {
            	echo "<b> **** <br> CRITIC ***** <br>";
            	echo "MIGRATION FAILURE - Unable to create Test Case Step NODE on nodes hierarchy <br></b>";
            }
	    }

        // STEP 3 - Remove fields from tcversions
        $sql = "ALTER TABLE {$tableSet['tcversions']} DROP COLUMN steps";
        $dbHandler->exec_query($sql);

        $sql = "ALTER TABLE {$tableSet['tcversions']} DROP COLUMN expected_results ";
        $dbHandler->exec_query($sql);

	} 
	echo 'Step - Test Case Migration - Finished !! <br><br> ';
}


/**
 * Migrate the existing user assignments for all test plans and test projects.
 * All test case execution assignments will be stored per build in TL 1.9.
 * So all tester assignments for the test cases in each test plan will be updated 
 * with the ID of the newest available build for that test plan.
 * 
 * @author Andreas Simon
 * @param database $dbHandler
 * @param array $tableSet
 */
function migrate_user_assignments(&$dbHandler, $tableSet) {
	//$starttime = microtime(true);

	echo 'Step - User Execution Assignment Migration - STARTED <br> ';
	echo 'Working - User Execution Assignment Migration <br> ';

	$testplan_mgr = new testplan($dbHandler);
	
	// get assignment type for execution
	$assignment_mgr = new assignment_mgr($dbHandler);
	$assignment_types = $assignment_mgr->get_available_types();
	$execution = $assignment_types['testcase_execution']['id'];
	
	// get table names
	$ua = $tableSet['user_assignments'];
	$tp_tcv = $tableSet['testplan_tcversions'];
	
	// get list of test plan IDs from the assigned test cases
	$sql = " SELECT distinct T.testplan_id " .
	       " FROM {$ua} UA, {$tp_tcv} T " .
	       " WHERE UA.feature_id = T.id " .
	       " AND (UA.type={$execution} OR UA.type IS NULL) ";
	$testplans = $dbHandler->fetchColumnsIntoArray($sql, 'testplan_id');
	
	// Get the newest (max) build ID for each of these test plan IDs and store them.
	// In $testplan_builds, we then have an array consisting of testplan_id => max_build_id
	// If no build for a test plan is found, its assignments will not be changed (build_id=0).
	$testplan_builds = array();
	if( !is_null($testplans) )
	{
		foreach ($testplans as $key => $tp_id) {
			// first we try to get an active build
			$max_build_id = $testplan_mgr->get_max_build_id($tp_id, testplan::GET_ACTIVE_BUILD);
			// if there is no active build, we get the max id no matter if it is active or not
			if ($max_build_id == 0) {
				$max_build_id = $testplan_mgr->get_max_build_id($tp_id);
			}
			
			if ($max_build_id > 0) {
				$testplan_builds[$tp_id] = $max_build_id;
			}
		}	
		
		// now update all assignments for these test plans
		foreach ($testplan_builds as $testplan_id => $build_id) {
			$subquery = " SELECT id as feature_id FROM {$tp_tcv} " .
			            " WHERE testplan_id = {$testplan_id} ";
    	
		// This update with ALIAS is not LIKED by Postgres
		//
		// OK
		// UPDATE user_assignments UA  
		// SET build_id = 2  WHERE UA.feature_id IN 
		// ( SELECT id AS feature_id FROM testplan_tcversions  WHERE testplan_id = 70)
		//
		// if used UA.build_id -> problems on Postgre 8.4
		// Message:
		// ERROR:  column "ua" of relation "user_assignments" does not exist
		// LINE 2: SET UA.build_id = 2  WHERE UA.feature_id IN              
		
		// $sql = " UPDATE {$ua} UA " .
		//        " SET UA.build_id = {$build_id} " .
		//        " WHERE UA.feature_id IN($subquery) " .
		//        " AND (UA.type={$execution} OR UA.type IS NULL) ";
		// 
		// 20101212 - franciscom		
		$sql = " UPDATE {$ua} " .
		       " SET build_id = {$build_id} " .
		       " WHERE feature_id IN($subquery) " .
		       " AND (type={$execution} OR type IS NULL) ";
			
			$dbHandler->exec_query($sql);
		}
	}
	
	// delete objects
	unset($testplan_mgr);
	
	// check how long the function is running on huge databases...
	//$endtime = microtime(true) - $starttime;
	//echo "<br/>migrate_user_assignments() needed $endtime seconds to finish<br/>";
	echo 'Step - User Execution Assignment Migration - Finished <br><br> ';
}

/**
 * Migrate custom field values AT DESIGN TIME, for test cases and requirements,
 * to point to item version instead of item:
 * test case version instead of test case
 * requirement version instead of requirement
 * 
 * @author Francisco Mancardi 
 * @param database $dbHandler
 * @param array $tableSet
 */
function migrate_cfield_links(&$dbHandler, $tableSet) 
{
	$treeMgr = new tree($dbHandler);
	$nodeTypes = $treeMgr->get_available_node_types();
	unset($treeMgr);
	
	$sql = " SELECT CFDV.*, NHITEM.node_type_id, NHVERSION.id AS version_node_id" .
	       " FROM {$tableSet['cfield_design_values']} CFDV " .
	       " JOIN {$tableSet['nodes_hierarchy']} NHITEM ON NHITEM.id = CFDV.node_id " .
	       " JOIN {$tableSet['nodes_hierarchy']} NHVERSION ON NHVERSION.parent_id = NHITEM.id " .
	       " WHERE NHITEM.node_type_id IN ({$nodeTypes['testcase']},{$nodeTypes['requirement']}) ";
	
	$workingSet = $dbHandler->get_recordset($sql);
	
	echo 'Step - Custom Fields (DESIGN TIME) Migration - STARTED <br> ';
	if( !is_null($workingSet) )
	{
		echo "Working - Custom Fields Migration - Records to process:" .  count($workingSet) . "<br>";
		foreach($workingSet as $target)
		{
			// $values[] = "( {$target['field_id']}, {$target['version_node_id']}, '{$target['value']}' )";
			$victims[$target['node_id']] = $target['node_type_id'];
			
			// Ay!, I've forgot to escape target value
			$sql = " INSERT INTO {$tableSet['cfield_design_values']} (field_id,node_id,value) VALUES " . 
			       "( {$target['field_id']}, {$target['version_node_id']}," .
			       "'" . $dbHandler->prepare_string($target['value']) . "' )";
			$dbHandler->exec_query($sql);
		}
	
		// depending amount of data on db this can create an statement with too many characters
		// and can fail.
		// change to slower but safer strategy: one by one insert
		// $sql = " INSERT INTO {$tableSet['cfield_design_values']} (field_id,node_id,value) VALUES ";
		// $vSet = implode(',',$values);
		// $sql .= $vSet;
		// $dbHandler->exec_query($sql);
		
		
		
		foreach($victims as $node_id => $node_type_id)
		{
			$sql = " DELETE FROM {$tableSet['cfield_design_values']} WHERE node_id = $node_id "; 
		    $dbHandler->exec_query($sql);
		}
	}
	echo 'Step - Custom Fields (DESIGN TIME) Migration - Finished !! <br><br> ';

}
?>