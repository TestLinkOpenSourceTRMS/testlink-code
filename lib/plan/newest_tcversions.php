<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * @version $Id: newest_tcversions.php,v 1.4 2007/12/02 17:16:02 franciscom Exp $ 
 * 
 *
 * rev :
 *      20070930 - franciscom - added tplan combo box
 *
 */         
require('../../config.inc.php');
require_once("common.php");

testlinkInitPage($db);

$tree_mgr = new tree($db); 
$tsuite_mgr = new testsuite($db); 
$tplan_mgr = new testplan($db); 
$tcase_mgr = new testcase($db); 

$template_dir='plan/';
$user_id=$_SESSION['userID'];
$tproject_id =  $_SESSION['testprojectID'];
$tproject_name =  $_SESSION['testprojectName'];

$tplan_id = isset($_REQUEST['tplan_id']) ? $_REQUEST['tplan_id'] : $_SESSION['testPlanId'];

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : null;
$version_id = isset($_REQUEST['version_id']) ? $_REQUEST['version_id'] : 0;
$level = isset($_REQUEST['level']) ? $_REQUEST['level'] : null;
$keyword_id = isset($_REQUEST['keyword_id']) ? $_REQUEST['keyword_id'] : 0;
$user_feedback='';

$tplan_info=$tcase_mgr->get_by_id($tplan_id);
$tplan_name = $tplan_info['name'];


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



$tplans=getAccessibleTestPlans($db,$tproject_id,$user_id,1);
$map_tplans=array();
foreach($tplans as $key => $value)
{
  $map_tplans[$value['id']]=$value['name'];
}

$smarty = new TLSmarty();

$smarty->assign('tplans', $map_tplans);
$smarty->assign('tplan_id', $tplan_id);

$smarty->assign('show_details', $show_details );
$smarty->assign('user_feedback', $user_feedback);
$smarty->assign('testPlanName', $tplan_name);
$smarty->assign('tproject_name', $tproject_name);

$smarty->assign('testcases', $tcases);
$smarty->display($template_dir . 'newest_tcversions.tpl');
?>