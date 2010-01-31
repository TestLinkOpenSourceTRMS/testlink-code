<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * link/unlink test cases to a test plan
 *
 * @package 	TestLink
 * @copyright 	2007-2009, TestLink community 
 * @version    	CVS: $Id: planAddTC.php,v 1.90 2010/01/31 16:52:03 franciscom Exp $
 * @filesource	http://testlink.cvs.sourceforge.net/viewvc/testlink/testlink/lib/functions/object.class.php?view=markup
 * @link 		http://www.teamst.org/index.php
 * 
 * @internal Revisions:
 * 20100129 - franciscom - moved here from template, logic to initialize:
 *                         drawSavePlatformsButton,drawSaveCFieldsButton        
 *                         
 * 20090922 - franciscom - add contribution - bulk tester assignment while adding test cases
 *
 **/

require_once('../../config.inc.php');
require_once("common.php");
require_once('email_api.php');
require_once("specview.php");

testlinkInitPage($db);

$tree_mgr = new tree($db);
$tsuite_mgr = new testsuite($db);
$tplan_mgr = new testplan($db);
$tproject_mgr = new testproject($db);
$tcase_mgr = new testcase($db);

$templateCfg = templateConfiguration();
$args = init_args();
$gui = initializeGui($db,$args,$tplan_mgr,$tcase_mgr);

$keywordsFilter = null;
if(is_array($args->keyword_id))
{
    $keywordsFilter = new stdClass();
    $keywordsFilter->items = $args->keyword_id;
    $keywordsFilter->type = $gui->keywordsFilterType->selected;
}

$do_display = 0;
switch($args->item_level)
{
    case 'testsuite':
		$do_display = 1;
		break;

    case 'testproject':
	    show_instructions('planAddTC');
	    exit();
	    break;
}

