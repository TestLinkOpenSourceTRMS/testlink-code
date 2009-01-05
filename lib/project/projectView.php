<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: projectView.php,v $
 *
 * @version $Revision: 1.11 $
 * @modified $Date: 2009/01/05 21:38:57 $ $Author: schlundus $
 *
 * Display list of test projects
 *
*/
require_once('../../config.inc.php');
require_once("common.php");
testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();
$args = init_args();

$smarty = new TLSmarty();
$smarty->assign('canManage', has_rights($db,"mgt_modify_product"));

$tproject_mgr = new testproject($db);
$tprojects = $tproject_mgr->get_accessible_for_user($args->userID,'array_of_map', 
                                                    " ORDER BY nodes_hierarchy.name ");
if(count($tprojects) == 0)
{
    $default_template = "projectEdit.tpl"; 
    $smarty->assign('doAction',"create");
}
else
    $smarty->assign('tprojects',$tprojects);

$smarty->assign('doAction', $args->doAction);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


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