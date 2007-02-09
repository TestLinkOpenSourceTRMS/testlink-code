<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: execSetResults.php,v $
 *
 * @version $Revision: 1.54 $
 * @modified $Date: 2007/02/10 12:15:51 $ $Author: schlundus $
 *
 * 20070105 - franciscom - refactoring
 *
 * 20070104 - franciscom - 
 * 1. solved bug in custom fields for test suites
 *    I was always displaying the custom fields of
 *    the top test suite clicked.
 *
 * 2. start of test case custom field management
 *
 * 20070101 - franciscom - custom field management for test suites
 *
**/
require_once('../../config.inc.php');
require_once('common.php');
require_once('exec.inc.php');
require_once("../../lib/functions/builds.inc.php");
require_once("../../lib/functions/attachments.inc.php");

testlinkInitPage($db);

$smarty = new TLSmarty();

$PID_NOT_NEEDED = null;
$SHOW_ON_EXECUTION = 1;

$exec_cfg = config_get('exec_cfg');
$gui_cfg = config_get('gui'); 

$tree_mgr = new tree($db);
$tplan_mgr = new testplan($db);
$tcase_mgr = new testcase($db);
$build_mgr = new build_mgr($db);

$testdata = array();
$ts_cf_smarty = '';
$submitResult = null;

$_REQUEST = strings_stripSlashes($_REQUEST);
$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
$build_id = isset($_REQUEST['build_id']) ? intval($_REQUEST['build_id']) : 0;
$tc_id = isset($_REQUEST['tc_id']) ? intval($_REQUEST['tc_id']) : null;
$keyword_id = isset($_REQUEST['keyword_id']) ? intval($_REQUEST['keyword_id']) : 0;
$level = isset($_REQUEST['level']) ? $_REQUEST['level'] : '';
$owner = isset($_REQUEST['owner']) ? intval($_REQUEST['owner']) : null;
$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : null;

if (!strlen($level))
{
  	redirect($_SESSION['basehref'] . "/lib/general/show_help.php?help=executeTest&locale={$_SESSION['locale']}");
	exit();
}
$ownerDisplayName = null;
if ($owner)
	$ownerDisplayName = getUserName($db,$owner);


$tplan_id = $_SESSION['testPlanId'];
$user_id = $_SESSION['userID'];
$the_builds = $tplan_mgr->get_builds_for_html_options($tplan_id);
$build_name = isset($the_builds[$build_id]) ? $the_builds[$build_id] : '';

$history_on = manage_history_on($_REQUEST,$_SESSION,$exec_cfg,'btn_history_on','btn_history_off','history_on');
$_SESSION['history_on'] = $history_on;

$history_status_btn_name = 'btn_history_on';
if($history_on)
{
    $history_status_btn_name = 'btn_history_off';
}

$testplan_cf=null;
$cfexec_val_smarty= null;
$bugs = null;
$attachmentInfos = null;
$map_last_exec = null;
$other_execs = null;
$map_last_exec_any_build = null;
$tcAttachments = null;
$tSuiteAttachments = null;
$linked_tcversions = $tplan_mgr->get_linked_tcversions($tplan_id,$tc_id,$keyword_id,null,$owner,$status);
$tcase_id = 0;

// -------------------------------------------------
$rs = $tplan_mgr->get_by_id($tplan_id);
$testplan_cf=$tplan_mgr->html_table_of_custom_field_values($tplan_id,'execution',FILTER_BY_SHOW_ON_EXECUTION);
$testproject_id=$rs['parent_id'];
$smarty->assign('tplan_notes',$rs['notes']);
$smarty->assign('tplan_cf',$testplan_cf);

