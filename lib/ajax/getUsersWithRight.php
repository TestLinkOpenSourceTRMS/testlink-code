<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Get list of users with a project right
 * 
 * @package 	TestLink
 * @author 		Martin Havlat
 * @copyright 	2009, TestLink community 
 * @version    	CVS: $Id: getUsersWithRight.php,v 1.1 2010/02/12 00:20:12 havlat Exp $
 *
 * @internal Revisions:
 * None
 *
 **/

require_once('../../config.inc.php');
require_once('common.php');
testlinkInitPage($db);
$data = array();

$iParams = array(
		"right" => array(tlInputParameter::STRING_N,0,100,'/^[a-z0-9_]+$/')
		);
$args = G_PARAMS($iParams);


// user must have the same right as requested (security)
if (has_rights($db,$args['right']))
{
	$tlUser = new tlUser($_SESSION['userID']);
	$data['rows'] = $tlUser->getNamesForProjectRight($db,$args['right'],$_SESSION['testprojectID']);
	$data['rows'][] = array('id'=>'0','login'=>' ','first'=>' ','last'=>' '); // option for no owner
}
else
{
	tLog('Invalid right for the user: '.$args['right'], 'ERROR');	
}

echo json_encode($data);

?>