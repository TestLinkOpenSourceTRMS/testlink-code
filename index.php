<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: index.php,v $
 *
 * @version $Revision: 1.14 $
 * @modified $Date: 2008/01/02 21:14:00 $ by $Author: schlundus $
 *
 * @author Martin Havlat
 *
 * This file is main window. Include authorization of user and define frames (navBar and main).
**/
require_once('lib/functions/configCheck.php');
checkConfiguration();

require_once('config.inc.php');
require_once('doAuthorize.php');
require_once('common.php');
doSessionStart();
unset($_SESSION['basehref']);
setPaths();

$_POST = strings_stripSlashes($_POST);
$login = isset($_POST['login']) ? $_POST['login'] : null;
$pwd = isset($_POST['password']) ? $_POST['password'] : null;

if (!is_null($login))
{
	$op = doDBConnect($db);
	
	if ($op['status'])
	{
		if (doAuthorize($db,$login,$pwd,$msg) < tl::OK)
		{
			tLog("Account ".$login." doesn't exist or used wrong password.",'INFO');
			// not authorized
			tLog("Login '$login' fails. (Timing: " . tlTimingCurrent() . ')', 'INFO');
			redirect($_SESSION['basehref'] . "login.php?note=" . $msg);
			exit();
		}
		else
			tLog("Login ok. (Timing: " . tlTimingCurrent() . ')', 'INFO');
	}
	else
	{
		$smarty = new TLSmarty();
		$smarty->assign('title', lang_get('fatal_page_title'));
		$smarty->assign('content', $op['dbms_msg']);
		$smarty->display('workAreaSimple.tpl');  
		exit();
	}
}

//verify the session during a work
if (!isset($_SESSION['currentUser']))
{
	redirect(TL_BASE_HREF ."login.php?note=expired");
	exit;
}

$smarty = new TLSmarty();
$smarty->assign('title', lang_get('main_page_title'));
$smarty->assign('titleframe', 'lib/general/navBar.php');
$smarty->assign('mainframe', 'lib/general/mainPage.php');
$smarty->display('main.tpl');
?>