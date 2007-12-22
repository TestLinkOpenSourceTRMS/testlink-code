<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: users.inc.php,v $
 *
 * @version $Revision: 1.61 $
 * @modified $Date: 2007/12/22 09:58:59 $ $Author: schlundus $
 *
 * Functions for usermanagement
 *
 * 20050821 - fm - BUGID 239
 * 20051231 - scs - changes due to ADBdb
 * 20060205 - JBA - Remember last product (BTS 221); added by MHT
 * 20060224 - franciscom - changes in session product -> testproject
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
function setUserSession(&$db,$user, $id, $roleID, $email, $locale = null, $active = null,
                        $usertestprojectRoles = null,$userTestPlanRoles = null)
{
	tLog('setUserSession: $user=' . $user . ' $id='.$id.' $roleID='.$roleID.' $email='.$email.' $locale='.$locale);
	if (!is_null($user))
		$_SESSION['user'] = $user; 

	$_SESSION['userID']	= $id;
	$_SESSION['userdisplayname'] = $user;
	$_SESSION['email'] = $email; 
	$_SESSION['testprojectRoles'] = $usertestprojectRoles; 
	$_SESSION['testPlanRoles'] = $userTestPlanRoles; 
	$_SESSION['testprojectID'] = null;
	$_SESSION['s_lastAttachmentList'] = null;

	$user = tlUser::getById($db,$id);
	if ($user)
		$_SESSION['userdisplayname'] = $user->getDisplayName();
	
	if (!is_null($roleID))
	{
		$roleID = intval($roleID);
		$_SESSION['roleID'] = $roleID; 
		$role = new tlRole($roleID);
		if ($role->readFromDb($db) == tl::OK)
			$_SESSION['role'] = $role->description;
		tLog('setUserSession: $user='.$_SESSION['role']);
	}
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

function getUsersForHtmlOptions(&$db,$whereClause = null,$add_blank_option = false)
{
	$users_map = null;
	$users = tlUser::getAll($db,$whereClause,"id");
	if ($users)
	{
		if($add_blank_option)
			$users_map[0] = '';
		foreach($users as $id => $user)
		{
			$users_map[$id] = $user->getDisplayName();
		}
	}
	return $users_map;
}

function resetPassword(&$db,$userID,&$errorMsg)
{
	$errorMsg = '';
	$user = new tlUser($userID);
	$result = $user->readFromDB($db);
	if ($result == tl::OK)
	{
		if (strlen($user->emailAddress))
		{
			$newPassword = md5(uniqid(rand(),1));
			$result = $user->setPassword($newPassword);
			if ($result == tl::OK)
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
			$result = tlUser::USER_E_EMAILLENGTH;
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
		case tlUser::USER_E_LOGINLENGTH:
			$msg = lang_get('error_user_login_length_error');
			break;
		case tlUser::USER_E_EMAILLENGTH:
			$msg = lang_get('empty_email_address');
			break;
		case tlUser::USER_E_NOTALLOWED:
			$msg = lang_get('user_login_valid_regex');
			break;
		case tlUser::USER_E_FIRSTNAMELENGTH:
			$msg = lang_get('empty_first_name');
			break;
		case tlUser::USER_E_LOGINALREADYEXISTS:
			$msg = lang_get('user_name_exists');
			break;
		case tlUser::USER_E_LASTNAMELENGTH:
			$msg = lang_get('empty_last_name');
			break;
		case tlUser::USER_E_PWDEMPTY:
			$msg = lang_get('warning_empty_pwd');
			break;
		case tlUser::USER_E_PWDDONTMATCH:
			$msg = lang_get('passwd_dont_match');
			break;
		case tlUser::USER_S_PWDMGTEXTERNAL	:
			$msg = lang_get('password_mgmt_is_external');
			break;
		case ERROR:
		case tlUser::USER_E_DBERROR:
		default:
			$msg = lang_get('error_user_not_updated');
	}
	return $msg;
}

function getAllUsersRoles(&$db,$order_by = null)
{
	$query = "SELECT users.id FROM users LEFT OUTER JOIN roles ON users.role_id = roles.id ";
	$query .= is_null($order_by) ? " ORDER BY login " : $order_by;
	
	$users = tlDBObject::createObjectsFromDBbySQL($db,$query,"id","tlUser");
	return $users;
}
?>
