<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Allows assigning users roles to testplans or testprojects
 *
 * Development note:
 * Assign Test plans user roles do not allow Test Project selection,
 * then to change Test Project user need to use main Test Project Combo
 * 
 * @package 	  TestLink
 * @copyright   2005-2016, TestLink community
 * @filesource  usersAssign.php
 * @link 		    http://www.testlink.org
 *
 * @internal revisions
 * @since 1.9.15
 *
 */


require_once('../../config.inc.php');
require_once('users.inc.php');
testlinkInitPage($db,false,false,"checkRights");

$smarty = new TLSmarty();
$imgSet = $smarty->getImages();

$templateCfg = templateConfiguration();

$assignRolesFor = null;
$featureMgr = null;
$userFeatureRoles = null;
$doInitGui = true;

$tprojectMgr = new testproject($db);
$tplanMgr = new testplan($db);

$args = init_args();
$gui = initializeGui($db,$args);

$lbl = initLabels();

$target = new stdClass();
$target->testprojectID = null;
$target->testplanID = null;


switch($args->featureType)
{
    case "testproject":
    	$gui->highlight->assign_users_tproject = 1;
    	$gui->roles_updated = $lbl["test_project_user_roles_updated"];
    	$gui->not_for_you = $lbl["testproject_roles_assign_disabled"];
    	$gui->main_title = $lbl["assign_tproject_roles"];

      $assignRolesFor = $args->featureType;
    	$target->testprojectID = $args->featureID > 0 ? $args->featureID : null;
    	$featureMgr = &$tprojectMgr;
    break;
    
    case "testplan":
      $gui->highlight->assign_users_tplan = 1;
    	$gui->roles_updated = lang_get("test_plan_user_roles_updated");
    	$gui->not_for_you = lang_get("testplan_roles_assign_disabled");
      $gui->main_title = $lbl["assign_tplan_roles"];

    	$assignRolesFor = $args->featureType;
    	$target->testprojectID = $args->testprojectID;
    	$featureMgr = &$tplanMgr;

      $accessKey = 'private';
      if( $tprojectMgr->getPublicAttr($args->testprojectID) )
      {
        $accessKey = 'public';
      }  
      $gui->tprojectAccessTypeImg = '<img src="' . $imgSet[$accessKey] . 
                                    '" title="' . lang_get('access_' . $accessKey) . '" >';
    break;
}

if ($args->featureID && $args->doUpdate && $featureMgr)
{
    if(checkRightsForUpdate($db,$args->user,$args->testprojectID,$args->featureType,$args->featureID))
    {
      doUpdate($db,$args,$featureMgr);
      if( $gui->user_feedback == '' )
      {
        $gui->user_feedback = $gui->roles_updated;
    	}
    }
}
// --------------------------------------------------------------------------
// Important: 
// Must be done here after having done update, to get current information
$gui->users = tlUser::getAll($db,"WHERE active=1",null,null,tlUser::TLOBJ_O_GET_DETAIL_MINIMUM);
checkSessionValid($db);
$args->user = $_SESSION['currentUser'];
// --------------------------------------------------------------------------

switch($assignRolesFor)
{
  case 'testproject':
    $info = getTestProjectEffectiveRoles($db,$tprojectMgr,$args,$gui->users);
    list($gui->userFeatureRoles,$gui->features,$gui->featureID) = $info;
    $target->testprojectID = $gui->featureID;
  break;
        
  case 'testplan':
    $info = getTestPlanEffectiveRoles($db,$tplanMgr,$tprojectMgr,$args,$gui->users);
		if( is_null($info) )
		{
			$gui->user_feedback = lang_get('no_test_plans_available');
		}
    list($gui->userFeatureRoles,$gui->features,$gui->featureID)=$info;
  break;

}


$gui->grants = getGrantsForUserMgmt($db,$args->user,$target->testprojectID,-1);
$gui->accessTypeImg = '';


