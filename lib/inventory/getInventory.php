<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Get inventory data
 * 
 * @filespurce	getInventory.php
 * @package 	TestLink
 * @author 		Martin Havlat
 * @copyright 	2009, TestLink community 
 *
 * @internal Revisions:
 *
 **/

require_once('../../config.inc.php');
require_once('common.php');
testlinkInitPage($db);

$tlIs = new tlInventory(intval($_REQUEST['tproject_id']), $db);
$data = $tlIs->getAll();

$tlUser = new tlUser($_SESSION['userID']);
$users = $tlUser->getNames($db);

// fill login instead of user ID
if( !is_null($data) )
{
	foreach($data as $k => $v) 
	{
		if ($v['owner_id'] != '0')
		{
			$data[$k]['owner'] = $users[$v['owner_id']]['login'];
		}
		else
		{
			$data[$k]['owner'] = '';
		}
	}
}
//new dBug($data);
echo json_encode($data);
?>