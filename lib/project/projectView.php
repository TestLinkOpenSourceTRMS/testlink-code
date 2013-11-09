<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Display list of test projects
 *
 * @package 	  TestLink
 * @author 		  TestLink community
 * @copyright   2007-2013, TestLink community 
 * @filesource  projectView.php
 * @link 		    http://www.teamst.org/index.php
 *
 * @internal revisions
 * @since 1.9.9
 */


require_once('../../config.inc.php');
require_once("common.php");
testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();

$smarty = new TLSmarty();
$imgSet = $smarty->getImages();
$args = init_args();
$gui = initializeGui($db,$args);


$template2launch = $templateCfg->default_template;
if(!is_null($gui->tprojects) || $args->doAction=='list')
{  
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
    
    $gui->tprojects[$idx]['rmsstatusImg'] = '';
    if($gui->tprojects[$idx]['rmsname'] != '')
    {
      $ak = ($gui->tprojects[$idx]['reqmgr_integration_enabled']) ? 'active' : 'inactive';
      $gui->tprojects[$idx]['rmsstatusImg'] = ' <img title="' . $labels[$ak . '_integration'] . '" ' .
                                              ' alt="' . $labels[$ak . '_integration'] . '" ' .
                                              ' src="' . $imgSet[$ak] . '"/>';
    } 
  }

  if(count($gui->tprojects) == 0)
  {
      $template2launch = "projectEdit.tpl"; 
      $gui->doAction = "create";
  }
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
  $args->userID = isset($_SESSION['userID']) ? $_SESSION['userID'] : 0;
  $args->user = isset($_SESSION['currentUser']) ? $_SESSION['currentUser'] : null; 
  $args->name = isset($_REQUEST['name']) ? trim($_REQUEST['name']) : null ;

  if(!is_null($args->name))
  {
    if(strlen(trim($args->name)) == 0)
    {  
      $args->name = null;
    }      
  } 
  return $args;  
}

/**
 * 
 *
 */
function initializeGui(&$dbHandler,&$argsObj)
{
  $guiObj = new stdClass();
  $guiObj->doAction = $argsObj->doAction;
  $guiObj->canManage = $argsObj->user->hasRight($dbHandler,"mgt_modify_product");
  $guiObj->name = is_null($argsObj->name) ? '' : $argsObj->name;
  $guiObj->feedback = '';

  switch($argsObj->doAction)
  {
    case 'search':
      $filters = array('name' => array('op' => 'like', 'value' => $argsObj->name));
      $guiObj->feedback = lang_get('no_records_found');
    break;

    case 'list':
    default:
      $filters = null;
    break;
  }

  $tproject_mgr = new testproject($dbHandler);
  $opt = array('output' => 'array_of_map', 'order_by' => " ORDER BY name ", 'add_issuetracker' => true,
               'add_reqmgrsystem' => true);
  $guiObj->tprojects = $tproject_mgr->get_accessible_for_user($argsObj->userID,$opt,$filters);
  return $guiObj;
}


function checkRights(&$db,&$user)
{
	return $user->hasRight($db,'mgt_modify_product');
}