if(is_null($gui->features) || count($gui->features) == 0)
{
  $gui->features = null;
  if( $gui->user_feedback == '' )
	{
		$gui->user_feedback = $gui->not_for_you;
	}
}
else
{
  $accessKey = 'vorsicht';
  if( isset($gui->features[$gui->featureID]) )
  {
    $accessKey = $gui->features[$gui->featureID]['is_public'] ? 'public' : 'private';
    $gui->accessTypeImg = '<img src="' . $imgSet[$accessKey] . '" title="' . lang_get('access_' . $accessKey) . '" >';
  }  
  $gui->accessTypeImg = '<img src="' . $imgSet[$accessKey] . '" title="' . lang_get('access_' . $accessKey) . '" >';
}

$gui->hintImg = '<img src="' . $imgSet['heads_up'] . '" title="' . 
                lang_get('system_design_blocks_global_admin_change') . '" >';

$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/**
 * 
 *
 */
function init_args()
{
	$iParams = array(
			"featureType" => array(tlInputParameter::STRING_N,0,100),
			"featureID" => array(tlInputParameter::INT_N),
			"userRole" => array(tlInputParameter::ARRAY_INT),
			"do_update" => array(tlInputParameter::STRING_N,0,100),
	);

	$pParams = R_PARAMS($iParams);
    
	$args = new stdClass();
	$args->featureType = $pParams["featureType"];
  $args->featureID = $pParams["featureID"];
  $args->map_userid_roleid = $pParams["userRole"];
  $args->doUpdate = ($pParams["do_update"] != "") ? 1 : 0;
   
  // Warning: 
  // This value is used when doing Test Plan role assignment
  $args->testprojectID = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
  
  $args->testprojectName = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : null;
  
  $args->testplanID = isset($_SESSION['testplanID']) ? $_SESSION['testplanID'] : 0;

  $args->user = $_SESSION['currentUser'];
  $args->userID = $args->user->dbID;
    
  return $args;
}


/**
 * checks runned when tyring to run this page, to be sure user has rights to use it.
 *
 */
function checkRights(&$db,&$user)
{
	$result = false;
    $args = init_args();
    $answers = new stdClass();
    $answers->role_management = $user->hasRight($db,"role_management");
    
    // Two checks needed:
    // First on current test project without using test plan rights
    // if this fails then check again adding current test plan
    $answers->testplan_user_role_assignment = $user->hasRight($db,"testplan_user_role_assignment",$args->testprojectID,-1);
    if($answers->testplan_user_role_assignment != "yes")
    {
        $targetTestPlanID = null;
        if($args->featureType == 'testplan')
        {
            $targetTestPlanID = $args->featureID;
        }
        $answers->testplan_user_role_assignment = $user->hasRight($db,"testplan_user_role_assignment",null,$targetTestPlanID);
    }
    
    $answers->user_role_assignment = $user->hasRight($db,"user_role_assignment",null,-1);
    
    foreach($answers as $key => $value)
    {
        $answers->$key = $value == "yes" ? true : false;
        $result = $result | $answers->$key; 
    }
	
	if(!$result && ($args->featureType == 'testproject'))
	{
	    $feature2check = $args->featureID;
	    if($args->featureID == 0 || is_null($args->featureID))
	    {
	        $feature2check = $args->testprojectID; 
	    } 
        if($user->hasRight($db,"testproject_user_role_assignment",$feature2check,-1) == "yes") 	    
        {
            $result = true;
        }
	}

	return $result;
}

/**
 * checkRightsForUpdate
 *
 */
function checkRightsForUpdate(&$dbHandler,&$user,$testprojectID,$featureType,$featureID)
{
    $yes_no = "no";
    switch($featureType)
    {
        case 'testproject':
            if($user->hasRight($dbHandler,"user_role_assignment",$featureID) == "yes" ||
                $user->hasRight($dbHandler,"testproject_user_role_assignment",$featureID,-1,true) == "yes")
            {         
                $yes_no = "yes";
            }
        break;
            
        case 'testplan':
            $yes_no = $user->hasRight($dbHandler,"testplan_user_role_assignment",
                                       $testprojectID,$featureID);
        break;
    }

    return ($yes_no == 'yes');
}


/**
 * getTestProjectEffectiveRoles
 *
 */
