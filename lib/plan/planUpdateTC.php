<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * @version $Id: planUpdateTC.php,v 1.15 2006/04/26 07:07:55 franciscom Exp $
 * @author Martin Havlat
 * 
 * Update Test Cases within Test Case Suite 
 * 
 * @author Francisco Mancardi - 20051207 - BUGID 284
 *
 * @author Francisco Mancardi - 20051112
 * BUGID 0000218
 *
 * @author Francisco Mancardi - 20051009
 * BUGID 0000162: Moving a Testcase to another category
 *
 * @author Francisco Mancardi - 20050821
 * corrected autogol exresults KO, exresult OK
 *
 * 20051112 - scs - cosmetic changes, added escaping of testCase-Title,
 * 					added localization of status
 */         
require('../../config.inc.php');
require_once("../functions/common.php");
testlinkInitPage($db);

$resultString = null;
$arrData = array(); 
$tpID = $_SESSION['testPlanId'];

if(isset($_POST['updateSelected']))
{
	tLog('$_POST: ' . implode(',', $_POST));
	// removed the submit button
	unset($_POST['updateSelected']);
	
	while ($idUpdate = current($_POST)) 
	{
		// 20050730 - fm - Grab Test Case Spec ID from Test Plan Test Case ID
		$tcID = key($_POST);
		tLog('Update TC id: ' . $tcID);
 
    // 20051207 - fm - need the compid - BUGID 284
	  // 20050929 - fm - refactoring name 
	  // 20050730 - fm - BUGID: SF1242462
		$sql = " SELECT testcase.*,  cat.id as cat_id, mgtcat.name as cat_name, 
		                cat.mgtcatid as cat_mgtcatid,  comp.mgtcompid  
		         FROM testcase, category cat, mgtcategory mgtcat, component comp
		         WHERE cat.mgtcatid = mgtcat.id
		         AND   cat.compid   = comp.id 
		         AND   cat.id       = testcase.catid " .
		       " AND testcase.id=" . $tcID;

    //  AND   comp.mgtcompid = mgtcomp.id 
		       
		$result = $db->exec_query($sql);
		$tctp_data = $db->fetch_array($result);

  
		$specId = $tctp_data['mgttcid'];

    // 20050730 - fm - BUGID: SF1242462
		// Grab the relevant data from tc specs (mgt* Tables)
		// mgtTestCase table
		$tc_specs = get_tc_specs($db,$specId);

	  $mgtRow = $tc_specs;
		$mgtTitle = $db->prepare_string($mgtRow['title']);
		$mgtSteps = $db->prepare_string($mgtRow['steps']);
		$mgtExresult = $db->prepare_string($mgtRow['exresult']);
		$mgtKeywords = $mgtRow['keywords'];
		$mgtCatid   = $mgtRow['catid'];
		$mgtVersion = $mgtRow['version'];
		$mgtSummary = $db->prepare_string($mgtRow['summary']);
		$mgtTCorder = $mgtRow['TCorder'];
		
		// 20051207 - fm - BUGID 284
		$mgtCompid = $mgtRow['compid'];
		
		if($mgtVersion == "")
		{
			del_tc_from_tp($db,$tcID);
			$resultString .= "<p>".lang_get('planupdate_tc_deleted1')." [" . $specId . "] ".
			                       lang_get('planupdate_tc_deleted2')."</p>";
		}
		else
		{
			// 20050730 - fm - BUGID: SF1242462
			// Build the query to Update the testcase with the new data
			$updateSQL =  'update testcase ' .
			              'set TCorder=' . $mgtTCorder . 
			              ' , title="'      . $mgtTitle . 
			              '", steps="'    . $mgtSteps . 
			              '", exresult="' . $mgtExresult . 
			              '", keywords="' . $mgtKeywords . 
			              '", version="'  . $mgtVersion . 
			              '", summary="'  . $mgtSummary . '" '; 



			if( $mgtCatid != $tctp_data['cat_mgtcatid'])
			{	
				echo "// Category Has Changed !!!";
				$cat_id = process_tc_cat_change($db,$tcID, $tc_specs, $tpID);		
				$updateSQL .= ', catid=' . $cat_id;
			} 
			$updateSQL .= ' where id=' . $tcID;
			$updateResult = $db->exec_query($updateSQL);
			
			
			// 20051207 - fm - if category moved => component has changed => category table has to be updated
			// BUGID 284
			if( $mgtCompid != $tctp_data['compid'])
			{	
				// Component Has Changed !!!
				process_tc_comp_change($db,$tcID, $tc_specs, $tpID);		
			} 
			
			
			$resultString .= "<p>".lang_get('planupdate_tc_updated1')." [" . $specId . "]: ".htmlspecialchars($mgtTitle) . 
			                       lang_get('planupdate_tc_updated2')."</p>";
			                       
			                       
			                       
		}
   		next($_POST);
	}
	if (!count($_POST))
	{
		$resultString = "<p>".lang_get('plan_update_no_tc_updated')."</p>\n";
	}
}

