<?php
/* TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * $Id: planNew.php,v 1.10 2005/12/28 07:34:55 franciscom Exp $ 
 *
 * Purpose:  Add new Test Plan 
 *
 * @author: francisco mancardi - 20051120 - adding test plan filter by product behaivour
 * @author: francisco mancardi - 20050915 - refactoring function name
 * @author: francisco mancardi - 20050810 - deprecated $_SESSION['product'] removed
 * 20051125 - scs - added checking for duplicate tp names
*/
require('../../config.inc.php');
require("../functions/common.php");
require("plan.inc.php");
require_once("../../lib/functions/lang_api.php");
testlinkInitPage();


// 20051120 - fm
// The current selected Product
$prod->id   = $_SESSION['productID'];
$prod->name = $_SESSION['productName'];
$sqlResult = null;
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
		
		//20051125 - scs - added checking for duplicate tp names
		$plans = getAllTestPlans($_SESSION['productID'],null,1);
		$bDuplicate = false;
		for($i = 0;$i < sizeof($plans);$i++)
		{
			if ($plans[$i]['name'] == $name)
			{
				$bDuplicate = true;
				break;
			}
		}
		if (!$bDuplicate)
		{
			if (!insertTestPlan($projID,$name,$notes,$_SESSION['productID']))
			{
				$sqlResult =  $GLOBALS['db']->error_msg();
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
	
					$resultCat = do_sql_query($sqlCat);
					while ($myrowCat = $GLOBALS['db']->fetch_array($resultCat))
					{
						$sqlInsertCat = " INSERT INTO category (compid,mgtcatid,CATorder) " .
								            " VALUES ('" . $GLOBALS['db']->prepare_string($COMID) . " ','" . 
								            $GLOBALS['db']->prepare_string($myrowCat['mgtcatid'])  . "','" . 
								            $GLOBALS['db']->prepare_string($myrowCat['CATorder']) . "')";
						$resultInsertCat = do_sql_query($sqlInsertCat); 
						
						//grab the catid from the last insert so we can use it for the test case
						$CATID = $GLOBALS['db']->insert_id(); 
		
						//grab all of the test case info.. Anything with a default I ignore
						$sqlTC = " SELECT title,summary,steps,exresult,mgttcid,keywords,TCorder, version " .
						         " FROM testcase WHERE catid=" . $myrowCat['catid'];
						$resultTC = do_sql_query($sqlTC);
		
						while ($myrowTC = $GLOBALS['db']->fetch_array($resultTC)) 
						{
							//insert the test case code
							$sqlInsertTC = " INSERT INTO testcase " .
							               " (title,summary,steps,exresult,catid,mgttcid,keywords,TCorder,version) " .
							               " VALUES ('" . 
									           $GLOBALS['db']->prepare_string($myrowTC['title']) . "','" . 
									           $GLOBALS['db']->prepare_string($myrowTC['summary']) . "','" . 
									           $GLOBALS['db']->prepare_string($myrowTC['steps']) . "','" . 
									           $GLOBALS['db']->prepare_string($myrowTC['exresult']) . "','" . 
									           $GLOBALS['db']->prepare_string($CATID)               . "','" . 
									           $GLOBALS['db']->prepare_string($myrowTC['mgttcid']) . "','" . 
									           $GLOBALS['db']->prepare_string($myrowTC['keywords']) . "','" . 
									           $GLOBALS['db']->prepare_string($myrowTC['TCorder']) . "','" . 
									           $GLOBALS['db']->prepare_string($myrowTC['version']) . "')";
							$resultInsertTC = do_sql_query($sqlInsertTC);
						}//end the tc loop
					}//end the cat loop
				}//end the com loop
			}//end the copy if statement
		}
		else
			$sqlResult = lang_get('duplicate_tp_name');
	}
}

$smarty = new TLSmarty;

// 20051120 - fm
$smarty->assign('prod_name', $prod->name);
$smarty->assign('arrPlan', getAllActiveTestPlans($prod->id,FILTER_BY_PRODUCT));
$smarty->assign('sqlResult', $sqlResult);
$smarty->display('planNew.tpl');
?>