function getTestProjectEffectiveRoles($dbHandler,&$objMgr,&$argsObj,$users)
{
   $features = null;
	$gui_cfg = config_get('gui');

  // Accessible means user has a role on test project ?
  $opt = array('output' => 'map_of_map_full', 'order_by' => $gui_cfg->tprojects_combo_order_by);
	$testprojects = $objMgr->get_accessible_for_user($argsObj->userID,$opt);
 	

   // We need to populate the combo box with test project where current logged user ($argsObj->userID)
  // has right enough to assign user role.
  //
  $features = array();
  $idSet = $key2loop = array_keys($testprojects);
  // $idSet = array_keys($testprojects);
  $rolesCache = null;
  foreach($idSet as $tk)
  {
    // $rolesCache[$testprojects[$tk]['effective_role']][] = $tk;  
    if(!isset($rolesCache[$testprojects[$tk]['effective_role']]))
    {
      $rolesCache[$testprojects[$tk]['effective_role']] = new tlRole($testprojects[$tk]['effective_role']);
      $rolesCache[$testprojects[$tk]['effective_role']]->readFromDB($dbHandler);
    }  
  }  

  foreach($key2loop as $idx)
  {
    //echo '<br>Analizing TProj:' . $idx;
    $answer = $rolesCache[$testprojects[$idx]['effective_role']]->hasRight("user_role_assignment");
    //echo 'Question is: user_role_assignment - ANSWER IS:' . $answer . '<br>';
    
    if($answer == false)
    {
      $answer = $rolesCache[$testprojects[$idx]['effective_role']]->hasRight("testproject_user_role_assignment");  
      // echo 'Question is: testproject_user_role_assignment - ANSWER IS:' . $answer . '<br>';
    }  

    if($answer == true)
    {
      $features[$idx] = $testprojects[$idx];
    } 
  }
  //new dBug($features);
  //die();

	// If have no a test project ID, try to figure out which test project to show
	// Try with session info, if failed go to first test project available.
	if (!$argsObj->featureID)
	{
		if ($argsObj->testprojectID)
		{
			$argsObj->featureID = $argsObj->testprojectID;
		}
		else if (sizeof($features))
		{
		  $xx = current($features);
			$argsObj->featureID = $xx['id'];
		}	
	}
	
	// get private/public status for feature2check
	$featureIsPublic = 1;
	// $loop2do = sizeof($testprojects);
	// for($ppx=0; $ppx < $loop2do; $ppx++)
	$key2loop = array_keys($testprojects);
  foreach($key2loop as $ppx)
	{
		if( $testprojects[$ppx]['id'] == $argsObj->featureID )
		{
			$featureIsPublic = $testprojects[$ppx]['is_public'];
			break;
		}
	}
	
	foreach($users as &$user)
	{
		$user->readTestProjectRoles($dbHandler,$argsObj->featureID);
	}
	$effectiveRoles = get_tproject_effective_role($dbHandler,array('id' => $argsObj->featureID, 
	                                                               'is_public' => $featureIsPublic),null,$users);

	return array($effectiveRoles,$features,$argsObj->featureID);
}



/**
 * getTestPlanEffectiveRoles
 *
 */
