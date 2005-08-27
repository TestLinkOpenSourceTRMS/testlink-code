<?
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: execNavigator.php,v $
 *
 * @version $Revision: 1.3 $
 * @modified $Date: 2005/08/27 20:53:31 $
 *
 * @author Martin Havlat
 *
 *	@todo names of com, cat, TC should be grab from test specification tables
 *	@todo keywords are not collected from keywords table but from Test case suite
 *
 * 20050807 - fm changes in filterKeyword() call.
 * 20050807 - fm changes in createBuildMenu() call.
 * 20050815 - scs - optimized and reducing Sql statements
 * 20050828 - scs - reduced the code
 * 20050828 - scs - added searching for tcID
**/
require_once('../../config.inc.php');
require_once('common.php');
require_once('treeMenu.inc.php');
require_once('exec.inc.php');
require_once('builds.inc.php');
require_once("../../lib/functions/lang_api.php");
testlinkInitPage();

// global var for dtree only
$dtreeCounter = 0; 
$treeColored = (isset($_POST['colored']) && ($_POST['colored'] == 'result')) ? 'selected="selected"' : null;
$selectOwner = (isset($_POST['owner']) && ($_POST['owner'] == $_SESSION['user'])) ? 'selected="selected"' : null;
$filterOwner = array (array('id' => $_SESSION['user'], 'selected' => $selectOwner));
$tcID = isset($_POST['tcID']) ? intval($_POST['tcID']) : null;

// 20050807 - fm - function interface changed
$optBuild = createBuildMenu($_SESSION['testPlanId']);
$optBuildSelected = isset($_POST['build']) ? $_POST['build'] : key($optBuild);
$optResult = createResultsMenu();
$optResultSelected = isset($_POST['result']) ? $_POST['result'] : 'All';

// generate tree 
$menuUrl = null;

$SP_html_help_file = TL_INSTRUCTIONS_RPATH . $_SESSION['locale'] . "/executeTest.html";
$sMenu = generateExecTree($optBuildSelected,$SP_html_help_file,$menuUrl,$tcID);
$tree = invokeMenu($sMenu);

//20050828 - scs - quick check to see if the wanted tc is in the testplan
$tcData = null;
$testCaseID = null;
if ($tcID)
{
	$query = "SELECT testcase.id from component,category,testcase WHERE projID = {$_SESSION['testPlanId']} AND compid = component.id AND category.id = testcase.catid AND mgttcid = {$tcID}";
	$tcData = selectData($query);
	if ($tcData)
		$testCaseID = $tcData[0]['id'];
}

$smarty = new TLSmarty;
$smarty->assign('treeKind', TL_TREE_KIND);
$smarty->assign('treeColored', $treeColored);
$smarty->assign('optBuild', $optBuild);
$smarty->assign('optBuildSelected', $optBuildSelected);
$smarty->assign('optResult', $optResult);
$smarty->assign('optResultSelected', $optResultSelected); 
$smarty->assign('arrOwner', $filterOwner);
// 20050807 - fm - function interface changed
$smarty->assign('filterKeyword', filterKeyword($_SESSION['testPlanId']));
$smarty->assign('tcID',$tcID);
$smarty->assign('testCaseID',$testCaseID);
$smarty->assign('tcIDFound', $tcData ? 1 : 0);
$smarty->assign('tree', $tree);
$smarty->assign('menuUrl',$menuUrl);
$smarty->assign('SP_html_help_file',$SP_html_help_file);
$smarty->display('execNavigator.tpl');

