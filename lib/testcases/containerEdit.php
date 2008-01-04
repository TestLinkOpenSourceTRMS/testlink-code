<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @version $Revision: 1.70 $
 * @modified $Date: 2008/01/04 20:31:21 $ by $Author: franciscom $
 * @author Martin Havlat
 *
 * 
 *
 * 20070218 - franciscom - added $g_spec_cfg->automatic_tree_refresh to the
 *                         refresh tree logic
 *
 * xxxxxxxx - Added the Test Project as the FIRST Container where is possible to copy
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

$template_dir='testcases/';
$bRefreshTree = false;
$level = null;

// Option Transfer configuration
$opt_cfg->js_ot_name='ot';

$args=init_args($opt_cfg);


// --------------------------------------------------------------------------------------------
// --------------------------------------------------------------------------------------------

$gui_cfg = config_get('gui');
$spec_cfg = config_get('spec_cfg');
 
$smarty = new TLSmarty();

$a_keys['testsuite'] = array('details');

$a_tpl = array( 'move_testsuite_viewer' => 'containerMove.tpl',
                /* 'add_testsuite' => 'containerNew.tpl', */
                'delete_testsuite' => 'containerDelete.tpl',
                /* 'reorder_testsuites' => 'drag-drop-folder-tree.html',*/
                'reorder_testsuites' => 'containerOrderDnD.tpl',  /* DnD -> Drag and Drop */ 
                'updateTCorder' => 'containerView.tpl',
				); 

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
				           );

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
    $opt_cfg = opt_transf_empty_cfg();
    $opt_cfg->js_ot_name='ot';
    $opt_cfg->global_lbl='';
    $opt_cfg->from->lbl=lang_get('available_kword');
    $opt_cfg->from->map = $tproject_mgr->get_keywords_map($args->tprojectID);
    $opt_cfg->to->lbl=lang_get('assigned_kword');
    
    if($action=='edit_testsuite')
    {
      $opt_cfg->to->map=$tsuite_mgr->get_keywords_map($args->testsuiteID," ORDER BY keyword ASC ");
    }
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
if($action == 'edit_testsuite' || $action == 'new_testsuite')
{
	keywords_opt_transf_cfg($opt_cfg, $args->assigned_keyword_list); 
	$smarty->assign('opt_cfg', $opt_cfg);
	$tsuite_mgr->viewer_edit_new($smarty,$template_dir,$amy_keys, 
	                             $oWebEditor, $action,$args->containerID, $args->testsuiteID);
}
else if($action == 'add_testsuite')
{
	keywords_opt_transf_cfg($opt_cfg, ""); 
	$smarty->assign('opt_cfg', $opt_cfg);
	if ($name_ok)
	{
		$msg = 'ok';
		$ret =$tsuite_mgr->create($args->containerID,$c_data['container_name'],$c_data['details'],
								              $g_check_names_for_duplicates,
								              $g_action_on_duplicate_name);
		if($ret['status_ok'])
		{
		  $user_feedback=lang_get('testsuite_created');
		  
      if( strlen(trim($args->assigned_keyword_list)) > 0 )
      {
         // add keywords		  
         $a_keywords=explode(",",$args->assigned_keyword_list);
         $tsuite_mgr->addKeywords($ret['id'],$a_keywords);   	 
      }   

      if( $gui_cfg->enable_custom_fields )
      {
        $ENABLED = 1;
        $NO_FILTER_SHOW_ON_EXEC = null;
        $cfield_mgr = new cfield_mgr($db);
        $cf_map = $cfield_mgr->get_linked_cfields_at_design($args->tprojectID,$ENABLED,$NO_FILTER_SHOW_ON_EXEC,
                                                            'testsuite') ;
        $cfield_mgr->design_values_to_db($_REQUEST,$ret['id'],$cf_map);
      }  
		}                             
		else
		{                             
			$msg = $ret['msg'];
		}	
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
	
	// 20061231 - franciscom
	$tsuite_mgr->viewer_edit_new($smarty,$template_dir,$amy_keys, $oWebEditor, $action,
	                             $args->containerID, null,$msg,$user_feedback);
	
	
}
else if($action == 'update_testsuite')
{
	if($name_ok)
	{
	    $msg = 'ok';
	  	if ($tsuite_mgr->update($args->testsuiteID,$c_data['container_name'],$c_data['details'])) 
	  	{
        $tsuite_mgr->deleteKeywords($args->testsuiteID);   	 
        if( strlen(trim($args->assigned_keyword_list)) > 0 )
        {
           $a_keywords=explode(",",$args->assigned_keyword_list);
           $tsuite_mgr->addKeywords($args->testsuiteID,$a_keywords);   	 
        }

        if( $gui_cfg->enable_custom_fields )
        {
          $ENABLED=1;
          $NO_FILTER_SHOW_ON_EXEC=null;
          $cfield_mgr= new cfield_mgr($db);
          $cf_map=$cfield_mgr->get_linked_cfields_at_design($args->tprojectID,$ENABLED,$NO_FILTER_SHOW_ON_EXEC,
                                                            'testsuite') ;
          $cfield_mgr->design_values_to_db($_REQUEST,$args->testsuiteID,$cf_map);
        }  
      }   
      else
	  	{ 
	  	     $msg = $db->error_msg(); 
	  	}	
	}	
	$tsuite_mgr->show($smarty,$template_dir,$args->testsuiteID,'ok');
}
else if ($action == 'delete_testsuite')
{
  $feedback_msg='';
	if($args->bSure)
	{
	  $tsuite=$tsuite_mgr->get_by_id($args->objectID);
		$tsuite_mgr->delete_deep($args->objectID);
		$tsuite_mgr->deleteKeywords($args->objectID);   	 
		$smarty->assign('objectName', $tsuite['name']);

		$bRefreshTree = true;
		$feedback_msg='ok';
	}
	else
	{
		// Get test cases present in this testsuite and all children
		$testcases = $tsuite_mgr->get_testcases_deep($args->testsuiteID);
		
		$map_msg['warning']=null;
		$map_msg['link_msg']=null;
		$map_msg['delete_msg']=null;
		
		if(!is_null($testcases))
		{
		  $map_msg=build_del_testsuite_warning_msg($tree_mgr,$tcase_mgr,$testcases,$args->testsuiteID);
		}
		
		// prepare to show the delete confirmation page
		$smarty->assign('objectID',$args->testsuiteID);
		$smarty->assign('objectName', $args->tsuite_name);
		
		$smarty->assign('delete_msg',$map_msg['delete_msg']);
		$smarty->assign('warning', $map_msg['warning']);
		$smarty->assign('link_msg', $map_msg['link_msg']);
	}
	$smarty->assign('page_title',
	                lang_get('delete') . " " . lang_get('container_title_' . $level) . TITLE_SEP);
 	$smarty->assign('sqlResult',$feedback_msg);

}
else if($action == 'move_testsuite_viewer') 
{
	
	$testsuites = $tproject_mgr->gen_combo_test_suites($args->tprojectID,
	                                                  array($args->testsuiteID => 'exclude'));
  
	// Added the Test Project as the FIRST Container where is possible to copy
	$testsuites = array($args->tprojectID => $args->tprojectName) + $testsuites;
  
	$smarty->assign('old_containerID', $args->tprojectID); // original container
	$smarty->assign('arraySelect', $testsuites);
	$smarty->assign('objectID', $args->testsuiteID);
	$smarty->assign('object_name', $args->tsuite_name);
}
else if($action == 'reorder_testsuites')
{
	$oid = is_null($args->testsuiteID) ? $args->containerID : $args->testsuiteID;
	
	// 20071111 - franciscom
	$children = $tree_mgr->get_children($oid, array("testplan" => "exclude_me",
	                                                      "requirement_spec"  => "exclude_me"));	
  $object_info = $tree_mgr->get_node_hierachy_info($oid);
  $object_name = $object_info['name'];


 	if (!sizeof($children))
		$children = null;
		
	$smarty->assign('arraySelect', $children);
	$smarty->assign('objectID', $oid);
  $smarty->assign('object_name', $object_name);
  
  if( $oid == $args->tprojectID)
  {
    $level='testproject';
    $smarty->assign('level', $level);
    $smarty->assign('page_title',lang_get('container_title_' . $level));
  }
}
else if($action == 'do_testsuite_reorder')
{
	$generalResult = 'ok';
  $nodes_in_order=transform_nodes_order($args->nodes_order,$args->containerID);
	$tree_mgr->change_order_bulk($nodes_in_order);
	if( $args->containerID == $args->tprojectID )
	{
	  $tproject_mgr->show($smarty,$template_dir,$args->containerID,$generalResult);
	}
	else
	{
	  $tsuite_mgr->show($smarty,$template_dir,$args->containerID,$generalResult);
	}
}
else if($action == 'do_move')
{
	$tree_mgr->change_parent($args->objectID,$args->containerID);  
	
	$tproject_mgr->show($smarty,$template_dir,$args->tprojectID,'ok');
}	
else if($action == 'do_copy')
{
	$copyKeywords = isset($_POST['copyKeywords']) ? intval($_POST['copyKeywords']) : 0;
	
	$tsuite_mgr->copy_to($args->objectID, $args->containerID, $args->userID,
	                     config_get('check_names_for_duplicates'),
	                     config_get('action_on_duplicate_name'),$copyKeywords);
	
	$tsuite_mgr->show($smarty,$template_dir,$args->objectID,'ok');
}	
else 
{
	trigger_error("containerEdit.php - No correct GET/POST data", E_USER_ERROR);
}

if ($the_tpl)
{
  	$smarty->assign('refreshTree',$bRefreshTree && $spec_cfg->automatic_tree_refresh);
	$smarty->display($template_dir . $the_tpl);
} 




// ----------------------------------------------------------------------------------------- 
// Auxiliary functions
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
    $_REQUEST=strings_stripSlashes($_REQUEST);
    
    $args->tprojectID = $_SESSION['testprojectID'];
    $args->tprojectName = $_SESSION['testprojectName'];
    $args->userID = $_SESSION['userID'];


    $args->tsuite_name = isset($_REQUEST['testsuiteName']) ? $_REQUEST['testsuiteName'] : null;
    $args->nodes_order = isset($_REQUEST['nodes_order']) ? $_REQUEST['nodes_order'] : null;
    $args->bSure = (isset($_REQUEST['sure']) && ($_REQUEST['sure'] == 'yes'));

    $rl_html_name = $optionTransferCfg->js_ot_name . "_newRight";
    $args->assigned_keyword_list = isset($_REQUEST[$rl_html_name])? $_REQUEST[$rl_html_name] : "";

    
    $keys2loop=array('testsuiteID' => null, 'containerID' => null, 'objectID' => null);
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
?>


