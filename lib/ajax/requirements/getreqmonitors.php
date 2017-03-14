<?php
/** 
 *  TestLink Open Source Project - http://testlink.sourceforge.net/
 * 
 *  @filesource getreqmonitors.php
 *  @author   Francisco Mancardi
 * 
 *  @internal revisions
 */
require_once('../../../config.inc.php');
require_once('common.php');
testlinkInitPage($db);

$item_id = isset($_REQUEST['item_id']) ? intval($_REQUEST['item_id']): null;

$ou = new stdClass();
$ou->data = array();
if( !is_null($item_id) )
{
  $req_mgr = new requirement_mgr($db);
  $opt = array('output' => 'array');
  $mon = $req_mgr->getReqMonitors($item_id,$opt);
  
  $ou = new stdClass();
  $ou->data = $mon;
}
echo json_encode($ou);