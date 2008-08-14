<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @version $Revision: 1.86 $
 * @modified $Date: 2008/08/14 15:08:25 $ by $Author: franciscom $
 * @author Martin Havlat
 *
 * rev:
 *     20080602 - franciscom - doTestSuiteReorder() - fixed typo error
 *     20080504 - franciscom - removed references to gui->enable_custom_fields
 *     20080329 - franciscom - added contribution by Eugenia Drosdezki
 *                             Move/copy testcases
 *
 *     20080223 - franciscom - BUGID 1408
 *     20080129 - franciscom - contribution - tuergeist@gmail.com - doTestSuiteReorder() remove global coupling
 *     20080122 - franciscom - BUGID 1312
*/
require_once("../../config.inc.php");
require_once("common.php");
require_once("opt_transfer.php");
require_once("web_editor.php");

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
$spec_cfg = config_get('spec_cfg');
$smarty = new TLSmarty();

$a_keys['testsuite'] = array('details');

$a_tpl = array( 'move_testsuite_viewer' => 'containerMove.tpl',
                'delete_testsuite' => 'containerDelete.tpl',
                'reorder_testsuites' => 'containerOrderDnD.tpl',  /* DnD -> Drag and Drop */
                'updateTCorder' => 'containerView.tpl',
                'move_testcases_viewer' => 'containerMoveTC.tpl');

$a_actions = array ('edit_testsuite' => 0,
					          'new_testsuite' => 0,
                    'delete_testsuite' => 0,
					          'do_move' => 0,
					          'do_copy' => 0,
					          'reorder_testsuites' => 1,
					          'do_testsuite_reorder' => 0,
                    'add_testsuite' => 1,
					          'move_testsuite_viewer' => 0,
					          'update_testsuite' => 1,
                    'move_testcases_viewer' => 0,
	                  'do_move_tcase_set' => 0,
                    'do_copy_tcase_set' => 0 );

$a_init_opt_transfer=array('edit_testsuite' => 1,
					                 'new_testsuite'  => 1,
					                 'add_testsuite'  => 1,
                           'update_testsuite' => 1);

$the_tpl = null;
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

$smarty->assign('level', $level);
$smarty->assign('page_title',lang_get('container_title_' . $level));

if($init_opt_transfer)
{
    $opt_cfg = initializeOptionTransfer($tproject_mgr,$tsuite_mgr,$args,$action);
}

// create  web editor objects
$amy_keys = $a_keys[$level];
$oWebEditor = array();
foreach ($amy_keys as $key)
{
	$oWebEditor[$key] = web_editor($key,$_SESSION['basehref']);
}

if($get_c_data)
{
	$name_ok = 1;
	$c_data = getValuesFromPost($amy_keys);

	if($name_ok && !check_string($c_data['container_name'],$g_ereg_forbidden))
	{
		$msg = lang_get('string_contains_bad_chars');
		$name_ok = 0;
	}

	if($name_ok && !strlen($c_data['container_name']))
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
		$tsuite_mgr->viewer_edit_new($smarty,$template_dir,$amy_keys,
							   $oWebEditor, $action,$args->containerID, $args->testsuiteID);
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
    $ret=reorderTestSuiteViewer($smarty,$tree_mgr,$args);
    $level=is_null($ret) ? $level : $ret;
    break;

    case 'do_testsuite_reorder':
    doTestSuiteReorder($smarty,$template_dir,$tproject_mgr,$tsuite_mgr,$args);
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
        $msg=updateTestSuite($tsuite_mgr,$args,$c_data,$_REQUEST);
    }
    $tsuite_mgr->show($smarty,$template_dir,$args->testsuiteID,$msg);
    break;

    case 'add_testsuite':
	  keywords_opt_transf_cfg($opt_cfg, "");
	  $smarty->assign('opt_cfg', $opt_cfg);

	  if ($name_ok)
	  {
	      $messages=addTestSuite($tsuite_mgr,$args,$c_data,$_REQUEST);
	      $msg=$messages['msg'];
	  }
    else
    {
	      $messages['user_feedback']='';
    }

	  // setup for displaying an empty form
	  foreach ($amy_keys as $key)
	  {
	  	// Warning:
	  	// the data assignment will work while the keys in $the_data are identical
	  	// to the keys used on $oWebEditor.
	  	$of = &$oWebEditor[$key];
	  	$smarty->assign($key, $of->CreateHTML());
	  }

	  $tsuite_mgr->viewer_edit_new($smarty,$template_dir,$amy_keys, $oWebEditor, $action,
	                               $args->containerID, null,$msg,$messages['user_feedback']);
    break;


    case 'do_move_tcase_set':
    moveTestCases($smarty,$template_dir,$tsuite_mgr,$tree_mgr,$args);
    break;

    case 'do_copy_tcase_set':
    copyTestCases($smarty,$template_dir,$tsuite_mgr,$tcase_mgr,$args);
    break;


    default:
    trigger_error("containerEdit.php - No correct GET/POST data", E_USER_ERROR);
    break;


}

