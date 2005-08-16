<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: resultsTC.php,v 1.2 2005/08/16 18:00:58 franciscom Exp $ 
*
* @author	Martin Havlat <havlat@users.sourceforge.net>
* @author 	Chad Rosen
* 
* This page that views Test Report by individual test case.
*
* @author 20050807 - fm
* refactoring:  
* removed deprecated: $_SESSION['project']
*
*/
require('../../config.inc.php');
require_once('common.php');
require_once('builds.inc.php');
require_once('results.inc.php');
require_once("../../lib/functions/lang_api.php");
testlinkInitPage();

$arrData = array();

// 20050807 - fm
$arrBuilds = getBuilds($_SESSION['testPlanId']);

// is output is excel?
if (isset($_GET['format']) && $_GET['format'] =='excel'){
	$xls = TRUE;
}else{
	$xls = FALSE;
}

// 20050807 - fm
$sql = "select component.name,category.name, testcase.title, testcase.id,mgttcid from " .
		"project,component,category,testcase where project.id=" . $_SESSION['testPlanId'] . 
		" and component.projid=project.id and category.compid=component.id and " .
		"testcase.catid=category.id";
$result = do_mysql_query($sql);
$bRights = has_rights("tp_execute") && !$xls;

while ($myrow = mysql_fetch_row($result))
{ //Cycle through all of the test cases
	$container = null;
	$container[] = htmlspecialchars($myrow[0] . ' / ' . $myrow[1]); // test suite
	$container[] = "<b>" . $myrow[4] . "</b>:" . htmlspecialchars($myrow[2]); // title
	
	///SCHLUNDUS
	foreach ($arrBuilds as $build=>$name)
	{
		$tcID = $myrow[3];
		$tcStatus = getStatus($tcID, $build);
		if($tcStatus != 'n')
		{
			//This displays the pass,failed or blocked test case result
			//The hyperlink will take the user to the test case result in the execution page
			$descrStatus = getStatusName($tcStatus);
			if($bRights)
			{
				$container[] = '<a href="lib/execute/execSetResults.php?keyword=All&level=testcase&owner=All' . 
					'&id=' . $tcID . "&build=" . $build . '">'.$descrStatus . "</a>";
			}
			else
				$container[] = $descrStatus;
		}else
			$container[] = "-";
	}
	$arrData[] = $container;
}

// for excel send header
if ($xls)
	sendXlsHeader();

$smarty = new TLSmarty;
$smarty->assign('title', lang_get('title_test_report_all_builds'));
$smarty->assign('arrData', $arrData);
$smarty->assign('arrBuilds', $arrBuilds);
if ($xls) {
	$smarty->assign('printDate', date('"F j, Y, H:m"'));
	$smarty->assign('user', $_SESSION['user']);
}
$smarty->display('resultsTC.tpl');
?>


