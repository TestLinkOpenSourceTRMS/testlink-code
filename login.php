<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: login.php,v $
 *
 * @version $Revision: 1.9 $
 * @modified $Date: 2005/10/08 04:11:58 $ by $Author: havlat $
 *
 * @author Martin Havlat
 * 
 * The page allows adjust login data
 * 
 * 20050831 - scs - cosmetic changes
 * 200508 - MHT - added config check
 **/

require_once('lib/functions/configCheck.php');
checkConfiguration();

require('config.inc.php');
require_once('lib/functions/common.php');
require_once('lib/functions/users.inc.php');

doDBConnect();

$_GET = strings_stripSlashes($_GET);
$note = isset($_GET['note']) ? $_GET['note'] : null;

$message = lang_get('please_login');
// assign a comment for login
switch($note)
{
	case 'expired':
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
$smarty->assign('css', TL_BASE_HREF . 'gui/css/tl_login.css');
$smarty->display('login.tpl');
?>