// walk through the testplan test cases
$sqlTC = " SELECT testcase.id from testcase, category, component " .
		     " WHERE testcase.catid = category.id AND category.compid = component.id " .
		     " AND component.projid = " . $tpID;
$resultTC = $db->exec_query($sqlTC);
if ($resultTC)
{
	while($rowTC = $db->fetch_array($resultTC))
	{
		displayTC($db,$rowTC[0],$arrData); 
	}
}
$changesRequired = (!count($arrData)) ? 'no' : 'yes'; 

$smarty = new TLSmarty();
$smarty->assign('changesRequired', $changesRequired);
$smarty->assign('testPlanName', $_SESSION['testPlanName']);
$smarty->assign('resultString', $resultString);
$smarty->assign('arrData', $arrData);
$smarty->display('planUpdateTC.tpl');




//20050730 - fm - BUGID: SF1242462
function displayTC(&$db,$id,&$arrData)
{
  // 20051206 - fm - COMP.mgtcompid
  // 20051112 - fm - join with mgttestcase
  // 20050730 - fm
  // BUGID: SF1242462
  // added category.mgtcatid, testcase.catid
  //
	$sql = " SELECT MGTCAT.name AS TPTC_category, MGTCOMP.name AS TPTC_component,
	         TC.id, TC.title, TC.version, TC.mgttcid, CAT.mgtcatid, TC.catid,
	         TC.TCOrder AS TPTC_order, MGTTC.TCOrder AS MGTTC_order, COMP.mgtcompid 
	         FROM testcase TC, component COMP, category CAT, 
	              mgttestcase MGTTC, mgtcategory MGTCAT, mgtcomponent MGTCOMP 
	         WHERE CAT.mgtcatid = MGTCAT.id 
	         AND COMP.mgtcompid = MGTCOMP.id 
	         AND COMP.id=CAT.compid
	         AND CAT.id=TC.catid 
	         AND TC.mgttcid=MGTTC.id " .
	       " AND TC.id=" . $id . 
	       " ORDER BY TC.TCorder";
  
     
	$result = $db->exec_query($sql);
	while($row = $db->fetch_array($result)){

		//Assign values from the test case query
		$id = $row['id'];
		$title = $row['title'];
		$version = $row['version'];
		$mgtID = $row['mgttcid'];
		$containerName = $row['TPTC_component'] . '/' . $row['TPTC_category'];

    // 20051207 - fm - compid is needed - BUGID 284
	  // 20050730 - fm - BUGID: SF1242462 added , catid
		$sqlMgt = " SELECT version, catid, compid 
		            FROM mgttestcase, mgtcategory
		            WHERE mgttestcase.catid = mgtcategory.id " .
		          " AND mgttestcase.id=" . $mgtID;
		          
		$mgtResult = $db->exec_query($sqlMgt);
		$mgtRow = $db->fetch_array($mgtResult);

		if ($db->num_rows($mgtResult) == 0) 
		{
			$mgtVersion = "---";
			$status = lang_get("deleted");
		}
		else
		{
			$row = $db->fetch_array($mgtResult);
			$mgtVersion = $row['version'];
			$status = lang_get("updated");
		}
		
		// paste data if versions differs
	    // 20050730 - fm - BUGID: SF1242462
		$load_data = 0;
		$reason_to_update = "";
		if ($version != $mgtVersion )
		{
			$reason_to_update .= lang_get('different_versions');
			$load_data = 1;
		} 

    // 20051207 - fm - BUGID 284
    if( $row['mgtcompid'] != $mgtRow['compid']) 
		{
			if ($load_data )
			{	
				$reason_to_update .= " / ";
			}	
			$reason_to_update .= lang_get('component_has_changed');
			$load_data = 1;
		}


    if( $row['mgtcatid'] != $mgtRow['catid']) 
		{
			if ($load_data )
			{	
				$reason_to_update .= " / ";
			}	
			$reason_to_update .= lang_get('category_has_changed');
			$load_data = 1;
		}

		// 20051112 - check fot TC order changes
		if( $row['TPTC_order'] != $row['MGTTC_order'] )
		{
			if ($load_data )
		  {	
			  $reason_to_update .= " / ";
		  }	
			$reason_to_update .= lang_get('tcorder_has_changed');
			$load_data = 1;
		}


		if ($load_data)
		{
			$arrData[] = array("container" => $containerName, "specId" => $mgtID, 
				                 "planId" => $id, "name" => $title, "status" => $status, 
				                 "specVersion" => $mgtVersion, "planVersion" => $version, 
				                 "reason" => $reason_to_update);
		}
	}
}

