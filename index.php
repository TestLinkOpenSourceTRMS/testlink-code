<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: index.php,v $
 *
 * @version $Revision: 1.3 $
 * @modified $Date: 2005/08/24 06:28:15 $ by $Author: franciscom $
 *
 * @author Martin Havlat
 *
 * This file is main window. Include authorization of user and define frames (navBar and main).
 * 
 * @author Francisco Mancardi - 20050823
 * if installer directory exists,  block login
 *
 * @author Francisco Mancardi - 20050806 - Installer
**/

// check if we need to run the install program
clearstatcache();
$file_to_check = "config_db.inc.php";
if(is_file($file_to_check))
{
	require_once "config.inc.php";
}
else
{
	echo "Fatal Error. You haven't configured TestLink yet.<br/><a href='./install/index.php'>
		Click Here To Start Installation/Setup!</a></body></html>";
	exit();
}

// 20050823
$installer_dir="./install/";
clearstatcache();
if(is_dir($installer_dir))
{
	echo "TestLink Security Issue: <br> Please remove the install directory " . $installer_dir;
	exit();
}
// ---------------------------------------------------------------------------------------------------


require_once('config.inc.php');
require_once('doAuthorize.php');
require_once('common.php');
doSessionStart();
setPaths();

$login = isset($_POST['login']) ? $_POST['login'] : null;
if (!is_null($login))
{
	$op = doDBConnect();
	
	if ($op['status'])
		doAuthorize();
	else
	{
		$smarty = new TLSmarty;
		$smarty->assign('title', lang_get('fatal_page_title'));
		$smarty->assign('content', $op['dbms_msg']);
		// MHT 200507 corrected template filename
		$smarty->display('workAreaSimple.tpl');  
		exit();
	}
}

//verify the session during a work
if (!isset($_SESSION['user']))
{
	redirect(TL_BASE_HREF ."login.php?note=expired");
	exit;
}

$smarty = new TLSmarty;
$smarty->assign('title', lang_get('main_page_title'));
$smarty->assign('titleframe', 'lib/general/navBar.php');
$smarty->assign('mainframe', 'lib/general/mainPage.php');
$smarty->display('main.tpl');
?>