<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: execNavigator.php,v $
 *
 * @version $Revision: 1.22 $
 * @modified $Date: 2006/05/16 19:35:40 $ by $Author: schlundus $
 *
 * @author Martin Havlat
 *
 *	@todo names of com, cat, TC should be grab from test specification tables
 *	@todo keywords are not collected from keywords table but from Test case suite
 *
 * 20060429 - franciscom - generateExecTree() move to treeMenu.inc.php
 * 20050807 - fm changes in filterKeyword() call.
 * 20050807 - fm changes in createBuildMenu() call.
 * 20050815 - scs - optimized and reducing Sql statements
 * 20050828 - scs - reduced the code
 * 20050828 - scs - added searching for tcID
 * 20050907 - scs - fixed problem with wrong coloring
 * 20051011 - MHT - fixed $g_tc_status['not_run'] + minor refactorization
 **/
 
require_once('../../config.inc.php');
require_once('common.php');
require_once('treeMenu.inc.php');
require_once('exec.inc.php');
require_once('builds.inc.php');
testlinkInitPage($db);


$treeColored = (isset($_POST['colored']) && ($_POST['colored'] == 'result')) ? 'selected="selected"' : null;
$selectOwner = (isset($_POST['owner']) && ($_POST['owner'] == $_SESSION['user'])) ? 'selected="selected"' : null;
$filterOwner = array (array('id' => $_SESSION['user'], 'selected' => $selectOwner));
$tc_id = isset($_POST['tcID']) ? intval($_POST['tcID']) : null;
$keyword_id = isset($_POST['keyword_id']) ? $_POST['keyword_id'] : 0;             

$tplan_id   = isset($_SESSION['testPlanId']) ? $_SESSION['testPlanId'] : 0;
$tplan_name = isset($_SESSION['testPlanName']) ? $_SESSION['testPlanName'] : 'xxx';
$tplan_mgr = new testplan($db);
$optBuild = $tplan_mgr->get_builds_for_html_options($tplan_id);
$optBuildSelected = isset($_POST['build_id']) ? $_POST['build_id'] : key($optBuild);

$tproject_id   = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
$tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : 'xxx';

$kw_map = $tplan_mgr->get_keywords_map($_SESSION['testPlanId'],' order by keyword ');
if(!is_null($kw_map))
{
  // add the blank option
  // 0 -> id for no keyword
  $blank_map[0] = '';
  $keywords_map = $blank_map+$kw_map;
}

$menuUrl = null;
$SP_html_help_file = TL_INSTRUCTIONS_RPATH . $_SESSION['locale'] . "/executeTest.html";

$menuUrl = 'lib/execute/execSetResults.php';
$getArguments = '&build_id=' . $optBuildSelected;
if ($keyword_id)
	$getArguments .= '&keyword_id='.$keyword_id;
if ($tc_id)
	$getArguments .= '&tc_id='.$tc_id;
	
$sMenu = generateExecTree($db,$menuUrl,$tproject_id,$tproject_name,$tplan_id,$tplan_name,
                          $optBuildSelected,$getArguments,$keyword_id,$tc_id);

                     
$tree = invokeMenu($sMenu);
$tcData = null;
$testCaseID = null;
$optResult = null;
$optResultSelected = null;
$testCaseID = null;



$smarty = new TLSmarty();
$smarty->assign('treeKind', TL_TREE_KIND);
$smarty->assign('treeColored', $treeColored);
$smarty->assign('optBuild', $optBuild);
$smarty->assign('optBuildSelected', $optBuildSelected);
$smarty->assign('optResult', $optResult);
$smarty->assign('optResultSelected', $optResultSelected); 
$smarty->assign('arrOwner', $filterOwner);
$smarty->assign('keywords_map', $keywords_map);
$smarty->assign('keyword_id', $keyword_id);
$smarty->assign('tcID', intval($tc_id) > 0 ? $tc_id : '');
$smarty->assign('testCaseID',$testCaseID);
$smarty->assign('tcIDFound', $tcData ? 1 : 0);
$smarty->assign('tree', $tree);
$smarty->assign('menuUrl',$menuUrl);
$smarty->assign('args',$getArguments);
$smarty->assign('SP_html_help_file',$SP_html_help_file);
$smarty->display('execNavigator.tpl');



