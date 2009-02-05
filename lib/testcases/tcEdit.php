<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 *
 * Filename $RCSfile: tcEdit.php,v $
 *
 * @version $Revision: 1.96 $
 * @modified $Date: 2009/02/05 19:42:40 $  by $Author: schlundus $
 * This page manages all the editing of test cases.
 *
 * rev: 
 *     20080827 - franciscom - BUGID 1692 
 *     20080706 - franciscom - force refresh tree when operation can put tree on
 *                             situation that lead to errors if user click on deleted element.
 *     20080203 - franciscom - changes on $tcase_mgr->show() interface
 *     20080105 - franciscom - REQID 1248 - added logic to manage copy/move on top or bottom
 *     
 *     20071201 - franciscom - new web editor code
 *     20071106 - BUGID 1165
 *     20070826 - franciscom - is automatic tree refresh is disable,
 *                             do not refresh if test case changes during update
 *     
 *     20070701 - franciscom - feedback improvement on new version operation
 *     20070302 - franciscom - BUGID
 *     20070220 - franciscom - automatic tree refresh management
 *     20070218 - franciscom - added $g_spec_cfg->automatic_tree_refresh to the refresh tree logic
 *
 *
* -------------------------------------------------------------------------------- */

require_once("../../config.inc.php");
require_once("common.php");
require_once("opt_transfer.php");
require_once("web_editor.php");

$cfg=getCfg();
require_once(require_web_editor($cfg->webEditorCfg['type']));

testlinkInitPage($db);
$tcase_mgr = new testcase($db);
$tproject_mgr = new testproject($db);
$tree_mgr = new tree($db);
$tsuite_mgr = new testsuite($db);

$templateCfg=templateConfiguration();

$commandMgr=new testcaseCommands($db);
$commandMgr->setTemplateCfg(templateConfiguration());

$oWebEditor=createWebEditors($_SESSION['basehref'],$cfg->webEditorCfg);

$sqlResult = "";

$show_newTC_form = 0;
$optionTransferName='ot';
$args = init_args($cfg->spec,$optionTransferName);
$opt_cfg = initializeOptionTransferCfg($optionTransferName,$args,$tproject_mgr);
$gui=new stdClass();
$gui->editorType=$cfg->webEditorCfg['type'];
$gui->grants=getGrants($db);
$gui->opt_requirements=isset($_SESSION['testprojectOptReqs']) ? $_SESSION['testprojectOptReqs'] : null; 
$gui->action_on_duplicated_name='generate_new';

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->assign('has_been_executed',$args->has_been_executed);
$smarty->assign('execution_types',$tcase_mgr->get_execution_types());
$smarty->assign('attachments',null);


$active_status = 0;
$action_result = "deactivate_this_version";
if($args->do_activate_this)
{
	$active_status = 1;
	$action_result = "activate_this_version";
}

$login_name = $_SESSION['currentUser']->login;
$version = isset($_REQUEST['version']) ? intval($_REQUEST['version']) : 0;
$user_feedback = '';

if($args->container_id > 0)
{
	$pnode_info = $tree_mgr->get_node_hierachy_info($args->container_id);
	$node_descr = array_flip($tree_mgr->get_available_node_types());
	$parent_info['name'] = $pnode_info['name'];
	$parent_info['description'] = lang_get($node_descr[$pnode_info['node_type_id']]);
	$smarty->assign('parent_info',$parent_info);
}

$name_ok = 1;

if($args->do_create )
{
	// BUGID 0000086
	$result = lang_get('warning_empty_tc_title');
	if($name_ok && !check_string($args->name,$g_ereg_forbidden))
	{
		$msg = lang_get('string_contains_bad_chars');
		$user_feedback = $msg;
		$name_ok = 0;
	}
	if($name_ok && strlen($args->name) == 0)
	{
		$msg = lang_get('warning_empty_tc_title');
		$user_feedback = $msg;
		$name_ok = 0;
	}
}


