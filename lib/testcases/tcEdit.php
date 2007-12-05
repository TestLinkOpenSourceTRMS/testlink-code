<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: tcEdit.php,v $
 *
 * @version $Revision: 1.65 $
 * @modified $Date: 2007/12/05 21:25:15 $  by $Author: schlundus $
 * This page manages all the editing of test cases.
 *
 * 20071201 - franciscom - new web editor code
 * 20071106 - BUGID 1165 
 * 20070826 - franciscom - is automatic tree refresh is disable,
 *                         do not refresh if test case changes during update
 *
 * 20070701 - franciscom - feedback improvement on new version operation
 * 20070302 - franciscom - BUGID
 * 20070220 - franciscom - automatic tree refresh management
 * 20070218 - franciscom - added $g_spec_cfg->automatic_tree_refresh to the
 *                         refresh tree logic
 *
 *
**/
require_once("../../config.inc.php");
require_once("common.php");
require_once("web_editor.php");
require_once("opt_transfer.php");
testlinkInitPage($db);

$template_dir='testcases/';

$sqlResult="";
$gui_cfg = config_get('gui');
$order_cfg = config_get('tree_node_ordering');
$spec_cfg=config_get('spec_cfg');

$tcase_template_cfg=config_get('testcase_template');


// --------------------------------------------------------------------
// create web editor objects
//
// When using tinymce or none as web editor, we need to set rows and cols
// to appropriate values, to avoid an ugly ui.
// null => use default values defined on editor class file
//
// Rows and Cols values are useless for FCKeditor
//
//
$a_oWebEditor_cfg = array('summary' => array('rows'=> null,'cols' => null),
                          'steps' => array('rows'=> null,'cols' => 38) ,
                          'expected_results' => array('rows'=> null,'cols' => 38));
$oWebEditor = array();
foreach ($a_oWebEditor_cfg as $key => $value)
{
	$oWebEditor[$key] = web_editor($key,$_SESSION['basehref']);
}
// --------------------------------------------------------------------

$show_newTC_form = 0;
$args=init_args($spec_cfg);

$tcase_mgr = new testcase($db);
$tproject_mgr = new testproject($db);
$tree_mgr = new tree($db);
$tsuite_mgr = new testsuite($db);

$smarty = new TLSmarty();
$smarty->assign('has_been_executed',$args->has_been_executed);
$smarty->assign('execution_types',$tcase_mgr->get_execution_types());


$opt_cfg->js_ot_name = 'ot';
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

$do_activate_this = isset($_POST['activate_this_tcversion']) ? 1 : 0;
$do_deactivate_this = isset($_POST['deactivate_this_tcversion']) ? 1 : 0;

