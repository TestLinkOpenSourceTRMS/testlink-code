<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* This script is distributed under the GNU General Public License 2 or later. 
*
* Filename $RCSfile: usersedit.php,v $
*
* @version $Revision: 1.17 $
* @modified $Date: 2007/08/29 17:21:02 $ $Author: jbarchibald $
* 
* rev :  BUGID 918
*
*   20070829 - jbarchibald - fix bug 1000 - Testplan role assignments
*
* Allows editing a user
*/
require_once('../../config.inc.php');
require_once('testproject.class.php');
require_once('users.inc.php');
require_once('email_api.php');
testlinkInitPage($db);

$args = init_args($_GET,$_POST);
$user_id = $args->user_id;
$sessionUserID = $_SESSION['userID'];

$sqlResult = null;
$action = null;
$update_title_bar = 0;
$reload = 0;
$user_feedback = '';


$login_method = config_get('login_method');
$external_password_mgmt = ('LDAP' == $login_method )? 1 : 0;


if ($args->do_update)
{
	if ($args->user_id == 0)
	{
		$sqlResult=checkLogin($db,$args->login);
		if (!strlen($args->email))
		    $sqlResult = lang_get('empty_email_address');

    if( !$external_password_mgmt && !strlen($args->password))
    {
		    $sqlResult = lang_get('warning_empty_pwd');
    }
    
		if ($sqlResult =='ok')
		{
			$args->user_id = userInsert($db,$args->login, $args->password, $args->first, $args->last,  
			 	                              $args->email, $args->rights_id, $args->locale, $args->user_is_active);
			if(!$args->user_id)
				$sqlResult = lang_get('user_not_added');
			else
			  $user_feedback=sprintf(lang_get('user_created'),$args->login);	
		}	
		else
		{	
		  $user_feedback = nl2br($sqlResult);
		}
	}
	else
	{
		if (!strlen($args->email))
			$sqlResult = lang_get('empty_email_address');
		else	
			$sqlResult = userUpdate($db,$args->user_id,$args->first,$args->last,
	                            $args->email,null,$args->rights_id,$args->locale,$args->user_is_active);
		$action = "updated";							
		$user_id = $args->user_id;
		
	}

	if ($sqlResult == 'ok' && ($args->user_id == $sessionUserID))
	{
		//if the user has no longer the mgt_users right, reload the index.php page,
		//else we must update the titlebar
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

if ($args->do_reset_password)
{
  $user_feedback='';
  $op = reset_password($db,$user_id);
  
  if( $op->status_ok )
  {
    $user_feedback=lang_get('password_reseted');  
  }
  else
  {
    $user_feedback=$op->msg;
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

$smarty->assign('user_feedback',$user_feedback);
$smarty->assign('external_password_mgmt', $external_password_mgmt);
$smarty->assign('mgt_users',has_rights($db,"mgt_users"));
$smarty->assign('role_management',has_rights($db,"role_management"));
$smarty->assign('tp_user_role_assignment', has_rights($db,"mgt_users") ? "yes" : has_rights($db,"testplan_user_role_assignment"));
$smarty->assign('tproject_user_role_assignment', has_rights($db,"mgt_users") ? "yes" : has_rights($db,"user_role_assignment",null,-1));

$roles = getAllRoles($db);
unset($roles[TL_ROLES_UNDEFINED]);

$smarty->assign('optRights',$roles);
$smarty->assign('userData', $userResult);
$smarty->assign('result',$sqlResult);
$smarty->assign('action',$action);
$smarty->assign('user_feedback',$user_feedback);

$smarty->display('usersedit.tpl');
?>

<?php
// -------------------------------------------------------------------------------------------------
/*
  function: 

  args :
  
  returns: 

*/
function init_args($get_hash, $post_hash)
{
	$post_hash = strings_stripSlashes($post_hash);

	$intval_keys = array('delete' => 0, 'user' => 0,'user_id' => 0);
	foreach ($intval_keys as $key => $value)
	{
		$args->$key = isset($get_hash[$key]) ? intval($get_hash[$key]) : $value;
	}
	
	$intval_keys = array('rights_id' => TL_ROLES_GUEST);
	if( !isset($get_hash['user_id']) )
	{
	  $intval_keys['user_id']=0; 
	}
	
	foreach ($intval_keys as $key => $value)
	{
		$args->$key = isset($post_hash[$key]) ? intval($post_hash[$key]) : $value;
	}
	
	$nullable_keys = array('first','last','email','locale','login','password');
	foreach ($nullable_keys as $value)
	{
		$args->$value = isset($post_hash[$value]) ? $post_hash[$value] : null;
	}
 
	$bool_keys = array('user_is_active','do_update','do_reset_password');
	foreach ($bool_keys as $value)
	{
		$args->$value = isset($post_hash[$value]) ? 1 : 0;
	}
  
	return $args;
}
?>
