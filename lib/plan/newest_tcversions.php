<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * @version $Id: newest_tcversions.php,v 1.1 2007/05/02 07:30:30 franciscom Exp $ 
 * 
 */         
require('../../config.inc.php');
require_once("../functions/common.php");
require_once("plan.inc.php");

testlinkInitPage($db);

$tree_mgr = new tree($db); 
$tsuite_mgr = new testsuite($db); 
$tplan_mgr = new testplan($db); 
$tcase_mgr = new testcase($db); 
$tplan_id = $_SESSION['testPlanId'];
$tplan_name = $_SESSION['testPlanName'];

$tproject_id =  $_SESSION['testprojectID'];
$tproject_name =  $_SESSION['testprojectName'];


$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : null;
$version_id = isset($_REQUEST['version_id']) ? $_REQUEST['version_id'] : 0;
$level = isset($_REQUEST['level']) ? $_REQUEST['level'] : null;
$keyword_id = isset($_REQUEST['keyword_id']) ? $_REQUEST['keyword_id'] : 0;
$user_feedback='';

$linked_tcases=$tplan_mgr->get_linked_tcversions($tplan_id);
$tcases=$tplan_mgr->get_linked_and_newest_tcversions($tplan_id);

$qty_linked=count($linked_tcases);
$qty_newest=count($tcases);

$show_details=0;
if($qty_linked > 0)
{
  if($qty_newest > 0)
  {
    $show_details=1;  
  } 
  else
  {
    $user_feedback=lang_get('no_newest_version_of_linked_tcversions');  
  }
} 
else
{
  $user_feedback=lang_get('no_linked_tcversions');  
}


$smarty = new TLSmarty();


$smarty->assign('show_details', $show_details );
$smarty->assign('user_feedback', $user_feedback);
$smarty->assign('testPlanName', $tplan_name);
$smarty->assign('testcases', $tcases);
$smarty->display('newest_tcversions.tpl');
?>