<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: tcEdit.php,v $
 *
 * @version $Revision: 1.27 $
 * @modified $Date: 2006/04/24 17:44:59 $  by $Author: franciscom $
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
require_once("../functions/opt_transfer.php"); // 20060424 - franciscom


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
$testproject_id = $_SESSION['testprojectID'];
$show_newTC_form = 0;
$smarty = new TLSmarty;
$container_id = isset($_GET['containerID']) ? intval($_GET['containerID']) : 0;

$tcase_id = isset($_REQUEST['testcase_id']) ? intval($_REQUEST['testcase_id']) : 0;
$tcversion_id = isset($_REQUEST['tcversion_id']) ? intval($_REQUEST['tcversion_id']) : 0;


$name 		= isset($_POST['name']) ? strings_stripSlashes($_POST['name']) : null;
$summary 	= isset($_POST['summary']) ? strings_stripSlashes($_POST['summary']) : null;
$steps 		= isset($_POST['steps']) ? strings_stripSlashes($_POST['steps']) : null;
$expected_results 	= isset($_POST['expected_results']) ? strings_stripSlashes($_POST['expected_results']) : null;

$new_container_id = isset($_POST['new_container']) ? intval($_POST['new_container']) : 0;
$old_container_id = isset($_POST['old_container']) ? intval($_POST['old_container']) : 0;


// 20060424 - franciscom
$opt_cfg->js_ot_name='ot';
$rl_html_name = $opt_cfg->js_ot_name . "_newRight";
$right_list = isset($_REQUEST[$rl_html_name])? $_REQUEST[$rl_html_name] : "";



// manage the forms to collect data
$edit_tc   = isset($_REQUEST['edit_tc']) ? 1 : 0;
$delete_tc = isset($_POST['delete_tc']) ? 1 : 0;
$create_tc = isset($_POST['create_tc']) ? 1 : 0;
$move_copy_tc = isset($_POST['move_copy_tc']) ? 1 : 0;

// 20060326 - franciscom
$delete_tc_version = isset($_POST['delete_tc_version']) ? 1 : 0;



// really do the operation requested
$do_create = isset($_POST['do_create']) ? 1 : 0;
$do_update = isset($_POST['do_update']) ? 1 : 0;
$do_move   = isset($_POST['do_move']) ? 1 : 0;
$do_copy   = isset($_POST['do_copy']) ? 1 : 0;
$do_delete = isset($_POST['do_delete']) ? 1 : 0;
$do_create_new_version = isset($_POST['do_create_new_version']) ? 1 : 0;
$do_delete_tc_version = isset($_POST['do_delete_tc_version']) ? 1 : 0;


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

