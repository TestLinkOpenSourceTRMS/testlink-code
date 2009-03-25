<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 * 
 * @version $Id: planAddTCNavigator.php,v 1.44 2009/03/25 20:53:18 schlundus Exp $
 * @author Martin Havlat
 * 
 * 	Navigator for feature: add Test Cases to a Test Case Suite in Test Plan. 
 *	It builds the javascript tree that allow the user select a required part 
 *	Test specification. Keywords should be used for filter.
 * 
 * rev :
 *      20090118 - franciscom - added logic to switch for EXTJS tree type
 *                              how to build tree when there are filters
 *
 * 
 * ----------------------------------------------------------------------------------- */
require('../../config.inc.php');
require_once("common.php");
require_once("treeMenu.inc.php");
testlinkInitPage($db);

$templateCfg = templateConfiguration();

$args = init_args();
$gui = initializeGui($db,$args,$_SESSION['basehref']);
$gui->tree = buildTree($db,$gui,$args);

$smarty = new TLSmarty();
$smarty->assign('gui', $gui);

// IMPORTANT: A javascript variable 'args' will be initialized with this value
// using inc_head.tpl template.
$smarty->assign('args', $gui->args);
$smarty->assign('menuUrl', $gui->menuUrl);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/*
  function: get input data 

  args: -

  returns: object expected parameters

*/
function init_args()
{
    $args = new stdClass();
    $_REQUEST = strings_stripSlashes($_REQUEST);

    // Is an array because is a multiselect 
    $args->keyword_id = isset($_REQUEST['keyword_id']) ? $_REQUEST['keyword_id'] : 0;
    
    $args->tplan_id = isset($_REQUEST['tplan_id']) ? $_REQUEST['tplan_id'] : $_SESSION['testPlanId'];
    $args->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
    $args->tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : '';
    $args->user_id = $_SESSION['userID'];
    
    $args->doUpdateTree = isset($_REQUEST['doUpdateTree']) ? 1 : 0;

    $args->called_by_me = isset($_REQUEST['called_by_me']) ? 1 : 0;
    $args->called_url = isset($_REQUEST['called_url']) ? $_REQUEST['called_url'] : null;
 
    $args->keywordsFilterType =isset($_REQUEST['keywordsFilterType']) ? $_REQUEST['keywordsFilterType'] : 'OR';

    return $args;
}


