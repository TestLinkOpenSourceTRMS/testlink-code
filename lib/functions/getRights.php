<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * 
 * @filesource $RCSfile: getRights.php,v $
 * @version $Revision: 1.3 $
 * @modified $Date: 2005/08/20 18:39:13 $ by $Author: schlundus $
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
 * 
 * 20050819 - am - small cosmetic changes
 */
function getRoleRights($role)
{
	$roles = null;
	
	// 20050423 - fm - Grab the users rights from the rights table
	$sqlGetRights = "SELECT rights FROM rights " .
	                "WHERE role='" . mysql_escape_string($role) . "'";

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
	// 20050819 - am - we dont need to query the db for the rights every call
	//				 - so the rights are fetched only once per script 
	static $rights = null;
	if (is_null($rights))
		$rights = getRoleRights($_SESSION['role']);
	
	//check to see if the $roleQuestion variable appears in the $roles variable
	// 20050819 - am - extended to so we can check for the presence multiple rights
	if (is_array($roleQuestion))
	{
		$r = array_intersect($roleQuestion,$rights);
		if (sizeof($r) == sizeof($roleQuestion))
			return 'yes';
		else
			return null;
	}
	else
		return (in_array($roleQuestion,$rights) ? 'yes' : null);
}
?>