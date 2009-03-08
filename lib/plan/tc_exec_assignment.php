<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * @version $Id: tc_exec_assignment.php,v 1.35 2009/03/08 22:26:14 havlat Exp $ 
 * 
 * rev :
 *       20090201 - franciscom - new feature send mail to tester
 *       20080312 - franciscom - BUGID 1427
 *       20080114 - franciscom - added testcase external_id management
 *       20071228 - franciscom - BUG build combo of users using only users
 *                               that can execute test cases in testplan.
 * 
 *       20070912 - franciscom - BUGID 1041
 */         
require_once(dirname(__FILE__)."/../../config.inc.php");
require_once("common.php");
require_once("treeMenu.inc.php");
require_once('email_api.php');
require_once("specview.php");

testlinkInitPage($db,false,false,"checkRights");

$tree_mgr = new tree($db); 
$tplan_mgr = new testplan($db); 
$tcase_mgr = new testcase($db); 
$assignment_mgr = new assignment_mgr($db); 

$templateCfg = templateConfiguration();

$args = init_args();
$gui = initializeGui($db,$args,$tplan_mgr,$tcase_mgr);

$keywordsFilter = new stdClass();
$keywordsFilter->items = null;
$keywordsFilter->type = null;
if(is_array($args->keyword_id))
{
    $keywordsFilter->items = $args->keyword_id;
    $keywordsFilter->type = $gui->keywordsFilterType;
}
$arrData = array();

if(!is_null($args->doAction))
{
	if(!is_null($args->achecked_tc))
	{
		$types_map = $assignment_mgr->get_available_types();
		$status_map = $assignment_mgr->get_available_status();

		$task_test_execution = $types_map['testcase_execution']['id'];
		$open = $status_map['open']['id'];
		$db_now = $db->db_now();

		$features2 = array( 'upd' => array(), 'ins' => array(), 'del' => array());
	  $method2call = array( 'upd' => 'update', 'ins' => 'assign', 'del' => 'delete_by_feature_id');
	  $called = array( 'upd' => false, 'ins' => false, 'del' => false);

		foreach($args->achecked_tc as $key_tc => $value_tcversion)
		{
			$feature_id = $args->feature_id[$key_tc];

			if($args->has_prev_assignment[$key_tc] > 0)
			{
         
				if($args->tester_for_tcid[$key_tc] > 0)  
				{
          // Do only is tester has changed
				  if( $args->has_prev_assignment[$key_tc] != $args->tester_for_tcid[$key_tc])
				  {
			        $op='upd';
					    $features2[$op][$feature_id]['user_id'] = $args->tester_for_tcid[$key_tc];
					    $features2[$op][$feature_id]['type'] = $task_test_execution;
					    $features2[$op][$feature_id]['status'] = $open;
					    $features2[$op][$feature_id]['assigner_id'] = $args->user_id;
					    $features2[$op][$feature_id]['tcase_id'] = $key_tc;
					    $features2[$op][$feature_id]['tcversion_id'] = $value_tcversion;
              $features2[$op][$feature_id]['previous_user_id'] = $args->has_prev_assignment[$key_tc];					    
					}
				} 
				else
				{
	          $op='del';
					  $features2[$op][$feature_id]['tcase_id'] = $key_tc;
					  $features2[$op][$feature_id]['tcversion_id'] = $value_tcversion;
            $features2[$op][$feature_id]['previous_user_id'] = $args->has_prev_assignment[$key_tc];					    
				}	
			}
			else if($args->tester_for_tcid[$key_tc] > 0)
			{
			  $op='ins';
				$features2[$op][$feature_id]['user_id'] = $args->tester_for_tcid[$key_tc];
				$features2[$op][$feature_id]['type'] = $task_test_execution;
				$features2[$op][$feature_id]['status'] = $open;
				$features2[$op][$feature_id]['creation_ts'] = $db_now;
				$features2[$op][$feature_id]['assigner_id'] = $args->user_id;
				$features2[$op][$feature_id]['tcase_id'] = $key_tc;
				$features2[$op][$feature_id]['tcversion_id'] = $value_tcversion;
			}
		}
    foreach($features2 as $key => $values)
    {
        if( count($features2[$key]) > 0 )
        {
            if( $key == 'del' )
            {
                $assignment_mgr->$method2call[$key](array_keys($values));
            }
            else
            {
           	    $assignment_mgr->$method2call[$key]($values);
           	}
           	$called[$key]=true;
        }  
    }
			
		if($args->send_mail)
		{
		    foreach($called as $ope => $ope_status)
		    {
            if($ope_status)
            {
                send_mail_to_testers($db,$tcase_mgr,$gui,$args,$features2[$ope],$ope);     
		        }
		    }
		}	// if($args->send_mail)
	}  
}

