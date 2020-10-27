<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource  index.php
 * @package     TestLink
 * @copyright   2006-2019, TestLink community
 * @link        http://www.testlink.org
 *
 *
**/
require_once('config.inc.php');
require_once('common.php');
require_once('configCheck.php');
checkConfiguration();


doSessionStart();

// will be very interesting understand why we do this
unset($_SESSION['basehref']);  
setPaths();
$args = initArgs();

// verify the session during a work
$redir2login = true;
if( isset($_SESSION['currentUser']) ) {
  // Session exists we need to do other checks.
  // we use/copy Mantisbt approach
  $securityCookie = tlUser::auth_get_current_user_cookie();
  $redir2login = is_null($securityCookie);

  if(!$redir2login) {
    doDBConnect($db,database::ONERROREXIT);

    $user = new tlUser();
    $user->dbID = $_SESSION['currentUser']->dbID;
    $user->readFromDB($db);
    $dbSecurityCookie = $user->getSecurityCookie();
    $redir2login = ( $securityCookie != $dbSecurityCookie );
  } 
}

if($redir2login) {
  // destroy user in session as security measure
  unset($_SESSION['currentUser']);

  // If session does not exists I think is better in order to
  // manage other type of authentication method/schemas
  // to understand that this is a sort of FIRST Access.
  //
  // When TL undertand that session exists but has expired
  // is OK to call login with expired indication, but is not this case
  //
  // Dev Notes:
  // may be we are going to login.php and it will call us again!
  $urlo = TL_BASE_HREF . "login.php" . ($args->ssodisable ? '?ssodisable' : '');
  redirect($urlo);
  exit;
}


// We arrive to these lines only if we are logged in
// 
// Calling testlinkInitPage() I'm doing what we do on navBar.php
// navBar.php is called via main.tpl
// testlinkInitPage($db,('initProject' == 'initProject'));
$gui = initGui($db,$args);

$tplEngine = new TLSmarty();
$tplEngine->assign('gui', $gui);
$tplEngine->display('../dashio/main.tpl');

/**
 *
 */
function initArgs() {
  $iParams = array(
              "reqURI" => array(tlInputParameter::STRING_N,0,4000),
              "action" => array(tlInputParameter::STRING_N,1,15),
              "activeMenu" => array(tlInputParameter::STRING_N,6,20),
              "projectView" => array(tlInputParameter::INT_N));
  
  //:q!dump($_REQUEST);

  $args = new stdClass();
  R_PARAMS($iParams,$args);
  $args->user = $_SESSION['currentUser'];
  $args->ssodisable = getSSODisable();

  // CWE-79: 
  // Improper Neutralization of Input 
  // During Web Page Generation ('Cross-site Scripting')
  // 
  // https://cxsecurity.com/issue/WLB-2019110139
  if ($args->reqURI != '') {

    // some sanity checks
    // strpos ( string $haystack , mixed $needle
    if (stripos($args->reqURI,'javascript') !== false) {
      $args->reqURI = null; 
    }
  }
  if (null == $args->reqURI) {
    $args->reqURI = 'lib/general/mainPage.php';
  }
  $args->reqURI = $_SESSION['basehref'] . $args->reqURI;


  $k2l = array('tproject_id','current_tproject_id','tplan_id'); 
  foreach($k2l as $pp) {
    $args->$pp = isset($_REQUEST[$pp]) ? intval($_REQUEST[$pp]) : 0;
  } 


  // active menu needs to be validated using white list
  $items = getFirstLevelMenuStructure();
  $args->activeMenu = trim($args->activeMenu);
  if (!isset($args->activeMenu) ) {
    $args->activeMenu = '';
  }

  $args->projectView = ($args->projectView > 0) ? 1 : 0;

  return $args;
}

/**
 *
 *
 */
function initGui(&$dbH,&$argsObj) {

  list($add2args,$gui,$tprojMgr) = initUserEnv($dbH,$argsObj);
 
  $gui->action = $argsObj->action;
  $gui->title = lang_get('main_page_title');
  $gui->navbar_height = config_get('navbar_height');

  $sso = ($argsObj->ssodisable ? '&ssodisable' : '');
  $gui->logout = 'logout.php?viewer=' . $sso;

  $gui->current_tproject_id = $argsObj->current_tproject_id;
  if( $argsObj->current_tproject_id == 0 ) {
    $gui->current_tproject_id = $argsObj->tproject_id;
  }
  
  $gui->tproject_id = $argsObj->tproject_id;
  $gui->tplan_id = $argsObj->tplan_id;

  $gui->titleframe = "lib/general/navBar.php?" . 
                     "tproject_id={$gui->tproject_id}&" .
                     "tplan_id={$gui->tplan_id}&" .
                     "updateMainPage=1" . $sso;


  $gui->mainframe = $argsObj->reqURI;
  if( strpos($gui->mainframe,'?') !== FALSE ) {
    $gui->mainframe .= "&";
  } else {
    $gui->mainframe .= "?";    
  }

  // 20201022
  $activateMenu = "";
  if ($argsObj->activeMenu != "") {
    $activateMenu = "&activeMenu=$argsObj->activeMenu";
  }
  $projView = "&projectView=$argsObj->projectView";

  $gui->mainframe .= "tproject_id={$gui->tproject_id}&" .
                     "tplan_id={$gui->tplan_id}{$activateMenu}{$projView}";


  return $gui;
}