if ($the_tpl)
{
	$smarty->assign('refreshTree',$refreshTree && $spec_cfg->automatic_tree_refresh);
	$smarty->display($template_dir . $the_tpl);
}


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
  $msg['warning']=null;
  $msg['link_msg']=null;
  $msg['delete_msg']=null;

  if(!is_null($testcases))
  {
    $show_warning=0;
    $delete_msg='';
  	$verbose = array();
  	$msg['link_msg'] = array();

    $status_warning=array('linked_and_executed' => 1,
    	                    'linked_but_not_executed' => 1,
    	                    'no_links' => 0);

  	$delete_notice=array('linked_and_executed' => lang_get('delete_notice'),
    	                    'linked_but_not_executed' => '',
    	                    'no_links' => '');

  	foreach($testcases as $the_key => $elem)
  	{
  		$verbose[] = $tree_mgr->get_path($elem['id'],$tsuite_id);

  		$status=$tcase_mgr->check_link_and_exec_status($elem['id']);
  		$msg['link_msg'][] = $status;

  		if( $status_warning[$status] )
  		{
  		  $show_warning=1;
  		  $msg['delete_msg']=$delete_notice[$status];
  		}

  	}

  	$idx = 0;
  	if( $show_warning)
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
  	  $msg['link_msg']=null;
  		$msg['warning']=null;
  	}
  }
  return($msg);
} // end function



/*
  function:

  args :

  returns:

*/
function init_args($optionTransferCfg)
{
   	$args = new stdClass();
    $_REQUEST=strings_stripSlashes($_REQUEST);

    $args->tprojectID = $_SESSION['testprojectID'];
    $args->tprojectName = $_SESSION['testprojectName'];
    $args->userID = $_SESSION['userID'];


    $keys2loop=array('nodes_order' => null, 'tcaseSet' => null,'target_position' => 'bottom');
    foreach($keys2loop as $key => $value)
    {
       $args->$key = isset($_REQUEST[$key]) ? $_REQUEST[$key] : $value;
    }


    $args->tsuite_name = isset($_REQUEST['testsuiteName']) ? $_REQUEST['testsuiteName'] : null;
    $args->bSure = (isset($_REQUEST['sure']) && ($_REQUEST['sure'] == 'yes'));
    $rl_html_name = $optionTransferCfg->js_ot_name . "_newRight";
    $args->assigned_keyword_list = isset($_REQUEST[$rl_html_name])? $_REQUEST[$rl_html_name] : "";


    // integer values
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

    return $args;
}


