<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: usersView.php,v $
 *
 * @version $Revision: 1.2 $
 * @modified $Date: 2007/12/21 22:57:18 $ -  $Author: schlundus $
 *
 * This page shows all users
 */
include('../../config.inc.php');
require_once("users.inc.php");
testlinkInitPage($db);

$template_dir = 'usermanagement/';
$default_template = str_replace('.php','.tpl',basename($_SERVER['SCRIPT_NAME']));

$sqlResult = null;
$action = null;
$user_feedback = '';

$operation = isset($_REQUEST['operation']) ? $_REQUEST['operation'] : '';
$user_order_by = isset($_REQUEST['user_order_by']) ? $_REQUEST['user_order_by'] : 'order_by_login';
$order_by_dir['order_by_role_dir'] = isset($_REQUEST['order_by_role_dir']) ? $_REQUEST['order_by_role_dir'] : 'asc';
$order_by_dir['order_by_login_dir'] = isset($_REQUEST['order_by_login_dir']) ? $_REQUEST['order_by_login_dir'] : 'asc';
$user_id = isset($_REQUEST['user']) ? $_REQUEST['user'] : 0;

$orderByType = 'order_by_login';
$orderByDir = array('order_by_login_dir' => 'asc');

switch($operation)
{
	case 'delete':
		$user = new tlUser($user_id);
		$sqlResult = $user->readFromDB($db);
		if ($sqlResult == tl::OK)
		{
			$userLogin = $user->login;
			$sqlResult = $user->deleteFromDB($db);
			if ($sqlResult == tl::OK)
			{
				//if the users deletes itself then logout
				if ($user_id == $_SESSION['userID'])
				{
					header("Location: ../../logout.php");
					exit();
				}
				$user_feedback = sprintf(lang_get('user_deleted'),$userLogin);
			}
		}
		if ($sqlResult != tl::OK)
			$user_feedback = lang_get('error_user_not_deleted');

		$orderByType = $user_order_by;
		$orderByDir = $order_by_dir;
		break;
	case 'order_by_role':
	case 'order_by_login':
		$order_by_clause = get_order_by_clause($operation,$order_by_dir);
		$orderByType = $operation;
		$orderByDir = $order_by_dir;
		$user_order_by = $operation;
		$the_k = $operation . "_dir";
		$order_by_dir[$the_k] = $order_by_dir[$the_k] == 'asc' ? 'desc' : 'asc'; 
		break;
	default:
		$order_by_dir['order_by_login_dir'] = 'desc';
		break;
}
$order_by_clause = get_order_by_clause($orderByType,$orderByDir);
$users = getAllUsersRoles($db,$order_by_clause);

$smarty = new TLSmarty();
$smarty->assign('user_feedback',$user_feedback);
$smarty->assign('user_order_by',$user_order_by);
$smarty->assign('order_by_role_dir',$order_by_dir['order_by_role_dir']);
$smarty->assign('order_by_login_dir',$order_by_dir['order_by_login_dir']);
$smarty->assign('role_colour',config_get('role_colour'));
$smarty->assign('mgt_users',has_rights($db,"mgt_users"));
$smarty->assign('role_management',has_rights($db,"role_management"));
$smarty->assign('tp_user_role_assignment', has_rights($db,"mgt_users") ? "yes" : has_rights($db,"testplan_user_role_assignment"));
$smarty->assign('tproject_user_role_assignment', has_rights($db,"mgt_users") ? "yes" : has_rights($db,"user_role_assignment",null,-1));
$smarty->assign('update_title_bar',0);
$smarty->assign('reload',0);
$smarty->assign('users',$users);
$smarty->assign('result',$sqlResult);
$smarty->assign('action',$action);
$smarty->assign('base_href', $_SESSION['basehref']);
$smarty->display($template_dir . $g_tpl['usersview']);

function toogle_order_by_dir($which_order_by,$order_by_dir_map)
{
	$obm[$which_order_by] = $order_by_dir_map[$which_order_by] == 'asc' ? 'desc' : 'asc'; 
	return $obm;
}

function get_order_by_clause($order_by_type,$order_by_dir)
{
	switch($order_by_type)
	{
		case 'order_by_role':
			$order_by_clause = " ORDER BY description " . $order_by_dir['order_by_role_dir'];
			break;
		case 'order_by_login':
			$order_by_clause = " ORDER BY login " . $order_by_dir['order_by_login_dir'];
			break;
	}
	return $order_by_clause;
}
?>
