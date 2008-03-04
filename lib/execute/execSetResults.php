<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: execSetResults.php,v $
 *
 * @version $Revision: 1.86 $
 * @modified $Date: 2008/03/04 07:30:53 $ $Author: franciscom $
 *
 * 20080224 - franciscom - to avoid performance problems
 *                         clicking on root node will NOT try to display
 *                         all testcases in testplan.
 *
 * 20080104 - franciscom - REQ 1232 - web editor on execution notes
 *                         added createExecNotesWebEditor()
 * 20071224 - franciscom - refactoring
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
require_once("web_editor.php");

testlinkInitPage($db);

$template_dir = 'execute/';

$cfg=init_config();

$tcversion_id = null;
$PID_NOT_NEEDED = null;
$SHOW_ON_EXECUTION = 1;
$testdata = array();
$ts_cf_smarty = '';
$submitResult = null;

$args = init_args();

$smarty = new TLSmarty();
$tree_mgr = new tree($db);
$tplan_mgr = new testplan($db);
$tcase_mgr = new testcase($db);
$build_mgr = new build_mgr($db);
$exec_cfield_mgr = new exec_cfield_mgr($db,$args->tproject_id);

$build_info = $build_mgr->get_by_id($args->build_id);

$exec_mode = initializeExecMode($db,$cfg->exec_cfg,$args->user,$args->tproject_id,$args->tplan_id);
$has_exec_right = (has_rights($db,"testplan_execute")=="yes" ? 1 : 0);

$do_show_instructions=(strlen($args->level) == 0 || $args->level=='testproject') ? 1 : 0;
if ($do_show_instructions)
{
    show_instructions('executeTest');
	  exit();
}

$ownerDisplayName = null;
if ($args->filter_assigned_to)
{
	$user = tlUser::getById($db,$args->filter_assigned_to);
	if ($user)
		$ownerDisplayName = $user->getDisplayName();
}

$the_builds = $tplan_mgr->get_builds_for_html_options($args->tplan_id);
$build_name = isset($the_builds[$args->build_id]) ? $the_builds[$args->build_id] : '';

$history_on = manage_history_on($_REQUEST,$_SESSION,$cfg->exec_cfg,'btn_history_on','btn_history_off','history_on');
$_SESSION['history_on'] = $history_on;

$history_status_btn_name = $history_on ? 'btn_history_off' : 'btn_history_on';
$testplan_cf = null;
$cfexec_val_smarty = null;
$bugs = null;
$attachmentInfos = null;
$map_last_exec = null;
$other_execs = null;
$map_last_exec_any_build = null;
$tcAttachments = null;
$tSuiteAttachments = null;

$get_mode = GET_ONLY_EXECUTED;
if(is_null($args->filter_status) || $args->filter_status == $cfg->tc_status['not_run'])
	$get_mode = GET_ALSO_NOT_EXECUTED;