switch($args->doAction)
{
    case "doUpdate":
	      $op=$commandMgr->doUpdate($args,$_REQUEST);
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

		$cf_smarty = $tcase_mgr->html_table_of_custom_field_inputs($args->tcase_id);
    $smarty->assign('cf',$cf_smarty);
   	$smarty->assign('tc', $tc_data[0]);
  	$smarty->assign('opt_cfg', $opt_cfg);
  	$smarty->display($templateCfg->template_dir . $g_tpl['tcEdit']);
}
else if($args->create_tc)
{
	$show_newTC_form = 1;
	$opt_cfg->to->map = array();
	keywords_opt_transf_cfg($opt_cfg, $args->assigned_keywords_list);
	$smarty->assign('opt_cfg', $opt_cfg);
}
else if($args->do_create)
{
	$show_newTC_form = 1;
	if ($name_ok)
	{
		$user_feedback = lang_get('error_tc_add');
    $sqlResult = 'ko';
		$tcase = $tcase_mgr->create($args->container_id,$args->name,$args->summary,$args->steps,
		                            $args->expected_results,$args->user_id,$args->assigned_keywords_list,
		                            $cfg->treemenu_default_testcase_order,testcase::AUTOMATIC_ID,
		                            config_get('check_names_for_duplicates'),'block',$args->exec_type,
		                            $args->importance);

		if($tcase['status_ok'])
		{
			$cf_map = $tcase_mgr->cfield_mgr->get_linked_cfields_at_design($args->testproject_id,ENABLED,
			                                                             NO_FILTER_SHOW_ON_EXEC,'testcase') ;
			$tcase_mgr->cfield_mgr->design_values_to_db($_REQUEST,$tcase['id']);

      		$user_feedback = sprintf(lang_get('tc_created'),$args->name);
      		$sqlResult = 'ok';
		}
    	// BUGID 0001267 by cmurray
		elseif(isset($tcase['msg']))
		{
     		$user_feedback .= '' . $tcase['msg'];
		}
	}

	keywords_opt_transf_cfg($opt_cfg, $args->assigned_keywords_list);
 	$smarty->assign('opt_cfg', $opt_cfg);
 	$smarty->assign('sqlResult', $sqlResult);
	$smarty->assign('user_feedback', $user_feedback);
	$smarty->assign('testcase_name', $args->name);
	$smarty->assign('item', 'testcase');
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

	$smarty->assign('exec_status_quo',$exec_status_quo);
	$smarty->assign('title', lang_get('title_del_tc'));
	$smarty->assign('testcase_name', $tcinfo[0]['name']);
	$smarty->assign('testcase_id', $args->tcase_id);
	$smarty->assign('tcversion_id', testcase::ALL_VERSIONS);
	$smarty->assign('delete_message', $msg);
	$gui->refresh_tree = "yes";
	$smarty->display($templateCfg->template_dir . 'tcDelete.tpl');
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
	$smarty->assign('title', lang_get('title_del_tc') . TITLE_SEP_TYPE3 .
	                         lang_get('version') . " " . $tcinfo[0]['version']);

	$smarty->assign('testcase_name', $tcinfo[0]['name']);
	$smarty->assign('testcase_id', $args->tcase_id);
	$smarty->assign('tcversion_id', $args->tcversion_id);
	$smarty->assign('delete_message', $msg);
	$smarty->assign('exec_status_quo',$sq);
	$gui->refresh_tree = "no";
	$smarty->display($templateCfg->template_dir . 'tcDelete.tpl');
}
else if($args->do_delete)
{
	
 	$user_feedback = '';
	$msg = '';
	$action_result = 'deleted';
	$verbose_result = 'ok';
	$tcinfo = $tcase_mgr->get_by_id($args->tcase_id,$args->tcversion_id);
	if (!$tcase_mgr->delete($args->tcase_id,$args->tcversion_id))
	{
		$action_result = '';
		$verbose_result = $db->error_msg();
	}
	else
	{
		$user_feedback = sprintf(lang_get('tc_deleted'),$tcinfo[0]['name']);
	}

	$the_title = lang_get('title_del_tc') . htmlspecialchars($tcinfo[0]['name']);
  
  // 20080706 - refresh must be forced to avoid a wrong tree situation.
  // if tree is not refreshed and user click on deleted test case he/she
  // will get a SQL error
  // $refresh_tree = $cfg->spec->automatic_tree_refresh ? "yes" : "no";
  $gui->refresh_tree = "yes";
 
  // When deleting JUST one version, there is no need to refresh tree
	if($args->tcversion_id != testcase::ALL_VERSIONS)
	{
		  $the_title .= " " . lang_get('version') . " " . $tcinfo[0]['version'];
		  $gui->refresh_tree = "no";
	  	$user_feedback = sprintf(lang_get('tc_version_deleted'),$tcinfo[0]['name'],$tcinfo[0]['version']);
	}

  $smarty->assign('gui',$gui); 
	$smarty->assign('title', $the_title);
	$smarty->assign('testcase_name', $tcinfo[0]['name']);
	$smarty->assign('user_feedback', $user_feedback);
	$smarty->assign('sqlResult', $verbose_result);
	$smarty->assign('testcase_id', $args->tcase_id);
	$smarty->assign('delete_message', $msg);
	$smarty->assign('action',$action_result);
	$smarty->display($templateCfg->template_dir . 'tcDelete.tpl');
}
else if($args->move_copy_tc)
{
	// need to get the testproject for the test case
	$tproject_id = $tcase_mgr->get_testproject($args->tcase_id);
	$the_tc_node = $tree_mgr->get_node_hierachy_info($args->tcase_id);
	$tc_parent_id = $the_tc_node['parent_id'];

	// 20080105 - franciscom - seems to be useless
	// 20071111 - franciscom
	// $the_tree = $tree_mgr->get_subtree($tproject_id, array("testplan"=>"exclude me",
	//                                                        "requirement_spec"=>"exclude me",
	//                                                        "requirement"=>"exclude me",
	//                                                        "testcase"=>"exclude me"));
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
	$smarty->display($templateCfg->template_dir . 'tcMove.tpl');
	// move test case to another category
}
else if($args->do_move)
{
	$result = $tree_mgr->change_parent($args->tcase_id,$args->new_container_id);
  	$tree_mgr->change_child_order($args->new_container_id,$args->tcase_id,
                                  $args->target_position,$cfg->exclude_node_types);

	$smarty->assign('refreshTree',$args->do_refresh);
	$tsuite_mgr->show($smarty,$templateCfg->template_dir,$args->old_container_id);
}
else if($args->do_copy)
{
	  $user_feedback='';
	  $msg = '';
	  $action_result = 'copied';

	  // $result = $tcase_mgr->copy_to($args->tcase_id,$args->new_container_id,$args->user_id,TC_COPY_KEYWORDS,
	  //                               config_get('check_names_for_duplicates'),'block');

    // 20090120 - franciscom
	  $result = $tcase_mgr->copy_to($args->tcase_id,$args->new_container_id,$args->user_id,TC_COPY_KEYWORDS,
	                                config_get('check_names_for_duplicates'),
	                                config_get('action_on_duplicate_name'));
	  
	  $msg = $result['msg'];
    if($msg == "ok")
    {
		    $tree_mgr->change_child_order($args->new_container_id,$result['id'],
		                                $args->target_position,$cfg->exclude_node_types);
        
		    $ts_sep = config_get('testsuite_sep');
		    $tc_info = $tcase_mgr->get_by_id($args->tcase_id);
		    $container_info = $tree_mgr->get_node_hierachy_info($args->new_container_id);
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
	  $tcase_mgr->show($smarty,$templateCfg->template_dir,$args->tcase_id,$args->tcversion_id,$viewer_args);

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
	$smarty->assign('loadOnCancelURL',$_SESSION['basehref'].'/lib/testcases/archiveData.php?edit=testcase&id='.$args->tcase_id);
	
	$tcase_mgr->show($smarty,$templateCfg->template_dir,$args->tcase_id,testcase::ALL_VERSIONS, $viewer_args);
}
else if($args->do_activate_this || $args->do_deactivate_this)
{
	$tcase_mgr->update_active_status($args->tcase_id, $args->tcversion_id, $active_status);
	$viewer_args['action'] = $action_result;
	$viewer_args['refresh_tree']=DONT_REFRESH;
	$smarty->assign('loadOnCancelURL',$_SESSION['basehref'].'/lib/testcases/archiveData.php?edit=testcase&id='.$args->tcase_id);
	
	$tcase_mgr->show($smarty,$templateCfg->template_dir,$args->tcase_id,testcase::ALL_VERSIONS,$viewer_args);
}

// --------------------------------------------------------------------------
if ($show_newTC_form)
{
	$smarty->assign('containerID', $args->container_id);

  // ------------------------------------------------------------------------
  // 20071106 - BUGID 1165
	foreach ($oWebEditor->cfg as $key => $value)
	{
		switch($cfg->tcase_template->$key->type)
		{
			case 'string':
				$the_value = $cfg->tcase_template->$key->value;
				break;

			case 'string_id':
				$the_value = lang_get($cfg->tcase_template->$key->value);
				break;

			case 'file':
				$the_value = read_file($cfg->tcase_template->$key->value);
				break;

			default:
				$the_value = '';
				break;
		}
	    $of = &$oWebEditor->editor[$key];
	    $rows = $oWebEditor->cfg[$key]['rows'];
	    $cols = $oWebEditor->cfg[$key]['cols'];

		$of->Value = $the_value;
	    $smarty->assign($key, $of->CreateHTML($rows,$cols));
	} // foreach ($a_oWebEditor_cfg as $key)
  // ------------------------------------------------------------------------

  $tc_default=array('id' => 0, 'name' => '', 'execution_type' => TESTCASE_EXECUTION_TYPE_MANUAL);
	$tc_default['importance'] = $tlCfg->testcase_importance_default;
	$cf_smarty = $tcase_mgr->html_table_of_custom_field_inputs($args->tcase_id,$args->container_id);
 	$smarty->assign('cf',$cf_smarty);
 	$smarty->assign('tc',$tc_default);
	$smarty->display($templateCfg->template_dir . $g_tpl['tcNew']);
}

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
		$fContents = lang_get('problems_trying_to_access_template') . " {$file_name} ";

	return $fContents;
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


    $args->container_id = isset($_REQUEST['containerID']) ? intval($_REQUEST['containerID']) : 0;
    $args->tcase_id = isset($_REQUEST['testcase_id']) ? intval($_REQUEST['testcase_id']) : 0;
    $args->tcversion_id = isset($_REQUEST['tcversion_id']) ? intval($_REQUEST['tcversion_id']) : 0;
    
    $args->name = isset($_REQUEST['testcase_name']) ? $_REQUEST['testcase_name'] : null;
    $args->summary = isset($_REQUEST['summary']) ? $_REQUEST['summary'] : null;
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
    
    
    // from session
    $args->testproject_id = $_SESSION['testprojectID'];
    $args->user_id = $_SESSION['userID'];
    $args->do_refresh = $spec_cfg->automatic_tree_refresh;
    if(isset($_SESSION['tcspec_refresh_on_action']))
    {
    	$args->do_refresh=$_SESSION['tcspec_refresh_on_action'] == "yes" ? 1 : 0 ;
    }
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

?>