switch($args->level)
{
	case 'testcase':
		// build the data need to call gen_spec_view
		$my_path = $tree_mgr->get_path($args->id);
		$idx_ts = count($my_path) - 1;
		$tsuite_data = $my_path[$idx_ts - 1];
		
		$status_quo = $tcase_mgr->get_versions_status_quo($args->id, $args->version_id);
		$linked_items[$args->id] = $status_quo[$args->version_id];
		$linked_items[$args->id]['testsuite_id'] = $tsuite_data['id'];
		$linked_items[$args->id]['tc_id'] = $args->id;
		
		$exec_assignment = $tcase_mgr->get_version_exec_assignment($args->version_id,$args->tplan_id);
		$linked_items[$args->id]['user_id'] = $exec_assignment[$args->version_id]['user_id'];
		$linked_items[$args->id]['feature_id'] = $exec_assignment[$args->version_id]['feature_id'];
		$my_out = gen_spec_view($db,'testplan',$args->tplan_id,$tsuite_data['id'],$tsuite_data['name'],
				    		            $linked_items,null,$keywordsFilter->items,FILTER_BY_TC_OFF,WRITE_BUTTON_ONLY_IF_LINKED);
							           
		// index 0 contains data for the parent test suite of this test case, 
		// other elements are not needed.
		$out = array();
		$out['spec_view'][0] = $my_out['spec_view'][0];
		$out['num_tc'] = 1;
		break;
		
	case 'testsuite':
		$out = keywordFilteredSpecView($db,$args,$keywordsFilter,$tplan_mgr,$tcase_mgr);
		break;

	default:
		show_instructions('tc_exec_assignment');
		break;
}

$gui->items = $out['spec_view'];

// useful to avoid error messages on smarty template.
$gui->items_qty = is_null($gui->items) ? 0 : count($gui->items);
$gui->has_tc = $out['num_tc'] > 0 ? 1:0;
$gui->support_array = array_keys($gui->items);

