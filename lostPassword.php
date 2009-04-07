<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: lostPassword.php,v $
 *
 * @version $Revision: 1.31 $
 * @modified $Date: 2009/04/07 18:55:29 $ $Author: schlundus $
 *
**/
require_once('config.inc.php');
require_once('common.php');
require_once('users.inc.php');
require_once('email_api.php');

$args = init_args();

$op = doDBConnect($db);
if ($op['status'] == 0)
{
	$smarty = new TLSmarty();
	$smarty->assign('title', lang_get('fatal_page_title'));
	$smarty->assign('msg', $op['dbms_msg']);
	$smarty->display('fatal_error.tpl');
	exit();
}

$bPasswordMgtExternal = tlUser::isPasswordMgtExternal();
$note = lang_get('your_info_for_passwd');
if ($args->login != "" && !$bPasswordMgtExternal)
{
	$userID = tlUser::doesUserExist($db,$args->login);
	if (!$userID)
		$note = lang_get('bad_user');
	else
	{
		$result = resetPassword($db,$userID,$note);
		if ($result >= tl::OK)
		{
		  	$user = new tlUser($userID);
		  	if ($user->readFromDB($db) >= tl::OK)
		  		logAuditEvent(TLS("audit_pwd_reset_requested",$user->login),"PWD_RESET",$userID,"users");
			redirect(TL_BASE_HREF ."login.php?note=lost");
			exit();
		}
		else if ($result == tlUser::E_EMAILLENGTH)
			$note = lang_get('mail_empty_address');
		else if ($note != "")
			$note = getUserErrorMessage($result);
	}
}

$smarty = new TLSmarty();
$smarty->assign('note',$note);
$smarty->assign('external_password_mgmt',$bPasswordMgtExternal);
$smarty->assign('page_title',lang_get('page_title_lost_passwd'));
$smarty->display('loginLost.tpl');

function init_args()
{
	$iParams = array(
		"login" => array(tlInputParameter::STRING_N,0,30),
	);
	$pParams = P_PARAMS($iParams);
	
	$args = new stdClass();
	$args->login = $pParams["login"];
	
	return $args;
}
?>