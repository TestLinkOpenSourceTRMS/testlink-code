<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: login.php,v $
 *
 * @version $Revision: 1.43 $
 * @modified $Date: 2008/10/18 16:10:11 $ by $Author: franciscom $
 * @author Martin Havlat
 * 
 * Login management
 *
 * rev: 20081015 - franciscom - access to config parameters following development standard
 **/
require_once('lib/functions/configCheck.php');
checkConfiguration();
require_once('config.inc.php');
require_once('common.php');
require_once('doAuthorize.php');

$op = doDBConnect($db);
if (!$op['status'])
{
	$smarty = new TLSmarty();
	$smarty->assign('title', lang_get('fatal_page_title'));
	$smarty->assign('content', $op['dbms_msg']);
	$smarty->display('workAreaSimple.tpl'); 
	tLog('Connection fail page shown.','ERROR'); 
	exit();
}
$_GET = strings_stripSlashes($_GET);
$note = isset($_GET['note']) ? $_GET['note'] : null;
$reqURI = isset($_GET['req']) ? $_GET['req'] : null;
$_POST = strings_stripSlashes($_POST);
$login = isset($_POST['tl_login']) ? $_POST['tl_login'] : null;
$pwd = isset($_POST['tl_password']) ? $_POST['tl_password'] : null;
$preqURI = (isset($_POST['reqURI']) && strlen($_POST['reqURI'])) ? $_POST['reqURI'] : null;

switch($note)
{
	case 'expired':
		if(!isset($_SESSION))
			session_start();
		session_unset();
		session_destroy();
		$note = lang_get('session_expired');
		$reqURI = null;
		break;
	case 'first':
		$note = lang_get('your_first_login');
		$reqURI = null;
		break;
	case 'lost':
		$note = lang_get('passwd_lost');
		$reqURI = null;
		break;
	default:
		$note = lang_get('please_login');
		break;
}
if (!is_null($login))
{
	doSessionStart();
	unset($_SESSION['basehref']);
	setPaths();
	if (doAuthorize($db,$login,$pwd,$msg) < tl::OK)
	{
		if (!$msg)
			$note = lang_get('bad_user_passwd');
		else
			$note = $msg;
	}
	else
	{
		logAuditEvent(TLS("audit_login_succeeded",$login,$_SERVER['REMOTE_ADDR']),"LOGIN",$_SESSION['currentUser']->dbID,"users");
		redirect($_SESSION['basehref']."index.php".($preqURI ? "?reqURI=".urlencode($preqURI) :""));
		exit();
	}
}

$securityNotes = getSecurityNotes($db);
$login_method = config_get('login_method');
$external_password_mgmt = ('LDAP' == $login_method) ? 1 : 0;
$login_disabled=($external_password_mgmt && !checkForLDAPExtension()) ? 1:0;

// do not access config parameters in direct way USE ALWAYS config_get();
$logPeriodToDelete=config_get('removeEventsOlderThan');
$g_tlLogger->deleteEventsFor(null, strtotime("-{$logPeriodToDelete} days UTC"));

$smarty = new TLSmarty();
$smarty->assign('g_user_self_signup', config_get('user_self_signup'));
$smarty->assign('securityNotes',$securityNotes);
$smarty->assign('note',$note);
$smarty->assign('reqURI',$reqURI ? $reqURI : $preqURI);
$smarty->assign('login_disabled', $login_disabled);
$smarty->assign('external_password_mgmt', $external_password_mgmt);
$smarty->display('login.tpl');
?>
