<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* This script is distributed under the GNU General Public License 2 or later. 
*
* Filename $RCSfile: userInfo.php,v $
*
* @version $Revision: 1.11 $
* @modified $Date: 2006/01/05 07:30:34 $
* 
* Displays the users' information and allows users to change 
* their passwords and user info.
* 
*
* 20050913 - fm - BUGID 0000103: Localization is changed but not strings
* 20050829 - scs - moved POST params to the top of the script
* 20060102 - scs - changes due to ADOdb
*/
require_once('../../config.inc.php');
require_once('users.inc.php');
testlinkInitPage($db);

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
	$updateResult = userUpdate($db,$id,$first,$last,$email,null,null,$locale);
}
else if ($bChangePwd)
{
	$updateResult = updateUserPassword($db,$id,$old,$new);
}

$userResult ='';
existLogin($db,$_SESSION['user'], $userResult);

$smarty = new TLSmarty();
$smarty->assign('userData', $userResult);
$smarty->assign('updateResult', $updateResult);
// 20050913 - fm - BUGID 0000103: Localization is changed but not strings
$smarty->assign('update_title_bar', $bEdit);
$smarty->display('userInfo.tpl');
?>