$active_status=0;
$action_result = "deactivate_this_version";
if($do_activate_this)
{
	$active_status = 1;
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


$user_feedback='';

if($args->container_id > 0 )
{
  $pnode_info = $tree_mgr->get_node_hierachy_info($args->container_id);    
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
    $opt_cfg->from->map = $tproject_mgr->get_keywords_map($args->testproject_id);
    $opt_cfg->to->lbl=lang_get('assigned_kword');
}
if($do_create || $do_update)
{
	// BUGID 0000086
	$result = lang_get('warning_empty_tc_title');	
	if($name_ok && !check_string($args->name,$g_ereg_forbidden) )
	{
		$msg = lang_get('string_contains_bad_chars');
		$user_feedback=$msg;
		$name_ok = 0;
	}
	if($name_ok && strlen($args->name) == 0)
	{
		$msg = lang_get('warning_empty_tc_title');
		$user_feedback=$msg;
		$name_ok = 0;
	}
}

//If the user has chosen to edit a testcase then show this code
if($edit_tc)
{
    $opt_cfg->to->map = $tcase_mgr->get_keywords_map($args->tcase_id," ORDER BY keyword ASC ");
    keywords_opt_transf_cfg($opt_cfg, $assigned_keywords_list); 
    
  	$tc_data = $tcase_mgr->get_by_id($args->tcase_id,$args->tcversion_id);

  	foreach ($a_oWebEditor_cfg as $key => $value)
   	{
  	  	// Warning:
  	  	// the data assignment will work while the keys in $the_data are identical
  	  	// to the keys used on $oWebEditor.
  	  	$of = &$oWebEditor[$key];
  	  	$of->Value = $tc_data[0][$key];
  	  	
  	  	$rows=$a_oWebEditor_cfg[$key]['rows'];
        $cols=$a_oWebEditor_cfg[$key]['cols'];
  	  	$smarty->assign($key, $of->CreateHTML($rows,$cols));
  	}

    $cf_smarty = '';
    if($gui_cfg->enable_custom_fields) 
    {
		  $cf_smarty = $tcase_mgr->html_table_of_custom_field_inputs($args->tcase_id);
    }
    $smarty->assign('cf',$cf_smarty);	
   	$smarty->assign('tc', $tc_data[0]);
  	$smarty->assign('opt_cfg', $opt_cfg);
  	$smarty->display($template_dir . $g_tpl['tcEdit']);
} 
else if($do_update)
{
	$refresh_tree=$args->do_refresh?"yes":"no";
	if($name_ok)
	{
		$msg = 'ok';
    $status_ok=0;
    
		// to get the name before the user operation
		$tc_old = $tcase_mgr->get_by_id($args->tcase_id,$args->tcversion_id);
						
		if ($tcase_mgr->update($args->tcase_id,$args->tcversion_id,$args->name,$args->summary,
		                       $args->steps,$args->expected_results,
		                       $args->user_id,$assigned_keywords_list,
		                       TC_DEFAULT_ORDER,$args->exec_type) )
		{
			$status_ok=1;
		}
		else
		{
		   	$sqlResult =  $db->error_msg();
		}
	  
		if($status_ok && $gui_cfg->enable_custom_fields )
		{
			$ENABLED=1;
			$NO_FILTER_SHOW_ON_EXEC=null;
			$cf_map=$tcase_mgr->cfield_mgr->get_linked_cfields_at_design($args->testproject_id,
			                                                             $ENABLED,$NO_FILTER_SHOW_ON_EXEC,'testcase') ;
			$tcase_mgr->cfield_mgr->design_values_to_db($_REQUEST,$args->tcase_id);
		}
	}	
 	$action_result = 'updated';
	$tcase_mgr->show($smarty,$template_dir,$args->tcase_id, $args->user_id, $args->tcversion_id, 
	                 $action_result,$msg,$refresh_tree);
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
		$user_feedback = lang_get('error_tc_add');
    $sqlResult='ko';
		$tcase=$tcase_mgr->create($args->container_id,$args->name,$args->summary,$args->steps,
		                          $args->expected_results,$args->user_id,$assigned_keywords_list,
		                          $order_cfg->default_testcase_order,AUTOMATIC_ID,
		                          config_get('check_names_for_duplicates'),'block',$args->exec_type);
		                          
		if($tcase['status_ok'] && $gui_cfg->enable_custom_fields )
		{
			$cf_map=$tcase_mgr->cfield_mgr->get_linked_cfields_at_design($args->testproject_id,ENABLED,
			                                                             NO_FILTER_SHOW_ON_EXEC,'testcase') ;
			$tcase_mgr->cfield_mgr->design_values_to_db($_REQUEST,$tcase['id']);

      $user_feedback=sprintf(lang_get('tc_created'),$args->name);
      $sqlResult='ok';        
		}
	}

	keywords_opt_transf_cfg($opt_cfg, $assigned_keywords_list); 
 	$smarty->assign('opt_cfg', $opt_cfg);
 	$smarty->assign('sqlResult', $sqlResult);
	$smarty->assign('user_feedback', $user_feedback);
	$smarty->assign('testcase_name', $args->name);
	$smarty->assign('item', 'testcase');
}
else if($delete_tc)
{
 	$exec_status = 'ALL';
	$linked_tcversions = $tcase_mgr->get_linked_versions($args->tcase_id,$exec_status);
  
	$msg = '';
	$my_ret = $tcase_mgr->check_link_and_exec_status($args->tcase_id);
	$exec_status_quo = $tcase_mgr->get_exec_status($args->tcase_id);
	
	switch($my_ret)
	{
		case "linked_and_executed":
		$msg = lang_get('warning') . TITLE_SEP . lang_get('delete_linked_and_exec');
		break;

		case "linked_but_not_executed":
		$msg = lang_get('warning') . TITLE_SEP . lang_get('delete_linked');
		break;
	}

	$tcinfo = $tcase_mgr->get_by_id($args->tcase_id);
	
	$smarty->assign('exec_status_quo',$exec_status_quo);
	$smarty->assign('title', lang_get('title_del_tc'));
	$smarty->assign('testcase_name', $tcinfo[0]['name']);
	$smarty->assign('testcase_id', $args->tcase_id);
	$smarty->assign('tcversion_id', TC_ALL_VERSIONS);
	$smarty->assign('delete_message', $msg);

	$smarty->display($template_dir . 'tcDelete.tpl');
}
else if($delete_tc_version)
{
	$status_quo_map = $tcase_mgr->get_versions_status_quo($args->tcase_id);
	$exec_status_quo = $tcase_mgr->get_exec_status($args->tcase_id);
	
	
	$sq = null;
	if(!is_null($exec_status_quo))
	{
		if(isset($exec_status_quo[$args->tcversion_id]))
			$sq=array($args->tcversion_id => $exec_status_quo[$args->tcversion_id]);
	}
	
	if(intval($status_quo_map[$args->tcversion_id]['executed']))
	{
		$msg = lang_get('warning') . TITLE_SEP . lang_get('delete_linked_and_exec');
	}
	else if(intval($status_quo_map[$args->tcversion_id]['linked']))
	{
      	$msg = lang_get('warning') . TITLE_SEP . lang_get('delete_linked');	
	}
	else
	{
		$msg = '';
	}
  
	$tcinfo = $tcase_mgr->get_by_id($args->tcase_id,$args->tcversion_id);
	$smarty->assign('title', lang_get('title_del_tc') . TITLE_SEP_TYPE3 . 
	                         lang_get('version') . " " . $tcinfo[0]['version']);
	
	$smarty->assign('testcase_name', $tcinfo[0]['name']);
	$smarty->assign('testcase_id', $args->tcase_id);
	$smarty->assign('tcversion_id', $args->tcversion_id);
	$smarty->assign('delete_message', $msg);
	$smarty->assign('exec_status_quo',$sq);
	$smarty->display($template_dir . 'tcDelete.tpl');
}
else if($do_delete)
{
  $user_feedback='';
	$msg='';
	$action_result='deleted';
	$verbose_result='ok';
	$tcinfo=$tcase_mgr->get_by_id($args->tcase_id,$args->tcversion_id);
	
	if (!$tcase_mgr->delete($args->tcase_id,$args->tcversion_id))
	{
		$action_result='';
		$verbose_result=$db->error_msg();
	}
	else
	{
	  $user_feedback=sprintf(lang_get('tc_deleted'),$tcinfo[0]['name']);
	}
	
	$the_title = lang_get('title_del_tc') . $tcinfo[0]['name'];
  $refresh_tree=$spec_cfg->automatic_tree_refresh ? "yes" : "no";
	
	if( $args->tcversion_id != TC_ALL_VERSIONS )
	{
		$the_title .= " " . lang_get('version') . " " . $tcinfo[0]['version'];
		$refresh_tree="no";
	  $user_feedback=sprintf(lang_get('tc_version_deleted'),$tcinfo[0]['name'],$tcinfo[0]['version']);
	}
  
	$smarty->assign('title', $the_title);
	$smarty->assign('user_feedback', $user_feedback);
	$smarty->assign('sqlResult', $verbose_result);
	$smarty->assign('testcase_id', $args->tcase_id);
	$smarty->assign('delete_message', $msg);
	$smarty->assign('action',$action_result);
	$smarty->assign('refresh_tree',$refresh_tree);
	
	$smarty->display($template_dir . 'tcDelete.tpl');
}
else if($move_copy_tc)
{
	// need to get the testproject for the test case
	$tproject_id = $tcase_mgr->get_testproject($args->tcase_id);
	$the_tc_node = $tree_mgr->get_node_hierachy_info($args->tcase_id);
	$tc_parent_id = $the_tc_node['parent_id'];
	
	// 20071111 - franciscom
	$the_tree = $tree_mgr->get_subtree($tproject_id, array("testplan"=>"exclude me",
	                                                       "requirement_spec"=>"exclude me",
	                                                       "requirement"=>"exclude me",
	                                                       "testcase"=>"exclude me"));
	$the_xx = $tproject_mgr->gen_combo_test_suites($tproject_id);

	$the_xx[$the_tc_node['parent_id']] .= ' (' . lang_get('current') . ')'; 
	$tc_info = $tcase_mgr->get_by_id($args->tcase_id);

  $container_qty=count($the_xx);
  $move_enabled=1;
  if( $container_qty == 1 )
  {
     // move operation is nonsense
     $move_enabled=0;  
  }

	$smarty->assign('old_container', $the_tc_node['parent_id']); // original container
	$smarty->assign('array_container', $the_xx);
	$smarty->assign('testcase_id', $args->tcase_id);
  $smarty->assign('move_enabled',$move_enabled);
	$smarty->assign('name', $tc_info[0]['name']);
	$smarty->display($template_dir . 'tcMove.tpl');
// move test case to another category
}
else if($do_move)
{
	$result = $tree_mgr->change_parent($args->tcase_id,$args->new_container_id);
	$smarty->assign('refreshTree',$do_refresh);
	$tsuite_mgr->show($smarty,$template_dir,$args->old_container_id);
}
else if($do_copy)
{
  $user_feedback=''; 
	$msg = '';
	$action_result = 'copied';
	$result = $tcase_mgr->copy_to($args->tcase_id,$args->new_container_id,$args->user_id,TC_COPY_KEYWORDS,
	                              config_get('check_names_for_duplicates'),'block');
	$msg = $result['msg'];
 
  if($result['msg'] == "ok" )
  {
    $ts_sep=config_get('testsuite_sep');
    $tc_info=$tcase_mgr->get_by_id($args->tcase_id);
    $container_info=$tree_mgr->get_node_hierachy_info($args->new_container_id);
    $container_path=$tree_mgr->get_path($args->new_container_id);
    $path='';
    foreach($container_path as $key => $value)
    {
      $path .= $value['name'] . $ts_sep;
    }
    $path=trim($path,$ts_sep);
    $user_feedback=sprintf(lang_get('tc_copied'),$tc_info[0]['name'],$path);
  }	
	$smarty->assign('refreshTree',$do_refresh);
	
	$do_refresh_yes_no=$do_refresh?"yes":"no";
	$tcase_mgr->show($smarty,$template_dir,$args->tcase_id, $args->user_id,$args->tcversion_id,
	                 $action_result,$msg,$do_refresh_yes_no,$user_feedback);
}
else if($do_create_new_version)
{
  $user_feedback=''; 
	$show_newTC_form = 0;
	$action_result = "do_update";
	$msg = lang_get('error_tc_add');
	$op = $tcase_mgr->create_new_version($args->tcase_id,$args->user_id);
	if ($op['msg'] == "ok")
	{
	  $user_feedback=sprintf(lang_get('tc_new_version'),$op['version']);
		$msg = 'ok';
	}
	
	$tcase_mgr->show($smarty,$template_dir,$args->tcase_id, $args->user_id, TC_ALL_VERSIONS, 
	                 $action_result,$msg,DONT_REFRESH,$user_feedback);
}
else if($do_activate_this || $do_deactivate_this)
{
 	$msg = null; 
	$tcase_mgr->update_active_status($args->tcase_id, $args->tcversion_id, $active_status);
	$tcase_mgr->show($smarty,$template_dir,$args->tcase_id, $args->user_id, TC_ALL_VERSIONS,
	                 $action_result,$msg,DONT_REFRESH);
}
else
{
	tlog("A correct POST argument is not found.");
}
// --------------------------------------------------------------------------
if ($show_newTC_form)
{
	$smarty->assign('containerID', $args->container_id);

  // ------------------------------------------------------------------------
  // 20071106 - BUGID 1165
  foreach ($a_oWebEditor_cfg as $key => $value)
  {
    switch($tcase_template_cfg->$key->type)
    {
      case 'string':
    	$the_value = $tcase_template_cfg->$key->value;
      break;
      
      case 'string_id':
    	$the_value = lang_get($tcase_template_cfg->$key->value);
      break;
      
      
      case 'file':
    	$the_value = read_file($tcase_template_cfg->$key->value);
      break;
      
      default:
      $the_value = '';
      break;
    }
    $of = &$oWebEditor[$key];
    $rows=$a_oWebEditor_cfg[$key]['rows'];
    $cols=$a_oWebEditor_cfg[$key]['cols'];
    
    
    $of->Value = $the_value;
    $smarty->assign($key, $of->CreateHTML($rows,$cols));
  } // foreach ($a_oWebEditor_cfg as $key)
  // ------------------------------------------------------------------------


  $cf_smarty = '';
  if($gui_cfg->enable_custom_fields) 
  {
	  $cf_smarty = $tcase_mgr->html_table_of_custom_field_inputs($args->tcase_id,$args->container_id);
  }
  $smarty->assign('cf',$cf_smarty);	
	// ------------------------------------------------------------------------------------------------------
	
	$smarty->display($template_dir . $g_tpl['tcNew']);
}
?>


