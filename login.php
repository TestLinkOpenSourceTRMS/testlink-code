<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: login.php,v $
 *
 * @version $Revision: 1.33 $
 * @modified $Date: 2008/01/18 20:40:17 $ by $Author: schlundus $
 * @author Martin Havlat
 * 
 * Login management
 *
 * rev :
 *       20070831 - franciscom - color change to make more visible Logout link
 *       20070818 - franciscom - BUGID xxxx (di LDAP) 
 *       20070301 - franciscom - BUGID 695 (fawel contribute)
 *
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
$login = isset($_POST['login']) ? $_POST['login'] : null;
$pwd = isset($_POST['password']) ? $_POST['password'] : null;
$preqURI = (isset($_POST['reqURI']) && strlen($_POST['reqURI'])) ? $_POST['reqURI'] : null;

$message = lang_get('please_login');
// assign a comment for login
switch($note)
{
	case 'expired':
		if(!isset($_SESSION))
			session_start();
		session_unset();
		session_destroy();
		break;
	case 'first':
		$message = lang_get('your_first_login');
		break;
	case 'lost':
		$message = lang_get('passwd_lost');
		break;
	case 'sessionExists':
		$message = lang_get('login_msg_session_exists1') . ' <a style="color:white;" href="logout.php">' . 
 				   lang_get('logout_link') . '</a>' . lang_get('login_msg_session_exists2');
		break;
	default:
		break;
}
if (!is_null($login))
{
	doSessionStart();
	unset($_SESSION['basehref']);
	setPaths();
	if (doAuthorize($db,$login,$pwd,$msg) < tl::OK)
	{
		tLog(TLS("audit_login_failed",$login),'AUDIT',null,null,"users");
		$message = lang_get('bad_user_passwd');
	}
	else
	{
		tLog(TLS("audit_login_succeeded",$login),'AUDIT',null,$_SESSION['currentUser']->dbID,"users");
		redirect($_SESSION['basehref']."index.php".($preqURI ? "?reqURI=".urlencode($preqURI) :""));
		exit();
	}
}

$securityNotes = getSecurityNotes($db);
$bLDAPEnabled = false;

$smarty = new TLSmarty();
$smarty->assign('g_user_self_signup', config_get('user_self_signup'));
$smarty->assign('login_logo', LOGO_LOGIN_PAGE);
$smarty->assign('securityNotes',$securityNotes);
$smarty->assign('note',$message);
$smarty->assign('reqURI',$reqURI ? $reqURI : $preqURI);
$smarty->assign('css', TL_BASE_HREF . TL_LOGIN_CSS);
$smarty->assign('login_disabled', !checkForLDAPExtension($bLDAPEnabled));
$smarty->assign('external_password_mgmt', $bLDAPEnabled);
$smarty->display('login.tpl');
?>
