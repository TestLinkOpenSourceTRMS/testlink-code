<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Checks if a test case with this name already exist. Used to warn user
 * if non-unique test case name is entered.
 *
 * @package 	TestLink
 * @author 		Erik Eloff
 * @copyright 	2010,2014 TestLink community
 * @filesource  checkTCaseDuplicateName.php
 *
 * @internal revisions
 *
 **/

require_once('../../config.inc.php');
require_once('common.php');
testlinkInitPage($db);
$data = array('success' => true, 'message' => '');

$iParams = array("name" => array(tlInputParameter::STRING_N,0,100),
	             "testcase_id" => array(tlInputParameter::INT),
	             "testsuite_id" => array(tlInputParameter::INT));
$args = G_PARAMS($iParams);

if (has_rights($db, 'mgt_view_tc'))
{
	$tree_manager = new tree($db);
	$node_types_descr_id=$tree_manager->get_available_node_types();
	
	// To allow name check when creating a NEW test case => we do not have test case id
	$args['testcase_id'] = ($args['testcase_id'] > 0 )? $args['testcase_id'] : null;
	$args['testsuite_id'] = ($args['testsuite_id'] > 0 )? $args['testsuite_id'] : null;

	$check = $tree_manager->nodeNameExists($args['name'], $node_types_descr_id['testcase'],
										   $args['testcase_id'],$args['testsuite_id']);

	$data['success'] = !$check['status'];
	$data['message'] = $check['msg'];
}
else
{
	tLog('User has not right needed to do requested action - checkTCaseDuplicateName.php', 'ERROR');
	$data['success'] = false;
	$data['message'] = lang_get('user_has_no_right_for_action');
}

echo json_encode($data);