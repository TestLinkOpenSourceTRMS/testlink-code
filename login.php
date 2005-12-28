<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: login.php,v $
 *
 * @version $Revision: 1.13 $
 * @modified $Date: 2005/12/28 07:34:54 $ by $Author: franciscom $
 *
 * @author Martin Havlat
 * 
 * The page allows adjust login data
 * 
 * 20050831 - scs - cosmetic changes
 * 200508 - MHT - added config check
 **/
global $db; 
require_once('lib/functions/configCheck.php');
checkConfiguration();

require('config.inc.php');
require_once('lib/functions/common.php');
require_once('lib/functions/users.inc.php');

$op = doDBConnect($db);
if (!$op['status'])
{
	$smarty = new TLSmarty();
	$smarty->assign('title', lang_get('fatal_page_title'));
	$smarty->assign('content', $op['dbms_msg']);
	$smarty->display('workAreaSimple.tpl');  
	exit();
}



$_GET = strings_stripSlashes($_GET);
$note = isset($_GET['note']) ? $_GET['note'] : null;

$message = lang_get('please_login');
// assign a comment for login
switch($note)
{
	case 'expired':
		// 20051012 - am - fix for 134
		session_start();
		session_unset();
		session_destroy();
		$message = lang_get('session_expired');
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
		$message = lang_get('login_msg_session_exists1') . ' <a href="logout.php">' . 
			lang_get('logout_link') . '</a>' . lang_get('login_msg_session_exists2');
		break;
	default:
		break;
}

//20050826 - scs - added displaying of security notes
$securityNotes = getSecurityNotes();
	
$smarty = new TLSmarty();
$smarty->assign('securityNotes',$securityNotes);
$smarty->assign('note',$message);
$smarty->assign('css', TL_BASE_HREF . TL_LOGIN_CSS);
$smarty->display('login.tpl');
?>