// ----------------------------------------------------------------------------
// get_tc_specs() - GET TestCase SPECifications 
// get data from MGT tables
//
// args:
//
// returns: assoc. array with tc data
//
// rev :
//       20050731 - fm
//       creation
//       Added to solve 
//       BUGID: SF1242462 - test plan not updated when test case category changed
//
// ----------------------------------------------------------------------------
function get_tc_specs(&$db,$tc_id)
{
	$sql = " SELECT MGTTC.id as tcid,  catid, compid,  MGTTC.title , " .
	       " MGTCAT.name as cat_name, MGTCOMP.name as comp_name, " .
	       " steps, exresult, keywords, version, summary, TCorder " . 
	       " FROM mgttestcase  MGTTC , mgtcategory MGTCAT, mgtcomponent MGTCOMP " .
	       " WHERE MGTCOMP.id = MGTCAT.compid " .
	       " AND   MGTCAT.id = MGTTC.catid " .
	       " AND   MGTTC.id=" . $tc_id ;
	      
	$result = $db->exec_query($sql);
	$row = $db->fetch_array($result);
	          
	return $row;
}

// ----------------------------------------------------------------------------
// del_tc_from_tp() - DELete TestCase data FROM (some) Test Plan tables
//
// args: TestCase ID
//
// returns: -
//
// rev :
//      20050731 - fm
//      creation
//      Added for refactoring while trying to solve:
//      BUGID: SF1242462 - test plan not updated when test case category changed
// ----------------------------------------------------------------------------
function del_tc_from_tp(&$db,$tc_id)
{
	$sql = "DELETE FROM testcase WHERE id=" . $tc_id;
	$dummy = $db->exec_query($sql);
	
	$sql = "DELETE FROM results WHERE tcid=" . $tc_id;
	$dummy = $db->exec_query($sql); 
	
	$sql = "DELETE FROM bugs WHERE tcid=" . $tc_id;
	$dummy = $db->exec_query($sql); 
}

