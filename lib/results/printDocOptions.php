<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later.
 *  
 * @filesource $RCSfile: printDocOptions.php,v $
 * @version $Revision: 1.31 $
 * @modified $Date: 2010/02/17 21:32:44 $ by $Author: franciscom $
 * @author 	Martin Havlat
 * 
 *  Settings for generated documents
 * 	- Structure of a document 
 *	- It builds the javascript tree that allow the user select a required part 
 *		Test specification/ Test plan.
 *
 * rev :
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

// Important Notice:
// If you made add/remove elements from this array, you must update
// $printingOptions in printDocument.php and tree_getPrintPreferences() in testlink_library.js
$arrCheckboxes = array(
	array( 'value' => 'toc', 'description' => 'opt_show_toc', 'checked' => 'n'),
	array( 'value' => 'header', 'description' => 'opt_show_suite_txt', 'checked' => 'n'),
	array( 'value' => 'summary', 'description' => 'opt_show_tc_summary', 'checked' => 'y'),
	array( 'value' => 'body', 'description' => 'opt_show_tc_body', 'checked' => 'n'),
 	array( 'value' => 'author', 'description' => 'opt_show_tc_author', 'checked' => 'n'),
	array( 'value' => 'keyword', 'description' => 'opt_show_tc_keys', 'checked' => 'n'),
	array( 'value' => 'cfields', 'description' => 'opt_show_cfields', 'checked' => 'n'),
	array( 'value' => 'headerNumbering', 'description' => 'opt_show_hdrNumbering', 'checked' => 'n')
);

if($args->testprojectOptReqs)
{
	$arrCheckboxes[] = array( 'value' => 'requirement', 
	                          'description' => 'opt_show_tc_reqs', 'checked' => 'n');
}

$bAddTestPlanID = false; 
if($args->doc_type == 'testplan')
{
	$arrCheckboxes[] = array( 'value' => 'testplan', 
	                          'description' => 'opt_show_tplan_txt', 'checked' => 'n');
	$bAddTestPlanID = true;
}
else if($args->doc_type == 'testreport')
{
	$arrCheckboxes[] = array( 'value' => 'passfail', 
	                          'description' => 'opt_show_passfail', 'checked' => 'y');
	$arrCheckboxes[] = array( 'value' => 'metrics', 
	                          'description' => 'opt_show_metrics', 'checked' => 'n');
	$bAddTestPlanID = true;
}
foreach($arrCheckboxes as $key => $elem)
{
	$arrCheckboxes[$key]['description'] = lang_get($elem['description']);
}

// generate tree for product test specification
$workPath = 'lib/results/printDocument.php';
$getArguments = "&type=" . $args->doc_type; 
if ($bAddTestPlanID)
	$getArguments .= '&docTestPlanId=' . $args->tplan_id;

// generate tree for Test Specification
$tree = null;
switch($args->doc_type) 
{
    case 'testspec':
	    break;

    case 'testplan':
    case 'testreport':
		$tplan_mgr = new testplan($db);
		$tplan_info = $tplan_mgr->get_by_id($args->tplan_id);
		$testplan_name = htmlspecialchars($tplan_info['name']);
		$latestBuild = $tplan_mgr->get_max_build_id($args->tplan_id);
	      
		$filters = new stdClass();
  	  	$additionalInfo = new stdClass();
        
        // Set of filters Off
		$filters->keyword_id = null;
  	  	$filters->keywordsFilterType = null;
  	  	$filters->tc_id = null;
  	  	$filters->assignedTo = null;
  	  	$filters->status = null;
  	  	$filters->cf_hash = null;
		$filters->platform_id = null;

  	  	$filters->build_id = $latestBuild;
  	  	$filters->hide_testcases = HIDE_TESTCASES;
  	  	$filters->include_unassigned = 1;
  	  	$filters->show_testsuite_contents = 1;
  	  	$filters->statusAllPrevBuilds = null;
        
  	  	$additionalInfo->useCounters = CREATE_TC_STATUS_COUNTERS_OFF;
  	  	$additionalInfo->useColours = COLOR_BY_TC_STATUS_OFF;
        
		$treeContents = generateExecTree($db,$workPath,$args->tproject_id,$args->tproject_name,
				                         $args->tplan_id,$testplan_name,$getArguments,
				                         $filters,$additionalInfo);
        
      	$tree = $treeContents->menustring;
      	$gui->ajaxTree = new stdClass();
      	$gui->ajaxTree->root_node = $treeContents->rootnode;
        $gui->ajaxTree->children = $treeContents->menustring;
        $gui->ajaxTree->loadFromChildren = true;
        $gui->ajaxTree->cookiePrefix .= $gui->ajaxTree->root_node->id . "_" ;
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
					 "type" => array(tlInputParameter::STRING_N,0,100),
	);	
		
	R_PARAMS($iParams,$args);
	
	//@TODO schlundus, rename request param to type
	$args->doc_type = $args->type;
    $args->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
    $args->tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : '';

    $args->basehref = $_SESSION['basehref'];
    // $args->testprojectOptReqs = $_SESSION['testprojectOptReqs'];
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
     
    // Prefix for cookie used to save tree state
    $gui->ajaxTree->cookiePrefix = 'print' . str_replace(' ', '_', $args->doc_type) . '_';
    $gui->doc_type = $args->doc_type;    
    
    switch($args->doc_type)
    {
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
            $gui->ajaxTree->cookiePrefix .= $gui->ajaxTree->root_node->id . "_" ;
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
?>