/**
 * Function that add all or filtered of the test cases
 *
 * @author Andreas Morsing - optimized SQL and reduced number of SQL-Statements
 */
function DEPR_displayTCTree(&$db,$TCResult, $build, $owner, $colored, $menuUrl, $filteredResult, $dtreeCategoryId)
{
	global $g_tc_status;
	$data = null;
	
	$bFilteredResultAll = ($filteredResult == 'all');
	$tcIDs = null;
	$tcInfo = null;
	while ($myrowTC = $db->fetch_array($TCResult))
	{
		$name = $myrowTC[1];
		$tcID = $myrowTC[0];
		$mgttcid = $myrowTC[2];
	
		$tcIDs[] = $tcID;	
		$tcInfo[$tcID] = array($name,$mgttcid);
	}
	$notRunStatus = $g_tc_status['not_run'];
	$tcStatusInfo = null;
	if ($tcIDs)
	{
		$tcIDsList = implode(",",$tcIDs);
		// 20051015 - kl - only get results from builds in test plan
   // 10152005 - kl - only get results execute on test plan builds
		$csBuilds = get_cs_builds($db,$_SESSION['testPlanId']);
		$sqlResult = "SELECT tcid,status FROM results WHERE tcid IN (" . $tcIDsList . ") " .
		  " AND (results.build_id IN (" . $csBuilds . " )) ";
		if($colored == 'result')
		{
			$sqlResult .= " ORDER BY build_id DESC";
		}
		else
		{
			$sqlResult .= " AND build_id = " . $build;
		}
		
		$sqlBuildResult = $db->exec_query($sqlResult);
		//I need the num results so I can do the check below on not run test cases
		while($myrowTC = $db->fetch_array($sqlBuildResult))
		{
			$tcID = $myrowTC[0];
			$status = $myrowTC[1];
			if ($status == $notRunStatus || isset($tcStatusInfo[$tcID]))
				continue;
			$font = defineColor($status);
			$tcStatusInfo[$tcID] = array($status,$font);
		}
	}
	for($i = 0;$i < sizeof($tcIDs);$i++)
	{
		$tcID = $tcIDs[$i];
		
		$tc = $tcInfo[$tcID];
		$status = $notRunStatus;
		$font = 'black';
		$mgttcid = $tc[1];
		$name = $tc[0];
		$numResults = 0;
		if (isset($tcStatusInfo[$tcID]))
		{
			$status = $tcStatusInfo[$tcID][0];
			$font = $tcStatusInfo[$tcID][1];
			$numResults = 1;
		}
		//This is where I do the result display stuff
		//If there was a result passed in and it isn't all
		//add all the test cases and their status colors
		if ( ($bFilteredResultAll) ||
			 ($filteredResult == $status) || // eg for pass: p == p
			 ($filteredResult == $notRunStatus && ($numResults == 0)))
		{ 
			$data .= displayTC($font,$mgttcid,$name,$tcID,$menuUrl,$dtreeCategoryId);
		}
	}

	return $data;
}



function DEPR_displayTC($font,$mgttcid,$title,$tcid,$menuUrl, $dtreeCategoryId)
{
	$name = '<span';
	if ($font != 'black')
		$name .= ' class="' . $font.'"';
	$name .= '>[' . $mgttcid . "] " . filterString($title) . "</span>";
	$url = $menuUrl . '&level=testcase&id=' . $tcid;
	
	if (TL_TREE_KIND == 'JTREE') 
	{
		$url = 'javascript: ST('.$tcid.');';
		return "['" . $name . "','" . $url . "'],\n";
	}
	else if (TL_TREE_KIND == 'LAYERSMENU')	
		return '....|' . $name . "|" . $url . "|Test case||workframe|\n";
	else if (TL_TREE_KIND == 'DTREE')
	{
		global	$dtreeCounter;
		return "tlTree.add(" . $dtreeCounter++ . "," . $dtreeCategoryId . ",'" . $name . "','" . $url . "');\n";
	}
}


