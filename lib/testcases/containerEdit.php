<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @version $Revision: 1.42 $
 * @modified $Date: 2006/08/07 09:44:09 $ by $Author: franciscom $
 * @author Martin Havlat
 *
 * 20060804 - franciscom - added option transfer to manage keywords
 * 20060701 - franciscom
 * Added the Test Project as the FIRST Container where is possible to copy
 *
*/
require_once("../../config.inc.php");
require_once("../functions/common.php");
require_once("../../third_party/fckeditor/fckeditor.php");
require_once("../../lib/plan/plan.inc.php");
require_once("../functions/opt_transfer.php");
testlinkInitPage($db);

$tree_mgr = new tree($db);
$tproject_mgr = new testproject($db);
$tsuite_mgr = new testsuite($db);
$tcase_mgr = new testcase($db);

$my_tprojectID = $_SESSION['testprojectID'];
$my_testsuiteID = isset($_REQUEST['testsuiteID']) ? intval($_REQUEST['testsuiteID']) : null;
$my_containerID = isset($_REQUEST['containerID']) ? intval($_REQUEST['containerID']) : null;
if(!$my_containerID)
{
	$my_containerID = $my_tprojectID;	
}
$tsuite_name = isset($_REQUEST['testsuiteName']) ? strings_stripSlashes($_REQUEST['testsuiteName']) : null;
$objectID = isset($_GET['objectID']) ? intval($_GET['objectID']) : null;
$bSure = (isset($_GET['sure']) && ($_GET['sure'] == 'yes'));

// --------------------------------------------------------------------------------------------
// 20060804 - franciscom
$testproject_id = $_SESSION['testprojectID'];
$opt_cfg->js_ot_name='ot';
$rl_html_name = $opt_cfg->js_ot_name . "_newRight";
$assigned_keyword_list = isset($_REQUEST[$rl_html_name])? $_REQUEST[$rl_html_name] : "";
// --------------------------------------------------------------------------------------------

$smarty = new TLSmarty();

$a_keys['testsuite'] = array('details');

