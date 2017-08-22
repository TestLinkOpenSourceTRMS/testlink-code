<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Functions for usermanagement
 * 
 * @filesource  users.inc.php
 * @package     TestLink
 * @author      Martin Havlat
 * @copyright   2006-2015, TestLink community 
 * @link        http://www.testlink.org
 *
 * @internal revisions
 * @since 1.9.16
 */
require_once("common.php");


/**
 * set session data after modification or authorization
 *
 * @param resource &$db reference to DB identifier
 * @param string $user
 * @param integer $id
 * @param integer $roleID 
 * @param string $email 
 * @param string $locale [default = null]
 * @param boolean $active [default = null] documentation
 * 
 * @return integer status code
 * 
 * @TODO havlatm: move to tlSession class
 * @TODO fix return functionality
 **/
function setUserSession(&$db,$user, $id, $roleID, $email, $locale = null, $active = null)
{
  tLog('setUserSession: $user=' . $user . ' $id='.$id.' $roleID='.$roleID.' $email='.$email.' $locale='.$locale);

  $_SESSION['userID'] = $id;
  $_SESSION['testprojectID'] = null;
  $_SESSION['s_lastAttachmentList'] = null;

  if (!is_null($locale))
  {
    $_SESSION['locale'] = $locale;
    setDateTimeFormats($locale);
  }

  $tproject_mgr = new testproject($db);

  $gui_cfg = config_get('gui');
  $opt = array('output' => 'map_name_with_inactive_mark', 'order_by' => $gui_cfg->tprojects_combo_order_by);
  $arrProducts = $tproject_mgr->get_accessible_for_user($id,$opt);

  $tproject_cookie = 'TL_lastTestProjectForUserID_'. $id;
  if (isset($_COOKIE[$tproject_cookie]))
  {
    if (isset($arrProducts[$_COOKIE[$tproject_cookie]]) && $arrProducts[$_COOKIE[$tproject_cookie]])
    {
      $_SESSION['testprojectID'] = $_COOKIE[$tproject_cookie];
      tLog('Cookie: {$tproject_cookie}='.$_SESSION['testprojectID']);
    }
  }
  if (!$_SESSION['testprojectID'])
  {
      $tpID = null;
      if (sizeof($arrProducts))
      {
        $tpID = key($arrProducts);
      } 
      $_SESSION['testprojectID'] = $tpID;
  }
  // Validation is done in navBar.php
  $tplan_cookie = 'TL_lastTestPlanForUserID_' . $id;
  if (isset($_COOKIE[$tplan_cookie]))
  {
    $_SESSION['testplanID'] = $_COOKIE[$tplan_cookie];
    tLog("Cookie: {$tplan_cookie}=".$_SESSION['testplanID']);
  }

  return 1;
}

/*
  function: getUsersForHtmlOptions

  args:  db: reference to db object
        [whereClause]:
        [add_blank_option]:
        [active_filter]:

  returns: map

  rev :
*/
function getUsersForHtmlOptions(&$db,$whereClause = null,$additional_users = null, 
                                $active_filter = null,$users = null, $opt=null)
{
  $users_map = null;
  if (!$users)
  {
    $sqlWhere = $whereClause;
    if(!is_null($active_filter))
    {
      $whereClause .= ' AND active =' . ($active_filter > 0 ? 1 : 0) . ' ';
    }
    $users = tlUser::getAll($db,$sqlWhere,"id",null,tlUser::TLOBJ_O_GET_DETAIL_MINIMUM);
  }
  return buildUserMap($users,!is_null($additional_users),$additional_users,$opt);
}

/*
  function: buildUserMap

  args:
       $users: map of user objects
       [add_options]: default false.
                      true, elements present on additional_options arguments
                      will be will added to result map.
       
       [additional_options]: default null
                             map with key=user id, value=verbose description 
  
  returns: map ready to be used on a HTML select input.

*/
function buildUserMap($users,$add_options = false, $additional_options=null, $opt=null)
{
  $my['opt'] = array('userDisplayFormat' => null);
  $my['opt'] = array_merge($my['opt'],(array)$opt);

  $usersMap = null;
  $inactivePrefix = lang_get('tag_for_inactive_users');
  if ($users)
  {
    if($add_options)
    {
      $my_options = is_null($additional_options) ? array( 0 => '') : $additional_options;
      foreach($my_options as $code => $verbose_code)
      {
          $usersMap[$code] = $verbose_code;
      }
    }
    $userSet = array_keys($users);
    $loops2do = count($userSet);
    
    for( $idx=0; $idx < $loops2do ; $idx++)
    {
      $userID = $userSet[$idx];
      $usersMap[$userID] = $users[$userID]->getDisplayName($my['opt']['userDisplayFormat']);
      if($users[$userID]->isActive == 0)
      {
        $usersMap[$userID] = $inactivePrefix . ' ' . $usersMap[$userID];
      } 
    }
  }
  return $usersMap;
}


