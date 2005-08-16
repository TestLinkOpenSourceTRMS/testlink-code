<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * 
 * @filesource $RCSfile: getRights.php,v $
 * @version $Revision: 1.2 $
 * @modified $Date: 2005/08/16 18:00:55 $ by $Author: franciscom $
 * @author Martin Havlat, Chad Rosen
 * 
 * This script provides the get_rights and has_rights functions for
 *           verifying user level permissions.
 *
 *
 * Default USER RIGHTS:
 *
 * 'guest' 	- tp_metrics, mgt_view_tc, mgt_view_key
 * 'tester' - tp_execute, tp_metrics
 * 'senior tester' 	- tp_execute, tp_metrics, mgt_view_tc, mgt_modify_tc, mgt_view_key
 * 'lead' 	- tp_execute, tp_create_build, tp_metrics, tp_planning, tp_assign_rights,
 *				mgt_view_tc, mgt_modify_tc, mgt_view_key, mgt_modify_key
 * 'admin' 	- tp_execute, tp_create_build, tp_metrics, tp_planning, tp_assign_rights,
 *				mgt_view_tc, mgt_modify_tc, mgt_view_key, mgt_modify_key,
 *				mgt_modify_product, mgt_users
 *
 *
 * OPTIONS: FUNCTIONALITY ALLOWED FOR USER:
 * 
 * mgt_view_tc, tp_metrics, mgt_view_key - allow browse basic data
 * tp_execute - edit Test Results
 * mgt_modify_tc - edit Test Cases
 * mgt_modify_key - edit Keywords
 * mgt_modify_req - edit Product Requirements
 * tp_planning, tp_create_build, tp_assign_rights - Test Leader plans/prepares a testing
 * mgt_modify_product, mgt_users - just Admin edits Products and Users
 *
 *
 */////////////////////////////////////////////////////////////////////////////////

/** 
 * function will grab the current users rights
 * @param string $role 
 * @return string comma separated user rights
 */
function getRoleRights($role)
{
	$roles = null;
	
	//Grab the users rights from the rights table
	// 20050423 - fm
	$sqlGetRights = "SELECT rights FROM rights " .
	                "WHERE role='" . mysql_escape_string($role) . "'";

	tLog("\$sqlGetRights =>	$sqlGetRights");

	$resultGetRights = do_mysql_query($sqlGetRights);
	if ($resultGetRights)
	{
		$myrowGetRights = mysql_result($resultGetRights, 0, 0);

		tLog("\$myrowGetRights =>	$myrowGetRights");
		
		if ($myrowGetRights)
			$roles = explode(",",$myrowGetRights);
	}
	else
	{
		tLog('Request: '.$sqlGetRights.' causes '.mysql_error(), 'ERROR');
	}

	return $roles;
}

/** 
* function takes a roleQuestion from a specified link and returns whether 
* the user has rights to view it
*/
function has_rights($roleQuestion)
{
	//check to see if the $roleQuestion variable appears in the $roles variable
	return (in_array($roleQuestion,getRoleRights($_SESSION['role'])) ? 'yes' : null);
}

?>