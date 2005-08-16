<?php

////////////////////////////////////////////////////////////////////////////////
// @version $Id: planAddTC.php,v 1.2 2005/08/16 18:00:57 franciscom Exp $
// File:     planAddTC.php
// Author:   Chad Rosen
// Purpose:  This page manages the importation of test cases into testlink.
//
//
// 20050807 - fm - removed deprecated: $_SESSION['project']
//
////////////////////////////////////////////////////////////////////////////////
require('../../config.inc.php');
require("../functions/common.php");
require("plan.inc.php");
require_once("../../lib/functions/lang_api.php");
testlinkInitPage();

// 20050807 - fm
$idPlan =  $_SESSION['testPlanId'];

function dispCategories($keyword, $resultCat) 
{
	$idPlan =  $_SESSION['testPlanId'];
	
	$arrData = array();
	while($rowCAT = mysql_fetch_array($resultCat))
	{ 
		$arrTestCases = array();					
		$idCAT = $rowCAT[0];
		$nameCAT = $rowCAT[1];
		
		//Check the keyword that the user has submitted.
		if($keyword == 'NONE')
		{
			//If they keyword is NONE then just do a regular query
			$sqlTC = "SELECT id, title FROM mgttestcase WHERE catid='" . $idCAT . "' ORDER BY TCorder,id";
		}
		else
		{
			$keyword = mysql_escape_string($keyword);
			//keywordlist always have a trailing slash, so there are only two cases to consider the keyword is the first in the list
			//or its in the middle of list 		 
			$sqlTC = "SELECT id, title FROM mgttestcase WHERE catid='" . $idCAT . 
			         "' AND (keywords LIKE '%,{$keyword},%' OR keywords like '{$keyword},%') order by TCorder,id";
		}
		$resultTC = do_mysql_query($sqlTC);
		
		while($rowTC = mysql_fetch_array($resultTC))
		{ 
			//Display all test cases
			$idTC = $rowTC[0]; //Get the test case ID
			$titleTC = $rowTC[1]; //Get the test case title
			
			//Displays the test case name and a checkbox next to it
			//
			// 20050807 - fm - $idPlan
			
			$sqlCheck = "SELECT mgttcid FROM project,component,category,testcase " .
			            "WHERE mgttcid=" . $idTC . 
			            " AND project.id=component.projid AND component.id=category.compid AND " .
			            " category.id=testcase.catid AND project.id=" . $idPlan;
			$checkResult = do_mysql_query($sqlCheck);
			$checkRow = mysql_num_rows($checkResult);
			
			array_push($arrTestCases, array( 'id' => $idTC, 'name' => $titleTC,
											'added' => $checkRow));
		}
		
		array_push($arrData, array( 'id' => $idCAT, 'name' => $nameCAT,
									'tc' => $arrTestCases));
	}
	
	return $arrData;
}

$smarty = new TLSmarty;
$smarty->assign('testPlanName', $_SESSION['testPlanName']);

//Defining the keyword variable which is received from the left frame
$keyword = isset($_GET['key']) ? strings_stripSlashes($_GET['key']) : 'NONE';
if($keyword != 'NONE')
	$smarty->assign('key', $keyword);