$a_tpl = array( 'move_testsuite_viewer' => 'containerMove.tpl',
                'add_testsuite' => 'containerNew.tpl',
                'delete_testsuite' => 'containerDelete.tpl',
                'reorder_testsuites' => 'containerOrder.tpl',
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

if($init_opt_transfer)
{
    $opt_cfg = opt_transf_empty_cfg();
    $opt_cfg->js_ot_name='ot';
    $opt_cfg->global_lbl='';
    $opt_cfg->from->lbl=lang_get('available_kword');
    $opt_cfg->from->map = $tproject_mgr->get_keywords_map($testproject_id);
    $opt_cfg->to->lbl=lang_get('assigned_kword');
    
    if($action=='edit_testsuite')
    {
      $opt_cfg->to->map=$tsuite_mgr->get_keywords_map($my_testsuiteID," ORDER BY keyword ASC ");
    }
}

// --------------------------------------------------------------------
// create  fckedit objects
//
$amy_keys = $a_keys[$level];
$oFCK = array();
foreach ($amy_keys as $key)
{
	$oFCK[$key] = new FCKeditor($key) ;
	$of = &$oFCK[$key];
	$of->BasePath = $_SESSION['basehref'] . 'third_party/fckeditor/';
	$of->ToolbarSet=$g_fckeditor_toolbar;;
}

if($get_c_data)
{
	$name_ok = 1;
	$c_data = get_values_from_post($amy_keys);
	
	if($name_ok && !check_string($c_data['name'],$g_ereg_forbidden))
	{
		$msg = lang_get('string_contains_bad_chars');
		$name_ok = 0;
	}
	
	if($name_ok && !strlen($c_data['name']))
	{
		$msg = $warning_empty_name;
		$name_ok = 0;
	}
}

if($action == 'edit_testsuite' || $action == 'new_testsuite')
{
	keywords_opt_transf_cfg($opt_cfg, $assigned_keyword_list); 
  $smarty->assign('opt_cfg', $opt_cfg);
	$tsuite_mgr->viewer_edit_new($smarty,$amy_keys, $oFCK, $action,$my_containerID, $my_testsuiteID);
}
else if($action == 'add_testsuite')
{
	if ($name_ok)
	{
		$msg = 'ok';
		$ret =$tsuite_mgr->create($my_containerID,$c_data['name'],$c_data['details'],
								              $g_check_names_for_duplicates,
								              $g_action_on_duplicate_name);
		if($ret['status_ok'])
		{
      if( strlen(trim($assigned_keyword_list)) > 0 )
      {
         // add keywords		  
         $a_keywords=explode(",",$assigned_keyword_list);
         $tsuite_mgr->addKeywords($ret['id'],$a_keywords);   	 
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
		// to the keys used on $oFCK.
		$of = &$oFCK[$key];
		$smarty->assign($key, $of->CreateHTML());
	}
	$smarty->assign('sqlResult',$msg);
	$smarty->assign('containerID',$my_tprojectID);
}
else if($action == 'update_testsuite')
{
	if($name_ok)
	{
	    $msg = 'ok';
	  	if ($tsuite_mgr->update($my_testsuiteID,$c_data['name'],$c_data['details'])) 
	  	{
        $tsuite_mgr->deleteKeywords($my_testsuiteID);   	 
        if( strlen(trim($assigned_keyword_list)) > 0 )
        {
           // add keywords		  
           $a_keywords=explode(",",$assigned_keyword_list);
           $tsuite_mgr->addKeywords($my_testsuiteID,$a_keywords);   	 
        }
      }   
      else
	  	{ 
	  	     $msg = $db->error_msg(); 
	  	}	
	}	
	$tsuite_mgr->show($smarty,$my_testsuiteID,'ok');
}
else if ($action == 'delete_testsuite')
{
	if($bSure)
	{
	    $tsuite_mgr->delete_deep($objectID);
      $tsuite_mgr->deleteKeywords($objectID);   	 
	}
	else
	{
		// Get test cases present in this testsuite and all children
		$testcases = $tsuite_mgr->get_testcases_deep($my_testsuiteID);
		
		if(!is_null($testcases))
		{
			$verbose = array();
			$link_msg = array();
			foreach($testcases as $the_key => $elem)
			{
				$verbose[] = $tree_mgr->get_path($elem['id'],$my_testsuiteID);
				$link_msg[] = $tcase_mgr->check_link_and_exec_status($elem['id']);
			}
			
			$idx = 0; 
			$warning = array();
			foreach($verbose as $the_key => $elem)
			{
				$warning[$idx] = '';
				$bSlash = false;
				foreach($elem as $tkey => $telem)
				{
					if ($bSlash)
					{
						$warning[$idx] .= "\\";
					}	
					$warning[$idx] .= $telem['name'];
					$bSlash = true;
				}	  
				$idx++;
			}	
		}
		
		//if the user has clicked the delete button on the archive page show the delete confirmation page
		$smarty->assign('objectID',$my_testsuiteID);
		$smarty->assign('objectName', $tsuite_name);
		$smarty->assign('warning', $warning);
		$smarty->assign('link_msg', $link_msg);
	}
}
else if($action == 'move_testsuite_viewer') 
{
	
	$testsuites = $tproject_mgr->gen_combo_test_suites($my_tprojectID,
	                                                  array($my_testsuiteID => 'exclude'));
  
  // 20060701 - franciscom
  // Added the Test Project as the FIRST Container where is possible to copy
  $testsuites = array($my_tprojectID => $_SESSION['testprojectName']) + $testsuites;
  
	$smarty->assign('old_containerID', $my_tprojectID); // original container
	$smarty->assign('arraySelect', $testsuites);
	$smarty->assign('objectID', $my_testsuiteID);
	$smarty->assign('object_name', $tsuite_name);
}
else if($action == 'reorder_testsuites')
{
	$object_id = is_null($my_testsuiteID) ? $my_containerID : $my_testsuiteID;
	$children = $tree_mgr->get_children($object_id, array("testplan" => "exclude_me"));	
  	if (!sizeof($children))
		$children = null;
  	
	$smarty->assign('arraySelect', $children);
	$smarty->assign('data', $my_testsuiteID);
}
else if($action == 'do_testsuite_reorder')
{
	$generalResult = 'ok';
	$tree_mgr->change_order_bulk($_POST['id'],$_POST['order']);
	
	$tsuite_mgr->show($smarty,$my_containerID,'ok');
}
else if($action == 'do_move')
{
	$tree_mgr->change_parent($objectID,$my_containerID);  
	
	$tproject_mgr->show($smarty,$my_tprojectID,'ok');
}	
else if($action == 'do_copy')
{
	$copyKeywords = isset($_POST['copyKeywords']) ? intval($_POST['copyKeywords']) : 0;
	
	$tsuite_mgr->copy_to($objectID, $my_containerID, $_SESSION['userID'],
	                     config_get('check_names_for_duplicates'),
	                     config_get('action_on_duplicate_name'),$copyKeywords);
	
	$tsuite_mgr->show($smarty,$objectID,'ok');
}	
else 
{
	trigger_error("containerEdit.php - No correct GET/POST data", E_USER_ERROR);
}

if ($the_tpl)
{
	$smarty->display($the_tpl);
} 



// Auxiliary functions
function get_values_from_post($akeys2get)
{
	$akeys2get[] = 'name';
	$c_data = array();
	foreach($akeys2get as $key)
	{
		$c_data[$key] = isset($_POST[$key]) ? strings_stripSlashes($_POST[$key]) : null;
	}
	return $c_data;
}	
?>
