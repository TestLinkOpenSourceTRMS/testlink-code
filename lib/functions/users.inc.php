<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: users.inc.php,v $
 *
 * @version $Revision: 1.78 $
 * @modified $Date: 2008/11/04 19:25:48 $ $Author: schlundus $
 *
 * Functions for usermanagement
 *
 * rev: 20080822 - franciscom - resetPassword() - added generatePassword()
 *      20080405 - franciscom - getGrantsForUserMgmt()
 *      20080315 - franciscom - added initalize_tabsmenu()
 *      20080210 - franciscom - fixed message for error tlUser::E_PWDDONTMATCH
 *
 */
require_once("common.php");
require_once("user.class.php");
if('LDAP' == config_get('login_method') )
	require_once(dirname(__FILE__) . "/ldap_api.php");

/**
 * set session data after modification or authorization
 *
 * @param type $db [ref] documentation
 * @param type $user
 * @param type $id
 * @param type $roleID documentation
 * @param type $email documentation
 * @param type $locale [default = null] documentation
 * @param type $active [default = null] documentation
 * @return type documentation

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
    $cookedProduct = 'lastProductForUser'. $id;
    if (isset($_COOKIE[$cookedProduct]))
	{
		if (isset($arrProducts[$_COOKIE[$cookedProduct]]) && $arrProducts[$_COOKIE[$cookedProduct]])
    	{
			$_SESSION['testprojectID'] = $_COOKIE[$cookedProduct];
    		tLog('Cookie: lastProductForUser='.$_SESSION['testprojectID']);
    	}
	}
	if (!$_SESSION['testprojectID'])
	{
    	$tpID = null;
    	if (sizeof($arrProducts))
    		$tpID = key($arrProducts);
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
function getUsersForHtmlOptions(&$db,$whereClause = null,$add_blank_option = false, $active_filter=null,$users = null)
{
	$users_map = null;
	if (!$users)
		$users = tlUser::getAll($db,$whereClause,"id",null,tlUser::TLOBJ_O_GET_DETAIL_MINIMUM);

	$the_users=$users;
	if ($users)
	{
		if(!is_null($active_filter))
		{
			$the_users=array();
			foreach($users as $id => $user)
			{
				if($user->bActive == $active_filter)
					$the_users[$id] = $users[$id];
			}
		}
	}

	return buildUserMap($the_users,$add_blank_option);
}

function buildUserMap($users,$add_blank_option = false)
{
	$usersMap = null;
	if ($users)
	{
		if($add_blank_option)
			$usersMap[0] = '';
		foreach($users as $id => $user)
		{
			$usersMap[$id] = $user->getDisplayName();
		}
	}
	return $usersMap;
}

/*
  function: resetPassword

  args: db: dbHandler
        userID:
        reference to error string

  returns:
  
  rev: 20080822 - franciscom
       use generatePassword()

*/
function resetPassword(&$db,$userID,&$errorMsg)
{

	$errorMsg = '';
	$user = new tlUser($userID);
	$result = $user->readFromDB($db);
	
	if ($result >= tl::OK)
	{
		if (strlen($user->emailAddress))
		{
			$newPassword = generatePassword(8,4); 
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
	if (!strlen($errorMsg))
		$errorMsg = getUserErrorMessage($result);

	return $result;
}

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
	$query = "SELECT users.id FROM users LEFT OUTER JOIN roles ON users.role_id = roles.id ";
	$query .= is_null($order_by) ? " ORDER BY login " : $order_by;

	$users = tlDBObject::createObjectsFromDBbySQL($db,$query,"id","tlUser");
	return $users;
}

/*
  function: getTestersForHtmlOptions

  args :

  returns:

*/
function getTestersForHtmlOptions(&$db,$tplanID,$tprojectID,$users = null)
{
    $users_roles = get_tplan_effective_role($db,$tplanID,$tprojectID,null,$users);
    $userFilter = array();
    foreach($users_roles as $keyUserID => $roleInfo)
    {
		    if($roleInfo['effective_role']->hasRight('testplan_execute') && $roleInfo['user']->bActive)
			     $userFilter[$keyUserID] = $roleInfo['user'];
    }
	return buildUserMap($userFilter,true);
}


/*
  function:

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
            to pass grants to smarty templates

  args:
  
  returns: 

*/
function getGrantsForUserMgmt(&$dbHandler,&$userObj)
{
    $grants = new stdClass();
    $grants->user_mgmt=$userObj->hasRight($dbHandler,"mgt_users");
    $grants->role_mgmt=$userObj->hasRight($dbHandler,"role_management");
    
    if($grants->user_mgmt == 'yes')
    {
        $grants->tplan_user_role_assignment='yes';
        $grants->tproject_user_role_assignment='yes';  
    }
    else
    {
        $grants->tplan_user_role_assignment=$userObj->hasRight($dbHandler,"testplan_user_role_assignment");
        $grants->tproject_user_role_assignment=$userObj->hasRight($dbHandler,"user_role_assignment",null,-1);
    }
    return $grants;
}

/*
  function: generatePassword
            code taken from PHP manual user's notes. 
            you can choose the number of alphanumeric characters to add and 
            the number of non-alphanumeric characters. 
            You obtain a more secure password. 
            You can add another characters to the non-alphanumeric list if you need.
            
  args:numAlpha
       numNonAlpha
  
  returns: string

*/
function generatePassword($numAlpha=6,$numNonAlpha=2)
{
  $listAlpha = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
  $listNonAlpha = ',;:!?.$/*-+&@_+;./*&?$-!,';
  return str_shuffle(
     substr(str_shuffle($listAlpha),0,$numAlpha) .
     substr(str_shuffle($listNonAlpha),0,$numNonAlpha)
   );
}

?>