switch($args->doAction)
{
    case 'doAddRemove':
		// Remember:  checkboxes exist only if are checked
	    if(!is_null($args->testcases2add))
	    {
	    	// items_to_link structure:
	    	// key: test case id , value: map 
	    	//                            key: platform_id value: test case VERSION ID
		    $items_to_link = null;
            foreach ($args->testcases2add as $tcase_id => $info) 
            {
                foreach ($info as $platform_id => $tcase_id) 
                {
                    // $items_to_link[$tcase_id][$platform_id] = $args->tcversion_for_tcid[$tcase_id];
                    if( isset($args->tcversion_for_tcid[$tcase_id]) )
                    {
                    	$tcversion_id = $args->tcversion_for_tcid[$tcase_id];
                    }
                    else
                    {
                    	$tcversion_id = $args->linkedVersion[$tcase_id];
                    }
                    $items_to_link['tcversion'][$tcase_id] = $tcversion_id;
                    $items_to_link['platform'][$platform_id] = $platform_id;
                    $items_to_link['items'][$tcase_id][$platform_id] = $tcversion_id;
                }
            }
           
		    $linked_features=$tplan_mgr->link_tcversions($args->tplan_id,$items_to_link,$args->userID);
		    if( $args->testerID > 0 )
		    {
		    	$features2add = null;
				$status_map = $tplan_mgr->assignment_mgr->get_available_status();
		        $types_map = $tplan_mgr->assignment_mgr->get_available_types();
		        $db_now = $db->db_now();
                $tcversion_tcase = array_flip($items_to_link['tcversion']);
                
                $getOpt = array('outputFormat' => 'map', 'addIfNull' => true);
                $platformSet = $tplan_mgr->getPlatforms($args->tplan_id,$getOpt);
                
		    	foreach($linked_features as $platform_id => $tcversion_info)
		    	{
		    		foreach($tcversion_info as $tcversion_id => $feature_id)
		    		{
		    			$features2['add'][$feature_id]['user_id'] = $args->testerID;
						$features2['add'][$feature_id]['type'] = $types_map['testcase_execution']['id'];
						$features2['add'][$feature_id]['status'] = $status_map['open']['id'];
						$features2['add'][$feature_id]['assigner_id'] = $args->userID;
						$features2['add'][$feature_id]['tcase_id'] = $tcversion_tcase[$tcversion_id];
						$features2['add'][$feature_id]['tcversion_id'] = $tcversion_id;
					    $features2['add'][$feature_id]['creation_ts'] = $db_now;
					    $features2['add'][$feature_id]['platform_name'] = $platformSet[$platform_id];
					}
            	}

    			foreach($features2 as $key => $values)
    			{
			       	$tplan_mgr->assignment_mgr->assign($values);
    				$called[$key]=true;
    			}
				if($args->send_mail)
				{
				    foreach($called as $ope => $ope_status)
				    {
        		    	if($ope_status)
        		    	{
        		        	send_mail_to_testers($db,$tcase_mgr,$gui,$args,$features2['add'],$ope);     
				        }
				    }
				}	// if($args->send_mail)

		    }
	    }

	    if(!is_null($args->testcases2remove))
	    {
		    // remove without warning
		    $items_to_unlink=null;
            foreach ($args->testcases2remove as $tcase_id => $info) 
            {
                foreach ($info as $platform_id => $tcversion_id) 
                {
                    $items_to_unlink['tcversion'][$tcase_id] = $tcversion_id;
                    $items_to_unlink['platform'][$platform_id] = $platform_id;
                    $items_to_unlink['items'][$tcase_id][$platform_id] = $tcversion_id;
                }
            }
		    $tplan_mgr->unlink_tcversions($args->tplan_id,$items_to_unlink);
	    }
	    doReorder($args,$tplan_mgr);
	    $do_display = 1;
	    break;
	
    case 'doReorder':
		doReorder($args,$tplan_mgr);
		$do_display = 1;
		break;

    case 'doSavePlatforms':
		doSavePlatforms($args,$tplan_mgr);
		$do_display = 1;
		break;

    case 'doSaveCustomFields':
		doSaveCustomFields($args,$_REQUEST,$tplan_mgr,$tcase_mgr);
		$do_display = 1;
		break;
	
    default:
		break;
}
$smarty = new TLSmarty();
if($do_display)
{
	$tsuite_data = $tsuite_mgr->get_by_id($args->object_id);
		
	// This does filter on keywords ALWAYS in OR mode.
	$tplan_linked_tcversions = getFilteredLinkedVersions($args,$tplan_mgr,$tcase_mgr);
	$testCaseSet = null;
	if(!is_null($keywordsFilter))
	{ 
	    // With this pieces we implement the AND type of keyword filter.
	    $keywordsTestCases = $tproject_mgr->get_keywords_tcases($args->tproject_id,$keywordsFilter->items,
	                                                            $keywordsFilter->type);
	    
		if (sizeof($keywordsTestCases))
		{
			$testCaseSet = array_keys($keywordsTestCases);
		}
	}
	
	
	// Choose enable/disable display of custom fields, analysing if this kind of custom fields
	// exists on this test project.
	$cfields=$tsuite_mgr->cfield_mgr->get_linked_cfields_at_testplan_design($args->tproject_id,1,'testcase');
    $opt = array('write_button_only_if_linked' => 0, 'add_custom_fields' => 0);
    $opt['add_custom_fields'] = count($cfields) > 0 ? 1 : 0;
    $filters = array('keywords' => $args->keyword_id, 'testcases' => $testCaseSet);
	$out = gen_spec_view($db,'testproject',$args->tproject_id,$args->object_id,$tsuite_data['name'],
	                     $tplan_linked_tcversions,null,$filters,$opt);
  
  	$gui->has_tc = ($out['num_tc'] > 0 ? 1 : 0);
	$gui->items = $out['spec_view'];
	$gui->has_linked_items = $out['has_linked_items'];
	$gui->add_custom_fields = $opt['add_custom_fields'];
    $gui->drawSavePlatformsButton = false;
    $gui->drawSaveCFieldsButton = false;
    if( !is_null($gui->items) )
    {
		initDrawSaveButtons($gui);
    }
	$smarty->assign('gui', $gui);
	$smarty->display($templateCfg->template_dir .  'planAddTC_m1.tpl');
}


