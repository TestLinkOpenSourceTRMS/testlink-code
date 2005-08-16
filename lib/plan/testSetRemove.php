<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * @version $Id: testSetRemove.php,v 1.2 2005/08/16 18:00:57 franciscom Exp $ 
 * 
 * Remove Test Cases from Test Case Suite 
 * 
 *
 *
 * @author 20050807 - fm
 * refactoring:  
 * removed deprecated: $_SESSION['project']
 *
 */         
require('../../config.inc.php');
require("../functions/common.php");
require_once("../../lib/functions/lang_api.php");
testlinkInitPage();

$id = isset($_GET['data']) ? $_GET['data'] : null;
$level = isset($_GET['level']) ? $_GET['level'] : null;

// 20050807 - fm
//store the project number in a variable so that i can fresh the left frame later
//$project = isset($_SESSION['testPlanId']) ? $_SESSION['testPlanId'] : 0 ; 

$resultString = null;
$arrData = array();

/** process update if available */
if(isset($_POST['deleteTC']))
{
	$newArray = extractInput(false);

	$i = 1; //Start the counter at 3 because the first three variable is a submit box
	while ($i < count($newArray)) //Loop for the entire size of the array
	{
		$tcID = $newArray[$i]; //Then the first value is the ID
		if($newArray[$i + 1] == 'break')
		{
			//do nothing
			$i = $i + 2;
		}
		else
		{
			$sqlMGT = "SELECT mgttcid,title FROM testcase WHERE id='" . $tcID . "'";
			$resultMGT = do_mysql_query($sqlMGT);
			$mgtID = mysql_fetch_row($resultMGT);

			//Delete the test case as well as its results and bugs
			$sqlTCDel = "DELETE FROM testcase WHERE id='" . $tcID . "'";
			$sqlRESDel = "DELETE FROM results WHERE tcid='" . $tcID . "'";
			$sqlBUGDel = "DELETE FROM bugs WHERE tcid='" . $tcID . "'";
			$result = do_mysql_query($sqlTCDel);
			$result = do_mysql_query($sqlRESDel);
			$result = do_mysql_query($sqlBUGDel);

			//delete all results
			$resultString .= lang_get("test_case_removed_part1") . " <b>". $mgtID[0] . "</b>: " . htmlspecialchars($mgtID[1]) . " " .lang_get("has been removed."). " <br />";
			$i = $i + 3;
		}
	}

	//Add in code that refreshes the left frame..
//	$page = "editLeft.php?project=" . $project;
//	refreshFrame($page); //call the function below to refresh the left frame
}
elseif(isset($_POST['deletecomponent']))
{
	//Select all of the categories from the component
	$sqlCAT = "SELECT category.id FROM category WHERE compid='" . $_GET['data'] . "'";
	$resultCAT = do_mysql_query($sqlCAT); //Execute query

	while($rowCAT = mysql_fetch_array($resultCAT))
	{
		//Select all of the test cases from the categories
		$sqlTC = "SELECT id FROM testcase WHERE catid='" . $rowCAT[0] . "'";
		$resultTC = do_mysql_query($sqlTC);

		while($rowTC = mysql_fetch_array($resultTC))
		{
			//delete each of the results and bugs from the selected test case
			$sqlTCDel = "DELETE FROM testcase WHERE id='" . $rowTC[0] . "'";
			$sqlRESDel = "DELETE FROM results WHERE tcid='" . $rowTC[0] . "'";
			$sqlBUGDel = "DELETE FROM bugs WHERE tcid='" . $rowTC[0] . "'";
			$result = do_mysql_query($sqlTCDel);
			$result = do_mysql_query($sqlRESDel);
			$result = do_mysql_query($sqlBUGDel);
		}

		//delete each category when you're done
		$sqlCATDel = "DELETE FROM category WHERE id='" . $rowCAT[0] . "'";
		$resultCATDel = do_mysql_query($sqlCATDel);
	}

	//Grab the component name
	$sqlComName = "SELECT name FROM component WHERE id='" . $id . "'";
	$comResult = do_mysql_query($sqlComName);
	$comRow = mysql_fetch_row($comResult);
	
	//finally delete the component
	$sqlCOMDel = "DELETE FROM component WHERE id='" . $id . "'";
	$resultCOMDel = do_mysql_query($sqlCOMDel);

	$resultString = "<b>". lang_get("component_removed_part1") ."</b> " . $comRow[0] ." ". lang_get("component_removed_part2");
	
	//Add in code that refreshes the left frame..
//	$page = "editLeft.php?project=" . $project;
//	refreshFrame($page); //call the function below to refresh the left frame

}
elseif(isset($_POST['deletecategory'])) 
{
	//Select all of the test cases from the categories
	$sqlTC = "SELECT id FROM testcase WHERE catid='" . $id . "'";
	$resultTC = do_mysql_query($sqlTC); //Execute query

	while($rowTC = mysql_fetch_array($resultTC))
	{
		//delete each of the results and bugs from the selected test case
		$sqlTCDel = "DELETE FROM testcase WHERE id='" . $rowTC[0] . "'";
		$sqlRESDel = "DELETE FROM results WHERE tcid='" . $rowTC[0] . "'";
		$sqlBUGDel = "DELETE FROM bugs WHERE tcid='" . $rowTC[0] . "'";
		$result = do_mysql_query($sqlTCDel);
		$result = do_mysql_query($sqlRESDel);
		$result = do_mysql_query($sqlBUGDel);
	}

	//Grab the category name
	$sqlCatName = "SELECt name FROM category WHERE id='" . $id . "'";
	$catResult = do_mysql_query($sqlCatName);
	$catRow = mysql_fetch_row($catResult);

	//delete the category when you're done
	$sqlCATDel = "DELETE FROM category WHERE id='" . $id . "'";
	$resultCATDel = do_mysql_query($sqlCATDel);
	$resultString =  "<b>". lang_get("category_removed_part1") ." </b> " . $catRow[0] . " ". lang_get("category_removed_part2");

	//Add in code that refreshes the left frame..
//	$page = "editLeft.php?project=" . $project;
//	refreshFrame($page); //call the function below to refresh the left frame
}


