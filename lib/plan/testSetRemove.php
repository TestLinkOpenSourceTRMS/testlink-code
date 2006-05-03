<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * @version $Id: testSetRemove.php,v 1.12 2006/05/03 08:30:07 franciscom Exp $ 
 * 
 * Remove Test Cases from Test Case Suite 
 * 
 */         
require('../../config.inc.php');
require_once("../functions/common.php");
require_once("plan.inc.php");

// 20060430 - franciscom
require_once(dirname(__FILE__) . '/../functions/testsuite.class.php');
require_once(dirname(__FILE__) . '/../functions/testproject.class.php');
require_once(dirname(__FILE__) . '/../functions/tree.class.php');
require_once(dirname(__FILE__) . '/../functions/testplan.class.php');
require_once(dirname(__FILE__) . '/../functions/testcase.class.php');


testlinkInitPage($db);

$tree_mgr = New tree($db); 
$tsuite_mgr = New testsuite($db); 
$tplan_mgr = New testplan($db); 
$tcase_mgr = New testcase($db); 

$tplan_id =  $_SESSION['testPlanId'];
//$tproject_id =  $_SESSION['testprojectID'];


$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : null;
$version_id = isset($_REQUEST['version_id']) ? $_REQUEST['version_id'] : 0;
$level = isset($_REQUEST['level']) ? $_REQUEST['level'] : null;

$keyword_id = isset($_REQUEST['keyword_id']) ? $_REQUEST['keyword_id'] : 0;


$do_remove = isset($_POST['remove_tc']) ? 1 : 0;

$resultString = null;
$arrData = array();
// ---------------------------------------------------------------------------------------
if( $do_remove )
{
  $a_tc=isset($_POST['achecked_tc']) ? $_POST['achecked_tc'] : null;
  if( !is_null($a_tc) )
  {
      // remove without warning
      $tplan_mgr->unlink_tcversions($tplan_id,$a_tc);      
  }  
}
// ---------------------------------------------------------------------------------------


// ---------------------------------------------------------------------------------------
 
define('FILTER_BY_TC_OFF',null); 
define('WRITE_BUTTON_ONLY_IF_LINKED',1);

switch($level)
{
  case 'testcase':
  $out=null;
  
  if( !$do_remove ) 
  {
      
      // build the date need to call gen_spec_view
      $my_path=$tree_mgr->get_path($id);
      $idx_ts=count($my_path)-1;
      $tsuite_data=$my_path[$idx_ts-1];
      
      $pp=$tcase_mgr->get_versions_status_quo($id, $version_id);
      $linked_items[$id]=$pp[$version_id];
      $linked_items[$id]['testsuite_id']=$tsuite_data['id'];
      $linked_items[$id]['tc_id']=$id;
    
    
      $out=gen_spec_view($db,'testplan',
                         $tplan_id,$tsuite_data['id'],$tsuite_data['name'],
                         $linked_items,$keyword_id,FILTER_BY_TC_OFF,
                         WRITE_BUTTON_ONLY_IF_LINKED);
  }
  break;
  
  case 'testsuite':
  $tsuite_data=$tsuite_mgr->get_by_id($id);
  $out=gen_spec_view($db,'testplan',
                     $tplan_id,$id,$tsuite_data['name'],
                     $tplan_mgr->get_linked_tcversions($tplan_id,FILTER_BY_TC_OFF,$keyword_id),
                     $keyword_id,FILTER_BY_TC_OFF,WRITE_BUTTON_ONLY_IF_LINKED);
  break;

  
  default:
	// show instructions
	redirect( $_SESSION['basehref'] . $g_rpath['instructions'].'/testSetRemove.html');
  break;
  
}

$smarty = new TLSmarty;


$smarty->assign('has_tc', ($out['num_tc'] > 0 ? 1:0));
$smarty->assign('arrData', $out['spec_view']);
$smarty->assign('testPlanName', $_SESSION['testPlanName']);

$smarty->display('planRemoveTC_m1.tpl');
?>