echo "<pre>debug -" . __FILE__ ; print_r($_POST); echo "</pre>";

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
    $opt_cfg = opt_transf_empty_cfg();
    $opt_cfg->js_ot_name='ot';
    $opt_cfg->global_lbl=lang_get('title_assign_kw_to_tc');
    $opt_cfg->from->lbl=lang_get('available_kword');
    $opt_cfg->from->map=$tproject_mgr->get_keywords_map($testproject_id);
    $opt_cfg->to->lbl=lang_get('assigned_kword');
    $opt_cfg->to->map=$tcase_mgr->get_keywords_map($tcase_id," ORDER BY keyword ASC ");
    
    //$rl_html_name = $opt_cfg->js_ot_name . "_newRight";
    //$right_list = isset($_REQUEST[$rl_html_name])? $_REQUEST[$rl_html_name] : "";
    keywords_opt_transf_cfg($opt_cfg, $right_list); 
    
    
  	$myrowTC = $tcase_mgr->get_by_id($tcase_id,$tcversion_id);
    
    $keywords_map=$tcase_mgr->get_keywords_map($tcase_id," ORDER BY keyword ASC ");
  
  
  
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
  	$smarty->assign('keywords_map', $keywords_map);
  	$smarty->assign('opt_cfg', $opt_cfg);   // 20060424 - franciscom

  	$smarty->display($g_tpl['tcEdit']);


} 
else if($do_update)
{
  echo "<pre>debug" . __FUNCTION__; print_r($right_list); echo "</pre>";
  
	$refresh_tree='no';
	if( $name_ok)
	{
		$msg = 'ok';

    // to get the name before the user operation
 	  $tc_old = $tcase_mgr->get_by_id($tcase_id,$tcversion_id);
						
		if ($tcase_mgr->update($tcase_id,$tcversion_id,$name,$summary,$steps,$expected_results,
		                        $_SESSION['userID'],$right_list) )
		{
				if( strcmp($tc_old[0]['name'],$name) != 0 )
    		{
  	  			// only refresh menu tree is name changed
  	  			$refresh_tree='yes';
		    }	
		}
    else
    {
    	$sqlResult =  $db->error_msg();
    }
	}	
 	$action_result='updated';
  $tcase_mgr->show($tcase_id, $_SESSION['userID'], $tcversion_id, $action_result,$msg,$refresh_tree);
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
	
	$my_ret= $tcase_mgr->check_link_and_exec_status($tcase_id);
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

  $tcinfo=$tcase_mgr->get_by_id($tcase_id);
	$smarty->assign('title', lang_get('title_del_tc') . $tcinfo[0]['name']);
	
	$smarty->assign('testcase_id', $tcase_id);
	$smarty->assign('tcversion_id', TC_ALL_VERSIONS);

	$smarty->assign('delete_message', $msg);
	//$smarty->assign('delete_input_name', 'do_delete');
	
	$smarty->display('tcDelete.tpl');
}
else if($delete_tc_version)
{

  $status_quo_map = $tcase_mgr->get_versions_status_quo($tcase_id);
  if( intval($status_quo_map[$tcversion_id]['executed']) > 0)
  {
  	$msg = " This test case version has been linked to test plans <br>" .
			     " and has been runned<br>" .
			     " If you confirm the links to test plans, and execution related information will be removed";
  }
	else if( intval($status_quo_map[$tcversion_id]['linked']) > 0)
  {
		$msg = " This test case version has been linked to test plans <br>" .
			     " If you confirm the links to test plans will be removed";
  }
  else
  {
  	$msg='';
  }

  $tcinfo=$tcase_mgr->get_by_id($tcase_id,$tcversion_id);
	$smarty->assign('title', lang_get('title_del_tc') . 
	                         $tcinfo[0]['name'] . lang_get('version') . $tcinfo[0]['version']);
	
	$smarty->assign('testcase_id', $tcase_id);
	$smarty->assign('tcversion_id', $tcversion_id);
	
	$smarty->assign('delete_message', $msg);
	//$smarty->assign('delete_input_name', 'do_delete_tc_version');
	$smarty->display('tcDelete.tpl');
  
}
else if($do_delete)
{
	$msg='';
	$action_result='deleted';
  $verbose_result='ok';
  $tcinfo=$tcase_mgr->get_by_id($tcase_id,$tcversion_id);

	if (!$tcase_mgr->delete($tcase_id,$tcversion_id))
	{
	    $action_result='';
			$verbose_result=$db->error_msg();
	}

  $the_title = lang_get('title_del_tc') . $tcinfo[0]['name'];
  $refresh_tree="yes";
  if( $tcversion_id != TC_ALL_VERSIONS )
  {
  		$the_title .= " " . lang_get('version') . " " . $tcinfo[0]['version'];
  		$refresh_tree="no";
  }
	$smarty->assign('title', $the_title);
  $smarty->assign('sqlResult', $verbose_result);
	$smarty->assign('testcase_id', $tcase_id);
	$smarty->assign('delete_message', $msg);
	$smarty->assign('action',$action_result);
	$smarty->assign('refresh_tree',$refresh_tree);
	
	$smarty->display('tcDelete.tpl');
}
else if($move_copy_tc)
{
  // need to get the testproject for the test case
	$tproject_id=$tcase_mgr->get_testproject($tcase_id);
  $the_tc_node = $tree_mgr->get_node_hierachy_info($tcase_id);
  $tc_parent_id = $the_tc_node['parent_id'];
  $the_tree = $tree_mgr->get_subtree($tproject_id, array("testplan"=>"exclude me",
                                                         "testcase"=>"exclude me"));
  $the_xx = $tproject_mgr->gen_combo_test_suites($tproject_id);
  $the_xx[$the_tc_node['parent_id']] .= ' (' . lang_get('current') . ')'; 
	$tc_info = $tcase_mgr->get_by_id($tcase_id);

	$smarty->assign('old_container', $container_id); // original container
	$smarty->assign('array_container', $the_xx);
	$smarty->assign('testcase_id', $tcase_id);
	$smarty->assign('name', $tc_info[0]['name']);
	$smarty->display('tcMove.tpl');

// move test case to another category
}
else if($do_move)
{
	$result = $tree_mgr->change_parent($tcase_id,$new_container_id);
  $tsuite_mgr->show($old_container_id);
}
else if($do_copy)
{
	$msg='';
	$action_result='copied';
	$result = $tcase_mgr->copy_to($tcase_id,$new_container_id,$_SESSION['userID']);
	if($result)
	{
	  $msg='ok';
	}
	$tcase_mgr->show($tcase_id, $_SESSION['userID'],$tcversion_id,$action_result,$msg);
	
	//$result = copyTc($db,$catID, $tcase_id, $_SESSION['userID']);
	//showCategory($db,$oldCat, $result,'update',$catID);
}
else if($do_create_new_version)
{
	$show_newTC_form = 0;
	$action_result="create_new_version";
	$msg = lang_get('error_tc_add');
	$op=$tcase_mgr->create_new_version($tcase_id,$_SESSION['userID']);
	if ($op['msg'] == "ok")
	{
	  $msg = 'ok';
	}
	
	//echo "<pre>debug" . __FUNCTION__; print_r($op); echo "</pre>";
	define('DONT_REFRESH','no');
	$tcase_mgr->show($tcase_id, $_SESSION['userID'], TC_ALL_VERSIONS, 
	                            $action_result,$msg,DONT_REFRESH);
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

	$prodKeywords = getProductKeywords($db,$testproject_id);
	$smarty->assign('keys',$prodKeywords);
	$smarty->display($g_tpl['tcNew']);
}
?>