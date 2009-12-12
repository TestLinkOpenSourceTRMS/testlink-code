<?php
/** 
* 	TestLink Open Source Project - http://testlink.sourceforge.net/
* 
* 	@version 	$Id: listTestCases.php,v 1.47 2009/12/12 10:12:14 franciscom Exp $
* 	@author 	Martin Havlat
* 
* 	Generates tree menu with test specification. 
*   It builds the javascript tree that allows the user to choose testsuite or testcase.
*
*	@internal revision
*	20091210 - franciscom - test case execution type filter
*   20090308 - franciscom - added option Any in keywords filter
*   20090210 - BUGID 2062 - franciscom -
*/
require_once('../../config.inc.php');
require_once("common.php");
require_once("treeMenu.inc.php");
testlinkInitPage($db);

$templateCfg = templateConfiguration();
$tproject_mgr = new testproject($db);


$spec_cfg = config_get('spec_cfg');

$feature_action = array('edit_tc' => "lib/testcases/archiveData.php",
                        'keywordsAssign' => "lib/keywords/keywordsAssign.php",
                        'assignReqs' => "lib/requirements/reqTcAssign.php");

$treeDragDropEnabled =  array('edit_tc' => (has_rights($db,"mgt_modify_tc") == 'yes'),
                              'keywordsAssign' => false,
                              'assignReqs' => false);

$args = init_args($spec_cfg);
if(isset($feature_action[$args->feature]))
{
	$workPath = $feature_action[$args->feature];
}
else
{
	tLog("Wrong get argument 'feature'.", 'ERROR');
	exit();
}

// Here lazy loading tree configuration is done
$gui = initializeGui($db,$args,$tproject_mgr,$treeDragDropEnabled[$args->feature]);

$draw_filter = $spec_cfg->show_tsuite_filter;
$exclude_branches = null;
$tsuites_combo = null;
if($spec_cfg->show_tsuite_filter)
{
	$mappy = tsuite_filter_mgmt($db,$tproject_mgr,$args->tproject_id,$args->tsuites_to_show);
	$exclude_branches = $mappy['exclude_branches'];
	$tsuites_combo = $mappy['html_options'];
	$draw_filter = $mappy['draw_filter'];
}

$filters = array();
$filters['keywords'] = buildKeywordsFilter($args->keyword_id,$gui);
$filters['executionType'] = buildExecTypeFilter($args->exec_type,$gui);
$applyFilter = !is_null($filters['keywords']) || !is_null($filters['executionType']);

if($applyFilter)
{
	// Bye, Bye Lazy tree:
	// we need to use statically generated because user have choosen to apply a filter
	//
	
	
    // $treeMenu = generateTestSpecTree($db,$args->tproject_id, $args->tproject_name,
    //                                  $workPath,NOT_FOR_PRINTING,
    //                                  SHOW_TESTCASES,DO_ON_TESTCASE_CLICK,
    //                                  NO_ADDITIONAL_ARGS, $keywordsFilter,
    //                                  DO_NOT_FILTER_INACTIVE_TESTCASES,$exclude_branches);

	$options = array('forPrinting' => NOT_FOR_PRINTING, 'hideTestCases' => SHOW_TESTCASES,
	                 'getArguments' => NO_ADDITIONAL_ARGS, 
	                 'tc_action_enabled' => DO_ON_TESTCASE_CLICK,
	                 'ignore_inactive_testcases' => DO_NOT_FILTER_INACTIVE_TESTCASES, 
	                 'exclude_branches' => $exclude_branches);

    $treeMenu = generateTestSpecTree($db,$args->tproject_id, $args->tproject_name,
                                     $workPath,$filters,$options);

	$gui->ajaxTree->loader = '';
	$gui->ajaxTree->root_node = $treeMenu->rootnode;
	$gui->ajaxTree->children = $treeMenu->menustring ? $treeMenu->menustring : "''";
	$gui->ajaxTree->cookiePrefix = $args->feature;
	
	if($applyFilter)
	{
		$gui->ajaxTree->loader = '';  
	}	
}

$gui->treeHeader = lang_get('title_navigator'). ' - ' . lang_get('title_test_spec');
$gui->draw_filter = $draw_filter;
$gui->tsuites_combo = $tsuites_combo;
$gui->tcspec_refresh_on_action = $args->do_refresh;

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->assign('menuUrl',$workPath);
$smarty->display($templateCfg->template_dir . 'tcTree.tpl');

/*
  function: tsuite_filter_mgmt

  args :
  
  returns: map keys  draw_filter -> 1 / 0
                     map for smarty html_options

*/
function tsuite_filter_mgmt(&$db,&$tprojectMgr,$tproject_id,$tsuites_to_show)
{
	$ret = array('draw_filter' => 0,'html_options' => array(0 =>''),
                 'exclude_branches' => null);
             
	$fl_tsuites = $tprojectMgr->get_first_level_test_suites($tproject_id,'smarty_html_options');
	if($tsuites_to_show > 0)
	{
		foreach($fl_tsuites as $tsuite_id => $name)
     	{
			if($tsuite_id != $tsuites_to_show)
        		$ret['exclude_branches'][$tsuite_id] = 'exclude_me';
     	}  
  	} 
  
	$ret['draw_filter'] = (!is_null($fl_tsuites) && count($fl_tsuites) > 0) ? 1 : 0;
	$tsuites_combo = array(0 =>'');
  	if($ret['draw_filter'])
  	{
    	// add blank option as first choice
    	$ret['html_options'] += $fl_tsuites;
  	}
  	return $ret;
}

/**
 * 
 *
 */
