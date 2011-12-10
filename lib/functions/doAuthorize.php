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
 *
 * @internal revisions
 * @since 1.9.4
 * 20111210 - franciscom - TICKET 4711: Apache Webserver - SSL Client Certificate Authentication (Single Sign-on?)
 *						   new function doSSOClientCertificate()
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
	global $g_tlLogger;

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
 * for SSL Cliente Certificate we can not check password but
 * 1. login exists
 * 2. SSL context exist
 *
 * return map
 *
 */
function doSSOClientCertificate(&$dbHandler,$apache_mod_ssl_env,$authCfg=null)
{
	global $g_tlLogger;

	$result = array('status' => tl::ERROR, 'msg' => null);
	if( !isset($apache_mod_ssl_env['SSL_PROTOCOL']) )
	{
		return $result; 
	}
	
	// With this we trust SSL is enabled => go ahead with login control
	$authCfg = is_null($authCfg) ? config_get('authentication') : $authCfg;

	$login = $apache_mod_ssl_env[$authCfg['SSO_uid_field']];
	if( !is_null($login) )
	{
		$user = new tlUser();
		$user->login = $login;
		$login_exists = ($user->readFromDB($dbHandler,tlUser::USER_O_SEARCH_BYLOGIN) >= tl::OK); 
		if( $login_exists && $user->isActive)
		{
			// Need to do set COOKIE following Mantis model
			$auth_cookie_name = config_get('auth_cookie');
			$expireOnBrowserClose=false;
			setcookie($auth_cookie_name,$user->getSecurityCookie(),$expireOnBrowserClose,'/');			

			// Disallow two sessions within one browser
			if (isset($_SESSION['currentUser']) && !is_null($_SESSION['currentUser']))
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
					
					$g_tlLogger->endTransaction();
					$g_tlLogger->startTransaction();
					setUserSession($dbHandler,$user->login, $user->dbID,$user->globalRoleID,$user->emailAddress, 
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
	return $result;
}





/** 
 * @return array
 *         obj->status_ok = true/false
 *         obj->msg = message to explain what has happened to a human being.
 */
function auth_does_password_match(&$userObj,$cleartext_password)
{
	$authCfg = config_get('authentication');
	$ret = new stdClass();
	$ret->status_ok = false;
	$ret->msg = sprintf(lang_get('unknown_authentication_method'),$authCfg['method']);
	
	switch($authCfg['method'])
	{
		case 'LDAP':
			$msg[ERROR_LDAP_AUTH_FAILED] = lang_get('error_ldap_auth_failed');
			$msg[ERROR_LDAP_SERVER_CONNECT_FAILED] = lang_get('error_ldap_server_connect_failed');
			$msg[ERROR_LDAP_UPDATE_FAILED] = lang_get('error_ldap_update_failed');
			$msg[ERROR_LDAP_USER_NOT_FOUND] = lang_get('error_ldap_user_not_found');
			$msg[ERROR_LDAP_BIND_FAILED] = lang_get('error_ldap_bind_failed');
			
			$xx = ldap_authenticate($userObj->login, $cleartext_password);
			$ret->status_ok = $xx->status_ok;
			$ret->msg = $msg[$xx->status_code];	
		break;
		
		case 'MD5':
			$ret->status_ok = ($userObj->comparePassword($cleartext_password) == tl::OK);
			$ret->msg = 'ok';
		break;
	}

	return $ret;
}
?>