<?php
/*
  function: read_file

  args: file_name 
  
  returns: if file exist and can be read -> file contents
           else error message

*/
function read_file($file_name)
{
	$fContents = null;
	@$fd = fopen($file_name,"rb");
	if ($fd)
	{
		$fContents = fread($fd,filesize($file_name));
		fclose($fd);
	}
	else
	{
	  $fContents= lang_get('problems_trying_to_access_template') . " {$file_name} ";  
	}
	return $fContents;
}



/*
  function: 

  args:
  
  returns: 

*/
function init_args($spec_cfg)
{
  $args->container_id = isset($_REQUEST['containerID']) ? intval($_REQUEST['containerID']) : 0;
  $args->tcase_id = isset($_REQUEST['testcase_id']) ? intval($_REQUEST['testcase_id']) : 0;
  $args->tcversion_id = isset($_REQUEST['tcversion_id']) ? intval($_REQUEST['tcversion_id']) : 0;

  $args->name 		= isset($_POST['testcase_name']) ? strings_stripSlashes($_POST['testcase_name']) : null;
  $args->summary 	= isset($_POST['summary']) ? strings_stripSlashes($_POST['summary']) : null;
  $args->steps 		= isset($_POST['steps']) ? strings_stripSlashes($_POST['steps']) : null;
  $args->expected_results 	= isset($_POST['expected_results']) ? strings_stripSlashes($_POST['expected_results']) : null;
  $args->new_container_id = isset($_POST['new_container']) ? intval($_POST['new_container']) : 0;
  $args->old_container_id = isset($_POST['old_container']) ? intval($_POST['old_container']) : 0;

  $args->has_been_executed=isset($_REQUEST['has_been_executed']) ? intval($_REQUEST['has_been_executed']) : 0;
                                                                                                              
  $args->exec_type=isset($_REQUEST['exec_type']) ? $_REQUEST['exec_type'] : TESTCASE_EXECUTION_TYPE_MANUAL;


  // from session
  $args->testproject_id = $_SESSION['testprojectID'];
  $args->user_id = $_SESSION['userID'];
  $args->do_refresh=$spec_cfg->automatic_tree_refresh;
  if( isset($_SESSION['tcspec_refresh_on_action']) )
  {
    $args->do_refresh=$_SESSION['tcspec_refresh_on_action'] == "yes" ? 1 : 0 ;
  }


  return $args;
}
?>