<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 *
 * Filename $RCSfile: tcEdit.php,v $
 *
 * @version $Revision: 1.124 $
 * @modified $Date: 2010/01/02 18:19:34 $  by $Author: franciscom $
 * This page manages all the editing of test cases.
 *
 * rev: 
 *	20090831 - franciscom - preconditions
 *	20090401 - franciscom - BUGID 2364 - edit while executing
 *  20090401 - franciscom - BUGID 2316
 *  20090325 - franciscom - BUGID - problems with add to testplan
 *  20090302 - franciscom - BUGID 2163 - Create test case with same title, after submit, all data lost 
 *  20080827 - franciscom - BUGID 1692 
 *  20080706 - franciscom - force refresh tree when operation can put tree on
 *                          situation that lead to errors if user click on deleted element.
 *  20080203 - franciscom - changes on $tcase_mgr->show() interface
 *  20080105 - franciscom - REQID 1248 - added logic to manage copy/move on top or bottom
 *  
 *  20071201 - franciscom - new web editor code
 *  20071106 - BUGID 1165
 *  20070826 - franciscom - is automatic tree refresh is disable,
 *                          do not refresh if test case changes during update
 *  
 *  20070701 - franciscom - feedback improvement on new version operation
 *  20070302 - franciscom - BUGID
 *  20070220 - franciscom - automatic tree refresh management
 *  20070218 - franciscom - added $g_spec_cfg->automatic_tree_refresh to the refresh tree logic
 *
 *
 * -------------------------------------------------------------------------------- */

require_once("../../config.inc.php");
require_once("common.php");
require_once("opt_transfer.php");
require_once("web_editor.php");

$cfg = getCfg();
require_once(require_web_editor($cfg->webEditorCfg['type']));

testlinkInitPage($db);
$optionTransferName = 'ot';
$args = init_args($cfg->spec,$optionTransferName);

$tcase_mgr = new testcase($db);
$tproject_mgr = new testproject($db);
$tree_mgr = new tree($db);
$tsuite_mgr = new testsuite($db);

$templateCfg = templateConfiguration('tcEdit');

$commandMgr = new testcaseCommands($db);
$commandMgr->setTemplateCfg(templateConfiguration());

$oWebEditor = createWebEditors($args->basehref,$cfg->webEditorCfg);

$sqlResult = "";
$init_inputs=true; // BUGID 2163 - Create test case with same title, after submit, all data lost 

$show_newTC_form = 0;


$opt_cfg = initializeOptionTransferCfg($optionTransferName,$args,$tproject_mgr);
$gui = initializeGui($db,$args,$cfg,$tree_mgr);

$smarty = new TLSmarty();

$active_status = 0;
$action_result = "deactivate_this_version";
if($args->do_activate_this)
{
	$active_status = 1;
	$action_result = "activate_this_version";
}

$login_name = $_SESSION['currentUser']->login;
$version = isset($_REQUEST['version']) ? intval($_REQUEST['version']) : 0;


$name_ok = 1;

if($args->do_create)
{
	// BUGID 0000086
	$result = lang_get('warning_empty_tc_title');
	if($name_ok && !check_string($args->name,$g_ereg_forbidden))
	{
		$msg = lang_get('string_contains_bad_chars');
		$gui->user_feedback = $msg;
		$name_ok = 0;
	}
	if($name_ok && $args->name == "")
	{
		$msg = lang_get('warning_empty_tc_title');
		$gui->user_feedback = $msg;
		$name_ok = 0;
	}
}


switch($args->doAction)
{
    case "doUpdate":
        $op=$commandMgr->doUpdate($args,$_REQUEST);
    break;
	  
    case "doAdd2testplan":
        $op=$commandMgr->doAdd2testplan($args,$_REQUEST);
    break;
}

