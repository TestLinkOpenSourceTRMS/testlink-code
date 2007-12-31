<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: users.inc.php,v $
 *
 * @version $Revision: 1.65 $
 * @modified $Date: 2007/12/31 13:15:26 $ $Author: schlundus $
 *
 * Functions for usermanagement
 *
 * 20071228 - franciscom - added getTestersForHtmlOptions() 
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
	if (!is_null($user))
		$_SESSION['user'] = $user; 

	$_SESSION['userID']	= $id;
	$_SESSION['email'] = $email; 
	$_SESSION['testprojectID'] = null;
	$_SESSION['s_lastAttachmentList'] = null;

	if (!is_null($roleID))
	{
		$_SESSION['roleID'] = intval($roleID); 
		tLog('setUserSession: $user :: roleID '.$_SESSION['roleID']);
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
function getUsersForHtmlOptions(&$db,$whereClause = null,$add_blank_option = false, $active_filter=null)
{
	$users_map = null;
	$users = tlUser::getAll($db,$whereClause,"id");
	
	$the_users=$users;
	if ($users)
	{
    if( !is_null($active_filter) )
    {
        $the_users=array();
		    foreach($users as $id => $user)
		    {
		      if($user->bActive == $active_filter)
		      {
		    	  $the_users[$id] = $users[$id];
		    	}  
		    }
    }
  }

	if ($the_users)
	{
		if($add_blank_option)
			$users_map[0] = '';
		foreach($the_users as $id => $user)
		{
			$users_map[$id] = $user->getDisplayName();
		}
	}
	return $users_map;
}





/*
  function: resetPassword

  args:
  
  returns: 

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
			$newPassword = md5(uniqid(rand(),1));
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
function getTestersForHtmlOptions(&$db,$tplanID,$tprojectID)
{
    define('ACTIVE_USERS',1);
    $users_roles=get_tplan_effective_role($db,$tplanID,$tprojectID);
    $userFilter=array();
    foreach($users_roles as $keyUserID => $roleInfo)
    {
      if( roleHasRight($db,$roleInfo['effective_role_id'],'testplan_execute') )
      {
        $userFilter[]=$keyUserID;
      }  
    } 
   
    $testerList='';
    if( count($userFilter) > 0 && isset($userFilter[0]) )
    {
      $testerList=implode("','",$userFilter);  
    }
    $whereClause=" WHERE id IN ('{$testerList}') ";        


    $testers = getUsersForHtmlOptions($db,$whereClause,ADD_BLANK_OPTION,ACTIVE_USERS);
    return $testers;
    
}
?>
