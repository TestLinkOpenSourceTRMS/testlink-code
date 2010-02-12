<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 * 
 * This file handles the initial authentication for login and creates all user session variables.
 *
 * @package 	TestLink
 * @author 		Chad Rosen, Martin Havlat
 * @copyright 	2003-2009, TestLink community 
 * @version    	CVS: $Id: doAuthorize.php,v 1.34 2010/02/12 08:47:12 erikeloff Exp $
 * @link 		http://www.teamst.org/
 *
 * @todo Setting up cookies so that the user can automatically login next time
 *
 * @internal revisions:
 * 20100212 - eloff - BUGID 3103 - remove js-timeout alert in favor of BUGID 3088
 * 20100202 - franciscom - refactoring of doAuthorize (BUGID 0003129: After login failure blank page is displayed)
 *
 */

/** TBD */ 
require_once("users.inc.php");
require_once("roles.inc.php");

/** 
 * authorization function verifies login & password and set user session data 
 * return map
 *
 */
function doAuthorize(&$db,$login,$pwd)
{
	$result = array('status' => tl::ERROR, 'msg' => null);
	$_SESSION['locale'] = TL_DEFAULT_LOCALE; 
	if (!is_null($pwd) && !is_null($login))
	{
		$user = new tlUser();
		$user->login = $login;
		$login_exists = ($user->readFromDB($db,tlUser::USER_O_SEARCH_BYLOGIN) >= tl::OK); 
		if ($login_exists)
		{
			$password_check = auth_does_password_match($user,$pwd);
			if ($password_check->status_ok && $user->isActive)
			{
				// 20051007 MHT Solved  0000024 Session confusion 
				// Disallow two sessions within one browser
				if (isset($_SESSION['currentUser']) && !is_null($_SESSION['currentUser']))
				{
					$result['msg'] = lang_get('login_msg_session_exists1') . 
					                 ' <a style="color:white;" href="logout.php">' . 
						             lang_get('logout_link') . '</a>' . lang_get('login_msg_session_exists2');
				}
				else
				{ 
					//Setting user's session information
					$_SESSION['currentUser'] = $user;
					$_SESSION['lastActivity'] = time();
					
					global $g_tlLogger;
					$g_tlLogger->endTransaction();
					$g_tlLogger->startTransaction();
					setUserSession($db,$user->login, $user->dbID,$user->globalRoleID,$user->emailAddress, $user->locale,null);
					$result['status'] = tl::OK;
				}
			}
			else
			{
				logAuditEvent(TLS("audit_login_failed",$login,$_SERVER['REMOTE_ADDR']),"LOGIN_FAILED",$user->dbID,"users");
			}	
		}
	}
	return $result;
}


/** 
 * @return array
 *         obj->status_ok = true/false
 *         obj->msg = message to explain what has happened to a human being.
 */
// 20060507 - franciscom - based on mantis function
function auth_does_password_match(&$user,$cleartext_password)
{
	$authCfg = config_get('authentication');
	$ret = new stdClass();
	$ret->status_ok = true;
	$ret->msg = 'ok';
	
	if ('LDAP' == $authCfg['method']) 
	{
		$msg[ERROR_LDAP_AUTH_FAILED] = lang_get('error_ldap_auth_failed');
		$msg[ERROR_LDAP_SERVER_CONNECT_FAILED] = lang_get('error_ldap_server_connect_failed');
		$msg[ERROR_LDAP_UPDATE_FAILED] = lang_get('error_ldap_update_failed');
		$msg[ERROR_LDAP_USER_NOT_FOUND] = lang_get('error_ldap_user_not_found');
		$msg[ERROR_LDAP_BIND_FAILED] = lang_get('error_ldap_bind_failed');
		
		$xx = ldap_authenticate($user->login, $cleartext_password);
		$ret->status_ok = $xx->status_ok;
		$ret->msg = $msg[$xx->status_code];	
	}
	
	else // normal database password compare
	{
		if ($user->comparePassword($cleartext_password) != tl::OK)
			$ret->status_ok = false;
	}      
	
	return $ret;
}


?>
