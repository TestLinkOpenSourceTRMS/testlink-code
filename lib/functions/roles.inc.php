<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 *
 * This script provides the get_rights and has_rights functions for
 * verifying user level permissions.
 *
 * Default Role definition (1.6 version):
 *
 * 'guest' 	- testplan_metrics, mgt_view_tc, mgt_view_key
 * 'tester' - testplan_execute, testplan_metrics
 * 'senior tester' 	- testplan_execute, testplan_metrics, mgt_view_tc, mgt_modify_tc, mgt_view_key
 * 'lead' 	- testplan_execute, testplan_create_build, testplan_metrics, testplan_planning, testplan_assign_rights,
 *				mgt_view_tc, mgt_modify_tc, mgt_view_key, mgt_modify_key
 * 'admin' 	- testplan_execute, testplan_create_build, testplan_metrics, testplan_planning, testplan_assign_rights,
 *				mgt_view_tc, mgt_modify_tc, mgt_view_key, mgt_modify_key,
 *				mgt_modify_product, mgt_users
 *
 * Basic right identifiers:
 * 
 * 	mgt_view_tc, testplan_metrics, mgt_view_key - allow browse basic data
 * 	testplan_execute - edit Test Results
 * 	mgt_modify_tc - edit Test Cases
 * 	mgt_modify_key - edit Keywords
 * 	mgt_modify_req - edit Requirements
 * 	testplan_planning, testplan_create_build, testplan_assign_rights 
 * 			- Test Leader plans/prepares a testing
 * 	mgt_modify_product, mgt_users - just Admin edits Products and Users
 *
 *
 * @package 	TestLink
 * @author 		Martin Havlat, Chad Rosen
 * @copyright 	2006-2009, TestLink community 
 * @version    	CVS: $Id: roles.inc.php,v 1.61.2.3 2010/11/12 19:52:37 franciscom Exp $
 * 
 *
 * @internal rev: 
 *	20101112 - franciscom - BUGID 4006: get_tplan_effective_role() fixed bad variable usage -> error on event viewer
 *	20101111 - franciscom - BUGID 4006: test plan is_public
 *	20100930 - franciscom - BUGID 2344: Private test project
 *	20100307 - franciscom - removed wrong right due to copy/paste BUGID 3249
 *	20100220 - franciscom - added inventory rights
 *  20090425 - franciscom - BUGID 2417 - new right for test projects
 *  20081030 - franciscom - added new rights -> system
 *  20070901 - franciscom - BUGID 1016
 *  20070819 - franciscom - added get_tplan_effective_role(), get_tproject_effective_role()
 */

/** localization support */ 
require_once( dirname(__FILE__). '/lang_api.php' );

// 
// This can seems weird but we have this problem:
//
// lang_get() is used to translate user rights description and needs $_SESSION info.
// If no _SESSION info is found, then default locale is used.
// We need to be sure _SESSION info exists before using lang_get(); in any module.
//  
// Then we need to explicitily init this globals to get right localization.
// With previous implementation we always get localization on TL DEFAULT LOCALE
//
init_global_rights_maps();


/**
 * init global map with user rights and user rights description localized.
 */
