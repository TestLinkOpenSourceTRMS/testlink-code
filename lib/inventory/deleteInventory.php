<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Delete a device in inventory list
 * 
 * @filesource	deleteInventory.php
 * @package 	TestLink
 * @author 		Martin Havlat
 * @copyright 	2009, TestLink community 
 *
 * @internal revisions
 *
 **/

require_once('../../config.inc.php');
require_once('common.php');
testlinkInitPage($db);

$data['userfeedback'] = lang_get('inventory_msg_no_action');
$data['success'] = FALSE;
$args = init_args();

if ($_SESSION['currentUser']->hasRight($db,"project_inventory_management",$args->tproject_id))
{
	$tlIs = new tlInventory($args->tproject_id, $db);
	$data['success'] = $tlIs->deleteInventory($args->machineID);
	$data['success'] = ($data['success'] == 1 /*$tlIs->OK*/) ? true : false;
	$data['userfeedback'] = $tlIs->getUserFeedback();
}
else
{
	tLog('User has not rights to set a device!','ERROR');
	$data['userfeedback'] = lang_get('inventory_msg_no_rights');
}

echo json_encode($data);


function init_args()
{
    $_REQUEST = strings_stripSlashes($_REQUEST);
	$iParams = array("machineID" => array(tlInputParameter::INT_N),
					 "tproject_id" => array(tlInputParameter::INT_N) );

	$args = new stdClass();
    R_PARAMS($iParams,$args);
    
    // from session
    $args->userId = $_SESSION['userID'];

    return $args;
}

/**
 * @param $db resource the database connection handle
 * @param $user the current active user
 * 
 * @return boolean returns true if the page can be accessed
 */
function checkRights(&$db,&$user)
{
	return $user->hasRight($db,"project_inventory_management");
}
?>