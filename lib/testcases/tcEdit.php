<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: tcEdit.php,v $
 *
 * @version $Revision: 1.23 $
 * @modified $Date: 2006/03/20 18:02:37 $  by $Author: franciscom $
 * This page manages all the editing of test cases.
 *
 * @author Martin Havlat
 *
 * 20060305 - franciscom
 * 20060106 - scs - refactoring, fixed bug 9
**/
require_once("../../config.inc.php");
require_once("../functions/common.php");
require('archive.inc.php');
require('../keywords/keywords.inc.php');
require_once("../../third_party/fckeditor/fckeditor.php");
require_once("testcase.class.php"); // 20060226 - franciscom
require_once("testsuite.class.php"); // 20060306 - franciscom


testlinkInitPage($db);



// set variables
// --------------------------------------------------------------------
// create  fckedit objects
$a_ofck = array('summary','steps','expected_results');
$oFCK = array();
foreach ($a_ofck as $key)
{
	$oFCK[$key] = new fckeditor($key) ;
	$of = &$oFCK[$key];
	$of->BasePath = $_SESSION['basehref'] . 'third_party/fckeditor/';
	$of->ToolbarSet=$g_fckeditor_toolbar;;
}

// --------------------------------------------------------------------
$testprojectID = $_SESSION['testprojectID'];
$show_newTC_form = 0;
$smarty = new TLSmarty;
$container_id = isset($_GET['containerID']) ? intval($_GET['containerID']) : 0;

$testcaseID = isset($_GET['testcaseID']) ? intval($_GET['testcaseID']) : 0;
$name 		= isset($_POST['name']) ? strings_stripSlashes($_POST['name']) : null;
$summary 	= isset($_POST['summary']) ? strings_stripSlashes($_POST['summary']) : null;
$steps 		= isset($_POST['steps']) ? strings_stripSlashes($_POST['steps']) : null;
$expected_results 	= isset($_POST['expected_results']) ? strings_stripSlashes($_POST['expected_results']) : null;

$new_container_id = isset($_POST['new_container']) ? intval($_POST['new_container']) : 0;
$old_container_id = isset($_POST['old_container']) ? intval($_POST['old_container']) : 0;

// manage the forms to collect data
$edit_tc   = isset($_REQUEST['edit_tc']) ? 1 : 0;
$delete_tc = isset($_POST['delete_tc']) ? 1 : 0;
$create_tc = isset($_POST['create_tc']) ? 1 : 0;
$move_copy_tc = isset($_POST['move_copy_tc']) ? 1 : 0;

// really do the operation requested
$do_create = isset($_POST['do_create']) ? 1 : 0;
$do_update = isset($_POST['do_update']) ? 1 : 0;
$do_move   = isset($_POST['do_move']) ? 1 : 0;
$do_copy   = isset($_POST['do_copy']) ? 1 : 0;
$do_delete = isset($_POST['do_delete']) ? 1 : 0;


$login_name = $_SESSION['user'];
$version = isset($_POST['version']) ? intval($_POST['version']) : 0; 

$updatedKeywords = null;
if (isset($_POST['keywords']))
{
	$updatedKeywords = strings_stripSlashes(implode(",",$_POST['keywords']).",");
}


// 20060303 - francisco.mancardi@gruppotesi.com
$tcase_mgr = New testcase($db);
$tproject_mgr = New testproject($db);
$tree_mgr = New tree($db);
$tsuite_mgr = New testsuite($db);


echo "<pre>debug"; print_r($_POST); echo "</pre>";

$name_ok = 1;
if($do_create || $do_update)
{
	// BUGID 0000086
	$result = lang_get('warning_empty_tc_title');	
	if($name_ok && !check_string($name,$g_ereg_forbidden) )
	{
		$msg = lang_get('string_contains_bad_chars');
		$name_ok = 0;
	}
	if($name_ok && strlen($name) == 0)
	{
		$msg = lang_get('warning_empty_tc_title');
		$name_ok = 0;
	}
}

