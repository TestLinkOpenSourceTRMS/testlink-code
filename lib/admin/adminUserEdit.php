<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* This script is distributed under the GNU General Public License 2 or later. 
*
* Filename $RCSfile: adminUserEdit.php,v $
*
* @version $Revision: 1.2 $
* @modified $Date: 2005/12/31 14:38:10 $
* 
* Allows editing a user
*/
require_once('../../config.inc.php');
require_once('users.inc.php');
testlinkInitPage();

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

$userResult = getUserById($db,$user_id);
if ($userResult)
	$userResult = $userResult[0];

$smarty = new TLSmarty();
$smarty->assign('optRights', getListOfRights($db));
$smarty->assign('userData', $userResult);
$smarty->display('adminUserEdit.tpl');
?>