if(isset($_POST['addTC'])) //If the user submits the import form
{
	$i = 0;
	//This loop goes through all of the $_POST variables and maps them to values
	foreach ($_POST as $key)
    {
		$newArray[$i] = $key;
		$i++;
	}

	for($i = 1; $i < count($newArray); $i++) //Loop through all of the $_POST / $newArray variables
	{
		//If we find a testcase that has been checked then grab the value after it and increment by two
		if($newArray[$i] == 'on') 
		{
			$tcid = $newArray[$i + 1]; //If we find the test case that has been passed through it's value is always the next item in the list
			//Finding CATID for the test case
			$sqlMGTCATID = "select catid from mgttestcase where id='" . $tcid . "'";
			$resultMGTCATID = @do_mysql_query($sqlMGTCATID); //execute the query
			$rowMGTCATID = mysql_fetch_array($resultMGTCATID); //Grab the CATID
	
			//Finding the COMID from the tpid we just found
			$sqlMGTCOMID = "select compid from mgtcategory where id='" . $rowMGTCATID[0] . "'";
			$resultMGTCOMID = @do_mysql_query($sqlMGTCOMID); //execute the query
			$rowMGTCOMID = mysql_fetch_array($resultMGTCOMID); //Grab the CATID

			//This next long set of code looks through the kenny side of the DB and checks to see if each of the
			//Components,categories, or TCs already exist. If one of the top level items exists the function skips down to the next level and checks there. Finally if no TCs exist it does nothing.
			
			//Determining if the component already exists for the project being added to
			//
			// 20050807 - fm - $idPlan
			$sqlCOMID = "select mgtcompid,id from component where mgtcompid='" . $rowMGTCOMID[0] . "' and projid='" .  $idPlan . "'";
			$resultCOMID = @do_mysql_query($sqlCOMID); //execute the query
			
			if(mysql_num_rows($resultCOMID) > 0) //Are there any existing COM?
			{
				$rowResultCOMID = mysql_fetch_row($resultCOMID); //grab the actual COM ID value this time
				$sqlCATID = "select mgtcatid,id from category where mgtcatid='" . $rowMGTCATID[0] . "' and compid='" . $rowResultCOMID[1] . "'";
				$resultCATID = @do_mysql_query($sqlCATID); //execute query
				
				if(mysql_num_rows($resultCATID) > 0) //Are there any existing CAT?
				{
					$rowResultCATID = mysql_fetch_row($resultCATID); //grab the actual CAT ID value this time
					$sqlTCID = "select mgttcid from testcase where mgttcid='" . $tcid . "' and catid='" . $rowResultCATID[1] . "'";
					$resultTCID = @do_mysql_query($sqlTCID); //execute query

					if(mysql_num_rows($resultTCID) > 0) //Were there any test case matches?
					{

						//Do nothin
					}
					else //If the test case doesn't already exist
					{
						//Figure out the testcase info to be added
						$sqlAddMgtTC = "select title,summary,steps,exresult,version,keywords,TCorder from mgttestcase where id='" . $tcid . "'";
						$resultAddMgtTC = do_mysql_query($sqlAddMgtTC);
						$rowMGTAddTC = mysql_fetch_array($resultAddMgtTC); //Grab the TCID

						//Add the testcase to the project
						$steps = $rowMGTAddTC[2];
						$exresult = $rowMGTAddTC[3];
						$summary = $rowMGTAddTC[1];
										
						$sqlAddTC = "insert into testcase(title,mgttcid,catid,summary,steps,exresult,version,keywords,TCorder) values ('" . mysql_escape_string($rowMGTAddTC[0]) . "','" . $tcid . "','" . mysql_escape_string($rowResultCATID[1]) . "','" . mysql_escape_string($summary) . "','" . mysql_escape_string($steps) . "','" . mysql_escape_string($exresult) . "','" . mysql_escape_string($rowMGTAddTC[4]) . "','" . mysql_escape_string($rowMGTAddTC[5]) . "','" . mysql_escape_string($rowMGTAddTC[6]) . "')";
						$resultAddTC = do_mysql_query($sqlAddTC);
					}
					
				}
				else
				{
					//If the category doesn't exist
					//Figure out the category info to be added
					$sqlAddMgtCAT = "select name,CATorder from mgtcategory where id='" . $rowMGTCATID[0] . "'";
					$resultAddMgtCAT = do_mysql_query($sqlAddMgtCAT);
					$rowMGTAddCAT = mysql_fetch_array($resultAddMgtCAT); //Grab the CATID

					//Add the category to the project
					$sqlAddCAT = "insert into category(name,mgtcatid,compid,CATorder) values ('" . mysql_escape_string($rowMGTAddCAT[0]) . "','" . mysql_escape_string($rowMGTCATID[0]) . "','" . mysql_escape_string($rowResultCOMID[1]) . "','" . mysql_escape_string($rowMGTAddCAT[1]) . "')";
					$resultAddCAT = do_mysql_query($sqlAddCAT); //execute the query
					$addCATID =  mysql_insert_id(); //Grab the id of the category just entered

					//Add the test case to the project
					//Figure out the testcase info to be added
					$sqlAddMgtTC = "select title,summary,steps,exresult,version,keywords,TCorder from mgttestcase where id='" . $tcid . "'";
					$resultAddMgtTC = do_mysql_query($sqlAddMgtTC);
					$rowMGTAddTC = mysql_fetch_array($resultAddMgtTC); //Grab the TCID

					$steps = $rowMGTAddTC[2];
					$exresult = $rowMGTAddTC[3];
					$summary = $rowMGTAddTC[1];

					//Add the testcase to the project					
					$sqlAddTC = "insert into testcase(title,mgttcid,catid,summary,steps,exresult,version,keywords,TCorder) values ('" . mysql_escape_string($rowMGTAddTC[0]) . "','" . mysql_escape_string($tcid) . "','" . mysql_escape_string($addCATID)  . "','" . mysql_escape_string($summary) . "','" . mysql_escape_string($steps) . "','" . mysql_escape_string($exresult) . "','" . mysql_escape_string($rowMGTAddTC[4]) . "','" . mysql_escape_string($rowMGTAddTC[5]) . "','" . mysql_escape_string($rowMGTAddTC[6]) . "')";
					$resultAddTC = do_mysql_query($sqlAddTC); //execute query
				}
			}
			else
			{ 
				//Figure out the component info to be added
				$sqlAddMgtCOM = "select name from mgtcomponent where id='" . $rowMGTCOMID[0] . "'";
				$resultAddMgtCOM = do_mysql_query($sqlAddMgtCOM);
				$rowMGTAddCOM = mysql_fetch_array($resultAddMgtCOM); //Grab the COMID

				//Add the component to the project					
				//
				// 20050807 -fm - $idPlan
				$sqlAddCOM = "insert into component (name,mgtcompid,projid) " . 
				             " values ('" . mysql_escape_string($rowMGTAddCOM[0]) . "','" . 
				             mysql_escape_string($rowMGTCOMID[0]) . "','" . $idPlan . "')";
				             
				             
				$resultAddCOM = do_mysql_query($sqlAddCOM); //execute query
				$addCOMID =  mysql_insert_id();	 //Grab the id of the Component just entered
			
				//Figure out the category info to be added
				$sqlAddMgtCAT = "select name,CATorder from mgtcategory where id='" . $rowMGTCATID[0] . "'";
				$resultAddMgtCAT = do_mysql_query($sqlAddMgtCAT);
				$rowMGTAddCAT = mysql_fetch_array($resultAddMgtCAT); //Grab the CATID

				//Add the category to the project					
				$sqlAddCAT = "insert into category(name,mgtcatid,compid,CATorder) values ('" . mysql_escape_string($rowMGTAddCAT[0]) . "','" . mysql_escape_string($rowMGTCATID[0]) . "','" . mysql_escape_string($addCOMID) . "','" . mysql_escape_string($rowMGTAddCAT[1]) . "')";
				$resultAddCAT = do_mysql_query($sqlAddCAT); //execute the query
				$addCATID =  mysql_insert_id(); //Grab the id of the category just entered

				//Add the test case to the project
		
				//Figure out the test case info to be added
				$sqlAddMgtTC = "select title,summary,steps,exresult,version,keywords,TCorder from mgttestcase where id='" . $tcid . "'";
				$resultAddMgtTC = do_mysql_query($sqlAddMgtTC);
				$rowMGTAddTC = mysql_fetch_array($resultAddMgtTC); //Grab the TCID

				//Add the category to the project
				$steps = $rowMGTAddTC[2];
				$exresult = $rowMGTAddTC[3];
				$summary = $rowMGTAddTC[1];
				
				$sqlAddTC = "insert into testcase(title,mgttcid,catid,summary,steps,exresult,version,keywords,TCorder) values ('" . mysql_escape_string($rowMGTAddTC[0]) . "','" . mysql_escape_string($tcid) . "','" . mysql_escape_string($addCATID)  . "','" . mysql_escape_string($summary) . "','" . mysql_escape_string($steps) . "','" . mysql_escape_string($exresult) . "','" . mysql_escape_string($rowMGTAddTC[4]) . "','" . mysql_escape_string($rowMGTAddTC[5]) . "','" . mysql_escape_string($rowMGTAddTC[6]) . "')";
				$resultAddTC = do_mysql_query($sqlAddTC); //execute the query
			}
			$i = $i + 1; //increment the counter plus an extra one to skip the testcase number
		}
	}

	// TODO: real verification
	$smarty->assign('action', 'updated');
	$smarty->assign('item', 'TestPlan');
	$smarty->assign('sqlResult', 'ok');
}


