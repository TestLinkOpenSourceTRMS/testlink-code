<?php
/*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: migrate_18_to_19.php,v 1.1 2010/01/17 17:16:22 franciscom Exp $ 

Migrate from 1.7.2 to 1.8.0

Author: franciscom

tasks:
- create records on node_hierarchy for req_version
  getting new IDs.
  
- create records on node_hierarchy for tcsteps
  getting new IDs.

  
- Update IDs on ....

rev: 
      
*/

// over this qty, the process will take a lot of time
define('CRITICAL_TC_SPECS_QTY',2000);
define('FEEDBACK_STEP',2500);
define('FULL_FEEDBACK',FALSE);
define('DBVERSION4MIG', 'DB 1.2');

function migrate_18_to_19(&$dbHandler,$tableSet)
{
	echo __FUNCTION__;
    migrate_requirements($dbHandler,$tableSet);
    migrate_req_specs($dbHandler,$tableSet);
    migrate_testcases($dbHandler,$tableSet);
}

/**
 * migrate_requirements
 *
 */
function migrate_requirements(&$dbHandler,$tableSet)
{
	
// CREATE TABLE "requirements_TL18" (  
//   "id" BIGSERIAL NOT NULL ,
//   "srs_id" BIGINT NOT NULL DEFAULT '0' REFERENCES req_specs (id),
//   "req_doc_id" VARCHAR(32) NULL DEFAULT NULL,
//   "title" VARCHAR(100) NOT NULL DEFAULT '',
//   "scope" TEXT NULL DEFAULT NULL,
//   "status" CHAR(1) NOT NULL DEFAULT 'V',
//   "type" CHAR(1) NULL DEFAULT NULL,
//   "node_order" BIGINT NOT NULL DEFAULT 0,
//   "author_id" BIGINT NULL DEFAULT NULL,
//   "creation_ts" TIMESTAMP NOT NULL DEFAULT now(),
//   "modifier_id" BIGINT NULL DEFAULT NULL,
//   "modification_ts" TIMESTAMP NULL,
//   PRIMARY KEY ("id")
// ); 
	
	
	
// 	CREATE TABLE /*prefix*/requirements (  
//   "id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/nodes_hierarchy (id),
//   "srs_id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/req_specs (id),
//   "req_doc_id" VARCHAR(64) NOT NULL,
//   PRIMARY KEY ("id")
// ); 

// CREATE TABLE /*prefix*/req_versions(  
//   "id" BIGINT NOT NULL DEFAULT '0' REFERENCES  /*prefix*/nodes_hierarchy (id),
//   "version" INTEGER NOT NULL DEFAULT '1',
//   "scope" TEXT NULL DEFAULT NULL,
//   "status" CHAR(1) NOT NULL DEFAULT 'V',
//   "type" CHAR(1) NULL DEFAULT NULL,
//   "expected_coverage" INTEGER NOT NULL DEFAULT 1,
//   "author_id" BIGINT NULL DEFAULT NULL,
//   "creation_ts" TIMESTAMP NOT NULL DEFAULT now(),
//   "modifier_id" BIGINT NULL DEFAULT NULL,
//   "modification_ts" TIMESTAMP NULL,
//   PRIMARY KEY ("id","version")
// ); 

	echo __FUNCTION__;
	
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
        $sql = "ALTER TABLE {$tableSet['requirements']} " .
               "DROP scope, DROP status, DROP type, DROP author_id,DROP creation_ts, " .
               "DROP modifier_id,DROP modification_ts, DROP node_order,DROP title";
        $dbHandler->exec_query($sql);
	} 
}


/**
 * migrate_requirements_1
 *
 */
function migrate_requirements_1(&$dbHandler,$tableSet)
{
	echo __FUNCTION__;
	
	// get all requirements in system
	$sql = "SELECT * FROM {$tableSet['requirements']}";
	
	$rs = $dbHandler->get_recordset($sql);
	
	//
	if( !is_null($rs) && count($rs) > 0)
	{
		$req_mgr = new requirement_mgr($dbHandler);
	
	    $keyset = array_keys($rs);
	    foreach($keyset as $req_id)
	    {
			// function create_version($id,$version,$scope, $user_id, $status = TL_REQ_STATUS_VALID, 
	        //             $type = TL_REQ_TYPE_INFO, $expected_coverage=1)
            // 
            $req = $rs[$req_id];
			$op = $req_mgr->create_version($req['id'],1,$req['scope'], $req['author_id'],
			                               $req['status'],$req['type'],1);	    
			                         
			if( $op['status_ok'] )
			{
				$set = array();
			    $set[] = " creation_ts = '" . $req['creation_ts'] . "' ";                         

				if( is_int($req['modifier_id']) )
				{
					$set[] = " modifier_id = {$req['modifier_id']} ";
					$set[] = " modification_ts = '" . $req['modification_ts'] ."' ";
				}
			}                         
			$sql = " UPDATE {$tableSet['req_versions']} " .
			       " SET " . implode (",",$set);                         
	        $dbHandler->exec_query($sql);
	    }
	} 
	
	// remove fields
	
}

/**
 * migrate_req_specs
 *
 */
function migrate_req_specs(&$dbHandler,$tableSet)
{
	echo __FUNCTION__;
	
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
			       " SET doc_id = '" . "RSPEC-DOCID-" . $rs[$id]['id'] . "'";
	        $dbHandler->exec_query($sql);
	    }
	} 
}

/**
 * migrate_testcases
 *
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