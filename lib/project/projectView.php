<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Display list of test projects
 *
 * @package 	  TestLink
 * @author 		  TestLink community
 * @copyright   2007-2012, TestLink community 
 * @filesource  projectView.php
 * @link 		    http://www.teamst.org/index.php
 *
 * @internal revisions
 * @since 1.9.5
 */


require_once('../../config.inc.php');
require_once("common.php");
testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();

$smarty = new TLSmarty();
$imgSet = $smarty->getImages();
$args = init_args();

$gui = new stdClass();
$gui->doAction = $args->doAction;
$gui->canManage = has_rights($db,"mgt_modify_product");

$tproject_mgr = new testproject($db);
$gui->tprojects = $tproject_mgr->get_accessible_for_user($args->userID,'array_of_map', " ORDER BY name ",true);

$loop2do = count($gui->tprojects);
$labels = init_labels(array('active_integration' => null, 'inactive_integration' => null));
for($idx=0; $idx < $loop2do; $idx++)
{
  $gui->tprojects[$idx]['itstatusImg'] = '';
  if($gui->tprojects[$idx]['itname'] != '')
  {
    $ak = ($gui->tprojects[$idx]['issue_tracker_enabled']) ? 'active' : 'inactive';
    $gui->tprojects[$idx]['itstatusImg'] = ' <img title="' . $labels[$ak . '_integration'] . '" ' .
                                           ' alt="' . $labels[$ak . '_integration'] . '" ' .
 				                                   ' src="' . $imgSet[$ak] . '"/>';
  } 
}

$template2launch = $templateCfg->default_template;
if(count($gui->tprojects) == 0)
{
    $template2launch = "projectEdit.tpl"; 
    $gui->doAction = "create";
}
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