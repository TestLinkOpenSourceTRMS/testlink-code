<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* This script is distributed under the GNU General Public License 2 or later. 
*
* Filename $RCSfile: usersedit.php,v $
*
* @version $Revision: 1.4 $
* @modified $Date: 2006/02/25 21:48:27 $
* 
* Allows editing a user
*/
require_once('../../config.inc.php');
require_once('testproject.class.php');
require_once('users.inc.php');
testlinkInitPage($db);

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$args = init_args($_GET,$_POST,TRUE);
$sessionUserID = $_SESSION['userID'];

$sqlResult = null;
$action = null;
$update_title_bar = 0;
$reload = 0;

if ($args->do_update)
{
	if ($args->user_id == 0)
	{
		$sqlResult = checkLogin($db,$args->login);
		if ($sqlResult =='ok')
		{
			if(!userInsert($db,$args->login, $args->password, $args->first, $args->last,
						   $args->email, $args->rights_id, $args->locale, $args->user_is_active))
				$sqlResult = lang_get('user_not_added');
			else
				$args->user_id = $db->insert_id();	
		}		
		$action = "added";
	}
	else
	{
		$sqlResult = userUpdate($db,$args->user_id,$args->first,$args->last,
	                        $args->email,null,$args->rights_id,$args->locale,$args->user_is_active);
		$action = "updated";							
		$user_id = $args->user_id;
	}

	if ($sqlResult == 'ok' && ($args->user_id == $sessionUserID))
	{
		//if the user has no longer the mgt_users right, reload the index.php page,
		//else we must update the titlebar
		//BUGID 0000103: Localization is changed but not strings
		if (!has_rights($db,'mgt_users'))
			$reload = 1;
		else
			$update_title_bar = 1;
		
		if (!$args->user_is_active)
		{
			header("Location: ../../logout.php");
			exit();
		}
	}
}

$userResult = null;

if ($user_id)
{
	$userResult = getUserById($db,$user_id);
	if ($userResult)
		$userResult = $userResult[0];
}
	
$smarty = new TLSmarty();
$smarty->assign('optRights', getAllRoles($db));
$smarty->assign('userData', $userResult);
$smarty->assign('result',$sqlResult);
$smarty->assign('action',$action);
$smarty->display('usersedit.tpl');


// 20060107 - fm
function init_args($get_hash, $post_hash, $do_post_strip = TRUE)
{
	if($do_post_strip)
		$post_hash = strings_stripSlashes($post_hash);

	$intval_keys = array('delete' => 0, 'user' => 0);
	foreach ($intval_keys as $key => $value)
	{
		$args->$key = isset($get_hash[$key]) ? intval($get_hash[$key]) : $value;
	}
	 
	$intval_keys = array('rights_id' => TL_ROLES_GUEST, 'user_id' => 0);
	foreach ($intval_keys as $key => $value)
	{
		$args->$key = isset($post_hash[$key]) ? intval($post_hash[$key]) : $value;
	}
	
	$nullable_keys = array('first','last','email','locale','login','password');
	foreach ($nullable_keys as $value)
	{
		$args->$value = isset($post_hash[$value]) ? $post_hash[$value] : null;
	}
 
	$bool_keys = array('user_is_active','do_update');
	foreach ($bool_keys as $value)
	{
		$args->$value = isset($post_hash[$value]) ? 1 : 0;
	}
  
	return $args;
}
?>