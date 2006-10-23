<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: execSetResults.php,v $
 *
 * @version $Revision: 1.42 $
 * @modified $Date: 2006/10/23 20:11:28 $ $Author: schlundus $
 *
**/
require_once('../../config.inc.php');
require_once('common.php');
require_once('exec.inc.php');
require_once("../../lib/functions/builds.inc.php");
require_once("../../lib/functions/attachments.inc.php");

testlinkInitPage($db);

$exec_cfg = config_get('exec_cfg');

$tree_mgr = new tree($db);
$tplan_mgr = new testplan($db);
$tcase_mgr = new testcase($db);

$testdata = array();
$submitResult = null;

$_REQUEST = strings_stripSlashes($_REQUEST);
$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
$build_id = isset($_REQUEST['build_id']) ? intval($_REQUEST['build_id']) : 0;
$tc_id = isset($_REQUEST['tc_id']) ? intval($_REQUEST['tc_id']) : null;
$keyword_id = isset($_REQUEST['keyword_id']) ? intval($_REQUEST['keyword_id']) : 0;
$level = isset($_REQUEST['level']) ? $_REQUEST['level'] : '';
$owner = isset($_REQUEST['owner']) ? intval($_REQUEST['owner']) : null;

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

// Added to set Test Results editable by comparing themax Build ID and the requested Build ID.			
$editTestResult = "yes";
$latestBuild = 0;
if(($latestBuild > $build_id) && !(config_get('edit_old_build_results')))
{
	$editTestResult = "no";
}

$bugs = null;
$attachmentInfos = null;
$map_last_exec = null;
$other_execs = null;
$map_last_exec_any_build = null;
$tcAttachments = null;
$tSuiteAttachments = null;
$linked_tcversions = $tplan_mgr->get_linked_tcversions($tplan_id,$tc_id,$keyword_id,null,$owner);

$tcase_id = 0;
if(!is_null($linked_tcversions))
{
    // Get the path for every test case, grouping test cases that
    // have same parent.
    $items_to_exec = array();
	$_SESSION['s_lastAttachmentInfos'] = null;
    if($level == 'testcase')
    {
    	$items_to_exec[$id] = $linked_tcversions[$id]['tcversion_id'];    
    	$tcase_id = $id;
    	$tcversion_id = $linked_tcversions[$id]['tcversion_id'];
		$tcAttachments[$id] = getAttachmentInfos($db,$id,'nodes_hierarchy',1);
    }
    else
    {
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
    			}
    			
				if($path_elem['node_table'] == 'testsuites' && !isset($tSuiteAttachments[$path_elem['id']]))
					$tSuiteAttachments[$path_elem['id']] = getAttachmentInfos($db,$path_elem['id'],'nodes_hierarchy',true,1);
			} 
    	}
    }
    
    // will create a record even if the testcase version has not been executed (GET_NO_EXEC)
    $map_last_exec = $tcase_mgr->get_last_execution($tcase_id,$tcversion_id,$tplan_id,
                                                    $build_id,GET_NO_EXEC);
    
    if (isset($_REQUEST['save_results']) || isset($_REQUEST['do_bulk_save']))
    {
    	$submitResult = write_execution($db,$user_id,$_REQUEST,$tplan_id,$build_id,$map_last_exec);
    }
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
    
    if(!is_null($other_execs))
    {
        $_bugInterfaceOn = config_get('bugInterfaceOn');

        foreach($other_execs as $tcversion_id => $execInfo)
        {
			    $num_elem = sizeof($execInfo);   
        	for($idx = 0;$idx < $num_elem;$idx++)
        	{
        		$execID = $execInfo[$idx]['execution_id'];
        		
        		$aInfo = getAttachmentInfos($db,$execID,'executions',true,1);
        		if ($aInfo)
        		{
        			$attachmentInfos[$execID] = $aInfo;
        		}
        		
        		if($_bugInterfaceOn)
        		{
              $the_bugs = get_bugs_for_exec($db,config_get('bugInterface'),$execID);
              if( count($the_bugs) > 0 )
              { 
        		    $bugs[$execID] = $the_bugs;
        		  }
        		}
        	}
        }
    }
}

$smarty = new TLSmarty();
$smarty->assign('bugs_for_exec',$bugs); // 20060917 - franciscom

$rs = $tplan_mgr->get_by_id($tplan_id);
$smarty->assign('tplan_notes',$rs['notes']);

$rs = getBuild_by_id($db,$build_id);
$smarty->assign('build_notes',$rs['notes']);

$tsuite_info = get_ts_name_details($db,$tcase_id);
$smarty->assign('tsuite_info',$tsuite_info);


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
$smarty->assign('edit_test_results', $editTestResult);
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


// 20060809 - franciscom
// returns map with key=TCID
//                  values= assoc_array([tsuite_id => 5341
//                                      [details] => my detailas ts1
//                                      [tcid] => 5343
//                                      [tsuite_name] => ts1)
//       
function get_ts_name_details(&$db,$tcase_id)
{
$rs='';
$do_query=true;
$sql="Select ts.id as tsuite_id, ts.details, 
             nha.id as tc_id, nhb.name as tsuite_name 
      FROM testsuites ts, nodes_hierarchy nha, nodes_hierarchy nhb
      WHERE ts.id=nha.parent_id
      AND   nhb.id=nha.parent_id ";
if( is_array($tcase_id) && count($tcase_id) > 0)
{
  $in_list=implode(",",$tcase_id);
  $sql .= "AND nha.id IN (" . $in_list . ")";
}
else if( !is_null($tcase_id) )
{
  $sql .= "AND nha.id={$tcase_id}";
}
else
{
  $do_query=false;
}
if($do_query)
{
  $rs=$db->fetchRowsIntoMap($sql,'tc_id');
}

return($rs);
}
?>																																
