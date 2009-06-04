<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: lostPassword.php,v $
 *
 * @version $Revision: 1.37 $
 * @modified $Date: 2009/06/04 19:53:27 $ $Author: schlundus $
 *
**/
require_once('config.inc.php');
require_once('common.php');
require_once('users.inc.php');
require_once('email_api.php');
$templateCfg = templateConfiguration();

$args = init_args();
$gui = new stdClass();
$gui->external_password_mgmt = tlUser::isPasswordMgtExternal();
$gui->page_title = lang_get('page_title_lost_passwd');
$gui->note = lang_get('your_info_for_passwd');

$op = doDBConnect($db);
if ($op['status'] == 0)
{
	$smarty = new TLSmarty();
	$smarty->assign('title', lang_get('fatal_page_title'));
	$smarty->assign('msg', $op['dbms_msg']);
	$smarty->display('fatal_error.tpl');
	exit();
}

if ($args->login != "" && !$gui->external_password_mgmt)
{
	$userID = tlUser::doesUserExist($db,$args->login);
	if (!$userID)
	{
		$gui->note = lang_get('bad_user');
	}
	else
	{
		$result = resetPassword($db,$userID,$gui->note);
		if ($result >= tl::OK)
		{
		  	$user = new tlUser($userID);
		  	if ($user->readFromDB($db) >= tl::OK)
		  	{
		  		logAuditEvent(TLS("audit_pwd_reset_requested",$user->login),"PWD_RESET",$userID,"users");
			}
			redirect(TL_BASE_HREF ."login.php?note=lost");
			exit();
		}
		else if ($result == tlUser::E_EMAILLENGTH)
		{
			$gui->note = lang_get('mail_empty_address');
		}	
		else if ($note != "")
		{
			$gui->note = getUserErrorMessage($result);
		}	
	}
}

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->default_template);


function init_args()
{
	$iParams = array("login" => array(tlInputParameter::STRING_N,0,30));
	
	$args = new stdClass();
	$pParams = P_PARAMS($iParams,$args); // WHY TO ASSIGN A VALUE THAT IS NEVER USED ?????

	return $args;
}
?>