//If the user has selected a component
if($_GET['edit'] == 'component')
{
	$sqlCOM = "SELECT id, name FROM mgtcomponent WHERE id='" . $_GET['data'] . "' ORDER BY name";
	$resultCOM = do_mysql_query($sqlCOM);

	while($rowCOM = mysql_fetch_array($resultCOM))
	{
		$sqlCAT = "SELECT id, name FROM mgtcategory WHERE compid='" . $rowCOM[0] . "' ORDER BY CATorder,id";
		$resultCAT = do_mysql_query($sqlCAT);
		$arrData = dispCategories($keyword, $resultCAT);
		
		$smarty->assign('nameCOM', $rowCOM[1]);
		$smarty->assign('arrData', $arrData);
		$smarty->display('planAddTC.tpl');
	}

}//If the user has selected a category
else if($_GET['edit'] == 'category')
{
  	//Query to grab all of the category information based on what was passed in by the user
  	$sqlCAT = "SELECT id, name FROM mgtcategory WHERE id='" . $_GET['data'] . "' ORDER BY CATorder,id";
  	$resultCAT = do_mysql_query($sqlCAT);
  	$arrData = dispCategories($keyword, $resultCAT);

	$smarty->assign('arrData', $arrData);
	$smarty->display('planAddTC.tpl');
}
else
	redirect($_SESSION['basehref'] .$g_rpath['help'].'/planAddTC.html');
?>