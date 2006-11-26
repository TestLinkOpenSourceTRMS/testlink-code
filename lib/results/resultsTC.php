<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: resultsTC.php,v 1.13 2006/11/26 06:45:38 kevinlevy Exp $ 
*
* @author	Martin Havlat <havlat@users.sourceforge.net>
* @author 	Chad Rosen
* 
* Show Test Report by individual test case.
*
* @author 20050919 - fm - refactoring
* 
* 20051022 - scs - correct wrong index
*/

print "KL - 20061029 - work in progress <BR>";
require('../../config.inc.php');
require_once('common.php');
//require_once('builds.inc.php');
//require_once('results.inc.php');
require_once("../../lib/functions/lang_api.php");
testlinkInitPage($db);

$arrData = array();

//$arrBuilds = getBuilds($db,$_SESSION['testPlanId'], " ORDER BY builds.name ");

// is output is excel?
$xls = FALSE;
if (isset($_GET['format']) && $_GET['format'] =='excel'){
	$xls = TRUE;
}

// 20050919 - fm
/**
$sql = " SELECT MGTCOMP.name AS comp_name, MGTCAT.name as cat_name, TC.title, TC.id AS tcid, mgttcid" .
       " FROM testplans TP, component COMP, category CAT, testcase TC, mgtcomponent MGTCOMP, mgtcategory MGTCAT " .
       " WHERE MGTCOMP.id = COMP.mgtcompid " .
       " AND MGTCAT.id = CAT.mgtcatid " .
		   " AND COMP.projid=TP.id " .
		   " AND CAT.compid=COMP.id " .
		   " AND TC.catid=CAT.id" .
  	   " AND TP.id=" . $_SESSION['testPlanId'];

$result = $db->exec_query($sql);
$bRights = has_rights($db,"testplan_execute") && !$xls;

while ($myrow = $db->fetch_array($result))
{ //Cycle through all of the test cases
	$container = null;
	$container[] = htmlspecialchars($myrow['comp_name'] . ' / ' . $myrow['cat_name']);
	$container[] = "<b>" . $myrow['mgttcid'] . "</b>:" . htmlspecialchars($myrow['title']); 
	
	///SCHLUNDUS
	foreach ($arrBuilds as $build => $name)
	{
		$tcID = $myrow['tcid'];
		$tcStatus = getStatus($db,$tcID, $build);
		if($tcStatus != $g_tc_status['not_run'])
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
*/
// for excel send header
if ($xls)
	sendXlsHeader();

$smarty = new TLSmarty;
$smarty->assign('title', lang_get('title_test_report_all_builds'));
$smarty->assign('arrData', $arrData);
// $arrBuilds not defined
//$smarty->assign('arrBuilds', $arrBuilds);
if ($xls) {
	$smarty->assign('printDate', strftime($g_date_format, time()) );
	$smarty->assign('user', $_SESSION['user']);
}
$smarty->display('resultsTC.tpl');
?>


