<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: index.php,v $
 *
 * @version $Revision: 1.19 $
 * @modified $Date: 2008/10/12 08:11:56 $ by $Author: schlundus $
 *
 * @author Martin Havlat
 *
 * This file is main window. Include authorization of user and define frames (navBar and main).
**/
require_once('lib/functions/configCheck.php');
checkConfiguration();
require_once('config.inc.php');
require_once('common.php');
doSessionStart();

unset($_SESSION['basehref']);
setPaths();
$reqURI = isset($_GET['reqURI']) ? $_GET['reqURI'] : 'lib/general/mainPage.php';

//verify the session during a work
if (!isset($_SESSION['currentUser']))
{
	redirect(TL_BASE_HREF ."login.php?note=expired");
	exit;
}
$smarty = new TLSmarty();
$smarty->assign('title', lang_get('main_page_title'));
$smarty->assign('titleframe', 'lib/general/navBar.php');
$smarty->assign('mainframe', $reqURI);
$smarty->display('main.tpl');
?>