<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 * 
 * @filesource $RCSfile: doAuthorize.php,v $
 * @version $Revision: 1.14 $
 * @modified $Date: 2007/01/31 23:18:25 $ by $Author: jbarchibald $
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
 *///////////////////////////////////////////////////////////////////////////


require_once("users.inc.php");
require_once("roles.inc.php");


/** authorization function verifies login & password and set user session data */
//20051118 - scs - login and pwd are stripped two times, replaced POST by 
//					function params
//20060102 - scs - ADOdb changes
function doAuthorize(&$db,$login,$pwd)
{
    // 20070131 - jbarchibald - global import not needed. 
	// global $g_ui_show_check_filter_tp_by_testproject;
	$bSuccess = false;
	$sProblem = 'wrong'; // default problem attribute value
	
	$_SESSION['locale'] = TL_DEFAULT_LOCALE; 

	if (!is_null($pwd) && !is_null($login))
	{
		$login_exists = existLogin($db,$login,$userInfo);
		tLog("Account exist = " . $login_exists);

    if ($login_exists )
    {
       $password_check=auth_does_password_match( $login, $pwd, $userInfo['password']); 
    }
    
		//encrypt the password so it isn't stored plain text in the db
		if ($login_exists && $password_check->status_ok && $userInfo['active'])
		{
			// 20051007 MHT Solved  0000024 Session confusion 
			// Disallow two session with one browser
			if (isset($_SESSION['user']) && strlen($_SESSION['user']))
			{
				$sProblem = 'sessionExists';
				tLog("Session exists. No second login is allowed", 'INFO');
			}
			else
			{ 
                // 20070131 - jbarchibald
                $_SESSION['filter_tp_by_product'] = 1;
				$userProductRoles = getUserProductRoles($db,$userInfo['id']);
				$userTestPlanRoles = getUserTestPlanRoles($db,$userInfo['id']);
			    //Setting user's session information
			    // MHT 200507 move session update to function
			    setUserSession($db,$userInfo['login'], $userInfo['id'], 
			    		$userInfo['role_id'], $userInfo['email'], 
			    		$userInfo['locale'],null,$userProductRoles,$userTestPlanRoles);
		    	$bSuccess = true;
			}
		}
		else
		{
			 tLog("Account ".$login." doesn't exist or used wrong password.",'INFO');
		}
			
	}
	if ($bSuccess)
	{
	    tLog("Login ok. (Timing: " . tlTimingCurrent() . ')', 'INFO');
	    //forwarding user to the mainpage
	    redirect($_SESSION['basehref'] ."index.php");
	}
	else
	{
		// not authorized
	    tLog("Login '$login' fails. (Timing: " . tlTimingCurrent() . ')', 'INFO');
		redirect($_SESSION['basehref'] . "login.php?note=" . $sProblem);
	}
}


// 20060507 - franciscom - based on mantis function
//
// crypted_password is only used when the authentication method is 'MD5'
//
// returns:
//         obj->status_ok = true/false
//         obj->msg = message to explain what has happened to a human being.
//
function auth_does_password_match( $login_name, $cleartext_password, $crypted_password) 
{
    $msg[ERROR_LDAP_AUTH_FAILED]=lang_get('error_ldap_auth_failed');
    $msg[ERROR_LDAP_SERVER_CONNECT_FAILED]=lang_get('error_ldap_server_connect_failed');
    $msg[ERROR_LDAP_UPDATE_FAILED]=lang_get('error_ldap_update_failed');
    $msg[ERROR_LDAP_USER_NOT_FOUND]=lang_get('error_ldap_user_not_found');
    $msg[ERROR_LDAP_BIND_FAILED]=lang_get('error_ldap_bind_failed');
   
    
		$login_method = config_get( 'login_method' );
    $ret->status_ok=true;
    $ret->msg='ok';
  	if ( 'LDAP' == $login_method ) 
		{
			$xx=ldap_authenticate( $login_name, $cleartext_password );
			$ret->status_ok=$xx->status_ok;
		  $ret->msg=$msg[$xx->status_code];	
		}
    else
    {
       if($crypted_password != md5($cleartext_password))
       {
         $ret->status_ok=false;      
       }
    }
		return($ret);
}
?>
