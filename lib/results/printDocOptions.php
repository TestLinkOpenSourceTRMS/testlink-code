<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later.
 *  
 * @filesource $RCSfile: printDocOptions.php,v $
 * @version $Revision: 1.19 $
 * @modified $Date: 2009/02/25 15:04:07 $ by $Author: havlat $
 * @author 	Martin Havlat
 * 
 *  Settings for generated documents
 * 	- Structure of a document 
 *	- It builds the javascript tree that allow the user select a required part 
 *		Test specification/ Test plan.
 *
 * rev :
 * 	20090222 - havlatm - added new options 
 *      20081116 - franciscom - fixed bug (missed $gui->ajaxTree->loadFromChildren=true)
 *      20080819 - franciscom - fixed bug due to changes in return values of generate*tree()
 *                              TEMPLATE DO NOT WORK YET with EXTJS tree 
 *      20070509 - franciscom - added contribution BUGID
 *
 */
 
require('../../config.inc.php');
require("common.php");
require_once("treeMenu.inc.php");

testlinkInitPage($db);
$templateCfg = templateConfiguration();
$args = init_args();
$gui = initializeGui($db,$args,$_SESSION['basehref']);

$arrFormat = array(
	'format_html' => lang_get('format_html'), 
	'format_odt' => lang_get('format_odt'), 
	'format_msword' => lang_get('format_msword')
);

// Important Notice:
// If you made add/remove elements from this array, you must update
// $printingOptions in printData.php
$arrCheckboxes = array(
	array( 'value' => 'toc', 	'description' => 'opt_show_toc', 		'checked' => 'n'),
	array( 'value' => 'header', 'description' => 'opt_show_suite_txt', 	'checked' => 'n'),
	array( 'value' => 'summary', 'description' => 'opt_show_tc_summary', 'checked' => 'y'),
	array( 'value' => 'body', 	'description' => 'opt_show_tc_body',	'checked' => 'n'),
 	array( 'value' => 'author',	'description' => 'opt_show_tc_author', 	'checked' => 'n'),
	array( 'value' => 'keyword', 'description' => 'opt_show_tc_keys', 	'checked' => 'n')
);

if($_SESSION['testprojectOptReqs'])
{
	$arrCheckboxes[] = array( 'value' => 'requirement', 'description' => 'opt_show_tc_reqs', 'checked' => 'n');
}

if( $gui->report_type == 'testplan')
{
	$arrCheckboxes[] = array( 'value' => 'testplan', 'description' => 'opt_show_tplan_txt', 'checked' => 'n');
}

if( $gui->report_type == 'testreport')
{
	$arrCheckboxes[] = array( 'value' => 'passfail', 'description' => 'opt_show_passfail', 'checked' => 'y');
	$arrCheckboxes[] = array( 'value' => 'metrics', 'description' => 'opt_show_metrics', 'checked' => 'n');
}

// process setting for doc builder
$isSetPrefs = isset($_REQUEST['setPrefs']);
foreach($arrCheckboxes as $key => $elem)
{
	$arrCheckboxes[$key]['description'] = lang_get($elem['description']);
	if($isSetPrefs)
	{
		$field_name = $elem['value'];
		if(isset($_REQUEST[$field_name]) )
		{
			$arrCheckboxes[$key]['checked'] = 'y';   
		}  
	}
}

// generate tree for product test specification
$workPath = 'lib/results/printDocument.php';
$getArguments = "&type=" . $gui->report_type; 
if (($gui->report_type == 'testplan') || ($gui->report_type == 'testreport'))
	$getArguments .= '&docTestPlanId=' . $args->tplan_id;

// generate tree for Test Specification
$treeString = null;
$tree = null;
$treemenu_type = config_get('treemenu_type');
switch($gui->report_type)
{
    case 'testspec':
        if($treemenu_type != 'EXTJS')
        {
	          $treeString = generateTestSpecTree($db,$args->tproject_id, $args->tproject_name,$workPath,
	                                             FOR_PRINTING,HIDE_TESTCASES,ACTION_TESTCASE_DISABLE,$getArguments);
        }
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
  	  	$filters->keywordsFilterType=null;
  	  	$filters->tc_id = null;
  	  	$filters->assignedTo = null;
  	  	$filters->status = null;
  	  	$filters->cf_hash = null;

  	  	$filters->build_id = $latestBuild;
  	  	$filters->hide_testcases=HIDE_TESTCASES;
  	  	$filters->include_unassigned=1;
  	  	$filters->show_testsuite_contents=1;
  	  	$filters->statusAllPrevBuilds=null;
        
  	  	$additionalInfo->useCounters=CREATE_TC_STATUS_COUNTERS_OFF;
  	  	$additionalInfo->useColours=COLOR_BY_TC_STATUS_OFF;
        
		$treeContents = generateExecTree($db,$workPath,$args->tproject_id,$args->tproject_name,
				$args->tplan_id,$testplan_name,$getArguments,$filters,$additionalInfo);
        
      	$treeString = $treeContents->menustring;
      	$gui->ajaxTree = new stdClass();
      	if($treemenu_type == 'EXTJS')
      	{
          	$gui->ajaxTree->root_node = $treeContents->rootnode;
          	$gui->ajaxTree->children = $treeContents->menustring;
          	$gui->ajaxTree->loadFromChildren=true;
          	$gui->ajaxTree->cookiePrefix .= $gui->ajaxTree->root_node->id . "_" ;
      	}
    	break;

    default:
		tLog("Argument _REQUEST['type'] has invalid value", 'ERROR');
		exit();
    	break;
}