/*
  function:

  args:

  returns:

*/
function writeCustomFieldsToDB(&$db,$tprojectID,$tsuiteID,&$hash)
{
    $ENABLED=1;
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
	if($argsObj->bSure)
	{
	 	$tsuite = $tsuiteMgr->get_by_id($argsObj->objectID);
		$tsuiteMgr->delete_deep($argsObj->objectID);
		$tsuiteMgr->deleteKeywords($argsObj->objectID);
		$smartyObj->assign('objectName', $tsuite['name']);

		$doRefreshTree = true;
		$feedback_msg = 'ok';
	}
	else
	{
	  	$doRefreshTree = false;

		// Get test cases present in this testsuite and all children
		$testcases = $tsuiteMgr->get_testcases_deep($argsObj->testsuiteID);

		$map_msg['warning'] = null;
		$map_msg['link_msg'] = null;
		$map_msg['delete_msg'] = null;

		if(!is_null($testcases))
		{
			$map_msg = build_del_testsuite_warning_msg($treeMgr,$tcaseMgr,$testcases,$argsObj->testsuiteID);
		}

		// prepare to show the delete confirmation page
		$smartyObj->assign('objectID',$argsObj->testsuiteID);
		$smartyObj->assign('objectName', $argsObj->tsuite_name);
		$smartyObj->assign('delete_msg',$map_msg['delete_msg']);
		$smartyObj->assign('warning', $map_msg['warning']);
		$smartyObj->assign('link_msg', $map_msg['link_msg']);
	}
	$smartyObj->assign('page_title', lang_get('delete') . " " . lang_get('container_title_' . $level));
 	$smartyObj->assign('sqlResult',$feedback_msg);

 	return $doRefreshTree;
}

/*
  function: addTestSuite

  args:

  returns: messages map

*/
function addTestSuite(&$tsuiteMgr,&$argsObj,$container,&$hash)
{
		$ret =$tsuiteMgr->create($argsObj->containerID,$container['container_name'],$container['details'],
								             config_get('check_names_for_duplicates'),config_get('action_on_duplicate_name'));
		$messages['msg'] = $ret['msg'];
		$messages['user_feedback']='';
		if($ret['status_ok'])
		{
		  $messages['msg'] = 'ok';
		  $messages['user_feedback']=lang_get('testsuite_created');

      if( strlen(trim($argsObj->assigned_keyword_list)) > 0 )
      {
         $tsuiteMgr->addKeywords($ret['id'],explode(",",$argsObj->assigned_keyword_list));
      }
      writeCustomFieldsToDB($tsuiteMgr->db,$argsObj->tprojectID,$ret['id'],$hash);
		}
		return $messages;
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
  $level=null;
	$oid = is_null($argsObj->testsuiteID) ? $argsObj->containerID : $argsObj->testsuiteID;
	$children = $treeMgr->get_children($oid, array("testplan" => "exclude_me",
                                                 "requirement_spec"  => "exclude_me"));
  $object_info = $treeMgr->get_node_hierachy_info($oid);
  $object_name = $object_info['name'];


 	if (!sizeof($children))
		$children = null;

	$smartyObj->assign('arraySelect', $children);
	$smartyObj->assign('objectID', $oid);
  $smartyObj->assign('object_name', $object_name);

  if( $oid == $argsObj->tprojectID)
  {
    $level='testproject';
    $smartyObj->assign('level', $level);
    $smartyObj->assign('page_title',lang_get('container_title_' . $level));
  }

  return $level;
}


/*
  function: doTestSuiteReorder


  args:

  returns: -

  rev:
      20080602 - franciscom - fixed typo bug 
      20080223 - franciscom - fixed typo error - BUGID 1408
      removed wrong global coupling
*/
function  doTestSuiteReorder(&$smartyObj,$template_dir,&$tprojectMgr,&$tsuiteMgr,$argsObj)
{
	$nodes_in_order=transform_nodes_order($argsObj->nodes_order,$argsObj->containerID);
	$tprojectMgr->tree_manager->change_order_bulk($nodes_in_order);
	if( $argsObj->containerID == $argsObj->tprojectID )
	{
	  $objMgr=$tprojectMgr;
	}
	else
	{
	  $objMgr=$tsuiteMgr;
	}
	$objMgr->show($smartyObj,$template_dir,$argsObj->containerID,'ok');
}

