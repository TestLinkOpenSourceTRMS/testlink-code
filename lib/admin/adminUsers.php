<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: adminUsers.php,v $
 *
 * @version $Revision: 1.5 $
 * @modified $Date: 2005/11/13 19:19:31 $
 *
 * @author Martin Havlat
 *
 * This page allows  editing users.
 *
 * @author Andreas Morsing - added user_is_name_valid whenever a 
 * 							 new user will be modified
 * 20051112 - scs - added trimming of login
**/
include('../../config.inc.php');
require_once("users.inc.php");
testlinkInitPage();

$arrResults = null;
$_POST = strings_stripSlashes($_POST);
$bEditUser = isset($_POST['editUser']) ? $_POST['editUser'] : null;

if($bEditUser)
{
	$user_qty = count($_POST['id']);
	for($idx = 0;$idx < $user_qty;$idx++)
	{
		$sqlRes = lang_get("login_must_not_be_empty");
		
		$login = trim($_POST['login'][$idx]); 
		if (strlen($login))
		{
			if (user_is_name_valid($login))
			{
				if (userUpdate($_POST['id'][$idx],$_POST['first'][$idx],
							   $_POST['last'][$idx],$_POST['email'][$idx],
							   $login,$_POST['rights'][$idx],
				               $_POST['locale'][$idx]) == 'ok')
				{
					$sqlRes = lang_get('updated');
				}	
				else
					$sqlRes = lang_get('invalid_query') . mysql_error();
			}
			else
				$sqlRes = lang_get('invalid_user_name') . "\n" . 
						  lang_get('valid_user_name_format');
		}
			
		$arrResults[] =  array(
								'login' => $login,
					   			'action' => $sqlRes
							  );
	}
}
$users = null;
getAllUsers_assoc($users);

$smarty = new TLSmarty();
$smarty->assign('optRights', getListOfRights());
$smarty->assign('arrResults', $arrResults);
$smarty->assign('updated', $bEditUser ? 'yes' :null);
$smarty->assign('users', $users);
$smarty->display('adminUsers.tpl');
?>