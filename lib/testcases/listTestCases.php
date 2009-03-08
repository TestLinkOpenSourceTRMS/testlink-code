<?php
/** 
* 	TestLink Open Source Project - http://testlink.sourceforge.net/
* 
* 	@version 	$Id: listTestCases.php,v 1.40 2009/03/08 18:49:11 franciscom Exp $
* 	@author 	Martin Havlat
* 
* 	Generates tree menu with test specification. 
*   It builds the javascript tree that allows the user to choose testsuite or testcase.
*
*   rev: 
*        20090308 - franciscom - added option Any in keywords filter
*        20090210 - BUGID 2062 - franciscom -
*        20080817 - franciscom - initializeGui(): added code to get total number of 
*                                                 testcases in a test project, to display
*                                                 it on root tree node.
*
*        20080705 - franciscom - removed obsolte config parameter
*        20080608 - franciscom - user rights need to be checked in order to enable/disable
*                                javascript tree operations like drag & drop.
*
*        20080603 - franciscom - added tcase prefix in call to tree loader
*        20080525 - franciscom - refactored to use ext js tree
*        20070217 - franciscom - added test suite filter
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

$treeDragDropEnabled =  array('edit_tc' => has_rights($db,"mgt_modify_tc")=='yes' ? true: false,
                              'keywordsAssign' => false,
                              'assignReqs' => false);

$args=init_args();
if(!is_null($args->feature) && strlen($args->feature))
{
	if(isset($feature_action[$args->feature]))
	{
		$workPath = $feature_action[$args->feature];
	}
	else
	{
		tLog("Wrong get argument 'feature'.", 'ERROR');
		exit();
	}
}
else
{
	tLog("Missing argument 'feature'.", 'ERROR');
	exit();
}

$gui=initializeGui($args,$_SESSION['basehref'],$tproject_mgr,$treeDragDropEnabled[$args->feature]);
$do_refresh_on_action = manage_tcspec($_REQUEST,$_SESSION,
                                    'tcspec_refresh_on_action','hidden_tcspec_refresh_on_action',
                                    $spec_cfg->automatic_tree_refresh);

$_SESSION['tcspec_refresh_on_action'] = $do_refresh_on_action;

$title = lang_get('title_navigator'). ' - ' . lang_get('title_test_spec');




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
$treemenu_type = config_get('treemenu_type');

$keywordsFilter = buildKeywordsFilter($args->keyword_id,$gui);
$applyFilter = !is_null($keywordsFilter);
$buildCompleteTree = $treemenu_type != 'EXTJS' || ($treemenu_type == 'EXTJS' && $applyFilter);

if($buildCompleteTree)
{
    $treeMenu = generateTestSpecTree($db,$args->tproject_id, $args->tproject_name,
                                     $workPath,NOT_FOR_PRINTING,
                                     SHOW_TESTCASES,DO_ON_TESTCASE_CLICK,
                                     NO_ADDITIONAL_ARGS, $keywordsFilter,
                                     DO_NOT_FILTER_INACTIVE_TESTCASES,$exclude_branches);
    
    if($treemenu_type == 'EXTJS' )
    {
        $gui->ajaxTree->loader = '';
        $gui->ajaxTree->root_node = $treeMenu->rootnode;
        $gui->ajaxTree->children = $treeMenu->menustring ? $treeMenu->menustring : "''";
        $gui->ajaxTree->cookiePrefix = $args->feature;
    }
    else
    {
        $gui->ajaxTree = null;
        $gui->tree = invokeMenu($treeMenu->menustring,null,null);
    }

    if( $applyFilter )
    {
        $gui->ajaxTree->loader='';  
    }
}

$gui->treeHeader=$title;
$gui->draw_filter=$draw_filter;
$gui->tsuites_combo=$tsuites_combo;
$gui->tcspec_refresh_on_action=$do_refresh_on_action;

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->assign('treeKind', TL_TREE_KIND);
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
  

  $ret=array('draw_filter' => 0,
             'html_options' => array(0 =>''),
             'exclude_branches' => null);
             
  $fl_tsuites=$tprojectMgr->get_first_level_test_suites($tproject_id,'smarty_html_options');
  if( $tsuites_to_show > 0 )
  {
     foreach($fl_tsuites as $tsuite_id => $name)
     {
        if($tsuite_id != $tsuites_to_show)
        {
          $ret['exclude_branches'][$tsuite_id] = 'exclude_me';
        } 
     }  
  } 
  
  $ret['draw_filter']=(!is_null($fl_tsuites) && count($fl_tsuites) > 0) ? 1 :0;
  $tsuites_combo=array(0 =>'');
  if($ret['draw_filter'])
  {
    // add blank option as first choice
    $ret['html_options'] += $fl_tsuites;
  }
  return($ret);
}


/*
  function: 

  args:
  
  returns: 

*/
function manage_tcspec($hash_REQUEST,$hash_SESSION,$key2check,$hidden_name,$default)
{
    if (isset($hash_REQUEST[$hidden_name]))
    {
      $do_refresh = "no";
      if( isset($hash_REQUEST[$key2check]) )
      {
  	    $do_refresh = $hash_REQUEST[$key2check] > 0 ? "yes": "no";
      }
    }
    elseif (isset($hash_SESSION[$key2check]))
    {
       $do_refresh = $hash_SESSION[$key2check] > 0 ? "yes": "no";
    }
    else
    {  
       $do_refresh = $default > 0 ? "yes": "no";
    }
    return $do_refresh;
}