/**
 * reset user password in DB
 * 
 * @param resource &$db reference to database handler
 * @param integer $userID 
 * @param string $newPasswordSendMethod, default 'send_password_by_mail'
 * 
 * @return hash
 *         status: integer result status code
 *         password: new password
 *         msg: error message (if any)  
 */
function resetPassword(&$db,$userID,$passwordSendMethod='send_password_by_mail')
{
  $doIt = false;
  $retval = array('status' => tl::OK, 'password' => '', 'msg' => ''); 

  $user = new tlUser($userID);
  $retval['status'] = $user->readFromDB($db);

  if ($retval['status'] >= tl::OK)
  {
    // Reset can be done ONLY if user authentication method allows it.
    $systemCfg = config_get('authentication');
    $userAuthMethod = trim($user->authentication);
    if($userAuthMethod == '' || is_null($userAuthMethod))
    {
      $userAuthMethod = $systemCfg['method'];
    }  
  
    $cfg = $systemCfg['domain'];
    $doIt = isset($cfg[$userAuthMethod]) && 
                  $cfg[$userAuthMethod]['allowPasswordManagement'];
  }

  if ($doIt)
  {
    $retval['status'] = tlUser::E_EMAILLENGTH;

    if( trim($user->emailAddress) != "")
    {
      $newPassword = tlUser::generatePassword(8,4); 
      $retval['status'] = $user->setPassword($newPassword,$userAuthMethod);
      if ($retval['status'] >= tl::OK)
      {
        $retval['password'] = $newPassword;
        $mail_op = new stdClass();
        $mail_op->status_ok = false;
        if( $passwordSendMethod == 'send_password_by_mail' )
        {
          $msgBody = lang_get('your_password_is') . "\n\n" . $newPassword . "\n\n" . lang_get('contact_admin');
          $mail_op = @email_send(config_get('from_email'), 
                                 $user->emailAddress,lang_get('mail_passwd_subject'),$msgBody);
        }
        
        if ($mail_op->status_ok || ($passwordSendMethod == 'display_on_screen') )
        {
          $retval['status'] = $user->writePasswordToDB($db);
        }
        else
        {
          $retval['status'] = tl::ERROR;
          $retval['msg'] = $mail_op->msg;
        }
      }
    }
  }

  $retval['msg'] = ($retval['msg'] != "") ? $retval['msg'] : getUserErrorMessage($retval['status']) ;
  return $retval;
}

/*
  function: getUserErrorMessage

  args :
  
  returns: 

*/
function getUserErrorMessage($code)
{
  $msg = 'ok';
  switch($code)
  {
    case tl::OK:
    break;

    case tlUser::E_LOGINLENGTH:
      $msg = lang_get('error_user_login_length_error');
    break;

    case tlUser::E_EMAILLENGTH:
      $msg = lang_get('empty_email_address');
    break;

    case tlUser::E_EMAILFORMAT:
      $msg = lang_get('no_good_email_address');
    break;
      
    case tlUser::E_NOTALLOWED:
      $msg = lang_get('user_login_valid_regex');
    break;

    case tlUser::E_FIRSTNAMELENGTH:
      $msg = lang_get('empty_first_name');
    break;

    case tlUser::E_LOGINALREADYEXISTS:
      $msg = lang_get('user_name_exists');
    break;

    case tlUser::E_LASTNAMELENGTH:
      $msg = lang_get('empty_last_name');
    break;

    case tlUser::E_PWDEMPTY:
      $msg = lang_get('warning_empty_pwd');
    break;

    case tlUser::E_PWDDONTMATCH:
      $msg = lang_get('wrong_old_password');
    break;

    case tlUser::S_PWDMGTEXTERNAL :
      $msg = lang_get('password_mgmt_is_external');
    break;

    case ERROR:
    case tlUser::E_DBERROR:
    default:
      $msg = lang_get('error_user_not_updated');
    break;  
  }
  return $msg;
}


