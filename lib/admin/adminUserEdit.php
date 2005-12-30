<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* @version $Id: adminUserEdit.php,v 1.1 2005/12/30 16:02:03 franciscom Exp $ 
*
* @author Francisco Mancardi
* 
*
*/
require_once('../../config.inc.php');
require_once('users.inc.php');
require_once("../../lib/functions/lang_api.php");
testlinkInitPage();

define('DONT_UPDATE_LOGIN',null);


global $g_tpl;

$tpl='adminUserEdit.tpl';

$_REQUEST = strings_stripSlashes($_REQUEST);
$user_id = isset($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : 0;
$user_login = isset($_REQUEST['user_login']) ? $_REQUEST['user_login'] : null;


$do_update = isset($_REQUEST['do_update']) ? 1 : 0;

$smarty = new TLSmarty();
$smarty->assign('optRights', getListOfRights());


if( $do_update )
{
  $first  = isset($_REQUEST['first']) ? $_REQUEST['first'] : null;
  $last   = isset($_REQUEST['last']) ? $_REQUEST['last'] : null;
  $email  = isset($_REQUEST['email']) ? $_REQUEST['email'] : null;
  $locale = isset($_REQUEST['locale']) ? $_REQUEST['locale'] : null;
  $rights_id = isset($_REQUEST['rights_id']) ? intval($_REQUEST['rights_id']) : GUEST;

  $user_is_active = isset($_REQUEST['user_is_active']) ? 1 : 0;

   
	$updateResult = userUpdate($user_id,$first,$last,$email,DONT_UPDATE_LOGIN,$rights_id,$locale,$user_is_active);

  $users=getAllUsers_assoc();
  $smarty->assign('users', $users);
  $tpl = $g_tpl["adminUsers"];
}
else 
{
	$userResult ='';
	existLogin($user_login, $userResult);

	$smarty->assign('userData', $userResult);
	$smarty->assign('updateResult', $updateResult);
	$smarty->assign('update_title_bar', $bEdit);
}

$smarty->display($tpl);
?>