//If the user has chosen to edit a testcase then show this code
if($edit_tc)
{
	$setOfKeys = array();
	$myrowTC = $tcase_mgr->get_by_id($testcaseID);
	
	/*
	$tcKeywords = getTCKeywords($db,$testcaseID);
	$prodKeywords = getProductKeywords($db,$testprojectID);
	if (sizeof($prodKeywords))
	{
		if (sizeof($tcKeywords))
			$result = array_intersect($tcKeywords,$prodKeywords);
		else
			$result = array();
			
		for($i = 0;$i < sizeof($prodKeywords);$i++)
		{
			$selected = 'no';
			$keyword = $prodKeywords[$i];
			if (in_array($keyword,$result))
				$selected = 'yes';
			$setOfKeys[] = array( 'key' => $keyword, 
								  'selected' => $selected);
		}
	}
	*/

	foreach ($a_ofck as $key)
  	{
	  	// Warning:
	  	// the data assignment will work while the keys in $the_data are identical
	  	// to the keys used on $oFCK.
	  	$of = &$oFCK[$key];
	  	$of->Value = $myrowTC[0][$key];
	  	$smarty->assign($key, $of->CreateHTML());
	}

	$smarty->assign('tc', $myrowTC[0]);
	$smarty->assign('testcaseID', $testcaseID);
	$smarty->assign('keys', $setOfKeys);

	$smarty->display($g_tpl['tcEdit']);
} 
else if($do_update)
{
	$sqlResult = lang_get('string_contains_bad_chars');
	if( $name_ok)
	{
		$sqlResult = 'ok';

    // 20060303 - franciscom		
    // just to get the tcversion id without considering that more versions
    // can exits
    // THIS MUST BE IMPROVED
    //
		$tc_old = $tcase_mgr->get_by_id($testcaseID);

		if (!$tcase_mgr->update($testcaseID,$tc_old[0]['id'],$name,$summary,$steps,$expected_results,
		                       $_SESSION['userID'],$updatedKeywords))
		{
			$sqlResult =  $db->error_msg();
		}
	}	
 	$action_result='updated';
 	$msg_result='';
  if( strcmp($tc_old[0]['name'],$name) != 0 )
  {
  	$msg_result='ok';
  }	
	$tcase_mgr->show($testcaseID, $_SESSION['userID'],$action_result,$msg_result);
	
		// $tcase_mgr->show($testcaseID, $_SESSION['userID'],$action_result,$msg_result);
}
else if($create_tc)
{
	$show_newTC_form = 1;
}
else if($do_create)
{
	$show_newTC_form = 1;
	
	if ($name_ok)
	{
		$msg = lang_get('error_tc_add');
		if ($tcase_mgr->create($container_id,$name,$summary,$steps,$expected_results,$_SESSION['userID']))
		{
		  $msg = 'ok';
		}
		
	}
  
	$smarty->assign('sqlResult', $msg);
	$smarty->assign('name', $name);
	$smarty->assign('item', 'Test case');
}
else if($delete_tc)
{
	$msg='';
	
	// 20060305 - franciscom
	// check delete conditions
	$my_ret= $tcase_mgr->check_delete_condition($testcaseID);
	switch ($my_ret)
	{
		case "linked_and_executed":
		$msg = " This test case has been linked to test plans <br>" .
			     " and has been runned<br>" .
			     " If you confirm the links to test plans, and execution related information will be removed";
		break;

		case "linked_but_not_executed":
		$msg = " This test case has been linked to test plans <br>" .
			     " If you confirm the links to test plans will be removed";
		break;
		
	}

  $tcinfo=$tcase_mgr->get_by_id($testcaseID);
	$smarty->assign('title', lang_get('title_del_tc') . $tcinfo[0]['name']);
	
	$smarty->assign('testcaseID', $testcaseID);
	$smarty->assign('delete_message', $msg);
	$smarty->display('tcDelete.tpl');
}
else if($do_delete)
{
	$msg='';
	$action_result='deleted';
  $verbose_result='ok';
  $tcinfo=$tcase_mgr->get_by_id($testcaseID);

	if (!$tcase_mgr->delete($testcaseID))
	{
	    $action_result='';
			$verbose_result=$db->error_msg();
	}

	$smarty->assign('title', lang_get('title_del_tc') . $tcinfo[0]['name']);
  $smarty->assign('sqlResult', $verbose_result);
	$smarty->assign('testcaseID', $testcaseID);
	$smarty->assign('delete_message', $msg);
	$smarty->assign('action',$action_result);
	$smarty->display('tcDelete.tpl');
}
else if($move_copy_tc)
{
  // need to get the testproject for the test case
	$tproject_id=$tcase_mgr->get_testproject($testcaseID);
  $the_tc_node = $tree_mgr->get_node_hierachy_info($testcaseID);
  $tc_parent_id = $the_tc_node['parent_id'];
  $the_tree = $tree_mgr->get_subtree($tproject_id, array("testplan"=>"exclude me",
                                                         "testcase"=>"exclude me"));
  $the_xx = $tproject_mgr->gen_combo_test_suites($tproject_id);
  $the_xx[$the_tc_node['parent_id']] .= ' (' . lang_get('current') . ')'; 
	$tc_info = $tcase_mgr->get_by_id($testcaseID);

	$smarty->assign('old_container', $container_id); // original container
	$smarty->assign('array_container', $the_xx);
	$smarty->assign('testcaseID', $testcaseID);
	$smarty->assign('name', $tc_info[0]['name']);
	$smarty->display('tcMove.tpl');

// move test case to another category
}
else if($do_move)
{
	$result = $tree_mgr->change_parent($testcaseID,$new_container_id);
  $tsuite_mgr->show($old_container_id);
}
else if($do_copy)
{
	$action_result='copied';
	$result = $tcase_mgr->copy_to($testcaseID,$new_container_id,$_SESSION['userID']);
	
	if($result)
	{
	  $msg_result='ok';
	}
	$tcase_mgr->show($testcaseID, $_SESSION['userID'],$action_result,$msg_result);
	
	//$result = copyTc($db,$catID, $testcaseID, $_SESSION['userID']);
	//showCategory($db,$oldCat, $result,'update',$catID);
}
else
{
	tlog("A correct POST argument is not found.");
}

// --------------------------------------------------------------------------
if ($show_newTC_form)
{
	$smarty->assign('containerID', $container_id);
	
	foreach ($a_ofck as $key)
	{
		// Warning:
		// the data assignment will work while the keys in $the_data are identical
		// to the keys used on $oFCK.
		$of = &$oFCK[$key];
		$of->Value = "";
		$smarty->assign($key, $of->CreateHTML());
	}

	$prodKeywords = getProductKeywords($db,$testprojectID);
	$smarty->assign('keys',$prodKeywords);
	$smarty->display($g_tpl['tcNew']);
}
?>