<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @filesource $RCSfile: resultsBugs.php,v $
 * @version $Revision: 1.32 $
 * @modified $Date: 2009/06/04 19:22:01 $ by $Author: schlundus $
 * @author kevinlevy
 * 
 * rev :
 *      20080413 - franciscom - refactoring + BUGID 1477 
 *      20070827 - franciscom - BUGID 994
 */


require('../../config.inc.php');
require_once('results.class.php');
require_once("lang_api.php");
require_once('displayMgr.php');
testlinkInitPage($db,true,false,"checkRights");

$templateCfg = templateConfiguration();
$args = init_args();

$openBugs = array();
$resolvedBugs = array();
$arrData = array();

$tplan_mgr = new testplan($db);
$tproject_mgr = new testproject($db);

$tplan_info = $tplan_mgr->get_by_id($args->tplan_id);
$tproject_info = $tproject_mgr->get_by_id($args->tproject_id);
$re = new results($db, $tplan_mgr, $tproject_info, $tplan_info,ALL_TEST_SUITES,ALL_BUILDS);
$arrBuilds = $tplan_mgr->get_builds($args->tplan_id); 
$executionsMap = $re->getSuiteList();

// lastResultMap provides list of all test cases in plan - data set includes title and suite names
$lastResultMap = $re->getMapOfLastResult();
$indexOfArrData = 0;

// be sure to check if last result map is null or not before accessing
if ($lastResultMap) {
	while($suiteId = key($lastResultMap)) {
		$currentSuiteInfo = $lastResultMap[$suiteId];
		$timestampInfo = null;
		$bugInfo = null;
		while ($testCaseId = key($currentSuiteInfo)){
			// initialize bugInfo
			// $allTimeStamps = array();
			// initialize list of bugs associated with this testCaseId
			$allBugLinks = array();
			$currentTestCaseInfo = $currentSuiteInfo[$testCaseId];
			$suiteName = $currentTestCaseInfo['suiteName'];
			$name = $currentTestCaseInfo['name'];		
			$suiteExecutions = $executionsMap[$suiteId];
			$rowArray = array($suiteName, $testCaseId . ":" . $name);
			for ($i = 0; $i < sizeOf($suiteExecutions); $i++) {
				$currentExecution = $suiteExecutions[$i];
				if ($currentExecution['testcaseID'] == $testCaseId) {
					$executions_id = $currentExecution['executions_id'];
					// initialize bug associated with an execution
					$bugLink = null;
					if ($executions_id) {
						$bugLink = buildBugString($db, $executions_id, $openBugs, $resolvedBugs);
					}
					if ($bugLink) {
						if (!in_array($bugLink, $allBugLinks)) {
							array_push($allBugLinks, $bugLink);
							//array_push($allTimeStamps, $currentTimeStamp);
						}
					}
				}
			}		
			//array_push($rowArray, $timestampInfo);
			$allBugLinksString = implode("", $allBugLinks);
			//$allTimeStampsString = implode("<BR>", $allTimeStamps);
			array_push($rowArray, $allBugLinksString);
			
			// KL - 20070610
			$onlyShowTCsWithBugs = true;
			if (($allBugLinksString) && ($onlyShowTCsWithBugs)) {
				$arrData[$indexOfArrData] = $rowArray;
				$indexOfArrData++;
			}
				
			next($currentSuiteInfo);		
		}  // end while
		next($lastResultMap);
	} // end while
} // end if

$totalOpenBugs = count($openBugs);
$totalResolvedBugs = count($resolvedBugs);
$totalBugs = $totalOpenBugs + $totalResolvedBugs;
$totalCasesWithBugs = count($arrData);

$smarty = new TLSmarty;
$smarty->assign('user',$args->user);
$smarty->assign('printDate','');
$smarty->assign('tproject_name', $tproject_info['name']);
$smarty->assign('tplan_name', $tplan_info['name'] );
$smarty->assign('title', lang_get('link_report_total_bugs'));
$smarty->assign('arrData', $arrData);
$smarty->assign('arrBuilds', $arrBuilds);
$smarty->assign('totalOpenBugs', $totalOpenBugs);
$smarty->assign('totalResolvedBugs', $totalResolvedBugs);
$smarty->assign('totalBugs', $totalBugs);
$smarty->assign('totalCasesWithBugs', $totalCasesWithBugs);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);

/*
  function: 

  args :
  
  returns: 

*/
function registerBug($bugID, $bugInfo, &$openBugsArray, &$resolvedBugsArray)
{
	$linkString = $bugInfo['link_to_bts'];
	$position = strpos($linkString,"<del>");
	$position2 = strpos($linkString,"</del>");
	if ((!$position) && (!$position2))
	{
		$bugArray = $openBugsArray;
	}
	else
	{
		$bugArray = $resolvedBugsArray;
   	} 
   	tallyBug($bugID, $bugArray);
}

/*
  function: 

  args :
  
  returns: 

*/
function tallyBug($bugID, &$array) 
{
	if (!in_array($bugID, $array)) {
		array_push($array, $bugID);
	}
}


/*
  function: 

  args :
  
  returns: 

*/
function buildBugString(&$db,$execID,&$openBugsArray,&$resolvedBugsArray)
{
	$bugString = null;
  	$bugInterface = config_get('bugInterface');
  	if ($bugInterface)
  	{
  		$bugs = get_bugs_for_exec($db,$bugInterface,$execID);
		if ($bugs)
		{
			foreach($bugs as $bugID => $bugInfo)
			{
			  registerBug($bugID, $bugInfo, $openBugsArray, $resolvedBugsArray);
				$bugString .= $bugInfo['link_to_bts']."<br />";
			}
		}
  	}
	return $bugString;
}


/*
  function: init_args()

  args :
  
  returns: 

*/
function init_args()
{
	$iParams = array(
		"format" => array(tlInputParameter::INT_N),
		"tplan_id" => array(tlInputParameter::INT_N),
   	);
	$args = new stdClass();
	$pParams = R_PARAMS($iParams,$args);
	
	$args->tproject_id = $_SESSION['testprojectID'];
	$args->user = $_SESSION['currentUser'];

	return $args;
}

function checkRights(&$db,&$user)
{
	return $user->hasRight($db,'testplan_metrics');
}
?>