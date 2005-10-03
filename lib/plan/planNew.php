<?php
/* TestLink Open Source Project - http://testlink.sourceforge.net/ */
/* $Id: planNew.php,v 1.7 2005/10/03 07:20:14 franciscom Exp $ */
/* Purpose:  Add new Test Plan */
/*
 * @ author: francisco mancardi - 20050915 - refactoring function name
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
	{
		$sqlResult = lang_get('warning_empty_tp_name');
	}	
	else
	{
		$notes = isset($_POST['notes']) ? strings_stripSlashes($_POST['notes']) : null;
		$copy = isset($_POST['copy']) ? intval($_POST['copy']) : 0;
		
		$projID = 0;
		$sqlResult = 'ok';
		if (!insertTestPlan($projID,$name,$notes,$_SESSION['productID']))
		{
			$sqlResult =  mysql_error();
		}	
		
		$result = insertTestPlanPriorities($projID);
		$rights = isset($_POST['rights']) ? $_POST['rights'] : '';
		
		if($rights == 'on')
		{
			$result = insertTestPlanUserRight($projID,$_SESSION['userID']);
    }
    
		//user has decided to copy an existing Test Plan. 
		//What this code does is loops through each of the components, inserts the component info, 
		//loops through the categories from the component and then adds the category, 
		//and the same thing as before with test cases.
		if($copy) //if the user chose to copy then go through this code
		{
			$cInfo = getTestPlanComponents($copy);
			$num_comp = sizeof($cInfo);
			for($i = 0; $i < $num_comp;$i++)
			{
				//insert it into the component table with new ids
				$component = $cInfo[$i];
				$COMID = insertTestPlanComponent($projID,$component['mgtcompid']);
				
				//Grab all of the currently looping components categories
				// 20051001 - fm
				$sqlCat = " SELECT CAT.id as catid, MGTCAT.name, CAT.compid, CAT.mgtcatid, MGTCAT.CATorder " .
				          " FROM category CAT, mgtcategory MGTCAT " . 
				          " WHERE MGTCAT.id = CAT.mgtcatid " .  
				          " AND CAT.compid=" . $component['compid'];

				$resultCat = do_mysql_query($sqlCat);
				while ($myrowCat = mysql_fetch_assoc($resultCat))
				{
					$sqlInsertCat = " INSERT INTO category (compid,mgtcatid,CATorder) " .
							            " VALUES ('" . mysql_escape_string($COMID) . " ','" . 
							            mysql_escape_string($myrowCat['mgtcatid'])  . "','" . 
							            mysql_escape_string($myrowCat['CATorder']) . "')";
					$resultInsertCat = do_mysql_query($sqlInsertCat); 
					
					//grab the catid from the last insert so we can use it for the test case
					$CATID = mysql_insert_id(); 
	
					//grab all of the test case info.. Anything with a default I ignore
					$sqlTC = " SELECT title,summary,steps,exresult,mgttcid,keywords,TCorder, version " .
					         " FROM testcase WHERE catid=" . $myrowCat['catid'];
					$resultTC = do_mysql_query($sqlTC);
	
					while ($myrowTC = mysql_fetch_assoc($resultTC)) 
					{
						//insert the test case code
						$sqlInsertTC = " INSERT INTO testcase " .
						               " (title,summary,steps,exresult,catid,mgttcid,keywords,TCorder,version) " .
						               " VALUES ('" . 
								           mysql_escape_string($myrowTC['title']) . "','" . 
								           mysql_escape_string($myrowTC['summary']) . "','" . 
								           mysql_escape_string($myrowTC['steps']) . "','" . 
								           mysql_escape_string($myrowTC['exresult']) . "','" . 
								           mysql_escape_string($CATID)               . "','" . 
								           mysql_escape_string($myrowTC['mgttcid']) . "','" . 
								           mysql_escape_string($myrowTC['keywords']) . "','" . 
								           mysql_escape_string($myrowTC['TCorder']) . "','" . 
								           mysql_escape_string($myrowTC['version']) . "')";
						$resultInsertTC = do_mysql_query($sqlInsertTC);
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


