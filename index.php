<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: index.php,v $
 *
 * @version $Revision: 1.6 $
 * @modified $Date: 2005/11/19 23:07:35 $ by $Author: schlundus $
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
//200508 MHT - moved code to procedure
require_once('lib/functions/configCheck.php');
checkConfiguration();

require_once('config.inc.php');
require_once('doAuthorize.php');
require_once('common.php');
doSessionStart();
setPaths();

$_POST = strings_stripSlashes($_POST);
$login = isset($_POST['login']) ? $_POST['login'] : null;
$pwd = isset($_POST['password']) ? $_POST['password'] : null;

if (!is_null($login))
{
	$op = doDBConnect();
	
	if ($op['status'])
		doAuthorize($login,$pwd);
	else
	{
		$smarty = new TLSmarty();
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

$smarty = new TLSmarty();
$smarty->assign('title', lang_get('main_page_title'));
$smarty->assign('titleframe', 'lib/general/navBar.php');
$smarty->assign('mainframe', 'lib/general/mainPage.php');
$smarty->display('main.tpl');
?>