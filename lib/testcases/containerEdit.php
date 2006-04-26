<?php
/* TestLink Open Source Project - http://testlink.sourceforge.net/ */
/* $Id: containerEdit.php,v 1.37 2006/04/26 07:07:56 franciscom Exp $ */
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
require_once('archive.inc.php');
require_once("../../lib/functions/lang_api.php");
require_once("../../third_party/fckeditor/fckeditor.php");
require_once('containerComp.inc.php');
require_once('containerCat.inc.php');
require_once("../../lib/plan/plan.inc.php");

require_once('testsuite.class.php');  // 20060226 - franciscom
require_once('testproject.class.php');  // 20060308 - franciscom
require_once('tree.class.php');  // 20060308 - franciscom

testlinkInitPage($db);

$tree_mgr = New tree($db); // 20060308 - franciscom
$tproject_mgr = New testproject($db); // 20060308 - franciscom
$tsuite_mgr = New testsuite($db); // 20060226 - franciscom
$tcase_mgr = New testcase($db); // 20060226 - franciscom



$my_testsuiteID = isset($_REQUEST['testsuiteID']) ? intval($_REQUEST['testsuiteID']) : null;
$my_containerID = isset($_REQUEST['containerID']) ? intval($_REQUEST['containerID']) : null;

$my_tprojectID = $_SESSION['testprojectID'];

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


/*
$a_tpl = array( 'moveCom' => 'containerMove.tpl',
                'addCOM' => 'containerNew.tpl',
                'deleteCOM' => 'containerDelete.tpl',
                'moveCat' => 'containerMove.tpl',
                'addCAT'  => 'containerNew.tpl',
                'deleteCat' => 'containerDelete.tpl',
                'reorderCAT' => 'containerOrder.tpl',
                'updateTCorder' => 'containerView.tpl',
                'reorderTC' => 'tcReorder.tpl'); 
*/


$a_tpl = array( 'move_testsuite_viewer' => 'containerMove.tpl',
                'add_testsuite' => 'containerNew.tpl',
                // 'new_testsuite' => 'containerNew.tpl',
                'delete_testsuite' => 'containerDelete.tpl',
                'reorder_testsuites' => 'containerOrder.tpl',
                'updateTCorder' => 'containerView.tpl',
                'reorderTC' => 'tcReorder.tpl'); 

$a_actions = array ('edit_testsuite' => 0, 'new_testsuite' => 0,                       
                    'delete_testsuite' => 0, 'do_move' => 0, 'do_copy' => 0,
                    'reorder_testsuites' => 1, 'do_testsuite_reorder' => 0,
                    'add_testsuite' => 1, 'move_testsuite_viewer' => 0,
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
	// 20060425 - franciscom
	$tsuite_mgr->show($smarty,$my_testsuiteID,'ok');
	
}
else if ($action == 'delete_testsuite')
{
	// delete item and inner data (cat + tc) 
	//check to see if the user said he was sure he wanted to delete
	if($bSure)
	{

    $tsuite_mgr->delete_deep($objectID);
    exit();
    
    
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
		// Get test cases present in this testsuite and all children
		$testcases=$tsuite_mgr->get_testcases_deep($my_testsuiteID);
		
		if( !is_null($testcases) )
		{
			$verbose=array();
			$warning=array();
			
		  foreach ($testcases as $the_key => $elem)
		  {
		  	 $verbose[] = $tree_mgr->get_path($elem['id'],$my_testsuiteID);
		  }

		  $idx=0; 
		  foreach ($verbose as $the_key => $elem)
		  {
		    foreach ($elem as $tkey => $telem)
		    {
		  	  $warning[$idx] .= $telem['name'] . "\\"; 	
		    }	  
	  		$warning[$idx]=rtrim($warning[$idx], "\\");
		  	$idx++;
		  }	

      reset($testcases);
      $link_msg=array();
		  foreach ($testcases as $the_key => $elem)
		  {
		  	 $link_msg[] = $tcase_mgr->check_link_and_exec_status($elem['id']);
		  }
		}
	
		//if the user has clicked the delete button on the archive page show the delete confirmation page
		$smarty->assign('objectName', $tsuite_name);
		$smarty->assign('warning', $warning);
		$smarty->assign('link_msg', $link_msg);


		
	}
}
else if( $action == 'move_testsuite_viewer') 
{
	$testsuites = null;
	$testsuites =$tproject_mgr->gen_combo_test_suites($my_tprojectID,
	                                                  array($my_testsuiteID => 'exclude'));
  
	$smarty->assign('old_containerID', $my_tprojectID); // original container
	$smarty->assign('arraySelect', $testsuites);
	$smarty->assign('objectID', $my_testsuiteID);
	$smarty->assign('object_name', $tsuite_name);
	
}
else if($action == 'reorder_testsuites') //user has chosen the reorder page
{

	$object_id = is_null($my_testsuiteID) ? $my_containerID : $my_testsuiteID;
  // $children=$tree_mgr->get_children($object_id, array("testplan" => "exclude_me","testcase" => "exclude_me"));	
  $children=$tree_mgr->get_children($object_id, array("testplan" => "exclude_me"));	
  
  /*
  if( !is_null($children) )
  {
  	$aselect=array();
  	foreach($children as $the_key => $elem)
  	{
  	 $aselect[]= 
  	}  
  }
  */
  //echo "<pre>debug" . __FILE__ ; print_r($children); echo "</pre>";
  //exit();
  
	//$cats = null;
	//getOrderedComponentCategories($db,$my_testsuiteID,$cats);

	$smarty->assign('arraySelect', $children);
	$smarty->assign('data', $my_testsuiteID);
}
else if($action == 'do_testsuite_reorder') //Execute update categories order
{
	$generalResult = 'ok';
	
	$tree_mgr->change_order_bulk($_POST['id'],$_POST['order']);
	
	// 20060425 - franciscom
	$tsuite_mgr->show($smarty,$my_containerID,'ok');
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
else if($action == 'do_move')
{
	$tree_mgr->change_parent($objectID,$my_containerID);  
	
	// 20060425 - franciscom
  $tproject_mgr->show($smarty,$my_tprojectID,'ok');
	
	// copy_or_move_comp($db, $action, $objectID, $prodID ,$_POST,$_SESSION['userID'],$copyKeywords);
}	
else if($action == 'do_copy')
{
	$prodID = $_SESSION['testprojectID'];
	
	//20051013 - am - fix for 115
	$copyKeywords = isset($_POST['copyKeywords']) ? intval($_POST['copyKeywords']) : 0;
	
	// copy_or_move_comp($db, $action, $objectID, $prodID ,$_POST,$_SESSION['userID'],$copyKeywords);
	
	//
	$tsuite_mgr->copy_to($objectID, $my_containerID, $_SESSION['userID'],
	                     config_get('check_names_for_duplicates'),
	                     config_get('action_on_duplicate_name'));
	
	// 20060425 - franciscom
	$tsuite_mgr->show($smarty,$objectID,'ok');
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

