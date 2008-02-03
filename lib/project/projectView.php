<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: projectView.php,v $
 *
 * @version $Revision: 1.5 $
 * @modified $Date: 2008/02/03 21:39:01 $ $Author: schlundus $
 *
 * Display list of test projects
 *
*/
require('../../config.inc.php');
require_once("common.php");
testlinkInitPage($db);

$gui_cfg = config_get('gui');
$template_dir = 'project/';
$default_template = str_replace('.php','.tpl',basename($_SERVER['SCRIPT_NAME']));
$tproject_mgr = new testproject($db);

$args = init_args();
$smarty = new TLSmarty();
$smarty->assign('canManage', has_rights($db,"mgt_modify_product"));

$tprojects = $tproject_mgr->get_accessible_for_user($args->userID,'array_of_map', 
                                                    " ORDER BY nodes_hierarchy.name ");
if(count($tprojects) == 0)
{
    $default_template = "projectEdit.tpl"; 
    $smarty->assign('doAction',"create");
}
else
{
    $smarty->assign('tprojects',$tprojects);
}
$smarty->assign('api_ui_show', $g_api_ui_show);
$smarty->assign('doAction' . $args->doAction);
$smarty->display($template_dir . $default_template);

function init_args()
{
    $_REQUEST = strings_stripSlashes($_REQUEST);
    $args->tproject_id = isset($_SESSION['testprojectID']) ? intval($_SESSION['testprojectID']) : 0 ;
    $args->doAction = isset($_REQUEST['doAction']) ? $_REQUEST['doAction'] : 'list' ;
    $args->userID =isset($_SESSION['userID']) ? $_SESSION['userID'] : 0;
    
    return $args;  
}
?>