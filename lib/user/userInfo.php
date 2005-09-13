<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* @version $Id: userInfo.php,v 1.5 2005/09/13 17:06:01 franciscom Exp $ 
*
* @author	Asiel Brumfield <asielb@users.sourceforge.net>
* @author 	Martin Havlat
* 
* Displays the users' information and allows users to change 
* their passwords and user info.
* 
* 20050913 - fm - BUGID 0000103: Localization is changed but not strings
*
* 20050829 - scs - moved POST params to the top of the script
*
*/
require_once('../../config.inc.php');
require_once('users.inc.php');
require_once("../../lib/functions/lang_api.php");
testlinkInitPage();

$_POST = strings_stripSlashes($_POST);
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$first = isset($_POST['first']) ? $_POST['first'] : null;
$last = isset($_POST['last']) ? $_POST['last'] : null;
$email = isset($_POST['email']) ? $_POST['email'] : null;
$locale = isset($_POST['locale']) ? $_POST['locale'] : null;
$old = isset($_POST['old']) ? $_POST['old'] : null;
$new = isset($_POST['new1']) ? $_POST['new1'] : null;
$bEdit = isset($_POST['editUser']) ? 1 : 0;
$bChangePwd = isset($_POST['changePasswd']) ? 1 : 0;

$updateResult = null;
if ($bEdit)
{
	$updateResult = userUpdate($id,$first,$last,$email,null,null,$locale);
}
else if ($bChangePwd)
{
	$updateResult = updateUserPassword($id,$old,$new);
}

echo "<pre>debug"; echo $bEdit ; echo "</pre>";
$userResult ='';
existLogin($_SESSION['user'], $userResult);

$smarty = new TLSmarty();
$smarty->assign('userData', $userResult);
$smarty->assign('updateResult', $updateResult);

// 20050913 - fm - BUGID 0000103: Localization is changed but not strings
$smarty->assign('update_title_bar', $bEdit);

$smarty->display('userInfo.tpl');
?>