/** 
* 	Function creates data for tree menu
* 
*	@param integer $build
*	@return array input for layersmenu
*
* 	20050528 - fm added purl_to_help argument
*
*/
function generateExecTree($build,$purl_to_help,&$menuUrl,$tcIDFilter = null)
{
	global	$dtreeCounter;
	
	//If the user submits the sorting form
	$keyword = 'All';
	$owner = 'All';
	$filteredResult = 'all';
	$colored = 'build';

	if(isset($_POST['submitOptions']))
	{
		$keyword = isset($_POST['keyword']) ? strings_stripSlashes($_POST['keyword']) : null;
		$owner = isset($_POST['owner']) ? strings_stripSlashes($_POST['owner']) : null;
		$filteredResult = isset($_POST['result']) ? strings_stripSlashes($_POST['result']) : null;
		
		$colored = 'result';
		if (isset($_POST['colored']) && (strings_stripSlashes($_POST['colored']) == 'build'))
			$colored = 'build';
	}

	$dtreeCategoryId = null;
	$menuUrl = 'lib/execute/execSetResults.php?keyword=' . $keyword . 
			'&build=' . $build . '&owner=' . $owner;
	$menustring = null;
	//root of tree
	$testPlanName = filterString($_SESSION['testPlanName']);
	
	if (TL_TREE_KIND == 'LAYERSMENU') 
	{
		$menustring = ".|" . $testPlanName . "|" . $purl_to_help . "|Test Case Suite||workframe|\n";
	}
	elseif (TL_TREE_KIND == 'DTREE')
	{
		$menustring .= "tlTree.add(" . $dtreeCounter++ . ",-1,'" . $testPlanName . 
		               "','" . $purl_to_help . "');\n";
	}
	elseif (TL_TREE_KIND == 'JTREE')	
	{
		$help_html = $purl_to_help . "/testExecute.html";
		$menustring .= "['" . $testPlanName . "','SP()',\n";
	}
		
	//grab every component depending on the project 
	$sql = "select component.id, component.name from component where " .
			"component.projid = " . $_SESSION['testPlanId'] . " order by component.name";
	$comResult = do_mysql_query($sql);
	
	$bKeyWordAll = ($keyword == 'All');
	$keyword = mysql_escape_string($keyword);
	$bOwnerAll = ($owner == 'All');
	$owner = mysql_escape_string($owner);
	
	while ($myrowCOM = mysql_fetch_row($comResult)) { //display all the components until we run out

		//right now only categories are sorted by owners. This means that the entire component
		//will still show up if the user has decided to sort.
		//So since I don't want them being able to see every single test case when they click
		//the component I need to pass an owner variable to the execution page which will allow it
		//to sort by category
				
		//grab every category depending on the component
		if($bOwnerAll)
		{
			//If the user has not selected an owner to sort by
			$catSql = "select category.id, category.name from component,category where component.id = " . $myrowCOM[0] . " and component.id = category.compid order by CATorder,category.id";
		}
		else
		{ 
			//if the user selected a user to sort by
			$catSql = "select category.id, category.name from component,category where component.id = " . $myrowCOM[0] . " and component.id = category.compid and category.owner='" . $owner . "' order by CATorder,category.id";
		}
		$catResult = do_mysql_query($catSql);

		//check to see if there are any rows returned from the component query
		$numRowsCAT = mysql_num_rows($catResult);

		if($numRowsCAT > 0)
		{
			$componentName = filterString($myrowCOM[1]);
			if (TL_TREE_KIND == 'JTREE')				
				$menustring .= "['" . $componentName . "','SCO({$myrowCOM[0]})',\n\n";
			else if (TL_TREE_KIND == 'LAYERSMENU')
				$menustring .= "..|" . $componentName . "|" . $menuUrl . "&level=component&id=" . $myrowCOM[0]  . "|Component||workframe|\n";
			else if (TL_TREE_KIND == 'DTREE')
			{
				$dtreeComponentId = $dtreeCounter;
				$menustring .= "tlTree.add(" . $dtreeCounter++. ",0,'" . $componentName . "','" . $menuUrl . "&level=component&id=" . $myrowCOM[0] . "');\n";
			}
			while ($myrowCAT = mysql_fetch_row($catResult))
			{  //display all the categories until we run out
					
				//This next section displays test cases. However I need to check if there are actually are any test cases available first
				$TCsql = "select testcase.id, testcase.title, testcase.mgttcid from testcase where testcase.catid = " . $myrowCAT[0];
				//user passed in a result that isnt the default "all" and didnt select a keyword to sort by
				if(!$bKeyWordAll)
				{
					//user has selected to view a specific result and did select a keyword to sort by
					$TCsql .= " AND (keywords LIKE '%,{$keyword},%' OR keywords like '{$keyword},%')";
				}
				if ($tcIDFilter)
					$TCsql .= " AND testcase.mgttcid = {$tcIDFilter} ";
				$TCsql .= " order by TCorder,testcase.mgttcid";				

				$TCResult = do_mysql_query($TCsql); //run the query
				$numRowsTC = mysql_num_rows($TCResult); //count the rows
				if($numRowsTC > 0) //if there are actually test cases
				{
					//grab the test case info for this category
					$testCaseInfo = catCount($myrowCAT[0], $colored, $build);
					 
					//display the category  section.. The first part of this string 
					//displays the name of the category followed by how many passed, 
					//failed, blocked, and not run test cases there are in it
					$name = filterString($myrowCAT[1]) . " (" . $testCaseInfo. ")";
					$url = $menuUrl . "&level=category&id=" . $myrowCAT[0];
					
					if (TL_TREE_KIND == 'JTREE')
					{
						$url = "SC({$myrowCAT[0]})";
						$menustring .= "['" . $name . "','" . $url . "',\n";
					}
					else if (TL_TREE_KIND == 'LAYERSMENU')
						$menustring .= "...|" . $name . "|" . $url . "|Category||workframe|\n";
					else if (TL_TREE_KIND == 'DTREE')
					{
						$dtreeCategoryId = $dtreeCounter;
						$menustring .= "tlTree.add(" . $dtreeCounter++. "," . $dtreeComponentId . ",'" . $name . "','" . $url . "');\n";
					}

					$menustring .= displayTCTree($TCResult, $build, $owner, $colored, $menuUrl, $filteredResult, $dtreeCategoryId);
					if (TL_TREE_KIND == 'JTREE')
						$menustring .= "],\n";
				}
			}

			if (TL_TREE_KIND == 'JTREE')
				$menustring .= "],\n";
		}
	}

	return $menustring;
}


