<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Display list of test projects
 *
 * @package 	TestLink
 * @author 		TestLink community
 * @copyright 	2007-2009, TestLink community 
 * @version    	CVS: $Id: projectView.php,v 1.13 2010/02/16 21:46:32 havlat Exp $
 * @link 		http://www.teamst.org/index.php
 *
 */


require_once('../../config.inc.php');
require_once("common.php");
testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();
$args = init_args();

$gui = new stdClass();
$gui->doAction = $args->doAction;
$gui->canManage = has_rights($db,"mgt_modify_product");

$tproject_mgr = new testproject($db);
$gui->tprojects = $tproject_mgr->get_accessible_for_user($args->userID,'array_of_map', 
                                                         " ORDER BY nodes_hierarchy.name ");

$template2launch = $templateCfg->default_template;
if(count($gui->tprojects) == 0)
{
    $template2launch = "projectEdit.tpl"; 
    $gui->doAction = "create";
}
$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $template2launch);


/**
 * 
 *
 */
function init_args()
{
   $_REQUEST = strings_stripSlashes($_REQUEST);
   
   $args = new stdClass();
   $args->tproject_id = isset($_SESSION['testprojectID']) ? intval($_SESSION['testprojectID']) : 0 ;
   $args->doAction = isset($_REQUEST['doAction']) ? $_REQUEST['doAction'] : 'list' ;
   $args->userID =isset($_SESSION['userID']) ? $_SESSION['userID'] : 0;
    
   return $args;  
}

function checkRights(&$db,&$user)
{
	return $user->hasRight($db,'mgt_modify_product');
}
?>