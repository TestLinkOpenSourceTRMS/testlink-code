<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 * 
 * This file handles the initial authentication for login and creates all user session variables.
 *
 * @filesource	doAuthorize.php
 * @package 	TestLink
 * @author 		Chad Rosen, Martin Havlat
 * @copyright 	2003-2011, TestLink community 
 * @link 		http://www.teamst.org/
 *
 * @todo Setting up cookies so that the user can automatically login next time
 *
 * @internal revisions
 * @since 1.9.4
 * 20111127 - franciscom - doAuthorize() interface changed
 * 20110813 - franciscom - TICKET 4342: Security problem with multiple Testlink installations on the same server
 */

/** TBD */ 
require_once("users.inc.php");
require_once("roles.inc.php");

/** 
 * authorization function verifies login & password and set user session data 
 * return map
 *
 * we need an option to skip existent session block, in order to use
 * feature that requires login when session has expired and user has some data
 * not saved. (ajaxlogin on login.php page)
 */
function doAuthorize(&$db,$login,$pwd,$options=null)
{
	$result = array('status' => tl::ERROR, 'msg' => null);
	$_SESSION['locale'] = TL_DEFAULT_LOCALE; 
	
	$my['options'] = array('doSessionExistsCheck' => true); 
	$my['options'] = array_merge($my['options'], (array)$options);
	if (!is_null($pwd) && !is_null($login))
	{
		$user = new tlUser();
		$user->login = $login;
		$login_exists = ($user->readFromDB($db,tlUser::USER_O_SEARCH_BYLOGIN) >= tl::OK); 
		if ($login_exists)
		{
			$password_check = auth_does_password_match($user,$pwd);
			
			// This is right way to go, we provide better message
			if(!$password_check->status_ok)
			{
				$result = array('status' => tl::ERROR, 'msg' => $password_check->msg);
			}
			
			if ($password_check->status_ok && $user->isActive)
			{
				// TICKET 4342 
				// Need to do set COOKIE following Mantis model
				$auth_cookie_name = config_get('auth_cookie');
				$expireOnBrowserClose=false;
				setcookie($auth_cookie_name,$user->getSecurityCookie(),$expireOnBrowserClose,'/');			

				// Disallow two sessions within one browser
				if ($my['options']['doSessionExistsCheck'] && 
						isset($_SESSION['currentUser']) && !is_null($_SESSION['currentUser']))
				{
						$result['msg'] = lang_get('login_msg_session_exists1') . 
						                 ' <a style="color:white;" href="logout.php">' . 
							             lang_get('logout_link') . '</a>' . lang_get('login_msg_session_exists2');
				}
				else
				{ 
					// Setting user's session information
					$_SESSION['currentUser'] = $user;
					$_SESSION['lastActivity'] = time();
					
					global $g_tlLogger;
					$g_tlLogger->endTransaction();
					$g_tlLogger->startTransaction();
					setUserSession(	$db,$user->login, $user->dbID,$user->globalRoleID,$user->emailAddress, 
									$user->locale,null);
					$result['status'] = tl::OK;
				}
			}
			else
			{
				logAuditEvent(TLS("audit_login_failed",$login,$_SERVER['REMOTE_ADDR']),"LOGIN_FAILED",
							  $user->dbID,"users");
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