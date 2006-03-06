<?php
/* TestLink Open Source Project - http://testlink.sourceforge.net/ */
/* $Id: containerEdit.php,v 1.29 2006/03/06 17:31:01 franciscom Exp $ */
/* Purpose:  This page manages all the editing of test specification containers. */
/*
 *
 * 
 * 20060225 - franciscom
 * 20060106 - scs - fix for 0000326
 * 20051129 - franciscom -  BUGID 0000256
 * 20051010 - am - removed unneccesary php-warnings
 * 20050907 - franciscom - BUGID 0000086
 * 20050830 - franciscom - bug in deleteCOM e deleteCAT 
 * 20050820/20050826 - franciscom - fckeditor/refactoring
 * 20050820 - francisco mancardi - 
 * added missing control con category name length
 *
*/
require_once("../../config.inc.php");
require_once("../functions/common.php");
require('archive.inc.php');
require_once("../../lib/functions/lang_api.php");
require_once("../../third_party/fckeditor/fckeditor.php");
require('containerComp.inc.php');
require('containerCat.inc.php');
require_once("../../lib/plan/plan.inc.php");

require_once('testsuite.class.php');  // 20060226 - franciscom

testlinkInitPage($db);

$tsuite_mgr = New testsuite($db); // 20060226 - franciscom

$my_testsuiteID = isset($_REQUEST['testsuiteID']) ? intval($_REQUEST['testsuiteID']) : null;
$my_containerID = isset($_REQUEST['containerID']) ? intval($_REQUEST['containerID']) : null;

if(!$my_containerID)
{
	$my_containerID = $_SESSION['testprojectID'];	
}

$tsuite_name = isset($_REQUEST['testsuiteName']) ? stripslashes($_REQUEST['testsuiteName']) : null;
$objectID = isset($_GET['objectID']) ? intval($_GET['objectID']) : null;
$bSure = (isset($_GET['sure']) && ($_GET['sure'] == 'yes'));

$smarty = new TLSmarty();

// 20050822 - fm - name/key of fck objects to create and table column name
$a_keys['testsuite'] = array('details');

$a_tpl = array( 'move_testsuite' => 'containerMove.tpl',
                'add_testsuite' => 'containerNew.tpl',
                // 'new_testsuite' => 'containerNew.tpl',
                'delete_testsuite' => 'containerDelete.tpl',
                'reorder_testsuite' => 'containerOrder.tpl',
                'updateTCorder' => 'containerView.tpl',
                'reorderTC' => 'tcReorder.tpl'); 

$a_actions = array ('edit_testsuite' => 0, 'new_testsuite' => 0,                       
                    'delete_testsuite' => 0, 'moveCom' => 0, 
                    'add_testsuite' => 1, 'componentMove' => 0,
                    'addCOM' => 1,  'update_testsuite' => 1);

$the_tpl = null;

foreach ($a_actions as $the_key => $the_val)
{
	if (isset($_POST[$the_key]) )
	{
		$the_tpl = isset($a_tpl[$the_key]) ? $a_tpl[$the_key] : null;
		$action = $the_key;
		$get_c_data = $the_val;
		$level = 'testsuite';
		$warning_empty_name = lang_get('warning_empty_com_name');
		break;
	}
}                    
$smarty->assign('level', $level);
 
// --------------------------------------------------------------------
// create  fckedit objects
//
$amy_keys = $a_keys[$level];
$oFCK = array();
foreach ($amy_keys as $key)
{
	$oFCK[$key] = new FCKeditor($key) ;
	$of = &$oFCK[$key];
	$of->BasePath = $_SESSION['basehref'] . 'third_party/fckeditor/';
	$of->ToolbarSet=$g_fckeditor_toolbar;;
}

if($get_c_data)
{
	$name_ok = 1;
	$c_data = get_values_from_post($amy_keys);
	
	// BUGID 0000086
	if($name_ok && !check_string($c_data['name'],$g_ereg_forbidden))
	{
		$msg = lang_get('string_contains_bad_chars');
		$name_ok = 0;
	}
	
	if($name_ok && strlen($c_data['name']) == 0)
	{
		$msg = $warning_empty_name;
		$name_ok = 0;
	}
}


if($action == 'edit_testsuite' || $action == 'new_testsuite')
{
	$tsuite_mgr->viewer_edit_new($amy_keys, $oFCK, $action,$my_containerID, $my_testsuiteID);
}
else if($action == 'add_testsuite')
{
	// we will arrive here after submit in containerNew.tpl (newCOM)
	if ($name_ok)
	{
		$msg = 'ok';
		$ret =$tsuite_mgr->create($my_containerID,$c_data['name'],$c_data['details'],
                              $g_check_names_for_duplicates,
		                          $g_action_on_duplicate_name);
		                             
		if (!$ret['status_ok'] )                             
		{	
			$msg = $ret['msg'];
		}	
	}

	// setup for displaying an empty form
	foreach ($amy_keys as $key)
	{
		// Warning:
		// the data assignment will work while the keys in $the_data are identical
		// to the keys used on $oFCK.
		$of = &$oFCK[$key];
		$smarty->assign($key, $of->CreateHTML());
	}
	$smarty->assign('sqlResult',$msg);
	$smarty->assign('containerID',$my_tprojectID);
}

