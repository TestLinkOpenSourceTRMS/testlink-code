<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 * 
 * This file handles the initial authentication for login and creates all user session variables.
 *
 * @filesource  doAuthorize.php
 * @package     TestLink
 * @author      Chad Rosen, Martin Havlat
 * @copyright   2003-2013, TestLink community 
 * @link        http://www.teamst.org/
 *
 *
 * @internal revisions
 * @since 1.9.9
 */

require_once("users.inc.php");
require_once("roles.inc.php");
require_once("ldap_api.php");

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

  $doLogin = false;

  if (!is_null($pwd) && !is_null($login))
  {
    $user = new tlUser();
    $user->login = $login;
    $login_exists = ($user->readFromDB($db,tlUser::USER_O_SEARCH_BYLOGIN) >= tl::OK); 

    if ($login_exists)
    {
      $password_check = auth_does_password_match($user,$pwd);
      if(!$password_check->status_ok)
      {
        $result = array('status' => tl::ERROR, 'msg' => null);
      }
      
      $doLogin = $password_check->status_ok && $user->isActive;
      if( !$doLogin )
      {
        logAuditEvent(TLS("audit_login_failed",$login,$_SERVER['REMOTE_ADDR']),"LOGIN_FAILED",$user->dbID,"users");
      } 
    }
    else
    {
      $authCfg = config_get('authentication');
      if( $authCfg['ldap_automatic_user_creation'] )
      {
        $user->authentication = 'LDAP';  // force for auth_does_password_match
        $check = auth_does_password_match($user,$pwd);

        if( $check->status_ok )
        {
          $user = new tlUser(); 
          $user->login = $login;
          $user->authentication = 'LDAP';
          $user->isActive = true;
          $user->setPassword($pwd);  // write password on DB anyway

          $user->emailAddress = ldap_get_field_from_username($user->login,strtolower($authCfg['ldap_email_field']));
          $user->firstName = ldap_get_field_from_username($user->login,strtolower($authCfg['ldap_firstname_field']));
          $user->lastName = ldap_get_field_from_username($user->login,strtolower($authCfg['ldap_surname_field']));

          $user->firstName = (is_null($user->firstName) || strlen($user->firstName) == 0) ? $login : $user->firstName;
          $user->lastName = (is_null($user->lastName) || strlen($user->lastName) == 0) ? $login : $user->lastName;


          $doLogin = ($user->writeToDB($db) == tl::OK);
        }  
      }  
    }  
  }

  if( $doLogin )
  {
    // After some tests (I'm very tired), seems that re-reading is best option
    $user = new tlUser();
    $user->login = $login;
    $user->readFromDB($db,tlUser::USER_O_SEARCH_BYLOGIN);

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
      setUserSession($db,$user->login, $user->dbID,$user->globalRoleID,$user->emailAddress,$user->locale,null);
          
      $result['status'] = tl::OK;
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
  
  $authMethod = $userObj->authentication;
  switch($userObj->authentication)
  {
    case 'DB':
    case 'LDAP':
    break;

    default:
      $authMethod = $authCfg['method'];
    break;
  }

  // switch($authCfg['method'])
  switch($authMethod)
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
    case 'DB':
    default:
      $ret->status_ok = ($userObj->comparePassword($cleartext_password) == tl::OK);
      $ret->msg = 'ok';
    break;
  }

  return $ret;
}