<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource	charts.php
 * @package 	  TestLink
 * @author 		  Francisco Mancardi (francisco.mancardi@gmail.com)
 * @copyright 	2005-2013, TestLink community 
 * @link 		    http://www.teamst.org/index.php
 *
 * @internal revisions
 * @since 1.9.10
 *
 */
require_once('../../config.inc.php');
require_once('common.php');

$templateCfg = templateConfiguration();

$l18n = init_labels(array('overall_metrics' => null,'overall_metrics_for_platform' => null,
						              'results_by_keyword' => null,'results_top_level_suites' => null));

list($args,$tproject_mgr,$tplan_mgr) = init_args($db);

$tplan_info = $tplan_mgr->get_by_id($args->tplan_id);
$tproject_info = $tproject_mgr->get_by_id($args->tproject_id);

$gui = initializeGui($args);
if($gui->can_use_charts == 'OK')  
{
    $gui->tplan_name = $tplan_info['name'];
    $gui->tproject_name = $tproject_info['name'];
    
    $resultsCfg = config_get('results');
    
    $pathToScripts = "lib/results/";
    $chartsUrl=new stdClass();
    $chartsUrl->overallPieChart = $pathToScripts . "overallPieChart.php?apikey={$args->apikey}&tplan_id={$gui->tplan_id}";
    $chartsUrl->keywordBarChart = $pathToScripts . "keywordBarChart.php?apikey={$args->apikey}&tplan_id={$gui->tplan_id}" .
    									           "&tproject_id=$args->tproject_id";
    $chartsUrl->topLevelSuitesBarChart = $pathToScripts . 
    									                   "topLevelSuitesBarChart.php?apikey={$args->apikey}&tplan_id={$gui->tplan_id}" .
    									                   "&tproject_id=$args->tproject_id";
    
    $platformSet = $tplan_mgr->getPlatforms($gui->tplan_id,array('outputFormat' => 'map'));
    $platformIDSet = is_null($platformSet) ? array(0) : array_keys($platformSet);

    $gui->charts = array($l18n['overall_metrics'] => $chartsUrl->overallPieChart);
    if(!is_null($platformSet))
    {
    	foreach($platformIDSet as $platform_id)
    	{
    	  $description = $l18n['overall_metrics_for_platform'] .  ' ' . $platformSet[$platform_id];
    		$gui->charts[$description] = $pathToScripts . 
    									 "platformPieChart.php?apikey={$args->apikey}&tplan_id={$gui->tplan_id}&platform_id={$platform_id}";
    	}
    }
    
    $gui->charts = array_merge( $gui->charts,
                         array($l18n['results_by_keyword'] => $chartsUrl->keywordBarChart,
                         	   $l18n['results_top_level_suites'] => $chartsUrl->topLevelSuitesBarChart) );
}       

$smarty = new TLSmarty();
$smarty->assign("gui",$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);



/**
 * initialize user input
 * 
 * @param resource dbHandler
 * @return array $args array with user input information
 */
function init_args(&$dbHandler)
{
  $iParams = array("apikey" => array(tlInputParameter::STRING_N,0,64),
                   "tproject_id" => array(tlInputParameter::INT_N), 
	                 "tplan_id" => array(tlInputParameter::INT_N),
                   "format" => array(tlInputParameter::INT_N));

	$args = new stdClass();
	$pParams = R_PARAMS($iParams,$args);
  if( !is_null($args->apikey) )
  {
    $cerbero = new stdClass();
    $cerbero->args = new stdClass();
    $cerbero->args->tproject_id = $args->tproject_id;
    $cerbero->args->tplan_id = $args->tplan_id;

    if(strlen($args->apikey) == 32)
    {
      $cerbero->args->getAccessAttr = true;
      $cerbero->method = 'checkRights';
      $cerbero->redirect_target = "../../login.php?note=logout";
      setUpEnvForRemoteAccess($dbHandler,$args->apikey,$cerbero);
    }
    else
    {
      $args->addOpAccess = false;
      $cerbero->method = null;
      $cerbero->args->getAccessAttr = false;
      setUpEnvForAnonymousAccess($dbHandler,$args->apikey,$cerbero);
    }  
  }
  else
  {
    testlinkInitPage($dbHandler,false,false,"checkRights");  
	  $args->tproject_id = isset($_SESSION['testprojectID']) ? intval($_SESSION['testprojectID']) : 0;
  }

  $tproject_mgr = new testproject($dbHandler);
  $tplan_mgr = new testplan($dbHandler);
	if($args->tproject_id > 0) 
	{
		$args->tproject_info = $tproject_mgr->get_by_id($args->tproject_id);
		$args->tproject_name = $args->tproject_info['name'];
		$args->tproject_description = $args->tproject_info['notes'];
	}
	
	if ($args->tplan_id > 0) 
	{
		$args->tplan_info = $tplan_mgr->get_by_id($args->tplan_id);
	}
	
	return array($args,$tproject_mgr,$tplan_mgr);
}

/**
 *
 */
function initializeGui($argsObj)
{
  $gui=new stdClass();
  $gui->tplan_id = $argsObj->tplan_id;
  $gui->can_use_charts = checkLibGd();
  return $gui;
}



/*
 * rights check function for testlinkInitPage()
 */
function checkRights(&$db,&$user,$context = null)
{
  if(is_null($context))
  {
    $context = new stdClass();
    $context->tproject_id = $context->tplan_id = null;
    $context->getAccessAttr = false; 
  }

  $check = $user->hasRight($db,'testplan_metrics',$context->tproject_id,$context->tplan_id,$context->getAccessAttr);
  return $check;
}