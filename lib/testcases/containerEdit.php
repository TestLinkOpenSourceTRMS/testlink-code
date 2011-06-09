<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource	containerEdit.php
 * @package 	TestLink
 * @author 		Martin Havlat
 * @copyright 	2005-2011, TestLink community 
 * @link 		http://www.teamst.org/index.php
 *
 * @internal revisions
 *	20110402 - franciscom - BUGID 4322: New Option to block delete of executed test cases.	
 *  20101216 - asimon - refresh tree when creating new testsuite
 *	20101106 - franciscom - added check on $guiObj->testCaseSet and other variables that can be null
 *							to avoid event viewer warnings	when deleting test case in test suite 
 *							that has ONLY one test case.
 *
 *	20101012 - franciscom - BUGID 3890: Create Test Suite with same name that existent sibling - BLOCK
 *  20101011 - asimon - BUGID 3875
 *  20100916 - franciscom - BUGID 3778, 3779 - Option to reorder ALL CHILDREN Test Suites
 *  20100916 - franciscom - BUGID 3639 - reworked
 *  20100914 - franciscom - BUGID 3639 - reorderTestCasesDictionary()
 *  20100909 - franciscom - BUGID 3047: Deleting multiple TCs
 *  20100811 - asimon - BUGID 3669
 *  20100722 - asimon - BUGID 3406, removal of changes for 3049
 *  20100628 - asimon - removal of constants from filter control class
 *  20100625 - asimon - refactoring for new filter features and BUGID 3516
 *  20100624 - asimon - CVS merge (experimental branch to HEAD)
 *  20100314 - franciscom - added logic to refresh tree when copying N test cases 	
 * 						    added logic to get user choice regarding refresh tree from SESSION.
 *  20100223 - asimon - added removeTestcaseAssignments() for BUGID 3049
 *	20100204 - franciscom - changes in $tsuiteMgr->copy_to() call	
 *	20100202 - franciscom - BUGID 3130: TestSuite: Edit - rename Test Suite Name causes PHP Fatal Error
 *                          (bug created due change in show() interface
 *	20091206 - franciscom - addTestSuite() - new test suites are order set to last on tree branch
 *	20081225 - franciscom - Postgres SQL Error
 *	20080827 - franciscom - BUGID 1692
 *	20080329 - franciscom - added contribution by Eugenia Drosdezki - Move/copy testcases
 *	20080223 - franciscom - BUGID 1408
 *	20080129 - franciscom - contribution - tuergeist@gmail.com - doTestSuiteReorder() remove global coupling
 *	20080122 - franciscom - BUGID 1312
 */
require_once("../../config.inc.php");
require_once("common.php");
require_once("opt_transfer.php");
require_once("web_editor.php");
$editorCfg=getWebEditorCfg('design');
require_once(require_web_editor($editorCfg['type']));

testlinkInitPage($db);
$tree_mgr = new tree($db);
$tproject_mgr = new testproject($db);
$tsuite_mgr = new testsuite($db);
$tcase_mgr = new testcase($db);

$template_dir = 'testcases/';
$refreshTree = false;
$level = null;

// Option Transfer configuration
$opt_cfg=new stdClass();
$opt_cfg->js_ot_name = 'ot';

$args = init_args($opt_cfg);

$gui_cfg = config_get('gui');
$smarty = new TLSmarty();
$smarty->assign('editorType',$editorCfg['type']);

$a_keys['testsuite'] = array('details');
$a_tpl = array( 'move_testsuite_viewer' => 'containerMove.tpl',
                'delete_testsuite' => 'containerDelete.tpl',
                'move_testcases_viewer' => 'containerMoveTC.tpl',
                'do_copy_tcase_set' => 'containerMoveTC.tpl',
                'delete_testcases' =>  'containerDeleteTC.tpl',
                'do_delete_testcases' =>  'containerDeleteTC.tpl');

$a_actions = array ('edit_testsuite' => 0,'new_testsuite' => 0,'delete_testsuite' => 0,'do_move' => 0,
					'do_copy' => 0,'reorder_testsuites' => 1,'do_testsuite_reorder' => 0,
                    'add_testsuite' => 1,'move_testsuite_viewer' => 0,'update_testsuite' => 1,
                    'move_testcases_viewer' => 0,'do_move_tcase_set' => 0,
                    'do_copy_tcase_set' => 0, 'del_testsuites_bulk' => 0, 
                    'delete_testcases' => 0,'do_delete_testcases' => 0, 'reorder_testcases' => 0, 
                    'reorder_testsuites_alpha' => 0, 'reorder_testproject_testsuites_alpha' => 0);

$a_init_opt_transfer=array('edit_testsuite' => 1,'new_testsuite'  => 1,'add_testsuite'  => 1,
                           'update_testsuite' => 1);

$the_tpl = null;
$action = null;
$get_c_data = null;
$init_opt_transfer = null;

foreach ($a_actions as $the_key => $the_val)
{
	if (isset($_POST[$the_key]) )
	{
		$the_tpl = isset($a_tpl[$the_key]) ? $a_tpl[$the_key] : null;
		$init_opt_transfer = isset($a_init_opt_transfer[$the_key])?1:0;

		$action = $the_key;
		$get_c_data = $the_val;
		$level = 'testsuite';
		$warning_empty_name = lang_get('warning_empty_com_name');
		break;
	}
}

$lblkey = ($sortCriteria = config_get('testcase_reorder_by')) == 'NAME' ? '_alpha' : '_externalid';
$btn_reorder_testcases = lang_get('btn_reorder_testcases' . $lblkey);

$smarty->assign('level', $level);
$smarty->assign('page_title',lang_get('container_title_' . $level));

if($init_opt_transfer)
{
    $opt_cfg = initializeOptionTransfer($tproject_mgr,$tsuite_mgr,$args,$action);
}
// create  web editor objects
list($oWebEditor,$webEditorHtmlNames,$webEditorTemplateKey)=initWebEditors($a_keys,$level,$editorCfg);

if($get_c_data)
{
	$name_ok = 1;
	$c_data = getValuesFromPost($webEditorHtmlNames);

	if($name_ok && !check_string($c_data['container_name'],$g_ereg_forbidden))
	{
		$msg = lang_get('string_contains_bad_chars');
		$name_ok = 0;
	}

	if($name_ok && ($c_data['container_name'] == ""))
	{
		$msg = $warning_empty_name;
		$name_ok = 0;
	}
}


switch($action)
{
	case 'edit_testsuite':
	case 'new_testsuite':
		keywords_opt_transf_cfg($opt_cfg, $args->assigned_keyword_list);
		$smarty->assign('opt_cfg', $opt_cfg);
		// 20110315 - asimon: refresh tree when creating new testsuite
		$gui = new stdClass();
		$gui->refreshTree = $args->refreshTree;
		$smarty->assign('gui', $gui);
		$tsuite_mgr->viewer_edit_new($smarty,$template_dir,$webEditorHtmlNames,$oWebEditor,$action,
		                             $args->containerID, $args->testsuiteID,null,$webEditorTemplateKey);
		break;

    case 'delete_testsuite':
   		$refreshTree = deleteTestSuite($smarty,$args,$tsuite_mgr,$tree_mgr,$tcase_mgr,$level);
    break;

    case 'move_testsuite_viewer':
		moveTestSuiteViewer($smarty,$tproject_mgr,$args);
	    break;

    case 'move_testcases_viewer':
    	moveTestCasesViewer($db,$smarty,$tproject_mgr,$tree_mgr,$args);
    	break;

	case 'reorder_testsuites':
    	$ret = reorderTestSuiteViewer($smarty,$tree_mgr,$args);
    	$level = is_null($ret) ? $level : $ret;
    	break;

    case 'do_move':
    	moveTestSuite($smarty,$template_dir,$tproject_mgr,$args);
    	break;

    case 'do_copy':
    	copyTestSuite($smarty,$template_dir,$tsuite_mgr,$args);
    	break;

    case 'update_testsuite':
	  	if ($name_ok)
	  	{
        	$msg = updateTestSuite($tsuite_mgr,$args,$c_data,$_REQUEST);
    	}
		$guiObj = new stdClass();
		// BUGID 3875
		$guiObj->btn_reorder_testcases = $btn_reorder_testcases;
  	  	$guiObj->attachments = getAttachmentInfosFrom($tsuite_mgr,$args->testsuiteID);
	  	$guiObj->id = $args->testsuiteID;
		$guiObj->page_title = lang_get('container_title_testsuite');
		$guiObj->refreshTree = $args->refreshTree;
     	$tsuite_mgr->show($smarty,$guiObj,$template_dir,$args->testsuiteID,null,$msg);
    	break;

    case 'add_testsuite':
	    $messages = null;
	    $op['status'] = 0;
		if ($name_ok)
		{
	    	$op = addTestSuite($tsuite_mgr,$args,$c_data,$_REQUEST);
	    	$messages = array( 'result_msg' => $op['messages']['msg'], 
	    	                   'user_feedback' => $op['messages']['user_feedback']);
	  	}
    	
        // $userInput is used to maintain data filled by user if there is
        // a problem with test suite name.
        $userInput = $op['status'] ? null : $_REQUEST; 
        $assignedKeywords = $op['status'] ? "" : $args->assigned_keyword_list;
        keywords_opt_transf_cfg($opt_cfg, $assignedKeywords);
      	$smarty->assign('opt_cfg', $opt_cfg);
      	// 20101216 - asimon: refresh tree when creating new testsuite
      	$gui = new stdClass();
      	$gui->refreshTree = $args->refreshTree;
      	$smarty->assign('gui', $gui);
  	    $tsuite_mgr->viewer_edit_new($smarty,$template_dir,$webEditorHtmlNames, $oWebEditor, $action,
	                                 $args->containerID, null,$messages,
	                                 $webEditorTemplateKey,$userInput);
    	break;


    case 'do_move_tcase_set':
    	moveTestCases($smarty,$template_dir,$tsuite_mgr,$tree_mgr,$args);
    	break;

    case 'do_copy_tcase_set':
    	$op = copyTestCases($smarty,$template_dir,$tsuite_mgr,$tcase_mgr,$args);
    	$refreshTree = $op['refreshTree'];
    	moveTestCasesViewer($db,$smarty,$tproject_mgr,$tree_mgr,$args,$op['userfeedback']);
    	break;


    case 'delete_testcases':
        $args->refreshTree = false;
    	deleteTestCasesViewer($db,$smarty,$tproject_mgr,$tree_mgr,$tsuite_mgr,$tcase_mgr,$args);
    	break;

    case 'do_delete_testcases':
        $args->refreshTree = true;
        doDeleteTestCases($db,$args->tcaseSet,$tcase_mgr);
    	deleteTestCasesViewer($db,$smarty,$tproject_mgr,$tree_mgr,$tsuite_mgr,$tcase_mgr,$args,
    						  lang_get('all_testcases_have_been_deleted'));
    	break;

    // BUGID 3639 
	case 'reorder_testcases': 
    	reorderTestCasesByCriteria($args,$tsuite_mgr,$tree_mgr,$sortCriteria);
		$guiObj = new stdClass();
		$guiObj->btn_reorder_testcases = $btn_reorder_testcases;
		$guiObj->refreshTree = true;
  	  	$guiObj->attachments = getAttachmentInfosFrom($tsuite_mgr,$args->testsuiteID);
	  	$guiObj->id = $args->testsuiteID;
		$guiObj->page_title = lang_get('container_title_testsuite');
     	$tsuite_mgr->show($smarty,$guiObj,$template_dir,$args->testsuiteID,null,null);
    	break;
	

	case 'reorder_testsuites_alpha': 
    	reorderTestSuitesDictionary($args,$tree_mgr,$args->testsuiteID);
		$guiObj = new stdClass();
		$guiObj->btn_reorder_testcases = $btn_reorder_testcases;
		$guiObj->refreshTree = true;
  	  	$guiObj->attachments = getAttachmentInfosFrom($tsuite_mgr,$args->testsuiteID);
	  	$guiObj->id = $args->testsuiteID;
		$guiObj->page_title = lang_get('container_title_testsuite');
     	$tsuite_mgr->show($smarty,$guiObj,$template_dir,$args->testsuiteID,null,null);
    	break;

	case 'reorder_testproject_testsuites_alpha':
    	reorderTestSuitesDictionary($args,$tree_mgr,$args->tprojectID);
		$guiObj = new stdClass();
		$guiObj->btn_reorder_testcases = $btn_reorder_testcases;
		$guiObj->refreshTree = true;
  	  	$guiObj->attachments = getAttachmentInfosFrom($tproject_mgr,$args->tprojectID);
	  	$guiObj->id = $args->tprojectID;
		$guiObj->page_title = lang_get('container_title_testsuite');
     	$tproject_mgr->show($smarty,$guiObj,$template_dir,$args->tprojectID,null,null);
    	break;

    default:
    	trigger_error("containerEdit.php - No correct GET/POST data", E_USER_ERROR);
    	break;
}

if($the_tpl)
{
	$smarty->assign('refreshTree',$refreshTree && $args->refreshTree);
	$smarty->display($template_dir . $the_tpl);
}


/**
 * 
 *
 */
function getValuesFromPost($akeys2get)
{
	$akeys2get[] = 'container_name';
	$c_data = array();
	foreach($akeys2get as $key)
	{
		$c_data[$key] = isset($_POST[$key]) ? strings_stripSlashes($_POST[$key]) : null;
	}
	return $c_data;
}

/*
  function:

  args :

  returns:

*/
function build_del_testsuite_warning_msg(&$tree_mgr,&$tcase_mgr,&$testcases,$tsuite_id)
{
	$msg = null;
	$msg['warning'] = null;
	$msg['link_msg'] = null;
	$msg['delete_msg'] = null;

	if(!is_null($testcases))
	{
    	$show_warning = 0;
    	$delete_msg = '';
  		$verbose = array();
  		$msg['link_msg'] = array();
	
    	$status_warning = array('linked_and_executed' => 1,
    	                    	'linked_but_not_executed' => 1,
    	                    	'no_links' => 0);

		$delete_notice = array('linked_and_executed' => lang_get('delete_notice'),
    	                    	'linked_but_not_executed' => '',
    	                    	'no_links' => '');

		$getOptions = array('addExecIndicator' => true);
  		foreach($testcases as $the_key => $elem)
  		{
  			$verbose[] = $tree_mgr->get_path($elem['id'],$tsuite_id);
  			// $status = $tcase_mgr->check_link_and_exec_status($elem['id']);
			$xx = $tcase_mgr->get_exec_status($elem['id'],null,$getOptions);
			$status = 'no_links';
			if(!is_null($xx))
			{
				$status = $xx['executed'] ? 'linked_and_executed' : 'linked_but_not_executed';
			}
  			$msg['link_msg'][] = $status;

  			if($status_warning[$status])
  			{
  		  		$show_warning = 1;
  		  		$msg['delete_msg'] = $delete_notice[$status];
  			}
	  	}

	  	$idx = 0;
	  	if($show_warning)
	  	{
	  		$msg['warning'] = array();
	  		foreach($verbose as $the_key => $elem)
	  		{
	  			$msg['warning'][$idx] = '';
	  			$bSlash = false;
	  			foreach($elem as $tkey => $telem)
	  			{
	  				if ($bSlash)
	  				{
	  					$msg['warning'][$idx] .= "\\";
	  				}
	  				$msg['warning'][$idx] .= $telem['name'];
	  				$bSlash = true;
	  			}
	  			$idx++;
	  		}
	  	}
	  	else
	  	{
	  	  	$msg['link_msg'] = null;
	  		$msg['warning'] = null;
	  	}
 	}
	return $msg;
}


/*
  function:

  args :

  returns:

*/
function init_args($optionTransferCfg)
{
   	$args = new stdClass();
    $_REQUEST = strings_stripSlashes($_REQUEST);

    $args->tprojectID = $_SESSION['testprojectID'];
    $args->tprojectName = $_SESSION['testprojectName'];
    $args->userID = $_SESSION['userID'];

    $keys2loop=array('nodes_order' => null, 'tcaseSet' => null,
                     'target_position' => 'bottom', 'doAction' => '');
    foreach($keys2loop as $key => $value)
    {
       $args->$key = isset($_REQUEST[$key]) ? $_REQUEST[$key] : $value;
    }


    $args->tsuite_name = isset($_REQUEST['testsuiteName']) ? $_REQUEST['testsuiteName'] : null;
    $args->bSure = (isset($_REQUEST['sure']) && ($_REQUEST['sure'] == 'yes'));
    $rl_html_name = $optionTransferCfg->js_ot_name . "_newRight";
    $args->assigned_keyword_list = isset($_REQUEST[$rl_html_name])? $_REQUEST[$rl_html_name] : "";


    // integer values
    // BUGID 3406 - removed 'tplan_id' => 0
    $keys2loop=array('testsuiteID' => null, 'containerID' => null,
                     'objectID' => null, 'copyKeywords' => 0);
    foreach($keys2loop as $key => $value)
    {
       $args->$key = isset($_REQUEST[$key]) ? intval($_REQUEST[$key]) : $value;
    }

    if(is_null($args->containerID))
    {
    	$args->containerID = $args->tprojectID;
    }

    // BUGID 3669
	$args->refreshTree = isset($_SESSION['setting_refresh_tree_on_action']) ?
                         $_SESSION['setting_refresh_tree_on_action'] : 0;
    
    return $args;
}


/*
  function:

  args:

  returns:

*/
function writeCustomFieldsToDB(&$db,$tprojectID,$tsuiteID,&$hash)
{
    $ENABLED = 1;
    $NO_FILTERS = null;

    $cfield_mgr = new cfield_mgr($db);
    $cf_map = $cfield_mgr->get_linked_cfields_at_design($tprojectID,$ENABLED,
                                                        $NO_FILTERS,'testsuite');
    $cfield_mgr->design_values_to_db($hash,$tsuiteID,$cf_map);
}


/*
  function: deleteTestSuite

  args:

  returns: true -> refresh tree
           false -> do not refresh

*/
function deleteTestSuite(&$smartyObj,&$argsObj,&$tsuiteMgr,&$treeMgr,&$tcaseMgr,$level)
{
  	$feedback_msg = '';
	$system_message = '';
  	$testcase_cfg = config_get('testcase_cfg');
	$can_delete = 1;

	if($argsObj->bSure)
	{
	 	$tsuite = $tsuiteMgr->get_by_id($argsObj->objectID);
		$tsuiteMgr->delete_deep($argsObj->objectID);
		$tsuiteMgr->deleteKeywords($argsObj->objectID);
		$smartyObj->assign('objectName', $tsuite['name']);
		$doRefreshTree = true;
		$feedback_msg = 'ok';
		$smartyObj->assign('user_feedback',lang_get('testsuite_successfully_deleted'));
	}
	else
	{
	  	$doRefreshTree = false;

		// Get test cases present in this testsuite and all children
		$testcases = $tsuiteMgr->get_testcases_deep($argsObj->testsuiteID);

		$map_msg['warning'] = null;
		$map_msg['link_msg'] = null;
		$map_msg['delete_msg'] = null;

		if(is_null($testcases) || count($testcases) == 0)
		{
			$can_delete = 1;
		}
		else
		{
			$map_msg = build_del_testsuite_warning_msg($treeMgr,$tcaseMgr,$testcases,$argsObj->testsuiteID);
			if( in_array('linked_and_executed', (array)$map_msg['link_msg']) )
			{
				$can_delete = $testcase_cfg->can_delete_executed;
			}
		}

		$system_message = '';
		if(!$can_delete && !$testcase_cfg->can_delete_executed)  
		{
			$system_message = lang_get('system_blocks_tsuite_delete_due_to_exec_tc');
		}
	
		// prepare to show the delete confirmation page
		$smartyObj->assign('can_delete',$can_delete);
		$smartyObj->assign('objectID',$argsObj->testsuiteID);
		$smartyObj->assign('objectName', $argsObj->tsuite_name);
		$smartyObj->assign('delete_msg',$map_msg['delete_msg']);
		$smartyObj->assign('warning', $map_msg['warning']);
		$smartyObj->assign('link_msg', $map_msg['link_msg']);

	}
	$smartyObj->assign('system_message', $system_message);
	$smartyObj->assign('page_title', lang_get('delete') . " " . lang_get('container_title_' . $level));
 	$smartyObj->assign('sqlResult',$feedback_msg);

 	return $doRefreshTree;
}

/*
  function: addTestSuite

  args:

  returns: map with messages and status
  
  revision: 
  			20101012 - franciscom - BUGID 3890
  			when creating action on duplicate is setted to BLOCK without using 
  			config_get('action_on_duplicate_name').
  			This is because this config option has to be used ONLY when copying/moving not when creating.
  									
  			20091206 - franciscom - new items are created as last element of tree branch

*/
function addTestSuite(&$tsuiteMgr,&$argsObj,$container,&$hash)
{
    $new_order = null;

    // compute order
    $nt2exclude=array('testplan' => 'exclude_me','requirement_spec'=> 'exclude_me','requirement'=> 'exclude_me');
    $siblings = $tsuiteMgr->tree_manager->get_children($argsObj->containerID,$nt2exclude);
    if( !is_null($siblings) )
    {
    	$dummy = end($siblings);
    	$new_order = $dummy['node_order']+1;
    }
	$ret = $tsuiteMgr->create($argsObj->containerID,$container['container_name'],$container['details'],
	                         $new_order,config_get('check_names_for_duplicates'),'block');
		                         
    $op['messages']= array('msg' => $ret['msg'], 'user_feedback' => '');
    $op['status']=$ret['status_ok'];
	
	if($ret['status_ok'])
	{
		$op['messages']['user_feedback'] = lang_get('testsuite_created');
		if($op['messages']['msg'] != 'ok')
		{
			$op['messages']['user_feedback'] = $op['messages']['msg'];  
		}

		if(trim($argsObj->assigned_keyword_list) != "")
    	{
    		$tsuiteMgr->addKeywords($ret['id'],explode(",",$argsObj->assigned_keyword_list));
    	}
    	writeCustomFieldsToDB($tsuiteMgr->db,$argsObj->tprojectID,$ret['id'],$hash);
	}
	return $op;
}

/*
  function: moveTestSuiteViewer
            prepares smarty variables to display move testsuite viewer

  args:

  returns: -

*/
function  moveTestSuiteViewer(&$smartyObj,&$tprojectMgr,$argsObj)
{
	$testsuites = $tprojectMgr->gen_combo_test_suites($argsObj->tprojectID,
	                                                  array($argsObj->testsuiteID => 'exclude'));
	// Added the Test Project as the FIRST Container where is possible to copy
	$testsuites = array($argsObj->tprojectID => $argsObj->tprojectName) + $testsuites;

 	// original container (need to comment this better)
	$smartyObj->assign('old_containerID', $argsObj->tprojectID);
	$smartyObj->assign('containers', $testsuites);
	$smartyObj->assign('objectID', $argsObj->testsuiteID);
	$smartyObj->assign('object_name', $argsObj->tsuite_name);
	$smartyObj->assign('top_checked','checked=checked');
 	$smartyObj->assign('bottom_checked','');
}


/*
  function: reorderTestSuiteViewer
            prepares smarty variables to display reorder testsuite viewer

  args:

  returns: -

*/
function  reorderTestSuiteViewer(&$smartyObj,&$treeMgr,$argsObj)
{
	$level = null;
	$oid = is_null($argsObj->testsuiteID) ? $argsObj->containerID : $argsObj->testsuiteID;
	$children = $treeMgr->get_children($oid, array("testplan" => "exclude_me",
                                                 "requirement_spec"  => "exclude_me"));
  	$object_info = $treeMgr->get_node_hierarchy_info($oid);
  	$object_name = $object_info['name'];


	if (!sizeof($children))
		$children = null;

	$smartyObj->assign('arraySelect', $children);
	$smartyObj->assign('objectID', $oid);
	$smartyObj->assign('object_name', $object_name);

	if($oid == $argsObj->tprojectID)
  	{
    	$level = 'testproject';
    	$smartyObj->assign('level', $level);
    	$smartyObj->assign('page_title',lang_get('container_title_' . $level));
  }

  return $level;
}


/*
  function: updateTestSuite

  args:

  returns:

*/
function updateTestSuite(&$tsuiteMgr,&$argsObj,$container,&$hash)
{
	$msg = 'ok';
	$ret = $tsuiteMgr->update($argsObj->testsuiteID,$container['container_name'],$container['details']);
	if($ret['status_ok'])
	{
      $tsuiteMgr->deleteKeywords($argsObj->testsuiteID);
      if(trim($argsObj->assigned_keyword_list) != "")
      {
         $tsuiteMgr->addKeywords($argsObj->testsuiteID,explode(",",$argsObj->assigned_keyword_list));
      }
      writeCustomFieldsToDB($tsuiteMgr->db,$argsObj->tprojectID,$argsObj->testsuiteID,$hash);
  	}
  	else
	{
	    $msg = $ret['msg'];
	}
	return $msg;
}

/*
  function: copyTestSuite

  args:

  returns:

*/
function copyTestSuite(&$smartyObj,$template_dir,&$tsuiteMgr,$argsObj)
{
    $exclude_node_types=array('testplan' => 1, 'requirement' => 1, 'requirement_spec' => 1);
  	
  	$options = array();
	$options['check_duplicate_name'] = config_get('check_names_for_duplicates');
  	$options['action_on_duplicate_name'] = config_get('action_on_duplicate_name');
  	$options['copyKeywords'] = $argsObj->copyKeywords;

  	$op=$tsuiteMgr->copy_to($argsObj->objectID, $argsObj->containerID, $argsObj->userID,$options);
	if( $op['status_ok'] )
	{
	    $tsuiteMgr->tree_manager->change_child_order($argsObj->containerID,$op['id'],
	                                                 $argsObj->target_position,$exclude_node_types);
	}
	
	$guiObj = new stdClass();
	$guiObj->attachments = getAttachmentInfosFrom($tsuiteMgr,$argsObj->objectID);
	$guiObj->id = $argsObj->objectID;
	
	$tsuiteMgr->show($smartyObj,$guiObj,$template_dir,$argsObj->objectID,null,'ok');
}

/*
  function: moveTestSuite

  args:

  returns:

*/
function moveTestSuite(&$smartyObj,$template_dir,&$tprojectMgr,$argsObj)
{
	$exclude_node_types=array('testplan' => 1, 'requirement' => 1, 'requirement_spec' => 1);

	$tprojectMgr->tree_manager->change_parent($argsObj->objectID,$argsObj->containerID);
  	$tprojectMgr->tree_manager->change_child_order($argsObj->containerID,$argsObj->objectID,
                                                   $argsObj->target_position,$exclude_node_types);

	$guiObj = new stdClass();
	$guiObj->id = $argsObj->tprojectID;

  	$tprojectMgr->show($smartyObj,$guiObj,$template_dir,$argsObj->tprojectID,null,'ok');
}


/*
  function: initializeOptionTransfer

  args:

  returns: option transfer configuration

*/
function initializeOptionTransfer(&$tprojectMgr,&$tsuiteMgr,$argsObj,$doAction)
{
    $opt_cfg = opt_transf_empty_cfg();
    $opt_cfg->js_ot_name='ot';
    $opt_cfg->global_lbl='';
    $opt_cfg->from->lbl=lang_get('available_kword');
    $opt_cfg->from->map = $tprojectMgr->get_keywords_map($argsObj->tprojectID);
    $opt_cfg->to->lbl=lang_get('assigned_kword');

    if($doAction=='edit_testsuite')
    {
      $opt_cfg->to->map=$tsuiteMgr->get_keywords_map($argsObj->testsuiteID," ORDER BY keyword ASC ");
    }
    return $opt_cfg;
}


/*
  function: moveTestCasesViewer
            prepares smarty variables to display move testcases viewer

  args:

  returns: -

*/
function moveTestCasesViewer(&$dbHandler,&$smartyObj,&$tprojectMgr,&$treeMgr,$argsObj,$feedback='')
{
	$tables = $tprojectMgr->getDBTables(array('nodes_hierarchy','node_types','tcversions'));
	$testcase_cfg = config_get('testcase_cfg');
	$glue = $testcase_cfg->glue_character;
	
	$containerID = isset($argsObj->testsuiteID) ? $argsObj->testsuiteID : $argsObj->objectID;
	$containerName = $argsObj->tsuite_name;
	if( is_null($containerName) )
	{
		$dummy = $treeMgr->get_node_hierarchy_info($argsObj->objectID);
		$containerName = $dummy['name'];
	}
	
	
  	// 20081225 - franciscom have discovered that exclude selected testsuite branch is not good
  	//            when you want to move lots of testcases from one testsuite to it's children
  	//            testsuites. (in this situation tree drag & drop is not ergonomic).
  	$testsuites = $tprojectMgr->gen_combo_test_suites($argsObj->tprojectID);	                                                  
	$tcasePrefix = $tprojectMgr->getTestCasePrefix($argsObj->tprojectID) . $glue;

	// 20081225 - franciscom
	// While testing with PostGres have found this behaivour:
	// No matter is UPPER CASE has used on field aliases, keys on hash returned by
	// ADODB are lower case.
	// Accessing this keys on Smarty template using UPPER CASE fails.
	// Solution: have changed case on Smarty to lower case.
	//         
	$sql = "SELECT NHA.id AS tcid, NHA.name AS tcname, NHA.node_order AS tcorder," .
	       " MAX(TCV.version) AS tclastversion, TCV.tc_external_id AS tcexternalid" .
	       " FROM {$tables['nodes_hierarchy']} NHA, {$tables['nodes_hierarchy']}  NHB, " .
	       " {$tables['node_types']} NT, {$tables['tcversions']}  TCV " .
	       " WHERE NHB.parent_id=NHA.id " .
	       " AND TCV.id=NHB.id AND NHA.node_type_id = NT.id AND NT.description='testcase'" .
	       " AND NHA.parent_id={$containerID} " .
	       " GROUP BY NHA.id,NHA.name,NHA.node_order,TCV.tc_external_id " .
	       " ORDER BY TCORDER,TCNAME";

  	$children = $dbHandler->get_recordset($sql);
    
 	// check if operation can be done
	$user_feedback = $feedback;
	if(!is_null($children) && (sizeof($children) > 0) && sizeof($testsuites))
	{
	    $op_ok = true;
	}
	else
	{
	    $children = null;
	    $op_ok = false;
	    $user_feedback = lang_get('no_testcases_available_or_tsuite');
	}

	$smartyObj->assign('op_ok', $op_ok);
	$smartyObj->assign('user_feedback', $user_feedback);
	$smartyObj->assign('tcprefix', $tcasePrefix);
	$smartyObj->assign('testcases', $children);
	$smartyObj->assign('old_containerID', $argsObj->tprojectID); //<<<<-- check if is needed
	$smartyObj->assign('containers', $testsuites);
	$smartyObj->assign('objectID', $containerID);
	$smartyObj->assign('object_name', $containerName);
	$smartyObj->assign('top_checked','checked=checked');
	$smartyObj->assign('bottom_checked','');
}


/*
  function: copyTestCases
            copy a set of choosen test cases.

  args:

  returns: -

*/
function copyTestCases(&$smartyObj,$template_dir,&$tsuiteMgr,&$tcaseMgr,$argsObj)
{
	$op = array('refreshTree' => false, 'userfeedback' => '');
    if( ($qty=sizeof($argsObj->tcaseSet)) > 0)
    {
		$msg_id = $qty == 1 ? 'one_testcase_copied' : 'testcase_set_copied';
   		$op['userfeedback'] = sprintf(lang_get($msg_id),$qty);

        $check_names_for_duplicates_cfg = config_get('check_names_for_duplicates');
        $action_on_duplicate_name_cfg = config_get('action_on_duplicate_name');

        foreach($argsObj->tcaseSet as $key => $tcaseid)
        {
            $copy_op = $tcaseMgr->copy_to($tcaseid, $argsObj->containerID, $argsObj->userID,
	                                      $argsObj->copyKeywords,$check_names_for_duplicates_cfg,
	                    	              $action_on_duplicate_name_cfg);
        }
        
        $guiObj = new stdClass();
   		$guiObj->attachments = getAttachmentInfosFrom($tsuiteMgr,$argsObj->objectID);
		$guiObj->id = $argsObj->objectID;
		$guiObj->refreshTree = true;
        // $tsuiteMgr->show($smartyObj,$guiObj,$template_dir,$argsObj->objectID);
    	$op['refreshTree'] = true;
    }
    return $op;
}


/*
  function: moveTestCases
            move a set of choosen test cases.

  args:

  returns: -

*/
function moveTestCases(&$smartyObj,$template_dir,&$tsuiteMgr,&$treeMgr,$argsObj)
{
    if(sizeof($argsObj->tcaseSet) > 0)
    {
        $status_ok = $treeMgr->change_parent($argsObj->tcaseSet,$argsObj->containerID);
        $user_feedback= $status_ok ? '' : lang_get('move_testcases_failed');

        // objectID - original container
        $guiObj = new stdClass();
   		$guiObj->attachments = getAttachmentInfosFrom($tsuiteMgr,$argsObj->objectID);
		$guiObj->id = $argsObj->objectID;
		// BUGID 3669
		$guiObj->refreshTree = true;
        $tsuiteMgr->show($smartyObj,$guiObj,$template_dir,$argsObj->objectID,null,$user_feedback);
    }
}


/**
 * initWebEditors
 *
 */
 function initWebEditors($webEditorKeys,$containerType,$editorCfg)
 {
    switch($containerType)
    {
        case 'testsuite':
            // $cfg=config_get('testsuite_template');
            $itemTemplateKey='testsuite_template';
        break;    
        
        default:
            //$cfg=null;
            $itemTemplateKey=null;
        break;    
    }
    
    
    $htmlNames = $webEditorKeys[$containerType];
    $oWebEditor = array();
    foreach ($htmlNames as $key)
    {
      $oWebEditor[$key] = web_editor($key,$_SESSION['basehref'],$editorCfg);
    }
    return array($oWebEditor,$htmlNames,$itemTemplateKey);
 }
 
 
 
 
/*
  function: deleteTestCasesViewer
            prepares smarty variables to display move testcases viewer

  args:

  returns: -

	@internal revisions
	20110402 - franciscom - BUGID 4322: New Option to block delete of executed test cases.	
*/
function deleteTestCasesViewer(&$dbHandler,&$smartyObj,&$tprojectMgr,&$treeMgr,&$tsuiteMgr,
							   &$tcaseMgr,$argsObj,$feedback = null)
{

	$guiObj = new stdClass();
    $guiObj->main_descr = lang_get('delete_testcases');
	$guiObj->system_message = '';
    

	$tables = $tprojectMgr->getDBTables(array('nodes_hierarchy','node_types','tcversions'));
	$testcase_cfg = config_get('testcase_cfg');
	$glue = $testcase_cfg->glue_character;

	$containerID = isset($argsObj->testsuiteID) ? $argsObj->testsuiteID : $argsObj->objectID;
	$containerName = $argsObj->tsuite_name;
	if( is_null($containerName) )
	{
		$dummy = $treeMgr->get_node_hierarchy_info($argsObj->objectID);
		$containerName = $dummy['name'];
	}

	$guiObj->testCaseSet = $tsuiteMgr->get_children_testcases($containerID);
	$guiObj->exec_status_quo = null;
	$tcasePrefix = $tprojectMgr->getTestCasePrefix($argsObj->tprojectID);
	$hasExecutedTC = false;

	if( !is_null($guiObj->testCaseSet) && count($guiObj->testCaseSet) > 0)
	{
		foreach($guiObj->testCaseSet as &$child)
		{
			$external = $tcaseMgr->getExternalID($child['id'],null,$tcasePrefix);
			$child['external_id'] = $external[0];
			
			// key level 1 : Test Case Version ID
			// key level 2 : Test Plan  ID
			// key level 3 : Platform ID
			$getOptions = array('addExecIndicator' => true);
			$dummy = $tcaseMgr->get_exec_status($child['id'],null,$getOptions);	
			$child['draw_check'] = $testcase_cfg->can_delete_executed || (!$dummy['executed']);

			$hasExecutedTC = $hasExecutedTC || $dummy['executed'];
			unset($dummy['executed']);
			$guiObj->exec_status_quo[] = $dummy; 
		} 
	}
	// Need to understand if platform column has to be displayed on GUI
	if( !is_null($guiObj->exec_status_quo) )             
	{
		// key level 1 : Test Case Version ID
		// key level 2 : Test Plan  ID
		// key level 3 : Platform ID
		
		$itemSet = array_keys($guiObj->exec_status_quo);
		foreach($itemSet as $mainKey)
		{
			$guiObj->display_platform[$mainKey] = false;
			if(!is_null($guiObj->exec_status_quo[$mainKey]) )
			{
				$versionSet = array_keys($guiObj->exec_status_quo[$mainKey]);
				$stop = false;
				foreach($versionSet as $version_id)
				{
					$tplanSet = array_keys($guiObj->exec_status_quo[$mainKey][$version_id]);
					foreach($tplanSet as $tplan_id)
					{
						if( ($guiObj->display_platform[$mainKey] = !isset($guiObj->exec_status_quo[$mainKey][$version_id][$tplan_id][0])) )
						{
							$stop = true;
							break;
						}
					}
					if($stop)
					{
						break;
					}
				}
			}
		}	
	}
 	// check if operation can be done
	$guiObj->user_feedback = $feedback;
	if(!is_null($guiObj->testCaseSet) && (sizeof($guiObj->testCaseSet) > 0) )
	{
	    $guiObj->op_ok = true;
	    $guiObj->user_feedback = '';
	}
	else
	{
	    $guiObj->children = null;
	    $guiObj->op_ok = false;
	    $guiObj->user_feedback = is_null($guiObj->user_feedback) ? lang_get('no_testcases_available') : $guiObj->user_feedback;
	}

	if(!$testcase_cfg->can_delete_executed && $hasExecutedTC)  
	{
		$guiObj->system_message = lang_get('system_blocks_delete_executed_tc');
	}

	$guiObj->objectID = $containerID;
	$guiObj->object_name = $containerName;
	$guiObj->refreshTree = $argsObj->refreshTree;
	
	$smartyObj->assign('gui', $guiObj);
}


/*
  function: doDeleteTestCasesViewer
            prepares smarty variables to display move testcases viewer

  args:

  returns: -

*/
function doDeleteTestCases(&$dbHandler,$tcaseSet,&$tcaseMgr)
{
     if( count($tcaseSet) > 0 )
     {
     	foreach($tcaseSet as $victim)
     	{
     		$tcaseMgr->delete($victim);
        }
     }
}


/**
 * 
 *
 */
function reorderTestCasesByCriteria($argsObj,&$tsuiteMgr,&$treeMgr,$sortCriteria)
{
    $pfn = ($sortCriteria == 'NAME') ? 'reorderTestCasesDictionary' : 'reorderTestCasesByExtID';
	$pfn($argsObj,$tsuiteMgr,$treeMgr);
}


/**
 * 
 *
 */
function reorderTestCasesDictionary($argsObj,&$tsuiteMgr,&$treeMgr)
{
	$tcaseSet = (array)$tsuiteMgr->get_children_testcases($argsObj->testsuiteID,'simple');
	if( ($loop2do = count($tcaseSet)) > 0 )
	{
		for($idx=0; $idx < $loop2do; $idx++)
		{
			$a2sort[$tcaseSet[$idx]['id']] = $tcaseSet[$idx]['name'];
		}
		natsort($a2sort);
		$a2sort = array_keys($a2sort);
		$treeMgr->change_order_bulk($a2sort);
	}
}


/**
 * 
 *
 */
function reorderTestCasesByExtID($argsObj,&$tsuiteMgr,&$treeMgr)
{
	$tables = $tsuiteMgr->getDBTables(array('nodes_hierarchy','testsuites','tcversions'));

	$sql = " SELECT DISTINCT NHTC.id,TCV.tc_external_id " .
		   " FROM {$tables['nodes_hierarchy']} NHTC " .
		   " JOIN {$tables['nodes_hierarchy']} NHTCV ON NHTCV.parent_id = NHTC.id " .
		   " JOIN {$tables['tcversions']} TCV ON TCV.id = NHTCV.id " .
		   " JOIN {$tables['testsuites']} TS ON NHTC.parent_id = TS.id " .
		   " WHERE TS.id = " . intval($argsObj->testsuiteID) . 
		   " ORDER BY tc_external_id ASC";

	$tcaseSet = $tsuiteMgr->db->fetchColumnsIntoMap($sql,'tc_external_id','id');
	$treeMgr->change_order_bulk($tcaseSet);
}



/**
 * 
 *
 */
function reorderTestSuitesDictionary($args,$treeMgr,$parent_id)
{
	$exclude_node_types = array('testplan' => 1, 'requirement' => 1, 'testcase' => 1, 'requirement_spec' => 1);
	$itemSet = (array)$treeMgr->get_children($parent_id,$exclude_node_types);
	if( ($loop2do = count($itemSet)) > 0 )
	{
		for($idx=0; $idx < $loop2do; $idx++)
		{
			$a2sort[$itemSet[$idx]['id']] = $itemSet[$idx]['name'];
		}
		natsort($a2sort);
		$a2sort = array_keys($a2sort);
		$treeMgr->change_order_bulk($a2sort);
	}
}


?>