// ----------------------------------------------------------------------------
//
// Manages the process of updating Test Plan data when a TC has changed
// it's category
//
// 
// args: Test Case ID
//       Test Case Specifications data (assoc Array )
//
// returns:
//         the category id for Test Case ID, in the Test Plan
//
// rev:
//      20051009 - fm
//      BUGID 0000162: Moving a Testcase to another category
//      interface changes added $tpID
//
//      20050731 - fm
//      creation
//      Added to solve 
//      BUGID: SF1242462 - test plan not updated when test case category changed
//
function process_tc_cat_change(&$db,$tc_id, $tc_specs, $tpID)
{
	//
	// If testcase spec has changed category (mgtcat), 
	// we need to verify if this mgtcat is present in the Test Plan
	// If yes -> we need only to do an update
	// If not -> we need to add the category to the testplan, and maybe
	//           the component.(*)
	//           (*) Attention: 
	//               versions 1.5.x ALLOW only test case
	//               moving between CATEGORIES of the SAME COMPONENT
	
	// 20051009 - fm - my bug
	$sql = " SELECT CAT.* " .
	       " FROM category CAT, component COMP " .
	       " WHERE CAT.compid = COMP.id " .
	       " AND COMP.projid = " .  $tpID .
	       " AND CAT.mgtcatid=" . $tc_specs['catid'];
	$result = $db->exec_query($sql);
	
	if ($db->num_rows($result) == 0) 
	{
		// mgtcat belongs to a mgtcomp, then we need to check is mgtcomp
		// is part of the test plan.
		$sql = " SELECT * FROM component " .
				   " WHERE component.projid = " . $tpID .
				   " AND mgtcompid=" . $tc_specs['compid'];
		$result = $db->exec_query($sql);
		
		if ($db->num_rows($result) == 0) 
		{
			echo "MGT Comp and Cat have to be added to Test Plan";    
		}
		else
		{
			// fm - my bug - missing JOIN condition with tpID
			$sql = " SELECT MGTCAT.id as mgtcat_id, MGTCAT.name mgtcat_name, " .
				" MGTCAT.compid as mgtcat_compid, MGTCAT.CATorder as mgtcat_CATorder, " .
				" COMP.id compid " .
				" FROM mgtcategory MGTCAT, component COMP" .
				" WHERE MGTCAT.compid = COMP.mgtcompid" .
				" AND COMP.projid = " . $tpID .
				" AND MGTCAT.id=" . $tc_specs['catid'] ;
				
			$result = $db->exec_query($sql);
			$mgtcatRow = $db->fetch_array($result);
			
			// 20051009 - fm
			// BUGID 0000162: Moving a Testcase to another category
			// problem name field removed from category table.
			// excerpt from planAddTC.php
			$sqlAddCAT = " INSERT INTO category(mgtcatid,compid,CATorder) " .
						" VALUES (" . $mgtcatRow['mgtcat_id'] . "," . 
			$mgtcatRow['compid'] . "," . 
			$mgtcatRow['mgtcat_CATorder'] . ")";
			$resultAddCAT = $db->exec_query($sqlAddCAT); 
			$cat_id =  $db->insert_id();
		}    
	}      
	else   
	{
		$cat_row = $db->fetch_array($result);
		$cat_id = $cat_row['id'];
	}   
	
	return $cat_id;
}


// 20051207 - fm - BUGID 284
function process_tc_comp_change(&$db,$tc_id, $tc_specs, $tpID)
{
	//
	// A testcase spec can change only when the category has been
	// moved to another component.
	// An this can be done only INSIDE a Product.
	//
	// we need to verify if this component is present in the Test Plan
	// If yes -> we need only to do an update
	// If not -> 
	//           we need to add the component to the testplan, and maybe
	//           the category.
	//
	// print_r($tc_specs);
	
	$sql = " SELECT COMP.id AS compid, COMP.mgtcompid
	         FROM   component COMP 
	         WHERE  COMP.projid = " .  $tpID .
	       " AND COMP.mgtcompid=" . $tc_specs['compid'];
	
	$result = $db->exec_query($sql);
	
	
	if ($db->num_rows($result) == 0) 
	{
	  $sql = " INSERT INTO component (mgtcompid,projid)
	  				 VALUES (" . $tc_specs['compid'] . "," . $tpID . ")";
	   
	  $result  =  $db->exec_query($sql);
	  $comp_id =  $db->insert_id();
	}
	else
	{
		$row = $db->fetch_array($result); 
	  $comp_id =  $row['compid'];
  }

  // get catid 
	$sql = " SELECT testcase.catid  
		       FROM testcase 
		       WHERE testcase.id=" . $tc_id;
	
	$result = $db->exec_query($sql);
	$row = $db->fetch_array($result); 
	$cat_id =  $row['catid'];

  // Now the compid mustbe updated for the category
  $sql = " UPDATE category 
           SET category.compid = " . $comp_id .
         " WHERE category.id = " . $cat_id; 

	$result = $db->exec_query($sql);

}
?>
