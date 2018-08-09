<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 *
 * This script provides the get_rights and has_rights functions for
 * verifying user level permissions.
 *
 * @filesource  roles.inc.php
 * @package     TestLink
 * @author      Martin Havlat, Chad Rosen, Francisco Mancardi
 * @copyright   2006-2018, TestLink community 
 * 
 */

/** localization support */ 
require_once( dirname(__FILE__). '/lang_api.php' );

// 
// This can seems weird but we have this problem:
//
// lang_get() is used to translate user rights description and needs 
// $_SESSION info.
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
  // Every array, defines a section in the define role page => HAS EFFECTS ONLY ON LAYOUT
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
  global $g_rights_issuetrackers;
  global $g_rights_codetrackers;
  global $g_rights_executions;

  // global $g_rights_reqmgrsystems;

  global $g_propRights_global;
  global $g_propRights_product;
  

  // @since 1.9.7
  $l18nCfg = array('desc_testplan_execute' => null,'desc_testplan_create_build' => null,
                   'desc_testplan_metrics' => null,'desc_testplan_planning' => null,
                   'desc_user_role_assignment' => null,'desc_mgt_view_tc' => null,
                   'desc_mgt_modify_tc'  => null,'mgt_testplan_create' => null,
                   'desc_mgt_view_key' => null,'desc_mgt_modify_key' => null,
                   'desc_keyword_assignment' => null,'desc_mgt_view_req' => null,
                   'desc_monitor_requirement' => null,'desc_mgt_modify_req' => null,
				   'desc_req_tcase_link_management' => null,'desc_mgt_modify_product' => null,
                   'desc_project_inventory_management' => null,'desc_project_inventory_view' => null,
                   'desc_cfield_view' => null,'desc_cfield_management' => null,
                   'desc_cfield_assignment' => null,
                   'desc_exec_assign_testcases' => null,
                   'desc_platforms_view' => null,'desc_platforms_management' => null,
                   'desc_issuetrackers_view' => null,'desc_issuetrackers_management' => null,
                   'desc_codetrackers_view' => null,'desc_codetrackers_management' => null,
                   'desc_mgt_modify_users' => null,'desc_role_management' => null,
                   'desc_user_role_assignment' => null,
                   'desc_mgt_view_events' => null, 'desc_events_mgt' => null,
                   'desc_mgt_unfreeze_req' => null,'desc_mgt_plugins' => null,
                   'right_exec_edit_notes' => null, 'right_exec_delete' => null,
                   'right_testplan_unlink_executed_testcases' => null, 
                   'right_testproject_delete_executed_testcases' => null,
                   'right_testproject_edit_executed_testcases' => null,
                   'right_testplan_milestone_overview' => null,
                   'right_exec_testcases_assigned_to_me' => null,
                   'right_testproject_metrics_dashboard' => null,
                   'right_testplan_add_remove_platforms' => null,
                   'right_testplan_update_linked_testcase_versions' => null,
                   'right_testplan_set_urgent_testcases' => null,
                   'right_testplan_show_testcases_newest_versions' => null,
                   'right_testcase_freeze' => null,
                   'right_exec_ro_access' => null);



  $l18n = init_labels($l18nCfg);

  $g_rights_executions = array('exec_edit_notes' => $l18n['right_exec_edit_notes'], 
                               'exec_delete' => $l18n['right_exec_delete'],
                               'exec_ro_access' => $l18n['right_exec_ro_access']);

  // order is important ?
  $g_rights_tp = 
    array("mgt_testplan_create" => $l18n['mgt_testplan_create'],
          "testplan_create_build" => $l18n['desc_testplan_create_build'],
          "testplan_planning" => $l18n['desc_testplan_planning'],
          "testplan_execute" => $l18n['desc_testplan_execute'],
          "testplan_metrics" => $l18n['desc_testplan_metrics'],
          "testplan_user_role_assignment" => $l18n['desc_user_role_assignment'],
          "exec_assign_testcases" => $l18n['desc_exec_assign_testcases'],
          "testplan_unlink_executed_testcases" => $l18n['right_testplan_unlink_executed_testcases'],
          "testplan_milestone_overview"  => $l18n['right_testplan_milestone_overview'],
          "exec_testcases_assigned_to_me" => $l18n['right_exec_testcases_assigned_to_me'],
          'testplan_add_remove_platforms' => $l18n['right_testplan_add_remove_platforms'],
          'testplan_update_linked_testcase_versions' => $l18n['right_testplan_update_linked_testcase_versions'],
          'testplan_set_urgent_testcases' => $l18n['right_testplan_set_urgent_testcases'],
          'testplan_show_testcases_newest_versions' => $l18n['right_testplan_show_testcases_newest_versions']);
            
  $g_rights_mgttc = array("mgt_view_tc" => $l18n['desc_mgt_view_tc'],
                          "mgt_modify_tc" => $l18n['desc_mgt_modify_tc'],
                          "testproject_delete_executed_testcases" => $l18n['right_testproject_delete_executed_testcases'],
                          "testproject_edit_executed_testcases" => $l18n['right_testproject_edit_executed_testcases'],
                          "testcase_freeze" => $l18n['right_testcase_freeze']);
  
  $g_rights_kw = array("mgt_view_key" => $l18n['desc_mgt_view_key'],
                       "keyword_assignment" => $l18n['desc_keyword_assignment'],
                       "mgt_modify_key" => $l18n['desc_mgt_modify_key']);
  
  $g_rights_req = array("mgt_view_req" => $l18n['desc_mgt_view_req'],
                        "monitor_requirement" => $l18n['desc_monitor_requirement'],
                        "mgt_modify_req" => $l18n['desc_mgt_modify_req'],
                        "mgt_unfreeze_req" => $l18n['desc_mgt_unfreeze_req'],
                        "req_tcase_link_management" => $l18n['desc_req_tcase_link_management']);
  
  $g_rights_product = 
    array("mgt_modify_product" => $l18n['desc_mgt_modify_product'],
          "cfield_assignment" => $l18n['desc_cfield_assignment'],
          "project_inventory_management" => $l18n['desc_project_inventory_management'],
          "project_inventory_view" => $l18n['desc_project_inventory_view'] );            
  
  $g_rights_cf = array("cfield_view" => $l18n['desc_cfield_view'],
                       "cfield_management" => $l18n['desc_cfield_management']);
  
  
  $g_rights_platforms = array("platform_view" => $l18n['desc_platforms_view'],
                              "platform_management" => $l18n['desc_platforms_management']);

  $g_rights_issuetrackers = array("issuetracker_view" => $l18n['desc_issuetrackers_view'],
                                  "issuetracker_management" => $l18n['desc_issuetrackers_management']);

  $g_rights_codetrackers = array("codetracker_view" => $l18n['desc_codetrackers_view'],
                                 "codetracker_management" => $l18n['desc_codetrackers_management']);


  // $g_rights_reqmgrsystems = array("reqmgrsystem_view" => $l18n['desc_reqmgrsystems_view'],
  //                                 "reqmgrsystem_management" => $l18n['desc_reqmgrsystems_management']);


  // Global means test project independent.
  //
  // $g_rights_users_global = array("mgt_users" => $l18n['desc_mgt_modify_users'],
  //                                "role_management" => $l18n['desc_role_management'],
  //                                "user_role_assignment" => $l18n['desc_user_role_assignment']); 
  
  $g_rights_users_global = array("mgt_users" => $l18n['desc_mgt_modify_users'],
                                 "role_management" => $l18n['desc_role_management']);

  $g_rights_users = $g_rights_users_global;
              
  $g_rights_system = array ("mgt_view_events" => $l18n['desc_mgt_view_events'],
                            "events_mgt" => $l18n['desc_events_mgt'],
                            "mgt_plugins" => $l18n['desc_mgt_plugins']);


              
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
function get_tplan_effective_role(&$db,$tplan_id,$tproject,$user_id = null,$users = null,$inheritanceMode = null)
{
  $tplan_mgr = new testplan($db);
  $tplan = $tplan_mgr->get_by_id($tplan_id);
  unset($tplan_mgr);

  $roleInhMode = !is_null($inheritanceMode) ? $inheritanceMode : 
                   config_get('testplan_role_inheritance_mode');

 /**
  * key: user_id 
  * value: map with keys:
  *        login                (from users table - useful for debug)
  *        user_role_id         (from users table - useful for debug)
  *        uplayer_role_id      (always = user_role_id)
  *        uplayer_is_inherited
  *        effective_role_id  user role for test project
  *        is_inherited
  */
  $effective_role = get_tproject_effective_role($db,$tproject,$user_id,$users);

  foreach($effective_role as $user_id => $row) {

    $doNextStep = true;

    // Step 1 - If I've role specified for Test Plan, get and skip
    if( isset($row['user']->tplanRoles[$tplan_id]) ) {
      $isInherited = 0;
      $doNextStep = false;

      $effective_role[$user_id]['effective_role_id'] = $row['user']->tplanRoles[$tplan_id]->dbID;  
      $effective_role[$user_id]['effective_role'] = $row['user']->tplanRoles[$tplan_id];
    } 

    // For Private Test Plans specific role is NEEDED for users with 
    // global role !? ADMIN
    if( $doNextStep && 
       ($row['user']->globalRoleID != TL_ROLES_ADMIN) && !$tplan['is_public']) {
      $isInherited = 0;
      $doNextStep = false;

      $effective_role[$user_id]['effective_role_id'] = TL_ROLES_NO_RIGHTS;
      $effective_role[$user_id]['effective_role'] = '<no rights>';
    }

    if( $doNextStep ) {
      $isInherited = 1;

      switch($roleInhMode) {
        case 'testproject':
          $effective_role[$user_id]['uplayer_role_id'] = $effective_role[$user_id]['effective_role_id'];
          $effective_role[$user_id]['uplayer_is_inherited'] = $effective_role[$user_id]['is_inherited'];
        break;

        case 'global':
          $effective_role[$user_id]['effective_role_id'] = $row['user']->globalRoleID;
          $effective_role[$user_id]['effective_role'] = $row['user']->globalRole;
        break;
      }
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