if(!is_null($linked_tcversions))
{
	$items_to_exec = array();
	$_SESSION['s_lastAttachmentInfos'] = null;
    if($level == 'testcase')
    {
		$cf_smarty = '';
		$cfexec_smarty = '';
		
		$items_to_exec[$id] = $linked_tcversions[$id]['tcversion_id'];    
		$tcase_id = $id;
		$tcversion_id = $linked_tcversions[$id]['tcversion_id'];
		$tcAttachments[$id] = getAttachmentInfos($db,$id,'nodes_hierarchy',1);
 
		if($gui_cfg->enable_custom_fields)
		{
			$cf_smarty[$id] = $tcase_mgr->html_table_of_custom_field_values($id,'design',$SHOW_ON_EXECUTION);
			$cfexec_smarty[$id] = $tcase_mgr->html_table_of_custom_field_inputs($id,$PID_NOT_NEEDED,
			                                'execution',"_{$id}");
		}
		$smarty->assign('design_time_cf',$cf_smarty);
		$smarty->assign('execution_time_cf',$cfexec_smarty);	
    }
    else
    {
		// Get the path for every test case, grouping test cases that
		// have same parent.
    	$tcase_id = array();
    	$tcversion_id = array();
		$idx = 0;
		  
    	foreach($linked_tcversions as $item)
    	{
    		$path_f = $tree_mgr->get_path($item['tc_id'],null,'full');
    		foreach($path_f as $key => $path_elem)
    		{
    			if( $path_elem['parent_id'] == $id )
    			{
					 // Can be added because is present in the branch the user wants to view
					 // ID of branch starting node is in $id
					 $tcase_id[] = $item['tc_id'];
					 $tcversion_id[] = $item['tcversion_id'];
					 $tcAttachments[$item['tc_id']] = getAttachmentInfos($db,$item['tc_id'],'nodes_hierarchy',true,1);

			           // --------------------------------------------------------------------------------------
			           if($gui_cfg->enable_custom_fields)
			           {
							$cf_smarty[$item['tc_id']] = $tcase_mgr->html_table_of_custom_field_values($item['tc_id'],
							                                                                        'design',$SHOW_ON_EXECUTION);
							$cfexec_smarty[$item['tc_id']] = $tcase_mgr->html_table_of_custom_field_inputs($item['tc_id'],
							                                                            $PID_NOT_NEEDED,'execution',
							                                                            "_".$item['tc_id']);
			           }
			           $smarty->assign('design_time_cf',$cf_smarty);	
			           $smarty->assign('execution_time_cf',$cfexec_smarty);	
			           // --------------------------------------------------------------------------------------
    			}
    			
				if($path_elem['node_table'] == 'testsuites' && !isset($tSuiteAttachments[$path_elem['id']]))
					   $tSuiteAttachments[$path_elem['id']] = getAttachmentInfos($db,$path_elem['id'],'nodes_hierarchy',true,1);
			  } //foreach($path_f as $key => $path_elem) 
    	}
    }
    // will create a record even if the testcase version has not been executed (GET_NO_EXEC)
    $map_last_exec = $tcase_mgr->get_last_execution($tcase_id,$tcversion_id,$tplan_id,
                                                    $build_id,GET_NO_EXEC);
    
    // --------------------------------------------------------------------------------------------
    // Results to DB
    if (isset($_REQUEST['save_results']) || isset($_REQUEST['do_bulk_save']))
    {
      // 20070105 - added $testproject_id
    	$submitResult = write_execution($db,$user_id,$_REQUEST,$testproject_id,$tplan_id,$build_id,$map_last_exec);
    }
    // --------------------------------------------------------------------------------------------
    
    $map_last_exec_any_build = null;
    if( $exec_cfg->show_last_exec_any_build )
    {
        $map_last_exec_any_build = $tcase_mgr->get_last_execution($tcase_id,$tcversion_id,$tplan_id,
                                                                  ANY_BUILD,GET_NO_EXEC);
    }
    
    $exec_id_order = $exec_cfg->history_order;
    $other_execs = null;
    $attachmentInfos = null;
    if($history_on)
    {
        $other_execs = $tcase_mgr->get_executions($tcase_id,$tcversion_id,$tplan_id,$build_id,$exec_id_order);
    }    
    else
    {
        // Warning!!!:
        // we can't use the data we have got with previous call to get_last_execution()
        // because if user have asked to save results last execution data may be has changed
        $aux_map = $tcase_mgr->get_last_execution($tcase_id,$tcversion_id,$tplan_id,$build_id);

        if(!is_null($aux_map))
        {
            $other_execs = array();
            foreach($aux_map as $key => $value )
            {
               $other_execs[$key] = array($value);
            }
        }
    }
    
    // Get attachment,bugs, etc
    if(!is_null($other_execs))
    {
      $other_info=exec_additional_info($db,$tcase_mgr,$other_execs,$tplan_id);
 			$attachmentInfos=$other_info['attachment'];
      $bugs=$other_info['bugs'];
      $cfexec_val_smarty=$other_info['cfexec_values'];
    }

}

