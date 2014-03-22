<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Login page with configuratin checking and authorization
 *
 * @filesource  login.php
 * @package     TestLink
 * @author      Martin Havlat
 * @copyright   2006,2014 TestLink community 
 * @link        http://www.testlink.org
 * 
 * @internal revisions
 * @since 1.9.10
 *              
 **/

require_once('lib/functions/configCheck.php');
checkConfiguration();
require_once('config.inc.php');
require_once('common.php');
require_once('doAuthorize.php');

$templateCfg = templateConfiguration();
$doRenderLoginScreen = false;
$doAuthPostProcess = false;

doDBConnect($db, database::ONERROREXIT);
$args = init_args();
$gui = init_gui($db,$args);

// if these checks fail => we will redirect to login screen with some message
doBlockingChecks($db,$gui);

switch($args->action) 
{
  case 'doLogin':
  case 'ajaxlogin':
    doSessionStart(true);
     
    // When doing ajax login we need to skip control regarding session already open
    // that we use when doing normal login.
    // If we do not proceed this way we will enter an infinite loop
    $options = array('doSessionExistsCheck' => ($args->action=='doLogin'));
    $op = doAuthorize($db,$args->login,$args->pwd,$options);
    $doAuthPostProcess = true;
  break;

  case 'ajaxcheck':
    processAjaxCheck($db);
  break;
  
  case 'loginform':
    $doRenderLoginScreen = true;
    // unfortunatelly we use $args->note in order to do some logic.
    if( (trim($args->note) == "") &&
        $gui->authCfg['SSO_enabled'] && $gui->authCfg['SSO_method'] == 'CLIENT_CERTIFICATE')
    {
      doSessionStart(true);
      $op = doSSOClientCertificate($db,$_SERVER,$gui->authCfg);
      $doAuthPostProcess = true;
    }
  break;
}


if( $doAuthPostProcess ) 
{
  list($doRenderLoginScreen,$gui->note) = authorizePostProcessing($args,$op);
}

if( $doRenderLoginScreen ) 
{
  renderLoginScreen($gui);
}



/**
 * 
 *
 */
function init_args()
{
  // 2010904 - eloff - Why is req and reqURI parameters to the login?
  $iParams = array("note" => array(tlInputParameter::STRING_N,0,255),
                   "tl_login" => array(tlInputParameter::STRING_N,0,30),
                   "tl_password" => array(tlInputParameter::STRING_N,0,32),
                   "req" => array(tlInputParameter::STRING_N,0,4000),
                   "reqURI" => array(tlInputParameter::STRING_N,0,4000),
                   "action" => array(tlInputParameter::STRING_N,0, 10),
                   "destination" => array(tlInputParameter::STRING_N, 0, 255),
                   "loginform_token" => array(tlInputParameter::STRING_N, 0, 255)
  );
  $pParams = R_PARAMS($iParams);

  $args = new stdClass();
  $args->note = $pParams['note'];
  $args->login = $pParams['tl_login'];
  $args->pwd = $pParams['tl_password'];
  $args->reqURI = urlencode($pParams['req']);
  $args->preqURI = urlencode($pParams['reqURI']);
  $args->destination = urldecode($pParams['destination']);
  $args->loginform_token = urldecode($pParams['loginform_token']);

  if ($pParams['action'] == 'ajaxcheck' || $pParams['action'] == 'ajaxlogin') 
  {
    $args->action = $pParams['action'];
  } 
  else if (!is_null($args->login)) 
  {
    $args->action = 'doLogin';
  } 
  else 
  {
    $args->action = 'loginform';
  }
  return $args;
}

/**
 * 
 *
 */
