<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @package 	TestLink
 * @author 		Martin Havlat
 * @copyright 	2006-2009, TestLink community 
 * @version    	CVS: $Id: users.inc.php,v 1.95 2009/06/10 09:14:05 havlat Exp $
 * @link 		http://www.teamst.org/index.php
 *
 * @internal
 * Functions for usermanagement
 *
 * rev: 20090517 - franciscom - getTestersForHtmlOptions() interface changes
 *                              buildUserMap() added prefix to tag inactive users
 *      20081221 - franciscom - buildUserMap() interface changes
 *      20081213 - franciscom - refactoring removing old config options 
 *      20080822 - franciscom - resetPassword() - added generatePassword()
 *      20080405 - franciscom - getGrantsForUserMgmt()
 *      20080315 - franciscom - added initalize_tabsmenu()
 *      20080210 - franciscom - fixed message for error tlUser::E_PWDDONTMATCH
 *
 */

require_once("common.php");
require_once("user.class.php");

$authCfg = config_get('authentication');
if( 'LDAP' == $authCfg['method'] )
{
	require_once(dirname(__FILE__) . "/ldap_api.php");
}

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

	$_SESSION['userID']	= $id;
	$_SESSION['testprojectID'] = null;
	$_SESSION['s_lastAttachmentList'] = null;

	if (!is_null($locale))
	{
		$_SESSION['locale'] = $locale;
		set_dt_formats();
	}

	$tproject_mgr = new testproject($db);

	$gui_cfg = config_get('gui');
	$order_by = $gui_cfg->tprojects_combo_order_by;
	$arrProducts = $tproject_mgr->get_accessible_for_user($id,'map',$order_by);

	 // 20051208 - JBA - added to set the lastProduct the user has selected before logging off.
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
       20071228 - franciscom - added active_filter
*/
function getUsersForHtmlOptions(&$db,$whereClause = null,$additional_users = null, $active_filter = null,$users = null)
{
	$users_map = null;
	if (!$users)
	{
		$users = tlUser::getAll($db,$whereClause,"id",null,tlUser::TLOBJ_O_GET_DETAIL_MINIMUM);
	}
  
	$the_users = $users;
	if ($users)
	{
		if(!is_null($active_filter))
		{
			$the_users = array();
			foreach($users as $id => $user)
			{
				if($user->isActive == $active_filter)
				{
					$the_users[$id] = $users[$id];
				}	
			}
		}
	}
	return buildUserMap($the_users,!is_null($additional_users),$additional_users);
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
function buildUserMap($users,$add_options = false, $additional_options=null)
{
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
		foreach($users as $id => $user)
		{
			$usersMap[$id] = $user->getDisplayName();
			if($user->isActive == 0)
			{
			    $usersMap[$id] = $inactivePrefix . ' ' . $usersMap[$id];
			} 
		}
	}
	return $usersMap;
}


/**
 * reset user password in DB
 * 
 * @param resource &$db reference to DB identifier
 * @param integer $userID 
 * @param string &$errorMsg reference to error message
 * 
 * @return integer result status code
 */
function resetPassword(&$db,$userID,&$errorMsg)
{

	$errorMsg = '';
	$user = new tlUser($userID);
	$result = $user->readFromDB($db);
	
	if ($result >= tl::OK)
	{
		if ($user->emailAddress != "")
		{
			$newPassword = tlUser::generatePassword(8,4); 
			$result = $user->setPassword($newPassword);

			if ($result >= tl::OK)
			{
				$msgBody = lang_get('your_password_is') . $newPassword . lang_get('contact_admin');
				$mail_op = @email_send(config_get('from_email'), $user->emailAddress,
		                           lang_get('mail_passwd_subject'), $msgBody);
				if ($mail_op->status_ok)
					$result = $user->writeToDB($db);
				else
				{
					$result = tl::ERROR;
					$errorMsg = $mail_op->msg;
				}
			}
		}
		else
			$result = tlUser::E_EMAILLENGTH;
	}
	if ($errorMsg != "")
		$errorMsg = getUserErrorMessage($result);

	return $result;
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

		case tlUser::S_PWDMGTEXTERNAL	:
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
    $tables['users'] = DB_TABLE_PREFIX . 'users';
    $tables['roles'] = DB_TABLE_PREFIX . 'roles';
    
	$sql = "SELECT users.id FROM {$tables['users']} users " .
	         " LEFT OUTER JOIN {$tables['roles']} roles ON users.role_id = roles.id ";
	$sql .= is_null($order_by) ? " ORDER BY login " : $order_by;

	$users = tlDBObject::createObjectsFromDBbySQL($db,$sql,"id","tlUser",false,tlUser::TLOBJ_O_GET_DETAIL_MINIMUM);
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
 * @param $db reference to db handler
 * @param int $tplanID test plan id
 * @param int $tprojectID test project id
 * @param     $users
 * @param
 * @param string $activeStatus. values: 'active','inactive','any'  
 */
function getTestersForHtmlOptions(&$db,$tplanID,$tprojectID,$users = null, 
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

    $users_roles = get_tplan_effective_role($db,$tplanID,$tprojectID,null,$users);
    $userFilter = array();
    foreach($users_roles as $keyUserID => $roleInfo)
    {
        if($roleInfo['effective_role']->hasRight('testplan_execute') && 
           ($orOperand || $roleInfo['user']->isActive == $activeTarget) )
        {
            
             $userFilter[$keyUserID] = $roleInfo['user'];
        }   
    }
	return buildUserMap($userFilter,true,$additional_testers);
}


/*
  function: initialize_tabsmenu

  args:

  returns:

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
?>