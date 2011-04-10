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

	// // new dBug($securityCookie);
	// if(!$redir2login)
	// {
	// 	$dbSecurityCookie = $_SESSION['currentUser']->getSecurityCookie();
	// 	$redir2login = ( $securityCookie !=	$dbSecurityCookie );	
    // 
    // 
	// 	//new dBug($dbSecurityCookie);
	// 	//die();
	// }	
}
if($redir2login)
{
	redirect(TL_BASE_HREF ."login.php?note=expired");
	exit;
}

$smarty = new TLSmarty();
$smarty->assign('title', lang_get('main_page_title'));
$smarty->assign('titleframe', 'lib/general/navBar.php');
$smarty->assign('mainframe', $args->reqURI);
$smarty->display('main.tpl');

function init_args()
{
	$iParams = array("reqURI" => array(tlInputParameter::STRING_N,0,4000));
	$pParams = G_PARAMS($iParams);
	
	$args = new stdClass();
	$args->reqURI = ($pParams["reqURI"] != '') ? $pParams["reqURI"] : 'lib/general/mainPage.php';
	
	return $args;
}
?>