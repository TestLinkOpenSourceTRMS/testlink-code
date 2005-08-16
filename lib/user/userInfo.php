<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* @version $Id: userInfo.php,v 1.2 2005/08/16 18:00:59 franciscom Exp $ 
*
* @author	Asiel Brumfield <asielb@users.sourceforge.net>
* @author 	Martin Havlat
* 
* This file generates and displays the users' information and 
* allows users to change their passwords and user info.
*
*/
require_once('../../config.inc.php');
require_once('users.inc.php');
require_once("../../lib/functions/lang_api.php");
testlinkInitPage();

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$updateResult = null;
if (isset($_POST['editUser']))
{
	$first = isset($_POST['first']) ? strings_stripSlashes($_POST['first']) : null;
	$last = isset($_POST['last']) ? strings_stripSlashes($_POST['last']) : null;
	$email = isset($_POST['email']) ? strings_stripSlashes($_POST['email']) : null;
	$locale = isset($_POST['locale']) ? strings_stripSlashes($_POST['locale']) : null;

	$updateResult = userUpdate($id,$first,$last,$email,null,null,$locale);
}
else if (isset($_POST['changePasswd']))
{
	$old = isset($_POST['old']) ? strings_stripSlashes($_POST['old']) : null;
	$new = isset($_POST['new1']) ? strings_stripSlashes($_POST['new1']) : null;
	$updateResult = updateUserPassword($id,$old,$new);
}

$userResult ='';
existLogin($_SESSION['user'], $userResult);

$smarty = new TLSmarty;
$smarty->assign('ses', $_SESSION);
$smarty->assign('userData', $userResult);
$smarty->assign('updateResult', $updateResult);
$smarty->display('userInfo.tpl');
?>