/*
  function: initializeGui
  args :
  returns: 

  rev:20080629 - franciscom - added missed argument basehref
      20080622 - franciscom - changes for ext js tree
      20080429 - franciscom
*/
function initializeGui(&$dbHandler,&$argsObj,$basehref)
{
    $gui = new stdClass();
    $tprojectMgr = new testproject($dbHandler);
    $tcaseCfg = config_get('testcase_cfg'); 


    $gui->do_reload = 0;
    $gui->src_workframe = null;
    
    $gui->keywordsFilterItemQty = 0;
    $gui->keyword_id = $argsObj->keyword_id; 
    $gui->keywords_map = $tprojectMgr->get_keywords_map($argsObj->tproject_id); 
    if(!is_null($gui->keywords_map))
    {
        $gui->keywordsFilterItemQty = min(count($gui->keywords_map),3);
    }

    // filter using user roles
    $tplans = getAccessibleTestPlans($dbHandler,$argsObj->tproject_id,$argsObj->user_id);
    $gui->map_tplans = array();
    foreach($tplans as $key => $value)
    {
    	$gui->map_tplans[$value['id']] = $value['name'];
    }

    $gui->tplan_id = $argsObj->tplan_id;

    $gui->menuUrl = 'lib/plan/planAddTC.php';
    $gui->args = '&tplan_id=' . $gui->tplan_id;
    if(is_array($argsObj->keyword_id))
    {
		    $kl = implode(',',$argsObj->keyword_id);
		    $gui->args .= '&keyword_id=' . $kl;
    }
    else if($argsObj->keyword_id > 0)
    {
		    $gui->args .= '&keyword_id='.$argsObj->keyword_id;
    }
    $gui->args .= '&keywordsFilterType=' . $argsObj->keywordsFilterType;

    $gui->keywordsFilterType = new stdClass();
    $gui->keywordsFilterType->options = array('OR' => 'Or' , 'AND' =>'And'); 
    $gui->keywordsFilterType->selected=$argsObj->keywordsFilterType;
    
    
    // 20080622 - francisco.mancardi@gruppotesi.com
    // $tcasePrefix=$tprojectMgr->getTestCasePrefix($argsObj->tproject_id);
    
    $gui->ajaxTree=new stdClass();
    $gui->ajaxTree->loader=$basehref . 'lib/ajax/gettprojectnodes.php?' .
                           "root_node={$argsObj->tproject_id}&" .
                           "show_tcases=0";
                            
                           // 20080629 - franciscom
                           // "filter_node={$argsObj->tsuites_to_show}";

    $gui->ajaxTree->root_node=new stdClass();
    $gui->ajaxTree->root_node->href="javascript:EP({$argsObj->tproject_id})";
    $gui->ajaxTree->root_node->id=$argsObj->tproject_id;
    $gui->ajaxTree->root_node->name=$argsObj->tproject_name;
  
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

  
    // Prefix for cookie used to save tree state
    $gui->ajaxTree->cookiePrefix="planaddtc_{$gui->ajaxTree->root_node->id}_{$argsObj->user_id}_";

    // not allowed in this feature
    $gui->ajaxTree->dragDrop=new stdClass();
    $gui->ajaxTree->dragDrop->enabled=false;
    $gui->ajaxTree->dragDrop->BackEndUrl='';
    // TRUE -> beforemovenode() event will use our custom implementation 
    $gui->ajaxTree->dragDrop->useBeforeMoveNode=FALSE;
    
    
    return $gui;
}


/*
  function: 

  args :
  
  returns: 

*/
function buildTree(&$dbHandler,&$guiObj,&$argsObj)
{
    $keywordsFilter = null;
    $my_workframe = $_SESSION['basehref']. $guiObj->menuUrl .                      
                    "?edit=testproject&id={$argsObj->tproject_id}" . $guiObj->args;
    if($argsObj->doUpdateTree)
    {
	     $guiObj->src_workframe = $my_workframe; 
	  }
    else if($argsObj->called_by_me)
    {
       // -------------------------------------------------------------------------------
       // 20090308 - franciscom - think this is result of cut/paste from other
       //                         piece of TL (look at edit=testsuite that has no use on 
       //                         test case testplan assignment.
       //
       // Explain what is objective of this chunck of code 
       // Warning:
       // Algorithm based on field order on URL call
       // 
       $dummy = explode('?',$argsObj->called_url);
       
       $qs = explode('&',$dummy[1]);
       if($qs[0] == 'edit=testsuite')
       {
 			    $guiObj->src_workframe = $dummy[0] . "?" . $qs[0] . "&" . $guiObj->args;
       }
       else 
       {
 			    $guiObj->src_workframe = $my_workframe; 
 			 }   
 			 // -------------------------------------------------------------------------------
    }
  
    $keywordsFilter = buildKeywordsFilter($argsObj->keyword_id,$guiObj);
    $applyFilter = !is_null($keywordsFilter);

    $treeMenu = null;
    
    if($applyFilter)
    {
        $treeMenu = generateTestSpecTree($dbHandler,$argsObj->tproject_id, $argsObj->tproject_name,  
                                         $guiObj->menuUrl,NOT_FOR_PRINTING,
                                         HIDE_TESTCASES,ACTION_TESTCASE_DISABLE,
                                         $guiObj->args, $keywordsFilter,IGNORE_INACTIVE_TESTCASES);
        
		$guiObj->ajaxTree = new stdClass();
        $guiObj->ajaxTree->loader = '';
        $guiObj->ajaxTree->root_node = $treeMenu->rootnode;
        $guiObj->ajaxTree->children = $treeMenu->menustring ? $treeMenu->menustring : "''";
    }
    
    if($applyFilter)
        $guiObj->ajaxTree->loader = '';  

    return $treeMenu;
}
?>