/*
  function: init_args

  args:
  
  returns: 

*/
function init_args()
{
    $args = new stdClass();
    $_REQUEST = strings_stripSlashes($_REQUEST);

    $args->feature = isset($_REQUEST['feature']) ? $_REQUEST['feature'] : null;
    $args->tproject_id   = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
    $args->tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : '';
    $args->tsuites_to_show = isset($_REQUEST['tsuites_to_show']) ? $_REQUEST['tsuites_to_show'] : 0;
  
  
    // Is an array because is a multiselect 
    $args->keyword_id = isset($_REQUEST['keyword_id']) ? $_REQUEST['keyword_id'] : 0;
    $args->keywordsFilterType =isset($_REQUEST['keywordsFilterType']) ? $_REQUEST['keywordsFilterType'] : 'OR';

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
function initializeGui($argsObj,$basehref,&$tprojectMgr,$treeDragDropEnabled)
{
    $tcaseCfg=config_get('testcase_cfg');
    $gui_open = config_get('gui_separator_open');
    $gui_close = config_get('gui_separator_close');
        
    $gui = new stdClass();
    $gui->tree=null;
    $gui->str_option_any = $gui_open . lang_get('any') . $gui_close;

    $tcasePrefix=$tprojectMgr->getTestCasePrefix($argsObj->tproject_id);
    
    $gui->ajaxTree=new stdClass();
    $gui->ajaxTree->loader=$basehref . 'lib/ajax/gettprojectnodes.php?' .
                           "root_node={$argsObj->tproject_id}&" .
                           "tcprefix=" . urlencode($tcasePrefix. $tcaseCfg->glue_character) . "&" .
                           "filter_node={$argsObj->tsuites_to_show}";

    $gui->ajaxTree->root_node=new stdClass();
    $gui->ajaxTree->root_node->href="javascript:EP({$argsObj->tproject_id})";
    $gui->ajaxTree->root_node->id=$argsObj->tproject_id;
    
  	$tcase_qty = $tprojectMgr->count_testcases($argsObj->tproject_id);
    $gui->ajaxTree->root_node->name = $argsObj->tproject_name . " ($tcase_qty)";
  
    $gui->ajaxTree->dragDrop = new stdClass();
    $gui->ajaxTree->dragDrop->enabled = $treeDragDropEnabled;
    $gui->ajaxTree->dragDrop->BackEndUrl = $basehref . 'lib/ajax/dragdroptprojectnodes.php';
    
    // TRUE -> beforemovenode() event will use our custom implementation 
    $gui->ajaxTree->dragDrop->useBeforeMoveNode=FALSE;
  
  
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

    
    $gui->tsuite_choice=$argsObj->tsuites_to_show;  
    
    // 20090118 - franciscom    
    $gui->keywordsFilterType = new stdClass();
    $gui->keywordsFilterType->options = array('OR' => 'Or' , 'AND' =>'And'); 
    $gui->keywordsFilterType->selected=$argsObj->keywordsFilterType;
    $gui->keywordsFilterItemQty = 0;
    $gui->keyword_id = $argsObj->keyword_id; 
    $gui->keywords_map = $tprojectMgr->get_keywords_map($argsObj->tproject_id); 
    if(!is_null($gui->keywords_map))
    {
        $gui->keywordsFilterItemQty = min(count($gui->keywords_map),3);
        $gui->keywords_map = array( 0 => $gui->str_option_any) + $gui->keywords_map;
    }

    return $gui;  
}
?>