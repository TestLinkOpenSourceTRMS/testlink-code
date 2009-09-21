<?php
/** TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * 
 * @filesource $RCSfile: charts.php,v $
 * @version $Revision: 1.26 $
 * @modified $Date: 2009/09/21 09:29:56 $ by $Author: franciscom $
 * @author kevin
 *
 * Revisions:
 *  20081121 - franciscom - BUGID - added check of needed PHP extensions to avoid blank page
 *  20081027 - franciscom - refactored to use pChart and improve performance
 *	20080812 - havlatm - simplyfied, polite
 *
 **/
require_once('../../config.inc.php');
require_once('common.php');
testlinkInitPage($db);
$templateCfg = templateConfiguration();

$gui=new stdClass();

$tplan_mgr = new testplan($db);
$tproject_mgr = new testproject($db);
$tplan_id=$_REQUEST['tplan_id'];
$tproject_id=$_SESSION['testprojectID'];
$tplan_info = $tplan_mgr->get_by_id($tplan_id);
$tproject_info = $tproject_mgr->get_by_id($tproject_id);

$gui->can_use_charts = checkLibGd();

if($gui->can_use_charts == 'OK')  
{
    $gui->tplan_name = $tplan_info['name'];
    $gui->tproject_name = $tproject_info['name'];
    
    $resultsCfg=config_get('results');
    
    // Save in session to improve perfomance.
    // This data will be used in different *chart.php to generate on the fly image
    unset($_SESSION['statistics']);
    
    $re=new results($db, $tplan_mgr, $tproject_info, $tplan_info,ALL_TEST_SUITES,ALL_BUILDS);
    $_SESSION['statistics']['getTotalsForPlan']=$re->getTotalsForPlan();
    $_SESSION['statistics']['getTopLevelSuites'] = $re->getTopLevelSuites();
    $_SESSION['statistics']['getAggregateMap'] = $re->getAggregateMap();
    $_SESSION['statistics']['getAggregateOwnerResults'] = $re->getAggregateOwnerResults();
    $_SESSION['statistics']['getAggregateKeywordResults']= $re->getAggregateKeywordResults();
    
    
    new dBug($_SESSION['statistics']['getTotalsForPlan']);
    
    
    $pathToScripts = "lib/results/";
    $chartsUrl=new stdClass();
    $chartsUrl->overallPieChart = $pathToScripts . "overallPieChart.php";
    $chartsUrl->keywordBarChart = $pathToScripts . "keywordBarChart.php";
    $chartsUrl->ownerBarChart = $pathToScripts . "ownerBarChart.php";
    $chartsUrl->topLevelSuitesBarChart = $pathToScripts . "topLevelSuitesBarChart.php";
    
    $gui->charts = array(lang_get('overall_metrics') => $chartsUrl->overallPieChart,
                         lang_get('results_by_keyword') => $chartsUrl->keywordBarChart,
                         lang_get('results_by_tester') => $chartsUrl->ownerBarChart,
                         lang_get('results_top_level_suites') => $chartsUrl->topLevelSuitesBarChart );
           
}       

$smarty = new TLSmarty();
$smarty->assign("gui",$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);
?>