function init_args($spec_cfg)
{
	$iParams = array("feature" => array(tlInputParameter::STRING_N,0,50),
			         "keyword_id" => array(tlInputParameter::ARRAY_INT),
			         "keywordsFilterType" => array(tlInputParameter::STRING_N,0,5),
			         "tsuites_to_show" => array(tlInputParameter::INT_N),
					 "tcspec_refresh_on_action" => array(tlInputParameter::INT_N),
					 "hidden_tcspec_refresh_on_action" => array(tlInputParameter::INT_N),
					 "exec_type" => array(tlInputParameter::INT_N));
					 
	$args = new stdClass();
    R_PARAMS($iParams,$args);
    
    if (!$args->keywordsFilterType)
    {
    	$args->keywordsFilterType = "OR";
    }
    $args->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
    $args->tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : '';
    $args->basehref = $_SESSION['basehref'];
    $args->do_refresh = "no";
   
    if (!is_null($args->hidden_tcspec_refresh_on_action))
    {
    	if (!is_null($args->tcspec_refresh_on_action))
    	{
    		$args->do_refresh = $args->tcspec_refresh_on_action ? "yes" : "no";
        }
    }
    else if (isset($_SESSION["tcspec_refresh_on_action"]))
    {
    	$args->do_refresh = ($_SESSION["tcspec_refresh_on_action"] == "yes") ? "yes" : "no";
    }
    else
    {	
    	$args->do_refresh = ($spec_cfg->automatic_tree_refresh > 0) ? "yes": "no";
    }
	$_SESSION['tcspec_refresh_on_action'] = $args->do_refresh;
    	
	return $args;  
}

/*
  function: initializeGui
            initialize gui (stdClass) object that will be used as argument
            in call to Template Engine.
 
  args: argsObj: object containing User Input and some session values
        basehref: URL to web home of your testlink installation.
        tprojectMgr: test project manager object.
        treeDragDropEnabled: true/false. Controls Tree drag and drop behaivor.
        
  
  
  returns: stdClass object
  
  rev: 20080817 - franciscom
       added code to get total number of testcases in a test project, to display
       it on root tree node.

*/
function initializeGui($dbHandler,$args,&$tprojectMgr,$treeDragDropEnabled)
{
    $tcaseCfg = config_get('testcase_cfg');
    $gui_open = config_get('gui_separator_open');
    $gui_close = config_get('gui_separator_close');
        
    $gui = new stdClass();
    $gui->tree = null;
    $gui->str_option_any = $gui_open . lang_get('any') . $gui_close;

    $tcasePrefix = $tprojectMgr->getTestCasePrefix($args->tproject_id);
    
    $gui->ajaxTree = new stdClass();
    $gui->ajaxTree->loader = $args->basehref . 'lib/ajax/gettprojectnodes.php?' .
                             "root_node={$args->tproject_id}&" .
                             "tcprefix=" . urlencode($tcasePrefix. $tcaseCfg->glue_character) . "&" .
                             "filter_node={$args->tsuites_to_show}";

    $gui->ajaxTree->root_node = new stdClass();
    $gui->ajaxTree->root_node->href = "javascript:EP({$args->tproject_id})";
    $gui->ajaxTree->root_node->id = $args->tproject_id;
    
  	$tcase_qty = $tprojectMgr->count_testcases($args->tproject_id);
    $gui->ajaxTree->root_node->name = $args->tproject_name . " ($tcase_qty)";
  
    $gui->ajaxTree->dragDrop = new stdClass();
    $gui->ajaxTree->dragDrop->enabled = $treeDragDropEnabled;
    $gui->ajaxTree->dragDrop->BackEndUrl = $args->basehref . 'lib/ajax/dragdroptprojectnodes.php';
    
    // TRUE -> beforemovenode() event will use our custom implementation 
    $gui->ajaxTree->dragDrop->useBeforeMoveNode = false;
  
  
    // Prefix for cookie used to save tree state
    $gui->ajaxTree->cookiePrefix='tproject_' . $gui->ajaxTree->root_node->id . "_" ;
  
    // 20080831 - franciscom - Custom attribute
    // You can access to it's value using public property 'attributes' of object of Class Ext.tree.TreeNode 
    // example: mynode.attributes.testlink_node_type
    //
    // Important: 
    // Fore root node (this node)
    // You need to initialize every custom property you want to add to root node
    // on the js file that create it (treebyloader.js) and smarty template
    // 
    //
    // Also this property must be managed in php code used to generate JSON code.
    //
    // I'appologize for using MAGIC constant
    $gui->ajaxTree->root_node->testlink_node_type='testproject';

    
    $gui->tsuite_choice = $args->tsuites_to_show;  
    
    // 20090118 - franciscom    
    $gui->keywordsFilterType = new stdClass();
    $gui->keywordsFilterType->options = array('OR' => 'Or' , 'AND' =>'And'); 
    $gui->keywordsFilterType->selected = $args->keywordsFilterType;
    $gui->keywordsFilterItemQty = 0;
    $gui->keyword_id = $args->keyword_id; 
    $gui->keywords_map = $tprojectMgr->get_keywords_map($args->tproject_id); 
    if(!is_null($gui->keywords_map))
    {
        $gui->keywordsFilterItemQty = min(count($gui->keywords_map),3);
        $gui->keywords_map = array(0 => $gui->str_option_any) + $gui->keywords_map;
    }


    // 20091210 - franciscom    
    $tcaseMgr = new testcase($dbHandler);
    $gui->exec_type = $args->exec_type; 
    $gui->exec_type_map = $tcaseMgr->get_execution_types(); 
    $gui->exec_type_map = array(0 => $gui->str_option_any) + $gui->exec_type_map;
     
    return $gui;  
}
?>