function getTestPlanEffectiveRoles(&$dbHandler,&$tplanMgr,$tprojectMgr,&$argsObj,&$users)
{
  $features = array();
  $activeTestplans = $tprojectMgr->get_all_testplans($argsObj->testprojectID, array('plan_status' => 1));

  
  $ret = null;
  $status_ok = !is_null($activeTestplans);
  if($status_ok)
  {
    $myAccessibleSet = $argsObj->user->getAccessibleTestPlans($dbHandler,$argsObj->testprojectID,null,
                                                              array('output' =>'map'));
                                                              
    // we want to change map key, from testplan id to a sequential index
    // to maintain old logic
    $activeKeys = array_keys($activeTestplans);
    $myKeys = array_keys((array)$myAccessibleSet);
  //  $key2remove_intersect = array_intersect($activeKeys,$myKeys);
    $key2remove = $key2remove_diff = array_diff($activeKeys,$myKeys);
     if( !is_null($key2remove) )
    {
      foreach($key2remove as $target)
      {
        unset($activeTestplans[$target]);
      }
    }
    
  
  
    if($argsObj->user->hasRight($dbHandler,"mgt_users"))
    {
      $features = $activeTestplans;
    }
    else
    {
      //$loop2do = sizeof($activeTestplans);
      //for($idx = 0; $idx < $loop2do; $idx++)
      $features = array();
      $key2loop = array_keys($activeTestplans);
      foreach($key2loop as $idx)
      {
        // Humm!!, think we need to check testplan_user_role_assignment and not "testplan_planning"
        if($argsObj->user->hasRight($dbHandler,"testplan_user_role_assignment",null,
                                    $activeTestplans[$idx]['id']) == "yes")
        {
          $features[$idx] = $activeTestplans[$idx];
        } 
      }
    }
      
    //if nothing special was selected, use the one in the session or the first
    if (!$argsObj->featureID)
    {
      if (sizeof($features))
      {
        if ($argsObj->testplanID)
        {
          // $loop2do = sizeof($features);
          // for($idx = 0; $idx < $loop2do; $idx++)
          $key2loop = array_keys($features);
          foreach($key2loop as $idx)
          {
            if ($argsObj->testplanID == $features[$idx]['id'])
            {
              $argsObj->featureID = $argsObj->testplanID;
            } 
          }
        }
        if (!$argsObj->featureID)
        {
          $xx = current($features);
          $argsObj->featureID = $xx['id'];
        } 
      }
    }
    foreach($users as &$user)
    {
      $user->readTestProjectRoles($dbHandler,$argsObj->testprojectID);
      $user->readTestPlanRoles($dbHandler,$argsObj->featureID);
    }
      
    $tproject_info = $tprojectMgr->get_by_id($argsObj->testprojectID);
    $effectiveRoles = get_tplan_effective_role($dbHandler,$argsObj->featureID,$tproject_info,null,$users);
    $ret = array($effectiveRoles,$features,$argsObj->featureID);
  }
  return $ret;
}



/**
 * getTestPlanEffectiveRoles
 *
 */