function init_global_rights_maps()
{
	// Every array, defines a section in the define role page
	global $g_rights_tp;
	global $g_rights_mgttc;
	global $g_rights_kw;
	global $g_rights_req;
	global $g_rights_product;
	global $g_rights_cf;
	global $g_rights_users_global;
	global $g_rights_users;
	global $g_rights_system;
	global $g_rights_platforms;
	global $g_propRights_global;
	global $g_propRights_product;
	
	$g_rights_tp = array (	"testplan_execute" => lang_get('desc_testplan_execute'),
							"testplan_create_build" => lang_get('desc_testplan_create_build'),
							"testplan_metrics" => lang_get('desc_testplan_metrics'),
							"testplan_planning" => lang_get('desc_testplan_planning'),
							"testplan_user_role_assignment" => lang_get('desc_user_role_assignment'),
						);
						
	$g_rights_mgttc = array (	"mgt_view_tc" => lang_get('desc_mgt_view_tc'),
								"mgt_modify_tc" => lang_get('desc_mgt_modify_tc'),
								"mgt_testplan_create" => lang_get('mgt_testplan_create'),
							);
	
	$g_rights_kw = array (	
								"mgt_view_key" => lang_get('desc_mgt_view_key'),
								"mgt_modify_key" => lang_get('desc_mgt_modify_key'),
							);
	$g_rights_req = array (	
								"mgt_view_req" => lang_get('desc_mgt_view_req'),
								"mgt_modify_req" => lang_get('desc_mgt_modify_req'),
							);
	
	$g_rights_product = array("mgt_modify_product" => lang_get('desc_mgt_modify_product'),
                              "project_inventory_management" => lang_get('desc_project_inventory_management'),
                              "project_inventory_view" => lang_get('desc_project_inventory_view') );						
	
	$g_rights_cf = array("cfield_view" => lang_get('desc_cfield_view'),
						 "cfield_management" => lang_get('desc_cfield_management'));
	
	
	$g_rights_platforms = array("platform_view" => lang_get('desc_platforms_view'),
						        "platform_management" => lang_get('desc_platforms_management'));
	
	// Global means test project independent.
	$g_rights_users_global = array ( "mgt_users" => lang_get('desc_mgt_modify_users'),
								     "role_management" => lang_get('desc_role_management'),
								     "user_role_assignment" => lang_get('desc_user_role_assignment')); 
	
	$g_rights_users = $g_rights_users_global;
							
	$g_rights_system = array ("mgt_view_events" => lang_get('desc_mgt_view_events'),
	                          "events_mgt" => lang_get('desc_events_mgt'));
							
	$g_propRights_global = array_merge($g_rights_users_global,$g_rights_system,$g_rights_product);
    unset($g_propRights_global["testproject_user_role_assignment"]);
    
	$g_propRights_product = array_merge($g_propRights_global,$g_rights_mgttc,$g_rights_kw,$g_rights_req);
}


/** 
 * function takes a roleQuestion from a specified link and returns whether 
 * the user has rights to view it
 * 
 * @param resource &$db reference to database handler
 * @param string $roleQuestion a right identifier
 * @param integer $tprojectID (optional)
 * @param integer $tplanID (optional)
 * 
 * @see tlUser
 */
function has_rights(&$db,$roleQuestion,$tprojectID = null,$tplanID = null)
{
	return $_SESSION['currentUser']->hasRight($db,$roleQuestion,$tprojectID,$tplanID);
}


function propagateRights($fromRights,$propRights,&$toRights)
{
	// the mgt_users right isn't test project related so this right is inherited from
	// the global role (if set)
	foreach($propRights as $right => $desc)
	{
		if (in_array($right,$fromRights) && !in_array($right,$toRights))
		{
			$toRights[] = $right;
		}	
	}
}


/**
 * TBD
 *
 * @param string $rights 
 * @param mixed $roleQuestion 
 * @param boolean $bAND [default = 1] 
 * @return mixed 'yes' or null
 *
 * @author Andreas Morsing <schlundus@web.de>
 * @since 20.02.2006, 20:30:07
 *
 **/
function checkForRights($rights,$roleQuestion,$bAND = 1)
{
	$ret = null;
	//check to see if the $roleQuestion variable appears in the $roles variable
	if (is_array($roleQuestion))
	{
		$r = array_intersect($roleQuestion,$rights);
		if ($bAND)
		{
			//for AND all rights must be present
			if (sizeof($r) == sizeof($roleQuestion))
			{
				$ret = 'yes';
			}	
		}	
		else 
		{
			//for OR one of all must be present
			if (sizeof($r))
			{
				$ret = 'yes';
			}	
		}	
	}
	else
	{
		$ret = (in_array($roleQuestion,$rights) ? 'yes' : null);
	}
	return $ret;
}

/**
 * Get info about user(s) role at test project level,
 * with indication about the nature of role: inherited or assigned.
 * 
 * To get a user role we consider a 3 layer model:
 *          layer 1 - user           <--- uplayer
 *          layer 2 - test project   <--- in this fuction we are interested in this level.
 *          layer 3 - test plan
 * 
 * args : $tproject_id
 *        [$user_id]
 * 
 * @return array map with effetive_role in context ($tproject_id)
 *          key: user_id 
 *          value: map with keys:
 *                 login                (from users table - useful for debug)
 *                 user_role_id         (from users table - useful for debug)
 *                 uplayer_role_id      (always = user_role_id)
 *                 uplayer_is_inherited
 *                 effective_role_id  user role for test project
 *                 is_inherited
 */
