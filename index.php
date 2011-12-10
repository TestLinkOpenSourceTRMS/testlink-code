<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource index.php
 * @package TestLink
 * @copyright 2006-2011, TestLink community
 * @link http://www.teamst.org/index.php*
 *
 * @internal revisions
 * @since 1.9.4
 * 20111210 - franciscom -	TICKET 4711: Apache Webserver - SSL Client Certificate Authentication (Single Sign-on?) 
 *							minor change needed when $redir2login == true
 *	
 * 20110813 - franciscom - 	TICKET 4342: Security problem with multiple Testlink installations on the same server
 *
**/
require_once('lib/functions/configCheck.php');
checkConfiguration();
require_once('config.inc.php');
require_once('common.php');
doSessionStart();

unset($_SESSION['basehref']);  // will be very interesting understand why we do this
setPaths();
$args = init_args();

//verify the session during a work
$redir2login = true;
if( isset($_SESSION['currentUser']) )
{
	// Session exists we need to do other checks.
	//
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
		$redir2login = ( $securityCookie !=	$dbSecurityCookie );
	}	
}

if($redir2login)
{
	// destroy user in session as security measure
	unset($_SESSION['currentUser']);

	// 20111120 - franciscom
	// redirect(TL_BASE_HREF ."login.php?note=expired");
	//
	// If session does not exists I think is better in order to
	// manage other type of authentication method/schemas
	// to understand that this is a sort of FIRST Access.
	//
	// When TL undertand that session existed but has expired
	// is OK to call login with expired indication, but is not this case
	redirect(TL_BASE_HREF ."login.php");
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