/*
  function: init_args
            creates a sort of namespace

  args:

  returns: object with some REQUEST and SESSION values as members

*/
function init_args()
{
	$_REQUEST = strings_stripSlashes($_REQUEST);

	$args = new stdClass();
	$args->tplan_id = isset($_REQUEST['tplan_id']) ? $_REQUEST['tplan_id'] : $_SESSION['testplanID'];
	$args->object_id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
	$args->item_level = isset($_REQUEST['edit']) ? trim($_REQUEST['edit']) : null;
	$args->doAction = isset($_REQUEST['doAction']) ? $_REQUEST['doAction'] : "default";
	$args->tproject_id = $_SESSION['testprojectID'];
	$args->tproject_name = $_SESSION['testprojectName'];
	$args->testcases2add = isset($_REQUEST['achecked_tc']) ? $_REQUEST['achecked_tc'] : null;
	$args->tcversion_for_tcid = isset($_REQUEST['tcversion_for_tcid']) ? $_REQUEST['tcversion_for_tcid'] : null;
	$args->testcases2remove = isset($_REQUEST['remove_checked_tc']) ? $_REQUEST['remove_checked_tc'] : null;

	// Can be a list (string with , (comma) has item separator), that will be trasformed in an array.
	$keywordSet = isset($_REQUEST['keyword_id']) ? $_REQUEST['keyword_id'] : null;
	
	$args->keyword_id = 0;  
	if(!is_null($keywordSet))
	{
		$args->keyword_id = explode(',',$keywordSet);  
	}
	$args->keywordsFilterType = isset($_REQUEST['keywordsFilterType']) ? $_REQUEST['keywordsFilterType'] : 'OR';

	$args->testcases2order = isset($_REQUEST['exec_order']) ? $_REQUEST['exec_order'] : null;
	$args->linkedOrder = isset($_REQUEST['linked_exec_order']) ? $_REQUEST['linked_exec_order'] : null;
	$args->linkedVersion = isset($_REQUEST['linked_version']) ? $_REQUEST['linked_version'] : null;
	$args->linkedWithCF = isset($_REQUEST['linked_with_cf']) ? $_REQUEST['linked_with_cf'] : null;
	
	$args->feature2fix = isset($_REQUEST['feature2fix']) ? $_REQUEST['feature2fix'] : null;
	$args->userID = $_SESSION['currentUser']->dbID;
	$args->testerID = isset($_REQUEST['testerID']) ? intval($_REQUEST['testerID']) : 0;
    $args->send_mail = isset($_REQUEST['send_mail']) ? $_REQUEST['send_mail'] : false;

	return $args;
}

/*
  function: doReorder
            writes to DB execution order of test case versions 
            linked to testplan.

  args: argsObj: user input data collected via HTML inputs
        tplanMgr: testplan manager object

  returns: -

*/
function doReorder(&$argsObj,&$tplanMgr)
{
    $mapo = null;
  
    // Do this to avoid update if order has not been changed on already linked items      
    if(!is_null($argsObj->linkedVersion))
    {
        // Using memory of linked test case, try to get order
        foreach($argsObj->linkedVersion as $tcid => $tcversion_id)
        {
            if($argsObj->linkedOrder[$tcid] != $argsObj->testcases2order[$tcid] )
            { 
                $mapo[$tcversion_id] = $argsObj->testcases2order[$tcid];
            }    
        }
    }
    
    // Now add info for new liked test cases if any
    if(!is_null($argsObj->testcases2add))
    {
        $tcaseSet = array_keys($argsObj->testcases2add);
        foreach($tcaseSet as $tcid)
        {
        	// This check is needed because, after we have added test case
        	// for a platform, this will not be present anymore
        	// in tcversion_for_tcid, but it's present in  linkedVersion.
        	// IMPORTANT:
        	// We do not allow link of different test case version on a
        	// testplan no matter we are using or not platform feature.
        	//
            $tcversion_id=null;
        	if( isset($argsObj->tcversion_for_tcid[$tcid]) )
        	{
            	$tcversion_id = $argsObj->tcversion_for_tcid[$tcid];
            	//$mapo[$tcversion_id] = $argsObj->testcases2order[$tcid];
            }
            else if( isset($argsObj->linkedVersion[$tcid]) && 
                     !isset($mapo[$argsObj->linkedVersion[$tcid]]))
            {
            	// $mapo[$argsObj->linkedVersion[$tcid]]=$argsObj->testcases2order[$tcid];
            	$tcversion_id = $argsObj->linkedVersion[$tcid];
            }
            if( !is_null($tcversion_id))
            {
            	$mapo[$tcversion_id] = $argsObj->testcases2order[$tcid];
            }
        }
    }  
    
    if(!is_null($mapo))
    {
        $tplanMgr->setExecutionOrder($argsObj->tplan_id,$mapo);  
    }
    
}