function get_tproject_effective_role(&$db,$tproject,$user_id = null,$users = null)
{
	$effective_role = array();
	$tproject_id = $tproject['id'];
	if (!is_null($user_id))
	{
		$users = tlUser::getByIDs($db,(array)$user_id);
	}
	else if (is_null($users))
	{
		$users = tlUser::getAll($db);
	}

	if ($users)
	{
		foreach($users as $id => $user)
		{
			// manage admin exception
			$isInherited = 1;
			$effectiveRoleID = $user->globalRoleID;
			$effectiveRole = $user->globalRole;
			if( ($user->globalRoleID != TL_ROLES_ADMIN) && !$tproject['is_public'])
			{
				$isInherited = $tproject['is_public'];
				$effectiveRoleID = TL_ROLES_NO_RIGHTS;
				$effectiveRole = '<no rights>';
			}
			
			if(isset($user->tprojectRoles[$tproject_id]))
			{
				$isInherited = 0;
				$effectiveRoleID = $user->tprojectRoles[$tproject_id]->dbID;
				$effectiveRole = $user->tprojectRoles[$tproject_id];
			}  

			$effective_role[$id] = array('login' => $user->login,
										 'user' => $user,
										 'user_role_id' => $user->globalRoleID,
										 'uplayer_role_id' => $user->globalRoleID,
										 'uplayer_is_inherited' => 0,
										 'effective_role_id' => $effectiveRoleID,
										 'effective_role' => $effectiveRole,
										 'is_inherited' => $isInherited);
		}  
	}
	return $effective_role;
}


/**
 * Get info about user(s) role at test plan level,
 * with indication about the nature of role: inherited or assigned.
 * 
 * To get a user role we consider a 3 layer model:
 *          layer 1 - user           <--- uplayer
 *          layer 2 - test project   <--- in this fuction we are interested in this level.
 *          layer 3 - test plan
  
  args : $tplan_id
         $tproject_id
         [$user_id]
  
 * @return array map with effetive_role in context ($tplan_id)
           key: user_id 
           value: map with keys:
                  login                (from users table - useful for debug)
                  user_role_id         (from users table - useful for debug)
                  uplayer_role_id      user role for test project
                  uplayer_is_inherited 1 -> uplayer role is inherited 
                                       0 -> uplayer role is written in table
                  effective_role_id    user role for test plan
                  is_inherited   
                  
  @internal revisions
  20101111 - franciscom - BUGID 4006: test plan is_public                    
 */
function get_tplan_effective_role(&$db,$tplan_id,$tproject,$user_id = null,$users = null)
{
	$tplan_mgr = new testplan($db);
	$tplan = $tplan_mgr->get_by_id($tplan_id);
	unset($tplan_mgr);
	
	$effective_role = get_tproject_effective_role($db,$tproject,$user_id,$users);
	foreach($effective_role as $user_id => $row)
	{
		$isInherited = 1;
		$effective_role[$user_id]['uplayer_role_id'] = $effective_role[$user_id]['effective_role_id'];
		$effective_role[$user_id]['uplayer_is_inherited'] = $effective_role[$user_id]['is_inherited'];
		
		// BUGID 4006 
		// Manage administrator exception
		if( ($row['user']->globalRoleID != TL_ROLES_ADMIN) && !$tplan['is_public'])
		{
				$isInherited = $tproject['is_public'];
				$effectiveRoleID = TL_ROLES_NO_RIGHTS;
				$effectiveRole = '<no rights>';
		}
		// ---------------------------------------------------------------------------
		
		if(isset($row['user']->tplanRoles[$tplan_id]))
		{
			$isInherited = 0;
			$effective_role[$user_id]['effective_role_id'] = $row['user']->tplanRoles[$tplan_id]->dbID;  
			$effective_role[$user_id]['effective_role'] = $row['user']->tplanRoles[$tplan_id];
		}

		$effective_role[$user_id]['is_inherited'] = $isInherited;
	}
	return $effective_role;
}


function getRoleErrorMessage($code)
{
	$msg = 'ok';
	switch($code)
	{
		case tlRole::E_NAMEALREADYEXISTS:
			$msg = lang_get('error_duplicate_rolename');
			break;
		
		case tlRole::E_NAMELENGTH:
			$msg = lang_get('error_role_no_rolename');
			break;
			
		case tlRole::E_EMPTYROLE:
			$msg = lang_get('error_role_no_rights');
			break;
		
		case tl::OK:
			break;
		
		case ERROR:
		case tlRole::E_DBERROR:
		default:
			$msg = lang_get('error_role_not_updated');
	}
	return $msg;
}


function deleteRole(&$db,$roleID)
{
	$userFeedback = '';
	$role = new tlRole($roleID);
	$role->readFromDb($db);
	if ($role->deleteFromDB($db) < tl::OK)
		$userFeedback = lang_get("error_role_deletion");
	else
		logAuditEvent(TLS("audit_role_deleted",$role->getDisplayName()),"DELETE",$roleID,"roles");
	
	return $userFeedback;
}
?>
