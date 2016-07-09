<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource	planView.php
 * @internal revisions
 * @since 1.9.12
 *
 */
require_once('../../config.inc.php');
require_once("common.php");
testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();

$args=init_args();
$gui = initializeGui($db,$args);

if($args->tproject_id)
{
  $tproject_mgr = new testproject($db);
  $gui->tplans = $args->user->getAccessibleTestPlans($db,$args->tproject_id,null,
                                                     array('output' =>'mapfull', 'active' => null));
  $gui->drawPlatformQtyColumn = false;
  
  if( !is_null($gui->tplans) && count($gui->tplans) > 0 )
  {
    // do this test project has platform definitions ?
    $tplan_mgr = new testplan($db);
    $tplan_mgr->platform_mgr->setTestProjectID($args->tproject_id);
    $dummy = $tplan_mgr->platform_mgr->testProjectCount();
    $gui->drawPlatformQtyColumn = $dummy[$args->tproject_id]['platform_qty'] > 0;

    $tplanSet = array_keys($gui->tplans);
    $dummy = $tplan_mgr->count_testcases($tplanSet,null,array('output' => 'groupByTestPlan'));
    $buildQty = $tplan_mgr->get_builds($tplanSet,null,null,array('getCount' => true));
    $rightSet = array('testplan_user_role_assignment');

    foreach($tplanSet as $idk)
    {
      $gui->tplans[$idk]['tcase_qty'] = isset($dummy[$idk]['qty']) ? intval($dummy[$idk]['qty']) : 0;
      $gui->tplans[$idk]['build_qty'] = isset($buildQty[$idk]['build_qty']) ? intval($buildQty[$idk]['build_qty']) : 0;
      if( $gui->drawPlatformQtyColumn )
      {
        $plat = $tplan_mgr->getPlatforms($idk);
        $gui->tplans[$idk]['platform_qty'] = is_null($plat) ? 0 : count($plat);
      }


      // Get rights for each test plan
      foreach($rightSet as $target)
      {
        // DEV NOTE - CRITIC
        // I've made a theorically good performance choice to 
        // assign to $roleObj a reference to different roleObj
        // UNFORTUNATELLY this choice was responsible to destroy point object
        // since second LOOP
        $roleObj = null;
        if($gui->tplans[$idk]['has_role'] > 0)
        {
          $roleObj = $args->user->tplanRoles[$gui->tplans[$idk]['has_role']];
        }  
        else if (!is_null($args->user->tprojectRoles) && 
                 isset($args->user->tprojectRoles[$args->tproject_id]) )
        {
          $roleObj = $args->user->tprojectRoles[$args->tproject_id];
        }  

        if(is_null($roleObj))
        {
          $roleObj = $args->user->globalRole;
        }  
        $gui->tplans[$idk]['rights'][$target] = $roleObj->hasRight($target);  
      }  
    }    
    unset($tplan_mgr);  
  }
  unset($tproject_mgr);  
}

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/**
 * init_args
 *
 */
function init_args()
{
    $args = new stdClass();
    $args->tproject_id = isset($_SESSION['testprojectID']) ? intval($_SESSION['testprojectID']) : 0 ;
    $args->tproject_name = isset($_SESSION['testprojectName']) ? trim($_SESSION['testprojectName']) : '' ;

    $args->user = $_SESSION['currentUser'];
    return $args;
}

function initializeGui(&$dbHandler,$argsObj)
{
  $gui = new stdClass();
  $gui->tproject_id = $argsObj->tproject_id;
  $gui->tplans = null;
  $gui->user_feedback = '';
  $gui->grants = new stdClass();
  $gui->grants->testplan_create = $argsObj->user->hasRight($dbHandler,"mgt_testplan_create",$argsObj->tproject_id);
  $gui->main_descr = lang_get('testplan_title_tp_management'). " - " . 
                     lang_get('testproject') . ' ' . $argsObj->tproject_name;
  $cfg = getWebEditorCfg('testplan');
  $gui->editorType = $cfg['type'];
  
  return $gui;
}


/**
 * checkRights
 *
 */
function checkRights(&$db,&$user)
{
  return $user->hasRight($db,'mgt_testplan_create');
}