/*
  function: initializeGui

  args :
  
  returns: 

*/
function initializeGui(&$dbHandler,$argsObj,&$tplanMgr,&$tcaseMgr)
{
	
    $tcase_cfg = config_get('testcase_cfg');
    $title_separator = config_get('gui_title_separator_1');

    $gui = new stdClass();
    $gui->testCasePrefix = $tcaseMgr->tproject_mgr->getTestCasePrefix($argsObj->tproject_id);
    $gui->testCasePrefix .= $tcase_cfg->glue_character;
    
    $gui->can_remove_executed_testcases=$tcase_cfg->can_remove_executed;




    $gui->keywordsFilterType = $argsObj->keywordsFilterType;

    $gui->keywords_filter = '';
    $gui->has_tc = 0;
    $gui->items = null;
    $gui->has_linked_items = false;
    
    $gui->keywordsFilterType = new stdClass();
    $gui->keywordsFilterType->options = array('OR' => 'Or' , 'AND' =>'And'); 
    $gui->keywordsFilterType->selected=$argsObj->keywordsFilterType;

    // full_control, controls the operations planAddTC_m1.tpl will allow
    // 1 => add/remove
    // 0 => just remove
    $gui->full_control = 1;

    $tplan_info = $tplanMgr->get_by_id($argsObj->tplan_id);
    $gui->testPlanName = $tplan_info['name'];
    $gui->pageTitle = lang_get('test_plan') . $title_separator . $gui->testPlanName;
    $gui->refreshTree = false;
    $gui->testers = getTestersForHtmlOptions($dbHandler,$argsObj->tplan_id,$argsObj->tproject_id);
    $gui->testerID = $argsObj->testerID;
    $gui->send_mail = $argsObj->send_mail;
 
	$platform_mgr = new tlPlatform($dbHandler, $argsObj->tproject_id);
	$gui->platforms = $platform_mgr->getLinkedToTestplan($argsObj->tplan_id);
	$gui->platformsForHtmlOptions = null;
	$gui->usePlatforms = !is_null($gui->platforms);
	if($gui->usePlatforms)
	{
		$gui->platformsForHtmlOptions[0]='';
		foreach($gui->platforms as $elem)
		{
			$gui->platformsForHtmlOptions[$elem['id']] =$elem['name'];
		}
	}

	// 
	$gui->warning_msg = new stdClass();
	$gui->warning_msg->executed = lang_get('executed_can_not_be_removed');
	$actionTitle = 'title_remove_test_from_plan';
	$buttonValue = 'btn_remove_selected_tc';
	$gui->exec_order_input_disabled = 'disabled="disabled"';

	if( $gui->can_remove_executed_testcases )
	{
		$gui->warning_msg->executed = lang_get('has_been_executed');
	}

	if( $gui->full_control )
	{
    	$actionTitle = 'title_add_test_to_plan';
    	$buttonValue = 'btn_add_selected_tc';
		if( $gui->has_linked_items )
		{
	    	$actionTitle = 'title_add_remove_test_to_plan';
	    	$buttonValue = 'btn_add_remove_selected_tc';
		}
		$gui->exec_order_input_disabled = ' ';
	}
	$gui->actionTitle = lang_get($actionTitle);
	$gui->buttonValue = lang_get($buttonValue);

    return $gui;
}


/*
  function: doSaveCustomFields
            writes to DB value of custom fields displayed
            for test case versions linked to testplan.

  args: argsObj: user input data collected via HTML inputs
        tplanMgr: testplan manager object

  returns: -

*/
function doSaveCustomFields(&$argsObj,&$userInput,&$tplanMgr,&$tcaseMgr)
{
    // N.B.: I've use this piece of code also on write_execution(), think is time to create
    //       a method on cfield_mgr class.
    //       One issue: find a good method name
    $cf_prefix = $tcaseMgr->cfield_mgr->get_name_prefix();
	$len_cfp = tlStringLen($cf_prefix);
    $cf_nodeid_pos=4;
    
  	$nodeid_array_cfnames=null;

  	// Example: two test cases (21 and 19 are testplan_tcversions.id => FEATURE_ID)
  	//          with 3 custom fields
  	//
  	// custom_field_[TYPE]_[CFIELD_ID]_[FEATURE_ID]
  	//
  	// (
    // [21] => Array
    //     (
    //         [0] => custom_field_0_3_21
    //         [1] => custom_field_0_7_21
    //         [5] => custom_field_6_9_21
    //     )
    // 
    // [19] => Array
    //     (
    //         [0] => custom_field_0_3_19
    //         [1] => custom_field_0_7_19
    //         [5] => custom_field_6_9_19
    //     )
    // )
    //  	
    foreach($userInput as $input_name => $value)
    {
        if( strncmp($input_name,$cf_prefix,$len_cfp) == 0 )
        {
          $dummy=explode('_',$input_name);
          $nodeid_array_cfnames[$dummy[$cf_nodeid_pos]][]=$input_name;
        } 
    }
     
    // foreach($argsObj->linkedWithCF as $key => $link_id)
    foreach( $nodeid_array_cfnames as $link_id => $customFieldsNames)
    {   
    	
    	
        // Create a SubSet of userInput just with inputs regarding CF for a link_id
        // Example for link_id=21:
        //
        // $cfvalues=( 'custom_field_0_3_21' => A
        //             'custom_field_0_7_21' => 
        //             'custom_field_8_8_21_day' => 0
        //             'custom_field_8_8_21_month' => 0
        //             'custom_field_8_8_21_year' => 0
        //             'custom_field_6_9_21_' => Every day)
        //
        $cfvalues=null;
        foreach($customFieldsNames as $cf)
        {
           $cfvalues[$cf]=$userInput[$cf];
        }  
        $tcaseMgr->cfield_mgr->testplan_design_values_to_db($cfvalues,null,$link_id);
    }
}


