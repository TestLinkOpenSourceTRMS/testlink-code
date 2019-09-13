<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Get inventory data
 * 
 * @package 	TestLink
 * @author 		Martin Havlat
 * @copyright 2009,2019 TestLink community 
 *
 **/

require_once('../../config.inc.php');
require_once('common.php');
testlinkInitPage($db);

$tproj_id = intval($_SESSION['testprojectID']);
$tlIs = new tlInventory($tproj_id, $db);
$data = $tlIs->getAll();

$tlUser = new tlUser(intval($_SESSION['userID']));
$users = $tlUser->getNames($db);

// fill login instead of user ID
if (!is_null($data)) {
	foreach ($data as $k => $v) {
		if ($v['owner_id'] != '0') {
			$data[$k]['owner'] = $users[$v['owner_id']]['login'];
		} else {
			$data[$k]['owner'] = '';
		}
	}
}
echo json_encode($data);