<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Direct links for external access to reports
 *
 * How this feature works:
 * 
 * @package   TestLink
 * @author    franciscom
 * @copyright 2012,2017 TestLink community
 * @link      http://www.testlink.org/
 */

// some session and settings stuff from original index.php 
require_once('config.inc.php');
require_once('./cfg/reports.cfg.php');
require_once('common.php');

doDBConnect($db);
$args = init_args($db);
switch($args->light)
{
  case 'red':
    // can not find user or item 
  break;

  case 'green':
    $reportCfg = config_get('reports_list');
    $what2launch = null; 
    $cfg = isset($reportCfg[$args->type]) ? $reportCfg[$args->type] : null;
    
    switch($args->type)
    {
      case 'exec':
        $what2launch = "lib/execute/execPrint.php" .
                       "?id={$args->id}&apikey=$args->apikey";
      break;

      case 'file':
        $what2launch = "lib/attachments/attachmentdownload.php" .
                       "?id={$args->id}&apikey=$args->apikey";
      break;

      case 'metricsdashboard':
        $what2launch = "lib/results/metricsDashboard.php?apikey=$args->apikey";
      break;


      case 'test_report':
        $param = "&type={$args->type}&level=testproject" .
                 "&tproject_id={$args->tproject_id}&tplan_id={$args->tplan_id}" .
                 "&header=y&summary=y&toc=y&body=y&passfail=y&cfields=y&metrics=y&author=y" .
                 "&requirement=y&keyword=y&notes=y&headerNumbering=y&format=" . FORMAT_HTML;
        $what2launch = "lib/results/printDocument.php?apikey=$args->apikey{$param}";         
      break;
      
      case 'testreport_onbuild':
        $param = "&type={$args->type}&level=testproject" .
                 "&tproject_id={$args->tproject_id}&tplan_id={$args->tplan_id}&build_id={$args->build_id}" .
                 "&header=y&summary=y&toc=y&body=y&passfail=y&cfields=y&metrics=y&author=y" .
                 "&requirement=y&keyword=y&notes=y&headerNumbering=y&format=" . FORMAT_HTML;
        $what2launch = "lib/results/printDocument.php?apikey=$args->apikey{$param}";         
      break;

      case 'test_plan':
        $param = "&type={$args->type}&level=testproject" .
                 "&tproject_id={$args->tproject_id}&tplan_id={$args->tplan_id}" .
                 "&header=y&summary=y&toc=y&body=y&passfail=n&cfields=y&metrics=y&author=y" .
                 "&requirement=y&keyword=y&notes=y&headerNumbering=y&format=" . FORMAT_HTML;
        $what2launch = "lib/results/printDocument.php?apikey=$args->apikey{$param}";         
      break;
      
      case 'testspec':
        $param = "&type={$args->type}&level=testproject&id={$args->tproject_id}" .
                 "&tproject_id={$args->tproject_id}" .
                 "&header=y&summary=y&toc=y&body=y&cfields=y&author=y".
                 "&requirement=y&keyword=y&headerNumbering=y&format=" . FORMAT_HTML;    
        $what2launch = $cfg['url'] . "?apikey=$args->apikey{$param}";
      break;
      
      
      case 'metrics_tp_general':
        $param = "&tproject_id={$args->tproject_id}&tplan_id={$args->tplan_id}" .
                 "&format=" . FORMAT_HTML;
        $what2launch = $cfg['url'] . "?apikey=$args->apikey{$param}";
      break;
  
      case 'list_tc_failed':
      case 'list_tc_blocked':
      case 'list_tc_not_run':
        $param = "&tproject_id={$args->tproject_id}&tplan_id={$args->tplan_id}" .
                 "&format={$args->format}";
        $what2launch = $cfg['url'] ."&apikey=$args->apikey{$param}";
      break;
      
      case 'results_matrix';
        $param = "&tproject_id={$args->tproject_id}&tplan_id={$args->tplan_id}" .
                 "&format={$args->format}";
        $what2launch = $cfg['url'] ."?apikey=$args->apikey{$param}";
      break;


      case 'results_by_tester_per_build';
        $param = "&tproject_id={$args->tproject_id}&tplan_id={$args->tplan_id}&format=" . FORMAT_HTML;
        $what2launch = $cfg['url'] ."?apikey=$args->apikey{$param}";
      break;
      
      case 'charts_basic':
        $param = "&tproject_id={$args->tproject_id}&tplan_id={$args->tplan_id}&format=" . FORMAT_HTML;
        $what2launch = $cfg['url'] ."?apikey=$args->apikey{$param}";
      break;
      
      
      default:
        $needle = 'list_tc_';
        $nl = strlen($needle);
        if(strpos($key,$needle) !== FALSE)
        {
          $param = "&tproject_id={$args->tproject_id}&tplan_id={$args->tplan_id}" .
                   "&format={$args->format}";
          $what2launch = $cfg['url'] ."&apikey=$args->apikey{$param}";
        }  
        else
        {
          echo 'ABORTING - UNKNOWN TYPE:' . $args->type;
          die(); 
        }  
      break;
    }  
  
    if(!is_null($what2launch))
    {
      // changed to be able to get XLS file using wget
      // redirect(TL_BASE_HREF . $what2launch);
      //echo $what2launch;
      //die();
      header('Location:' . TL_BASE_HREF . $what2launch);
      exit();
    }
  break;
  
  default:
    // ??
  break;
  
} 



