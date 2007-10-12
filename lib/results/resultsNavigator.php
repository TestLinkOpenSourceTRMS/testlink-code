<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 * @version $Id: resultsNavigator.php,v 1.37 2007/10/12 18:22:21 havlat Exp $ 
 * @author	Martin Havlat <havlat@users.sourceforge.net>
 * 
 * Launcher for Test Results and Metrics.
 *
 * rev :
 *      20070930 - franciscom - 
 *      20070916 - franciscom - added logic to choose test plan
 *      20070826 - franciscom - disable resultsImport
 */
require('../../config.inc.php');
require_once('common.php');
require_once('builds.inc.php');
testlinkInitPage($db);

$tplan_mgr = new testplan($db);

$do_report=array();
$do_report['status_ok']=1;
$do_report['msg']='';

//$selectedBuild = null;
$selectedReportType = null;

$arrReportTypes = array('normal', 'MS Excel', 'HTML email');

// function get_href_config(&$db,$bug_interface_on,$req_mgmt_enabled)
$href_map=get_href_config($db,config_get('bugInterfaceOn'),$_SESSION['testprojectOptReqs']);

$tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
$tplan_id = isset($_REQUEST['tplan_id']) ? $_REQUEST['tplan_id'] : $_SESSION['testPlanId'];

$tplans=getAccessibleTestPlans($db,$tproject_id,$_SESSION['userID'],1);
$map_tplans=array();
foreach($tplans as $key => $value)
{
  $map_tplans[$value['id']]=$value['name'];
}

$the_builds = $tplan_mgr->get_builds_for_html_options($tplan_id);
$linked_tcversions=$tplan_mgr->get_linked_tcversions($tplan_id);

// -----------------------------------------------------------------------------
// Do some checks to understand i reports can be build

// Check if there are linked test cases to the choosen test plan.
if( is_null($linked_tcversions) || count($linked_tcversions) == 0 )
{
   // Test plan without test cases
   $do_report['status_ok']=0;
   $do_report['msg']=lang_get('report_tplan_has_no_tcases');       
}

// Build qty
if( is_null($the_builds) || count($the_builds) == 0 )
{
   // Test plan without builds can have execution data
   $do_report['status_ok']=0;
   $do_report['msg']=lang_get('report_tplan_has_no_build');       
}
// -----------------------------------------------------------------------------


if($do_report['status_ok'])
{
  // $the_builds = array_reverse($the_builds, true);

  if (isset($_GET['report_type']))
	  $selectedReportType = intval($_GET['report_type']);
  else
	  $selectedReportType = sizeof($arrReportTypes) ? key($arrReportTypes) : null;
}

$workframe="";

if ( isset($_REQUEST['called_by_me']) )
{
  // Algorithm based on field order on URL call
  define('IDX_URL',0);
  define('IDX_QSTRING',1);

  define('IDX_REPORT_TYPE_ON_QSTRING',0);
  define('IDX_BUILD_ID_ON_QSTRING',1);
  define('IDX_TPLAN_ID_ON_QSTRING',2);
  
  $dummy=explode('?',$_REQUEST['called_url']);
  $qs=explode('&',$dummy[IDX_QSTRING]);
  
  if( $do_report['status_ok'] )
  {    
    $workframe = $dummy[IDX_URL] . "?" . 
                 $qs[IDX_REPORT_TYPE_ON_QSTRING] . "&" . 
                 $qs[IDX_BUILD_ID_ON_QSTRING] . "&tplan_id={$tplan_id}";
  }
  else
  {
    $workframe=$_SESSION['basehref'] . "lib/general/show_help.php" .
                                       "?help=showMetrics&locale={$_SESSION['locale']}";
  }             
}



$smarty = new TLSmarty;

$smarty->assign('workframe', $workframe);
$smarty->assign('do_report', $do_report);
$smarty->assign('title', lang_get('title_nav_results'));
$smarty->assign('arrData', $href_map['general_reports']);
//$smarty->assign('arrDataB', $href_map['build_reports']);
//$smarty->assign('arrBuilds', $the_builds);
$smarty->assign('tplans', $map_tplans);

$smarty->assign('tplan_id', $tplan_id);

$smarty->assign('selectedReportType', $selectedReportType);
$smarty->assign('arrReportTypes', $arrReportTypes);
//$smarty->assign('email_to', $email_to);
$smarty->display('resultsNavigator.tpl');
?>



<?php
function get_href_config(&$db,$bug_interface_on,$req_mgmt_enabled)
{
  $tc_status_map=config_get('tc_status');
  $map_r=array();
  
  // there is list of available results and metrics view
  $arrData = array(
	array('name' => lang_get('link_report_general_tp_metrics'), 
	      'href' => 'resultsGeneral.php?report_type='), 
	array('name' => lang_get('link_report_overall_build'), 
	      'href' => 'resultsAllBuilds.php?report_type='), 
  array('name' => lang_get('link_report_metrics_more_builds'), 
        'href' => 'resultsMoreBuilds.php?report_type='), 
	array('name' => lang_get('link_report_failed'), 
        'href' => "resultsByStatus.php?type={$tc_status_map['failed']}&amp;report_type="),
	array('name' => lang_get('link_report_blocked_tcs'), 
	                'href' => "resultsByStatus.php?type={$tc_status_map['blocked']}&amp;report_type="),
	array('name' => lang_get('link_report_not_run'), 
	                'href' => "resultsByStatus.php?type={$tc_status_map['not_run']}&amp;report_type="),	
	array('name' => lang_get('link_report_test'), 'href' => 'resultsTC.php?report_type='),
	array('name' => lang_get('link_charts'), 'href' => 'charts.php?report_type='));
	
	// 20070826 - has problems
	// array('name' => lang_get('link_results_import'), 'href' => 'resultsImport.php?report_type='));
	
	// not ready yet
	// array('name' => lang_get('time_charts'), 'href' => 'timeCharts.php?report_type=')

  if ($bug_interface_on)
  {
	 $arrData[] = array('name' => lang_get('link_report_total_bugs'), 
	                    'href' => 'resultsBugs.php?report_type=');
  }

  // if ($_SESSION['testprojectOptReqs']
  if ($req_mgmt_enabled && has_rights($db,"mgt_view_req"))
  {
	  $arrData[] = array('name' => lang_get('link_report_reqs_coverage'), 
	                     'href' => 'resultsReqs.php?report_type=');
  }

  // this results are related to selected build
  $arrDataB = array(
	  array('name' => lang_get('link_report_metrics_active_build'), 'href' => 'resultsBuild.php'),
  );

  
  $map_r['general_reports']=$arrData;
  $map_r['build_reports']=$arrDataB;
  
  return $map_r;
}  
?>