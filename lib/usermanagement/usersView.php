<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: usersView.php,v $
 *
 * @version $Revision: 1.30 $
 * @modified $Date: 2009/10/05 08:47:11 $ -  $Author: franciscom $
 *
 * shows all users
 *
 * rev: 20080416 - franciscom - getRoleColourCfg()
 *
 */
require_once("../../config.inc.php");
require_once("users.inc.php");
testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();
$args = init_args();
$grants = getGrantsForUserMgmt($db,$args->currentUser);

$sqlResult = null;
$action = null;
$user_feedback = '';

$orderBy = new stdClass();
$orderBy->type = 'order_by_login';
$orderBy->dir = array('order_by_login_dir' => 'asc');
	
switch($args->operation)
{
	case 'delete':
		//user cannot delete itself
		if ($args->user_id != $args->currentUserID)
		{
			$user = new tlUser($args->user_id);
			$sqlResult = $user->readFromDB($db);
			if ($sqlResult >= tl::OK)
			{
				$userLogin = $user->login;
				$sqlResult = $user->deleteFromDB($db);
				if ($sqlResult >= tl::OK)
				{
					logAuditEvent(TLS("audit_user_deleted",$user->login),"DELETE",$args->user_id,"users");
					$user_feedback = sprintf(lang_get('user_deleted'),$userLogin);
				}
			}
		}

		if ($sqlResult != tl::OK)
			$user_feedback = lang_get('error_user_not_deleted');

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
$order_by_clause = get_order_by_clause($orderBy);
$users = getAllUsersRoles($db,$order_by_clause);

$highlight = initialize_tabsmenu();
$highlight->view_users = 1;

$smarty = new TLSmarty();
$smarty->assign('highlight',$highlight);
$smarty->assign('user_feedback',$user_feedback);
$smarty->assign('user_order_by',$args->user_order_by);
$smarty->assign('order_by_role_dir',$args->order_by_dir['order_by_role_dir']);
$smarty->assign('order_by_login_dir',$args->order_by_dir['order_by_login_dir']);
$smarty->assign('role_colour',getRoleColourCfg($db));
$smarty->assign('update_title_bar',0);
$smarty->assign('reload',0);
$smarty->assign('users',$users);
$smarty->assign('result',$sqlResult);
$smarty->assign('action',$action);
$smarty->assign('base_href', $args->basehref);
$smarty->assign('grants',$grants);

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
	$iParams = array(
			"operation" => array(tlInputParameter::STRING_N,0,50),
			"user_order_by" => array(tlInputParameter::STRING_N,0,50),
			"order_by_role_dir" => array(tlInputParameter::STRING_N,0,4),
			"order_by_login_dir" => array(tlInputParameter::STRING_N,0,4),
			"user" => array(tlInputParameter::INT_N),
		);

	$pParams = R_PARAMS($iParams);

	$args = new stdClass();
	$args->operation = $pParams["operation"];
    $args->user_order_by = ($pParams["user_order_by"] != '') ? $pParams["user_order_by"] : 'order_by_login';
    $args->order_by_dir["order_by_role_dir"] = ($pParams["order_by_role_dir"] != '') ? $pParams["order_by_role_dir"] : 'asc';
    $args->order_by_dir["order_by_login_dir"] = ($pParams["order_by_login_dir"] != '') ? $pParams["order_by_login_dir"] : 'asc';
    $args->user_id = $pParams['user'];

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

function checkRights(&$db,&$user)
{
	return $user->hasRight($db,'mgt_users');
}
?>
