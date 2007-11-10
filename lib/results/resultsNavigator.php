<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 * @version $Id: resultsNavigator.php,v 1.38 2007/11/10 02:52:38 havlat Exp $ 
 * @author	Martin Havlat <havlat@users.sourceforge.net>
 * 
 * Scope: Launcher for Test Results and Metrics.
 *
 * rev :
 *      20071109 - havlatm - move data to config + refactorization; removed obsolete build list
 * 							 move functin into class  
 *      20070930 - franciscom - 
 *      20070916 - franciscom - added logic to choose test plan
 *      20070826 - franciscom - disable resultsImport
 * 
 * TODO: 
 * 		get_builds_for_html_options() ==> reports->get_count_builds() 
 **/
 
 
require('../../config.inc.php');
require_once('common.php');
require_once('builds.inc.php');
require_once('reports.class.php');
testlinkInitPage($db);


$do_report=array();
$do_report['status_ok']=1;
$do_report['msg']='';
$selectedReportType = null;

$tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
$tplan_id = isset($_REQUEST['tplan_id']) ? $_REQUEST['tplan_id'] : $_SESSION['testPlanId'];
$btsEnabled = config_get('bugInterfaceOn');

$tplan_mgr = new testplan($db);
$reports_magic = new reports($db, $tplan_id);

// create a list or reports
$href_map = $reports_magic->get_list_reports($btsEnabled ,$_SESSION['testprojectOptReqs']);


$tplans = getAccessibleTestPlans($db,$tproject_id,$_SESSION['userID'],1);
$map_tplans=array();
foreach($tplans as $key => $value)
{
  $map_tplans[$value['id']]=$value['name'];
}


// -----------------------------------------------------------------------------
// Do some checks to understand i reports can be build

// Check if there are linked test cases to the choosen test plan.
$linked_tcversions=$tplan_mgr->get_linked_tcversions($tplan_id);
if( is_null($linked_tcversions) || count($linked_tcversions) == 0 )
{
   // Test plan without test cases
   $do_report['status_ok']=0;
   $do_report['msg']=lang_get('report_tplan_has_no_tcases');       
}

// Build qty
$the_builds = $tplan_mgr->get_builds_for_html_options($tplan_id);
if( is_null($the_builds) || count($the_builds) == 0 )
{
   // Test plan without builds can have execution data
   $do_report['status_ok']=0;
   $do_report['msg']=lang_get('report_tplan_has_no_build');       
}
// -----------------------------------------------------------------------------


if($do_report['status_ok'])
{
  if (isset($_GET['report_type']))
	  $selectedReportType = intval($_GET['report_type']);
  else
	  $selectedReportType = sizeof($g_reports_cfg->formats) ? key($g_reports_cfg->formats) : null;
}

$workframe="";

// get data from navigator reload
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
$smarty->assign('arrData', $href_map);
$smarty->assign('tplans', $map_tplans);
$smarty->assign('arrReportTypes', $g_reports_cfg->formats);

$smarty->assign('tplan_id', $tplan_id);
$smarty->assign('selectedReportType', $selectedReportType);

$smarty->display('resultsNavigator.tpl');
?>
