<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Direct links for external access to reports
 *
 *
 * How this feature works:
 * 
 * @package 	TestLink
 * @author 		franciscom
 * @copyright 2012, TestLink community
 * @link 		  http://www.teamst.org/index.php
 * @since     1.9.5
 *
 * @internal revisions
 *
 */

// some session and settings stuff from original index.php 
require_once('config.inc.php');
require_once('./cfg/reports.cfg.php');
require_once('common.php');

$args = init_args($db);
$user = tlUser::getByAPIKey($db,$args->apikey);
$userCount = count($user);
new dBug($args);

switch($userCount)
{
  case 0:
    // can not find user 
  break;

  case 1:
    $reportCfg = config_get('reports_list');
    $what2launch = null; 
    $cfg = isset($reportCfg[$args->type]) ? $reportCfg[$args->type] : null;
    
    switch($args->type)
    {
      case 'metricsdashboard':
        $param =  "&tproject_id={$args->tproject_id}";
  			$what2launch = "lib/results/metricsDashboard.php?apikey=$args->apikey{$param}";
      break;


      case 'test_report':
        $param = "&type={$args->type}" .
                 "&tproject_id={$args->tproject_id}&tplan_id={$args->tplan_id}" .
                 "&header=y&summary=y&toc=y&body=y&passfail=y&cfields=y&metrics=y&author=y" .
                 "&requirement=y&keyword=y&notes=y&headerNumbering=y&format=0";
  			$what2launch = "lib/results/printDocument.php?apikey=$args->apikey{$param}";
      
      break;
      
      case 'testspec':
        $param = "&type={$args->type}&level=testproject&id={$args->tproject_id}" .
                 "&tproject_id={$args->tproject_id}" .
                 "&header=y&summary=y&toc=y&body=y&cfields=y&author=y".
                 "&requirement=y&keyword=y&headerNumbering=y&format=0";    
  			$what2launch = "lib/results/printDocument.php?apikey=$args->apikey{$param}";
      break;
      
      
      case 'metrics_tp_general':
        $param = "&tproject_id={$args->tproject_id}&tplan_id={$args->tplan_id}" .
                 "&format=0";
  			$what2launch = $cfg['url'] . "?apikey=$args->apikey{$param}";
      break;
  
      case 'list_tc_failed':
      case 'list_tc_blocked':
      case 'list_tc_norun':
        $param = "&tproject_id={$args->tproject_id}&tplan_id={$args->tplan_id}&format=0";
  			$what2launch = $cfg['url'] ."&apikey=$args->apikey{$param}";
      break;
      
      case 'results_matrix';
        $param = "&tproject_id={$args->tproject_id}&tplan_id={$args->tplan_id}";
  			$what2launch = $cfg['url'] ."?apikey=$args->apikey{$param}";
      break;

      case 'results_by_tester_per_build';
        $param = "&tproject_id={$args->tproject_id}&tplan_id={$args->tplan_id}&format=0";
  			$what2launch = $cfg['url'] ."?apikey=$args->apikey{$param}";
      break;
      
      case 'charts_basic':
        $param = "&tproject_id={$args->tproject_id}&tplan_id={$args->tplan_id}&format=0";
  			$what2launch = $cfg['url'] ."?apikey=$args->apikey{$param}";
      break;
      
      
      default:
        echo 'ABORTING - UNKNOWN TYPE:' . $args->type;
        die(); 
      break;
    }  
  
    if(!is_null($what2launch))
    {
    		redirect(TL_BASE_HREF . $what2launch);
  			exit();
    }
  break;
  
  default:
    // too many users
  break;
  
} 


function init_args(&$dbHandler)
{
	$_REQUEST = strings_stripSlashes($_REQUEST);
	$args = new stdClass();

  try
  {
    // ATTENTION - give a look to $tlCfg->reports_list
    $typeSize = 30;
  	$iParams = array("apikey" => array(tlInputParameter::STRING_N,32,32),
  	                 "tproject_id" => array(tlInputParameter::INT_N),
  	                 "tplan_id" => array(tlInputParameter::INT_N),
  	                 "level" => array(tlInputParameter::STRING_N,0,16),
  	                 "type" => array(tlInputParameter::STRING_N,0,$typeSize));  
	}
  catch (Exception $e)  
  {  
    echo $e->getMessage();
    exit();
  }

	                
	R_PARAMS($iParams,$args);
  setUpEnvForRemoteAccess($dbHandler,$args->apikey,null,array('setPaths' => true,'clearSession' => true));
  return $args;
}
?>