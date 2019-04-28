<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/
* 
* @filesource dragdroprequirementnodes.php
* @author   Francisco Mancardi
* 
* manage drag and drop on requirement specification tree
*
*/
require_once('../../config.inc.php');
require_once('common.php');
testlinkInitPage($db);


$args=init_args();
$treeMgr = new tree($db);

switch($args->doAction) {
  case 'changeParent':
    $treeMgr->change_parent($args->nodeid,$args->newparentid);
    $sql = " UPDATE " . DB_TABLE_PREFIX . "requirements " .
           " SET srs_id=" . intval($args->newparentid) .
           " WHERE id=" . intval($args->nodeid);
    $db->exec_query($sql);
  break;

  case 'doReorder':
    $dummy = explode(',',$args->nodelist);
    $treeMgr->change_order_bulk($dummy);
  break;
}

/**
 *
 */
function init_args() {
  $args=new stdClass();
  
  $key2loop=array('nodeid','newparentid','nodeorder');
  foreach($key2loop as $key) {
    $args->$key = isset($_REQUEST[$key]) ? intval($_REQUEST[$key]) : null;   
  }

  $key2loop = array('doAction','top_or_bottom','nodelist');
  foreach($key2loop as $key) {
    $args->$key = isset($_REQUEST[$key]) ? $_REQUEST[$key] : null;   
  }

  return $args;
}                                                                                            