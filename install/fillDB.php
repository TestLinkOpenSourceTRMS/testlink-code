<?php
require('../config.inc.php');
require('../lib/functions/common.php');
include('../lib/keywords/keywords.inc.php');
include('../lib/testcases/archive.inc.php');
include('../lib/plan/plan.inc.php');
include('../lib/functions/product.inc.php');
testlinkInitPage(FALSE,TRUE);
// Execute these queries to clear you DB before execting this script 
/*
DELETE FROM mgtproduct;
DELETE FROM mgtcomponent;
DELETE FROM mgtcategory;
DELETE FROM keywords;
DELETE FROM mgttestcase;
DELETE FROM build;
DELETE FROM project;
DELETE FROM projRights;
DELETE FROM results;
DELETE FROM testcase;
DELETE FROM category;
DELETE FROM component;
DELETE from priority;
*/

//change this to your needs
$numberOfProducts = 5;
$numberOfKeywordsPerProduct = 10;
$numberOfComponentsPerProduct = 10;
$numberOfComponentCategories = 10;
$numberOfTestCasesPerCategory = 30;

// this is the userID of the user to whom all these data belongs
$userID = 1;
// this is the username of the user to whom all these data belongs
$userName = "admin";
// we simply use build 1
$build = 1;

//just generate a long string which will be used as general purpose data
$shortSuffix = md5(time());
$suffix = $shortSuffix."AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA";

for($x = 0;$x < $numberOfProducts;$x++)
{
	//create a new project, used mgtproduct table
	$productID = 0;
	$result = createProduct("##TESTPRODUCT##{$x}","red",1);
	if ($result)
		$productID = mysql_insert_id();
	if ($productID)
		$result = updateProduct($productID, "PRODUCT-{$x}-".$shortSuffix, "silver", 'Y', 'N');
	
	$keywordList = null;
	if ($result)
	{
		$keywordIDs = null;
		for($i = 0; $i < $numberOfKeywordsPerProduct;$i++)
		{
			$keyword = "P{$x}-K{{$i}-".$shortSuffix;
			$keywordList .= $keyword.",";
			$result = addNewKeyword($productID,$keyword,"NOTES-".$suffix.$suffix);
			$keywordIDs[] = mysql_insert_id();
		}
	}
	if ($result)
	{
		$testPlanID = 0;
		$result = insertPlan($testPlanID,"P{$x}-TP-".$shortSuffix,$suffix,$productID);
		insertProjectBuild($build,$testPlanID);
		insertProjectUserRight($testPlanID,$userID);
		insertProjectPriorities($testPlanID);
		$states = array("p","f","b","n");	
		for($i = 0;$i < $numberOfComponentsPerProduct;$i++)
		{
			$compName = "P{$x}-COM{$i}-".$shortSuffix;
			$compID = insertProductComponent($productID,$compName,$suffix,$suffix,$suffix,$suffix,$suffix);
			
			$query = "insert into component (name,mgtcompid,projid) values ('" . $compName . "'," . $compID. "," . $testPlanID . ")";
			$result = mysql_query($query);
			$tpCompID =  mysql_insert_id();
			
			for($j = 0;$j < $numberOfComponentCategories;$j++)
			{
				$catName = "P{$x}-COM{$i}-CAT{$j}-".$shortSuffix;
				$catID = insertComponentCategory($compID,$catName,$suffix,$suffix,$suffix,$suffix);
				
				$query = "insert into category(name,mgtcatid,compid,owner) values ('" . $catName . "'," . $catID . "," . $tpCompID . ",'".$userName."')";
				$result = mysql_query($query);
				$tpCatID = mysql_insert_id();
				
				
				for($k = 0;$k < $numberOfTestCasesPerCategory;$k++)
				{
					$title = "P{$x}-COM{$i}-CAT{$j}-TC-{$k}-".$shortSuffix;
					$tcID = insertTestcase($catID,$title,$suffix,$suffix,$suffix,$userID,null,$keywordList.",");

					$query = "insert into testcase(title,mgttcid,catid,summary,steps,exresult,version,keywords) values ('" . $title . "'," . $tcID . "," . $tpCatID  . ",'" . $suffix . "','" . $suffix . "','" . $suffix . "'," . 1 . ",'" . $keywordList . "')";
					$result = mysql_query($query);
					$tpTCID =  mysql_insert_id();
					
					$state = $states[round(rand(0,3))];
					$sql = "insert into results (build,daterun,status,tcid,notes,runby) values ('" . $build . "',CURRENT_DATE(),'" . $state . "','" . $tpTCID . "','" . "AUTOMATIC" . "','" . $userName . "')";
					$result = mysql_query($sql);
				}
			}
		}
	}
}
?>