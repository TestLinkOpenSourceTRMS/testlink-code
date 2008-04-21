<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: usersView.php,v $
 *
 * @version $Revision: 1.18 $
 * @modified $Date: 2008/04/21 08:30:03 $ -  $Author: franciscom $
 *
 * shows all users
 *
 * rev: 20080416 - franciscom - getRoleColourCfg()
 *
 */
require_once("../../config.inc.php");
require_once("users.inc.php");
testlinkInitPage($db);

$templateCfg = templateConfiguration();

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
$smarty->assign('role_colour',getRoleColourCfg($db));

$grants=getGrantsForUserMgmt($db,$_SESSION['currentUser']);


$smarty->assign('grants',$grants);

$smarty->assign('update_title_bar',0);
$smarty->assign('reload',0);
$smarty->assign('users',$users);
$smarty->assign('result',$sqlResult);
$smarty->assign('action',$action);
$smarty->assign('base_href', $_SESSION['basehref']);
$smarty->display($templateCfg->template_dir . $g_tpl['usersview']);



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
function getRoleColourCfg(&$dbHandler)
{
    $role_colour=config_get('role_colour');
    $roles = tlRole::getAll($dbHandler,null,null,null,tlRole::TLOBJ_O_GET_DETAIL_MINIMUM);
    unset($roles[TL_ROLES_UNDEFINED]);
    foreach($roles as $roleObj)
    {
        if( !isset($role_colour[$roleObj->name]) )
        {
            $role_colour[$roleObj->name]='';  
        }  
    }    
    return $role_colour;
}

?>
