<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Displays the users' information and allows users to change their passwords and user info.
 *
 * @package 	TestLink
 * @author 		-
 * @copyright 	2007-2011, TestLink community 
 * @version    	CVS: $Id: userInfo.php,v 1.34 2011/01/10 15:38:55 asimon83 Exp $
 * @link 		http://www.teamst.org/index.php
 *
 *
 * @internal Revisions:
 *	20101008 - Julian - reload navBar after changing personal data (localization)
 */
require_once('../../config.inc.php');
require_once('users.inc.php');
require_once('../../lib/api/APIKey.php');
testlinkInitPage($db);

$templateCfg = templateConfiguration();
$args = init_args();

$user = new tlUser($args->userID);
$user->readFromDB($db);

$gui = new stdClass();
$gui->tproject_id = $args->tproject_id; 
$gui->update_title_bar = 0;
$gui->external_password_mgmt = tlUser::isPasswordMgtExternal();
$gui->mgt_view_events = $user->hasRight($db,"mgt_view_events",$gui->tproject_id);


$op = new stdClass();
$op->auditMsg = null;
$op->user_feedback = null;
$op->status = tl::OK;

$doUpdate = false;
switch($args->doAction)
{
    case 'editUser':
		$doUpdate = true;
		foreach($args->user as $key => $value)
		{
			$user->$key = $value;
		}
		$op->status = tl::OK;
		$op->auditMsg = "audit_user_saved";
		$op->user_feedback = lang_get('result_user_changed');
		$gui->update_title_bar = 1;
    	break;

    case 'changePassword':
	    $op = changePassword($args,$user);
	    $doUpdate = ($op->status >= tl::OK);
	    break;

    case 'genAPIKey':
	    $op = generateAPIKey($args,$user);
	    break;
}

if($doUpdate)
{
	$op->status = $user->writeToDB($db);
	if ($op->status >= tl::OK)
	{
		logAuditEvent(TLS($op->auditMsg,$user->login),"SAVE",$user->dbID,"users");
		$_SESSION['currentUser'] = $user;
		setUserSession($db,$user->login, $args->userID, $user->globalRoleID, $user->emailAddress, $user->locale);
	}
}

$gui->loginHistory = new stdClass();
$gui->loginHistory->failed = $g_tlLogger->getAuditEventsFor($args->userID,"users","LOGIN_FAILED",10);
$gui->loginHistory->ok = $g_tlLogger->getAuditEventsFor($args->userID,"users","LOGIN",10);

if ($op->status != tl::OK)
{
	$op->user_feedback = getUserErrorMessage($op->status);
}
$user->readFromDB($db);

// set a string if not generated key yet
if (null == $user->userApiKey)
{
	$user->userApiKey = TLS('none');
}

$gui->user_feedback = $op->user_feedback;

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->assign('user',$user);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


function init_args()
{
	$_REQUEST=strings_stripSlashes($_REQUEST);

	$iParams = array("firstName" => array("POST",tlInputParameter::STRING_N,0,30),
			         "lastName" => array("REQUEST",tlInputParameter::STRING_N,0,30),
			         "emailAddress" => array("REQUEST",tlInputParameter::STRING_N,0,100),
			         "locale" => array("POST",tlInputParameter::STRING_N,0,10),
			         "oldpassword" => array("POST",tlInputParameter::STRING_N,0,32),
			         "newpassword" => array("POST",tlInputParameter::STRING_N,0,32),
			         "doAction" => array("POST",tlInputParameter::STRING_N,0,15,null,'checkDoAction'));

	$pParams = I_PARAMS($iParams);
	

	$args = new stdClass();
    $args->user = new stdClass();
 	$args->user->firstName = $pParams["firstName"];
	$args->user->lastName = $pParams["lastName"];
	$args->user->emailAddress = $pParams["emailAddress"];
	$args->user->locale = $pParams["locale"];
	$args->oldpassword = $pParams["oldpassword"];
	$args->newpassword = $pParams["newpassword"];
	$args->doAction = $pParams["doAction"];

	$args->tproject_id = isset($_REQUEST['tproject_id']) ? intval($_REQUEST['tproject_id']) : 0;
	$args->tplan_id = isset($_REQUEST['tplan_id']) ? intval($_REQUEST['tplan_id']) : 0;

	$args->userID = isset($_SESSION['currentUser']) ? $_SESSION['currentUser']->dbID : 0;
        
    return $args;
}

/*
  function: changePassword

  args:

  returns: object with properties:
           status
           user_feedback: string message for on screen feedback
           auditMsg: to be written by logAudid

*/
function changePassword(&$argsObj,&$userMgr)
{
	$op = new stdClass();
    $op->status = $userMgr->comparePassword($argsObj->oldpassword);
    $op->user_feedback = '';
    $op->auditMsg = '';
	if ($op->status == tl::OK)
	{
		$userMgr->setPassword($argsObj->newpassword);
		$op->user_feedback = lang_get('result_password_changed');
		$op->auditMsg = "audit_user_pwd_saved";
	}
    return $op;
}


function generateAPIKey(&$argsObj,&$user)
{
	$op = new stdClass();
    $op->status = tl::OK;
    $op->user_feedback = null;

    if ($user)
    {
	    $APIKey = new APIKey();
	    if ($APIKey->addKeyForUser($argsObj->userID) < tl::OK)
		{
			logAuditEvent(TLS("audit_user_apikey_set",$user->login),"CREATE",$user->login,"users");
			$op->user_feedback = lang_get('result_apikey_create_ok');
		}
    }
    return $op;
}

/**
 * check function for tlInputParameter doAction
 *
 */
function checkDoAction($input)
{
	$domain = array_flip(array('editUser','changePassword','genAPIKey'));
	$status_ok = isset($domain[$input]) ? true : false;
	return $status_ok;
}

?>