<?php
/** TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * 
 * @filesource $RCSfile: product.inc.php,v $
 * @version $Revision: 1.19 $
 * @modified $Date: 2007/08/20 06:41:29 $
 * @author Martin Havlat
 *
 * Functions for Product management (create,update,delete)
 * Functions for get data see product.core.inc.php
 *
 * @author: francisco mancardi - 20060101 - product notes management
 *
 */
require_once('product.core.inc.php');

/**
 * delete a product including all dependent data
 * 
 * @param integer $id
 * @param pointer Problem message
 * @return boolean 1=ok || 0=ko
 */
// MHT 20050630 added to delete all nested data
/** @todo the function are not able to delete test plan data from another product (i.e. test case suite) */
function DEPRECATED_deleteProduct(&$db,$id, &$error)
{
	$error = ''; //clear error string
	
	$arrExecSql = array();
	$tp = new testproject($db);
	$kwMap = $tp->get_keywords_map($id);
	if ($kwMap)
	{
		$kwIDs = implode(",",array_keys($kwMap));
		
		$arrExecSql[] = array(
							"DELETE FROM testcase_keywords  WHERE keyword_id IN ({$kwIDs})",
							 'info_tc_keywords_delete_fails',
							 );
		$arrExecSql[] = array(
							"DELETE FROM object_keywords  WHERE keyword_id IN ({$kwIDs})",
							 'info_object_keywords_delete_fails',
							 );					 
		$arrExecSql[] = array (
							 "DELETE FROM keywords WHERE testproject_id=" .$id,
							 'info_keywords_delete_fails',
							 );
	}	
	$sql = "SELECT id FROM req_specs WHERE testproject_id=" . $id;
	$srsIDs = $db->fetchColumnsIntoArray($sql,"id");
	if ($srsIDs)
	{
		$srsIDs = implode(",",$srsIDs);
		$sql = "SELECT id FROM requirements WHERE srs_id IN ({$srsIDs})";
		$reqIDs = $db->fetchColumnsIntoArray($sql,"id");
		if ($reqIDs)
		{
			$reqIDs = implode(",",$reqIDs);
			$arrExecSql[] = array (
							 "DELETE FROM req_coverage WHERE req_id IN ({$reqIDs})",
							 'info_req_coverage_delete_fails',
							 );
			$arrExecSql[] = array (
							 "DELETE FROM requirements WHERE id IN ({$reqIDs})",
							 'info_requirements_delete_fails',
							 );
		}
		$arrExecSql[] = array (
						 "DELETE FROM req_specs WHERE id IN ({$srsIDs})",
						 'info_req_specs_delete_fails',
						 );
		
		
	}
	
	$arrExecSql[] = array(
			"UPDATE users SET default_testproject_id = NULL WHERE default_testproject_id = {$id}",
			 'info_resetting_default_project_fails',
	);
	
	$arrExecSql[] = array(
			"DELETE FROM user_testproject_roles WHERE testproject_id = {$id}",
			 'info_deleting_project_roles_fails',
	);
	$tpIDs = $tp->get_all_testplans($id);
	if ($tpIDs)
	{
		$tpIDs = implode(",",array_keys($tpIDs));
		$arrExecSql[] = array(
			"DELETE FROM user_testplan_roles WHERE testplan_id IN  ({$tpIDs})",
			 'info_deleting_testplan_roles_fails',
		);
		$arrExecSql[] = array(
			"DELETE FROM testplan_tcversions WHERE testplan_id IN ({$tpIDs})",
			 'info_deleting_testplan_tcversions_fails',
		);

		$arrExecSql[] = array(
			"DELETE FROM risk_assignments WHERE testplan_id IN ({$tpIDs})",
			 'info_deleting_testplan_risk_assignments_fails',
		);
		
		$arrExecSql[] = array(
			"DELETE FROM priorities WHERE testplan_id IN ({$tpIDs})",
			 'info_deleting_testplan_risk_assignments_fails',
		);
		
		$arrExecSql[] = array(
			"DELETE FROM milestones WHERE testplan_id IN ({$tpIDs})",
			 'info_deleting_testplan_milestones_fails',
		);
		
		$sql = "SELECT id FROM executions WHERE testplan_id IN ({$tpIDs})";
		$execIDs = $db->fetchColumnsIntoArray($sql,"id");
		if ($execIDs)
		{
			$execIDs = implode(",",$execIDs);
		
			$arrExecSql[] = array(
			"DELETE FROM execution_bugs WHERE execution_id IN ({$execIDs})",
			 'info_deleting_execution_bugs_fails',
				);
		}
			 
		$arrExecSql[] = array(
			"DELETE FROM builds WHERE testplan_id IN ({$tpIDs})",
			 'info_deleting_builds_fails',
		);

		$arrExecSql[] = array(
			"DELETE FROM executions WHERE testplan_id IN ({$tpIDs})",
			 'info_deleting_execution_fails',
		); 
	}
		
	$test_spec = $tp->tree_manager->get_subtree($id);
	if(count($test_spec))
	{
		$ids = array("nodes_hierarchy" => array());
		foreach($test_spec as $elem)
		{
			$eID = $elem['id'];
			$table = $elem['node_table'];
			$ids[$table][] = $eID;
			$ids["nodes_hierarchy"][] = $eID;
		}
		
		foreach($ids as $tableName => $fkIDs)
		{
			$fkIDs = implode(",",$fkIDs);
			
			if ($tableName != "testcases")
			{
				$arrExecSql[] = array(
					"DELETE FROM {$tableName} WHERE id IN ({$fkIDs})",
					 "info_deleting_{$tableName}_fails",
					);
			}
		}
	}			
	//MISSING DEPENDENT DATA:
	/*
	* ATTACHMENTS
	* CUSTOM FIELDS
	*/
		
	// delete all nested data over array $arrExecSql
	foreach ($arrExecSql as $oneSQL)
	{
		if (empty($error))
		{
			$sql = $oneSQL[0];
			$result = $db->exec_query($sql);	
			if (!$result)
				$error .= lang_get($oneSQL[1]);
		}
	}	
	// delete product itself
	if (empty($error))
	{
		$sql = "DELETE FROM testprojects WHERE id = {$id}";
		$result = $db->exec_query($sql);

		if ($result)
		{
			$tproject_id_on_session = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : $id;
			if ($id == $tproject_id_on_session)
				setSessionTestProject(null);
		}
		else
			$error .= lang_get('info_product_delete_fails');
	}

	return empty($error) ? 1 : 0;
}
?>
