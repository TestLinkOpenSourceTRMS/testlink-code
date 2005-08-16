<?php
/* TestLink Open Source Project - http://testlink.sourceforge.net/ */
/* $Id: planNew.php,v 1.2 2005/08/16 18:00:57 franciscom Exp $ */
/* Purpose:  Add new Test Plan */
/*
 * @ author: francisco mancardi - 20050810
 * deprecated $_SESSION['product'] removed
*/
////////////////////////////////////////////////////////////////////////////////
require('../../config.inc.php');
require("../functions/common.php");
require("plan.inc.php");
require_once("../../lib/functions/lang_api.php");
testlinkInitPage();

$sqlResult = null;
// insert new project
if(isset($_POST['newTestPlan'])) 
{
	$name = isset($_POST['name']) ? strings_stripSlashes($_POST['name']) : null;
	if (!strlen($name))
		$sqlResult = lang_get('warning_empty_tp_name');
	else
	{
		$notes = isset($_POST['notes']) ? strings_stripSlashes($_POST['notes']) : null;
		$copy = isset($_POST['copy']) ? intval($_POST['copy']) : 0;
		$projID = 0;
		if (insertPlan($projID,$name,$notes,$_SESSION['productID']))
			$sqlResult = 'ok';
		else
			$sqlResult =  mysql_error();
		$result = insertProjectPriorities($projID);
		$rights = isset($_POST['rights']) ? $_POST['rights'] : '';
		if($rights == 'on')
			$result = insertProjectUserRight($projID,$_SESSION['userID']);
		//user has decided to copy an existing project. What this code does is loops through each of the components, inserts the component info, loops through the categories from the component and then adds the category, and the same thing as before with test cases.
		if($copy) //if the user chose to copy then go through this code
		{
			$result = getProjectComponents($copy,$cInfo);
			for($i = 0; $i < sizeof($cInfo);$i++)
			{
				//insert it into the component table with new ids
				$component = $cInfo[$i];
				$COMID = insertProjectComponent($projID,$component[1],$component[2]);
				//Grab all of the currently looping components categories
				$sqlCat = "select id,name,compid,mgtcatid,CATorder from category where " .
						"compid='" . $component[0] . "'";
				$resultCat = do_mysql_query($sqlCat);
				while ($myrowCat = mysql_fetch_row($resultCat)) //loop through categories
				{
					//insert the new category
					$sqlInsertCat = "insert into category (name,compid,mgtcatid,CATorder) " .
							"values ('" . mysql_escape_string($myrowCat[1]) . "','" . mysql_escape_string($COMID) . " ','" . 
							mysql_escape_string($myrowCat[3])  . "','" . mysql_escape_string($myrowCat[4]) . "')";
					$resultInsertCat = do_mysql_query($sqlInsertCat); //run insert code
					$CATID = mysql_insert_id(); //grab the catid from the last insert so we can use it for the test case
	
					//grab all of the test case info.. Anything with a default I ignore
					$sqlTC = "select title,summary,steps,exresult,mgttcid,keywords,TCorder," .
							"version from testcase where catid='" . mysql_escape_string($myrowCat[0]) . "'";
					$resultTC = do_mysql_query($sqlTC);
	
					while ($myrowTC = mysql_fetch_row($resultTC)) //loop through test case code
					{
						//insert the test case code
						$sqlInsertTC = "insert into testcase (title,summary,steps,exresult," .
								"catid,mgttcid,keywords,TCorder,version) values ('" . 
								mysql_escape_string($myrowTC[0]) . "','" . mysql_escape_string($myrowTC[1]) . "','" . mysql_escape_string($myrowTC[2]) . 
								"','" . mysql_escape_string($myrowTC[3]) . "','" . mysql_escape_string($CATID) . "','" . 
								mysql_escape_string($myrowTC[4]) . "','" . mysql_escape_string($myrowTC[5]) . "','" . mysql_escape_string($myrowTC[6]) . 
								"','" . mysql_escape_string($myrowTC[7]) . "')";
						$resultInsertTC = do_mysql_query($sqlInsertTC); //run insert code
					}//end the tc loop
				}//end the cat loop
			}//end the com loop
		}//end the copy if statement
	}
}
$smarty = new TLSmarty;
$smarty->assign('sqlResult', $sqlResult);
$smarty->assign('arrPlan', getAllActiveTestPlans());
$smarty->display('planNew.tpl');
?>


