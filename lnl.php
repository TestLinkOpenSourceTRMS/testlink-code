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
require_once('common.php');

$args = init_args($db);
$user = tlUser::getByAPIKey($db,$args->apikey);
$userCount = count($user);
switch($userCount)
{
  case 0:
    // can not find user 
  break;

  case 1:
    $what2launch = null; 
    switch($args->type)
    {
      case 'testreport':
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
      
      case 'metricsdashboard':
        $param =  "&tproject_id={$args->tproject_id}";
  			$what2launch = "lib/results/metricsDashboard.php?apikey=$args->apikey{$param}";
      break;
      
      case 'resultsgeneral':
        $param = "&tproject_id={$args->tproject_id}&tplan_id={$args->tplan_id}" .
                 "&format=0";
  			$what2launch = "lib/results/resultsGeneral.php?apikey=$args->apikey{$param}";
      break;
  
      case 'failedtestcases':
        $param = "&tproject_id={$args->tproject_id}&tplan_id={$args->tplan_id}" .
                 "&type=f&format=0";
  			$what2launch = "lib/results/resultsByStatus.php?apikey=$args->apikey{$param}";
      break;
  
      case 'notruntestcases':
        $param = "&tproject_id={$args->tproject_id}&tplan_id={$args->tplan_id}" .
                 "&type=n&format=0";
  			$what2launch = "lib/results/resultsByStatus.php?apikey=$args->apikey{$param}";
      break;

      case 'blockedtestcases':
        $param = "&tproject_id={$args->tproject_id}&tplan_id={$args->tplan_id}" .
                 "&type=b&format=0";
  			$what2launch = "lib/results/resultsByStatus.php?apikey=$args->apikey{$param}";
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
  	$iParams = array("apikey" => array(tlInputParameter::STRING_N,32,32),
  	                 "tproject_id" => array(tlInputParameter::INT_N),
  	                 "tplan_id" => array(tlInputParameter::INT_N),
  	                 "level" => array(tlInputParameter::STRING_N,0,16),
  	                 "type" => array(tlInputParameter::STRING_N,0,20));  
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