<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: adminUsersDelete.php,v $
 *
 * @version $Revision: 1.3 $
 * @modified $Date: 2005/08/29 11:13:46 $
 *
 * @author Martin Havlat
 *
 * @todo deactive users instead of delete
 * 20050829 - scs - moved POST params to the top of the script
 *
**/
include('../../config.inc.php');
require_once("users.inc.php");
testlinkInitPage();

$id = isset($_POST['user']) ? intval($_POST['user']) : 0;
$bDelete = isset($_POST['delete']) ? 1 : 0;

$sqlRes = null;
if($bDelete)
{
	$sqlRes = userDelete($id);
} 
$arrLogin = getListOfUsers();

$smarty = new TLSmarty();
$smarty->assign('result', $sqlRes);
$smarty->assign('arrLogin', $arrLogin);
$smarty->display('adminUsersDelete.tpl');
?>

