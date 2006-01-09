<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: adminUsers.php,v $
 *
 * @version $Revision: 1.12 $
 * @modified $Date: 2006/01/09 07:18:15 $
 *
 * @author Martin Havlat
 *
 * This page shows all users
 *
 * 20053112 - scs - cleanup, due to removing bulk update of users
 * 20060103 - scs - ADOdb changes
 * 20060107 - fm  - refactoring init_args()
 *
**/
include('../../config.inc.php');
require_once("users.inc.php");
testlinkInitPage($db);

$sqlRes = null;
$action = null;
$update_title_bar = 0;
$reload = 0;

$args = init_args($_GET,$_POST,TRUE);

if ($args->do_update)
{
	$sqlRes = userUpdate($db,$args->user_id,$args->first,$args->last,
	                         $args->email,null,$args->rights_id,$args->locale,$args->user_is_active);
	$action = "updated";
	if ($sqlRes == 'ok' && $args->user_id == $_SESSION['userID'])
	{
		//if the user has no longer the mgt_users right, reload the index.php page,
		//else we must update the titlebar
		//BUGID 0000103: Localization is changed but not strings
		if (!has_rights($db,'mgt_users'))
		{
			$reload = 1;
		}	
		else
		{
			$update_title_bar = 1;
		}
		
		if (!$args->user_is_active)
		{
			header("Location: ../../logout.php");
			exit();
		}
	}
}
else if ($args->delete && $args->user)
{
	$sqlRes = userDelete($db,$args->user);
	
	//if the users deletes itself then logout
	if ($args->user == $_SESSION['userID'])
	{
		header("Location: ../../logout.php");
		exit();
	}
	$action = "deleted";
}
	
$users = getAllUsers($db);
$rights = getListOfRights($db);

$smarty = new TLSmarty();
$smarty->assign('optRights',$rights);
$smarty->assign('update_title_bar',$update_title_bar);
$smarty->assign('reload',$reload);
$smarty->assign('users',$users);
$smarty->assign('result',$sqlRes);
$smarty->assign('action',$action);
$smarty->display($g_tpl['adminUsers']);
?>


<?php
// 20060107 - fm
function init_args($get_hash, $post_hash, $do_post_strip=TRUE)
{
	if( $do_post_strip)
	{
  	$post_hash = strings_stripSlashes($post_hash);
  }

  $intval_keys=array('delete' => 0, 'user' => 0);
  foreach ($intval_keys as $key => $value)
  {
    $args->$key=isset($get_hash[$key]) ? intval($get_hash[$key]) : $value;
  }
	 
  $intval_keys=array('rights_id' => GUEST, 'user_id' => 0);
  foreach ($intval_keys as $key => $value)
  {
    $args->$key=isset($post_hash[$key]) ? intval($post_hash[$key]) : $value;
  }
	
	$nullable_keys=array('first','last','email','locale');
  foreach ($nullable_keys as $value)
  {
    $args->$value=isset($post_hash[$value]) ? $post_hash[$value] : null;
  }
 
  $bool_keys=array('user_is_active','do_update');
  foreach ($bool_keys as $value)
  {
    $args->$value=isset($post_hash[$value]) ? 1 : 0;
  }
  
  return ($args);
}
?>