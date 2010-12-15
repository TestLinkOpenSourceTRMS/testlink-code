<?php
/** 
* 	TestLink Open Source Project - http://testlink.sourceforge.net/
* 
* 	@version 	$Id: getreqlog.php,v 1.1.2.5 2010/12/15 21:48:13 mx-julian Exp $
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
	$sql = "SELECT log_message FROM {$tables[$target_table]} WHERE id=" . intval($item_id);
	$info = $db->get_recordset($sql);
    $info = nl2br($info[0]['log_message']);
    
    // <p> and </p> tag at the beginning and the end of summary cause visualization
    // errors -> remove them and add <br> to get a similar effect
    $info = str_replace("<p>","",$info);
    $info = str_replace("</p>","<br>",$info);
    
    // if log message is empty show this information
    if ($info == "") {
    	$info = lang_get("empty_log_message");
    }
}
echo $info;