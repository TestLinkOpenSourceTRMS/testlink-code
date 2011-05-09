<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource	charts.php
 * @package 	TestLink
 * @author 		Francisco Mancardi (francisco.mancardi@gmail.com)
 * @copyright 	2005-2011, TestLink community 
 * @link 		http://www.teamst.org/index.php
 *
 * @internal revisions
 * 20100716 - eloff - BUGID 3562: include bug tracking if activated
 * 20100221 - franciscom - fixed call to getPlatforms()	
 */
require_once('../../config.inc.php');
require_once('common.php');
if (config_get('interface_bugs') != 'NO')
{
  require_once(TL_ABS_PATH. 'lib' . DIRECTORY_SEPARATOR . 'bugtracking' .
               DIRECTORY_SEPARATOR . 'int_bugtracking.php');
}
testlinkInitPage($db);
$templateCfg = templateConfiguration();

$gui=new stdClass();

$tplan_mgr = new testplan($db);
$tproject_mgr = new testproject($db);
$gui->tplan_id = intval($_REQUEST['tplan_id']);
$gui->tproject_id = intval($_REQUEST['tproject_id']);
$tplan_info = $tplan_mgr->get_by_id($gui->tplan_id);
$tproject_info = $tproject_mgr->get_by_id($gui->tproject_id);

// ??
// $tplan_mgr->getStatusTotalsByPlatform($gui->tplan_id);
$gui->can_use_charts = checkLibGd();
$totals = $tplan_mgr->getStatusTotals($gui->tplan_id);


if($gui->can_use_charts == 'OK')  
{
    $gui->tplan_name = $tplan_info['name'];
    $gui->tproject_name = $tproject_info['name'];
    
    $resultsCfg=config_get('results');
    
    // Save in session to improve perfomance.
    // This data will be used in different *chart.php to generate on the fly image
    unset($_SESSION['statistics']);
    
    $re=new results($db, $tplan_mgr, $tproject_info, $tplan_info,ALL_TEST_SUITES,ALL_BUILDS,ALL_PLATFORMS);
    $_SESSION['statistics']['getTopLevelSuites'] = $re->getTopLevelSuites();
    $_SESSION['statistics']['getAggregateMap'] = $re->getAggregateMap();
    $_SESSION['statistics']['getAggregateOwnerResults'] = $re->getAggregateOwnerResults();
    $_SESSION['statistics']['getAggregateKeywordResults']= $re->getAggregateKeywordResults();
    
    $pathToScripts = "lib/results/";
    $tprojectTplanInfo = "?tproject_id={$gui->tproject_id}&tplan_id={$gui->tplan_id}";
    $chartsUrl=new stdClass();
    $chartsUrl->overallPieChart = $pathToScripts . "overallPieChart.php" . $tprojectTplanInfo;
    $chartsUrl->keywordBarChart = $pathToScripts . "keywordBarChart.php" . $tprojectTplanInfo;
    $chartsUrl->ownerBarChart = $pathToScripts . "ownerBarChart.php" . $tprojectTplanInfo;
    $chartsUrl->topLevelSuitesBarChart = $pathToScripts . "topLevelSuitesBarChart.php" . $tprojectTplanInfo;
    
    $platformSet = $tplan_mgr->getPlatforms($gui->tplan_id,array('outputFormat' => 'map'));
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