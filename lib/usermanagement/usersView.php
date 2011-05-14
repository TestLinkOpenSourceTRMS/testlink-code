<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Shows all users
 *
 * @package 	TestLink
 * @author 		-
 * @copyright 	2007-2009, TestLink community 
 * @version    	CVS: $Id: usersView.php,v 1.37 2011/01/10 15:38:55 asimon83 Exp $
 * @link 		http://www.teamst.org/index.php
 *
 *
 * @internal Revisions:
 *  20100419 - franciscom - BUGID 3355: A user can not be deleted from the list
 *	20100326 - franciscom - BUGID 3324
 *	20100106 - franciscom - security improvement - checkUserOrderBy()
 *                         (after scanning with Acunetix Web Security Scanner)
 *                          
 */
require_once("../../config.inc.php");
require_once("users.inc.php");
testlinkInitPage($db);
$templateCfg = templateConfiguration();
$args = init_args();
checkRights($db,$_SESSION['currentUser'],$args);


$sqlResult = null;
$action = null;
$user_feedback = '';

$orderBy = new stdClass();
$orderBy->type = 'order_by_login';
$orderBy->dir = array('order_by_login_dir' => 'asc');
	
switch($args->operation)
{
	case 'disable':
		// user cannot disable => inactivate itself
		if ($args->user_id != $args->currentUserID)
		{
			$user = new tlUser($args->user_id);
			$sqlResult = $user->readFromDB($db);
			if ($sqlResult >= tl::OK)
			{
				$userLogin = $user->login;
				$sqlResult = $user->setActive($db,0);
				if ($sqlResult >= tl::OK)
				{
					logAuditEvent(TLS("audit_user_disabled",$user->login),"DISABLE",$args->user_id,"users");
					$user_feedback = sprintf(lang_get('user_disabled'),$userLogin);
				}
			}
		}
    	
		if ($sqlResult != tl::OK)
		{
			$user_feedback = lang_get('error_user_not_disabled');
	    }
		
		$orderBy->type = $args->user_order_by;
		$orderBy->dir = $args->order_by_dir;
	break;
		
	case 'order_by_role':
	case 'order_by_login':
		$orderBy->type = $args->operation;
		$orderBy->dir = $args->order_by_dir;
		$args->user_order_by = $args->operation;
		$order_by_clause = get_order_by_clause($orderBy);

		$the_k = $args->operation . "_dir";
		$args->order_by_dir[$the_k] = $args->order_by_dir[$the_k] == 'asc' ? 'desc' : 'asc';
		break;

	default:
		$order_by_dir['order_by_login_dir'] = 'desc';
		break;
}


$gui = initializeGui($db,$args,$orderBy);

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->assign('user_feedback',$user_feedback);
$smarty->assign('result',$sqlResult);
$smarty->assign('action',$action);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


function toggle_order_by_dir($which_order_by,$order_by_dir_map)
{
	$obm[$which_order_by] = $order_by_dir_map[$which_order_by] == 'asc' ? 'desc' : 'asc';
	return $obm;
}

/*
  function: get_order_by_clause()
            get order by SQL clause to use to order user info

  args:

  returns: string

*/
function get_order_by_clause($order)
{
	switch($order->type)
	{
		case 'order_by_role':
			$order_by_clause = " ORDER BY description " . $order->dir['order_by_role_dir'];
			break;

		case 'order_by_login':
			$order_by_clause = " ORDER BY login " . $order->dir['order_by_login_dir'];
			break;
	}
	return $order_by_clause;
}


