<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: resultsMoreBuilds_buildReport.php,v 1.13 2005/09/26 00:48:36 kevinlevy Exp $ 
*
* @author	Kevin Levy <kevinlevy@users.sourceforge.net>
* 
* This page show Metrics a test plan based on a start build,
* end build, keyword, test plan id, and owner.
* @author  Francisco Mancardi - 20050905 refactoring
*/

require('../../config.inc.php');
require_once('common.php');
require_once('../functions/resultsMoreBuilds.inc.php');
require_once('../functions/builds.inc.php');
require_once('builds.inc.php');
require_once('results.inc.php');
testlinkInitPage();

$tpName = isset($_GET['testPlanName']) ? strings_stripSlashes($_GET['testPlanName']) : null;  
$tpID = isset($_SESSION['testPlanId']) ? $_SESSION['testPlanId'] : 0;  
$keyword = isset($_GET['keyword']) ? strings_stripSlashes($_GET['keyword']) : null;  
$owner = isset($_GET['owner']) ? strings_stripSlashes($_GET['owner']) : null;  
$lastStatus = isset($_GET['lastStatus']) ? strings_stripSlashes($_GET['lastStatus']) : null;  

$buildsSelected = array();
$componentsSelected = array();

$xls = FALSE;
$xls_localized_string = lang_get('excel_format');

if (isset($_GET['format']) && $_GET['format'] == '$xls_localized_string')
{

  $xls = TRUE;

} 

if (isset($_REQUEST['build']))

{

  foreach($_REQUEST['build'] AS $val)
    
  {
    
      $buildsSelected[] = $val;
  
  }

}

if (isset($_REQUEST['component']))

{

  foreach($_REQUEST['component'] AS $val)
    
    {
    
      $componentsSelected[] = $val;
    
    }

}

$a2check = array('build','keyword','owner','testPlanName','testPlanName',"lastStatus"); 

if( !check_hash_keys($_GET, $a2check, "is not defined in \$GET")) 

{

      exit;

}


tlTimingStart();

$reportData = createResultsForTestPlan($_GET['testPlanName'],$_SESSION['testPlanId'], $buildsSelected, $_GET['keyword'], $_GET['owner'], $_GET['lastStatus'], $xls, $componentsSelected);
tlTimingStop();
$queryParameters = $reportData[0];
$summaryOfResults = $reportData[1];
$allComponentData = $reportData[2];

/*  

var_dump(strlen($summaryOfResults));  

var_dump(strlen($allComponentData));  

var_dump(tlTimingCurrent());  

*/  


//echo tlTimingCurrent();
//var_dump(strlen($allComponentData));
// display smarty
$smarty = new TLSmarty();
$smarty->assign('queryParameters', $queryParameters);
$smarty->assign('summaryOfResults', $summaryOfResults);
$smarty->assign('allComponentData', $allComponentData);
$smarty->assign('xls', $xls);
// for excel send header
if ($xls) {

  sendXlsHeader();

  $smarty->assign('printDate', strftime($g_date_format, time()) ); 

  $smarty->assign('user', $_SESSION['user']);

 }

// this contains example of how this excel data gets used
// $smarty->display('resultsTC.tpl');


$smarty->display('resultsMoreBuilds_report.tpl');

?>