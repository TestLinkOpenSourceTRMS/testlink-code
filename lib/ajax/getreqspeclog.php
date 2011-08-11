<?php
/** 
 * 	TestLink Open Source Project - http://testlink.sourceforge.net/
 * 
 * 	@filesource	getreqspeclog.php
 * 	@author 	Francisco Mancardi
 * 
 *	@internal Revisions:
 */
require_once('../../config.inc.php');
require_once('common.php');
testlinkInitPage($db);

$item_id = isset($_REQUEST['item_id']) ? intval($_REQUEST['item_id']): null;
$info = '';
if( !is_null($item_id) )
{
	$tables = tlObjectWithDB::getDBTables(array('req_specs_revisions'));
	$target_table = 'req_specs_revisions';
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