/*
  function: init_args()
            get info from request and session

  args:

  returns: object

*/
function init_args()
{
	// input from GET['HelloString3'], 
	// type: string,  
	// minLen: 1, 
	// maxLen: 15,
	// regular expression: null
	// checkFunction: applys checks via checkFooOrBar() to ensure its either 'foo' or 'bar' 
	// normalization: done via  normFunction() which replaces ',' with '.' 
	// "HelloString3" => array("GET",tlInputParameter::STRING_N,1,15,'checkFooOrBar','normFunction'),
	$iParams = array("operation" => array(tlInputParameter::STRING_N,0,50),
			         "user_order_by" => array(tlInputParameter::STRING_N,0,50,null,'checkUserOrderBy'),			
			         "order_by_role_dir" => array(tlInputParameter::STRING_N,0,4),
			         "order_by_login_dir" => array(tlInputParameter::STRING_N,0,4),
			         "user" => array(tlInputParameter::INT_N),
			         "tproject_id" => array(tlInputParameter::INT_N),
			         "tplan_id" => array(tlInputParameter::INT_N),
			         "hide_inactive_users" => array(tlInputParameter::CB_BOOL));

	$pParams = R_PARAMS($iParams);

	// BUGID 4066 - take care of proper escaping when magic_quotes_gpc is enabled	
	$_REQUEST=strings_stripSlashes($_REQUEST);

	$args = new stdClass();
	$args->operation = $pParams["operation"];
    $args->user_order_by = ($pParams["user_order_by"] != '') ? $pParams["user_order_by"] : 'order_by_login';
    $args->order_by_dir["order_by_role_dir"] = ($pParams["order_by_role_dir"] != '') ? $pParams["order_by_role_dir"] : 'asc';
    $args->order_by_dir["order_by_login_dir"] = ($pParams["order_by_login_dir"] != '') ? $pParams["order_by_login_dir"] : 'asc';
    $args->user_id = $pParams['user'];
    $args->tproject_id = $pParams['tproject_id'];
    $args->tplan_id = $pParams['tplan_id'];
	
	
	// BUGID 3355: A user can not be deleted from the list
	$args->hide_inactive_users = $pParams["hide_inactive_users"];
	$args->checked_hide_inactive_users = $args->hide_inactive_users ? 'checked="checked"' : '';
	$display = $args->hide_inactive_users ? 'none' : 'table-row';
	$args->body_onload = "onload=\"toggleRowByClass('hide_inactive_users','inactive_user','{$display}')\"";

    $args->currentUser = $_SESSION['currentUser'];
    $args->currentUserID = $_SESSION['currentUser']->dbID;
    $args->basehref =  $_SESSION['basehref'];
    
    return $args;
}

/*
  function: getRoleColourCfg
            using configuration parameter ($g_role_colour)
            creates a map with following structure:
            key: role name
            value: colour

            If name is not defined on $g_role_colour (this normally
            happens for user defined roles), will be added with '' as colour (means default colour).

  args: db: reference to db object

  returns: map

*/
function getRoleColourCfg(&$db)
{
    $role_colour = config_get('role_colour');
    $roles = tlRole::getAll($db,null,null,null,tlRole::TLOBJ_O_GET_DETAIL_MINIMUM);
    unset($roles[TL_ROLES_UNDEFINED]);
    foreach($roles as $roleObj)
    {
    	if(!isset($role_colour[$roleObj->name]))
        {
            $role_colour[$roleObj->name] = '';
        }
    }
    return $role_colour;
}


/**
 * check function for tlInputParameter user_order_by
 *
 */
function checkUserOrderBy($input)
{
	$domain = array_flip(array('order_by_role','order_by_login'));
	
	$status_ok = isset($domain[$input]) ? true : false;
	return $status_ok;
}


function initializeGui(&$dbHandler,&$argsObj,$orderBy)
{
	$guiObj = new stdClass();
	
	$guiObj->highlight = initialize_tabsmenu();
	$guiObj->highlight->view_users = 1;

	$guiObj->update_title_bar = 0;
	$guiObj->reload = 0;
	$guiObj->user_order_by = $argsObj->user_order_by;
	$guiObj->order_by_role_dir = $argsObj->order_by_dir['order_by_role_dir'];
	$guiObj->order_by_login_dir = $argsObj->order_by_dir['order_by_login_dir'];
	$guiObj->checked_hide_inactive_users = $argsObj->checked_hide_inactive_users;
	$guiObj->base_href = $argsObj->basehref;
	$guiObj->body_onload = $argsObj->body_onload;

	$guiObj->role_colour = getRoleColourCfg($dbHandler);
	$guiObj->users = getAllUsersRoles($dbHandler,get_order_by_clause($orderBy));
	$guiObj->grants = getGrantsForUserMgmt($dbHandler,$argsObj->currentUser,$argsObj->tproject_id);

	return $guiObj;
}



/**
 * 
 *
 */
function checkRights(&$db,&$userObj,$argsObj)
{
	// For this feature check must be done on Global Rights => those that belong to
	// role assigned to user when user was created (Global/Default Role)
	// => enviroment is ignored.
	// To instruct method to ignore enviromente, we need to set enviroment but with INEXISTENT ID 
	// (best option is negative value)
	$env['tproject_id'] = -1;
	$env['tplan_id'] = -1;
	checkSecurityClearance($db,$userObj,$env,array('mgt_users'),'and');
}
?>
