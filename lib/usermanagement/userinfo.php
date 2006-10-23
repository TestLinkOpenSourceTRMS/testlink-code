<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* This script is distributed under the GNU General Public License 2 or later. 
*
* Filename $RCSfile: userinfo.php,v $
*
* @version $Revision: 1.4 $
* @modified $Date: 2006/10/23 20:11:28 $
* 
* Displays the users' information and allows users to change 
* their passwords and user info.
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
$userName = $_SESSION['user'];

$login_method = config_get('login_method');
$external_password_mgmt = ('LDAP' == $login_method )? 1 : 0;


$updateResult = null;
if ($bEdit)
	$updateResult = userUpdate($db,$id,$first,$last,$email,null,null,$locale);
else if ($bChangePwd)
	$updateResult = updateUserPassword($db,$id,$old,$new);

$userResult ='';
existLogin($db,$userName, $userResult);

$smarty = new TLSmarty();
$smarty->assign('external_password_mgmt', $external_password_mgmt);
$smarty->assign('userData', $userResult);
$smarty->assign('updateResult', $updateResult);
$smarty->assign('update_title_bar', $bEdit);
$smarty->display('userInfo.tpl');
?>