//If the user has chosen to edit a testcase then show this code
if($args->edit_tc)
{
    $opt_cfg->to->map = $tcase_mgr->get_keywords_map($args->tcase_id," ORDER BY keyword ASC ");
    keywords_opt_transf_cfg($opt_cfg, $args->assigned_keywords_list);

  	$tc_data = $tcase_mgr->get_by_id($args->tcase_id,$args->tcversion_id);
  	foreach ($oWebEditor->cfg as $key => $value)
   	{
  	  	// Warning:
  	  	// the data assignment will work while the keys in $the_data are identical
  	  	// to the keys used on $oWebEditor.
  	  	$of = &$oWebEditor->editor[$key];
  	  	$of->Value = $tc_data[0][$key];
  	  	$rows = $oWebEditor->cfg[$key]['rows'];
        $cols = $oWebEditor->cfg[$key]['cols'];
  	  	$smarty->assign($key, $of->CreateHTML($rows,$cols));
  	}

    $filters=$tcase_mgr->buildCFLocationMap();
	foreach($filters as $locationKey => $locationFilter)
	{ 
		$cf_smarty[$locationKey] = 
			$tcase_mgr->html_table_of_custom_field_inputs($args->tcase_id,null,'design','',
			                                              null,null,null,$locationFilter);
	}	
	// -----------------------------------------------------------------------------
    // $smarty->assign('cf',$cf_smarty);
   	// $smarty->assign('tc', $tc_data[0]);
  	// $smarty->assign('opt_cfg', $opt_cfg);
    $templateCfg = templateConfiguration('tcEdit');
    
    
    $gui->cf = $cf_smarty;
    $gui->tc = $tc_data[0];
    $gui->opt_cfg = $opt_cfg;
    $smarty->assign('gui',$gui);
  	$smarty->display($templateCfg->template_dir . $templateCfg->default_template);
}
else if($args->create_tc)
{
	$show_newTC_form = 1;
	$opt_cfg->to->map = array();
	keywords_opt_transf_cfg($opt_cfg, $args->assigned_keywords_list);

	// $smarty->assign('opt_cfg', $opt_cfg);
    $gui->opt_cfg = $opt_cfg;
    $smarty->assign('gui',$gui);

}
else if($args->do_create)
{
	$show_newTC_form = 1;
	if ($name_ok)
	{
		$gui->user_feedback = lang_get('error_tc_add');
        $gui->sqlResult = 'ko';
        
        $new_order = $cfg->treemenu_default_testcase_order;

    	// compute order
    	$nt2exclude=array('testplan' => 'exclude_me','requirement_spec'=> 'exclude_me','requirement'=> 'exclude_me');
    	$siblings = $tcase_mgr->tree_manager->get_children($args->container_id,$nt2exclude);
    	if( !is_null($siblings) )
    	{
    		$dummy = end($siblings);
    		$new_order = $dummy['node_order']+1;
    	}
    	// 20091217 - francisco.mancardi@gruppotesi.com
    	//
    	$options = array('check_duplicate_name' => config_get('check_names_for_duplicates'),
    	                 'action_on_duplicate_name' => 'block');
       	$tcase = $tcase_mgr->create($args->container_id,$args->name,$args->summary,$args->preconditions,
       	                            $args->steps,$args->expected_results,$args->user_id,
       	                            $args->assigned_keywords_list,$new_order,testcase::AUTOMATIC_ID,
       	                            $args->exec_type,$args->importance,$options);

		if($tcase['status_ok'])
		{
			$cf_map = $tcase_mgr->cfield_mgr->get_linked_cfields_at_design($args->testproject_id,ENABLED,
			                                                             NO_FILTER_SHOW_ON_EXEC,'testcase') ;
	    	$tcase_mgr->cfield_mgr->design_values_to_db($_REQUEST,$tcase['id']);
        	$gui->user_feedback = sprintf(lang_get('tc_created'),$args->name);
        	$gui->sqlResult = 'ok';
		}
		elseif(isset($tcase['msg']))
		{
    	  // BUGID 0001267 by cmurray
     		$user_feedback .= '' . $tcase['msg'];
     		$init_inputs=false;
		}
	}

	keywords_opt_transf_cfg($opt_cfg, $args->assigned_keywords_list);
 	// $smarty->assign('opt_cfg', $opt_cfg);
 	// $smarty->assign('sqlResult', $sqlResult);
	// $smarty->assign('user_feedback', $user_feedback);
	// $smarty->assign('testcase_name', $args->name);
	// $smarty->assign('item', 'testcase');
	$gui->opt_cfg = $opt_cfg;
	$gui->testcase_name = $args->name;
	$gui->item = 'testcase';
    $smarty->assign('gui',$gui);
}
else if($args->delete_tc)
{
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

	// $smarty->assign('exec_status_quo',$exec_status_quo);
	// $smarty->assign('title', lang_get('title_del_tc'));
	// $smarty->assign('testcase_name', $tcinfo[0]['name']);
	// $smarty->assign('testcase_id', $args->tcase_id);
	// $smarty->assign('tcversion_id', testcase::ALL_VERSIONS);
	// $smarty->assign('delete_message', $msg);
       
	$gui->exec_status_quo = $exec_status_quo;
	$gui->title = lang_get('title_del_tc');
	$gui->testcase_name =  $tcinfo[0]['name'];
	$gui->testcase_id = $args->tcase_id;
	$gui->tcversion_id = testcase::ALL_VERSIONS;
	$gui->delete_message = $msg;
	$gui->refresh_tree = "yes";
    $smarty->assign('gui',$gui);

    $templateCfg = templateConfiguration('tcDelete');
    $smarty->display($templateCfg->template_dir . $templateCfg->default_template);
}
else if($args->delete_tc_version)
{
	$status_quo_map = $tcase_mgr->get_versions_status_quo($args->tcase_id);
	$exec_status_quo = $tcase_mgr->get_exec_status($args->tcase_id);

	$sq = null;
	if(!is_null($exec_status_quo))
	{
		if(isset($exec_status_quo[$args->tcversion_id]))
			$sq = array($args->tcversion_id => $exec_status_quo[$args->tcversion_id]);
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

	// $smarty->assign('title', lang_get('title_del_tc') . TITLE_SEP_TYPE3 .
	//                          lang_get('version') . " " . $tcinfo[0]['version']);
    // 
	// $smarty->assign('testcase_name', $tcinfo[0]['name']);
	// $smarty->assign('testcase_id', $args->tcase_id);
	// $smarty->assign('tcversion_id', $args->tcversion_id);
	// $smarty->assign('delete_message', $msg);
	// $smarty->assign('exec_status_quo',$sq);

	$gui->title = lang_get('title_del_tc') . 
	              TITLE_SEP_TYPE3 . lang_get('version') . " " . $tcinfo[0]['version'];
	$gui->testcase_name = $tcinfo[0]['name'];
	$gui->testcase_id = $args->tcase_id;
	$gui->tcversion_id = $args->tcversion_id;
	$gui->delete_message = $msg;
	$gui->exec_status_quo = $sq;
	$gui->refresh_tree = "no";

    $smarty->assign('gui',$gui);
    $templateCfg = templateConfiguration('tcDelete');
    $smarty->display($templateCfg->template_dir . $templateCfg->default_template);
}
else if($args->do_delete)
{
	
 	$gui->user_feedback = '';
	$gui->delete_message = '';
	$gui->action = 'deleted';
	$gui->sqlResult = 'ok';
	$tcinfo = $tcase_mgr->get_by_id($args->tcase_id,$args->tcversion_id);
	
	if (!$tcase_mgr->delete($args->tcase_id,$args->tcversion_id))
	{
		$gui->action = '';
		$gui->sqlResult = $db->error_msg();
	}
	else
	{
		$gui->user_feedback = sprintf(lang_get('tc_deleted'),$tcinfo[0]['name']);
	}

	$gui->title = lang_get('title_del_tc') . htmlspecialchars($tcinfo[0]['name']);
  
  	// 20080706 - refresh must be forced to avoid a wrong tree situation.
  	// if tree is not refreshed and user click on deleted test case he/she
  	// will get a SQL error
  	// $refresh_tree = $cfg->spec->automatic_tree_refresh ? "yes" : "no";
  	$gui->refresh_tree = "yes";
 
  	// When deleting JUST one version, there is no need to refresh tree
	if($args->tcversion_id != testcase::ALL_VERSIONS)
	{
		  $gui->title .= " " . lang_get('version') . " " . $tcinfo[0]['version'];
		  $gui->refresh_tree = "no";
	  	  $gui->user_feedback = sprintf(lang_get('tc_version_deleted'),$tcinfo[0]['name'],$tcinfo[0]['version']);
	}

	// $smarty->assign('title', $the_title);
	// $smarty->assign('testcase_name', $tcinfo[0]['name']);
	// $smarty->assign('user_feedback', $user_feedback);
	// $smarty->assign('sqlResult', $verbose_result);
	// $smarty->assign('testcase_id', $args->tcase_id);
	// $smarty->assign('delete_message', $msg);
	// $smarty->assign('action',$action_result);

	//$gui->title = $the_title;
	$gui->testcase_name = $tcinfo[0]['name'];
	$gui->sqlResult = $verbose_result;
	$gui->testcase_id = $args->tcase_id;
    $smarty->assign('gui',$gui); 

    $templateCfg = templateConfiguration('tcDelete');
    $smarty->display($templateCfg->template_dir . $templateCfg->default_template);
}
else if($args->move_copy_tc)
{
	// need to get the testproject for the test case
	$tproject_id = $tcase_mgr->get_testproject($args->tcase_id);
	$the_tc_node = $tree_mgr->get_node_hierarchy_info($args->tcase_id);
	$tc_parent_id = $the_tc_node['parent_id'];
	$the_xx = $tproject_mgr->gen_combo_test_suites($tproject_id);

	$the_xx[$the_tc_node['parent_id']] .= ' (' . lang_get('current') . ')';
	$tc_info = $tcase_mgr->get_by_id($args->tcase_id);

	$container_qty = count($the_xx);
	$move_enabled = 1;
	if($container_qty == 1)
	{
		// move operation is nonsense
		$move_enabled = 0;
	}

	$smarty->assign('top_checked','checked=checked');
	$smarty->assign('bottom_checked','');
	$smarty->assign('old_container', $the_tc_node['parent_id']); // original container
	$smarty->assign('array_container', $the_xx);
	$smarty->assign('testcase_id', $args->tcase_id);
	$smarty->assign('move_enabled',$move_enabled);
	$smarty->assign('name', $tc_info[0]['name']);

    $templateCfg = templateConfiguration('tcMove');
    $smarty->display($templateCfg->template_dir . $templateCfg->default_template);
}
else if($args->do_move)
{
	$result = $tree_mgr->change_parent($args->tcase_id,$args->new_container_id);
  	$tree_mgr->change_child_order($args->new_container_id,$args->tcase_id,
                                  $args->target_position,$cfg->exclude_node_types);

    $gui->refreshTree = $args->do_refresh;
	$smarty->assign('gui',$gui);
	$tsuite_mgr->show($smarty,$templateCfg->template_dir,$args->old_container_id);
}
else if($args->do_copy)
{
	$user_feedback='';
	$msg = '';
	$action_result = 'copied';
	$options = array('check_duplicate_name' => config_get('check_names_for_duplicates'),
                     'action_on_duplicate_name' => config_get('action_on_duplicate_name'),
                     'copy_also' => $args->copy);

	  
	  // $result = $tcase_mgr->copy_to($args->tcase_id,$args->new_container_id,$args->user_id,$args->copy,
	  //                               config_get('check_names_for_duplicates'),
	  //                               config_get('action_on_duplicate_name'));
	  // 
	// new dBug($options);  
	$result = $tcase_mgr->copy_to($args->tcase_id,$args->new_container_id,$args->user_id,$options);
	$msg = $result['msg'];

    if($msg == "ok")
    {
		    $tree_mgr->change_child_order($args->new_container_id,$result['id'],
		                                $args->target_position,$cfg->exclude_node_types);
        
		    $ts_sep = config_get('testsuite_sep');
		    $tc_info = $tcase_mgr->get_by_id($args->tcase_id);
		    $container_info = $tree_mgr->get_node_hierarchy_info($args->new_container_id);
		    $container_path = $tree_mgr->get_path($args->new_container_id);
		    $path = '';
		    foreach($container_path as $key => $value)
		    {
		    	$path .= $value['name'] . $ts_sep;
		    }
		    $path = trim($path,$ts_sep);
		    $user_feedback = sprintf(lang_get('tc_copied'),$tc_info[0]['name'],$path);
    }
	  $smarty->assign('refreshTree',$args->do_refresh);

	  $viewer_args['action'] = $action_result;
	  $viewer_args['refresh_tree']=$args->do_refresh?"yes":"no";
	  $viewer_args['msg_result'] = $msg;
	  $viewer_args['user_feedback'] = $user_feedback;
	  $tcase_mgr->show($smarty,$templateCfg->template_dir,$args->tcase_id,
	                   $args->tcversion_id,$viewer_args,null, $args->show_mode);

}
else if($args->do_create_new_version)
{
	$user_feedback = '';
	$show_newTC_form = 0;
	$action_result = "do_update";
	$msg = lang_get('error_tc_add');
	$op = $tcase_mgr->create_new_version($args->tcase_id,$args->user_id);
	if ($op['msg'] == "ok")
	{
		$user_feedback = sprintf(lang_get('tc_new_version'),$op['version']);
		$msg = 'ok';
	}

	$viewer_args['action'] = $action_result;
	$viewer_args['refresh_tree'] = DONT_REFRESH;
	$viewer_args['msg_result'] = $msg;
	$viewer_args['user_feedback'] = $user_feedback;
	
	// used to implement go back ??
	$smarty->assign('loadOnCancelURL',
	                $_SESSION['basehref'].'/lib/testcases/archiveData.php?edit=testcase&id='.$args->tcase_id);
	
	// 20090419 - BUGID - 
	$testcase_version = !is_null($args->show_mode) ? $args->tcversion_id : testcase::ALL_VERSIONS;
	$tcase_mgr->show($smarty,$templateCfg->template_dir,$args->tcase_id,$testcase_version, 
	                 $viewer_args,null, $args->show_mode);
}
else if($args->do_activate_this || $args->do_deactivate_this)
{
	$tcase_mgr->update_active_status($args->tcase_id, $args->tcversion_id, $active_status);
	$viewer_args['action'] = $action_result;
	$viewer_args['refresh_tree']=DONT_REFRESH;
	$smarty->assign('loadOnCancelURL',$_SESSION['basehref'].'/lib/testcases/archiveData.php?edit=testcase&id='.$args->tcase_id);
	
	$tcase_mgr->show($smarty,$templateCfg->template_dir,$args->tcase_id,
	                 testcase::ALL_VERSIONS,$viewer_args,null, $args->show_mode);
}

// --------------------------------------------------------------------------
if ($show_newTC_form)
{
	
	// BUGID 2163 - Create test case with same title, after submit, all data lost 
  	$tc_default=array('id' => 0, 'name' => '');
  	$tc_default['importance'] = $init_inputs ? $tlCfg->testcase_importance_default : $args->importance;
  	$tc_default['execution_type'] = $init_inputs ? TESTCASE_EXECUTION_TYPE_MANUAL : $args->exec_type;
  	foreach ($oWebEditor->cfg as $key => $value)
  	{
  	    $of = &$oWebEditor->editor[$key];
  	    $rows = $oWebEditor->cfg[$key]['rows'];
  	    $cols = $oWebEditor->cfg[$key]['cols'];
  	    if( $init_inputs)
  	    {
  	      $of->Value = getItemTemplateContents('testcase_template', $of->InstanceName, '');
  	    }
  	    else
  	    {
  	  		$of->Value = $args->$key;
		}
		$smarty->assign($key, $of->CreateHTML($rows,$cols));
  	} // foreach ($a_oWebEditor_cfg as $key)


	$filters=$tcase_mgr->buildCFLocationMap();
	foreach($filters as $locationKey => $locationFilter)
	{ 
		$cf_smarty[$locationKey] = 
			$tcase_mgr->html_table_of_custom_field_inputs($args->tcase_id,$args->container_id,'design','',
			                                              null,null,null,$locationFilter);

    }


	$gui->cf = $cf_smarty;
	$gui->tc = $tc_default;
	$gui->containerID = $args->container_id;
	$smarty->assign('gui',$gui);

    $templateCfg = templateConfiguration('tcNew');
  	$smarty->display($templateCfg->template_dir . $templateCfg->default_template);
}



/*
  function:

  args:

  returns:

*/
function init_args($spec_cfg,$otName)
{
    $tc_importance_default=config_get('testcase_importance_default');
    
    $args = new stdClass();
    $_REQUEST = strings_stripSlashes($_REQUEST);

    $rightlist_html_name = $otName . "_newRight";
    $args->assigned_keywords_list = isset($_REQUEST[$rightlist_html_name])? $_REQUEST[$rightlist_html_name] : "";

	// $args->step_number = isset($_REQUEST['step_number']) ? intval($_REQUEST['step_number']) : 0;

    $args->container_id = isset($_REQUEST['containerID']) ? intval($_REQUEST['containerID']) : 0;
    $args->tcase_id = isset($_REQUEST['testcase_id']) ? intval($_REQUEST['testcase_id']) : 0;
    $args->tcversion_id = isset($_REQUEST['tcversion_id']) ? intval($_REQUEST['tcversion_id']) : 0;
    
    $args->name = isset($_REQUEST['testcase_name']) ? $_REQUEST['testcase_name'] : null;
    $args->summary = isset($_REQUEST['summary']) ? $_REQUEST['summary'] : null;
    $args->preconditions = isset($_REQUEST['preconditions']) ? $_REQUEST['preconditions'] : null;
    $args->steps = isset($_REQUEST['steps']) ? $_REQUEST['steps'] : null;
    
    $args->expected_results = isset($_REQUEST['expected_results']) ? $_REQUEST['expected_results'] : null;
    $args->new_container_id = isset($_REQUEST['new_container']) ? intval($_REQUEST['new_container']) : 0;
    $args->old_container_id = isset($_REQUEST['old_container']) ? intval($_REQUEST['old_container']) : 0;
    $args->has_been_executed = isset($_REQUEST['has_been_executed']) ? intval($_REQUEST['has_been_executed']) : 0;
    $args->exec_type = isset($_REQUEST['exec_type']) ? $_REQUEST['exec_type'] : TESTCASE_EXECUTION_TYPE_MANUAL;
    $args->importance = isset($_REQUEST['importance']) ? $_REQUEST['importance'] : $tc_importance_default;
    
    $args->doAction = isset($_REQUEST['doAction']) ? $_REQUEST['doAction'] : '';
    
    $args->edit_tc   = isset($_REQUEST['edit_tc']) ? 1 : 0;
    $args->delete_tc = isset($_REQUEST['delete_tc']) ? 1 : 0;
    $args->create_tc = isset($_REQUEST['create_tc']) ? 1 : 0;
    $args->move_copy_tc = isset($_REQUEST['move_copy_tc']) ? 1 : 0;
    $args->delete_tc_version = isset($_REQUEST['delete_tc_version']) ? 1 : 0;
    $args->do_create = isset($_REQUEST['do_create']) ? 1 : 0;
    // $args->do_update = isset($_REQUEST['do_update']) ? 1 : 0;
    $args->do_move   = isset($_REQUEST['do_move']) ? 1 : 0;
    $args->do_copy   = isset($_REQUEST['do_copy']) ? 1 : 0;
    $args->do_delete = isset($_REQUEST['do_delete']) ? 1 : 0;
    $args->do_create_new_version = isset($_REQUEST['do_create_new_version']) ? 1 : 0;
    $args->do_delete_tc_version = isset($_REQUEST['do_delete_tc_version']) ? 1 : 0;
    $args->do_activate_this = isset($_REQUEST['activate_this_tcversion']) ? 1 : 0;
    $args->do_deactivate_this = isset($_REQUEST['deactivate_this_tcversion']) ? 1 : 0;
    $args->target_position = isset($_REQUEST['target_position']) ? $_REQUEST['target_position'] : 'bottom';
    
    // BUGID 2316
    $key2loop=array("keyword_assignments","requirement_assignments");
    foreach($key2loop as $key)
    {
       $args->copy[$key]=isset($_REQUEST[$key])?true:false;    
    }    
    
    
    // 20090419 - franciscom - BUGID - edit while executing
    $args->show_mode = (isset($_REQUEST['show_mode']) && $_REQUEST['show_mode'] != '') ? $_REQUEST['show_mode'] : null;
        
    // from session
    $args->testproject_id = $_SESSION['testprojectID'];
    $args->user_id = $_SESSION['userID'];
    $args->do_refresh = $spec_cfg->automatic_tree_refresh;
    if(isset($_SESSION['tcspec_refresh_on_action']))
    {
    	$args->do_refresh=$_SESSION['tcspec_refresh_on_action'] == "yes" ? 1 : 0 ;
    }
    $args->opt_requirements = isset($_SESSION['testprojectOptReqs']) ? $_SESSION['testprojectOptReqs'] : null; 
	$args->basehref = $_SESSION['basehref'];
    return $args;
}


/*
  function: initializeOptionTransferCfg
  args :
  returns: 
*/
function initializeOptionTransferCfg($otName,&$argsObj,&$tprojectMgr)
{
    $otCfg = new stdClass();
    if($argsObj->create_tc || $argsObj->edit_tc || $argsObj->do_create)
    {
        $otCfg = opt_transf_empty_cfg();
        $otCfg->global_lbl = '';
        $otCfg->from->lbl = lang_get('available_kword');
        $otCfg->from->map = $tprojectMgr->get_keywords_map($argsObj->testproject_id);
        $otCfg->to->lbl = lang_get('assigned_kword');
    }
    $otCfg->js_ot_name = $otName;
    return $otCfg;
}

/*
  function: createWebEditors

      When using tinymce or none as web editor, we need to set rows and cols
      to appropriate values, to avoid an ugly ui.
      null => use default values defined on editor class file
      Rows and Cols values are useless for FCKeditor

  args :
  
  returns: object
  
  rev: 20080902 - franciscom - manage column number as function of layout for tinymce
*/
function createWebEditors($basehref,$editorCfg)
{
    $specGUICfg=config_get('spec_cfg');
    $layout=$specGUICfg->steps_results_layout;
    
    $cols=array('steps' => array('horizontal' => 38, 'vertical' => null),
                'expected_results' => array('horizontal' => 38, 'vertical' => null));
        
    $owe = new stdClass();
    
    // Rows and Cols configuration
    $owe->cfg = array('summary' => array('rows'=> null,'cols' => null),
                      'preconditions' => array('rows'=> null,'cols' => null) ,
                      'steps' => array('rows'=> null,'cols' => $cols['steps'][$layout]) ,
                      'expected_results' => array('rows'=> null, 'cols' => $cols['expected_results'][$layout]));
    
    $owe->editor = array();
    foreach ($owe->cfg as $key => $value)
    {
    	$owe->editor[$key] = web_editor($key,$basehref,$editorCfg);
    }
    
    return $owe;
}

/*
  function: getCfg
  args :
  returns: object
*/
function getCfg()
{
    $cfg=new stdClass();
    $cfg->treemenu_default_testcase_order = config_get('treemenu_default_testcase_order');
    $cfg->spec = config_get('spec_cfg');
    $cfg->exclude_node_types = array('testplan' => 1, 'requirement' => 1, 'requirement_spec' => 1);
    $cfg->tcase_template = config_get('testcase_template');
    $cfg->webEditorCfg=getWebEditorCfg('design');
    
    return $cfg;
}

/*
  function: getGrants
  args :
  returns: object
*/
function getGrants(&$dbHandler)
{
    $grants=new stdClass();
    $grants->requirement_mgmt=has_rights($dbHandler,"mgt_modify_req"); 

    return $grants;
}


/**
 * 
 *
 */
funtion initializeGui(&$dbHandler,&$argsObj,$cfgObj,&$treeMgr)
{
	$guiObj = new stdClass();
	$guiObj->editorType = $cfgObj->webEditorCfg['type'];
	$guiObj->grants = getGrants($dbHandler);
	$guiObj->opt_requirements = $argsObj->opt_requirements; 
	$guiObj->action_on_duplicated_name = 'generate_new';
	$guiObj->show_mode = $argsObj->show_mode;
    $guiObj->has_been_executed = $argsObj->has_been_executed;
    $guiObj->attachments = null;
	$guiObj->parent_info = null;
	$guiObj->user_feedback = '';
	
	if($argsObj->container_id > 0)
	{
		$pnode_info = $treeMgr->get_node_hierarchy_info($argsObj->container_id);
		$node_descr = array_flip($treeMgr->get_available_node_types());
		$gui->parent_info['name'] = $pnode_info['name'];
		$gui->parent_info['description'] = lang_get($node_descr[$pnode_info['node_type_id']]);
	}
	
	return $guiObj;
}
?>