<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: login.php,v $
 *
 * @version $Revision: 1.21 $
 * @modified $Date: 2007/03/12 21:46:16 $ by $Author: schlundus $
 * @author Martin Havlat
 * 
 * Login management
 *
 * rev :
 *       20070301 - franciscom - BUGID 695 (fawel contribute)
 *
 **/
require_once('lib/functions/configCheck.php');
checkConfiguration();

require('config.inc.php');
require_once('lib/functions/common.php');
require_once('lib/functions/users.inc.php');

tLog('Login page requested by ' . $_SERVER['REMOTE_ADDR'], 'INFO');
$op = doDBConnect($db);
if (!$op['status'])
{
	$smarty = new TLSmarty();
	$smarty->assign('title', lang_get('fatal_page_title'));
	$smarty->assign('content', $op['dbms_msg']);
	$smarty->display('workAreaSimple.tpl'); 
	tLog('Connection fail page shown.'); 
	exit();
}



$_GET = strings_stripSlashes($_GET);
$note = isset($_GET['note']) ? $_GET['note'] : null;

$message = lang_get('please_login');
// assign a comment for login
switch($note)
{
	case 'expired':
	  if( !isset($_SESSION) )
	  { 
		  session_start();
		}
		session_unset();
		session_destroy();
		// 20070110 - MHT - removed note because it confuses in some cases 	
		// $message = lang_get('session_expired');
		break;

	case 'wrong':
		$message = lang_get('bad_user_passwd');
		break;

	case 'first':
		$message = lang_get('your_first_login');
		break;

	case 'lost':
		$message = lang_get('passwd_lost');
		break;

	case 'sessionExists':
		$message = lang_get('login_msg_session_exists1') . ' <a style="color:black;" href="logout.php">' . 
				   lang_get('logout_link') . '</a>' . lang_get('login_msg_session_exists2');
		break;
	default:
		break;
}

$securityNotes = getSecurityNotes($db);

$smarty = new TLSmarty();

// 20070301 - BUGID 695
$smarty->assign('g_user_self_signup', config_get('user_self_signup'));

$smarty->assign('login_logo', LOGO_LOGIN_PAGE);
$smarty->assign('securityNotes',$securityNotes);
$smarty->assign('note',$message);
$smarty->assign('css', TL_BASE_HREF . TL_LOGIN_CSS);
$smarty->display('login.tpl');
?>