/*
  function: doSavePlatforms
            writes to DB execution ... of test case versions linked to testplan.

  args: argsObj: user input data collected via HTML inputs
        tplanMgr: testplan manager object

  returns: -

*/
function doSavePlatforms(&$argsObj,&$tplanMgr)
{
	foreach($argsObj->feature2fix as $feature_id => $tcversion_platform)
	{
		$tcversion_id = key($tcversion_platform);
		$platform_id = current($tcversion_platform);
		if( $platform_id != 0 )
		{
			$tplanMgr->changeLinkedTCVersionsPlatform($argsObj->tplan_id,0,$platform_id,$tcversion_id);
		}	
	}
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
    $mail_details['new']=lang_get('mail_testcase_assigned') . "<br /><br />";
    $mail_subject['new']=lang_get('mail_subject_testcase_assigned');
    $use_testers['new']= true ;
   
    $tcaseSet=null;
    $tcnames=null;
    $email=array();
   
    $userSet[]=$argsObj->userID;
    $userSet[]=$argsObj->testerID;
    
    $userData=tlUser::getByIDs($dbHandler,$userSet);
    $assigner=$userData[$argsObj->userID]->firstName . ' ' . $userData[$argsObj->userID]->lastName ;
              
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
        $tcaseSet[$value['tcase_id']]=$value['tcase_id'];
        $tcversionSet[$value['tcversion_id']]=$value['tcversion_id'];
    } 
    
    $infoSet=$tcaseMgr->get_by_id_bulk($tcaseSet,$tcversionSet);
    foreach($infoSet as $value)
    {
        $tcnames[$value['testcase_id']] = $guiObj->testCasePrefix . $value['tc_external_id'] . ' ' . $value['name'];    
    }

    $path_info = $tcaseMgr->tree_manager->get_full_path_verbose($tcaseSet,array('output_format' => 'simple'));
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
                $userObj=$userData[$user_id];
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


/**
 * initDrawSaveButtons
 *
 */
function initDrawSaveButtons(&$guiObj)
{
	$keySet = array_keys($guiObj->items);

    // Logic to initialize drawSavePlatformsButton
	foreach($keySet as $key)
	{
		$breakLoop = false;
		$testSuite = &$guiObj->items[$key];
		if($testSuite['linked_testcase_qty'] > 0)
		{
			$tcaseSet = array_keys($testSuite['testcases']);
			foreach($tcaseSet as $tcaseKey)
			{
				if( isset($testSuite['testcases'][$tcaseKey]['feature_id'][0]) )
				{
					$breakLoop = true;
					$guiObj->drawSavePlatformsButton = true;
					break;
				}
			}
		} 
		if( $breakLoop )
		{
			break;
		}
	}
    
    // Logic to initialize drawSaveCFieldsButton
	reset($keySet);
	foreach($keySet as $key)
	{
		$breakLoop = false;
		$tcaseSet = &$guiObj->items[$key]['testcases'];
		if( !is_null($tcaseSet) )
		{
			$tcversionSet = array_keys($tcaseSet);
			foreach($tcversionSet as $tcversionID)
			{
				if( isset($tcaseSet[$tcversionID]['custom_fields']) && 
				    !is_null($tcaseSet[$tcversionID]['custom_fields']))
				{
					$breakLoop = true;
					$guiObj->drawSaveCFieldsButton = true;
					break;
				}
			}
		}
		if( $breakLoop )
		{
			break;
		}
	}
}
?>