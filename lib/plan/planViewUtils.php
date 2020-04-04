<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource  planView.php
 *
 */

function planViewGUIInit(&$dbH,$argsObj,&$guiObj,&$tplanMgr) 
{
  $tproject_id = $argsObj->tproject_id;
  if ($guiObj->getTestPlans) {
    $guiObj->tplans = 
      $argsObj->user->getAccessibleTestPlans($dbH,$tproject_id,
                        null, array('output' =>'mapfull', 
                                    'active' => null)); 
  }

  $guiObj->createEnabled = $guiObj->grants->testplan_create == 'yes' 
                           && $tproject_id > 0;

  if( !is_null($guiObj->tplans) && count($guiObj->tplans) > 0 ) {
    $tplanMgr->platform_mgr->setTestProjectID($tproject_id);
    $dummy = $tplanMgr->platform_mgr->testProjectCount();
    $guiObj->drawPlatformQtyColumn = 
      $dummy[$tproject_id]['platform_qty'] > 0;

    $tplanSet = array_keys($guiObj->tplans);
    $dummy = $tplanMgr->count_testcases($tplanSet,null,
                array('output' => 'groupByTestPlan'));
    $buildQty = $tplanMgr->get_builds($tplanSet,null,null,
                   array('getCount' => true));
    $rightSet = array('testplan_user_role_assignment');

    foreach($tplanSet as $idk) {
      $guiObj->tplans[$idk]['tcase_qty'] = 
        isset($dummy[$idk]['qty']) ? intval($dummy[$idk]['qty']) : 0;
      $guiObj->tplans[$idk]['build_qty'] = isset($buildQty[$idk]['build_qty']) ? intval($buildQty[$idk]['build_qty']) : 0;

      if( $guiObj->drawPlatformQtyColumn ) {
        $plat = $tplanMgr->getPlatforms($idk);
        $guiObj->tplans[$idk]['platform_qty'] = is_null($plat) ? 0 : count($plat);
      }

      // Get rights for each test plan
      foreach($rightSet as $target) {
        // DEV NOTE - CRITIC
        // I've made a theorically good performance choice to 
        // assign to $roleObj a reference to different roleObj
        // UNFORTUNATELLY this choice was responsible to destroy 
        // the pointed object since second LOOP
        $roleObj = null;
        if($guiObj->tplans[$idk]['has_role'] > 0) {
          $roleObj = $argsObj->user->tplanRoles[$guiObj->tplans[$idk]['has_role']];
        }  
        else if (!is_null($argsObj->user->tprojectRoles) && 
                 isset($argsObj->user->tprojectRoles[$tproject_id]) )
        {
          $roleObj = $argsObj->user->tprojectRoles[$tproject_id];
        }  

        if(is_null($roleObj)) {
          $roleObj = $argsObj->user->globalRole;
        }  
        $guiObj->tplans[$idk]['rights'][$target] = 
          $roleObj->hasRight($target);  
      }  
    }    
  }
}  