/**
 *
 */
function init_args(&$dbHandler)
{
  $_REQUEST = strings_stripSlashes($_REQUEST);
  $args = new stdClass();

  try
  {
    // ATTENTION - give a look to $tlCfg->reports_list
    // format domain: see reports.cfg.php FORMAT_*
    $typeSize = 30;
    $userAPIkeyLen = 32;
    $objectAPIkeyLen = 64;

    $iParams = array("apikey" => array(tlInputParameter::STRING_N,
                                       $userAPIkeyLen,$objectAPIkeyLen),
                     "tproject_id" => array(tlInputParameter::INT_N),
                     "tplan_id" => array(tlInputParameter::INT_N),
                     "level" => array(tlInputParameter::STRING_N,0,16),
                     "type" => array(tlInputParameter::STRING_N,0,$typeSize),
                     'id' => array(tlInputParameter::INT_N),
                     'format' => array(tlInputParameter::STRING_N,0,1));  
  }
  catch (Exception $e)  
  {  
    echo $e->getMessage();
    exit();
  }
                  
  R_PARAMS($iParams,$args);

  $args->format = intval($args->format);
  $args->format = ($args->format <= 0) ? FORMAT_HTML : $args->format;

  $args->envCheckMode = $args->type == 'file' ? 'hippie' : 'paranoic';
  $args->light = 'red';
  $opt = array('setPaths' => true,'clearSession' => true);
  
  // validate apikey to avoid SQL injection
  $args->apikey = trim($args->apikey);
  $akl = strlen($args->apikey);
  
  switch($akl)
  {
    case $userAPIkeyLen:
    case $objectAPIkeyLen:
    break;

    default:
     throw new Exception("Aborting - Bad API Key lenght", 1);
    break;  
  }

  if($akl == $userAPIkeyLen)
  {
    $args->debug = 'USER-APIKEY';
    setUpEnvForRemoteAccess($dbHandler,$args->apikey,null,$opt);
    $user = tlUser::getByAPIKey($dbHandler,$args->apikey);
    $args->light = (count($user) == 1) ? 'green' : 'red';
  }
  else
  {
    if(is_null($args->type) || trim($args->type) == '')
    {
      throw new Exception("Aborting - Bad type", 1);
    } 

    if($args->type == 'exec')
    {
      $tex = DB_TABLE_PREFIX . 'executions';
      $sql = "SELECT testplan_id FROM $tex WHERE id=" . intval($args->id);
      $rs = $dbHandler->get_recordset($sql);
      if( is_null($rs) )
      {
        die(__FILE__ . '-' . __LINE__);
      }  

      $rs = $rs[0];
      $tpl = DB_TABLE_PREFIX . 'testplans';
      $sql = "SELECT api_key FROM $tpl WHERE id=" . intval($rs['testplan_id']);
      $rs = $dbHandler->get_recordset($sql);
      if( is_null($rs) )
      {
        die(__FILE__ . '-' . __LINE__);
      }  
      $rs = $rs[0];
      $args->apikey = $rs['api_key'];
      $args->envCheckMode = 'hippie';
    }  

    $args->debug = 'OBJECT-APIKEY';
    $kerberos = new stdClass();
    $kerberos->args = $args;
    $kerberos->method = null;

    if( setUpEnvForAnonymousAccess($dbHandler,$args->apikey,$kerberos,$opt) )
    {
      $args->light = 'green';
    }
  }
  return $args;
}