function init_gui(&$db,$args)
{
  $gui = new stdClass();
  
  $gui->authCfg = config_get('authentication');
  $gui->user_self_signup = config_get('user_self_signup');
  $gui->securityNotes = getSecurityNotes($db);
  $gui->external_password_mgmt = false;
  $gui->login_disabled = (('LDAP' == $gui->authCfg['method']) && !checkForLDAPExtension()) ? 1 : 0;

  switch($args->note)
  {
    case 'expired':
      if(!isset($_SESSION))
      {
        session_start();
      }
      session_unset();
      session_destroy();
      $gui->note = lang_get('session_expired');
      $gui->reqURI = null;
    break;
        
    case 'first':
      $gui->note = lang_get('your_first_login');
      $gui->reqURI = null;
    break;
        
    case 'lost':
      $gui->note = lang_get('passwd_lost');
      $gui->reqURI = null;
    break;
        
    default:
      $gui->note = lang_get('please_login');
    break;
  }
  $gui->reqURI = $args->reqURI ? $args->reqURI : $args->preqURI;
  $gui->destination = $args->destination;
  
  return $gui;
}


/**
 * doBlockingChecks
 *
 * wrong Schema version will BLOCK ANY login action
 *
 * @param &$dbHandler DataBase Handler
 * @param &$guiObj some gui elements that will be used to give feedback
 *  
 */
function doBlockingChecks(&$dbHandler,&$guiObj)
{
  $op = checkSchemaVersion($dbHandler);
  if( $op['status'] < tl::OK ) 
  {
    // Houston we have a problem
    // This check to kill session was added to avoid following situation
    // TestLink 1.9.5 installed
    // Install TestLink 1.9.6 in another folder, pointing to same OLD DB
    // you logged in TL 1.9.5 => session is created
    // you try to login to 1.9.6, you get the Update DB Schema message but
    // anyway because a LIVE AND VALID session you are allowed to login => BAD
    if(isset($op['kill_session']) && $op['kill_session'])
    {
      session_unset();
      session_destroy();
    } 
    $guiObj->note = $op['msg'];
    renderLoginScreen($guiObj);
  }
}


/**
 * renderLoginScreen
 * simple piece of code used to clean up code layout
 * 
 * @global  $g_tlLogger
 * @param stdClassObject $guiObj
 */
function renderLoginScreen($guiObj)
{
  global $g_tlLogger; 
  $templateCfg = templateConfiguration();
  $logPeriodToDelete = config_get('removeEventsOlderThan');
  $g_tlLogger->deleteEventsFor(null, strtotime("-{$logPeriodToDelete} days UTC"));
  
  $smarty = new TLSmarty();
  $smarty->assign('gui', $guiObj);
  $smarty->display($templateCfg->default_template);
}


/**
 * 
 * @param stdClassObject $argsObj
 * @param hash $op
 */
function authorizePostProcessing($argsObj,$op)
{
  $note = null;
  $renderLoginScreen = false;
  if($op['status'] == tl::OK)
  {
    // Login successful, redirect to destination
    logAuditEvent(TLS("audit_login_succeeded",$argsObj->login,
                  $_SERVER['REMOTE_ADDR']),"LOGIN",$_SESSION['currentUser']->dbID,"users");
    
    if ($argsObj->action == 'ajaxlogin') 
    {
      echo json_encode(array('success' => true));
    } 
    else 
    {
      // If destination param is set redirect to given page ...
      if (!empty($argsObj->destination) && preg_match("/linkto.php/", $argsObj->destination)) 
      {
        redirect($argsObj->destination);
      }
      else
      {
        // ... or show main page
        redirect($_SESSION['basehref'] . "index.php?caller=login" . 
            ($argsObj->preqURI ? "&reqURI=".urlencode($argsObj->preqURI) :""));
      
      }
      exit(); // hmm seems is useless
    }
  }
  else
  {
    $note = is_null($op['msg']) ? lang_get('bad_user_passwd') : $op['msg'];
    if($argsObj->action == 'ajaxlogin') 
    {
      echo json_encode(array('success' => false,'reason' => $note));
    }
    else
    {
      $renderLoginScreen = true;
    }
  }
  
  return array($renderLoginScreen,$note);
}

/**
 * 
 *
 */
function processAjaxCheck(&$dbHandler)
{
   // Send a json reply, include localized strings for use in js to display a login form.
   doSessionStart(true);
   echo json_encode(array('validSession' => checkSessionValid($dbHandler, false),
                        'username_label' => lang_get('login_name'),
                        'password_label' => lang_get('password'),
                        'login_label' => lang_get('btn_login'),
                          'timeout_info' => lang_get('timeout_info')));

}
?>