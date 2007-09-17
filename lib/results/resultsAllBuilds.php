<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: resultsAllBuilds.php,v 1.17 2007/09/17 06:29:07 franciscom Exp $ 
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

$tplan_mgr = new testplan($db);
$tproject_mgr = new testproject($db);

$tplan_id=$_REQUEST['tplan_id'];
$tproject_id=$_SESSION['testprojectID'];

$tplan_info = $tplan_mgr->get_by_id($tplan_id);
$tproject_info = $tproject_mgr->get_by_id($tproject_id);

$tplan_name = $tplan_info['name'];
$tproject_name = $tproject_info['name'];

$arrData = null;
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

if( $do_report['status_ok'] )
{
  $arrDataBuilds = $re->getAggregateBuildResults();
  $i = 0;
  if ($arrDataBuilds != null) {
    while ($buildId = key($arrDataBuilds)) {
     $arr = $arrDataBuilds[$buildId];
     //% not run := 100 - percentage completed
     $arr[9] = 100 - $arr[9]; 
     $arrData[$i] = $arr;
     $i++;
     next($arrDataBuilds);
    }
  }
}  

$smarty = new TLSmarty;
$smarty->assign('do_report', $do_report);
$smarty->assign('tcs_css', $g_tc_status_css);
$smarty->assign('title', lang_get('title_metrics_x_build'));
$smarty->assign('tproject_name', $tproject_name);
$smarty->assign('tplan_name', $tplan_name);
$smarty->assign('arrData', $arrData);

$report_type = isset($_GET['report_type']) ? intval($_GET['report_type']) : null;
displayReport('resultsAllBuilds', $smarty, $report_type);

?>