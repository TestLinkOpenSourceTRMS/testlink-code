<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* This script is distributed under the GNU General Public License 2 or later. 
*
* Filename $RCSfile: usersedit.php,v $
*
* @version $Revision: 1.22 $
* @modified $Date: 2007/12/18 20:47:19 $ $Author: schlundus $
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

$template_dir = 'usermanagement/';
testlinkInitPage($db);

$args = init_args($_GET,$_POST);
$user_id = $args->user_id;
$sessionUserID = $_SESSION['userID'];

$sqlResult = null;
$action = null;
$user_feedback = '';

$login_method = config_get('login_method');
$external_password_mgmt = ('LDAP' == $login_method )? 1 : 0;

if ($args->do_update)
{
	if ($args->user_id == 0)
	{
		$user = new tlUser();	
		$sqlResult = $user->setPassword($args->password);
		if ($sqlResult == OK)
		{
			$user->m_login = $args->login;
			$user->m_emailAddress = $args->email;
			$user->m_firstName = $args->first;
			$user->m_lastName = $args->last;
			$user->m_globalRoleID = $args->rights_id;
			$user->m_locale = $args->locale;
			$user->m_bActive = $args->user_is_active;
			
			$sqlResult = $user->writeToDB($db);
		}
		if ($sqlResult == OK)
			$user_feedback = sprintf(lang_get('user_created'),$args->login);
		else 
			$sqlResult = getUserErrorMessage($sqlResult);
	}
	else
	{
		$user = new tlUser($args->user_id);
		$sqlResult = $user->readFromDB($db);
		if ($sqlResult == OK)
		{
			$user->m_firstName = $args->first;
			$user->m_lastName = $args->last;
			$user->m_emailAddress = $args->email;
			$user->m_locale = $args->locale;
			$user->m_bActive = $args->user_is_active;
			$user->m_globalRoleID = $args->rights_id;
			
			$sqlResult = $user->writeToDB($db);
			if ($sqlResult == OK && $sessionUserID == $args->user_id)
			{
				setUserSession($db,$user->m_login, $sessionUserID, $user->m_globalRoleID, $user->m_emailAddress, $user->m_locale);
				if (!$args->user_is_active)
				{
					header("Location: ../../logout.php");
					exit();
				}
			}
			$sqlResult = getUserErrorMessage($sqlResult);
			$action = "updated";							
		}
	}
}
else if ($args->do_reset_password && $user_id)
{
	$result = resetPassword($db,$user_id,$user_feedback);
	if ($result == OK)
		$user_feedback = lang_get('password_reseted');  		
}
$user = null;
if ($user_id)
{
	$user = new tlUser($user_id);
	$user->readFromDB($db);
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
$smarty->assign('userData', $user);
$smarty->assign('result',$sqlResult);
$smarty->assign('action',$action);
$smarty->display($template_dir . 'usersedit.tpl');


function init_args($get_hash, $post_hash)
{
	$post_hash = strings_stripSlashes($post_hash);

	$intval_keys = array('delete' => 0, 'user' => 0,'user_id' => 0);
	foreach ($intval_keys as $key => $value)
	{
		$args->$key = isset($get_hash[$key]) ? intval($get_hash[$key]) : $value;
	}
	
	$intval_keys = array('rights_id' => TL_ROLES_GUEST);
	if(!isset($get_hash['user_id']))
	{
		$intval_keys['user_id'] = 0; 
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