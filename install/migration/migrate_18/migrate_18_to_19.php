<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 *
 * Migrate from 1.8.x tp 1.9.0
 * tasks:
 * - create records on node_hierarchy for req_version
 *   getting new IDs.
 * - create records on node_hierarchy for tcsteps
 *   getting new IDs.
 * - Update IDs on ....
 * - Update project options
 *  
 * $Id: migrate_18_to_19.php,v 1.5 2010/02/16 21:46:32 havlat Exp $
 * Author: franciscom
 * 
 * @internal rev:
 * 	20100215 - havlatm - test project options
 *  20100119 - franciscom - migrate_req_specs() - drop title
 *	20100118 - franciscom - fixed bug on migrate_req_specs()
 */

// over this qty, the process will take a lot of time
define('CRITICAL_TC_SPECS_QTY',2000);
define('FEEDBACK_STEP',2500);
define('FULL_FEEDBACK',FALSE);
define('DBVERSION4MIG', 'DB 1.2');


function migrate_18_to_19(&$dbHandler,$tableSet)
{
    migrate_requirements($dbHandler,$tableSet);
    migrate_req_specs($dbHandler,$tableSet);
    migrate_testcases($dbHandler,$tableSet);
    migrate_project_options($dbHandler,$tableSet);
}


/**
 * migrate_requirements
 */
function migrate_requirements(&$dbHandler,$tableSet)
{
	// do requirements exist?
	$sql = "SELECT id FROM {$tableSet['requirements']}";
 	$reqSet = $dbHandler->get_recordset($sql);
    
	if( !is_null($reqSet) && count($reqSet) > 0)
	{
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
	    foreach($reqSet as $dummy => $req_info)
	    {
	    	$item_id = $tree_mgr->new_node($req_info['id'],$node_types_descr_id['requirement_version']);
	    	$sql = " UPDATE {$tableSet['req_versions']} " .
	    	       " SET id = {$item_id} WHERE id={$req_info['id']}";
            $dbHandler->exec_query($sql);
	    }

        // STEP 3 - Remove fields from requirements
        $adodbObj = $dbHandler->get_dbmgr_object();
        $colNames = $adodbObj->MetaColumnNames($tableSet['requirements']);
        $cols2drop = array("scope", "status", "type", "author_id","creation_ts",
                           "modifier_id","modification_ts","node_order","title");
        $cols2drop = array_flip($cols2drop);
        foreach($cols2drop as $colname => $dummy)
        {
        	if( !isset($colNames[strtoupper($colname)]) )
        	{
        		unset($cols2drop[$colname]);
        	}
        	else
        	{
        		$cols2drop[$colname] = " DROP $colname ";
        	}
        }
        $drop_clause = implode(",", $cols2drop);
        $sql = "ALTER TABLE {$tableSet['requirements']} {$drop_clause} ";
        $dbHandler->exec_query($sql);
	} 
}


/**
 * migrate_req_specs
 */
function migrate_req_specs(&$dbHandler,$tableSet)
{
	// get all requirements in system
	$sql = "SELECT * FROM {$tableSet['req_specs']}";
	$rs = $dbHandler->get_recordset($sql);
	
	//
	if( !is_null($rs) && count($rs) > 0)
	{
		$keyset = array_keys($rs);
		foreach($keyset as $id)
		{
			$sql = " UPDATE {$tableSet['req_specs']} " .
				" SET doc_id = '" . "RSPEC-DOCID-" . $rs[$id]['id'] . "'" .
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
			$cols2drop[$colname] = " DROP $colname ";
		}
	}
	$drop_clause = implode(",", $cols2drop);
	$sql = "ALTER TABLE {$tableSet['req_specs']} {$drop_clause} ";
	$dbHandler->exec_query($sql);
}


/**
 * Migrate project options to json format
 */
function migrate_project_options(&$dbHandler,$tableSet)
{
	// get all projects in system
	$sql = "SELECT * FROM {$tableSet['testprojects']}";
	$rs = $dbHandler->get_recordset($sql);
	
	// Set new parameter
	if( !is_null($rs) && count($rs) > 0)
	{
		$keyset = array_keys($rs);
		foreach($keyset as $id)
		{
		  	$options = new stdClass();
		  	$options->requirementsEnabled = $rs[$id]['option_reqs'];
	  		$options->testPriorityEnabled = $rs[$id]['option_priority'];
	  		$options->automationEnabled = $rs[$id]['option_automation'];
	  		$options->infrastructureEnabled = FALSE;
	  		
	  		$serOptions = serialize($options);
			
			$sql = " UPDATE {$tableSet['testprojects']} SET" .
					" options='" .  $serOptions . "', " .
					" WHERE id=" . $rs[$id]['id'];
			$dbHandler->exec_query($sql);
		}
	} 
	
	// STEP 3 - Remove fields from project
	$adodbObj = $dbHandler->get_dbmgr_object();
	$colNames = $adodbObj->MetaColumnNames($tableSet['testprojects']);
	$cols2drop = array('option_reqs','option_priority','option_automation');
	$cols2drop = array_flip($cols2drop);
	foreach($cols2drop as $colname => $dummy)
	{
		if( !isset($colNames[strtoupper($colname)]) )
		{
			unset($cols2drop[$colname]);
		}
		else
		{
			$cols2drop[$colname] = " DROP $colname ";
		}
	}
	$drop_clause = implode(",", $cols2drop);
	$sql = "ALTER TABLE {$tableSet['req_specs']} {$drop_clause} ";
	$dbHandler->exec_query($sql);
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
	echo __FUNCTION__;
	
	// do test cases exist?
	$sql = "SELECT id FROM {$tableSet['tcversions']}";
 	$itemSet = $dbHandler->get_recordset($sql);

	if( !is_null($itemSet) && count($itemSet) > 0)
	{
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
	    	$item_id = $tree_mgr->new_node($item_info['id'],$node_types_descr_id['testcase_step']);
	    	$sql = " UPDATE {$tableSet['tcsteps']} " .
	    	       " SET id = {$item_id} WHERE id={$item_info['id']}";
            $dbHandler->exec_query($sql);
	    }

        // STEP 3 - Remove fields from tcversions
        $sql = "ALTER TABLE {$tableSet['tcversions']} " .
               "DROP steps, DROP expected_results ";
        $dbHandler->exec_query($sql);
	} 
}
?>