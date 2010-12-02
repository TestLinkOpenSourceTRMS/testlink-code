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
 * @package 	TestLink
 * @copyright 	2005-2010, TestLink community
 * @version    	CVS: $Id: usersAssign.php,v 1.32.2.3 2010/12/02 06:42:04 franciscom Exp $
 * @link 		http://www.teamst.org/index.php
 *
 * @internal Revisions:
 *	20101202 - franciscom - BUGID 4065
 *	20101112 - franciscom - getTestPlanEffectiveRoles() fixed typo error -> errors on event viewer
 *  20101004 - asimon - adapted to new interface of getTestersForHtmlOptions
 *  20100930 - franciscom - BUGID 2344: Private test project
 *	20100313 - erikeloff - BUGID 3271 - show only active users on assign to project/test plan
 *	20091129 - franciscom - ISSUE 2554 - colouring
 *
 */
require_once('../../config.inc.php');
require_once('users.inc.php');
testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();

$assignRolesFor = null;
$featureMgr = null;
$userFeatureRoles = null;
$doInitGui = true;
$guiCfg = config_get('gui');

$tprojectMgr = new testproject($db);
$tplanMgr = new testplan($db);

$args = init_args();

$gui = new stdClass();
$gui->highlight = initialize_tabsmenu();
$gui->user_feedback = '';
$gui->no_features = '';
$gui->roles_updated = '';
$gui->tproject_name = $args->testprojectName;
$gui->optRights = tlRole::getAll($db,null,null,null,tlRole::TLOBJ_O_GET_DETAIL_MINIMUM);
$gui->features = null;
$gui->featureType = $args->featureType;
$gui->featureID = null;

$gui->role_colour = null;
if($guiCfg->usersAssignGlobalRoleColoring == ENABLED) 
{
	$gui->role_colour = tlRole::getRoleColourCfg($db);
}

$target = new stdClass();
$target->testprojectID = null;
$target->testplanID = null;

switch($args->featureType)
{
    case "testproject":
    	$gui->highlight->assign_users_tproject = 1;
    	$gui->roles_updated = lang_get("test_project_user_roles_updated");
    	$gui->not_for_you = lang_get("testproject_roles_assign_disabled");
    	$assignRolesFor = $args->featureType;
    	$target->testprojectID = $args->featureID > 0 ? $args->featureID : null;
    	$featureMgr = &$tprojectMgr;
    break;
    
    case "testplan":
      	$gui->highlight->assign_users_tplan = 1;
    	$gui->roles_updated = lang_get("test_plan_user_roles_updated");
    	$gui->not_for_you = lang_get("testplan_roles_assign_disabled");
    	$assignRolesFor = $args->featureType;
    	$target->testprojectID = $args->testprojectID;
    	$featureMgr = &$tplanMgr;
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

		// 20101202 - franciscom - BUGID 4065
		if( is_null($info) )
		{
			$gui->user_feedback = lang_get('no_test_plans_available');
		}
        list($gui->userFeatureRoles,$gui->features,$gui->featureID)=$info;
    break;

}

$gui->grants = getGrantsForUserMgmt($db,$args->user,$target->testprojectID,-1);
if(is_null($gui->features) || count($gui->features) == 0)
{
    $gui->features = null;
	// 20101202 - franciscom - BUGID 4065
	if( $gui->user_feedback == '' )
	{
		$gui->user_feedback = $gui->not_for_you;
	}
}

$smarty = new TLSmarty();
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
    // First on current test without using test plan rights
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
	$order_by = $gui_cfg->tprojects_combo_order_by;

    // Accessible means user has a role on test project
	$testprojects = $objMgr->get_accessible_for_user($argsObj->userID,'array_of_map',$order_by);
	
	// Another more restrictive filter has to be applied, related to what we want to do
	// user has to be right to manage roles on test project 
	if($argsObj->user->hasRight($dbHandler,"mgt_users"))
	{
		$features = $testprojects;
	}
	else
	{
	    $loop2do = sizeof($testprojects);
        for($idx = 0; $idx < $loop2do; $idx++)
		{
		    $answer = $argsObj->user->hasRight($dbHandler,"testproject_user_role_assignment",$testprojects[$idx]['id'],-1);
			if($answer == "yes")
			{
				$features[] = $testprojects[$idx];
			}	
		}
	}
	
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
			$argsObj->featureID = $features[0]['id'];
		}	
	}
	
	// get private/public status for feature2check
	$loop2do = sizeof($testprojects);
	$featureIsPublic = 1;
	for($ppx=0; $ppx < $loop2do; $ppx++)
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
	$effectiveRoles = get_tproject_effective_role($dbHandler,array('id' => $argsObj->featureID, 'is_public' => $featureIsPublic),
												  null,$users);
	return array($effectiveRoles,$features,$argsObj->featureID);
}



/**
 * getTestPlanEffectiveRoles
 *
 * 20101202 - franciscom - BUGID 4065
 */
function getTestPlanEffectiveRoles(&$dbHandler,&$tplanMgr,$tprojectMgr,&$argsObj,&$users)
{
	$features = array();
	$activeTestplans = $tprojectMgr->get_all_testplans($argsObj->testprojectID, array('plan_status' => 1));
	
	$ret = null;
	$status_ok = !is_null($activeTestplans);
	if($status_ok)
	{
        // we want to change map key, from testplan id to a sequential index
        // to maintain old logic
	    $activeTestplans = array_values($activeTestplans);
    
		if($argsObj->user->hasRight($dbHandler,"mgt_users"))
		{
			$features = $activeTestplans;
		}
		else
		{
		    $loop2do = sizeof($activeTestplans);
			for($idx = 0; $idx < $loop2do; $idx++)
			{
			    // Humm!!, think we need to check testplan_user_role_assignment and not
			    // "testplan_planning"
				if($argsObj->user->hasRight($dbHandler,"testplan_user_role_assignment",null,$activeTestplans[$idx]['id']) == "yes")
				{
					$features[] = $activeTestplans[$idx];
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
				    $loop2do = sizeof($features);
					for($idx = 0; $idx < $loop2do; $idx++)
					{
						if ($argsObj->testplanID == $features[$idx]['id'])
						{
							$argsObj->featureID = $argsObj->testplanID;
						}	
					}
				}
				if (!$argsObj->featureID)
				{
					$argsObj->featureID = $features[0]['id'];
				}	
			}
		}
		foreach($users as &$user)
		{
			$user->readTestProjectRoles($dbHandler,$argsObj->testprojectID);
			$user->readTestPlanRoles($dbHandler,$argsObj->featureID);
		}
    	
		// 20101004 - asimon - adapted to new interface of getTestersForHtmlOptions
		$tproject_info = $tprojectMgr->get_by_id($argsObj->testprojectID);
    	
		$effectiveRoles = get_tplan_effective_role($dbHandler,$argsObj->featureID,$tproject_info,null,$users);
 		$ret = array($effectiveRoles,$features,$argsObj->featureID);
	}
	return $ret;
}


function doUpdate(&$dbHandler,&$argsObj,&$featureMgr)
{
	$featureMgr->deleteUserRoles($argsObj->featureID);
	foreach($argsObj->map_userid_roleid as $user_id => $role_id)
	{
		if ($role_id)
		{
			$featureMgr->addUserRole($user_id,$argsObj->featureID,$role_id);
		}	
	}
}
?>