function getTestPlanEffectiveRolesNEW(&$dbHandler,&$tplanMgr,$tprojectMgr,&$argsObj,&$users)
{
	$features = array();
	$activeTestplans = $tprojectMgr->get_all_testplans($argsObj->testprojectID, array('plan_status' => 1));

 	
	$ret = null;
	$status_ok = !is_null($activeTestplans);
	if($status_ok)
	{
  	$myAccessibleSet = $argsObj->user->getAccessibleTestPlans($dbHandler,$argsObj->testprojectID,null,
	                                                            array('output' =>'map'));
	                                                            

    //echo __LINE__;
    //echo __FUNCTION__;
    //new dBug($myAccessibleSet);
   
    // we want to change map key, from testplan id to a sequential index to maintain old logic
    $activeKeys = array_keys($activeTestplans);
    $myKeys = array_keys((array)$myAccessibleSet);
	  $key2remove = $key2remove_diff = array_diff($activeKeys,$myKeys);
    if( !is_null($key2remove) )
    {
      foreach($key2remove as $target)
      {
        unset($activeTestplans[$target]);
      }
    }
    
	  // $activeTestplans = array_values($activeTestplans);
    //new dBug($activeTestplans);  
    	  
    // 2013-04-01
    // now is not clear why this logic is right  
    //
    // analisys has to go from detail (test plan) to general
    // Step 1 - check if user has specific role on test plan
    // Step 2 - If Step 1 fails
    //          check if user has specific role on test project
    //          that contains the test project
    // Step 3 - If Step 2 fails
    //          check Global Role.
    // 

		if($argsObj->user->hasRight($dbHandler,"mgt_users"))
		{
			$features = $activeTestplans;
		}
		else
		{
		  //$loop2do = sizeof($activeTestplans);
			//for($idx = 0; $idx < $loop2do; $idx++)
			$features = array();
		  $key2loop = array_keys($activeTestplans);
			foreach($key2loop as $idx)
			{
        // Humm!!, think we need to check testplan_user_role_assignment and not "testplan_planning"
				if($argsObj->user->hasRight($dbHandler,"testplan_user_role_assignment",null,
				                            $activeTestplans[$idx]['id']) == "yes")
				{
					$features[$idx] = $activeTestplans[$idx];
				}	
			}
		}
    	
		//if nothing special was selected, use the one in the session or the first
		if (!$argsObj->featureID)
		{
			if (sizeof($features))
			{
				if ($argsObj->testplanID)
				{
				  // $loop2do = sizeof($features);
					// for($idx = 0; $idx < $loop2do; $idx++)
					$key2loop = array_keys($features);
					foreach($key2loop as $idx)
					{
						if ($argsObj->testplanID == $features[$idx]['id'])
						{
							$argsObj->featureID = $argsObj->testplanID;
						}	
					}
				}
				if (!$argsObj->featureID)
				{
				  $xx = current($features);
					$argsObj->featureID = $xx['id'];
				}	
			}
		}

		foreach($users as &$user)
		{
			$user->readTestProjectRoles($dbHandler,$argsObj->testprojectID);
			$user->readTestPlanRoles($dbHandler,$argsObj->featureID);
		}
    	
		$tproject_info = $tprojectMgr->get_by_id($argsObj->testprojectID);
      
		$effectiveRoles = get_tplan_effective_role($dbHandler,$argsObj->featureID,$tproject_info,null,$users);

    // it seems that here is the best place to check if current logged user
    // can manege roles on current selected test plan.
    // why I did not find this before ???
    $features = array();
    $key2loop = array_keys($activeTestplans);
    foreach($key2loop as $idx)
    {
      $answer = $rolesCache[$testprojects[$idx]['effective_role']]->hasRight("user_role_assignment");
      //echo 'Question is: user_role_assignment - ANSWER IS:' . $answer . '<br>';
    
      if($answer == false)
      {
        $answer = $rolesCache[$testprojects[$idx]['effective_role']]->hasRight("testproject_user_role_assignment");  
        // echo 'Question is: testproject_user_role_assignment - ANSWER IS:' . $answer . '<br>';
      }  

      if($answer == true)
      {
        $features[$idx] = $testprojects[$idx];
      } 


        // Humm!!, think we need to check testplan_user_role_assignment and not "testplan_planning"
        if($argsObj->user->hasRight($dbHandler,"testplan_user_role_assignment",null,
                                    $activeTestplans[$idx]['id']) == "yes")
        {
          $features[$idx] = $activeTestplans[$idx];
        } 
    }



 		$ret = array($effectiveRoles,$features,$argsObj->featureID);
	}
	return $ret;
}


function doUpdate(&$dbHandler,&$argsObj,&$featureMgr)
{
	$featureMgr->deleteUserRoles($argsObj->featureID,
                               array_keys($argsObj->map_userid_roleid));
	foreach($argsObj->map_userid_roleid as $user_id => $role_id)
	{
		if ($role_id)
		{
			$featureMgr->addUserRole($user_id,$argsObj->featureID,$role_id);
		}	
	}
}

function initializeGui(&$dbHandler,$argsObj)
{
  $gui = new stdClass();
  
  $gui->highlight = initialize_tabsmenu();
  $gui->user_feedback = '';
  $gui->no_features = '';
  $gui->roles_updated = '';
  $gui->tproject_name = $argsObj->testprojectName;
  $gui->featureType = $argsObj->featureType;
  $gui->optRights = tlRole::getAll($dbHandler,null,null,null,tlRole::TLOBJ_O_GET_DETAIL_MINIMUM);
  $gui->features = null;
  $gui->featureID = null;
  $gui->role_colour = null;
  $gui->tprojectAccessTypeImg = '';

  $guiCfg = config_get('gui');
  if($guiCfg->usersAssignGlobalRoleColoring == ENABLED) 
  {
  	$gui->role_colour = tlRole::getRoleColourCfg($dbHandler);
  }
  return $gui;
}

/**
 *
 */
function initLabels()
{
  $tg = array('test_project_user_roles_updated' => null,
              'testproject_roles_assign_disabled' => null,
              'assign_tproject_roles' => null,
              'assign_tplan_roles' => null);
  $labels = init_labels($tg);
  return $labels;
}
