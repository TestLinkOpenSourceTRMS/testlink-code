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
 * @copyright 	2010, TestLink community
 * @version    	CVS: $Id: checkDuplicateName.php,v 1.2 2010/12/01 14:37:08 asimon83 Exp $
 *
 * @internal Revisions:
 * 20100225 - eloff - initial commit
 *
 **/

require_once('../../config.inc.php');
require_once('common.php');
testlinkInitPage($db);
$data = array('success' => true, 'message' => '');

// take care of proper escaping when magic_quotes_gpc is enabled
$_REQUEST=strings_stripSlashes($_REQUEST);

$iParams = array(	"name" => array(tlInputParameter::STRING_N,0,100),
	                "testcase_id" => array(tlInputParameter::INT));
$args = G_PARAMS($iParams);

if($_SESSION['currentUser']->hasRight($db, 'mgt_view_tc'))
{
	$tree_manager = new tree($db);

	$node_types_descr_id=$tree_manager->get_available_node_types();
	$my_node_type=$node_types_descr_id['testcase'];
	$name = $args['name'];
	$tc_id = $args['testcase_id'];

	$check = $tree_manager->nodeNameExists($name, $my_node_type, $tc_id);

	$data['success'] = !$check['status'];
	$data['message'] = $check['msg'];
}
else
{
	tLog('Invalid right for the user: '.$args['right'], 'ERROR');
	$data['success'] = false;
	$data['message'] = lang_get('Invalid right');
}

echo json_encode($data);
?>