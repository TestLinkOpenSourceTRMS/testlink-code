<?php
/** 
* 	TestLink Open Source Project - http://testlink.sourceforge.net/
* 
* 	@version 	$Id: reqSpecListTree.php,v 1.3 2008/02/06 19:35:21 schlundus Exp $
* 	@author 	Francisco Mancardi (francisco.mancardi@gmail.com)
* 
* 	Tree menu with requirement specifications.
*/
require_once('../../config.inc.php');
require_once("common.php");
require_once("treeMenu.inc.php");
require_once("req_tree_menu.php");
require_once('requirements.inc.php');
require_once('requirement_spec_mgr.class.php');
testlinkInitPage($db);

$req_cfg = config_get('req_cfg');
$template_dir = "requirements/";
$default_template = str_replace('.php','.tpl',basename($_SERVER['SCRIPT_NAME']));

$tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
$tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : 'undefned';
$title = lang_get('title_navigator'). ' - ' . lang_get('title_req_spec');

$treeString = gen_req_tree_menu($db,$tproject_id, $tproject_name);
$tree = null;
if (strlen($treeString))
	$tree = invokeMenu($treeString,"",null);

$req_spec_manager_url = $req_cfg->module . "reqSpecView.php";
$req_manager_url = $req_cfg->module . "reqView.php";
		
$smarty = new TLSmarty();
$smarty->assign('treeKind', TL_TREE_KIND);
$smarty->assign('tree', $tree);
$smarty->assign('treeHeader', $title);
$smarty->assign('req_spec_manager_url',$req_spec_manager_url);
$smarty->assign('req_manager_url',$req_manager_url);
$smarty->display($template_dir . $default_template);
?>
