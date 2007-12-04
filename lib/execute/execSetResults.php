<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: execSetResults.php,v $
 *
 * @version $Revision: 1.72 $
 * @modified $Date: 2007/12/04 09:20:45 $ $Author: franciscom $
 *
 * 20071113 - franciscom - added contribution History for all builds.
 * 20071006 - franciscom - changes on exec_cfield_mgr() call
 * 20071002 - jbarchibald - BUGID 1051
 * 20070707 - franciscom - BUGID 921
 * 20070519 - franciscom - BUGID 856
 * 20070306 - franciscom - BUGID 705
 * 20070222 - franciscom - BUGID 647
 *
**/
require_once('../../config.inc.php');
require_once('common.php');
require_once('exec.inc.php');
require_once("builds.inc.php");
require_once("attachments.inc.php");

testlinkInitPage($db);

$template_dir='execute/';
$tcversion_id=null;

$smarty = new TLSmarty();

$PID_NOT_NEEDED = null;
$SHOW_ON_EXECUTION = 1;

$tproject_id = $_SESSION['testprojectID'];
$tplan_id = $_SESSION['testPlanId'];
$user_id = $_SESSION['userID'];

$exec_cfg = config_get('exec_cfg');
$gui_cfg = config_get('gui');
$tc_status = config_get('tc_status'); 

// BUGID 647
$smarty->assign('enable_custom_field',$gui_cfg->enable_custom_fields);

$tree_mgr = new tree($db);
$tplan_mgr = new testplan($db);
$tcase_mgr = new testcase($db);
$build_mgr = new build_mgr($db);

// 20070914 - jbarchibald
$exec_cfield_mgr = new exec_cfield_mgr($db,$tproject_id);


$effective_role=get_effective_role($db,$user_id,$tproject_id,$tplan_id);
$all_roles = getAllRoles($db);
$exec_mode='all';
if( $all_roles[$effective_role] == 'tester' )
{
  $exec_cfg = config_get('exec_cfg');
  $exec_mode=$exec_cfg->exec_mode->tester;
}

$can_exec=(has_rights($db,"testplan_execute")=="yes"?1:0);


$testdata = array();
$ts_cf_smarty = '';
$submitResult = null;




$_REQUEST = strings_stripSlashes($_REQUEST);
$do_exec = isset($_REQUEST['execute_cases']) ? 1 : 0;
$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
$build_id = isset($_REQUEST['build_id']) ? intval($_REQUEST['build_id']) : 0;
$tc_id = isset($_REQUEST['tc_id']) ? intval($_REQUEST['tc_id']) : null;
$keyword_id = isset($_REQUEST['keyword_id']) ? intval($_REQUEST['keyword_id']) : 0;

$filter_assigned_to = isset($_REQUEST['filter_assigned_to']) ? intval($_REQUEST['filter_assigned_to']) : null;
$filter_status = isset($_REQUEST['filter_status']) ? $_REQUEST['filter_status'] : null;

$level = isset($_REQUEST['level']) ? $_REQUEST['level'] : '';
$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : null;

// jbarchibald 20070911 - adding custom field filtering
$cf_selected = isset($_REQUEST['cfields']) ? unserialize($_REQUEST['cfields']) : null;


// 20070211 - franciscom
$do_delete=isset($_REQUEST['do_delete']) ? intval($_REQUEST['do_delete']) : 0;
if($do_delete)
{
  $exec_to_delete =isset($_REQUEST['exec_to_delete']) ? intval($_REQUEST['exec_to_delete']) : 0;
}

if (!strlen($level))
{
  	redirect($_SESSION['basehref'] . "/lib/general/show_help.php?help=executeTest&locale={$_SESSION['locale']}");
	exit();
}
$ownerDisplayName = null;
if ($filter_assigned_to)
	$ownerDisplayName = getUserName($db,$filter_assigned_to);


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


$get_mode=GET_ONLY_EXECUTED;
if( is_null($filter_status) || $filter_status == $tc_status['not_run'])
{
  $get_mode=GET_ALSO_NOT_EXECUTED;
}

