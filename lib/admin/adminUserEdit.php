<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* This script is distributed under the GNU General Public License 2 or later. 
*
* Filename $RCSfile: adminUserEdit.php,v $
*
* @version $Revision: 1.3 $
* @modified $Date: 2006/01/05 07:30:33 $
* 
* Allows editing a user
*/
require_once('../../config.inc.php');
require_once('users.inc.php');
testlinkInitPage($db);

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

$userResult = getUserById($db,$user_id);
if ($userResult)
	$userResult = $userResult[0];

$smarty = new TLSmarty();
$smarty->assign('optRights', getListOfRights($db));
$smarty->assign('userData', $userResult);
$smarty->display('adminUserEdit.tpl');
?>
