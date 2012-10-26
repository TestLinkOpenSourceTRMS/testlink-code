<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource	testSuiteEdit.php
 * @package 	  TestLink
 * @author 		  Martin Havlat
 * @copyright 	2005-2012, TestLink community 
 * @link 		    http://www.teamst.org/index.php
 *
 * @internal revisions
 */
require_once("../../config.inc.php");
require_once("common.php");
require_once("web_editor.php");
$editorCfg = getWebEditorCfg('design');
require_once(require_web_editor($editorCfg['type']));

echo __FILE__;

testlinkInitPage($db);
$tree_mgr = new tree($db);
$tproject_mgr = new testproject($db);
$tsuite_mgr = new testsuite($db);
$tcase_mgr = new testcase($db);

$assign_gui = true;
list($args,$gui) = initializeEnv($db,$tree_mgr);
$gui->editorType = $editorCfg['type'];

$keywordSet = array('testproject' => $tproject_mgr->get_keywords_map($args->tproject_id),
                    'testsuite' => null);

if($args->action=='edit_testsuite')
{
  $keywordSet['testsuite'] = $tsuite_mgr->get_keywords_map($args->testsuiteID," ORDER BY keyword ASC ");
}


new dBug($args->action);

$gui_cfg = config_get('gui');
$smarty = new TLSmarty();
$smarty->tlTemplateCfg = templateConfiguration();
switch($args->action)
{
	case 'edit_testsuite':
	case 'new_testsuite':
	  $gui->refreshTree = false;
    renderTestSuiteForManagement($smarty,$args,$gui,$tsuite_mgr,$keywordSet);
    exit();
	break;

  case 'add_testsuite':
	  $messages = null;
	  $op['status'] = 0;
		if ($args->nameIsOK)
		{
	    $op = addTestSuite($tsuite_mgr,$args,$_REQUEST);
	    $messages = array('result_msg' => $op['messages']['msg'], 
	                      'user_feedback' => $op['messages']['user_feedback']);
	  }
    
    // $userInput is used to maintain data filled by user if there is
    // a problem with test suite name.
    $userInput = $op['status'] ? null : $_REQUEST; 
    if($op['status'])
    {
      $args->assigned_keyword_list = "";
      $gui->refreshTree = $args->refreshTree;
    } 
    renderTestSuiteForManagement($smarty,$args,$gui,$tsuite_mgr,$keywordSet,$userInput);
    exit();
  break;

  case 'delete_testsuite':
   	deleteTestSuite($args,$gui,$tsuite_mgr,$tcase_mgr);
		$smarty->assign('gui', $gui);
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
    moveTestSuite($smarty,$tproject_mgr,$args,$gui);
  break;

  case 'do_copy':
    copyTestSuite($smarty,$tsuite_mgr,$args,$gui);
  break;

  case 'update_testsuite':
    if ($args->nameIsOK)
	  {
      $op = updateTestSuite($tsuite_mgr,$args,$_REQUEST);
    }

    if($op['status_ok'])
    {
  	  $gui->id = $args->testsuiteID;
	  	$gui->page_title = lang_get('container_title_testsuite');
		  $gui->refreshTree = $args->refreshTree;
     	$identity = new stdClass();
     	$identity->id = $args->testsuiteID;
     	$identity->tproject_id = $args->tproject_id;
     	$tsuite_mgr->show($smarty,$gui,$identity);
    }
    else
    {
      // $userInput is used to maintain data filled by user if there is
      // a problem with test suite name.
      $userInput = $_REQUEST; 
      if( $gui->midAirCollision = ($op['reason'] == 'midAirCollision') )
      {
        $foe = new tlUser($op['more']['updater_id']);
			  $foe->readFromDB($db);
        $gui->midAirCollisionMsg['main'] = sprintf(lang_get('collision_detected_some_one_else'),
                                                   $op['more']['modification_ts'],$foe->login,$foe->emailAddress);
        $gui->midAirCollisionMsg['details'] = sprintf(lang_get('collision_detected_choices'),$foe->login);
      }    
            
      renderTestSuiteForManagement($smarty,$args,$gui,$tsuite_mgr,$keywordSet,$userInput);
      exit();
    }
  break;

  case 'do_move_tcase_set':
    moveTestCases($smarty,$template_dir,$tsuite_mgr,$tree_mgr,$args);
  break;

  case 'do_copy_tcase_set':
    $op = copyTestCases($smarty,$template_dir,$tsuite_mgr,$tcase_mgr,$args);
    $gui->refreshTree = $op['refreshTree'] && $args->refreshTree;
    moveTestCasesViewer($db,$smarty,$tproject_mgr,$tree_mgr,$args,$op['userfeedback']);
  break;


  case 'delete_testcases':
    $args->refreshTree = false;
    $assign_gui = false;
    deleteTestCasesViewer($db,$smarty,$tproject_mgr,$tree_mgr,$tsuite_mgr,$tcase_mgr,$args);
  break;

  case 'do_delete_testcases':
    $args->refreshTree = true;
    $assign_gui = false;
    doDeleteTestCases($db,$args->tcaseSet,$tcase_mgr);
    deleteTestCasesViewer($db,$smarty,$tproject_mgr,$tree_mgr,$tsuite_mgr,$tcase_mgr,$args,
    				              lang_get('all_testcases_have_been_deleted'));
  break;

	case 'reorder_testcases': 
    reorderTestCasesByCriteria($args,$tsuite_mgr,$tree_mgr,$sortCriteria);
    $gui->refreshTree = true;
	  $gui->id = $args->testsuiteID;
		$gui->page_title = lang_get('container_title_testsuite');
    $identity = new stdClass();
    $identity->id = $args->testsuiteID;
    $identity->tproject_id = $args->tproject_id;
    $tsuite_mgr->show($smarty,$gui,$identity);
  break;
	

	case 'reorder_testsuites_alpha': 
    reorderTestSuitesDictionary($args,$tree_mgr,$args->testsuiteID);
		$gui->refreshTree = true;
	  $gui->id = $args->testsuiteID;
	  $gui->page_title = lang_get('container_title_testsuite');

    $identity = new stdClass();
    $identity->id = $args->testsuiteID;
    $identity->tproject_id = $args->tproject_id;
    $tsuite_mgr->show($smarty,$gui,$identity);
  break;

	case 'reorder_testproject_testsuites_alpha':
    reorderTestSuitesDictionary($args,$tree_mgr,$args->tproject_id);
		$gui->refreshTree = true;
	  $gui->id = $args->tproject_id;
		$gui->page_title = lang_get('container_title_testsuite');

		$identity = new stdClass();
		$identity->id = $args->tproject_id;
    $tproject_mgr->show($smarty,$guiObj,$identity);
    break;

    default:
    	trigger_error(__FILE__ . " - No correct GET/POST data", E_USER_ERROR);
    break;
}