/*
  function: getAllUsersRoles

  args:

  returns:
  

*/
function getAllUsersRoles(&$db,$order_by = null)
{
  $tables = tlObject::getDBTables(array('users','roles'));
    
  $sql = "SELECT users.id FROM {$tables['users']} users " .
         " LEFT OUTER JOIN {$tables['roles']} roles ON users.role_id = roles.id ";
  $sql .= is_null($order_by) ? " ORDER BY login " : $order_by;

  $users = tlDBObject::createObjectsFromDBbySQL($db,$sql,"id","tlUser",false,tlUser::TLOBJ_O_GET_DETAIL_MINIMUM);
  
  $loop2do = count($users);
  $specialK = array_flip((array)config_get('demoSpecialUsers'));
  $demoModeEnabled = config_get('demoMode');
  for($idx=0; $idx < $loop2do; $idx++)
  {
    $users[$idx]->isDemoSpecial = $demoModeEnabled ? isset($specialK[$users[$idx]->login]) : false; 
  }
  return $users;
}

/*
  function: getTestersForHtmlOptions

  args: 

  returns:

*/
/**
 * getTestersForHtmlOptions
 * returns users that have role on ($tplanID,$tprojectID) with right
 * to execute a test case.
 *
 * @param resource &$db reference to database handler
 * @param integer $tplanID test plan id
 * @param integer $tprojectID test project id
 * @param $users UNUSED - remove
 * @param $additional_testers TBD
 * @param string $activeStatus. values: 'active','inactive','any'
 * 
 * @return array TBD  
 * @internal revisions
 */
function getTestersForHtmlOptions(&$db,$tplanID,$tproject,$users = null, 
                                  $additional_testers = null,$activeStatus = 'active')
{
  $orOperand = false;
  $activeTarget = 1;
  switch ($activeStatus)
  {
    case 'any':
      $orOperand = true;
    break;
        
    case 'inactive':
      $activeTarget = 0;
    break;
        
    case 'active':
      default:
    break;
  }

  $users_roles = get_tplan_effective_role($db,$tplanID,$tproject,null,$users);

  $userFilter = array();
  foreach($users_roles as $keyUserID => $roleInfo)
  {
    if( is_object($roleInfo['effective_role']) )
    {
      if( $roleInfo['effective_role']->hasRight('testplan_execute') && 
              ($orOperand || $roleInfo['user']->isActive == $activeTarget) )
      {
        $userFilter[$keyUserID] = $roleInfo['user'];
      }
    }   
  }
  return buildUserMap($userFilter,true,$additional_testers);
}

/**
 *
 */
function initialize_tabsmenu()
{
  $hl = new stdClass();
  $hl->view_roles = 0;
  $hl->create_role = 0;
  $hl->edit_role = 0;

  $hl->view_users = 0;
  $hl->create_user = 0;
  $hl->edit_user = 0;

  $hl->assign_users_tproject = 0;
  $hl->assign_users_tplan = 0;
  return $hl;
}


/*
  function: getGrantsForUserMgmt 
            utility function used on all user and role pages
            to pass grants to smarty templates.
            Logic is:
            if user has Global user management right => no control
               on specific test project or test plan is done
           

  args:
  
  returns: 

*/
function getGrantsForUserMgmt(&$dbHandler,&$userObj,$tprojectID=null,$tplanID=null)
{
  $answers = new stdClass();
  $grants = new stdClass();
  $grants->user_mgmt = $userObj->hasRight($dbHandler,"mgt_users");
  $grants->role_mgmt = $userObj->hasRight($dbHandler,"role_management");

  // in order to assign DEFAULT role to user, due to current implementation
  // you need to access all user data => we request you can do mgt_users.
  // A question arise:
  // how user_role_assignment right has to be understood ?
  // how it is used ? where ?
  // $grants->user_role_assignment = $userObj->hasRight($dbHandler,"user_role_assignment");

  $grants->tproject_user_role_assignment = "no";
  $grants->tplan_user_role_assignment = "no";
    
  if($grants->user_mgmt == 'yes')
  {
    $grants->tplan_user_role_assignment = 'yes';
    $grants->tproject_user_role_assignment = 'yes';  
  }
  else
  {
    $grants->tplan_user_role_assignment = $userObj->hasRight($dbHandler,"testplan_user_role_assignment",
                                                             $tprojectID,$tplanID);
        
        
    $answers->user_role_assignment = $userObj->hasRight($dbHandler,"user_role_assignment",null,-1);
    $answers->testproject_user_role_assignment=$userObj->hasRight($dbHandler,"testproject_user_role_assignment",$tprojectID,-1);
    if($answers->user_role_assignment == "yes" || $answers->testproject_user_role_assignment == "yes")
    {    
      $grants->tproject_user_role_assignment = "yes";
    }
  }    
  foreach($grants as $key => $value)
  {
    $grants->$key = $value == "yes" ? "yes" : "no";       
  }
    
  return $grants;
}