// ---------------------------------------------------------
// Testplan executions and result archiving. Checks whether execute cases button was clicked
//

if($do_exec == 1)
{
	/** @note Retrive testcase ids in an array */
	$tc_versions = array();
	$tc_versions = isset($_REQUEST['tc_version']) ? $_REQUEST['tc_version'] : null;
	if( !is_null($tc_versions) && count($tc_versions) > 0)
	{
	   $status_and_notes=do_remote_execution($db,$tc_versions);
	   
	   // Need to be added to $_REQUEST, because we are using $_REQUEST as input
	   // for the function responsible of writing exec results. write_execution()
	   $status_map=$status_and_notes['status'];
	   $notes_map=$status_and_notes['notes'];
	   
	   if( count($status_map) > 0 )
	   {
	      foreach($status_map as $key => $value)
	      {
	        $_REQUEST['status'][$key]=$value;  
	        $_REQUEST['notes'][$key]=$notes_map[$key];  
	      } 
	   }
	   
	}
}	
// -----------------------------------------------------------





// 20070306 - franciscom - BUGID 705
// 20070914 - jbarchibald - added $cf_selected parameter
$linked_tcversions = $tplan_mgr->get_linked_tcversions($tplan_id,$tc_id,$keyword_id,$get_mode,
                                                       $filter_assigned_to,$filter_status,$build_id,$cf_selected);

$tcase_id = 0;

// -------------------------------------------------
$rs = $tplan_mgr->get_by_id($tplan_id);
$testplan_cf=$tplan_mgr->html_table_of_custom_field_values($tplan_id,'execution',FILTER_BY_SHOW_ON_EXECUTION);
$testproject_id=$rs['parent_id'];
$smarty->assign('tplan_notes',$rs['notes']);
$smarty->assign('tplan_cf',$testplan_cf);

