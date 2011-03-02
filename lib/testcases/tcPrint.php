<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename: tcPrint.php
 *
 * Scope: test case print
 * 
 * @internal revisions:
 *
 * 20100315 - franciscom - BUGID 4286
 * ----------------------------------------------------------------------------------- */

require_once("../../config.inc.php");
require_once("common.php");
testlinkInitPage($db);
$templateCfg = templateConfiguration();

$tree_mgr = new tree($db);
$args = init_args();

$gui = new stdClass();

$gui->object_name='';
$gui->goback_url = !is_null($args->goback_url) ? $args->goback_url : ''; 

$exporting_just_one_tc = 0;
$node_id = $args->container_id;
$check_children = 0;

$node = $tree_mgr->get_node_hierarchy_info($node_id);

$gui->object_name=$node['name'];
$gui->page_title = sprintf(lang_get('print_testcase'),$node['name']);

$gui->tproject_name=$args->tproject_name;
$gui->tproject_id=$args->tproject_id;
$gui->tcID=$args->tcase_id; 
$gui->useRecursion=$args->useRecursion ? 1 : 0;
$gui->tcVersionID=$args->tcversion_id;
$gui->containerID=$args->container_id;

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/*
  function: init_args

  args:
  
  returns: 

*/
function init_args()
{
    $_REQUEST = strings_stripSlashes($_REQUEST);
    
    $args = new stdClass();
    $args->tcase_id = isset($_REQUEST['testcase_id']) ? intval($_REQUEST['testcase_id']) : 0;
    $args->tcversion_id = isset($_REQUEST['tcversion_id']) ? intval($_REQUEST['tcversion_id']) : 0;
    $args->container_id = isset($_REQUEST['containerID']) ? intval($_REQUEST['containerID']) : 0;
    $args->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
    $args->tproject_name = $_SESSION['testprojectName'];
    $args->goback_url=isset($_REQUEST['goback_url']) ? $_REQUEST['goback_url'] : null;
    return $args;
}
?>
