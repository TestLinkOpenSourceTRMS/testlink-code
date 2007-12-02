<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: metrics_dashboard.php,v $
 *
 * @version $Revision: 1.3 $
 * @modified $Date: 2007/12/02 17:08:16 $ $Author: franciscom $
 *
 * @author franciscom
 *
 *20070907 - francisco.mancardi@gruppotesi.com
**/
require('../../config.inc.php');
require_once('common.php');
testlinkInitPage($db);

$template_dir='results/';

$tproject_name=$_SESSION['testprojectName'];
$tplan_metrics=getMetrics($db,$_SESSION['userID'],$_SESSION['testprojectID']);

$smarty = new TLSmarty;
$smarty->assign('tplan_metrics', $tplan_metrics);
$smarty->assign('tproject_name', $tproject_name);
$smarty->display($template_dir .'metrics_dashboard.tpl'); 
?>


<?php
function getMetrics(&$db,$user_id,$tproject_id)
{

  $linked_tcversions=array();
  $metrics=array();
  $tplan_mgr = new testplan($db);
  
  // get all tesplans accessibles  for user
  $test_plans = getAccessibleTestPlans($db,$tproject_id,$user_id);

  // Get count of testcases linked to every testplan
  foreach($test_plans as $key => $value)
  {
    $tplan_id=$value['id'];
    $linked_tcversions[$tplan_id] = $tplan_mgr->get_linked_tcversions($tplan_id);
    
    $metrics[$tplan_id]['tplan_name']=$value['name'];
    $metrics[$tplan_id]['executed']=0;
    $metrics[$tplan_id]['active']=0;
    $metrics[$tplan_id]['total']=0;

  }
  
  // Get count of executed testcases
  foreach($linked_tcversions as $tplan_id => $tc)
  {
    $metrics[$tplan_id]['executed']=0;
    $metrics[$tplan_id]['active']=0;
    $metrics[$tplan_id]['total']=0;
    $metrics[$tplan_id]['executed_vs_active']=-1;
    $metrics[$tplan_id]['executed_vs_total']=-1;
    $metrics[$tplan_id]['active_vs_total']=-1;
 
    if( !is_null($tc) )
    {
      foreach($tc as $key => $value)
      {
        if( $value['exec_id'] > 0 )
        {
          $metrics[$tplan_id]['executed']++;
        }
        if( $value['active'])
        {
          $metrics[$tplan_id]['active']++;
        }
        $metrics[$tplan_id]['total']++;
      } // foreach
    }   
  } // foreach
  
  
  // Calculate percentages
  foreach($metrics as $tplan_id => $value)
  {
    if( $metrics[$tplan_id]['total'] > 0 )
    {
      if( $metrics[$tplan_id]['active'] > 0 )
      {
        $metrics[$tplan_id]['executed_vs_active']=$metrics[$tplan_id]['executed']/$metrics[$tplan_id]['active'];
        $metrics[$tplan_id]['executed_vs_active'] *=100;
      }  
      $metrics[$tplan_id]['executed_vs_total']=$metrics[$tplan_id]['executed']/$metrics[$tplan_id]['total'];
      $metrics[$tplan_id]['executed_vs_total'] *=100;
      
      $metrics[$tplan_id]['active_vs_total']=$metrics[$tplan_id]['active']/$metrics[$tplan_id]['total'];
      $metrics[$tplan_id]['active_vs_total'] *=100;
    }
  } // foreach
  return $metrics;
}


?>
