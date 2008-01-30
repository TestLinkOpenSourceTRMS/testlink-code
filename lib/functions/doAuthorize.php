<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 * 
 * @filesource $RCSfile: doAuthorize.php,v $
 * @version $Revision: 1.24 $
 * @modified $Date: 2008/01/30 19:52:23 $ by $Author: schlundus $
 * @author Chad Rosen, Martin Havlat
 *
 * This file handles the initial login and creates all user session variables.
 *
 * @todo Setting up cookies so that the user can automatically login next time
 * 
 * Revision:
 *           20070130 - jbarchibald -
 *           $_SESSION['filter_tp_by_product'] should always default to = 1;
 *
 *           20060507 - franciscom - 
 *           added bare bones LDAP authentication using mantis code
 *                                  
 *
 */
require_once("users.inc.php");
require_once("roles.inc.php");

/** authorization function verifies login & password and set user session data */
function doAuthorize(&$db,$login,$pwd,&$msg)
{
    $result = tl::ERROR;
	$msg = 'wrong'; // default problem attribute value
	
	$_SESSION['locale'] = TL_DEFAULT_LOCALE; 
	if (!is_null($pwd) && !is_null($login))
	{
		$user = new tlUser();
		$user->login = $login;
		$login_exists = ($user->readFromDB($db,tlUser::USER_O_SEARCH_BYLOGIN) >= tl::OK); 
		if ($login_exists)
	    {
			$password_check = auth_does_password_match($user,$pwd);
			if ($password_check->status_ok && $user->bActive)
			{
				// 20051007 MHT Solved  0000024 Session confusion 
				// Disallow two sessions within one browser
				if (isset($_SESSION['currentUser']) && !is_null($_SESSION['currentUser']))
				{
					$msg = lang_get('login_msg_session_exists1') . ' <a style="color:white;" href="logout.php">' . 
							lang_get('logout_link') . '</a>' . lang_get('login_msg_session_exists2');
				}
				else
				{ 
					$_SESSION['filter_tp_by_product'] = 1;
					//Setting user's session information
					$_SESSION['currentUser'] = $user;
					global $g_tlLogger;
					$g_tlLogger->endTransaction();
					$g_tlLogger->startTransaction();
					setUserSession($db,$user->login, $user->dbID,$user->globalRoleID,$user->emailAddress, $user->locale,null);
					$result = tl::OK;
				}
			}
			else
				logAuditEvent(TLS("audit_login_failed",$login,$_SERVER['REMOTE_ADDR']),"LOGIN_FAILED",$user->dbID,"users");
		}
	}
	return $result;
}


// 20060507 - franciscom - based on mantis function
//
//
// returns:
//         obj->status_ok = true/false
//         obj->msg = message to explain what has happened to a human being.
//
function auth_does_password_match(&$user,$cleartext_password)
{
	$login_method = config_get('login_method');
	$ret->status_ok = true;
	$ret->msg = 'ok';
	if ('LDAP' == $login_method) 
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
	else if ($user->comparePassword($cleartext_password) != tl::OK)
		$ret->status_ok = false;      
	
	return $ret;
}
?>
