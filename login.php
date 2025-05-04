<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Login page with configuratin checking and authorization
 *
 * @filesource  login.php
 * @package     TestLink
 * @copyright   2006,2020 TestLink community 
 * @link        http://www.testlink.org
 * 
 **/
require_once('lib/functions/configCheck.php');
checkConfiguration();
require_once('config.inc.php');
require_once('common.php');
require_once('oauth_api.php');
require_once('doAuthorize.php');

$templateCfg = templateConfiguration();
$doRenderLoginScreen = false;
$doAuthPostProcess = false;


doDBConnect($db, database::ONERROREXIT);
$args = init_args();
$gui = init_gui($db,$args);


// if these checks fail => we will redirect to login screen with some message
doBlockingChecks($db,$gui);

switch($args->action) {
  case 'doLogin':
  case 'ajaxlogin':
    doSessionStart(true);
     
    // When doing ajax login we need to skip control regarding session already open
    // that we use when doing normal login.
    // If we do not proceed this way we will enter an infinite loop
    $options = new stdClass();
    $options->doSessionExistsCheck = ($args->action =='doLogin');
    $op = doAuthorize($db,$args->login,$args->pwd,$options);
    $doAuthPostProcess = true;
    $gui->draw = true;
  break;

  case 'ajaxcheck':
    processAjaxCheck($db);
  break;


  case 'oauth':
    // If code is empty then break
    if (!isset($args->oauth_code)){
        renderLoginScreen($gui);
        die();
    }

    // Switch between oauth providers
    // validate providers
    $includeOK = false;
    $oauth_params = getOAuthProviderCfg($args->oauth_name);
    if ($oauth_params != null) {
      $g2i = $args->oauth_name . '.php';
      if (!include_once($g2i)) {
        die("Oauth client doesn't exist");
      } else {
        $includeOK = true;
      }             
    }

    // No good!
    if ($includeOK == false) {
      renderLoginScreen($gui);
      die();      
    }
   
    $user_token = oauth_get_token($oauth_params, $args->oauth_code);
    if($user_token->status['status'] == tl::OK) {
      doSessionStart(true);
      $op = doAuthorize($db,$user_token->options->user,'oauth',$user_token->options);
      $doAuthPostProcess = true;
    } else {
    	$gui->note = $user_token->status['msg'];
    	$gui->draw=true;    
      renderLoginScreen($gui);
      die();
    }
  break;

  case 'loginform':
    $doRenderLoginScreen = true;
    $gui->draw = true;
    $op = null;

    // unfortunatelly we use $args->note in order to do some logic.
    if( ($args->note=trim($args->note)) == "" ) {
      if( $gui->authCfg['SSO_enabled'] ) {
        doSessionStart(true);
        $doAuthPostProcess = true;
        
        switch ($gui->authCfg['SSO_method']) {
          case 'CLIENT_CERTIFICATE':
            $op = doSSOClientCertificate($db,$_SERVER,$gui->authCfg);
          break;
          
          case 'WEBSERVER_VAR':
            //DEBUGsyslogOnCloud('Trying to execute SSO using SAML');
            $op = doSSOWebServerVar($db,$gui->authCfg);
          break;
        }
      }
    }
  break;
}

if( $doAuthPostProcess ) {
  list($doRenderLoginScreen,$gui->note) = authorizePostProcessing($args,$op);
}

if( $doRenderLoginScreen ) {
  renderLoginScreen($gui);
}

/**
 * 
 *
 */