$smarty = new TLSmarty();
$smarty->assign('gui', $gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/*
  function: 

  args :
  
  returns: 

*/
function init_args()
{
	  $_REQUEST = strings_stripSlashes($_REQUEST);
	  $args = new stdClass();
	  $args->user_id = $_SESSION['userID'];
	  $args->tproject_id = $_SESSION['testprojectID'];
	  $args->tproject_name = $_SESSION['testprojectName'];
      
	  $args->tplan_id = isset($_REQUEST['tplan_id']) ? $_REQUEST['tplan_id'] : $_SESSION['testPlanId'];
      
	  $key2loop = array('doAction' => null,'level' => null , 'achecked_tc' => null, 
	    	              'version_id' => 0, 'has_prev_assignment' => null, 'send_mail' => false,
	    	              'tester_for_tcid' => null, 'feature_id' => null, 'id' => 0, 'filter_assigned_to' => null);
	  foreach($key2loop as $key => $value)
	  {
	  	$args->$key = isset($_REQUEST[$key]) ? $_REQUEST[$key] : $value;
	  }
    
    // Can be a list (string with , (comma) has item separator), that will be trasformed in an array.
    $keywordSet = isset($_REQUEST['keyword_id']) ? $_REQUEST['keyword_id'] : null;
    $args->keyword_id = is_null($keywordSet) ? 0 : explode(',',$keywordSet); 
    $args->keywordsFilterType = isset($_REQUEST['keywordsFilterType']) ? $_REQUEST['keywordsFilterType'] : 'OR';
    
    if( !is_null($args->filter_assigned_to) )
    {
        $args->filter_assigned_to = (array)$args->filter_assigned_to;  
    }
    
	  return $args;
}

/*
  function: initializeGui

  args :
  
  returns: 

*/
function initializeGui(&$dbHandler,$argsObj,&$tplanMgr,&$tcaseMgr)
{
    $tcase_cfg = config_get('testcase_cfg');
    $gui = new stdClass();
    $gui->send_mail=$argsObj->send_mail;
    $gui->glueChar=$tcase_cfg->glue_character;
    
    if ($argsObj->level != 'testproject')
    {
	    $gui->testCasePrefix = $tcaseMgr->tproject_mgr->getTestCasePrefix($argsObj->tproject_id);
	    $gui->testCasePrefix .= $tcase_cfg->glue_character;
									  
	    $gui->keywordsFilterType = $argsObj->keywordsFilterType;
	
	    $tplan_info = $tplanMgr->get_by_id($argsObj->tplan_id);
	    $gui->testPlanName = $tplan_info['name'];
	    $gui->main_descr = lang_get('title_tc_exec_assignment') . $gui->testPlanName;
	    
	    $gui->all_users = tlUser::getAll($dbHandler,null,"id",null);
	   	$gui->users = getUsersForHtmlOptions($dbHandler,null,null,null,$gui->all_users);
	   	$gui->testers = getTestersForHtmlOptions($dbHandler,$argsObj->tplan_id,$argsObj->tproject_id,$gui->all_users);
	  }

    return $gui;
}


/**
 * send_mail_to_testers
 *
 *
 * @return void
 */
function send_mail_to_testers(&$dbHandler,&$tcaseMgr,&$guiObj,&$argsObj,$features,$operation)
{
    $testers['new']=null;
    $testers['old']=null;
    $mail_details['new']=lang_get('mail_testcase_assigned') . "<br /><br />";
    $mail_details['old']=lang_get('mail_testcase_assignment_removed'). "<br /><br />";
    $mail_subject['new']=lang_get('mail_subject_testcase_assigned');
    $mail_subject['old']=lang_get('mail_subject_testcase_assignment_removed');
    $use_testers['new']= ($operation == 'del') ? false : true ;
    $use_testers['old']= ($operation == 'ins') ? false : true ;
   

    $tcaseSet=null;
    $tcnames=null;
    $email=array();
   
    $assigner=$guiObj->all_users[$argsObj->user_id]->firstName . ' ' .
              $guiObj->all_users[$argsObj->user_id]->lastName ;
              
    $email['from_address']=config_get('from_email');
    $body_first_lines = lang_get('testproject') . ': ' . $argsObj->tproject_name . '<br />' .
                        lang_get('testplan') . ': ' . $guiObj->testPlanName .'<br /><br />';


    // Get testers id
    foreach($features as $feature_id => $value)
    {
        if($use_testers['new'])
        {
            $testers['new'][$value['user_id']][$value['tcase_id']]=$value['tcase_id'];              
        }
        if( $use_testers['old'] )
        {
            $testers['old'][$value['previous_user_id']][$value['tcase_id']]=$value['tcase_id'];              
        }
        
        $tcaseSet[$value['tcase_id']]=$value['tcase_id'];
        $tcversionSet[$value['tcversion_id']]=$value['tcversion_id'];
    } 

    $infoSet=$tcaseMgr->get_by_id_bulk($tcaseSet,$tcversionSet);
    foreach($infoSet as $value)
    {
        $tcnames[$value['testcase_id']] = $guiObj->testCasePrefix . $value['tc_external_id'] . ' ' . $value['name'];    
    }
    
    $path_info = $tcaseMgr->tree_manager->get_full_path_verbose($tcaseSet);
    $flat_path=null;
    foreach($path_info as $tcase_id => $pieces)
    {
        $flat_path[$tcase_id]=implode('/',$pieces) . '/' . $tcnames[$tcase_id];  
    }


    foreach($testers as $tester_type => $tester_set)
    {
        if( !is_null($tester_set) )
        {
            $email['subject'] = $mail_subject[$tester_type] . ' ' . $guiObj->testPlanName;  
            foreach($tester_set as $user_id => $value)
            {
                $userObj=$guiObj->all_users[$user_id];
                $email['to_address']=$userObj->emailAddress;
                $email['body'] = $body_first_lines;
                $email['body'] .= sprintf($mail_details[$tester_type],
                                          $userObj->firstName . ' ' .$userObj->lastName,$assigner);
                foreach($value as $tcase_id)
                {
                    $email['body'] .= $flat_path[$tcase_id] . '<br />';  
                }  
                $email['body'] .= '<br />' . date(DATE_RFC1123);
  	            $email_op = email_send($email['from_address'], $email['to_address'], 
  	            		$email['subject'], $email['body'], '', true, true);
            } // foreach($tester_set as $user_id => $value)
  	    }                       
    }
}

function checkRights(&$db,&$user)
{
	return $user->hasRight($db,'testplan_planning');
}
?>