$attachmentRepository = tlAttachmentRepository::create($db);
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
  		$tcAttachments[$id] = getAttachmentInfos($attachmentRepository,$id,'nodes_hierarchy',1);
   
  		if($gui_cfg->enable_custom_fields)
  		{
  			$cf_smarty[$id] = $tcase_mgr->html_table_of_custom_field_values($id,'design',$SHOW_ON_EXECUTION);
  			
        // BUGID 856: Guest user can execute test case
  			if($can_exec)
  			{
  			   $cfexec_smarty[$id] = $tcase_mgr->html_table_of_custom_field_inputs($id,$PID_NOT_NEEDED,
  			                                                                       'execution',"_{$id}");
  			}

  		}
  		$smarty->assign('design_time_cf',$cf_smarty);
  		$smarty->assign('execution_time_cf',$cfexec_smarty);	

      // 20070405 - BUGID 766
      $tc_info=$tree_mgr->get_node_hierachy_info($tcase_id);
	    $tSuiteAttachments[$tc_info['parent_id']] = getAttachmentInfos($attachmentRepository,$tc_info['parent_id'],
		                                                                 'nodes_hierarchy',true,1);

    }
    else
    {
      // ---------------------------------------------------------------------------------
      // 20070708 - franciscom
      $tsuite_mgr=new testsuite($db); 
          $tsuite_data = $tsuite_mgr->get_by_id($id);
        
          $out = gen_spec_view($db,'testplan',$tplan_id,$id,$tsuite_data['name'],
                           $linked_tcversions,
                           null,
                           $keyword_id,FILTER_BY_TC_OFF,WRITE_BUTTON_ONLY_IF_LINKED,DO_PRUNE);
      $tcase_id = array();
    	$tcversion_id = array();
      foreach($out['spec_view'] as $key => $value)
      {
         if( count($value['testcases']) > 0 )
         {
           foreach($value['testcases'] as $xkey => $xvalue)
           {
             $tcase_id[]=$xkey;
             $tcversion_id[]=$xvalue['linked_version_id'];
           }  
         }
      }
      // ---------------------------------------------------------------------------------

		  // Get the path for every test case, grouping test cases that
		  // have same parent.
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
					 $tcAttachments[$item['tc_id']] = getAttachmentInfos($attachmentRepository,$item['tc_id'],'nodes_hierarchy',true,1);

		       // --------------------------------------------------------------------------------------
			     if($gui_cfg->enable_custom_fields)
			     {
							$cf_smarty[$item['tc_id']] = $tcase_mgr->html_table_of_custom_field_values($item['tc_id'],
							                                                                        'design',$SHOW_ON_EXECUTION);
							                                                                        
             // BUGID 856: Guest user can execute test case
      			 if($can_exec)
  			     {
							$cfexec_smarty[$item['tc_id']] = $tcase_mgr->html_table_of_custom_field_inputs($item['tc_id'],
							                                                            $PID_NOT_NEEDED,'execution',
							                                                            "_".$item['tc_id']);
             }
			     }
			     $smarty->assign('design_time_cf',$cf_smarty);	
			     $smarty->assign('execution_time_cf',$cfexec_smarty);	
			     // --------------------------------------------------------------------------------------
    			} // if( $path_elem['parent_id'] == $id )
    			
				  if($path_elem['node_table'] == 'testsuites' && !isset($tSuiteAttachments[$path_elem['id']]))
					   $tSuiteAttachments[$path_elem['id']] = getAttachmentInfos($attachmentRepository,$path_elem['id'],'nodes_hierarchy',true,1);
					   
			  } //foreach($path_f as $key => $path_elem) 
    	} // foreach($linked_tcversions as $item)
    }
    
    
    // will create a record even if the testcase version has not been executed (GET_NO_EXEC)
    $map_last_exec = $tcase_mgr->get_last_execution($tcase_id,$tcversion_id,$tplan_id,$build_id,GET_NO_EXEC);
    
    // --------------------------------------------------------------------------------------------
    // Results to DB
    if (isset($_REQUEST['save_results']) || isset($_REQUEST['do_bulk_save']))
    {
      // 20070105 - added $testproject_id
    	$submitResult = write_execution($db,$user_id,$_REQUEST,$testproject_id,$tplan_id,$build_id,$map_last_exec);
    }

    if ($do_delete)
    {
      // 20070105 - added $testproject_id
    	delete_execution($db,$exec_to_delete);
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
      // 20071113 - Contribution
      $build_id_filter=$build_id;
      if($exec_cfg->show_history_all_builds )
      {
        $build_id_filter=ANY_BUILD;
      }  
      $other_execs = $tcase_mgr->get_executions($tcase_id,$tcversion_id,$tplan_id,$build_id_filter,$exec_id_order);
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
      $other_info=exec_additional_info($db,$attachmentRepository,$tcase_mgr,$other_execs,$tplan_id);
 			$attachmentInfos=$other_info['attachment'];
      $bugs=$other_info['bugs'];
      $cfexec_val_smarty=$other_info['cfexec_values'];
    }

}


// tester assignment 
if( !is_null($map_last_exec) )
{
  foreach($map_last_exec as $version_id => $value)
  {
    $map_last_exec[$version_id]['assigned_user']='';
    $map_last_exec[$version_id]['assigned_user_id']=0;
    $p3 = $tcase_mgr->get_version_exec_assignment($version_id,$tplan_id);
    if(intval($p3[$version_id]['user_id']) > 0 )
    {
      $user_data=getUserById($db,$p3[$version_id]['user_id']);
      $map_last_exec[$version_id]['assigned_user']=format_username($user_data[0]);  
      $map_last_exec[$version_id]['assigned_user_id']=$p3[$version_id]['user_id'];
    }  
  }
}

// --------------------------------------------------------------------
// Reorder executions to mantaing correct visualization order.
$dummy=array();
if( is_array($tcversion_id) )
{
  foreach($tcversion_id as $key => $value)
  {
     $dummy[$key]=$map_last_exec[$value];    
  }
  $map_last_exec=null;
  $map_last_exec=$dummy;
}
// --------------------------------------------------------------------


$smarty->assign('other_exec_cfexec',$cfexec_val_smarty);
$smarty->assign('bugs_for_exec',$bugs);

