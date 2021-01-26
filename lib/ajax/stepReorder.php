<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/
* 
* @filesource stepReorder.php
* @author 	  Francisco Mancardi
* 
* manage reorder of test case steps done using
* https://github.com/isocra/TableDnD
*
*/


require_once('../../config.inc.php');
require_once('common.php');
testlinkInitPage($db);
$args = init_args();
$tcaseMgr = new testcase($db);

// No authorization checks
if ($args->stepSeq != '') {  
  $xx = explode('&', $args->stepSeq);
  $point = 1;
  $renumbered = [];
  foreach($xx as $step_id) {
    $renumbered[$step_id] = $point++; 
  }

  // Get test case version id from 1 step
  $nt = $tcaseMgr->tree_manager->get_available_node_types();
  $tables = tlObjectWithDB::getDBTables(array('tcsteps','nodes_hierarchy'));
  $sql = "SELECT NH_STEPS.parent_id 
          FROM {$tables['nodes_hierarchy']} NH_STEPS 
          WHERE NH_STEPS.id = {$xx[0]} 
          AND NH_STEPS.node_type_id = {$nt['testcase_step']}";

  $tcaseMgr->set_step_number($renumbered);
  file_put_contents('/var/testlink/logs/stepReorder.log', json_encode($renumbered));

  echo json_encode($renumbered);
}

/**
 *
 */
function init_args()
{
  $args = new stdClass();    
  $args->stepSeq = isset($_REQUEST["stepSeq"])? $_REQUEST["stepSeq"] : "";

  return $args;
}