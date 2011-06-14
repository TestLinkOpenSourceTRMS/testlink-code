<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later.
 *  
 * @filesource $RCSfile: printDocOptions.php,v $
 * @version $Revision: 1.41 $
 * @modified $Date: 2010/11/06 18:46:33 $ by $Author: amkhullar $
 * @author 	Martin Havlat
 * 
 *  Settings for generated documents
 * 	- Structure of a document 
 *	- It builds the javascript tree that allow the user select a required part 
 *		Test specification/ Test plan.
 *
 * rev :
 * 		20101106 - amitkhullar - BUGID 2738: Contribution: option to include TC Exec notes in test report
 *		20101003 - franciscom - init_checkboxes() refactored used common pattern
 *		20100723 - BUGID 3451 and related
 *  	20100326 - asimon - refactored to include requirement documents
 *                          added init_checkboxes()
 *		20090322 - amkhullar - added new option custom fields while printing Test plan/report
 * 		20090222 - havlatm - added new options 
 *
 */
require_once("../../config.inc.php");
require_once("../../cfg/reports.cfg.php");
require_once("common.php");
require_once("treeMenu.inc.php");

testlinkInitPage($db);
$templateCfg = templateConfiguration();
$args = init_args();
$gui = initializeGui($db,$args);
$arrCheckboxes = init_checkboxes($args);

$workPath = 'lib/results/printDocument.php';
switch($args->doc_type)
{
	case 'testplan':
	case 'testreport':
	$addTestPlanID = true;
	break;
	
	default:
	$addTestPlanID = false;
	break;
}

$getArguments = "&type=" . $args->doc_type; 

if ($addTestPlanID) {
	$getArguments .= '&docTestPlanId=' . $args->tplan_id;
}

// generate tree
$tree = null;
$additionalArgs = '';
switch($args->doc_type) 
{
    case 'testspec':
	case 'reqspec':
	break;

    case 'testplan':
    case 'testreport':
		$tplan_mgr = new testplan($db);
		$tplan_info = $tplan_mgr->get_by_id($args->tplan_id);
		$testplan_name = htmlspecialchars($tplan_info['name']);
		$latestBuild = $tplan_mgr->get_max_build_id($args->tplan_id);
	      
		$filters = new stdClass();
  	  	$additionalInfo = new stdClass();
        
  	  	// ----- BUGID 3451 and related ---------------------------------------
  	  	// Notice: these variables were wrong since the changes to filtering system,
  	  	// but they did not cause the bug responsible for 3451.
  	  	// See print.inc.php for the real solution!
  	  	
		// Set of filters Off
  	  	$filters->filter_keywords = null;
  	  	$filters->filter_keywords_filter_type = null;
  	  	$filters->filter_tc_id = null;
  	  	$filters->filter_assigned_user = null;
  	  	$filters->filter_result_result = null;
  	  	$filters->filter_custom_fields = null;
  	  	$filters->setting_platform = null;
  	  	
  	  	$filters->filter_result_build = $latestBuild;
  	  	$filters->hide_testcases = HIDE_TESTCASES;
  	  	$filters->filter_assigned_user_include_unassigned = true;
  	  	$filters->show_testsuite_contents = true;
		// ----- BUGID 3451 and related ---------------------------------------
  	  	
  	  	$additionalInfo->useCounters = CREATE_TC_STATUS_COUNTERS_OFF;
  	  	$additionalInfo->useColours = COLOR_BY_TC_STATUS_OFF;
        
        list($treeContents, $additionalArgs) = generateExecTree($db,$workPath,$args->tproject_id,$args->tproject_name,
				                                                $args->tplan_id,$testplan_name,$filters,$additionalInfo);
        
      	$tree = $treeContents->menustring;
      	$gui->ajaxTree = new stdClass();
      	$gui->ajaxTree->root_node = $treeContents->rootnode;
        $gui->ajaxTree->children = $treeContents->menustring;
        $gui->ajaxTree->loadFromChildren = true;
        // BUGID 4613 - improved cookie prefix for test plan report and test report
        $report = $args->doc_type == "testplan" ? "test_plan_report" : "test_report";
        $gui->ajaxTree->cookiePrefix .= "{$report}_tplan_id_{$args->tplan_id}_";
        
      	break;

    default:
		tLog("Argument _REQUEST['type'] has invalid value", 'ERROR');
		exit();
    	break;
}


