<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 * 
 * Handles the initial authentication for login and creates all user session variables.
 *
 * @filesource  doAuthorize.php
 * @package     TestLink
 * @author      Chad Rosen, Martin Havlat,Francisco Mancardi
 * @copyright   2003-2017, TestLink community 
 * @link        http://www.testlink.org
 *
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
  $doChecks = true;
  
  $login = trim($login);
  $pwd = trim($pwd);

  if($login == '')
  {
    $doChecks = false;
    $result['msg'] = ' ';    
  } 

  if( $doChecks && !is_null($pwd) && !is_null($login))
  {
    $user = new tlUser();
    $user->login = $login;
    $login_exists = ($user->readFromDB($db,tlUser::USER_O_SEARCH_BYLOGIN) >= tl::OK); 

    if ($login_exists)
    {
      $doGo = true;
      $checkDate = !is_null($user->expiration_date);
      $checkDate = $checkDate && (trim($user->expiration_date) != '');
      
      if( $checkDate )
      {
        $now = strtotime(date_format(date_create(),'Y-m-d'));
        $exd = strtotime($user->expiration_date);

        if($now >= $exd )
        {
          // Expired!
          $doGo = false;
          $result['msg'] = lang_get('tluser_account_expired');
        }  
      }  
 
      if( $doGo )
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

          $uf = getUserFieldsFromLDAP($user->login,$authCfg['ldap'][$check->ldap_index]);
          $user->emailAddress = $uf->emailAddress;
          $user->firstName = $uf->firstName;
          $user->lastName = $uf->lastName;
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
    $expireOnBrowserClose=false;
    $auth_cookie_name = config_get('auth_cookie');
    $cookie_path = config_get('cookie_path');    

    // IMPORTANT DEVELOPMENT DEBUG NOTICE
    // From PHP Manual
    // setcookie() defines a cookie to be sent along with the rest of the HTTP headers. 
    // Like other headers, cookies must be sent BEFORE ANY OUTPUT from your script 
    // (this is a protocol restriction). This requires that you place calls to this function 
    // prior to any output, including <html> and <head> tags as well as any whitespace.
    //
    setcookie($auth_cookie_name,$user->getSecurityCookie(),$expireOnBrowserClose,$cookie_path);      

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

  $ret = array('status' => tl::ERROR, 'msg' => null, 'checkedBy' => __FUNCTION_);
  if( !isset($apache_mod_ssl_env['SSL_PROTOCOL']) )
  {
    return $ret; 
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
      $expireOnBrowserClose=false;
      $auth_cookie_name = config_get('auth_cookie');
      $cookie_path = config_get('cookie_path');
      setcookie($auth_cookie_name,$user->getSecurityCookie(),$expireOnBrowserClose,$cookie_path);      

      // Disallow two sessions within one browser
      if (isset($_SESSION['currentUser']) && !is_null($_SESSION['currentUser']))
      {
        $ret['msg'] = lang_get('login_msg_session_exists1') . 
                      ' <a style="color:white;" href="logout.php">' . 
                      lang_get('logout_link') . '</a>' . 
                      lang_get('login_msg_session_exists2');
      }
      else
      { 
        // Setting user's session information
        $_SESSION['currentUser'] = $user;
        $_SESSION['lastActivity'] = time();
          
        $g_tlLogger->endTransaction();
        $g_tlLogger->startTransaction();
        setUserSession($dbHandler,$user->login, $user->dbID,$user->globalRoleID,
                       $user->emailAddress,$user->locale,null);
        $ret['status'] = tl::OK;
      }
    }
    else
    {
      logAuditEvent(TLS("audit_login_failed",$login,$_SERVER['REMOTE_ADDR']),
                    "LOGIN_FAILED",$user->dbID,"users");
    } 
  }
  return $ret;
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

  switch($authMethod)
  {
    case 'LDAP':
      $msg[ERROR_LDAP_AUTH_FAILED] = lang_get('error_ldap_auth_failed');
      $msg[ERROR_LDAP_SERVER_CONNECT_FAILED] = lang_get('error_ldap_server_connect_failed');
      $msg[ERROR_LDAP_UPDATE_FAILED] = lang_get('error_ldap_update_failed');
      $msg[ERROR_LDAP_USER_NOT_FOUND] = lang_get('error_ldap_user_not_found');
      $msg[ERROR_LDAP_BIND_FAILED] = lang_get('error_ldap_bind_failed');
      $msg[ERROR_LDAP_START_TLS_FAILED] = lang_get('error_ldap_start_tls_failed');
      
      $xx = ldap_authenticate($userObj->login, $cleartext_password);
      $ret->status_ok = $xx->status_ok;
      $ret->msg = $xx->status_ok ? 'ok' : $msg[$xx->status_code];
      $ret->ldap_index = $xx->ldap_index;
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


/**
 *
 *
 */
function getUserFieldsFromLDAP($login,$ldapCfg)
{
  $k2l = array('emailAddress' => 'email', 'firstName' => 'firstname', 'lastName' => 'surname'); 
  $ret = new stdClass();
  
  foreach($k2l as $p => $ldf)
  {
    $ret->$p = ldap_get_field_from_username($ldapCfg,$login,
                                            strtolower($ldapCfg['ldap_' . $ldf . '_field']));
  }  

  // Defaults
  $k2l = array('firstName' => $login,'lastName' => $login, 'emailAddress' => 'no_mail_configured@on_ldapserver.org');
  foreach($k2l as $prop => $val)
  {
    if( is_null($ret->$prop) || strlen($ret->$prop) == 0 )
    {
      $ret->$prop = $val;  
    }
  }  

  return $ret;
} 


/** 
 *
 *
 */
function doSSOWebServerVar(&$dbHandler,$authCfg=null)
{
  $debugMsg = __FUNCTION__;

  $ret = array('status' => tl::ERROR, 'msg' => null, 'checkedBy' => __FUNCTION__);
  $authCfg = is_null($authCfg) ? config_get('authentication') : $authCfg;

  $userIdentity = null;
  if( isset($_SERVER[$authCfg['SSO_uid_field']]) )
  {
    $userIdentity = trim($_SERVER[$authCfg['SSO_uid_field']]);
  }  

  if( !is_null($userIdentity) && $userIdentity != '' )
  {
    $tables = tlObject::getDBTables(array('users'));

    $sql = "/* $debugMsg */" . 
           "SELECT login,role_id,email,first,last,active " .
           "FROM {$tables['users']} " . 
           "WHERE active = 1 AND " . 
           " {$authCfg['SSO_user_target_dbfield']} = '".
           $dbHandler->prepare_string($userIdentity) . "'";

    $rs = $dbHandler->get_recordset($sql);
    
    $login_exists = !is_null($rs) && ($accountQty =count($rs)) == 1;
    $loginKO = true;

    if( $login_exists  )
    {
      $rs = current($rs);
      if( intval($rs['active']) == 1 )
      {
        $loginKO = false;

        $user = new tlUser();
        $user->login = $rs['login'];
        $user->readFromDB($dbHandler,tlUser::USER_O_SEARCH_BYLOGIN);
        $xx = doSessionSetUp($dbHandler,$user);

        if( !is_null($xx) )
        {
          $ret = $xx;
        }   
      }    
    } 

    if( $loginKO )
    {
      if($accountQty > 1)
      {
        $ret['msg'] = TLS("audit_login_sso_failed_multiple_matches",
                          $_SERVER['REMOTE_ADDR'],$accountQty,$userIdentity,
                          $authCfg['SSO_user_target_dbfield']);
      }  
      else
      {
        $ret['msg'] = TLS("audit_login_failed_silence",$_SERVER['REMOTE_ADDR']);
      }  
      logAuditEvent($result['msg'], "LOGIN_FAILED","users");
    }
  }

  return $ret;
}

/**
 *
 */
function doSessionSetUp(&$dbHandler,&$userObj)
{
  global $g_tlLogger;

  $ret = null;

  // Need to do set COOKIE following Mantis model
  $expireOnBrowserClose=false;
  $auth_cookie_name = config_get('auth_cookie');
  $cookie_path = config_get('cookie_path');
  setcookie($auth_cookie_name,$userObj->getSecurityCookie(),$expireOnBrowserClose,
            $cookie_path);      

  // Block two sessions within one browser
  if (isset($_SESSION['currentUser']) && !is_null($_SESSION['currentUser']))
  {
    $ret['msg'] = lang_get('login_msg_session_exists1') . 
                     ' <a style="color:white;" href="logout.php">' . 
                     lang_get('logout_link') . '</a>' . lang_get('login_msg_session_exists2'); 
  }
  else
  { 
    // Setting user's session information
    $_SESSION['currentUser'] = $userObj;
    $_SESSION['lastActivity'] = time();
          
    $g_tlLogger->endTransaction();
    $g_tlLogger->startTransaction();
    setUserSessionFromObj($dbHandler,$userObj);

    $ret['status'] = tl::OK;
  }
  
  return $ret;        
}
