<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @package 	TestLink
 * @copyright 	2007-2009, TestLink community 
 * @version    	CVS: $Id: planAddTC.php,v 1.75 2009/08/08 14:11:50 franciscom Exp $
 * @filesource	http://testlink.cvs.sourceforge.net/viewvc/testlink/testlink/lib/functions/object.class.php?view=markup
 * @link 		http://www.teamst.org/index.php
 * 
 * @internal Revisions:
 *           link/unlink test cases to a test plan
 **/

require_once('../../config.inc.php');
require_once("common.php");
require("specview.php");

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
		    $items_to_link = my_array_intersect_keys($args->testcases2add,$args->tcversion_for_tcid);
		    $tplan_mgr->link_tcversions($args->tplan_id,$items_to_link,$args->userID);
	    }

	    if(!is_null($args->testcases2remove))
	    {
		    // remove without warning
		    $tplan_mgr->unlink_tcversions($args->tplan_id,$args->testcases2remove);
	    }
	    doReorder($args,$tplan_mgr);
	    $do_display = 1;
	    break;
	
    case 'doReorder':
		doReorder($args,$tplan_mgr);
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


	// define('DONT_PRUNE',0);
	// define('WRITE_BUTTON_ALWAYS',0);
	// $out = gen_spec_view($db,'testproject',$args->tproject_id,$args->object_id,$tsuite_data['name'],
	//                      $tplan_linked_tcversions,null,$args->keyword_id,
	//                      $testCaseSet,WRITE_BUTTON_ALWAYS,DONT_PRUNE,$add_custom_fields);
  
	$out = gen_spec_view($db,'testproject',$args->tproject_id,$args->object_id,$tsuite_data['name'],
	                     $tplan_linked_tcversions,null,$filters,$opt);
  
  	$gui->has_tc = ($out['num_tc'] > 0 ? 1 : 0);
	$gui->items = $out['spec_view'];
	$gui->has_linked_items = $out['has_linked_items'];
	$gui->add_custom_fields = $opt['add_custom_fields'];
  
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
		$args->keyword_id = explode(',',$keywordSet);  
	
	$args->keywordsFilterType = isset($_REQUEST['keywordsFilterType']) ? $_REQUEST['keywordsFilterType'] : 'OR';

	$args->testcases2order = isset($_REQUEST['exec_order']) ? $_REQUEST['exec_order'] : null;
	$args->linkedOrder = isset($_REQUEST['linked_exec_order']) ? $_REQUEST['linked_exec_order'] : null;
	$args->linkedVersion = isset($_REQUEST['linked_version']) ? $_REQUEST['linked_version'] : null;
	$args->linkedWithCF = isset($_REQUEST['linked_with_cf']) ? $_REQUEST['linked_with_cf'] : null;
	
	$args->userID = $_SESSION['currentUser']->dbID;

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
        foreach($argsObj->testcases2add as $tcid)
        {
            $tcversion_id = $argsObj->tcversion_for_tcid[$tcid];
            $mapo[$tcversion_id] = $argsObj->testcases2order[$tcid];
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
    $gui->pageTitle = lang_get('test_plan') . $title_separator . $tplan_info['name'];
    $gui->refreshTree = false;


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
    // function testplan_design_values_to_db($hash,$node_id,$link_id,$cf_map=null,$hash_type=null)
    // N.B.: I've use this piece of code also on write_execution(), think is time to create
    //       a method on cfield_mgr class.
    //       One issue: find a good method name
    $cf_prefix = $tcaseMgr->cfield_mgr->get_name_prefix();
	$len_cfp = tlStringLen($cf_prefix);
    $cf_nodeid_pos=4;
    
  	$nodeid_array_cfnames=null;
  	// Example: two test cases (21 adn 19 are testplan_tcversions.id)
  	//          with 3 custom fields
  	// (
    // [21] => Array
    //     (
    //         [0] => custom_field_0_3_21
    //         [1] => custom_field_0_7_21
    //         [5] => custom_field_6_9_21_
    //     )
    // 
    // [19] => Array
    //     (
    //         [0] => custom_field_0_3_19
    //         [1] => custom_field_0_7_19
    //         [5] => custom_field_6_9_19_
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
?>