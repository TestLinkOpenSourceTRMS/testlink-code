<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @package     TestLink
 * @author      Francisco Mancardi (francisco.mancardi@gmail.com)
 * @copyright   2005-2018, TestLink community 
 * @filesource  tc_exec_assignment.php
 * @link        http://www.testlink.org
 *
 */
         
require_once(dirname(__FILE__)."/../../config.inc.php");
require_once("common.php");
require_once("treeMenu.inc.php");
require_once('email_api.php');
require_once("specview.php");
require_once('Zend/Validate/EmailAddress.php');

testlinkInitPage($db,false,false,"checkRights");

$objMgr['tree'] = new tree($db);
$objMgr['tplan'] = new testplan($db);
$objMgr['tcase'] = new testcase($db);
$objMgr['assign'] = new assignment_mgr($db);

$tree_mgr = &$objMgr['tree']; 
$tplan_mgr = &$objMgr['tplan'];
$tcase_mgr = &$objMgr['tcase'];
$assignment_mgr = &$objMgr['assign'];


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

$status_map = $assignment_mgr->get_available_status();
$types_map = $assignment_mgr->get_available_types();
$cfg['task_test_execution'] = $types_map['testcase_execution']['id'];
$task_test_execution = $cfg['task_test_execution'];

switch($args->doAction)
{
  case 'std':
    if(!is_null($args->achecked_tc))
    {
      $open = $status_map['open']['id'];
      $db_now = $db->db_now();
      $features2 = array( 'upd' => array(), 'ins' => array(), 'del' => array());
      $method2call = array( 'upd' => 'update', 'ins' => 'assign', 'del' => 'delete_by_feature_id_and_build_id');
      $called = array( 'upd' => false, 'ins' => false, 'del' => false);

      foreach($args->achecked_tc as $key_tc => $platform_tcversion)
      {
        foreach($platform_tcversion as $platform_id => $tcversion_id)
        {
          $feature_id = $args->feature_id[$key_tc][$platform_id];

          $op='ins';
          $features2[$op][$platform_id][$feature_id]['user_id'] = $args->tester_for_tcid[$key_tc][$platform_id];
          $features2[$op][$platform_id][$feature_id]['type'] = $task_test_execution;
          $features2[$op][$platform_id][$feature_id]['status'] = $open;
          $features2[$op][$platform_id][$feature_id]['creation_ts'] = $db_now;
          $features2[$op][$platform_id][$feature_id]['assigner_id'] = $args->user_id;
          $features2[$op][$platform_id][$feature_id]['tcase_id'] = $key_tc;
          $features2[$op][$platform_id][$feature_id]['tcversion_id'] = $tcversion_id;
          $features2[$op][$platform_id][$feature_id]['build_id'] = $args->build_id; 
        }

      }

      foreach($features2 as $key => $featByPlatform)
      {
        if( count($features2[$key]) > 0 )
        {
          foreach($featByPlatform as $plat => $values)
          {
            $assignment_mgr->assign($values);
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
      } // if($args->send_mail)   
    }  
  break;


  case 'doRemoveAll':
    if(!is_null($args->achecked_tc)) {
      doRemoveAll($db,$args,$gui,$cfg,$objMgr);
    }  
  break; 

  case 'doRemove':
    $signature[] = array('type' => $task_test_execution, 
                         'user_id' => $args->targetUser, 
                         'feature_id' => $args->targetFeature, 
                         'build_id' => $args->build_id);
    $assignment_mgr->deleteBySignature($signature);

    if($args->send_mail)
    {
      // In order to send mail to tester we need info about test case, test case version 
      // and build, and we need to use feature_id to get this info
      $feature = current($tplan_mgr->getFeatureByID($args->targetFeature));
      
      $items = array();
      $lnk[$args->targetFeature] = array();
      $lnk[$args->targetFeature]['previous_user_id'] = array($args->targetUser);
      $lnk[$args->targetFeature]['tcase_id'] = intval($feature['tcase_id']);
      $lnk[$args->targetFeature]['tcversion_id'] = intval($feature['tcversion_id']);
      $items[intval($feature['platform_id'])] = $lnk;

      send_mail_to_testers($db,$tcase_mgr,$gui,$args,$items,'del');     
    } 
  break; 

  case 'linkByMail':
    $context = array('tplan_id' => $args->tplan_id,
                     'build_id' => $args->build_id);
    $assignment_mgr->emailLinkToExecPlanning($context,$args->userSet);
  break;

  case 'doBulkUserRemove':
    if(!is_null($args->achecked_tc) && !is_null($args->userSet)) {
      doBulkUserRemove($db,$args,$gui,$cfg,$objMgr);    
    }  
  break; 


}





switch($args->level) {
  case 'testcase':
    // build the data need to call gen_spec_view
    $xx = $tcase_mgr->getPathLayered(array($args->id));
    $yy = array_keys($xx);  // done to silence warning on end()
    $tsuite_data['id'] = end($yy);
    $tsuite_data['name'] = $xx[$tsuite_data['id']]['value']; 
        
    $xx = $tplan_mgr->getLinkInfo($args->tplan_id,$args->id,$args->control_panel['setting_platform'],
                                  array('output' => 'assignment_info','build4assignment' => $args->build_id));
    
    $linked_items[$args->id] = $xx;
    $opt = array('write_button_only_if_linked' => 1, 'user_assignments_per_build' => $args->build_id,
                 'useOptionalArrayFields' => true);

    $filters = array('keywords' => $keywordsFilter->items, 'testcases' => $args->id);
    
    $my_out = gen_spec_view($db,'testplan',$args->tplan_id,$tsuite_data['id'],$tsuite_data['name'],
                            $linked_items,null,$filters,$opt);

    // index 0 contains data for the parent test suite of this test case, 
    // other elements are not needed.
    $out = array();
    $out['spec_view'][0] = $my_out['spec_view'][0];
    $out['num_tc'] = 1;
  break;
    
  case 'testsuite':
    $filters = array();
    $filters['keywordsFilter'] = $keywordsFilter;
    $filters['testcaseFilter'] = (isset($args->testcases_to_show)) ? $args->testcases_to_show : null;
    $filters['assignedToFilter'] = property_exists($args,'filter_assigned_to') ? $args->filter_assigned_to : null;
    $filters['executionTypeFilter'] = $args->control_panel['filter_execution_type'];
    $filters['cfieldsFilter'] = $args->control_panel['filter_custom_fields'];

    // ORDER IS CRITIC - Attention in refactoring    
    $opt = array('assigned_on_build' => $args->build_id, 'addPriority' => true,
                 'addExecInfo' => false);
    $filters += $opt;
    $opt['accessKeyType'] = 'tcase+platform+stackOnUser';
    $opt['useOptionalArrayFields'] = true;
    $opt['tlFeature'] = 'testCaseExecTaskAssignment';

    // platform filter is generated inside getFilteredSpecView() using $args->control_panel['setting_platform'];
    // $out = getFilteredSpecView($db, $args, $tplan_mgr, $tcase_mgr, $filters, $opt);

    $out = getFilteredSpecViewFlat($db, $args, $tplan_mgr, $tcase_mgr, $filters, $opt);
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

if ($_SESSION['testprojectOptions']->testPriorityEnabled) 
{
  $cfg = config_get('priority');
  $gui->priority_labels = init_labels($cfg["code_label"]);
}

// Changing to _flat template
$tplCfg = templateConfiguration();
$tpl = $tplCfg->tpl;
$tpl = str_replace('.tpl', '_flat.tpl', $tpl);

$smarty = new TLSmarty();
$smarty->assign('gui', $gui);
$smarty->display($tpl);

/*
  function: 

  args :
  
  returns: 

*/
function init_args()
{
  $_REQUEST = strings_stripSlashes($_REQUEST);
  $args = new stdClass();
  $args->user_id = intval($_SESSION['userID']);
  $args->tproject_id = intval($_SESSION['testprojectID']);
  $args->tproject_name = $_SESSION['testprojectName'];
      
  $key2loop = array('doActionButton' => null, 'doAction' => null,'level' => null , 'achecked_tc' => null, 
                    'version_id' => 0, 'has_prev_assignment' => null, 'send_mail' => false,
                    'tester_for_tcid' => null, 'feature_id' => null, 'id' => 0);
    
  foreach($key2loop as $key => $value)
  {
    $args->$key = isset($_REQUEST[$key]) ? $_REQUEST[$key] : $value;
  }
  
  $args->userSet = null;
  $target = $_REQUEST['bulk_tester_div']; 
  if(isset($target) && count($target) > 0) {
    foreach($target as $uid) {
      if($uid > 0) {
        $args->userSet[$uid] = $uid;
      }  
    }  
  }  

  // For more information about the data accessed in session here, see the comment
  // in the file header of lib/functions/tlTestCaseFilterControl.class.php.
  $form_token = isset($_REQUEST['form_token']) ? $_REQUEST['form_token'] : 0;
  $mode = 'plan_mode';
  $session_data = isset($_SESSION[$mode]) && isset($_SESSION[$mode][$form_token]) ? $_SESSION[$mode][$form_token] : null;

  $args->control_panel = $session_data;
    
  $key2loop = array('refreshTree' => array('key' => 'setting_refresh_tree_on_action', 'value' => 0),
                    'filter_assigned_to' => array('key' => 'filter_assigned_user', 'value' => null));
  
  foreach($key2loop as $key => $info)
  {
    $args->$key = isset($session_data[$info['key']]) ? $session_data[$info['key']] : $info['value']; 
  }
  
    
  $args->keyword_id = 0;
  $fk = 'filter_keywords';
  if (isset($session_data[$fk])) 
  {
    $args->keyword_id = $session_data[$fk];
    if (is_array($args->keyword_id) && count($args->keyword_id) == 1) 
    {
      $args->keyword_id = $args->keyword_id[0];
    }
  }
  
  $args->keywordsFilterType = null;
  $fk = 'filter_keywords_filter_type';
  if (isset($session_data[$fk])) 
  {
    $args->keywordsFilterType = $session_data[$fk];
  }
  
  
  $args->testcases_to_show = null;
  if (isset($session_data['testcases_to_show'])) 
  {
    $args->testcases_to_show = $session_data['testcases_to_show'];
  }
  
  $args->build_id = intval(isset($session_data['setting_build']) ? $session_data['setting_build'] : 0);
  $args->platform_id = intval(isset($session_data['setting_platform']) ? 
                       $session_data['setting_platform'] : 0);
  
  $args->tplan_id = intval(isset($session_data['setting_testplan']) ? $session_data['setting_testplan'] : 0);
  if ($args->tplan_id) 
  {
    $args->tplan_id = intval(isset($_REQUEST['tplan_id']) ? $_REQUEST['tplan_id'] : $_SESSION['testplanID']);
  }
    

  $args->targetFeature = intval(isset($_REQUEST['targetFeature']) ? $_REQUEST['targetFeature'] : 0);  
  $args->targetUser = intval(isset($_REQUEST['targetUser']) ? $_REQUEST['targetUser'] : 0);  


  $key = 'doRemoveAll';
  if( ($args->$key = isset($_REQUEST[$key]) ? 1 : 0) )
  {
    $args->doAction = $key;
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
  $platform_mgr = new tlPlatform($dbHandler,$argsObj->tproject_id);
  
  $tcase_cfg = config_get('testcase_cfg');
  $gui = new stdClass();
  $gui->platforms = $platform_mgr->getLinkedToTestplanAsMap($argsObj->tplan_id);
  $gui->usePlatforms = $platform_mgr->platformsActiveForTestplan($argsObj->tplan_id);
  $gui->bulk_platforms = $platform_mgr->getLinkedToTestplanAsMap($argsObj->tplan_id);
  $gui->bulk_platforms[0] = lang_get("all_platforms");
  ksort($gui->bulk_platforms);
    
  $gui->send_mail = $argsObj->send_mail;
  $gui->send_mail_checked = "";
  if($gui->send_mail)
  {
    $gui->send_mail_checked = ' checked="checked" ';
  }
    
  $gui->glueChar=$tcase_cfg->glue_character;
    
  if ($argsObj->level != 'testproject')
  {
    $gui->testCasePrefix = $tcaseMgr->tproject_mgr->getTestCasePrefix($argsObj->tproject_id);
    $gui->testCasePrefix .= $tcase_cfg->glue_character;
    $gui->keywordsFilterType = $argsObj->keywordsFilterType;
    $gui->build_id = $argsObj->build_id;
    $gui->tplan_id = $argsObj->tplan_id;
      
    $tplan_info = $tplanMgr->get_by_id($argsObj->tplan_id);
    $gui->testPlanName = $tplan_info['name'];
  
    $build_info = $tplanMgr->get_build_by_id($argsObj->tplan_id, $argsObj->build_id);
    $gui->buildName = $build_info['name'];
    $gui->main_descr = sprintf(lang_get('title_tc_exec_assignment'),$gui->buildName, $gui->testPlanName);

    $tproject_mgr = new testproject($dbHandler);
    $tproject_info = $tproject_mgr->get_by_id($argsObj->tproject_id);

    $gui->all_users = tlUser::getAll($dbHandler,null,"id",null);
    $gui->users = getUsersForHtmlOptions($dbHandler,null,null,null,$gui->all_users);
    $gui->testers = getTestersForHtmlOptions($dbHandler,$argsObj->tplan_id,$tproject_info,$gui->all_users);
  }

  return $gui;
}


/**
 * send_mail_to_testers
 *
 * @param hash $features main key platform_id
 * @param string $operation
 *
 * @return void
 */
function send_mail_to_testers(&$dbHandler,&$tcaseMgr,&$guiObj,&$argsObj,$features,$operation)
{
  $testers['new']=null;
  $testers['old']=null;
  $lb = array('platform' => null, 'testplan' => null, 'testproject' => null, 
              'build' =>null);
  $lbl = init_labels($lb);

  $mail_details['new']=lang_get('mail_testcase_assigned') . "<br /><br />";
  $mail_details['old']=lang_get('mail_testcase_assignment_removed'). "<br /><br />";
  $mail_subject['new']=lang_get('mail_subject_testcase_assigned');
  $mail_subject['old']=lang_get('mail_subject_testcase_assignment_removed');
  $use_testers['new']= ($operation == 'del') ? false : true ;
  $use_testers['old']= ($operation == 'ins') ? false : true ;
   
  $tcaseSet=null;
  $tcnames=null;

  $assigner=$guiObj->all_users[$argsObj->user_id]->firstName . ' ' .
            $guiObj->all_users[$argsObj->user_id]->lastName ;
              

  $email=array();
  $email['from_address']=config_get('from_email');
  $email['attachment'] = null;
  $email['cc'] = null;
  $email['exit_on_error'] = true;
  $email['htmlFormat'] = true;

  $body_header = $lbl['testproject'] . ': ' . $argsObj->tproject_name . '<br />' .
                 $lbl['testplan'] . ': ' . $guiObj->testPlanName .'<br />' .
                 $lbl['build'] . ': ' . $guiObj->buildName .'<br /><br />';


  // Do we really have platforms?
  $pset = array_flip(array_keys($features));
  if( $hasPlat = !isset($pset[0]) )
  {
    $platMgr = new tlPlatform($dbHandler,$argsObj->tproject_id);
    $platSet = $platMgr->getAllAsMap();
  }  
   
  // Get testers id & item set with test case & test case version                 
  foreach($features as $platform_id => $items)
  {
    $plat[$platform_id] = $platform_id;
    foreach( $items as $feature_id => $value )
    {
      if( $use_testers['new'] || $use_testers['old'] )
      {
        if( $use_testers['new'] )
        {
          $ty = (array)$value['user_id'];
          $accessKey = 'new';          
        }
          
        if( $use_testers['old'] )
        {
          $ty = (array)$value['previous_user_id'];
          $accessKey = 'old';
        }
            
        foreach( $ty as $user_id )
        {
          $testers[$accessKey][$user_id][$platform_id][$feature_id]=$value['tcase_id'];
        }  
      }
              
      $tcaseSet[$value['tcase_id']]=$value['tcase_id'];
      $tcversionSet[$value['tcversion_id']]=$value['tcversion_id'];
    }    
  }  
 
  $infoSet = $tcaseMgr->get_by_id_bulk($tcaseSet,$tcversionSet);
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

  $validator = new Zend_Validate_EmailAddress();
  foreach($testers as $tester_type => $tester_set)
  {
    if( !is_null($tester_set) )
    {
      $email['subject'] = $mail_subject[$tester_type] . ' ' . $guiObj->testPlanName;  
      foreach($tester_set as $user_id => $set2work)
      {
        // workaround till solution will be found
        if($user_id <= 0)
        {
          continue;
        }  

        $userObj=$guiObj->all_users[$user_id];
        $email['to_address'] = trim($userObj->emailAddress);
        if($email['to_address'] == '' || !$validator->isValid($email['to_address']))
        {
          continue;
        }  

        $email['body'] = $body_header;
        $email['body'] .= sprintf($mail_details[$tester_type],
                          $userObj->firstName . ' ' .$userObj->lastName,$assigner);

        foreach ($set2work as $pid => $value) 
        {
          if( $pid != 0 )
          {
            $email['body'] .= $lbl['platform'] . ': ' . $platSet[$pid] . '<br />';  
          }  
  
          foreach($value as $tcase_id)
          {
            $email['body'] .= $flat_path[$tcase_id] . '<br />';  
            $wl = $tcaseMgr->buildDirectWebLink($_SESSION['basehref'],$tcase_id,
                                                $argsObj->testproject_id);
           
            $email['body'] .= '<a href="' . $wl . '">' . 
                              'direct link to test case spec ' .
                              '</a>' .
                              '<br /><br />';

          }  
        }

          
        $email['body'] .= '<br />' . date(DATE_RFC1123);

        $email_op = email_send($email['from_address'], $email['to_address'], 
                               $email['subject'], $email['body'], $email['cc'], 
                               $email['attachment'],$email['exit_on_error'], 
                               $email['htmlFormat']);
      } // foreach($tester_set as $user_id => $value)
    }                       
  }
}

/**
 *
 */
function doRemoveAll(&$dbH,&$argsObj,&$guiObj,$cfg,$oMgr) {
  $op='del';
  $features2[$op] = array();

  foreach($argsObj->achecked_tc as $key_tc => $ptc) {
    foreach($ptc as $platform_id => $tcversion_id) {
      $fid = $argsObj->feature_id[$key_tc][$platform_id];
      $features2[$op][$fid]['type'] = $cfg['task_test_execution'];
      $features2[$op][$fid]['build_id'] = $argsObj->build_id; 
    }
  }
  
  // Must be done before delete
  if($argsObj->send_mail) {
    $fSet = array_keys($features2[$op]);
    $items = $oMgr['tplan']->getFeatureByID($fSet);
    $testers = $oMgr['assign']->getUsersByFeatureBuild($fSet,$argsObj->build_id,$cfg['task_test_execution']);

    $f4mail = array();
    foreach($items as $fid => $value)
    {
      $pid = $value['platform_id'];
      $f4mail[$pid][$fid]['previous_user_id'] = array_keys($testers[$fid]); 
      $f4mail[$pid][$fid]['tcase_id'] = $items[$fid]['tcase_id'];
      $f4mail[$pid][$fid]['tcversion_id'] = $items[$fid]['tcversion_id'];
    } 
  }


  foreach($features2 as $key => $values) {
    if( count($features2[$key]) > 0 ) {
      $oMgr['assign']->delete_by_feature_id_and_build_id($values);
    }  
  }

  if($argsObj->send_mail) {
    send_mail_to_testers($dbH,$oMgr['tcase'],$guiObj,$argsObj,$f4mail,'del'); 
  }   
}

/**
 *
 */
function doBulkUserRemove(&$dbH,&$argsObj,&$guiObj,$cfg,$oMgr) {
  
  $feat = null;
  if(!is_null($argsObj->achecked_tc)) {
    foreach($argsObj->achecked_tc as $key_tc => $ptc) {
      foreach($ptc as $platform_id => $tcversion_id) {
        foreach($argsObj->userSet as $user2remove) {
          $fid = $argsObj->feature_id[$key_tc][$platform_id];
          $feat[$fid]['type'] = $cfg['task_test_execution'];
          $feat[$fid]['feature_id'] = $fid;
          $feat[$fid]['build_id'] = $argsObj->build_id; 
          $feat[$fid]['user_id'] = $user2remove;
        }
      }
    }

    // Must be done before delete
    if($argsObj->send_mail) {
      $fSet = array_keys($feat);
      $items = $oMgr['tplan']->getFeatureByID($fSet);
      $testers = $oMgr['assign']->getUsersByFeatureBuild($fSet,$argsObj->build_id,$cfg['task_test_execution']);

      $f4mail = array();
      foreach($items as $fid => $value) {
        $pid = $value['platform_id'];
        $f4mail[$pid][$fid]['previous_user_id'] = array_keys($testers[$fid]); 
        $f4mail[$pid][$fid]['tcase_id'] = $items[$fid]['tcase_id'];
        $f4mail[$pid][$fid]['tcversion_id'] = $items[$fid]['tcversion_id'];
      } 
    }

    $oMgr['assign']->deleteBySignature($feat);

    if($argsObj->send_mail) {
      send_mail_to_testers($dbH,$oMgr['tcase'],$guiObj,$argsObj,$f4mail,'del'); 
    }   

  }
}


function checkRights(&$db,&$user)
{
  return $user->hasRight($db,'exec_assign_testcases');
}