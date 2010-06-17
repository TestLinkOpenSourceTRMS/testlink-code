<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @filesource $RCSfile: resultsBugs.php,v $
 * @version $Revision: 1.35 $
 * @modified $Date: 2010/06/17 06:56:14 $ by $Author: erikeloff $
 * @author kevinlevy
 * 
 * rev :
 *	20100616 - eloff - refactor out results class
 *	20100124 - eloff - BUGID 3012 - don't show internal id in report
 *	20080413 - franciscom - refactoring + BUGID 1477 
 *	20070827 - franciscom - BUGID 994
 */


require('../../config.inc.php');
require_once("lang_api.php");
require_once('displayMgr.php');
require_once('exec.inc.php'); // used for bug string lookup
if (config_get('interface_bugs') != 'NO')
{
  require_once(TL_ABS_PATH. 'lib' . DIRECTORY_SEPARATOR . 'bugtracking' .
               DIRECTORY_SEPARATOR . 'int_bugtracking.php');
}
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

$filters = array();
$options = array('output' => 'array', 'only_executed' => true, 'details' => 'full');
$results = $tplan_mgr->get_linked_tcversions($args->tplan_id, $filters, $options);

$testcase_bugs = array();
foreach ($results as $execution) {
	$tc_id = $execution['tc_id'];
	$mine[] = $execution['exec_id'];
	$exec_id = $execution['exec_id'];
	$bug_urls = buildBugString($db, $exec_id, $openBugs, $resolvedBugs);
	if ($bug_urls)
	{
		// First bug found for this tc
		if (!isset($testcase_bugs[$tc_id])) {
			$suiteName = $execution['tsuite_name'];
			$tc_name = buildExternalIdString($tproject_info['prefix'], $execution['external_id']) . ":" . $execution['name'];
			$testcase_bugs[$tc_id] = array($suiteName, $tc_name, array());
		}
		foreach ($bug_urls as $url)
		{
			if (!in_array($url, $testcase_bugs[$tc_id][2]))
			{
				$testcase_bugs[$tc_id][2][] = $url;
			}
		}
	}
}
foreach ($testcase_bugs as &$row)
{
	$row[2] = implode("<br/>", $row[2]);
}
$arrData = array_values($testcase_bugs);

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
$smarty->assign('totalOpenBugs', $totalOpenBugs);
$smarty->assign('totalResolvedBugs', $totalResolvedBugs);
$smarty->assign('totalBugs', $totalBugs);
$smarty->assign('totalCasesWithBugs', $totalCasesWithBugs);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);

/**
 * Register the bug for counting.
 */
function registerBug($bugID, $bugInfo, &$openBugsArray, &$resolvedBugsArray)
{
	$linkString = $bugInfo['link_to_bts'];
	$position = strpos($linkString,"<del>");
	$position2 = strpos($linkString,"</del>");
	if ((!$position) && (!$position2))
	{
		if (!in_array($bugID, $openBugsArray))
			$openBugsArray[] = $bugID;
	}
	else if (!in_array($bugID, $resolvedBugsArray))
	{
		$resolvedBugsArray[] = $bugID;
   	} 
}


/**
 * Get links to bugs related to execution.
 * @param $db
 * @param $execID execution id
 * @param $openBugsArray array to count open bugs
 * @param $resolvedBugsArray array to count resolved bugs
 *
 * @return array List of links to related bugs
 */
function buildBugString(&$db,$execID,&$openBugsArray,&$resolvedBugsArray)
{
	$bugUrls = array();
	$bugInterface = config_get('bugInterface');
	if ($bugInterface)
	{
		$bugs = get_bugs_for_exec($db,$bugInterface,$execID);
		if ($bugs)
		{
			foreach($bugs as $bugID => $bugInfo)
			{
				registerBug($bugID, $bugInfo, $openBugsArray, $resolvedBugsArray);
				$bugUrls[] = $bugInfo['link_to_bts'];
			}
		}
	}
	return $bugUrls;
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
