<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: usersview.php,v $
 *
 * @version $Revision: 1.12 $
 * @modified $Date: 2007/12/14 22:42:51 $ -  $Author: schlundus $
 *
 * This page shows all users
 */
include('../../config.inc.php');
require_once("users.inc.php");
testlinkInitPage($db);

$template_dir = 'usermanagement/';

$sqlResult = null;
$action = null;
$do_toggle = 0;
$user_feedback = '';

$operation = isset($_REQUEST['operation']) ? $_REQUEST['operation'] : '';
$user_order_by = isset($_REQUEST['user_order_by']) ? $_REQUEST['user_order_by'] : 'order_by_login';
$order_by_dir['order_by_role_dir'] = isset($_REQUEST['order_by_role_dir']) ? $_REQUEST['order_by_role_dir'] : 'asc';
$order_by_dir['order_by_login_dir'] = isset($_REQUEST['order_by_login_dir']) ? $_REQUEST['order_by_login_dir'] : 'asc';
$user_id = isset($_REQUEST['user']) ? $_REQUEST['user'] : 0;

switch($operation)
{
	case 'delete':
		$user_data = getUserByID($db,$user_id);
		$sqlResult = userDelete($db,$user_id);
		//if the users deletes itself then logout
		if ($user_id == $_SESSION['userID'])
		{
			header("Location: ../../logout.php");
			exit();
		}
		$user_feedback=sprintf(lang_get('user_deleted'),$user_data[0]['login']);

		//$action = "deleted";
		$order_by_clause = get_order_by_clause($user_order_by,$order_by_dir);
		$do_toggle = 0;
		break;
		
	case 'order_by_role':
	case 'order_by_login':
		$order_by_clause = get_order_by_clause($operation,$order_by_dir);
		$do_toggle = 1;
		$user_order_by = $operation;
		break;
		
	default:
		$order_by_clause = get_order_by_clause('order_by_login',
												array('order_by_login_dir' => 'asc'));
		$order_by_dir['order_by_login_dir'] = 'desc';
		break;
}

$users = getAllUsersRoles($db,$order_by_clause);
if($do_toggle)
{
	$the_k = $operation . "_dir";
	$order_by_dir[$the_k] = $order_by_dir[$the_k] == 'asc' ? 'desc' : 'asc'; 
}

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
			$order_by_clause = " ORDER BY role_description " . $order_by_dir['order_by_role_dir'];
			break;
		case 'order_by_login':
			$order_by_clause = " ORDER BY login " . $order_by_dir['order_by_login_dir'];
			break;
	}
	return $order_by_clause;
}
?>