$smarty->assign('other_exec_cfexec',$cfexec_val_smarty);
$smarty->assign('bugs_for_exec',$bugs);

$rs = $build_mgr->get_by_id($build_id);
$smarty->assign('build_notes',$rs['notes']);

$editTestResult = ($rs['open']==1) ? "yes" : "no";
$smarty->assign('edit_test_results', $editTestResult);
// -------------------------------------------------------

// 20070105 - franciscom - refactoring
smarty_assign_tsuite_info($smarty,$_REQUEST,$db,$tcase_id);


$smarty->assign('tpn_view_status',
                isset($_POST['tpn_view_status']) ? $_POST['tpn_view_status']:0);
$smarty->assign('bn_view_status',
                isset($_POST['bn_view_status']) ? $_POST['bn_view_status']:0);
$smarty->assign('bc_view_status',
                isset($_POST['bc_view_status']) ? $_POST['bc_view_status']:0);


$smarty->assign('tcAttachments',$tcAttachments);
$smarty->assign('attachments',$attachmentInfos);
$smarty->assign('tSuiteAttachments',$tSuiteAttachments);

$smarty->assign('id',$id);
$smarty->assign('rightsEdit', has_rights($db,"testplan_execute"));
$smarty->assign('map_last_exec', $map_last_exec);
$smarty->assign('other_exec', $other_execs);
$smarty->assign('show_last_exec_any_build', $exec_cfg->show_last_exec_any_build);
$smarty->assign('history_on',$history_on);
$smarty->assign('history_status_btn_name',$history_status_btn_name);
$smarty->assign('att_model',$exec_cfg->att_model);
$smarty->assign('show_last_exec_any_build', $exec_cfg->show_last_exec_any_build);
$smarty->assign('map_last_exec_any_build', $map_last_exec_any_build);
$smarty->assign('build_name', $build_name);
$smarty->assign('owner', $owner);
$smarty->assign('ownerDisplayName', $ownerDisplayName);
$smarty->assign('updated', $submitResult);
$smarty->assign('g_bugInterface', $g_bugInterface);
$smarty->display($g_tpl['execSetResults']);

function manage_history_on($hash_REQUEST,$hash_SESSION,
                           $exec_cfg,$btn_on_name,$btn_off_name,$hidden_on_name)
{
    
    if( isset($hash_REQUEST[$btn_on_name]) )
    {
		$history_on = true;
    }
    elseif(isset($_REQUEST[$btn_off_name]))
    {
		$history_on = false;
    }
    elseif (isset($_REQUEST[$hidden_on_name]))
    {
       $history_on = $_REQUEST[$hidden_on_name];
    }
    elseif (isset($_SESSION[$hidden_on_name]))
    {
       $history_on = $_SESSION[$hidden_on_name];
    }
    else
    {
       $history_on = $exec_cfg->history_on;
    }
    return $history_on;
}




