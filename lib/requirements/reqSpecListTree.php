<?php
/** 
* 	TestLink Open Source Project - http://testlink.sourceforge.net/
* 
* 	@version 	$Id: reqSpecListTree.php,v 1.10 2009/04/02 20:16:17 schlundus Exp $
* 	@author 	Francisco Mancardi (francisco.mancardi@gmail.com)
* 
* 	Tree menu with requirement specifications.
*
*   rev: 20080824 - franciscom - added code to manage EXTJS tree component
*/
require_once('../../config.inc.php');
require_once("common.php");
require_once("treeMenu.inc.php");
require_once('requirements.inc.php');
testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();
$args = init_args();
$gui = initializeGui($args,$_SESSION['basehref']);

$smarty = new TLSmarty();
$smarty->assign('gui', $gui);
$smarty->assign('tree', null);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/*
  function: init_args

  args:
  
  returns: 

*/
function init_args()
{
    $args = new stdClass();
    $args->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
    $args->tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : 'undefned';
    return $args;
}

/*
  function: initializeGui
            initialize gui (stdClass) object that will be used as argument
            in call to Template Engine.
 
  args: argsObj: object containing User Input and some session values
        basehref: URL to web home of your testlink installation.
  
  returns: stdClass object
  
  rev: 

*/
function initializeGui($argsObj,$basehref)
{
    $gui = new stdClass();
    $gui->tree_title=lang_get('title_navigator'). ' - ' . lang_get('title_req_spec');
  
    $gui->req_spec_manager_url = "lib/requirements/reqSpecView.php";
    $gui->req_manager_url = "lib/requirements/reqView.php";
    
    $gui->ajaxTree=new stdClass();
    $gui->ajaxTree->loader=$basehref . 'lib/ajax/getrequirementnodes.php?' .
                           "root_node={$argsObj->tproject_id}";

    $gui->ajaxTree->root_node=new stdClass();
    $gui->ajaxTree->root_node->href="javascript:TPROJECT_REQ_SPEC_MGMT({$argsObj->tproject_id})";
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
    
   
    $gui->ajaxTree->dragDrop=new stdClass();
    $gui->ajaxTree->dragDrop->enabled=TRUE;
    $gui->ajaxTree->dragDrop->BackEndUrl=$basehref . 'lib/ajax/dragdroprequirementnodes.php';
    
    // TRUE -> beforemovenode() event will use our custom implementation 
    $gui->ajaxTree->dragDrop->useBeforeMoveNode=TRUE;
  
    $gui->ajaxTree->cookiePrefix='requirement_spec' . $gui->ajaxTree->root_node->id . "_" ;
    return $gui;  
}
function checkRights(&$db,&$user)
{
	return $user->hasRight($db,'mgt_view_req');
}
?>