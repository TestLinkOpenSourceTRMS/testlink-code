<?php
/** TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * 
 * @filesource $RCSfile: charts.php,v $
 * @version $Revision: 1.28 $
 * @modified $Date: 2009/10/25 19:22:53 $ by $Author: franciscom $
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
$gui->tplan_id=$_REQUEST['tplan_id'];
$tproject_id=$_SESSION['testprojectID'];
$tplan_info = $tplan_mgr->get_by_id($gui->tplan_id);
$tproject_info = $tproject_mgr->get_by_id($tproject_id);

$tplan_mgr->getStatusTotalsByPlatform($gui->tplan_id);
$gui->can_use_charts = checkLibGd();
$totals = $tplan_mgr->getStatusTotals($gui->tplan_id);


// $tplan_mgr->

if($gui->can_use_charts == 'OK')  
{
    $gui->tplan_name = $tplan_info['name'];
    $gui->tproject_name = $tproject_info['name'];
    
    $resultsCfg=config_get('results');
    
    // Save in session to improve perfomance.
    // This data will be used in different *chart.php to generate on the fly image
    unset($_SESSION['statistics']);
    
    $re=new results($db, $tplan_mgr, $tproject_info, $tplan_info,ALL_TEST_SUITES,ALL_BUILDS);
    // $_SESSION['statistics']['getTotalsForPlan']=$re->getTotalsForPlan();
    $_SESSION['statistics']['getTopLevelSuites'] = $re->getTopLevelSuites();
    $_SESSION['statistics']['getAggregateMap'] = $re->getAggregateMap();
    $_SESSION['statistics']['getAggregateOwnerResults'] = $re->getAggregateOwnerResults();
    $_SESSION['statistics']['getAggregateKeywordResults']= $re->getAggregateKeywordResults();

    // new dBug($_SESSION['statistics']);
    
    
    $pathToScripts = "lib/results/";
    $chartsUrl=new stdClass();
    $chartsUrl->overallPieChart = $pathToScripts . "overallPieChart.php?tplan_id={$gui->tplan_id}";
    $chartsUrl->keywordBarChart = $pathToScripts . "keywordBarChart.php";
    $chartsUrl->ownerBarChart = $pathToScripts . "ownerBarChart.php";
    $chartsUrl->topLevelSuitesBarChart = $pathToScripts . "topLevelSuitesBarChart.php";
    
    $platformSet = $tplan_mgr->getPlatforms($gui->tplan_id);
    $platformIDSet = is_null($platformSet) ? array(0) : array_keys($platformSet);

    $gui->charts = array(lang_get('overall_metrics') => $chartsUrl->overallPieChart);
    if(!is_null($platformSet))
    {
    	$label =lang_get('overall_metrics_for_platform');
    	foreach($platformIDSet as $platform_id)
    	{
    	    $description = $label .  ' ' . $platformSet[$platform_id];
    		$gui->charts[$description] = $pathToScripts . "platformPieChart.php?tplan_id={$gui->tplan_id}&platform_id={$platform_id}";
    	}
    }
    
    $gui->charts = array_merge( $gui->charts,
                         array(lang_get('results_by_keyword') => $chartsUrl->keywordBarChart,
                         lang_get('results_by_tester') => $chartsUrl->ownerBarChart,
                         lang_get('results_top_level_suites') => $chartsUrl->topLevelSuitesBarChart) );
}       

$smarty = new TLSmarty();
$smarty->assign("gui",$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);
?>