/** grab data for table */
if($level == 'component')
{
	$sqlCat = "SELECT id FROM category WHERE compid =" . $id;
	$result = do_mysql_query($sqlCat);

	while($row = mysql_fetch_array($result))
	{
		$sqlTC = "SELECT id FROM testcase WHERE catid=" . $row[0];
		$resultTC = do_mysql_query($sqlTC);

		while($rowTC = mysql_fetch_array($resultTC))
			displayTC($rowTC[0],$arrData);
	}
}
else if ($level == 'category')
{
		$sqlCat ="SELECT id FROM testcase WHERE catid =" . $id;
		$result = do_mysql_query($sqlCat);

		while($row = mysql_fetch_array($result))
			displayTC($row[0],$arrData);
}
else if($level == 'tc')
{
	displayTC($id,$arrData);
} 
else
{
	// show instructions
	redirect( $_SESSION['basehref'] . $g_rpath['instructions'].'/testSetRemove.html');
}

$smarty = new TLSmarty;
$smarty->assign('level', $level);
$smarty->assign('id', $id);
$smarty->assign('testPlanName', $_SESSION['testPlanName']);
$smarty->assign('resultString', $resultString);
$smarty->assign('arrData', $arrData);
$smarty->display('planRemoveTC.tpl');


function displayTC($id,&$arrData)
{
	$sql = "SELECT category.name, component.name, testcase.id, title, summary,steps,exresult, active, version, mgttcid,TCorder FROM testcase,component,category WHERE testcase.id='" . $id . "' AND component.id=category.compid AND category.id=testcase.catid ORDER BY TCorder";
	$result = do_mysql_query($sql);
	
	while($row = mysql_fetch_array($result))
	{
		//Assign values from the test case query
		$id = $row['id'];
		$title = $row['title'];
		$mgtID = $row['mgttcid'];
		$TCorder = $row['TCorder'];
		$compName = $row[1];
		$catName = $row[0];
		
		$arrData[] = array(	'id' => $id, 
							'name' => $title, 
							'container' => $compName . '/' . $catName
						  );
	}
}
?>