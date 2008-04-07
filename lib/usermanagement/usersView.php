<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: usersView.php,v $
 *
 * @version $Revision: 1.16 $
 * @modified $Date: 2008/04/07 07:07:00 $ -  $Author: franciscom $
 *
 * This page shows all users
 */
require_once("../../config.inc.php");
require_once("users.inc.php");
testlinkInitPage($db);

$template_dir = 'usermanagement/';
$default_template = str_replace('.php','.tpl',basename($_SERVER['SCRIPT_NAME']));

$sqlResult = null;
$action = null;
$user_feedback = '';

$orderBy=new stdClass();
$orderBy->type = 'order_by_login';
$orderBy->dir = array('order_by_login_dir' => 'asc');

$args=init_args();

switch($args->operation)
{
	case 'delete':
		$user = new tlUser($args->user_id);
		$sqlResult = $user->readFromDB($db);
		if ($sqlResult >= tl::OK)
		{
			$userLogin = $user->login;
			$sqlResult = $user->deleteFromDB($db);
			if ($sqlResult >= tl::OK)
			{
				logAuditEvent(TLS("audit_user_deleted",$user->login),"DELETE",$args->user_id,"users");
				//if the users deletes itself then logout
				if ($args->user_id == $_SESSION['currentUser']->dbID)
				{
					header("Location: ../../logout.php");
					exit();
				}
				$user_feedback = sprintf(lang_get('user_deleted'),$userLogin);
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

$smarty = new TLSmarty();
$smarty->assign('highlight',$highlight);
$smarty->assign('user_feedback',$user_feedback);
$smarty->assign('user_order_by',$args->user_order_by);
$smarty->assign('order_by_role_dir',$args->order_by_dir['order_by_role_dir']);
$smarty->assign('order_by_login_dir',$args->order_by_dir['order_by_login_dir']);
$smarty->assign('role_colour',config_get('role_colour'));

$grants=getGrantsForUserMgmt($db,$_SESSION['currentUser']);
$smarty->assign('grants',$grants);

// $smarty->assign('mgt_users',has_rights($db,"mgt_users"));
// $smarty->assign('role_management',has_rights($db,"role_management"));
// $smarty->assign('tp_user_role_assignment', has_rights($db,"mgt_users") ? "yes" : has_rights($db,"testplan_user_role_assignment"));
// $smarty->assign('tproject_user_role_assignment', has_rights($db,"mgt_users") ? "yes" : has_rights($db,"user_role_assignment",null,-1));

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
    $args = new stdClass();
    $_REQUEST = strings_stripSlashes($_REQUEST);

    $key2loop=array('operation' => '', 'user_order_by' => 'order_by_login');
    foreach($key2loop as $key => $value)
    {
        $args->$key=isset($_REQUEST[$key]) ? $_REQUEST[$key] : $value;
    }
   
    $key2loop=array('order_by_role_dir' => 'asc', 'order_by_login_dir' => 'asc');
    foreach($key2loop as $key => $value)
    {
        $args->order_by_dir[$key]=isset($_REQUEST[$key]) ? $_REQUEST[$key] : $value;
    }
    $args->user_id = isset($_REQUEST['user']) ? $_REQUEST['user'] : 0;

    return $args;  
}
?>