$smarty = new TLSmarty();
$smarty->assign('gui', $gui);
$smarty->assign('arrCheckboxes', $arrCheckboxes);
$smarty->assign('selFormat', $args->format);
$smarty->assign('docType', $args->doc_type);
$smarty->assign('docTestPlanId', $args->tplan_id);
$smarty->assign('tree', $tree);
$smarty->assign('menuUrl', $workPath);
$smarty->assign('args', $getArguments);
$smarty->assign('additionalArgs',$additionalArgs);

$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/**
 * get user input and create an object with properties representing this inputs.
 * @return stdClass object 
 */
function init_args()
{
	$args = new stdClass();
	$iParams = array("tplan_id" => array(tlInputParameter::INT_N),
			         "format" => array(tlInputParameter::INT_N,999),
					 "type" => array(tlInputParameter::STRING_N,0,100));	
		
	R_PARAMS($iParams,$args);
	
	//@TODO schlundus, rename request param to type
	$args->doc_type = $args->type;
    $args->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
    $args->tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : '';

    $args->basehref = $_SESSION['basehref'];
    $args->testprojectOptReqs = $_SESSION['testprojectOptions']->requirementsEnabled;
    
    return $args;
}


/**
 * Initialize gui (stdClass) object that will be used as argument
 * in call to Template Engine.
 *
 * @param class pointer args: object containing User Input and some session values
 * 		TBD structure
 * 
 * ?     tprojectMgr: test project manager object.
 * ?     treeDragDropEnabled: true/false. Controls Tree drag and drop behaivor.
 * 
 * @return stdClass TBD structure
 */ 
//  rev: 20080817 - franciscom - added code to get total number of testcases 
//  in a test project, to display it on root tree node.
function initializeGui(&$db,$args)
{
    $tcaseCfg = config_get('testcase_cfg');
    $reqCfg = config_get('req_cfg');
        
    $gui = new stdClass();
    $gui->mainTitle = '';
    $tprojectMgr = new testproject($db);
    $tcasePrefix = $tprojectMgr->getTestCasePrefix($args->tproject_id);

    $gui->tree_title = '';
    $gui->ajaxTree = new stdClass();
    $gui->ajaxTree->root_node = new stdClass();
    $gui->ajaxTree->dragDrop = new stdClass();
    $gui->ajaxTree->dragDrop->enabled = false;
    $gui->ajaxTree->dragDrop->BackEndUrl = null;
    $gui->ajaxTree->children = '';
     
    // BUGID 4613 - improved cookie prefix for test spec doc and req spec doc
    $gui->ajaxTree->cookiePrefix = $args->doc_type . '_doc_';
    $gui->doc_type = $args->doc_type;
    
    switch($args->doc_type)
    {
    	// BUGID 3067
    	case 'reqspec':
    		$gui->tree_title = lang_get('title_req_print_navigator');
            
           	$gui->ajaxTree->loader =  $args->basehref . 'lib/ajax/getrequirementnodes.php?' .
                                   "root_node={$args->tproject_id}&" .
                                   "show_children=0&operation=print";
	        
	       	$gui->ajaxTree->loadFromChildren = 0;
	       	$gui->ajaxTree->root_node->href = "javascript:TPROJECT_PTP_RS({$args->tproject_id})";
           	$gui->ajaxTree->root_node->id = $args->tproject_id;

            $req_qty = $tprojectMgr->count_all_requirements($args->tproject_id);
            $gui->ajaxTree->root_node->name = htmlspecialchars($args->tproject_name) . " ($req_qty)";
            $gui->ajaxTree->cookiePrefix .= "tproject_id_" . $gui->ajaxTree->root_node->id . "_" ;
	        $gui->mainTitle = lang_get('requirement_specification_report');
    	break;
    	// end BUGID 3067
    	
		case 'testspec':
			$gui->tree_title = lang_get('title_tc_print_navigator');
            
           	$gui->ajaxTree->loader =  $args->basehref . 'lib/ajax/gettprojectnodes.php?' .
                                   "root_node={$args->tproject_id}&" .
                                   "show_tcases=0&operation=print&" .
                                   "tcprefix=". urlencode($tcasePrefix.$tcaseCfg->glue_character) ."}";
	          
	       	$gui->ajaxTree->loadFromChildren = 0;
	       	$gui->ajaxTree->root_node->href = "javascript:TPROJECT_PTP({$args->tproject_id})";
           	$gui->ajaxTree->root_node->id = $args->tproject_id;

            $tcase_qty = $tprojectMgr->count_testcases($args->tproject_id);
            $gui->ajaxTree->root_node->name = htmlspecialchars($args->tproject_name) . " ($tcase_qty)";
            $gui->ajaxTree->cookiePrefix .= "tproject_id_" . $gui->ajaxTree->root_node->id . "_" ;
	        $gui->mainTitle = lang_get('testspecification_report');
	    break;
	    
	    case 'testreport':
	        $gui->mainTitle = lang_get('test_report');
	    break;
	      
        case 'testplan':
	          $gui->tree_title = lang_get('title_tp_print_navigator');
	          $gui->ajaxTree->loadFromChildren = 1;
	          $gui->ajaxTree->loader = '';
	          $gui->mainTitle = lang_get('test_plan');
	    break;
    }
    $gui->mainTitle .=  ' - ' . lang_get('doc_opt_title');

    
    $gui->outputFormat = array(FORMAT_HTML => lang_get('format_html'), 
	                           FORMAT_ODT => lang_get('format_odt'), 
	                           FORMAT_MSWORD => lang_get('format_msword'));
    return $gui;  
}

