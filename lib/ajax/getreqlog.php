<?php
/** 
* 	TestLink Open Source Project - http://testlink.sourceforge.net/
* 
* 	@version 	$Id: getreqlog.php,v 1.1.2.1 2010/12/12 14:17:22 franciscom Exp $
* 	@author 	Francisco Mancardi
* 
*   Used on Add/Remove test case to test plan feature, to display summary via ExtJS tooltip
*
*	@internal Revisions:
*/
require_once('../../config.inc.php');
require_once('common.php');
testlinkInitPage($db);

$reqMgr = new requirement_mgr($db);
$item_id = isset($_REQUEST['item_id']) ? intval($_REQUEST['item_id']): null;
$info = '';
if( !is_null($item_id) )
{
	$tables = tlObjectWithDB::getDBTables(array('req_versions','req_revisions'));

	// get item type
	$node_types = $reqMgr->tree_mgr->get_available_node_types();
	$dummy = $reqMgr->tree_mgr->get_node_hierarchy_info($item_id);
	
	
	$target_table = 'req_revisions';
	if($dummy['node_type_id'] == $node_types['requirement_version'] )
	{
		$target_table = 'req_versions';
	}
	$sql = "SELECT log_messages FROM {$tables[$target_table]} WHERE id=" . intval($item_id);
	$info = $db->get_recordset($sql);
    $info = $info[0]['log_message']; 
}
echo $info;