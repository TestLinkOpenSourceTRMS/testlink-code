<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Test Case and Test Steps operations
 *
 * @package 	TestLink
 * @author 		TestLink community
 * @copyright 	2007-2009, TestLink community 
 * @version    	CVS: $Id: tcEdit.php,v 1.144 2010/02/21 16:11:53 franciscom Exp $
 * @link 		http://www.teamst.org/index.php
 *
 *
 *	@internal revisions
 *  20100124 - franciscom - fixed bug on copy test cases - do not obey to top or bottom user choice
 *  20100106 - franciscom - Multiple Test Case Steps Feature
 *  20100104 - franciscom - fixed bug on create new version, now is created
 *                          from selected version and NOT FROM LATEST
 *	20100103 - franciscom - refactoring to use command class
 *	20090831 - franciscom - preconditions
 *	20090401 - franciscom - BUGID 2364 - edit while executing
 *  20090401 - franciscom - BUGID 2316
 *  20090325 - franciscom - BUGID - problems with add to testplan
 *  20090302 - franciscom - BUGID 2163 - Create test case with same title, after submit, all data lost 
 *  20080827 - franciscom - BUGID 1692 
 *  20080105 - franciscom - REQID 1248 - added logic to manage copy/move on top or bottom
 *  20071106 - BUGID 1165
 **/




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

// new dBug($args);
$templateCfg = templateConfiguration('tcEdit');

$commandMgr = new testcaseCommands($db);
$commandMgr->setTemplateCfg(templateConfiguration());

$testCaseEditorKeys = array('summary' => 'summary','preconditions' => 'preconditions');
$oWebEditor = createWebEditors($args->basehref,$cfg->webEditorCfg,$testCaseEditorKeys);

$sqlResult = "";
$init_inputs=true; // BUGID 2163 - Create test case with same title, after submit, all data lost 
$show_newTC_form = 0;

$opt_cfg = initializeOptionTransferCfg($optionTransferName,$args,$tproject_mgr);
$gui = initializeGui($db,$args,$cfg,$tcase_mgr);

$smarty = new TLSmarty();

$active_status = 0;
$name_ok = 1;
$action_result = "deactivate_this_version";
if($args->do_activate_this)
{
	$active_status = 1;
	$action_result = "activate_this_version";
}

$doRender = false;
$pfn = $args->doAction;
switch($args->doAction)
{
    case "doUpdate":
    case "doAdd2testplan":
        $op=$commandMgr->$pfn($args,$_REQUEST);
    break;
	
	case "edit":  
	case "create":  
	case "doCreate":  
        $oWebEditorKeys = array_keys($oWebEditor->cfg);
        $op = $commandMgr->$pfn($args,$opt_cfg,$oWebEditorKeys);
        $doRender = true;
    break;
    
	case "delete":  
	case "doDelete":  
    case "createStep":
    case "editStep":
    case "doCreateStep":
    case "doUpdateStep":
    case "doDeleteStep":
    case "doReorderSteps":
        $op=$commandMgr->$pfn($args,$_REQUEST);
        $doRender = true;
    break;

}

if( $doRender )
{
	// renderGui($args,$gui,$op,$templateCfg,$cfg->webEditorCfg);
	renderGui($args,$gui,$op,$templateCfg,$cfg);
	exit();
}