/**
 * Function that add all or filtered of the test cases
 *
 * @author Andreas Morsing - optimized SQL and reduced number of SQL-Statements
 */
function displayTCTree($TCResult, $build, $owner, $colored, $menuUrl, $filteredResult, $dtreeCategoryId)
{
	$data = null;
	
	$bFilteredResultAll = ($filteredResult == 'all');
	$tcIDs = null;
	$tcInfo = null;
	while ($myrowTC = mysql_fetch_row($TCResult))
	{
		$name = $myrowTC[1];
		$tcID = $myrowTC[0];
		$mgttcid = $myrowTC[2];
	
		$tcIDs[] = $tcID;	
		$tcInfo[$tcID] = array($name,$mgttcid);
	}
	$tcStatusInfo = null;
	if ($tcIDs)
	{
		$tcIDsList = implode(",",$tcIDs);
		$sqlResult = "SELECT tcid,status FROM results WHERE tcid IN (" . $tcIDsList . ")";
		if($colored == 'result')
			$sqlResult .= " ORDER BY build DESC";
		else
			$sqlResult .= " AND build = '" . $build . "'";
		
		$sqlBuildResult = do_mysql_query($sqlResult);
		//I need the num results so I can do the check below on not run test cases
		while($myrowTC = mysql_fetch_row($sqlBuildResult))
		{
			$tcID = $myrowTC[0];
			$status = $myrowTC[1];
			$font = defineColor($status);
			$tcStatusInfo[$tcID] = array($status,$font);
		}
	}
	
	for($i = 0;$i < sizeof($tcIDs);$i++)
	{
		$tcID = $tcIDs[$i];
		
		$tc = $tcInfo[$tcID];
		$status = 'n';
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
			 ($filteredResult == 'n' && ($numResults == 0)))
		{ 
			$data .= displayTC($font,$mgttcid,$name,$tcID,$menuUrl,$dtreeCategoryId);
		}
	}

	return $data;
}