if($gui->tpl)
{
	if( $assign_gui )
	{
    	$smarty->assign('gui', $gui);
  }
	$smarty->display($smarty->tlTemplateCfg->template_dir . $gui->tpl);
}

/*
  function:

  args :

  returns:

*/
function build_del_testsuite_warning_msg(&$tcase_mgr,&$testcases,$tsuite_id)
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
   	$status_warning = array('linked_and_executed' => 1,'linked_but_not_executed' => 1,'no_links' => 0);
		$delete_notice = array(	'linked_and_executed' => lang_get('delete_notice'),
    	                    	'linked_but_not_executed' => '','no_links' => '');

		$getOptions = array('addExecIndicator' => true);
  	foreach($testcases as $the_key => $elem)
  	{
  		$verbose[] = $tcase_mgr->tree_mgr->get_path($elem['id'],$tsuite_id);
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
	  		$addSlash = false;
	  		foreach($elem as $tkey => $telem)
	  		{
	  		  if($addSlash)
	  			{
	  			  $msg['warning'][$idx] .= "\\";
	  			}
	  			$msg['warning'][$idx] .= $telem['name'];
	  			$addSlash = true;
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
function init_args(&$treeMgr)
{
  $args = new stdClass();
  $_REQUEST = strings_stripSlashes($_REQUEST);
 
  new dBug($_REQUEST);
 
 
	$args->containerType = isset($_REQUEST['containerType']) ?  $_REQUEST['containerType'] :'testsuite';
	$args->details = isset($_REQUEST['details']) ?  $_REQUEST['details'] : '';
	$args->midAirCollisionTimeStamp = isset($_REQUEST['midAirCollisionTimeStamp']) ?  $_REQUEST['midAirCollisionTimeStamp'] : '';
  $args->userID = $_SESSION['userID'];
  $args->userObj = $_SESSION['currentUser'];

  $args->tproject_name = '';	
  $args->tproject_id = isset($_REQUEST['tproject_id']) ? intval($_REQUEST['tproject_id']) : 0;
  if($args->tproject_id)
  {
  	$dummy = $treeMgr->get_node_hierarchy_info($args->tproject_id);
  	$args->tproject_name = $dummy['name'];
  }
  
  $args->parentID = isset($_REQUEST['parentID']) ? intval($_REQUEST['parentID']) : 0;
  if($args->parentID == 0 && $args->containerType == 'testproject')
  {
    // we are trying to create a TOP Level test suite
    $args->parentID = $args->tproject_id;
  }  
  
  
  $keys2loop=array('nodes_order' => null, 'tcaseSet' => null,'target_position' => 'bottom', 'doAction' => '');
  foreach($keys2loop as $key => $value)
  {
     $args->$key = isset($_REQUEST[$key]) ? $_REQUEST[$key] : $value;
  }
 
  $args->testsuiteName = isset($_REQUEST['testsuiteName']) ? $_REQUEST['testsuiteName'] : null;
  $args->doIt = isset($_REQUEST['doIt']);

  $args->assigned_keyword_list = isset($_REQUEST['assigned_keyword_list'])? $_REQUEST['assigned_keyword_list'] : "";
  
  // integer values
  $keys2loop=array('testsuiteID' => null, 'copyKeywords' => 0);
  foreach($keys2loop as $key => $value)
  {
     $args->$key = isset($_REQUEST[$key]) ? intval($_REQUEST[$key]) : $value;
  }
  
  // hmmm IMHO depends on action
  // Would like to remove
  //if(is_null($args->containerID))
  //{
  //	$args->containerID = $args->tproject_id;
  //}
  if( isset($_REQUEST['testsuiteName']) )
  {
    $args->nameIsOK = true;
    $args->msg = '';
    $args->testsuiteName = $args->name = trim($_REQUEST['testsuiteName']);
    if( !check_string($args->testsuiteName,config_get('ereg_forbidden')) )
    {
      $args->msg = lang_get('string_contains_bad_chars');
      $args->nameIsOK = false;
    }
    
    if($args->nameIsOK && $args->testsuiteName == '')
    {
      $args->msg = lang_get('warning_empty_com_name');
      $args->nameIsOK = false;
    } 
  }
  $args->refreshTree = testproject::getUserChoice($args->tproject_id,array('tcaseTreeRefreshOnAction','edit_mode'));
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

*/
function deleteTestSuite(&$argsObj,&$guiObj,&$tsuiteMgr,&$tcaseMgr)
{
	$guiObj->refreshTree = false;
	$guiObj->can_delete = 1;
  $guiObj->delete_msg = $guiObj->warning_msg = $guiObj->link_msg = null;
	$guiObj->system_msg = $guiObj->feedback_msg = $guiObj->user_feedback = $guiObj->last_chance_msg = '';
	

	$guiObj->testsuiteID = $argsObj->testsuiteID;
	$guiObj->testsuiteName = $argsObj->testsuiteName;

	if($argsObj->doIt)
	{
	 	$tsuite = $tsuiteMgr->getNode($argsObj->testsuiteID);
    $guiObj->testsuiteName  = $tsuite['name'];
		$guiObj->user_feedback = sprintf(lang_get('testsuite_successfully_deleted'),$guiObj->testsuiteName);
		$guiObj->refreshTree = true;
		$guiObj->feedback_msg = 'ok';

		$tsuiteMgr->delete_deep($argsObj->testsuiteID);
		$tsuiteMgr->deleteKeywords($argsObj->testsuiteID);
	}
	else
	{
    $testcase_cfg = config_get('testcase_cfg');
    $guiObj->last_chance_msg = sprintf(lang_get('question_del_testsuite'),$argsObj->testsuiteName);
    
		// Get test cases present in this testsuite and all children
		$testcases = $tsuiteMgr->get_testcases_deep($argsObj->testsuiteID);
  	if(is_null($testcases) || count($testcases) == 0)
		{
			$guiObj->can_delete = 1;
		}
		else
		{
			$msgSet = build_del_testsuite_warning_msg($tcaseMgr,$testcases,$argsObj->testsuiteID);
			if( in_array('linked_and_executed', (array)$msgSet['link_msg']) )
			{
				$guiObj->can_delete = $testcase_cfg->can_delete_executed;
			}
			foreach($msgSet as $key)
			{
			  $guiObj->$key = $msgSet[$key];
			}
		}
		if(!$guiObj->can_delete && !$testcase_cfg->can_delete_executed)  
		{
			$guiObj->system_msg = lang_get('system_blocks_tsuite_delete_due_to_exec_tc');
		}
	}
 	// $guiObj->sqlResult = $guiObj->feedback_msg;
	$guiObj->page_title = lang_get('delete') . " " . lang_get('testsuite') . ' : ' . $guiObj->testsuiteName;


}

/*
  function: addTestSuite

  args:

  returns: map with messages and status
  
  revision: 

*/
function addTestSuite(&$tsuiteMgr,&$argsObj,&$hash)
{
    $item = new stdClass();
	  // $item->parent_id = $argsObj->containerID;
	  $item->parent_id = $argsObj->parentID;
	  $item->name = $argsObj->testsuiteName;
	  $item->details = $argsObj->details;
	  $item->check_duplicate_name = config_get('check_names_for_duplicates');
	  $item->action_on_duplicate_name = 'block';
    $item->userID = intval($_SESSION['currentUser']->dbID);
	  $item->order = null;

    // compute order
    $nt2exclude=array('testplan' => 'exclude_me','requirement_spec'=> 'exclude_me','requirement'=> 'exclude_me');
    $siblings = $tsuiteMgr->tree_manager->get_children($argsObj->parentID,$nt2exclude);
    if( !is_null($siblings) )
    {
    	$dummy = end($siblings);
    	$item->order = $dummy['node_order']+1;
    }
   
	  $ret = $tsuiteMgr->create($item);
	  new dBug($ret);
	  
    $op['messages']= array('msg' => $ret['msg'], 'user_feedback' => '');
    $op['status'] = $ret['status_ok'];
	
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
    	writeCustomFieldsToDB($tsuiteMgr->db,$argsObj->tproject_id,$ret['id'],$hash);
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
	$testsuites = $tprojectMgr->gen_combo_test_suites($argsObj->tproject_id,
	                                                  array($argsObj->testsuiteID => 'exclude'));
	// Added the Test Project as the FIRST Container where is possible to copy
	$testsuites = array($argsObj->tproject_id => $argsObj->tproject_name) + $testsuites;

 	// original container (need to comment this better)
 	// $smartyObj->assign('gui',$guiObj);
	$smartyObj->assign('old_containerID', $argsObj->tproject_id);
	$smartyObj->assign('containers', $testsuites);
	$smartyObj->assign('objectID', $argsObj->testsuiteID);
	$smartyObj->assign('object_name', $argsObj->testsuiteName);
	$smartyObj->assign('top_checked','checked=checked');
 	$smartyObj->assign('bottom_checked','');
}


/*
  function: reorderTestSuiteViewer
            prepares smarty variables to display reorder testsuite viewer

  args:

  returns: -

*/
function reorderTestSuiteViewer(&$smartyObj,&$treeMgr,$argsObj)
{
	$containerType = null;
	$oid = is_null($argsObj->testsuiteID) ? $argsObj->parentID : $argsObj->testsuiteID;
	$children = $treeMgr->get_children($oid, array("testplan" => "exclude_me",
                                                 "requirement_spec"  => "exclude_me"));
	if (!sizeof($children))
	{
		$children = null;
  }

  $object_info = $treeMgr->get_node_hierarchy_info($oid);
  $object_name = $object_info['name'];
  
	$smartyObj->assign('arraySelect', $children);
	$smartyObj->assign('objectID', $oid);
	$smartyObj->assign('object_name', $object_name);

	if($oid == $argsObj->tproject_id)
  {
    	$containerType = 'testproject';
    	$smartyObj->assign('level', $containerType);
    	$smartyObj->assign('page_title',lang_get('container_title_' . $containerType));
  }

  return $containerType;
}


/*
  function: updateTestSuite

  args:

  returns:

*/
function updateTestSuite(&$tsuiteMgr,&$argsObj,&$hash)
{
  new dBug($_REQUEST);
  new dBug($argsObj);
	$item = new stdClass();
	$item->id = $argsObj->testsuiteID;
	$item->name = $argsObj->testsuiteName;
	$item->details = $argsObj->details;
	$item->parent_id = null;
	$item->order = null;
	$item->userID = intval($_SESSION['currentUser']->dbID); 
	$item->modification_ts = $argsObj->midAirCollisionTimeStamp;

	$ret = $tsuiteMgr->update($item);
	if($ret['status_ok'])
	{
      $tsuiteMgr->deleteKeywords($argsObj->testsuiteID);
      if(trim($argsObj->assigned_keyword_list) != "")
      {
         $tsuiteMgr->addKeywords($argsObj->testsuiteID,explode(",",$argsObj->assigned_keyword_list));
      }
      writeCustomFieldsToDB($tsuiteMgr->db,$argsObj->tproject_id,$argsObj->testsuiteID,$hash);
  }
	return $ret;
}


/*
  function: copyTestSuite

  args:

  returns:

*/
function copyTestSuite(&$smartyObj,&$tsuiteMgr,$argsObj,$guiObj)
{
  $exclude_node_types=array('testplan' => 1, 'requirement' => 1, 'requirement_spec' => 1);
  	
  $options = array();
	$options['check_duplicate_name'] = config_get('check_names_for_duplicates');
  $options['action_on_duplicate_name'] = config_get('action_on_duplicate_name');
  $options['copyKeywords'] = $argsObj->copyKeywords;

  $op=$tsuiteMgr->copy_to($argsObj->objectID, $argsObj->parentID, $argsObj->userID,$options);
	if( $op['status_ok'] )
	{
    $tsuiteMgr->tree_manager->change_child_order($argsObj->parentID,$op['id'],
	                                               $argsObj->target_position,$exclude_node_types);
	}
	
	$guiObj->attachments = getAttachmentInfosFrom($tsuiteMgr,$argsObj->objectID);
	$guiObj->id = $argsObj->objectID;

	$identity = new stdClass();
	$identity->tproject_id = $argsObj->tproject_id;
	$identity->id = $argsObj->objectID;
	$tsuiteMgr->show($smartyObj,$guiObj,$identity);
}

/*
  function: moveTestSuite

  args:

  returns:

*/
function moveTestSuite(&$smartyObj,&$tprojectMgr,$argsObj,$guiObj)
{
	$exclude_node_types=array('testplan' => 1, 'requirement' => 1, 'requirement_spec' => 1);

	$tprojectMgr->tree_manager->change_parent($argsObj->objectID,$argsObj->parentID);
  $tprojectMgr->tree_manager->change_child_order($argsObj->parentID,$argsObj->objectID,
                                                 $argsObj->target_position,$exclude_node_types);

	
	$identity = new stdClass();
	$identity->id = $guiObj->id = $argsObj->tproject_id;
  // $tprojectMgr->show($smartyObj,$guiObj,$argsObj->tproject_id,null,'ok');
  $tprojectMgr->show($smartyObj,$guiObj,$identity);
}


/*
  function: initializeOptionTransfer

  args:

  returns: option transfer configuration

*/
function initializeOptionTransfer(&$tprojectMgr,&$tsuiteMgr,$argsObj,$doAction)
{
    $opt_cfg = opt_transf_empty_cfg();
    $opt_cfg->global_lbl='';
    $opt_cfg->from->lbl = lang_get('available_kword');
    $opt_cfg->to->lbl = lang_get('assigned_kword');
    $opt_cfg->from->map = $tprojectMgr->get_keywords_map($argsObj->tproject_id);

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
	$containerName = $argsObj->testsuiteName;
	if( is_null($containerName) )
	{
		$dummy = $treeMgr->get_node_hierarchy_info($argsObj->objectID);
		$containerName = $dummy['name'];
	}
	
	
  	// 20081225 - franciscom have discovered that exclude selected testsuite branch is not good
  	//            when you want to move lots of testcases from one testsuite to it's children
  	//            testsuites. (in this situation tree drag & drop is not ergonomic).
  	$testsuites = $tprojectMgr->gen_combo_test_suites($argsObj->tproject_id);	                                                  
	$tcasePrefix = $tprojectMgr->getTestCasePrefix($argsObj->tproject_id) . $glue;

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
	$smartyObj->assign('old_containerID', $argsObj->tproject_id); //<<<<-- check if is needed
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
            $copy_op = $tcaseMgr->copy_to($tcaseid, $argsObj->parentID, $argsObj->userID,
	                                      $argsObj->copyKeywords,$check_names_for_duplicates_cfg,
	                    	              $action_on_duplicate_name_cfg);
        }
        
        $guiObj = new stdClass();
   		$guiObj->attachments = getAttachmentInfosFrom($tsuiteMgr,$argsObj->objectID);
		$guiObj->id = $argsObj->objectID;
		$guiObj->refreshTree = true;
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
function moveTestCases(&$smartyObj,&$tsuiteMgr,&$treeMgr,$argsObj)
{
  if(sizeof($argsObj->tcaseSet) > 0)
  {
    // objectID - original container
    $guiObj = new stdClass();
   	$guiObj->attachments = getAttachmentInfosFrom($tsuiteMgr,$argsObj->objectID);
		$guiObj->id = $argsObj->objectID;
		
		$guiObj->refreshTree = true;
		$guiObj->user_feedback = $user_feedback;

    $status_ok = $treeMgr->change_parent($argsObj->tcaseSet,$argsObj->parentID);
    $guiObj->user_feedback = $status_ok ? '' : lang_get('move_testcases_failed');
  	
  	$identity = new stdClass();
	  $identity->tproject_id = $argsObj->tproject_id;
	  $identity->id = $argsObj->objectID;
	  $tsuiteMgr->show($smartyObj,$guiObj,$identity);
  }
}


/**
 * initWebEditors
 *
 */
 function initWebEditors()
 {
    $editorCfg = getWebEditorCfg('design');
    $editorSet = new stdClass();
    $editorSet->jsControls = array();
    $editorSet->templates = 'testsuite_template';
    $editorSet->inputNames = array('details');
    foreach($editorSet->inputNames as $key)
    {
      $editorSet->jsControls[$key] = web_editor($key,$_SESSION['basehref'],$editorCfg);
    }
    return $editorSet;
 }
 
 
 
 
/*
  function: deleteTestCasesViewer
            prepares smarty variables to display move testcases viewer

  args:

  returns: -

	@internal revisions
*/
function deleteTestCasesViewer(&$dbHandler,&$smartyObj,&$tprojectMgr,&$treeMgr,&$tsuiteMgr,
							   &$tcaseMgr,$argsObj,$feedback = null)
{

	$guiObj = new stdClass();
    $guiObj->main_descr = lang_get('delete_testcases');

	$tables = $tprojectMgr->getDBTables(array('nodes_hierarchy','node_types','tcversions'));
	$testcase_cfg = config_get('testcase_cfg');
	$glue = $testcase_cfg->glue_character;

	$guiObj->system_message = '';

	$containerID = isset($argsObj->testsuiteID) ? $argsObj->testsuiteID : $argsObj->objectID;
	$containerName = $argsObj->testsuiteName;
	if( is_null($containerName) )
	{
		$dummy = $treeMgr->get_node_hierarchy_info($argsObj->objectID);
		$containerName = $dummy['name'];
	}

	$guiObj->testCaseSet = $tsuiteMgr->get_children_testcases($containerID);
	$guiObj->exec_status_quo = null;
	$tcasePrefix = $tprojectMgr->getTestCasePrefix($argsObj->tproject_id);

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
	$guiObj->tproject_id = $argsObj->tproject_id;

	$smartyObj->assign('gui', $guiObj);
	return false;
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


function initializeEnv(&$dbHandler,$treeMgr)
{
  $env = array();
  $env[0] = init_args($treeMgr);
  $env[1] = initializeGui($dbHandler,$env[0]);
  $argsObj = &$env[0];
  $guiObj = &$env[1];

  
  $actionTpl = array( 'move_testsuite_viewer' => 'testSuiteMove.tpl',
                      'delete_testsuite' => 'testSuiteDelete.tpl',
                      'move_testcases_viewer' => 'containerMoveTC.tpl',
                      'do_copy_tcase_set' => 'containerMoveTC.tpl',
                      'delete_testcases' =>  'containerDeleteTC.tpl',
                      'do_delete_testcases' =>  'containerDeleteTC.tpl');

  $actionGetData = array('edit_testsuite' => 0,'new_testsuite' => 0,'delete_testsuite' => 0,'do_move' => 0,
					               'do_copy' => 0,'reorder_testsuites' => 1,'do_testsuite_reorder' => 0,
                         'add_testsuite' => 1,'move_testsuite_viewer' => 0,'update_testsuite' => 1,
                         'move_testcases_viewer' => 0,'do_move_tcase_set' => 0,
                         'do_copy_tcase_set' => 0, 'del_testsuites_bulk' => 0, 
                         'delete_testcases' => 0,'do_delete_testcases' => 0, 'reorder_testcases' => 0, 
                         'reorder_testsuites_alpha' => 0, 'reorder_testproject_testsuites_alpha' => 0);

  $actionInitOptTransfer = array('edit_testsuite' => 1,'new_testsuite'  => 1,'add_testsuite'  => 1,
                                 'update_testsuite' => 1);

  $guiObj->tpl = null;
  $guiObj->page_title = '';
  
  $argsObj->action = null;
  $argsObj->init_opt_transfer = null;
  $argsObj->getUserInput = null;

  foreach ($actionGetData as $key => $val)
  {
	  if (isset($_POST[$key]) )
	  {
	    $argsObj->action = $key;
		  $argsObj->init_opt_transfer = isset($actionInitOptTransfer[$argsObj->action]) ? 1 : 0;
		  $argsObj->getUserInput = $val;

		  $guiObj->tpl = isset($actionTpl[$argsObj->action]) ? $actionTpl[$argsObj->action] : null;
      $guiObj->page_title = lang_get('container_title_testsuite');
		  break;
	  }
  }

  return $env;
}

function initializeGui(&$dbHand,&$argsObj)
{
  $guiObj = new stdClass();
  $guiObj->containerType = $argsObj->containerType;
  $guiObj->tproject_id = $argsObj->tproject_id;
  $guiObj->refreshTree = $argsObj->refreshTree;
  $guiObj->midAirCollision = false;
  $guiObj->midAirCollisionMsg = array();
   
   
  $guiObj->keywordsViewHREF = "lib/keywords/keywordsView.php?tproject_id={$argsObj->tproject_id} " .
	  			 		                ' target="mainframe" class="bold" ' .
   	  		  	 		            ' title="' . lang_get('menu_manage_keywords') . '"';



  $lblkey = ($guiObj->SortCriteria = config_get('testcase_reorder_by')) == 'NAME' ? '_alpha' : '_externalid';
  $guiObj->btn_reorder_testcases = lang_get('btn_reorder_testcases' . $lblkey);
  $guiObj->page_title = lang_get('container_title_' . $argsObj->containerType);

  $guiObj->grants = new stdClass();
  $guiObj->grants->mgt_modify_tc = $argsObj->userObj->hasRight($dbHand,'mgt_modify_tc',$argsObj->tproject_id);
  return $guiObj;
}


function renderTestSuiteForManagement(&$tplEngine,&$argsObj,&$guiObj,&$tsuiteMgr,$keywordSet,$userInput=null)
{
  $guiObj->optionTransfer = tlKeyword::optionTransferGuiControl();
  $guiObj->optionTransfer->setNewRightInputName('assigned_keyword_list');
  $guiObj->optionTransfer->initFromPanel(null,lang_get('available_kword'));
  $guiObj->optionTransfer->initToPanel(null,lang_get('assigned_kword'));

  $guiObj->optionTransfer->setFromPanelContent($keywordSet['testproject']);
  $guiObj->optionTransfer->setToPanelContent($keywordSet['testsuite']);
  $guiObj->optionTransfer->updatePanelsContent($argsObj->assigned_keyword_list);
  
  $guiObj->optionTransferJSObject = json_encode($guiObj->optionTransfer->getHtmlInputNames());
  
  $context = array('tproject_id' => $argsObj->tproject_id,
		               'parent_id' => $argsObj->parentID, 
		               'id' => $argsObj->testsuiteID);

  $editorsObj = initWebEditors();
  
  new dBug($guiObj);
	$tsuiteMgr->viewer_edit_new($tplEngine,$guiObj,$argsObj->action,$context,$editorsObj,null,$userInput);
}
?>