/*
  function: updateTestSuite

  args:

  returns:

*/
function updateTestSuite(&$tsuiteMgr,&$argsObj,$container,&$hash)
{
	$msg = 'ok';
	if ($tsuiteMgr->update($argsObj->testsuiteID,$container['container_name'],$container['details']))
	{
    $tsuiteMgr->deleteKeywords($argsObj->testsuiteID);
    if( strlen(trim($argsObj->assigned_keyword_list)) > 0 )
    {
       $tsuiteMgr->addKeywords($argsObj->testsuiteID,explode(",",$argsObj->assigned_keyword_list));
    }
    writeCustomFieldsToDB($tsuiteMgr->db,$argsObj->tprojectID,$argsObj->testsuiteID,$hash);
  }
  else
	{
	     $msg = $tsuiteMgr->db->error_msg();
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
  	$op=$tsuiteMgr->copy_to($argsObj->objectID, $argsObj->containerID, $argsObj->userID,
	                          config_get('check_names_for_duplicates'),
	                          config_get('action_on_duplicate_name'),$argsObj->copyKeywords);
	  if( $op['status_ok'] )
	  {
	      $tsuiteMgr->tree_manager->change_child_order($argsObj->containerID,$op['id'],
                                                     $argsObj->target_position,$exclude_node_types);
	  }
	  $tsuiteMgr->show($smartyObj,$template_dir,$argsObj->objectID,'ok');
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
  $tprojectMgr->show($smartyObj,$template_dir,$argsObj->tprojectID,'ok');
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
function moveTestCasesViewer(&$dbHandler,&$smartyObj,&$tprojectMgr,&$treeMgr,$argsObj)
{
	$testcase_cfg = config_get('testcase_cfg');
	$glue = $testcase_cfg->glue_character;
	$testsuites = $tprojectMgr->gen_combo_test_suites($argsObj->tprojectID,
	                                                  array($argsObj->testsuiteID => 'exclude'));
	$tcasePrefix = $tprojectMgr->getTestCasePrefix($argsObj->tprojectID) . $glue;

 	 $sql = "SELECT NHA.id AS TCID, NHA.name AS TCNAME, NHA.node_order AS TCORDER," .
        " MAX(TCV.version) AS TCLASTVERSION, TCV.tc_external_id TCEXTERNALID" .
        " FROM nodes_hierarchy NHA, nodes_hierarchy NHB, node_types NT, tcversions TCV " .
        " WHERE NHB.parent_id=NHA.id " .
        " AND TCV.id=NHB.id AND NHA.node_type_id = NT.id AND NT.description='testcase'" .
        " AND NHA.parent_id={$argsObj->testsuiteID} " .
        " GROUP BY NHA.id,NHA.name,NHA.node_order,TCV.tc_external_id " .
        " ORDER BY TCORDER,TCNAME";

  $children = $dbHandler->get_recordset($sql);

 	// check if operation can be done
	$user_feedback = '';
	if(!is_null($children) && (sizeof($children) > 0) && sizeof($testsuites))
	{
	    $op_ok = true;
	}
	else
	{
	    $children = null;
	    $op_ok = false;
	    $user_feedback = lang_get('no_testcases_available');
	}

	$smartyObj->assign('op_ok', $op_ok);
	$smartyObj->assign('user_feedback', $user_feedback);
	$smartyObj->assign('tcprefix', $tcasePrefix);
	$smartyObj->assign('testcases', $children);
	$smartyObj->assign('old_containerID', $argsObj->tprojectID); //<<<<-- check if is needed
	$smartyObj->assign('containers', $testsuites);
	$smartyObj->assign('objectID', $argsObj->testsuiteID);
	$smartyObj->assign('object_name', $argsObj->tsuite_name);
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
    if(sizeof($argsObj->tcaseSet) > 0)
    {
        $check_names_for_duplicates_cfg = config_get('check_names_for_duplicates');
        $action_on_duplicate_name_cfg = config_get('action_on_duplicate_name');

        foreach($argsObj->tcaseSet as $key => $tcaseid)
        {
            $op=$tcaseMgr->copy_to($tcaseid, $argsObj->containerID, $argsObj->userID,
	                                 $argsObj->copyKeywords,$check_names_for_duplicates_cfg,
	                                 $action_on_duplicate_name_cfg);
        }
        $tsuiteMgr->show($smartyObj,$template_dir,$argsObj->objectID);
    }

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
        $tsuiteMgr->show($smartyObj,$template_dir,$argsObj->objectID,$user_feedback);
    }
}
?>