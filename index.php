<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource  index.php
 * @package     TestLink
 * @copyright   2006-2017, TestLink community
 * @link        http://www.testlink.org
 *
 *
**/
require_once('lib/functions/configCheck.php');
checkConfiguration();
require_once('config.inc.php');
require_once('common.php');
doSessionStart();

// will be very interesting understand why we do this
unset($_SESSION['basehref']);  
setPaths();
list($args,$gui) = initEnv();

// verify the session during a work
$redir2login = true;
if( isset($_SESSION['currentUser']) )
{
  // Session exists we need to do other checks.
  // we use/copy Mantisbt approach
  $securityCookie = tlUser::auth_get_current_user_cookie();
  $redir2login = is_null($securityCookie);

  if(!$redir2login)
  {
    // need to get fresh info from db, before asking for securityCookie
    doDBConnect($db,database::ONERROREXIT);
    $user = new tlUser();
    $user->dbID = $_SESSION['currentUser']->dbID;
    $user->readFromDB($db);
    $dbSecurityCookie = $user->getSecurityCookie();
    $redir2login = ( $securityCookie != $dbSecurityCookie );
  } 
}

if($redir2login)
{
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

$tplEngine = new TLSmarty();
$tplEngine->assign('gui', $gui);
$tplEngine->display('main.tpl');


/**
 *
 *
 */
function initEnv()
{
  $iParams = array("reqURI" => array(tlInputParameter::STRING_N,0,4000));
  $pParams = G_PARAMS($iParams);
  
  $args = new stdClass();
  $args->ssodisable = getSSODisable();
  $args->reqURI = ($pParams["reqURI"] != '') ? $pParams["reqURI"] : 'lib/general/mainPage.php';
  $args->tproject_id = isset($_REQUEST['tproject_id']) ? intval($_REQUEST['tproject_id']) : 0;
  $args->tplan_id = isset($_REQUEST['tplan_id']) ? intval($_REQUEST['tplan_id']) : 0;

  $gui = new stdClass();
  $gui->title = lang_get('main_page_title');
  $gui->mainframe = $args->reqURI;
  $gui->navbar_height = config_get('navbar_height');

  $sso = ($args->ssodisable ? '&ssodisable' : '');
  $gui->titleframe = "lib/general/navBar.php?" . 
                     "tproject_id={$args->tproject_id}&" .
                     "tplan_id={$args->tplan_id}&" .
                     "updateMainPage=1" . $sso;
  $gui->logout = 'logout.php?viewer=' . $sso;

  return array($args,$gui);
}