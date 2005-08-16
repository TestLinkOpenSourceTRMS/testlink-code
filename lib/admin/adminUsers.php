<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: adminUsers.php,v $
 *
 * @version $Revision: 1.2 $
 * @modified $Date: 2005/08/16 18:00:53 $
 *
 * @author Martin Havlat
 *
 * This page allows  editing users.
 *
 * @author Andreas Morsing - added user_is_name_valid whenever a new user will be modified
**/
include('../../config.inc.php');
require_once("../../lib/functions/lang_api.php");
require_once("users.inc.php");
testlinkInitPage();

$arrResults = null;
$_POST = strings_stripSlashes($_POST);
$editUser = isset($_POST['editUser']) ? $_POST['editUser'] : null;
if($editUser)
{
	$user_qty = count($_POST['id']);
	for($idx = 0;$idx < $user_qty;$idx++)
	{
		$sqlRes = lang_get("login_must_not_be_empty");
		
		$login = $_POST['login'][$idx]; 
		if (strlen($login))
		{
			if (user_is_name_valid($login))
			{
				if (userUpdate($_POST['id'][$idx],$_POST['first'][$idx],$_POST['last'][$idx],
				               $_POST['email'][$idx],$_POST['login'][$idx],$_POST['rights'][$idx],
				               $_POST['locale'][$idx]) == 'ok')
				{
					$sqlRes = lang_get('updated');
				}	
				else
					$sqlRes = lang_get('invalid_query') . mysql_error();
			}
			else
				$sqlRes = lang_get('invalid_user_name') . "\n" . lang_get('valid_user_name_format');
		}
			
		$arrResults[] =  array(
								'login' => $_POST['login'][$idx],
					   			'action' => $sqlRes
							  );
	}
}
$users = null;
getAllUsers_assoc($users);

$smarty = new TLSmarty;
$smarty->assign('optRights', getListOfRights());
$smarty->assign('arrResults', $arrResults);
$smarty->assign('updated', $editUser ? 'yes' :null);
$smarty->assign('users', $users);
$smarty->display('adminUsers.tpl');
?>