function init_args() {
  $pwdInputLen = config_get('loginPagePasswordMaxLenght');

  // 2010904 - eloff - Why is req and reqURI parameters to the login?
  $iParams = array("note" => array(tlInputParameter::STRING_N,0,255),
                   "tl_login" => array(tlInputParameter::STRING_N,0,100),
                   "tl_password" => array(tlInputParameter::STRING_N,0,$pwdInputLen),
                   "req" => array(tlInputParameter::STRING_N,0,4000),
                   "reqURI" => array(tlInputParameter::STRING_N,0,4000),
                   "action" => array(tlInputParameter::STRING_N,0, 10),
                   "destination" => array(tlInputParameter::STRING_N, 0, 255),
                   "loginform_token" => array(tlInputParameter::STRING_N, 0, 255),
                   "viewer" => array(tlInputParameter::STRING_N, 0, 3),
                   "oauth" => array(tlInputParameter::STRING_N,0,100),
                   "code" => array(tlInputParameter::STRING_N,0,4000),
                   "state" => array(tlInputParameter::STRING_N,0,100),
                  );
  $pParams = R_PARAMS($iParams);

  $args = new stdClass();
  $args->note = $pParams['note'];
  $args->login = $pParams['tl_login'];

  $args->pwd = $pParams['tl_password'];
  $args->ssodisable = getSSODisable();
  $args->reqURI = urlencode($pParams['req']);
  $args->preqURI = urlencode($pParams['reqURI']);
  $args->destination = urldecode($pParams['destination']);
  $args->loginform_token = urldecode($pParams['loginform_token']);

  // $args->viewer = $pParams['viewer']; 
  $args->viewer = '';

  $k2c = array('ajaxcheck' => 'do','ajaxlogin' => 'do');
  if (isset($k2c[$pParams['action']]))  {
    $args->action = $pParams['action'];
  } else if (!is_null($args->login)) {
    $args->action = 'doLogin';
  // This 'if' branch may be removed in later versions. Kept for compatibility    
  } else if (!is_null($pParams['oauth']) && $pParams['oauth']) {
    $args->action = 'oauth';
    $args->oauth_name = $pParams['oauth'];
    $args->oauth_code = $pParams['code'];
  } else if (!is_null($pParams['state']) && !is_null($pParams['code'])) {
   
    // We use state to undertand the provider when the redirect url
    // can not have query string, as happens with Microsoft
    // state will be 'testlink provider id'$$$state(random string)
    //
    // read https://auth0.com/docs/protocols/oauth2/oauth-state
    //
    $args->action = 'oauth';
    $args->oauth_name = explode('$$$',$pParams['state']);
    $args->oauth_name = $args->oauth_name[0];
    $args->oauth_code = $pParams['code'];
  } else {
    $args->action = 'loginform';
  }

  // whitelist oauth_name
  if (strcasecmp($args->action,'oauth') == 0) {
    validateOauth($args->oauth_name);
  }

  return $args;
}

/**
 *
 */
function validateOauth($name) {
  $name = trim($name);
  $oauthServers = config_get('OAuthServers');
  $whitelistOK = false;
  foreach ($oauthServers as $serverCfg) {
    if (strcasecmp($name, $serverCfg['oauth_name']) == 0) {
      $whitelistOK = true;
      break;
    }
  }

  if ($whitelistOK == false) {
    die("Invalid Oauth Service");
  } 
}

/**
 * 
 *
 */