else if($action == 'update_testsuite')
{
	if( $name_ok )
	{
	    $msg = 'ok';
	  	if (!$tsuite_mgr->update($my_testsuiteID,$c_data['name'],$c_data['details']))
	  	{
	  		$msg = $db->error_msg();
	  	}
	}	
	$tsuite_mgr->show($my_testsuiteID);
	
	// showComponent($db,$my_testsuiteID, $msg);
}
else if ($action == 'delete_testsuite')
{
	// delete item and inner data (cat + tc) 
	//check to see if the user said he was sure he wanted to delete
	if($bSure)
	{
		$cats = null;
		$smarty->assign('sqlResult', 'ok');

		$cats=getComponentCategoryIDs($db,$objectID);
		if (sizeof($cats))
		{
			// 20051208 - fm 
			// $catIDs = "'".implode(",",$cats)."'";
			$catIDs = implode(",",$cats);
			deleteCategoriesTestCases($db,$catIDs);
			deleteComponentCategories($db,$objectID);
		}
		if (!deleteComponent($db,$objectID))
		{
		  $smarty->assign('sqlResult', $db->error_msg());
		}
		
		// 20051208 - fm 
		del_tp_info_by_mgtcomp($db,$objectID);
	}
	else
	{
		//if the user has clicked the delete button on the archive page show the delete confirmation page
		$smarty->assign('objectName', $tsuite_name);
		$smarty->assign('objectID', $my_testsuiteID);
	}
}
else if( $action == 'moveCom') 
{
	$products = null;
	$my_tprojectID;
	getAllProductsBut($db,0,$products);

	$smarty->assign('old_containerID', $my_tprojectID); // original container
	$smarty->assign('arraySelect', $products);
	$smarty->assign('objectID', $my_testsuiteID);
}
else if($action == 'reorder_testsuite') //user has chosen the reorder CAT page
{
	$cats = null;
	getOrderedComponentCategories($db,$my_testsuiteID,$cats);

	$smarty->assign('arraySelect', $cats);
	$smarty->assign('data', $my_testsuiteID);
}
else if($action == 'update_testsuite_Order') //Execute update categories order
{
	$newArray = hash2array($_POST,true);
	$generalResult = 'ok';
	
	//skip the first one, this is the submit button
	$qta_loops=sizeof($newArray);
	for($i = 1;$i < $qta_loops ;$i++)
	{
		$catID = intval($newArray[$i++]);
		$order = intval($newArray[$i]);
		
		if (!updateCategoryOrder($db,$catID,$order))
			$generalResult .= lang_get('error_update_catorder')." {$catID}";
	}

	showComponent($db,$my_testsuiteID, $generalResult);
}
else if($action == 'reorderTC') 
{
	//user has chosen to reorder the test cases of this category
	$tcs = null;
	getOrderedCategoryTestcases($db,$my_categoryID,$tcs);

	$smarty->assign('arrTC', $tcs);
	$smarty->assign('data', $my_categoryID);
	
} //Update db according to a category's reordered test cases
else if($action == 'updateTCorder') 
{
	$newArray = hash2array($_POST,true); //Reorder the POST array to numeric
	$generalResult = 'ok';
	
	//skip the first one, this is the submit button
	for($i = 1;$i < sizeof($newArray);$i++)
	{
		$id = intval($newArray[$i++]);
		$order = intval($newArray[$i]);
		
		if (!updateTestCaseOrder($db,$id,$order))
			$generalResult .= $db->error_msg() . '<br />';
	}

	$smarty->assign('sqlResult', $generalResult);
	$smarty->assign('data', getCategory($db,$my_categoryID));
}
else if($action == 'componentCopy' || $action == 'componentMove')
{
	$prodID = $_SESSION['testprojectID'];
	
	//20051013 - am - fix for 115
	$copyKeywords = isset($_POST['copyKeywords']) ? intval($_POST['copyKeywords']) : 0;
	
	copy_or_move_comp($db, $action, $objectID, $prodID ,$_POST,$_SESSION['userID'],$copyKeywords);
}	
else 
{
	trigger_error("containerEdit.php - No correct GET/POST data", E_USER_ERROR);
}

if ($the_tpl)
{
	$smarty->display($the_tpl);
} 



function get_values_from_post($akeys2get)
{
	$amy_post = $akeys2get;
	$amy_post[] = 'name';
	$c_data = array();
	foreach ($amy_post as $key)
	{
		$c_data[$key] = isset($_POST[$key]) ? strings_stripSlashes($_POST[$key]) : null;
	}
	return $c_data;
}	
?>