/**
 * Initializes the checkbox options.
 * Made this a function to simplify handling of differences 
 * between printing for requirements and testcases and to make code more readable.
 * 
 * @author Andreas Simon
 * 
 * @param stdClass $args reference to user input parameters
 * 
 * @return array $arrCheckboxes
 */
function init_checkboxes(&$args) {
	
	// Important Notice:
	// If you want to add or remove elements in this array, you must also update
	// $printingOptions in printDocument.php and tree_getPrintPreferences() in testlink_library.js
	
	$arrCheckboxes = array();
	
	// these are the options which are always needed, type-specific ones follow below in switch
	$arrCheckboxes[] = array( 'value' => 'toc','description' => 'opt_show_toc', 'checked' => 'n');
	$arrCheckboxes[] = array( 'value' => 'headerNumbering','description' => 'opt_show_hdrNumbering','checked' => 'n');
	
	switch($args->doc_type) 
	{
		case 'reqspec':
			$key2init= array('req_spec_scope','req_spec_author','req_spec_overwritten_count_reqs',
						     'req_spec_type','req_spec_cf','req_scope','req_author','req_status',
							 'req_type','req_cf','req_relations','req_linked_tcs','req_coverage');

			$key2init2yes = array('req_spec_scope' => 'y','req_scope' => 'y');
			foreach($key2init as $key)
			{
				$checked = isset($key2init2yes[$key]) ? $key2init2yes[$key] : 'n';
				$arrCheckboxes[] = array('value' => $key,'description' => 'opt_' . $key, 'checked' => $checked);
			} 
		break;
		
		default:
			$arrCheckboxes[] = array('value' => 'header','description' => 'opt_show_suite_txt','checked' => 'n');
			$arrCheckboxes[] = array('value' => 'summary','description' => 'opt_show_tc_summary','checked' => 'y');
			$arrCheckboxes[] = array('value' => 'body','description' => 'opt_show_tc_body','checked' => 'n');
			$arrCheckboxes[] = array('value' => 'author','description' => 'opt_show_tc_author','checked' => 'n');
			$arrCheckboxes[] = array('value' => 'keyword','description' => 'opt_show_tc_keys','checked' => 'n');
			$arrCheckboxes[] = array('value' => 'cfields','description' => 'opt_show_cfields','checked' => 'n');

			if($args->testprojectOptReqs) 
			{
				$arrCheckboxes[] = array( 'value' => 'requirement','description' => 'opt_show_tc_reqs','checked' => 'n');
			}

			if($args->doc_type == 'testplan') 
			{
				$arrCheckboxes[] = array( 'value' => 'testplan','description' => 'opt_show_tplan_txt','checked' => 'n');
			} 
			else if ($args->doc_type == 'testreport')	
			{
				$arrCheckboxes[] = array('value' => 'notes', 'description' => 'opt_show_tc_notes',	'checked' => 'n');
				$arrCheckboxes[] = array( 'value' => 'passfail','description' => 'opt_show_passfail','checked' => 'y');
				$arrCheckboxes[] = array( 'value' => 'metrics','description' => 'opt_show_metrics','checked' => 'n');
			}
		break;		
	}

	foreach ($arrCheckboxes as $key => $elem) 
	{
		$arrCheckboxes[$key]['description'] = lang_get($elem['description']);
	}
	
	return $arrCheckboxes;
}
?>