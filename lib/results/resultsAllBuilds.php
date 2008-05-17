<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: resultsAllBuilds.php,v 1.21 2008/05/17 17:41:08 franciscom Exp $ 
*
* @author	Martin Havlat <havlat@users.sourceforge.net>
* 
* This page show Test Results over all Builds.
*
* @author Kevin Levy 20061029 - 1.7 upgrate
*/

require('../../config.inc.php');
require_once('common.php');
require_once('../functions/results.class.php');
require_once('displayMgr.php');
testlinkInitPage($db);

$template_dir='results/';

$args = init_args();
$tplan_mgr = new testplan($db);
$tproject_mgr = new testproject($db);


$tplan_info = $tplan_mgr->get_by_id($args->tplan_id);
$tproject_info = $tproject_mgr->get_by_id($args->tproject_id);

$tplan_name = $tplan_info['name'];
$tproject_name = $tproject_info['name'];

$do_report=array();
$do_report['status_ok']=1;
$do_report['msg']='';

$re = new results($db, $tplan_mgr, $tproject_info, $tplan_info,
                  ALL_TEST_SUITES,ALL_BUILDS);


$topLevelSuites = $re->getTopLevelSuites();

$do_report=array();
$do_report['status_ok']=1;
$do_report['msg']='';

if( is_null($topLevelSuites) )
{
  $do_report['status_ok']=0;
  $do_report['msg']=lang_get('report_tspec_has_no_tsuites');
}

$results=null;
if( $do_report['status_ok'] )
{
  $results = $re->getAggregateBuildResults();
  if ($results != null) 
  {
      $dummy=current($results);
      $colDefinition=$dummy['details'];
      
      // Get labels
      $resultsCfg=config_get('results');
      $labels=$resultsCfg['status_label'];
      foreach($colDefinition as $status_verbose => $value)
      {
            $l18n_label = isset($labels[$status_verbose]) ? lang_get($labels[$status_verbose]) : 
                          lang_get($status_verbose); 
            
            $colDefinition[$status_verbose]['qty']=$l18n_label;
            $colDefinition[$status_verbose]['percentage']='[%]';
      }
  }    
}  

$smarty = new TLSmarty;
$smarty->assign('do_report', $do_report);
$smarty->assign('tcs_css', $g_tc_status_css);
$smarty->assign('title', lang_get('title_metrics_x_build'));
$smarty->assign('tproject_name', $tproject_name);
$smarty->assign('tplan_name', $tplan_name);
$smarty->assign('colDefinition', $colDefinition);
$smarty->assign('results',$results);

displayReport($template_dir . 'resultsAllBuilds', $smarty, $args->format);



/*
  function: init_args 

  args:
  
  returns: 

*/
function init_args()
{
    $_REQUEST = strings_stripSlashes($_REQUEST);
    $args=new stdClass();
    $args->tplan_id=$_REQUEST['tplan_id'];
    $args->tproject_id=$_SESSION['testprojectID'];
    $args->format = isset($_REQUEST['format']) ? intval($_REQUEST['format']) : null;
    return $args;
}
?>