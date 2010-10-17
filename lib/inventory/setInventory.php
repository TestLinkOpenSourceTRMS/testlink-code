<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Add or modify a device in inventory list
 * 
 * @package 	TestLink
 * @author 		Martin Havlat
 * @copyright 	2009, TestLink community 
 * @version    	CVS: $Id: setInventory.php,v 1.7 2010/10/17 09:46:37 franciscom Exp $
 *
 * @internal Revisions:
 * None
 *
 **/

require_once('../../config.inc.php');
require_once('common.php');
testlinkInitPage($db);

$data['userfeedback'] = lang_get('inventory_msg_no_action');
$data['success'] = FALSE;
$args = init_args();

if ($_SESSION['currentUser']->hasRight($db,"project_inventory_management"))
{
	$tlIs = new tlInventory($_SESSION['testprojectID'], $db);
	$data['success'] = $tlIs->setInventory($args);
	$data['success'] = ($data['success'] == 1 /*$tlIs->OK*/) ? true : false;
	$data['userfeedback'] = $tlIs->getUserFeedback();
	$data['record'] = $tlIs->getCurrentData();
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
					"machineOwner" => array(tlInputParameter::INT_N),
			        "machineName" => array(tlInputParameter::STRING_N,0,255),
			        "machineIp" => array(tlInputParameter::STRING_N,0,50),
			        "machineNotes" => array(tlInputParameter::STRING_N,0,2000),
			        "machinePurpose" => array(tlInputParameter::STRING_N,0,2000),
			        "machineHw" => array(tlInputParameter::STRING_N,0,2000),
	 				);

	$args = new stdClass();
    R_PARAMS($iParams,$args);
    
    return $args;
}

?>