function init_gui(&$db,$args) {
  $gui = new stdClass();
  $gui->viewer = $args->viewer;

  $secCfg = config_get('config_check_warning_frequence');
  $gui->securityNotes = '';
  if( (strcmp($secCfg, 'ALWAYS') == 0) || 
      (strcmp($secCfg, 'ONCE_FOR_SESSION') == 0 && !isset($_SESSION['getSecurityNotesDone'])) ) {
    $_SESSION['getSecurityNotesDone'] = 1;
    $gui->securityNotes = getSecurityNotes($db);
  }  

  $gui->authCfg = config_get('authentication');
  $gui->user_self_signup = config_get('user_self_signup');

  // Oauth buttons
  $oau = config_get('OAuthServers');
  $gui->oauth = array();
  foreach ($oau as $oauth_prov) {
    if ($oauth_prov['oauth_enabled']) {
        $name = $oauth_prov['oauth_name'];
        $gui->oauth[$name] = new stdClass();
        $gui->oauth[$name]->name = ucfirst($name);
        $gui->oauth[$name]->link = oauth_link($oauth_prov);
        $gui->oauth[$name]->icon = $name . '.png';
    }
  }

  if (isset($gui->authCfg['sso_only']) && $gui->authCfg['sso_only']) {
    $gui->external_password_mgmt = true;
  } else {
    $gui->external_password_mgmt = false;
    $domain = $gui->authCfg['domain'];
    $mm = $gui->authCfg['method'];
    if( isset($domain[$mm]) ) {
      $ac = $domain[$mm];
      $gui->external_password_mgmt = !$ac['allowPasswordManagement'];
    }
  }

  $gui->login_disabled = (('LDAP' == $gui->authCfg['method']) && !checkForLDAPExtension()) ? 1 : 0;

  switch($args->note) {
    case 'expired':
      if(!isset($_SESSION)) {
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
      $gui->note = '';
    break;
  }

  $gui->ssodisable = 0;
  if(property_exists($args,'ssodisable')) {
    $gui->ssodisable = $args->ssodisable;
  }  

  $gui->reqURI = $args->reqURI ? $args->reqURI : $args->preqURI;
  $gui->destination = $args->destination;
  $gui->pwdInputMaxLenght = config_get('loginPagePasswordMaxLenght');
  

  // Random Background
  $imgSet = array();
  $imgSet[] = "wp-testing04.jpg";
  $imgSet[] = "Fedora-24-Default-Wallpaper-1.png";
  $imgSet[] = "fedora-76343.jpg";
  $imgSet[] = "fedora-hex_0.002.png";
  $imgSet[] = "fedora-x.jpeg";
  $imgSet[] = "fedora21.png-1024x640.jpg";
  $imgSet[] = "fedora28.png";

  $itemQty = count($imgSet)-1;
  $ixx = rand(0,$itemQty);

  $gui->loginBackgroundImg = 
    "gui/templates/dashio/img/login/" . $imgSet[$ixx]; 
  
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
function doBlockingChecks(&$dbHandler,&$guiObj) {
  $op = checkSchemaVersion($dbHandler);
  if( $op['status'] < tl::OK ) {
    // Houston we have a problem
    // This check to kill session was added to avoid following situation
    // TestLink 1.9.5 installed
    // Install TestLink 1.9.6 in another folder, pointing to same OLD DB
    // you logged in TL 1.9.5 => session is created
    // you try to login to 1.9.6, you get the Update DB Schema message but
    // anyway because a LIVE AND VALID session you are allowed to login => BAD
    if(isset($op['kill_session']) && $op['kill_session']) {
      session_unset();
      session_destroy();
    } 

    $guiObj->draw = false;
    $guiObj->note = $op['msg'];
    renderLoginScreen($guiObj);
    die();
  }
}


/**
 * renderLoginScreen
 * simple piece of code used to clean up code layout
 * 
 * @global  $g_tlLogger
 * @param stdClassObject $guiObj
 */
function renderLoginScreen($guiObj) {
  global $g_tlLogger; 
  $templateCfg = templateConfiguration();
  $logPeriodToDelete = config_get('removeEventsOlderThan');
  $g_tlLogger->deleteEventsFor(null, strtotime("-{$logPeriodToDelete} days UTC"));
  
  $smarty = new TLSmarty();
  $smarty->assign('gui', $guiObj);

  $templ = config_get('tpl');
  $tpl = $templ['login'];
  
  $smarty->display($tpl);
}


/**
 * 
 * @param stdClassObject $argsObj
 * @param hash $op
 */
function authorizePostProcessing($argsObj,$op) {
  $note = null;
  $renderLoginScreen = false;
  if($op['status'] == tl::OK) {
    // Login successful, redirect to destination
    logAuditEvent(TLS("audit_login_succeeded",$argsObj->login,
                  $_SERVER['REMOTE_ADDR']),"LOGIN",$_SESSION['currentUser']->dbID,"users");
    
    if ($argsObj->action == 'ajaxlogin') {
      echo json_encode(array('success' => true));
    } else {
      // If destination param is set redirect to given page ...
      if ( !empty($argsObj->destination) ) {

        // 1) remove host.port from TL_BASE_HREF -> base_folder
        // https://hsgdshdjs:80/bsbsbb
        // http://fjljfld:8080/Hhhhs
        // http://hjhsjdhshdk/
        $baseURL = str_replace('://',':',TL_BASE_HREF);	
        $basePieces = explode(':',TL_BASE_HREF);
        $howManyPieces = count($basePieces); 
        switch ($howManyPieces) {
        	case 2:
          case 3:
        	break;
        	default:
            echo 'Security Check Failure';
        	  die();
        	break;  
        }

        // http:  hjhsjdhshdk/  
        // http:  hjhsjdhshdk/base_folder
        // https: hsgdshdjs: >> 80/bsbsbb
        // http:  fjljfld:   >> 8080/Hhhhs
        $dummy = explode('/',$basePieces[$howManyPieces-1]);
        $baseFolder = '/';
        $compo = trim($dummy[1]);
        if ($compo != '') {
          $baseFolder .= $compo . '/';
        }

        // 2) check base_folder/linkto.php
        $where = strpos($argsObj->destination, $baseFolder . 'linkto.php');
        $checkOK = ($where !== false) && ($where == 0);
        if ($checkOK == false) {
          echo 'Security Check Failure';
          die();
        }

        // 3) validate content after linkto.php?
        $dummy = explode($baseFolder . 'linkto.php?');
        $afterLinkTo = $baseFolder . 'linkto.php?' . cleanInput($dummy[1]);
        redirect($afterLinkTo);
      } else {
        // ... or show main page
        $_SESSION['viewer'] = $argsObj->viewer;
        $ad = $argsObj->ssodisable ? '&ssodisable=1' : '';
        $ad .= ($argsObj->preqURI ? "&reqURI=".urlencode($argsObj->preqURI) :"");

        $rul = $_SESSION['basehref'] . 
                 "index.php?caller=login&viewer={$argsObj->viewer}" . $ad;
        
        redirect($rul);
      }
      exit(); // hmm seems is useless
    }
  } else {
    $note = '';
    if(!$argsObj->ssodisable) {
      $note = is_null($op['msg']) ? lang_get('bad_user_passwd') : $op['msg'];
    } 

    if($argsObj->action == 'ajaxlogin') {
      echo json_encode(array('success' => false,'reason' => $note));
    } else {
      $renderLoginScreen = true;
    }
  }
  
  return array($renderLoginScreen,$note);
}

/**
 * 
 *
 */
function processAjaxCheck(&$dbHandler) {
   // Send a json reply, include localized strings for use in js to display a login form.
   doSessionStart(true);
   echo json_encode(array('validSession' => checkSessionValid($dbHandler, false),
                        'username_label' => lang_get('login_name'),
                        'password_label' => lang_get('password'),
                        'login_label' => lang_get('btn_login'),
                          'timeout_info' => lang_get('timeout_info')));

}


// from https://css-tricks.com/snippets/php/sanitize-database-inputs/
function cleanInput($input) {
 
  $search = array(
    '@<script[^>]*?>.*?</script>@si',   // Strip out javascript
    '@<[\/\!]*?[^<>]*?>@si',            // Strip out HTML tags
    '@<style[^>]*?>.*?</style>@siU',    // Strip style tags properly
    '@<![\s\S]*?--[ \t\n\r]*>@'         // Strip multi-line comments
  );
 
    $output = preg_replace($search, '', $input);
    return $output;
}