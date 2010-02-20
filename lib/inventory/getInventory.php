<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Get inventory data
 * 
 * @package 	TestLink
 * @author 		Martin Havlat
 * @copyright 	2009, TestLink community 
 * @version    	CVS: $Id: getInventory.php,v 1.2 2010/02/20 09:27:29 franciscom Exp $
 *
 * @internal Revisions:
 * None
 *
 **/

require_once('../../config.inc.php');
require_once('common.php');
testlinkInitPage($db);

$tlIs = new tlInventory($_SESSION['testprojectID'], $db);
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