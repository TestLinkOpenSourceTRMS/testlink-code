<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: login.php,v $
 *
 * @version $Revision: 1.52 $
 * @modified $Date: 2009/11/19 20:05:39 $ by $Author: schlundus $
 * @author Martin Havlat
 * 
 * Login management
 *
 **/
require_once('lib/functions/configCheck.php');
checkConfiguration();
require_once('config.inc.php');
require_once('common.php');
require_once('doAuthorize.php');

$templateCfg = templateConfiguration();

$op = doDBConnect($db);
if (!$op['status'])
{
	$smarty = new TLSmarty();
	$smarty->assign('title', lang_get('fatal_page_title'));
	$smarty->assign('content', $op['dbms_msg']);
	$smarty->display('workAreaSimple.tpl'); 
	tLog('Connection fail page shown.','ERROR'); 
	exit();
}

$args = init_args();
$gui = init_gui($db,$args);

if(!is_null($args->login))
{
	doSessionStart();
	unset($_SESSION['basehref']);
	setPaths();
	
	if(doAuthorize($db,$args->login,$args->pwd,$msg) < tl::OK)
	{
		if (!$msg)
			$gui->note = lang_get('bad_user_passwd');
		else
			$gui->note = $msg;
	}
	else
	{
		$args->currentUser = $_SESSION['currentUser'];
		logAuditEvent(TLS("audit_login_succeeded",$args->login,
		                  $_SERVER['REMOTE_ADDR']),"LOGIN",$args->currentUser->dbID,"users");
		redirect($_SESSION['basehref']."index.php".($args->preqURI ? "?reqURI=".urlencode($args->preqURI) :""));
		exit();
	}
}

$logPeriodToDelete = config_get('removeEventsOlderThan');
$g_tlLogger->deleteEventsFor(null, strtotime("-{$logPeriodToDelete} days UTC"));

$smarty = new TLSmarty();
$smarty->assign('gui', $gui);
$smarty->display($templateCfg->default_template);

function init_args()
{
	$iParams = array("note" => array(tlInputParameter::STRING_N,0,255),
		"tl_login" => array(tlInputParameter::STRING_N,0,30),
		"tl_password" => array(tlInputParameter::STRING_N,0,32),
		"req" => array(tlInputParameter::STRING_N,0,4000),
		"reqURI" => array(tlInputParameter::STRING_N,0,4000),
	);
	$pParams = R_PARAMS($iParams);
	
    $args = new stdClass();
    $args->note = $pParams['note'];
    $args->login = $pParams['tl_login'];
    $args->pwd = $pParams['tl_password'];
    $args->reqURI = $pParams['req'];
    $args->preqURI = $pParams['reqURI'];
	
    return $args;
}

function init_gui(&$db,$args)
{
	$gui = new stdClass();
	
	$authCfg = config_get('authentication');
	$gui->securityNotes = getSecurityNotes($db);
	$gui->external_password_mgmt = ('LDAP' == $authCfg['method']) ? 1 : 0;
	$gui->login_disabled = ($gui->external_password_mgmt && !checkForLDAPExtension()) ? 1 : 0;
	$gui->user_self_signup = config_get('user_self_signup');

	switch($args->note)
    {
    	case 'expired':
    		if(!isset($_SESSION))
    		{
    			session_start();
    		}
    		session_unset();
    		session_destroy();
    		$gui->note = lang_get('session_expired');
    		$gui->reqURI = null;
    		break;
    		
    	case 'first':
    		$gui->note = lang_get('your_first_login');
    		$gui->reqURI = null;
    		break;
    		
    	case 'lost':
    		$gui->note = lang_get('passwd_lost');
    		$gui->reqURI = null;
    		break;
    		
    	default:
    		$gui->note = lang_get('please_login');
    		break;
    }
	$gui->reqURI = $args->reqURI ? $args->reqURI : $args->preqURI;
    
	return $gui;
}
?>