if($args->delete_tc_version)
{
	$status_quo_map = $tcase_mgr->get_versions_status_quo($args->tcase_id);
	$exec_status_quo = $tcase_mgr->get_exec_status($args->tcase_id);
    
    $msg = '';
	$sq = null;
	if(!is_null($exec_status_quo))
	{
		if(isset($exec_status_quo[$args->tcversion_id]))
		{
			$sq = array($args->tcversion_id => $exec_status_quo[$args->tcversion_id]);
		}	
	}

	if(intval($status_quo_map[$args->tcversion_id]['executed']))
	{
		$msg = lang_get('warning') . TITLE_SEP . lang_get('delete_linked_and_exec');
	}
	else if(intval($status_quo_map[$args->tcversion_id]['linked']))
	{
      	$msg = lang_get('warning') . TITLE_SEP . lang_get('delete_linked');
	}

	$tcinfo = $tcase_mgr->get_by_id($args->tcase_id,$args->tcversion_id);

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
	$gui->move_enabled = 1;
	if($container_qty == 1)
	{
		// move operation is nonsense
		$gui->move_enabled = 0;
	}
    $gui->top_checked = 'checked=checked';
	$gui->bottom_checked = '';
	$gui->old_container = $the_tc_node['parent_id']; // original container
	$gui->array_container = $the_xx;
	$gui->testcase_id = $args->tcase_id;
	$gui->name = $tc_info[0]['name'];

	$smarty->assign('gui', $gui);
    $templateCfg = templateConfiguration('tcMove');
    $smarty->display($templateCfg->template_dir . $templateCfg->default_template);
}
else if($args->do_move)
{
	$result = $tree_mgr->change_parent($args->tcase_id,$args->new_container_id);
  	$tree_mgr->change_child_order($args->new_container_id,$args->tcase_id,
                                  $args->target_position,$cfg->exclude_node_types);

    $gui->refreshTree = $args->do_refresh;
	$tsuite_mgr->show($smarty,$gui,$templateCfg->template_dir,$args->old_container_id);
}
else if($args->do_copy)
{
	$user_feedback='';
	$msg = '';
	$action_result = 'copied';
	$options = array('check_duplicate_name' => config_get('check_names_for_duplicates'),
                     'action_on_duplicate_name' => config_get('action_on_duplicate_name'),
                     'copy_also' => $args->copy);

	$result = $tcase_mgr->copy_to($args->tcase_id,$args->new_container_id,$args->user_id,$options);
	$msg = $result['msg'];
    if($result['status_ok'])
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

	$gui->refreshTree = $args->do_refresh;
	$viewer_args['action'] = $action_result;
	$viewer_args['refresh_tree']=$args->do_refresh?"yes":"no";
	$viewer_args['msg_result'] = $msg;
	$viewer_args['user_feedback'] = $user_feedback;
	$tcase_mgr->show($smarty,$gui,$templateCfg->template_dir,$args->tcase_id,
	                 $args->tcversion_id,$viewer_args,null, $args->show_mode);

}
else if($args->do_create_new_version)
{
	$user_feedback = '';
	$show_newTC_form = 0;
	$action_result = "do_update";
	$msg = lang_get('error_tc_add');
	$op = $tcase_mgr->create_new_version($args->tcase_id,$args->user_id,$args->tcversion_id);
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
	// $smarty->assign('loadOnCancelURL',
	//                 $_SESSION['basehref'].'/lib/testcases/archiveData.php?edit=testcase&id='.$args->tcase_id);
	
	// 20090419 - BUGID - 
	$gui->loadOnCancelURL = $_SESSION['basehref'] . 
	                        '/lib/testcases/archiveData.php?edit=testcase&id=' . $args->tcase_id;
	
	$testcase_version = !is_null($args->show_mode) ? $args->tcversion_id : testcase::ALL_VERSIONS;
	$tcase_mgr->show($smarty,$gui,$templateCfg->template_dir,$args->tcase_id,$testcase_version, 
	                 $viewer_args,null, $args->show_mode);
}
else if($args->do_activate_this || $args->do_deactivate_this)
{
	$gui->loadOnCancelURL = $_SESSION['basehref'] . 
	                        '/lib/testcases/archiveData.php?edit=testcase&id=' . $args->tcase_id;

	$tcase_mgr->update_active_status($args->tcase_id, $args->tcversion_id, $active_status);
	$viewer_args['action'] = $action_result;
	$viewer_args['refresh_tree']=DONT_REFRESH;

	$tcase_mgr->show($smarty,$gui,$templateCfg->template_dir,$args->tcase_id,
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

    // new dBug($cf_smarty);

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

    // new dBug($_REQUEST);

    $rightlist_html_name = $otName . "_newRight";
    $args->assigned_keywords_list = isset($_REQUEST[$rightlist_html_name])? $_REQUEST[$rightlist_html_name] : "";
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
    
    $edit_tc = isset($_REQUEST['edit_tc']) ? 1 : 0;
    $delete_tc = isset($_REQUEST['delete_tc']) ? 1 : 0;
    $do_delete = isset($_REQUEST['do_delete']) ? 1 : 0;
    $create_tc = isset($_REQUEST['create_tc']) ? 1 : 0;
    $do_create = isset($_REQUEST['do_create']) ? 1 : 0;

    if( $edit_tc )
    {
    	$args->doAction = 'edit';
    }
    if( $delete_tc )
    {
    	$args->doAction = 'delete';
    }
    if( $do_delete )
    {
    	$args->doAction = 'doDelete';
    }
    if( $create_tc )
    {
    	$args->doAction = 'create';
    }

    if( $do_create )
    {
    	$args->doAction = 'doCreate';
    }

    
    
    $args->move_copy_tc = isset($_REQUEST['move_copy_tc']) ? 1 : 0;
    $args->delete_tc_version = isset($_REQUEST['delete_tc_version']) ? 1 : 0;
    $args->do_move   = isset($_REQUEST['do_move']) ? 1 : 0;
    $args->do_copy   = isset($_REQUEST['do_copy']) ? 1 : 0;
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

    // Multiple Test Case Steps Feature
	$args->step_number = isset($_REQUEST['step_number']) ? intval($_REQUEST['step_number']) : 0;
	$args->step_id = isset($_REQUEST['step_id']) ? intval($_REQUEST['step_id']) : 0;
	$args->step_set = isset($_REQUEST['step_set']) ? $_REQUEST['step_set'] : null;

        
    // from session
    $args->testproject_id = $_SESSION['testprojectID'];
    $args->user_id = $_SESSION['userID'];
    $args->do_refresh = $spec_cfg->automatic_tree_refresh;
    if(isset($_SESSION['tcspec_refresh_on_action']))
    {
    	$args->do_refresh=$_SESSION['tcspec_refresh_on_action'] == "yes" ? 1 : 0 ;
    }
    
	$args->opt_requirements = null;
	if( isset($_SESSION['testprojectOptions']) )
	{
		$args->opt_requirements = $_SESSION['testprojectOptions']->requirementsEnabled;
	} 

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
    switch($argsObj->doAction)
    {
    	case 'create':
    	case 'edit':
    	case 'doCreate':
        	$otCfg = opt_transf_empty_cfg();
        	$otCfg->global_lbl = '';
        	$otCfg->from->lbl = lang_get('available_kword');
        	$otCfg->from->map = $tprojectMgr->get_keywords_map($argsObj->testproject_id);
        	$otCfg->to->lbl = lang_get('assigned_kword');
    	break;
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
function createWebEditors($basehref,$editorCfg,$editorSet=null)
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
    $force_create = is_null($editorSet);
    foreach ($owe->cfg as $key => $value)
    {
    	if( $force_create || isset($editorSet[$key]) )
    	{
    		$owe->editor[$key] = web_editor($key,$basehref,$editorCfg);
    	}
    	else
    	{
    		unset($owe->cfg[$key]);
    	}
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
    
    $cfg->editorKeys = new stdClass();
    $cfg->editorKeys->testcase = array('summary' => true, 'preconditions' => true);    
    $cfg->editorKeys->step = array('steps' => true, 'expected_results' => true);    
    
    
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
function initializeGui(&$dbHandler,&$argsObj,$cfgObj,&$tcaseMgr)
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
	
	$guiObj->loadOnCancelURL = $_SESSION['basehref'] . 
	                          '/lib/testcases/archiveData.php?edit=testcase&id=' . $argsObj->tcase_id;

	
	if($argsObj->container_id > 0)
	{
		$pnode_info = $tcaseMgr->tree_manager->get_node_hierarchy_info($argsObj->container_id);
		$node_descr = array_flip($tcaseMgr->tree_manager->get_available_node_types());
		$guiObj->parent_info['name'] = $pnode_info['name'];
		$guiObj->parent_info['description'] = lang_get($node_descr[$pnode_info['node_type_id']]);
	}
	
	$guiObj->direct_link = $tcaseMgr->buildDirectWebLink($_SESSION['basehref'],$argsObj->tcase_id,$argsObj->testproject_id);

	
	return $guiObj;
}

/**
 * manage GUI rendering
 *
 */
function renderGui(&$argsObj,$guiObj,$opObj,$templateCfg,$cfgObj)
{
    $smartyObj = new TLSmarty();
    $renderType = 'none';
    //
    // key: operation requested (normally received from GUI on doAction)
    // value: operation value to set on doAction HTML INPUT
    // This is useful when you use same template (example xxEdit.tpl), for create and edit.
    // When template is used for create -> operation: doCreate.
    // When template is used for edit -> operation: doUpdate.
    //              
    // used to set value of: $guiObj->operation
    //
    $actionOperation = array('create' => 'doCreate', 'doCreate' => 'doCreate',
                             'edit' => 'doUpdate','delete' => 'doDelete', 'doDelete' => '',
                             'createStep' => 'doCreateStep', 'doCreateStep' => 'doCreateStep',
                             'editStep' => 'doUpdateStep', 'doUpdateStep' => 'doUpdateStep',  
                             'doDeleteStep' => '', 'doReorderSteps' => '');

	$initWebEditorFromTemplate = $opObj->initWebEditorFromTemplate;                             
    $oWebEditor = createWebEditors($argsObj->basehref,$cfgObj->webEditorCfg); 

	foreach ($oWebEditor->cfg as $key => $value)
  	{
  		$of = &$oWebEditor->editor[$key];
  		$rows = $oWebEditor->cfg[$key]['rows'];
  		$cols = $oWebEditor->cfg[$key]['cols'];
		switch($argsObj->doAction)
    	{
    	    case "edit":
    	    case "delete":
    	    case "editStep":
    	    case "doCreate":
    	    case "doDelete":
    	    case "doCreateStep":
  				$initWebEditorFromTemplate = false;
  				$of->Value = $argsObj->$key;
  			break;
  			
    	    case "create":
  			default:	
  				$initWebEditorFromTemplate = true;
  			break;
  		}
        $guiObj->operation = $actionOperation[$argsObj->doAction];
	
  		if(	$initWebEditorFromTemplate)
  		{
			$of->Value = getItemTemplateContents('testcase_template', $of->InstanceName, '');	
		}	
		$smartyObj->assign($key, $of->CreateHTML($rows,$cols));

	}
      
    switch($argsObj->doAction)
    {
        case "edit":
   	    case "create":
        case "delete":
        case "createStep":
        case "editStep":
   	    case "doCreate":
        case "doDelete":
        case "doCreateStep":
        case "doUpdateStep":
        case "doDeleteStep":
        case "doReorderSteps":
            $renderType = 'template';
            
            // Document !!!!
            $key2loop = get_object_vars($opObj);
            foreach($key2loop as $key => $value)
            {
                $guiObj->$key = $value;
            }
            $guiObj->operation = $actionOperation[$argsObj->doAction];
            
            $tplDir = (!isset($opObj->template_dir)  || is_null($opObj->template_dir)) ? $templateCfg->template_dir : $opObj->template_dir;
            $tpl = is_null($opObj->template) ? $templateCfg->default_template : $opObj->template;
            
            $pos = strpos($tpl, '.php');
           	if($pos === false)
           	{
                $tpl = $tplDir . $tpl;      
            }
            else
            {
                $renderType = 'redirect';  
            } 
        break;
    }

    switch($renderType)
    {
        case 'template':
        	$smartyObj->assign('gui',$guiObj);
		    $smartyObj->display($tpl);
        	break;  
 
        case 'redirect':
		      header("Location: {$tpl}");
	  		  exit();
        break;

        default:
       	break;
    }

}

?>