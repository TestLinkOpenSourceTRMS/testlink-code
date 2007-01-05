<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: tcEdit.php,v $
 *
 * @version $Revision: 1.47 $
 * @modified $Date: 2007/01/05 13:57:30 $  by $Author: franciscom $
 * This page manages all the editing of test cases.
 *
 *
 * 20061112 - franciscom
 * added logic for new operations: activate/deactivate test case version
 *
**/
require_once("../../config.inc.php");
require_once("../functions/common.php");
require_once('archive.inc.php');
require_once('../keywords/keywords.inc.php');
require_once("../../third_party/fckeditor/fckeditor.php");
require_once("../functions/opt_transfer.php");
testlinkInitPage($db);


$gui_cfg=config_get('gui');
$order_cfg=config_get('tree_node_ordering');

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
$userID = $_SESSION['userID'];
$show_newTC_form = 0;
$smarty = new TLSmarty();

$container_id = isset($_GET['containerID']) ? intval($_GET['containerID']) : 0;
$tcase_id = isset($_REQUEST['testcase_id']) ? intval($_REQUEST['testcase_id']) : 0;
$tcversion_id = isset($_REQUEST['tcversion_id']) ? intval($_REQUEST['tcversion_id']) : 0;

$name 		= isset($_POST['testcase_name']) ? strings_stripSlashes($_POST['testcase_name']) : null;
$summary 	= isset($_POST['summary']) ? strings_stripSlashes($_POST['summary']) : null;
$steps 		= isset($_POST['steps']) ? strings_stripSlashes($_POST['steps']) : null;
$expected_results 	= isset($_POST['expected_results']) ? strings_stripSlashes($_POST['expected_results']) : null;
$new_container_id = isset($_POST['new_container']) ? intval($_POST['new_container']) : 0;
$old_container_id = isset($_POST['old_container']) ? intval($_POST['old_container']) : 0;

$opt_cfg->js_ot_name='ot';
$rl_html_name = $opt_cfg->js_ot_name . "_newRight";
$assigned_keywords_list = isset($_REQUEST[$rl_html_name])? $_REQUEST[$rl_html_name] : "";

// manage the forms to collect data
$edit_tc   = isset($_REQUEST['edit_tc']) ? 1 : 0;
$delete_tc = isset($_POST['delete_tc']) ? 1 : 0;
$create_tc = isset($_POST['create_tc']) ? 1 : 0;
$move_copy_tc = isset($_POST['move_copy_tc']) ? 1 : 0;

$delete_tc_version = isset($_POST['delete_tc_version']) ? 1 : 0;

// really do the operation requested
$do_create = isset($_POST['do_create']) ? 1 : 0;
$do_update = isset($_POST['do_update']) ? 1 : 0;
$do_move   = isset($_POST['do_move']) ? 1 : 0;
$do_copy   = isset($_POST['do_copy']) ? 1 : 0;
$do_delete = isset($_POST['do_delete']) ? 1 : 0;
$do_create_new_version = isset($_POST['do_create_new_version']) ? 1 : 0;
$do_delete_tc_version = isset($_POST['do_delete_tc_version']) ? 1 : 0;


// 20061104 - franciscom
$do_activate_this = isset($_POST['activate_this_tcversion']) ? 1 : 0;
$do_deactivate_this = isset($_POST['deactivate_this_tcversion']) ? 1 : 0;

$active_status=0;
$action_result = "deactivate_this_version";
if($do_activate_this)
{
 $active_status=1;
 $action_result = "activate_this_version";
}

$login_name = $_SESSION['user'];
$version = isset($_POST['version']) ? intval($_POST['version']) : 0; 

$updatedKeywords = null;
if (isset($_POST['keywords']))
{
	$updatedKeywords = strings_stripSlashes(implode(",",$_POST['keywords']).",");
}

$init_opt_transfer = ($create_tc || $edit_tc || $do_create) ? 1 : 0;

$tcase_mgr = new testcase($db);
$tproject_mgr = new testproject($db);
$tree_mgr = new tree($db);
$tsuite_mgr = new testsuite($db);

// 20061231 - franciscom
if($container_id > 0 )
{
  $pnode_info = $tree_mgr->get_node_hierachy_info($container_id);    
  $node_descr = array_flip($tree_mgr->get_available_node_types());
  $parent_info['name']=$pnode_info['name'];
  $parent_info['description']=lang_get($node_descr[$pnode_info['node_type_id']]);
  $smarty->assign('parent_info',$parent_info);
}

$name_ok = 1;