/*
  function: get_ts_name_details

  args :
  
  returns: map with key=TCID
           values= assoc_array([tsuite_id => 5341
                               [details] => my detailas ts1
                               [tcid] => 5343
                               [tsuite_name] => ts1)
*/
function get_ts_name_details(&$db,$tcase_id)
{
	$rs = '';
	$do_query = true;
	$sql="Select ts.id as tsuite_id, ts.details, 
	             nha.id as tc_id, nhb.name as tsuite_name 
	      FROM testsuites ts, nodes_hierarchy nha, nodes_hierarchy nhb
	      WHERE ts.id=nha.parent_id
	      AND   nhb.id=nha.parent_id ";
	if( is_array($tcase_id) && count($tcase_id) > 0)
	{
		$in_list = implode(",",$tcase_id);
		$sql .= "AND nha.id IN (" . $in_list . ")";
	}
	else if(!is_null($tcase_id))
	{
		$sql .= "AND nha.id={$tcase_id}";
	}
	else
	{
		$do_query = false;
	}
	if($do_query)
	{
		$rs = $db->fetchRowsIntoMap($sql,'tc_id');
	}
	
	return $rs;
}

function smarty_assign_tsuite_info(&$smarty,&$request_hash, &$db,$tcase_id)
{

  $tsuite_info = get_ts_name_details($db,$tcase_id);
  $smarty->assign('tsuite_info',$tsuite_info);
  
  // --------------------------------------------------------------------------------
  if(!is_null($tsuite_info))
  {
    $a_tsvw=array();
    $a_ts=array();
    $a_tsval=array();
   
    $gui_cfg = config_get('gui');
    $tsuite_mgr = New testsuite($db);
    
    foreach($tsuite_info as $key => $elem)
    {
      $main_k = 'tsdetails_view_status_' . $key;
      $a_tsvw[] = $main_k;
      $a_ts[] = 'tsdetails_' . $key;
      $a_tsval[] = isset($request_hash[$main_k]) ? $request_hash[$main_k] : 0;
   
      if( $gui_cfg->enable_custom_fields ) 
      {
        $tsuite_id = $elem['tsuite_id'];
        $tc_id = $elem['tc_id'];
        if(!isset($cached_cf[$tsuite_id]))
           $cached_cf[$tsuite_id] = $tsuite_mgr->html_table_of_custom_field_values($tsuite_id);

        $ts_cf_smarty[$tc_id] = $cached_cf[$tsuite_id];
      }
   
    }
    $smarty->assign('tsd_div_id_list',implode(",",$a_ts));
    $smarty->assign('tsd_hidden_id_list',implode(",",$a_tsvw));
    $smarty->assign('tsd_val_for_hidden_list',implode(",",$a_tsval));
  
	$smarty->assign('ts_cf_smarty',$ts_cf_smarty);
  }

}  
// --------------------------------------------------------------------------------

function exec_additional_info(&$db,&$tcase_mgr,$other_execs,$tplan_id)
{
  $bugInterfaceOn = config_get('bugInterfaceOn');
  $bugInterface = config_get('bugInterface');
  $attachmentInfos = null;
  $bugs = null;
  $cfexec_values = null;

  foreach($other_execs as $tcversion_id => $execInfo)
  {
    $num_elem = sizeof($execInfo);   
  	for($idx = 0;$idx < $num_elem;$idx++)
  	{
  		$exec_id = $execInfo[$idx]['execution_id'];
  		
  		$aInfo = getAttachmentInfos($db,$exec_id,'executions',true,1);
  		if ($aInfo)
  			$attachmentInfos[$exec_id] = $aInfo;
  		
  		if($bugInterfaceOn)
  		{
			$the_bugs = get_bugs_for_exec($db,$bugInterface,$exec_id);
			if(count($the_bugs) > 0)
				$bugs[$exec_id] = $the_bugs;
  		}

     
      // Custom fields
      $cfexec_values[$exec_id] = $tcase_mgr->html_table_of_custom_field_values($tcversion_id,'execution',null,
                                                                               $exec_id,$tplan_id);
  	}
  }
  
  $info = array( 'attachment' => $attachmentInfos,
               'bugs' => $bugs,
               'cfexec_values' => $cfexec_values);      
               
  return $info;
}
?>																																
