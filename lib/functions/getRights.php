<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * 
 * @filesource $RCSfile: getRights.php,v $
 * @version $Revision: 1.6 $
 * @modified $Date: 2005/12/29 20:59:00 $ by $Author: schlundus $
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
 * 20050819 - scs - small cosmetic changes
 */
function getRoleRights($role)
{
	$roles = null;

	$sql = "SELECT rights FROM rights " .
	       "WHERE role='" . $GLOBALS['db']->prepare_string($role) . "'";

	$result = do_sql_query($sql);
	if ($result)
	{
		$myrow = $GLOBALS['db']->fetch_array($result);
		if ($myrow)
		{
			$roles = explode(",",$myrow['rights']);
		}	
	}
	else
	{
		tLog('Request: ' .$sql. ' causes '. $GLOBALS['db']->error_msg(), 'ERROR');
	}
	return $roles;
}

/** 
* function takes a roleQuestion from a specified link and returns whether 
* the user has rights to view it
*/
function has_rights($roleQuestion)
{
	// 20050819 - scs - we dont need to query the db for the rights every call
	//				 - so the rights are fetched only once per script 
	static $rights = null;
	if (is_null($rights))
	{
		//echo "<pre>debug"; print_r($_SESSION); echo "</pre>";
		$rights = getRoleRights($_SESSION['role']);
		//echo "<pre>debug\$rights"; print_r($rights); echo "</pre>";
	}
	
	//check to see if the $roleQuestion variable appears in the $roles variable
	// 20050819 - scs - extended to so we can check for the presence of multiple rights
	if (is_array($roleQuestion))
	{
		$r = array_intersect($roleQuestion,$rights);
		if (sizeof($r) == sizeof($roleQuestion))
		{
			return 'yes';
		}	
		else
		{
			return null;
		}	
	}
	else
	{
		return (in_array($roleQuestion,$rights) ? 'yes' : null);
	}	
}
?>