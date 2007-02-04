<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: usersview.php,v $
 *
 * @version $Revision: 1.7 $
 * @modified $Date: 2007/02/04 20:18:32 $ -  $Author: schlundus $
 *
 * This page shows all users
 * 
 * 20070106 - franciscom - added order by login and order by role
**/
include('../../config.inc.php');
require_once("users.inc.php");
testlinkInitPage($db);

$sqlResult = null;
$action = null;
$do_toogle=0;

$operation = isset($_REQUEST['operation']) ? $_REQUEST['operation'] : '';
$user_order_by = isset($_REQUEST['user_order_by']) ? $_REQUEST['user_order_by'] : 'order_by_login';
$order_by_dir['order_by_role_dir']=isset($_REQUEST['order_by_role_dir']) ? $_REQUEST['order_by_role_dir'] : 'asc';
$order_by_dir['order_by_login_dir']=isset($_REQUEST['order_by_login_dir']) ? $_REQUEST['order_by_login_dir'] : 'asc';

switch($operation)
{
	case 'delete':
		$user_id = isset($_REQUEST['user']) ? $_REQUEST['user'] : 0;
		$user_data = getUserByID($db,$user_id);
		$sqlResult = userDelete($db,$user_id);
		//if the users deletes itself then logout
		if ($user_id == $_SESSION['userID'])
		{
			header("Location: ../../logout.php");
			exit();
		}
		$action = "deleted";
		$order_by_clause = get_order_by_clause($user_order_by,$order_by_dir);
		$do_toogle = 0;
		break;
	case 'order_by_role':
	case 'order_by_login':
		$order_by_clause = get_order_by_clause($operation,$order_by_dir);
		$do_toogle = 1;
		$user_order_by = $operation;
		break;
	default:
		$order_by_clause = get_order_by_clause('order_by_login',
												array('order_by_login_dir' => 'asc'));
		$order_by_dir['order_by_login_dir'] = 'desc';
		break;
}

$users = get_all_users_roles($db,$order_by_clause);
if($do_toogle)
{
	$the_k = $operation . "_dir";
	$order_by_dir[$the_k] = $order_by_dir[$the_k] == 'asc' ? 'desc' : 'asc'; 
}

$smarty = new TLSmarty();
$smarty->assign('user_order_by',$user_order_by);
$smarty->assign('order_by_role_dir',$order_by_dir['order_by_role_dir']);
$smarty->assign('order_by_login_dir',$order_by_dir['order_by_login_dir']);
$smarty->assign('role_colour',config_get('role_colour'));


$smarty->assign('mgt_users',has_rights($db,"mgt_users"));
$smarty->assign('role_management',has_rights($db,"role_management"));
$smarty->assign('tp_user_role_assignment', has_rights($db,"mgt_users") ? "yes" : has_rights($db,"user_role_assignment"));
$smarty->assign('tproject_user_role_assignment', has_rights($db,"mgt_users") ? "yes" : has_rights($db,"user_role_assignment",null,-1));
$smarty->assign('update_title_bar',0);
$smarty->assign('reload',0);
$smarty->assign('users',$users);
$smarty->assign('result',$sqlResult);
$smarty->assign('action',$action);
$smarty->display($g_tpl['usersview']);

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
