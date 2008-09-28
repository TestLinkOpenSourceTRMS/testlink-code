<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: metricsDashboard.php,v $
 *
 * @version $Revision: 1.4 $
 * @modified $Date: 2008/09/28 10:04:43 $ $Author: franciscom $
 *
 * @author franciscom
 *
 * rev:
 *     20080928 - franciscom - refactoring
 *     20080124 - franciscom - BUGID 1321
 *     20070907 - franciscom
**/
require('../../config.inc.php');
require_once('common.php');
testlinkInitPage($db);
$templateCfg = templateConfiguration();

$gui=new stdClass();
$gui->tproject_name = $_SESSION['testprojectName'];
$user_id = $_SESSION['userID'];
$tproject_id = $_SESSION['testprojectID'];

$gui->tplan_metrics = getMetrics($db,$user_id,$tproject_id);

$smarty = new TLSmarty;
$smarty->assign('gui', $gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template); 
?>


<?php
function getMetrics(&$db,$user_id,$tproject_id)
{
  $linked_tcversions = array();
  $metrics = array();
  $tplan_mgr = new testplan($db);
  
  
  // BUGID 1215
  // get all tesplans accessibles  for user, for $tproject_id
  $test_plans = getAccessibleTestPlans($db,$tproject_id,$user_id,FILTER_BY_PRODUCT);

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
  $round_precision=config_get('dashboard_precision');
  foreach($metrics as $tplan_id => $value)
  {
    if( $metrics[$tplan_id]['total'] > 0 )
    {
      if( $metrics[$tplan_id]['active'] > 0 )
      {
        $metrics[$tplan_id]['executed_vs_active']=$metrics[$tplan_id]['executed']/$metrics[$tplan_id]['active'];
        $metrics[$tplan_id]['executed_vs_active'] *=100;
        $metrics[$tplan_id]['executed_vs_active'] = round($metrics[$tplan_id]['executed_vs_active'],$round_precision);
      }  
      $metrics[$tplan_id]['executed_vs_total']=$metrics[$tplan_id]['executed']/$metrics[$tplan_id]['total'];
      $metrics[$tplan_id]['executed_vs_total'] *=100;
      $metrics[$tplan_id]['executed_vs_total'] = round($metrics[$tplan_id]['executed_vs_total'],$round_precision);
      
      $metrics[$tplan_id]['active_vs_total']=$metrics[$tplan_id]['active']/$metrics[$tplan_id]['total'];
      $metrics[$tplan_id]['active_vs_total'] *=100;
      $metrics[$tplan_id]['active_vs_total'] = round($metrics[$tplan_id]['active_vs_total'],$round_precision);
    }
  } // foreach
  return $metrics;
}


?>