if($init_opt_transfer)
{
    $opt_cfg = opt_transf_empty_cfg();
    $opt_cfg->js_ot_name = 'ot';
    $opt_cfg->global_lbl = '';
    $opt_cfg->from->lbl = lang_get('available_kword');
    $opt_cfg->from->map = $tproject_mgr->get_keywords_map($testproject_id);
    $opt_cfg->to->lbl=lang_get('assigned_kword');
}
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
    $opt_cfg->to->map = $tcase_mgr->get_keywords_map($tcase_id," ORDER BY keyword ASC ");
    keywords_opt_transf_cfg($opt_cfg, $assigned_keywords_list); 
    
  	$tc_data = $tcase_mgr->get_by_id($tcase_id,$tcversion_id);

  	foreach ($a_ofck as $key)
   	{
  	  	// Warning:
  	  	// the data assignment will work while the keys in $the_data are identical
  	  	// to the keys used on $oFCK.
  	  	$of = &$oFCK[$key];
  	  	$of->Value = $tc_data[0][$key];
  	  	$smarty->assign($key, $of->CreateHTML());
  	}

    // ----------------------------------------------------------------------
    // 20070104 - franciscom
    $cf_smarty='';
    if( $gui_cfg->enable_custom_fields ) 
    {
      $cf_smarty = $tcase_mgr->html_table_of_custom_field_inputs($tcase_id);
    } // if( $gui_cfg
    $smarty->assign('cf',$cf_smarty);	
    // ----------------------------------------------------------------------
  
  	$smarty->assign('tc', $tc_data[0]);
  	$smarty->assign('opt_cfg', $opt_cfg);

  	$smarty->display($g_tpl['tcEdit']);
} 
else if($do_update)
{
	$refresh_tree='no';
	if($name_ok)
	{
		$msg = 'ok';
    $status_ok=0;
    
		// to get the name before the user operation
		$tc_old = $tcase_mgr->get_by_id($tcase_id,$tcversion_id);
						
		if ($tcase_mgr->update($tcase_id,$tcversion_id,$name,$summary,$steps,$expected_results,
		                        $userID,$assigned_keywords_list) )
		{
			$status_ok=1;
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
	  
	  // 20070104 - franciscom
	  if($status_ok && $gui_cfg->enable_custom_fields )
	  {
	     $ENABLED=1;
       $NO_FILTER_SHOW_ON_EXEC=null;
       $cf_map=$tcase_mgr->cfield_mgr->get_linked_cfields_at_design($testproject_id,$ENABLED,$NO_FILTER_SHOW_ON_EXEC,
                                                                    'testcase') ;
 	     $tcase_mgr->cfield_mgr->design_values_to_db($_REQUEST,$tcase_id);
	  }
	  
	  
	}	
 	$action_result='updated';
	$tcase_mgr->show($smarty,$tcase_id, $userID, $tcversion_id, $action_result,$msg,$refresh_tree);
}
else if($create_tc)
{
	$show_newTC_form = 1;
	
	$opt_cfg->to->map=array();
	keywords_opt_transf_cfg($opt_cfg, $assigned_keywords_list); 
	$smarty->assign('opt_cfg', $opt_cfg);
}
else if($do_create)
{
	$show_newTC_form = 1;
	
	if ($name_ok)
	{
    define('AUTOMATIC_ID',0);

		$msg = lang_get('error_tc_add');
		$tcase=$tcase_mgr->create($container_id,$name,$summary,$steps,
		                          $expected_results,$userID,$assigned_keywords_list,
		                          $order_cfg->default_testcase_order,AUTOMATIC_ID,
		                          config_get('check_names_for_duplicates'),'block');
		$msg = $tcase['msg'];                       
	}

	keywords_opt_transf_cfg($opt_cfg, $assigned_keywords_list); 
 	$smarty->assign('opt_cfg', $opt_cfg);
 	$smarty->assign('sqlResult', $msg);
	$smarty->assign('testcase_name', $name);
	$smarty->assign('item', 'Test case');
}
else if($delete_tc)
{
  $exec_status = 'ALL';
	$linked_tcversions = $tcase_mgr->get_linked_versions($tcase_id,$exec_status);
  
	$msg = '';
	$my_ret = $tcase_mgr->check_link_and_exec_status($tcase_id);
	$exec_status_quo = $tcase_mgr->get_exec_status($tcase_id);
	
	switch($my_ret)
	{
		case "linked_and_executed":
		$msg = lang_get('warning') . TITLE_SEP . lang_get('delete_linked_and_exec');
		break;

		case "linked_but_not_executed":
		$msg = lang_get('warning') . TITLE_SEP . lang_get('delete_linked');
		break;
	}

	$tcinfo = $tcase_mgr->get_by_id($tcase_id);
	
	$smarty->assign('exec_status_quo',$exec_status_quo);
	//$smarty->assign('title', lang_get('title_del_tc') . $tcinfo[0]['name']);
	$smarty->assign('title', lang_get('title_del_tc'));
	$smarty->assign('testcase_name', $tcinfo[0]['name']);
	$smarty->assign('testcase_id', $tcase_id);
	$smarty->assign('tcversion_id', TC_ALL_VERSIONS);
	$smarty->assign('delete_message', $msg);

	$smarty->display('tcDelete.tpl');
}
else if($delete_tc_version)
{
	$status_quo_map = $tcase_mgr->get_versions_status_quo($tcase_id);
	$exec_status_quo = $tcase_mgr->get_exec_status($tcase_id);
	
	
	$sq = null;
	if(!is_null($exec_status_quo))
	{
		if(isset($exec_status_quo[$tcversion_id]))
			$sq=array($tcversion_id => $exec_status_quo[$tcversion_id]);
	}
	
	if(intval($status_quo_map[$tcversion_id]['executed']))
	{
			$msg = lang_get('warning') . TITLE_SEP . lang_get('delete_linked_and_exec');
	}
	else if(intval($status_quo_map[$tcversion_id]['linked']))
	{
      $msg = lang_get('warning') . TITLE_SEP . lang_get('delete_linked');	
	}
	else
	{
		$msg = '';
	}
  
	$tcinfo = $tcase_mgr->get_by_id($tcase_id,$tcversion_id);
	//$smarty->assign('title', lang_get('title_del_tc') . 
	//                         $tcinfo[0]['name'] . TITLE_SEP . 
	//                         lang_get('version') . " " . $tcinfo[0]['version']);
	
	$smarty->assign('title', lang_get('title_del_tc') . TITLE_SEP_TYPE3 . 
	                         lang_get('version') . " " . $tcinfo[0]['version']);
	
	$smarty->assign('testcase_name', $tcinfo[0]['name']);
	$smarty->assign('testcase_id', $tcase_id);
	$smarty->assign('tcversion_id', $tcversion_id);
	$smarty->assign('delete_message', $msg);
	$smarty->assign('exec_status_quo',$sq);
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
	$tproject_id = $tcase_mgr->get_testproject($tcase_id);
	$the_tc_node = $tree_mgr->get_node_hierachy_info($tcase_id);
	$tc_parent_id = $the_tc_node['parent_id'];
	$the_tree = $tree_mgr->get_subtree($tproject_id, array("testplan"=>"exclude me",
	                                             "testcase"=>"exclude me"));
	$the_xx = $tproject_mgr->gen_combo_test_suites($tproject_id);
	$the_xx[$the_tc_node['parent_id']] .= ' (' . lang_get('current') . ')'; 
	$tc_info = $tcase_mgr->get_by_id($tcase_id);

	$smarty->assign('old_container', $the_tc_node['parent_id']); // original container
	$smarty->assign('array_container', $the_xx);
	$smarty->assign('testcase_id', $tcase_id);
	$smarty->assign('name', $tc_info[0]['name']);
	$smarty->display('tcMove.tpl');
// move test case to another category
}
else if($do_move)
{
	$result = $tree_mgr->change_parent($tcase_id,$new_container_id);
	$smarty->assign('refreshTree',1);
	$tsuite_mgr->show($smarty,$old_container_id);
}
else if($do_copy)
{
	$msg = '';
	$action_result = 'copied';
	$result = $tcase_mgr->copy_to($tcase_id,$new_container_id,$userID,TC_COPY_KEYWORDS,
	                              config_get('check_names_for_duplicates'),'block');
	$msg = $result['msg'];
	$smarty->assign('refreshTree',1);
	$tcase_mgr->show($smarty,$tcase_id, $userID,$tcversion_id,$action_result,$msg);
}
else if($do_create_new_version)
{
	$show_newTC_form = 0;
	$action_result = "create_new_version";
	$msg = lang_get('error_tc_add');
	$op = $tcase_mgr->create_new_version($tcase_id,$userID);
	if ($op['msg'] == "ok")
		$msg = 'ok';
	
	define('DONT_REFRESH','no');
	$tcase_mgr->show($smarty,$tcase_id, $userID, TC_ALL_VERSIONS, 
	                            $action_result,$msg,DONT_REFRESH);
}
else if($do_activate_this || $do_deactivate_this)
{
	$tcase_mgr->update_active_status($tcase_id, $tcversion_id, $active_status);

	define('DONT_REFRESH','no');
	define('DONT_SAY_A_WORD',null);
	
  $msg = DONT_SAY_A_WORD; 
	$tcase_mgr->show($smarty,$tcase_id, $userID, TC_ALL_VERSIONS,
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
	
	$smarty->display($g_tpl['tcNew']);
}
?>