// ---------------------------------------------------------
// Testplan executions and result archiving. Checks whether execute cases button was clicked
//
if($args->doExec == 1)
{
	/** @note Retrive testcase ids in an array */
	if(!is_null($args->tc_versions) && count($args->tc_versions))
	{
		$status_and_notes=do_remote_execution($db,$args->tc_versions);

		// Need to be added to $_REQUEST, because we are using $_REQUEST as input
		// for the function responsible of writing exec results. write_execution()
		$status_map = $status_and_notes['status'];
		$notes_map = $status_and_notes['notes'];

		if(count($status_map))
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
// 20080224 - franciscom - BUGID 1056
// 20070306 - franciscom - BUGID 705
// 20070914 - jbarchibald - added $cf_selected parameter
$linked_tcversions = $tplan_mgr->get_linked_tcversions($args->tplan_id,$args->tc_id,$args->keyword_id,$get_mode,
                                                       $args->filter_assigned_to,
                                                       $args->filter_status,$args->build_id,
                                                       $args->cf_selected,$args->include_unassigned);

$tcase_id = 0;

// -------------------------------------------------
$rs = $tplan_mgr->get_by_id($args->tplan_id);
$testplan_cf = $tplan_mgr->html_table_of_custom_field_values($args->tplan_id,'execution',FILTER_BY_SHOW_ON_EXECUTION);

// 20071224 - think is not needed I can use what is defined on SESSION
// $testproject_id=$rs['parent_id'];
$smarty->assign('tplan_notes',$rs['notes']);
$smarty->assign('tplan_cf',$testplan_cf);

$smarty->assign('include_unassigned',$args->include_unassigned);

$attachmentRepository = tlAttachmentRepository::create($db);
if(!is_null($linked_tcversions))
{
	  $items_to_exec = array();
	  $_SESSION['s_lastAttachmentInfos'] = null;
	  if($args->level == 'testcase')
    {
		  $cf_smarty = '';
  		$cfexec_smarty = '';
  		
  		$items_to_exec[$args->id] = $linked_tcversions[$args->id]['tcversion_id'];    
  		$tcase_id = $args->id;
  		$tcversion_id = $linked_tcversions[$args->id]['tcversion_id'];
  		$tcAttachments[$args->id] = getAttachmentInfos($attachmentRepository,$args->id,'nodes_hierarchy',1);
      $tcasePrefix=$tcase_mgr->getPrefix($tcase_id);
      
  		if($cfg->gui_cfg->enable_custom_fields)
  		{
  			$cf_smarty[$args->id] = $tcase_mgr->html_table_of_custom_field_values($args->id,'design',$SHOW_ON_EXECUTION);
  			
        // BUGID 856: Guest user can execute test case
  			if($has_exec_right)
  			{
  			   $cfexec_smarty[$args->id] = $tcase_mgr->html_table_of_custom_field_inputs($args->id,$PID_NOT_NEEDED,
  			                                                                       'execution',"_{$args->id}");
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
        $tsuite_data = $tsuite_mgr->get_by_id($args->id);
        
        $out = gen_spec_view($db,'testplan',$args->tplan_id,$args->id,$tsuite_data['name'],
                             $linked_tcversions,null,$args->keyword_id,
                             FILTER_BY_TC_OFF,WRITE_BUTTON_ONLY_IF_LINKED,DO_PRUNE);
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
        $tcasePrefix=$tcase_mgr->getPrefix($tcase_id[0]);
        // ---------------------------------------------------------------------------------

		    // Get the path for every test case, grouping test cases that
		    // have same parent.
		    $idx = 0;
    	  foreach($linked_tcversions as $item)
    	  {
    	  	$path_f = $tree_mgr->get_path($item['tc_id'],null,'full');
    	  	foreach($path_f as $key => $path_elem)
    	  	{
    	  		if( $path_elem['parent_id'] == $args->id )
    	  		{
			  		 // Can be added because is present in the branch the user wants to view
			  		 // ID of branch starting node is in $args->id
			  		 $tcAttachments[$item['tc_id']] = getAttachmentInfos($attachmentRepository,$item['tc_id'],'nodes_hierarchy',true,1);
        
		         // --------------------------------------------------------------------------------------
			       if($cfg->gui_cfg->enable_custom_fields)
			       {
			  				$cf_smarty[$item['tc_id']] = $tcase_mgr->html_table_of_custom_field_values($item['tc_id'],
			  				                                                                        'design',$SHOW_ON_EXECUTION);
			  				                                                                        
               // BUGID 856: Guest user can execute test case
        			 if($has_exec_right)
  		  	     {
			  				$cfexec_smarty[$item['tc_id']] = $tcase_mgr->html_table_of_custom_field_inputs($item['tc_id'],
			  				                                                            $PID_NOT_NEEDED,'execution',
			  				                                                            "_".$item['tc_id']);
               }
			       }
			       $smarty->assign('design_time_cf',$cf_smarty);	
			       $smarty->assign('execution_time_cf',$cfexec_smarty);	
			       // --------------------------------------------------------------------------------------
    	  		} // if( $path_elem['parent_id'] == $args->id )
    	  		
			  	  if($path_elem['node_table'] == 'testsuites' && !isset($tSuiteAttachments[$path_elem['id']]))
			  		   $tSuiteAttachments[$path_elem['id']] = getAttachmentInfos($attachmentRepository,$path_elem['id'],'nodes_hierarchy',true,1);
			  		   
			    } //foreach($path_f as $key => $path_elem) 
    	  } // foreach($linked_tcversions as $item)
    }
    
    
    // will create a record even if the testcase version has not been executed (GET_NO_EXEC)
    $map_last_exec = $tcase_mgr->get_last_execution($tcase_id,$tcversion_id,$args->tplan_id,$args->build_id,GET_NO_EXEC);
    
    // --------------------------------------------------------------------------------------------
    // Results to DB
    if ($args->save_results || $args->do_bulk_save )
    {
      // 20070105 - added $testproject_id
      // 20071224 - $testproject_id -> args->tproject_id 
    	$submitResult = write_execution($db,$args->user->dbID,$_REQUEST,$args->tproject_id,
    	                                $args->tplan_id,$args->build_id,$map_last_exec);
    }

    if ($args->doDelete)
    {
    	delete_execution($db,$args->exec_to_delete);
    }
    // --------------------------------------------------------------------------------------------
    
    $map_last_exec_any_build = null;
    if( $cfg->exec_cfg->show_last_exec_any_build )
    {
        $map_last_exec_any_build = $tcase_mgr->get_last_execution($tcase_id,$tcversion_id,$args->tplan_id,
                                                                  ANY_BUILD,GET_NO_EXEC);
    }
    
    $exec_id_order = $cfg->exec_cfg->history_order;
    $other_execs = null;
    $attachmentInfos = null;
    if($history_on)
    {
      // 20071113 - Contribution
      $build_id_filter=$args->build_id;
      if($cfg->exec_cfg->show_history_all_builds )
      {
        $build_id_filter=ANY_BUILD;
      }  
      $other_execs = $tcase_mgr->get_executions($tcase_id,$tcversion_id,$args->tplan_id,$build_id_filter,$exec_id_order);
    }    
    else
    {
        // Warning!!!:
        // we can't use the data we have got with previous call to get_last_execution()
        // because if user have asked to save results last execution data may be has changed
        $aux_map = $tcase_mgr->get_last_execution($tcase_id,$tcversion_id,$args->tplan_id,$args->build_id);

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
      $other_info=exec_additional_info($db,$attachmentRepository,$tcase_mgr,$other_execs,$args->tplan_id);
 			$attachmentInfos=$other_info['attachment'];
      $bugs=$other_info['bugs'];
      $cfexec_val_smarty=$other_info['cfexec_values'];
    }

}


if( !is_null($map_last_exec) )
{
  $map_last_exec=setTesterAssignment($db,$map_last_exec,$tcase_mgr,$args->tplan_id);

  // Warning: setCanExecute() must be called AFTER setTesterAssignment()  
  $can_execute=$has_exec_right && ($build_info['is_open']==1);
  $map_last_exec=setCanExecute($map_last_exec,$exec_mode,$can_execute,$args->user->dbID);
}

// --------------------------------------------------------------------
// Reorder executions to mantaing correct visualization order.
if( is_array($tcversion_id) )
{
  $map_last_exec=reorderExecutions($tcversion_id,$map_last_exec);
}
// --------------------------------------------------------------------

$execNotesInputs=createExecNotesWebEditor($map_last_exec,$_SESSION['basehref']);

smarty_assign_tsuite_info($smarty,$_REQUEST,$db,$tcase_id);
$smarty->assign('cfg',$cfg);
$smarty->assign('tcasePrefix',$tcasePrefix . $cfg->testcase_cfg->glue_character);
$smarty->assign('execution_types',$tcase_mgr->get_execution_types());
$smarty->assign('exec_notes_editors', $execNotesInputs);
$smarty->assign('exec_mode', $exec_mode);
$smarty->assign('other_exec_cfexec',$cfexec_val_smarty);
$smarty->assign('bugs_for_exec',$bugs);
$smarty->assign('build_notes',$build_info['notes']);
$smarty->assign('build_is_open', ($build_info['is_open'] == 1 ? 1 : 0));
$smarty->assign('tpn_view_status',$args->tpn_view_status);
$smarty->assign('bn_view_status',$args->bn_view_status);
$smarty->assign('bc_view_status',$args->bc_view_status);
$smarty->assign('enable_custom_field',$cfg->gui_cfg->enable_custom_fields);
$smarty->assign('can_delete_execution',$cfg->exec_cfg->can_delete_execution);
$smarty->assign('default_status',config_get('tc_status_for_ui_default'));
$smarty->assign('alluserInfo',tlUser::getAll($db,null,'id'));
$smarty->assign('tcAttachments',$tcAttachments);
$smarty->assign('attachments',$attachmentInfos);
$smarty->assign('tSuiteAttachments',$tSuiteAttachments);
$smarty->assign('id',$args->id);
$smarty->assign('has_exec_right', $has_exec_right);
$smarty->assign('map_last_exec', $map_last_exec);
$smarty->assign('other_exec', $other_execs);
$smarty->assign('show_last_exec_any_build', $cfg->exec_cfg->show_last_exec_any_build);
$smarty->assign('history_on',$history_on);
$smarty->assign('history_status_btn_name',$history_status_btn_name);
$smarty->assign('att_model',$cfg->exec_cfg->att_model);
$smarty->assign('show_last_exec_any_build', $cfg->exec_cfg->show_last_exec_any_build);
$smarty->assign('show_history_all_builds', $cfg->exec_cfg->show_history_all_builds);
$smarty->assign('map_last_exec_any_build', $map_last_exec_any_build);
$smarty->assign('build_name', $build_name);
$smarty->assign('owner', $args->filter_assigned_to);
$smarty->assign('ownerDisplayName', $ownerDisplayName);
$smarty->assign('updated', $submitResult);
$smarty->assign('g_bugInterface', $g_bugInterface);
$smarty->assign('tester_id',$args->user->dbID);
$smarty->display($template_dir . $g_tpl['execSetResults']);
?>

<?php
/*
  function: 

  args:
  
  returns: 
  
  rev:
      schlundus: changed the user_id to the currentUser of the session
*/
function init_args()
{
 	$_REQUEST = strings_stripSlashes($_REQUEST);
	$args->doExec = isset($_REQUEST['execute_cases']) ? 1 : 0;
	$args->doDelete = isset($_REQUEST['do_delete']) ? 1 : 0;
	$args->cf_selected = isset($_REQUEST['cfields']) ? unserialize($_REQUEST['cfields']) : null;
	$args->tc_versions = isset($_REQUEST['tc_version']) ? $_REQUEST['tc_version'] : null;  

	$key2loop = array('id' => 0,'build_id' =>0, 'keyword_id' => 0, 'exec_to_delete' => 0, 
				            'tpn_view_status' => 0, 'bn_view_status' => 0, 'bc_view_status' => 0, 
				            'save_results' => 0, 'do_bulk_save' => 0,
				            'tc_id' => null, 'filter_assigned_to' => null, 'filter_status' => null);

	foreach($key2loop as $key => $value)
	{
		$args->$key = isset($_REQUEST[$key]) ? intval($_REQUEST[$key]) : $value;
	}

	$key2loop = array('level' => '','status' => null);
	foreach($key2loop as $key => $value)
	{
		$args->$key = isset($_REQUEST[$key]) ? $_REQUEST[$key] : $value;
	}

  // Checkbox
  $args->include_unassigned=isset($_REQUEST['include_unassigned']) ? $_REQUEST['include_unassigned'] : 0;

	$args->tproject_id = $_SESSION['testprojectID'];
	$args->tplan_id = $_SESSION['testPlanId'];
	$args->user = $_SESSION['currentUser'];

	return $args;  
}


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
	$sql = "Select ts.id as tsuite_id, ts.details, 
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
		$executionResults[$tcase_id] = executeTestCase($tcase_id,$tree_mgr,$cfield_mgr);
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

	}
	
	return $ret;
}


/*
  function: 

  args:
  
  returns: 

*/
function initializeExecMode(&$db,$exec_cfg,$user,$tproject_id,$tplan_id)
{
    $effective_role = $user->getEffectiveRole($db,$tproject_id,$tplan_id);
	  
	  // SCHLUNDUS: hmm, for user defined roles, this wont work correctly
	  // 20080104 - franciscom - Please explain why do you think will not work ok ?
	  //                         do you prefer to check for exec right ?
	  //
	  // SCHLUNDUS: jep, exactly. If a user defines it own roles than a check for the tester
	  // role will not do the desired effect of putting the logged in user in tester-view-mode
	  // instead we must check for presence (and or absence) the right(s) which mades a user a tester 
	  return ($effective_role->dbID == TL_ROLES_TESTER) ?  $exec_cfg->exec_mode->tester : 'all';
} // function end


/*
  function: 

  args:
  
  returns: 

*/
function setTesterAssignment(&$db,$exec_info,&$tcase_mgr,$tplan_id)
{     
	foreach($exec_info as $version_id => $value)
	{
		$exec_info[$version_id]['assigned_user'] = '';
		$exec_info[$version_id]['assigned_user_id'] = 0;
		$p3 = $tcase_mgr->get_version_exec_assignment($version_id,$tplan_id);
		
		$assignedTesterId = intval($p3[$version_id]['user_id']);
		
		if($assignedTesterId)
		{
			$user = tlUser::getByID($db,$assignedTesterId);
			if ($user)
			{
				$exec_info[$version_id]['assigned_user']= $user->getDisplayName();  
			}
			$exec_info[$version_id]['assigned_user_id'] = $assignedTesterId;
		}  
	}
	return $exec_info;
} //function end

/*
  function: 
           Reorder executions to mantaing correct visualization order.

  args:
  
  returns: 

*/
function reorderExecutions(&$tcversion_id,&$exec_info)
{
    $dummy = array();
    foreach($tcversion_id as $key => $value)
    {
       $dummy[$key] = $exec_info[$value];    
    }
    return $dummy;    
}

/*
  function: 

  args:
  
  returns: 

*/
function setCanExecute($exec_info,$execution_mode,$can_execute,$tester_id)
{     
	foreach($exec_info as $key => $tc_exec) 
	{
		$execution_enabled = 0;  
		if($can_execute == 1 && $tc_exec['active'] == 1)
		{
			$assigned_to_me = $tc_exec['assigned_user_id'] == $tester_id ? 1 : 0;
			$is_free = $tc_exec['assigned_user_id'] == '' ? 1 : 0;

			switch($execution_mode)
			{
				case 'assigned_to_me':
					$execution_enabled = $assigned_to_me;
					break;

				case 'assigned_to_me_or_free':
					$execution_enabled = $assigned_to_me || $is_free;
					break;

				case 'all':
					$execution_enabled = 1;
					break;

				default:
					$execution_enabled = 0;  
					break;
			} // switch
		}
		$exec_info[$key]['can_be_executed']=$execution_enabled;
	}
	return $exec_info;
} //function end


/*
  function: createExecNotesWebEditor
            creates map of html needed to display web editors
            for execution notes.
            
  args: tcversions: array where each element has information
                    about testcase version that can be executed.
                    
        basehref: URL            
  
  returns: map
           key: testcase id
           value: html to display web editor.

  rev : 20080104 - creation  
*/
function createExecNotesWebEditor(&$tcversions,$basehref)
{
  
    if(is_null($tcversions) || count($tcversions) == 0 )
    {
        return null;  // nothing todo >>>------> bye!  
    }
     
    // Important Notice:
    //
    // When using tinymce or none as web editor, we need to set rows and cols
    // to appropriate values, to avoid an ugly ui.
    // null => use default values defined on editor class file
    //
    // Rows and Cols values are useless for FCKeditor.
    //
    //
    $a_oWebEditor_cfg = array('summary' => array('rows'=> null,'cols' => null),
                              'steps' => array('rows'=> null,'cols' => 38) ,
                              'expected_results' => array('rows'=> null,'cols' => 38));


    $idx=0;
    foreach($tcversions as $key => $tcv)
    {
        $tcversion_id=$tcv['id'];
        $tcase_id=$tcv['testcase_id'];

        $of=web_editor("notes[{$tcversion_id}]",$basehref) ;
        $of->Value = null;
        
        // Magic numbers that can be determined by trial and error
        $editors[$tcase_id]=$of->CreateHTML(10,60);         
        unset($of);
    }
    return $editors;
}



/*
  function: init_config 

  args:
  
  returns: 

*/
function init_config()
{
    $cfg = new stdClass();
    $cfg->exec_cfg = config_get('exec_cfg');
    $cfg->gui_cfg = config_get('gui');
    $cfg->tc_status = config_get('tc_status'); 
    $cfg->testcase_cfg = config_get('testcase_cfg'); 
    return $cfg;
}

?>																																
