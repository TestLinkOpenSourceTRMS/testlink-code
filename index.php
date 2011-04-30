<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Main window. Include authorization of user and define frames (navBar and main).
 *
 * @filesource	index.php
 * @package 	TestLink
 * @copyright 	2006-2011, TestLink community 
 * @link 		http://www.teamst.org/index.php
 *
 * @internal revisions
 * 20110410 - franciscom - BUGID 4342
**/
require_once('lib/functions/configCheck.php');
checkConfiguration();
require_once('config.inc.php');
require_once('common.php');
doSessionStart();

unset($_SESSION['basehref']);
setPaths();
$args = init_args();

// verify the session during a work
$redir2login = true;

if( isset($_SESSION['currentUser']) )
{
	// use Mantisbt approach
	$securityCookie = tlUser::auth_get_current_user_cookie();
	$redir2login = is_null($securityCookie);
	if(!$redir2login)
	{
		// need to get fresh info from db,
		// before asking for securityCookie
		doDBConnect($db);
		$user = new tlUser();
		$user->dbID = $_SESSION['currentUser']->dbID;
		$user->readFromDB($db);
		
		$dbSecurityCookie = $user->getSecurityCookie();
		$redir2login = ( $securityCookie !=	$dbSecurityCookie );	
		
	}	
}
if($redir2login)
{
	// destroy user in session as security measure
	unset($_SESSION['currentUser']);
	redirect(TL_BASE_HREF ."login.php?note=expired");
	exit;
}

$smarty = new TLSmarty();
$smarty->assign('title', lang_get('main_page_title'));
$smarty->assign('titleframe', "lib/general/navBar.php?tproject_id={$args->tproject_id}&updateMainPage=1");
// $smarty->assign('titleframe', "lib/general/navBar.php?tproject_id={$args->tproject_id}");
$smarty->assign('mainframe', $args->reqURI);
$smarty->display('main.tpl');



function init_args()
{
	$_REQUEST=strings_stripSlashes($_REQUEST);
	$args = new stdClass();
	
	$iParams = array("reqURI" => array(tlInputParameter::STRING_N,0,4000));
	$pParams = G_PARAMS($iParams);
	$args->reqURI = ($pParams["reqURI"] != '') ? $pParams["reqURI"] : 'lib/general/mainPage.php';
	$args->tproject_id = isset($_REQUEST['tproject_id']) ? intval($_REQUEST['tproject_id']) : 0;
	return $args;
}
?>