$rs = $build_mgr->get_by_id($build_id);
$smarty->assign('build_notes',$rs['notes']);

$editTestResult = ($rs['is_open']==1) ? "yes" : "no";
$smarty->assign('edit_test_results', $editTestResult);
// -------------------------------------------------------

$smarty->assign('exec_mode', $exec_mode);


// 20070105 - franciscom - refactoring
smarty_assign_tsuite_info($smarty,$_REQUEST,$db,$tcase_id);


$smarty->assign('tpn_view_status',
                isset($_POST['tpn_view_status']) ? $_POST['tpn_view_status']:0);
$smarty->assign('bn_view_status',
                isset($_POST['bn_view_status']) ? $_POST['bn_view_status']:0);
$smarty->assign('bc_view_status',
                isset($_POST['bc_view_status']) ? $_POST['bc_view_status']:0);

$smarty->assign('can_delete_execution',$exec_cfg->can_delete_execution);
$smarty->assign('default_status',config_get('tc_status_for_ui_default'));

$smarty->assign('alluserInfo',getAllUsers($db,null,'id'));
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
$smarty->assign('show_history_all_builds', $exec_cfg->show_history_all_builds);
$smarty->assign('map_last_exec_any_build', $map_last_exec_any_build);
$smarty->assign('build_name', $build_name);
$smarty->assign('owner', $filter_assigned_to);
$smarty->assign('ownerDisplayName', $ownerDisplayName);
$smarty->assign('updated', $submitResult);
$smarty->assign('g_bugInterface', $g_bugInterface);

$smarty->assign('tester_id',$user_id);

$smarty->display($template_dir . $g_tpl['execSetResults']);
?>



<?php
/*
  function: 

  args :
  
  returns: 

*/
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

/*
  function: 

  args :
  
  returns: 

*/
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
        {
           $cached_cf[$tsuite_id] = $tsuite_mgr->html_table_of_custom_field_values($tsuite_id);
        }
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


/*
  function: 

  args :
  
  returns: 

*/
function exec_additional_info(&$db,$attachmentRepository,&$tcase_mgr,$other_execs,$tplan_id)
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
  		
  		$aInfo = getAttachmentInfos($attachmentRepository,$exec_id,'executions',true,1);
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
} //function end


/*
  function: 

  args :
  
  returns: 

*/
function do_remote_execution(&$db,$tc_versions)
{
  $tc_status = config_get('tc_status');
  $tree_mgr = new tree($db);
  $cfield_mgr = new cfield_mgr($db);
  
	$ret=array();
  $ret["status"]=array();
	$ret["notes"]=array();

	$executionResults = array();

	$myResult = array();
	foreach($tc_versions as $version_id => $tcase_id)
	{
		// RPC call
		$executionResults[$tcase_id] =  executeTestCase($tcase_id,$tree_mgr,$cfield_mgr);
		if($executionResults){
			$myResult = $executionResults[$tcase_id]['result'];
			$myNotes = $executionResults[$tcase_id]['notes'];
			if ($myResult != -1 and $myNotes != -1) {
				$db_now = $db->db_now();
				$my_notes = $db->prepare_string(trim($myNotes));
				$my_result = strtolower($myResult);
				$my_result = $my_result{0};
				if( $my_result != $tc_status['passed'] && 
				    $my_result != $tc_status['failed'] && 
				    $my_result != $tc_status['blocked']){
					$my_result = $tc_status['blocked'];
				}
				// 
				$ret["status"][$version_id] = $myResult;
				$ret["notes"][$version_id] = $my_notes;
				//
				$sql = "INSERT INTO executions (build_id,tester_id,status,testplan_id,tcversion_id,execution_ts,notes) ".
				       "VALUES ({$build_id},{$user_id},'{$my_result}',{$tplan_id},{$version_id},{$db_now},'{$my_notes}')";
				$db->exec_query($sql);
			}
		}

	} //foreach($tc_versions as $version_id => $tcase_id)
	
	return $ret;
} //function end
?>																																