//this function gathers all of the passed,failed,blocked, 
//and not run information about the test cases so that it can be displayed next to the category
//
// 20051002 - fm - refactoring
function DEPR_catCount(&$db,$catID,$colored,$build)
{
	global $g_tc_status;

	$returnValues = null;
	
	$sql = " SELECT count(testcase.id) AS num_tc from category,testcase " .
	       " WHERE category.id=" . $catID . 
	       " AND category.id = testcase.catid";


	$totalTCResult = $db->exec_query($sql);
	$totalTCs = $db->fetch_array($totalTCResult);
	
	if($colored == 'result') //if they did then use these queries
	{
		$arrayCounter = 0;
		$pass = 0;
		$fail = 0;
		$blocked = 0;
		$notRun = 0;
		$testCaseArray = null;
		// 10152005 - kl - only get results execute on test plan builds
		$csBuilds = get_cs_builds($db,$_SESSION['testPlanId']);
		//Now grab all of the test cases and their results	
   // 10152005 - kl - only get results execute on test plan builds
		$sql = " SELECT tcid, status, build.name " .
		       " FROM results,category,testcase,build " .
		       " WHERE category.id = testcase.catid " .
		       " AND   testcase.id = results.tcid " .
		       " AND   build.id = results.build_id " . 
	         " AND (results.build_id IN (" . $csBuilds . " )) " . 
		       " AND   category.id=" . $catID . 
		       " ORDER BY build.name DESC";
		$totalResult = $db->exec_query($sql);

		//Setting the results to an array.. Only taking the most recent results and displaying them
		while($totalRow = $db->fetch_array($totalResult))
		{
			//This is a test.. I've got a problem if the user goes and sets a previous p,f,b 
			// value to a 'n' value. The program then sees the most recent value as an not run. 
			//I think we want the user to then see the most recent p,f,b value
			if($totalRow['status'] != $g_tc_status['not_run'])	{
				$testCaseArray[$totalRow['tcid']] = $totalRow['status'];
			}
		}
		
		//This is the code that determines the pass,fail,blocked amounts
		//I had to write this code so that the loop before would work.. 
		//I'm sure there is a better way to do it but hell if I know how to figure it out..
		if(count($testCaseArray) > 0)
		{
			//This loop will cycle through the arrays and count the amount of p,f,b,n
			foreach($testCaseArray as $tc)
			{
				if($tc == $g_tc_status['passed']) {
					$pass++;
				} elseif($tc == $g_tc_status['failed']) {
					$fail++;
				} elseif($tc == $g_tc_status['blocked']) {
					$blocked++;
				}
			}//end foreach
		}//end if
		//setup the return values

		$tmp = $totalTCs['num_tc'] - ($pass + $fail + $blocked); //the equation for not run
		$returnValues = '<span class="green">' . $pass . "</span>";
		$returnValues .= ',<span class="red">' . $fail . "</span>";
		$returnValues .= ',<span class="blue">' . $blocked . "</span>";
		$returnValues .= ',<span class="black">' . $tmp . "</span>"; //append font to front and back
	}
	else
	{	
		//else use a specific build
		$sql = " SELECT COUNT(testcase.id) AS num_tc,status " .
		       " FROM category,testcase,results " .
		       " WHERE category.id=testcase.catid " .
		       " AND testcase.id=results.tcid " .
		       " AND category.id=" . $catID . 
		       " AND results.build_id=" . $build . 
		       " GROUP BY results.status";
		$result = $db->exec_query($sql);
	
		$values[$g_tc_status['passed']] = 0;
		$values[$g_tc_status['failed']] = 0;
		$values[$g_tc_status['blocked']] = 0;
		$values[$g_tc_status['not_run']] = 0;
		if ($result)
		{
			while($row = $db->fetch_array($result))
			{
				$values[$row['status']] = $row['num_tc'];
			}	
		}
		
		$values[$g_tc_status['not_run']] = $totalTCs['num_tc'] -  $values[$g_tc_status['passed']] - 
		                                                   $values[$g_tc_status['failed']] - 
		                                                   $values[$g_tc_status['blocked']];
		
		$returnValues = '<span class="green">' . $values[$g_tc_status['passed']] . "</span>"; 
		$returnValues .=  ',<span class="red">' . $values[$g_tc_status['failed']] . "</span>"; 
		$returnValues .= ',<span class="blue">' . $values[$g_tc_status['blocked']] . "</span>";
		$returnValues .= ',<span class="black">' . $values[$g_tc_status['not_run']] . "</span>";
	}
	return $returnValues;
}
?>