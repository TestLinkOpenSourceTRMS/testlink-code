<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource	charts.php
 * @package 	TestLink
 * @author 		Francisco Mancardi (francisco.mancardi@gmail.com)
 * @copyright 	2005-2012, TestLink community 
 * @link 		http://www.teamst.org/index.php
 *
 * @internal revisions
 * @since 1.9.4
 * 20120602 - franciscom - TICKET 5041: Charts - Report by Tester - REMOVE / DEPRECATED
 *
 */
require_once('../../config.inc.php');
require_once('common.php');

testlinkInitPage($db);
$templateCfg = templateConfiguration();
$gui=new stdClass();

$l18n = init_labels(array('overall_metrics' => null,'overall_metrics_for_platform' => null,
						  'results_by_keyword' => null,'results_top_level_suites' => null));

$tplan_mgr = new testplan($db);
$tproject_mgr = new testproject($db);
$gui->tplan_id = $_REQUEST['tplan_id'];
$tproject_id = $_SESSION['testprojectID'];
$tplan_info = $tplan_mgr->get_by_id($gui->tplan_id);
$tproject_info = $tproject_mgr->get_by_id($tproject_id);

$gui->can_use_charts = checkLibGd();

if($gui->can_use_charts == 'OK')  
{
    $gui->tplan_name = $tplan_info['name'];
    $gui->tproject_name = $tproject_info['name'];
    
    $resultsCfg=config_get('results');
    
    $pathToScripts = "lib/results/";
    $chartsUrl=new stdClass();
    $chartsUrl->overallPieChart = $pathToScripts . "overallPieChart.php?tplan_id={$gui->tplan_id}";
    $chartsUrl->keywordBarChart = $pathToScripts . "keywordBarChart.php?tplan_id={$gui->tplan_id}" .
    									           "&tproject_id=$tproject_id";
    $chartsUrl->topLevelSuitesBarChart = $pathToScripts . 
    									 "topLevelSuitesBarChart.php?tplan_id={$gui->tplan_id}" .
    									 "&tproject_id=$tproject_id";
    
    $platformSet = $tplan_mgr->getPlatforms($gui->tplan_id,array('outputFormat' => 'map'));
    $platformIDSet = is_null($platformSet) ? array(0) : array_keys($platformSet);

    $gui->charts = array($l18n['overall_metrics'] => $chartsUrl->overallPieChart);
    if(!is_null($platformSet))
    {
    	foreach($platformIDSet as $platform_id)
    	{
    	    $description = $l18n['overall_metrics_for_platform'] .  ' ' . $platformSet[$platform_id];
    		$gui->charts[$description] = $pathToScripts . 
    									 "platformPieChart.php?tplan_id={$gui->tplan_id}&platform_id={$platform_id}";
    	}
    }
    
    $gui->charts = array_merge( $gui->charts,
                         array($l18n['results_by_keyword'] => $chartsUrl->keywordBarChart,
                         	   $l18n['results_top_level_suites'] => $chartsUrl->topLevelSuitesBarChart) );
}       

$smarty = new TLSmarty();
$smarty->assign("gui",$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);
?>