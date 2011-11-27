<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Login page with configuratin checking and authorization
 *
 * @package 	TestLink
 * @author 		Martin Havlat
 * @copyright 	2006, TestLink community 
 * @version    	CVS: $Id: login.php,v 1.58.2.1 2010/11/24 08:05:58 mx-julian Exp $
 * @filesource	http://testlink.cvs.sourceforge.net/viewvc/testlink/testlink/login.php?view=markup
 * @link 		http://www.teamst.org/index.php
 * 
 * @internal Revisions
 * @since 1.9.4
 * 20111127 - franciscom - ajaxlogin has to avoid existent session check
 **/

require_once('lib/functions/configCheck.php');
checkConfiguration();
require_once('config.inc.php');
require_once('common.php');
require_once('doAuthorize.php');

$templateCfg = templateConfiguration();
$doRender = false;

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

switch($args->action) 
{
	case 'doLogin':
		 doSessionStart();
		 unset($_SESSION['basehref']);
		 setPaths();
		 
		 // check if db scheme is up to date else deny login
		 // only try to authorize user if scheme version is OK
		 $op = checkSchemaVersion($db);
		 if($op['status'] == tl::OK) {
		 	$op = doAuthorize($db,$args->login,$args->pwd);
		 }
		 
		 if($op['status'] < tl::OK)
		 {
		 	$gui->note = is_null($op['msg']) ? lang_get('bad_user_passwd') : $op['msg'];
	 		$doRender = true;
		 }
		 else
		 {
			 // Login successful, redirect to destination
		 	$args->currentUser = $_SESSION['currentUser'];
		 	logAuditEvent(TLS("audit_login_succeeded",$args->login,
		 	                  $_SERVER['REMOTE_ADDR']),"LOGIN",$args->currentUser->dbID,"users");
			// If destination param is set redirect to given page ...
			if (!empty($args->destination) && preg_match("/linkto.php/", $args->destination)) 
			{
					redirect($args->destination);
			}
			// ... or show main page
		 	redirect($_SESSION['basehref']."index.php".($args->preqURI ? "?reqURI=".urlencode($args->preqURI) :""));
			exit();
		 }
		 break;
	
	
	case 'ajaxlogin':

		 // When doing ajax login we need to skip control regarding session already open
		 // that we use when doing normal login.
		 // If we do not proceed this way we will enter an infiniti loop
		 doSessionStart();
		 unset($_SESSION['basehref']);
		 setPaths();
		 
	 	 $op = doAuthorize($db,$args->login,$args->pwd,array('doSessionExistsCheck' => false));
		 if($op['status'] < tl::OK)
		 {
		 	$gui->note = is_null($op['msg']) ? lang_get('bad_user_passwd') : $op['msg'];
	 		echo json_encode(array('success' => false,'reason' => $gui->note));
		 }
		 else
		 {
			 // Login successful, redirect to destination
		 	$args->currentUser = $_SESSION['currentUser'];
		 	logAuditEvent(TLS("audit_login_succeeded",$args->login,
		 	                  $_SERVER['REMOTE_ADDR']),"LOGIN",$args->currentUser->dbID,"users");
		 	echo json_encode(array('success' => true));
		 }
		 break;
	
	
	case 'ajaxcheck':
		 doSessionStart();
		 unset($_SESSION['basehref']);
		 setPaths();
		 $validSession = checkSessionValid($db, false);
	     
		 // Send a json reply, include localized strings for use in js to display a login form.
		 echo json_encode(array('validSession' => $validSession,
		 	                    'username_label' => lang_get('login_name'),
		 	                    'password_label' => lang_get('password'),
		 	                    'login_label' => lang_get('btn_login'),
		                        'timeout_info' => lang_get('timeout_info')));
		 break;
	
	case 'loginform':
		 $doRender = true;
		 break;
}

if( $doRender )
{
	$logPeriodToDelete = config_get('removeEventsOlderThan');
	$g_tlLogger->deleteEventsFor(null, strtotime("-{$logPeriodToDelete} days UTC"));
	
	$smarty = new TLSmarty();
	$smarty->assign('gui', $gui);
	$smarty->display($templateCfg->default_template);
}



/**
 * 
 *
 */
function init_args()
{
	// we need to understand Why is req and reqURI parameters to the login?
	$iParams = array("note" => array(tlInputParameter::STRING_N,0,255),
		             "tl_login" => array(tlInputParameter::STRING_N,0,30),
		             "tl_password" => array(tlInputParameter::STRING_N,0,32),
		             "req" => array(tlInputParameter::STRING_N,0,4000),
		             "reqURI" => array(tlInputParameter::STRING_N,0,4000),
		             "action" => array(tlInputParameter::STRING_N,0, 10),
		             "destination" => array(tlInputParameter::STRING_N, 0, 255),
	);
	$pParams = R_PARAMS($iParams);

    $args = new stdClass();
    $args->note = $pParams['note'];
    $args->login = $pParams['tl_login'];
    $args->pwd = $pParams['tl_password'];
    $args->reqURI = urlencode($pParams['req']);
    $args->preqURI = urlencode($pParams['reqURI']);
	$args->destination = urldecode($pParams['destination']);

	if ($pParams['action'] == 'ajaxcheck' || $pParams['action'] == 'ajaxlogin') {
		$args->action = $pParams['action'];
	} else if (!is_null($args->login)) {
		$args->action = 'doLogin';
	} else {
		$args->action = 'loginform';
	}

    return $args;
}

/**
 * 
 *
 */
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
	$gui->destination = $args->destination;
    
	return $gui;
}
?>