<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: adminUsersDelete.php,v $
 *
 * @version $Revision: 1.2 $
 * @modified $Date: 2005/08/16 18:00:53 $
 *
 * @author Martin Havlat
 *
 * @todo deactive users instead of delete
 *
**/
include('../../config.inc.php');
require_once("users.inc.php");
require_once("../../lib/functions/lang_api.php");
testlinkInitPage();

$sqlRes = null;
if(isset($_POST['delete']))
{
	$id = isset($_POST['user']) ? intval($_POST['user']) : 0;
	$sqlRes = userDelete($id);
} 
$arrLogin = getListOfUsers();

$smarty = new TLSmarty;
$smarty->assign('result', $sqlRes);
$smarty->assign('arrLogin', $arrLogin);
$smarty->display('adminUsersDelete.tpl');
?>