function displayTC($font,$mgttcid,$title,$tcid,$menuUrl, $dtreeCategoryId)
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


//this function gathers all of the passed,failed,blocked, and not run information about the test cases so that it can be displayed next to the category
function catCount($catID,$colored,$build)
{
	//check to see if the user selected the cumulative checkbox
	//destroying old variables.. Shouldnt really matter but since I copied this from another place i'm leaving it in

	$returnValues = null;
	unset($totalRow);
	unset($testCaseArray);
	unset($totalTCs);
	
	$sql = "select count(testcase.id) from category,testcase where category.id='" . $catID . "' and category.id = testcase.catid";
	$totalTCResult = do_mysql_query($sql);
	$totalTCs = mysql_fetch_row($totalTCResult);
	
	if($colored == 'result') //if they did then use these queries
	{
		$arrayCounter = 0;
		$pass = 0;
		$fail = 0;
		$blocked = 0;
		$notRun = 0;
		$testCaseArray = null;
		
		//Now grab all of the test cases and their results	
		$sql = "select tcid,status from results,category,testcase where category.id='" . $catID . "' and category.id = testcase.catid and testcase.id = results.tcid order by build";
		$totalResult = do_mysql_query($sql);

		//Setting the results to an array.. Only taking the most recent results and displaying them
		while($totalRow = mysql_fetch_row($totalResult))
		{
			//This is a test.. I've got a problem if the user goes and sets a previous p,f,b value to a 'n' value. The program then sees the most recent value as an not run. I think we want the user to then see the most recent p,f,b value
			if($totalRow[1] != 'n')	{
				$testCaseArray[$totalRow[0]] = $totalRow[1];
			}
		}
		
		//This is the code that determines the pass,fail,blocked amounts
		//I had to write this code so that the loop before would work.. I'm sure there is a better way to do it but hell if I know how to figure it out..
		if(count($testCaseArray) > 0)
		{
			//This loop will cycle through the arrays and count the amount of p,f,b,n
			foreach($testCaseArray as $tc)
			{
				if($tc == 'p') {
					$pass++;
				} elseif($tc == 'f') {
					$fail++;
				} elseif($tc == 'b') {
					$blocked++;
				}
			}//end foreach
		}//end if

		//setup the return values
		$returnValues = '<span class="green">' . $pass . "</span>"; //pass
		$returnValues .= ',<span class="red">' . $fail . "</span>"; //fail
		$returnValues .= ',<span class="blue">' . $blocked . "</span>"; //blocked
		$tmp = $totalTCs[0] - ($pass + $fail + $blocked); //the equation for not run
		$returnValues .= ',<span class="black">' . $tmp . "</span>"; //append font to front and back
	}
	else
	{	
		//else use a specific build
		$sql = "SELECT COUNT(testcase.id) AS c,status FROM category,testcase,results WHERE category.id='" . $catID . "' and category.id=testcase.catid AND testcase.id=results.tcid AND build='" . $build . "' GROUP BY results.status";
		$result = do_mysql_query($sql);
	
		$values['p'] = 0;
		$values['f'] = 0;
		$values['b'] = 0;
		$values['n'] = 0;
		if ($result)
		{
			while($row = mysql_fetch_array($result))
				$values[$row['status']] = $row['c'];
		}
		
		$values['n'] = $totalTCs[0] -  $values['p'] - $values['f'] - $values['b'];
		
		//Set the passed,failed, and blocked results to the returned value
		$returnValues = '<span class="green">' . $values['p'] . "</span>";  //pass
		$returnValues .=  ',<span class="red">' . $values['f'] . "</span>"; //failed
		$returnValues .= ',<span class="blue">' . $values['b'] . "</span>"; //blocked
		$returnValues .= ',<span class="black">' . $values['n'] . "</span>";
	}
	return $returnValues;
}
?>