$tree = ($treemenu_type == 'EXTJS') ? $treeString :invokeMenu($treeString);

$smarty = new TLSmarty();
$smarty->assign('gui', $gui);
$smarty->assign('treeKind', TL_TREE_KIND);
$smarty->assign('arrCheckboxes', $arrCheckboxes);
$smarty->assign('arrFormat', $arrFormat);
$smarty->assign('selFormat', $args->format);
$smarty->assign('docType', $gui->report_type);
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
    $args=new stdClass();
    $_REQUEST = strings_stripSlashes($_REQUEST);

    $args->tproject_id   = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
    $args->tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : '';

    $args->tplan_id   = isset($_REQUEST['tplan_id']) ? $_REQUEST['tplan_id'] : 0;
    $args->format = isset($_REQUEST['format']) ? $_REQUEST['format'] : 'html';
    $args->report_type = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';
    
    return $args;
}


/**
 * Initialize gui (stdClass) object that will be used as argument
 * in call to Template Engine.
 *
 * @param class pointer argsObj: object containing User Input and some session values
 * 		TBD structure
 * @param string basehref: URL to web home of your testlink installation.
 * 
 * ?     tprojectMgr: test project manager object.
 * ?     treeDragDropEnabled: true/false. Controls Tree drag and drop behaivor.
 * 
 * @return stdClass TBD structure
 */ 
//  rev: 20080817 - franciscom - added code to get total number of testcases 
//  in a test project, to display it on root tree node.
function initializeGui(&$dbHandler,$argsObj,$basehref)
{
    $tcaseCfg=config_get('testcase_cfg');
        
    $gui = new stdClass();
    $tprojectMgr = new testproject($dbHandler);
    $tcasePrefix=$tprojectMgr->getTestCasePrefix($argsObj->tproject_id);

    $gui->tree_title='';
    $gui->ajaxTree=new stdClass();
    $gui->ajaxTree->root_node=new stdClass();
    $gui->ajaxTree->dragDrop=new stdClass();
    $gui->ajaxTree->dragDrop->enabled=false;
    $gui->ajaxTree->dragDrop->BackEndUrl=null;
    $gui->ajaxTree->children='';
     
    // Prefix for cookie used to save tree state
    $gui->ajaxTree->cookiePrefix='print' . str_replace(' ', '_', $argsObj->report_type) . '_';
    
    switch($argsObj->report_type)
    {
        case 'testspec':
	          $gui->tree_title=lang_get('title_tc_print_navigator');
            
            $gui->ajaxTree->loader=$basehref . 'lib/ajax/gettprojectnodes.php?' .
                                   "root_node={$argsObj->tproject_id}&" .
                                   "show_tcases=0&operation=print&" .
                                   "tcprefix=".urlencode($tcasePrefix.$tcaseCfg->glue_character)."}";
	          
	          $gui->ajaxTree->loadFromChildren=0;
	          $gui->ajaxTree->root_node->href="javascript:TPROJECT_PTP({$argsObj->tproject_id})";
            $gui->ajaxTree->root_node->id=$argsObj->tproject_id;

            $tcase_qty = $tprojectMgr->count_testcases($argsObj->tproject_id);
            $gui->ajaxTree->root_node->name=$argsObj->tproject_name . " ($tcase_qty)";
            
            $gui->ajaxTree->cookiePrefix .=$gui->ajaxTree->root_node->id . "_" ;
	      break;
	      
        case 'testplan':
	          $gui->tree_title=lang_get('title_tp_print_navigator');
	          $gui->ajaxTree->loadFromChildren=1;
	          $gui->ajaxTree->loader='';
	      break;
    }

    $gui->report_type=$argsObj->report_type;    
    return $gui;  
}
?>
