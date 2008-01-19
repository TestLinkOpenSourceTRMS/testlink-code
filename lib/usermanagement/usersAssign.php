<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* This script is distributed under the GNU General Public License 2 or later. 
*
* Filename $RCSfile: usersAssign.php,v $
*
* @version $Revision: 1.7 $
* @modified $Date: 2008/01/19 17:47:48 $ $Author: schlundus $
* 
* Allows assigning users roles to testplans or testprojects
*
* rev :
*      20070819 - franciscom - 
*      refactoring of delete and insert calls
*      new functions to generate $userFeatureRoles
*
*      20070227 - franciscom - refatoring to solve refresh problem
*                              when changing test project on navBar
*
*      20070829 - jbarchibald - fix bug 1000 - Testplan role assignments
*/
require_once('../../config.inc.php');
require_once('users.inc.php');
testlinkInitPage($db);

$template_dir = 'usermanagement/';
$default_template = str_replace('.php','.tpl',basename($_SERVER['SCRIPT_NAME']));

$feature = isset($_REQUEST['feature']) ? $_REQUEST['feature'] : null;
$featureID = isset($_REQUEST['featureID']) ? intval($_REQUEST['featureID']) : 0;
$map_userid_roleid = isset($_REQUEST['userRole']) ? $_REQUEST['userRole'] : null;
$bUpdate = isset($_REQUEST['do_update']) ? 1 : 0;

$testprojectID = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
$testprojectName = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : null;
$tpID = isset($_SESSION['testPlanId']) ? $_SESSION['testPlanId'] : 0;
$userID = $_SESSION['currentUser']->dbID;

$user_feedback = '';
$no_features = '';
$roles_updated = '';
$bTestproject = false;
$bTestPlan = false;
$mgr = null;

if ($feature == "testproject")
{
	$roles_updated = lang_get("test_project_user_roles_updated");
	$no_features = lang_get("no_test_projects");
	$bTestproject = true;
	$mgr = new testproject($db);
}
else if ($feature == "testplan")
{
	$roles_updated = lang_get("test_plan_user_roles_updated");
	$no_features = lang_get("no_test_plans");
	$bTestPlan = true;
	$mgr = new testplan($db);
}

if ($featureID && $bUpdate && $mgr)
{
	tLog(TLS("audit_user_assign_changed"),'AUDIT',null,$featureID,$feature."s");
	$mgr->deleteUserRoles($featureID);			
	foreach($map_userid_roleid as $user_id => $role_id)
	{
		if ($role_id)
			$mgr->addUserRole($user_id,$featureID,$role_id);
	}
	$user_feedback = $roles_updated; 
}
$can_manage_users = has_rights($db,"mgt_users");

$userFeatureRoles = null;
$features = null;
if ($bTestproject)
{
	$gui_cfg = config_get('gui');
	$order_by = $gui_cfg->tprojects_combo_order_by;
	$features = $mgr->get_accessible_for_user($userID,'array_of_map',$order_by);

	// If have no a test project ID, try to figure out which test project to show
	// Try with session info, if failed go to first test project available. 
	if (!$featureID)
	{
		if ($testprojectID)
			$featureID = $testprojectID;
		else if (sizeof($features))
			$featureID = $features[0]['id'];
	}
	$userFeatureRoles = get_tproject_effective_role($db,$featureID);
}
else if($bTestPlan)
{
	$activeFeatures = getAllActiveTestPlans($db,$testprojectID,$_SESSION['filter_tp_by_product']);
	$features = array();
	if ($can_manage_users)
		$features = $activeFeatures;
	else
	{
		for($i = 0;$i < sizeof($activeFeatures);$i++)
		{
			$f = $activeFeatures[$i];
			if (has_rights($db,"testplan_planning",null,$f['id']))
				$features[] = $f;
		}
	}
	//if nothing special was selected, use the one in the session or the first
	if (!$featureID)
	{
		if (sizeof($features))
		{
			if ($tpID)
			{
				for($i = 0;$i < sizeof($features);$i++)
				{
					if ($tpID == $features[$i]['id'])
						$featureID = $tpID;
				}
			}
			if (!$featureID)
				$featureID = $features[0]['id'];
		}
	}
	
	$userFeatureRoles = get_tplan_effective_role($db,$featureID,$testprojectID);
}
if(is_null($features))
	$user_feedback = $no_features;

$smarty = new TLSmarty();
$smarty->assign('user_feedback',$user_feedback);
$smarty->assign('mgt_users',$can_manage_users);
$smarty->assign('role_management',has_rights($db,"role_management"));
$smarty->assign('tp_user_role_assignment', 
                $can_manage_users ? "yes" : has_rights($db,"testplan_user_role_assignment"));
$smarty->assign('tproject_user_role_assignment', 
                $can_manage_users ? "yes" : has_rights($db,"user_role_assignment",null,-1));
$smarty->assign('tproject_name',$testprojectName);
$smarty->assign('optRights', tlRole::getAll($db,null,null,null,tlRole::TLOBJ_O_GET_DETAIL_MINIMUM));
$smarty->assign('userData',tlUser::getAll($db));
$smarty->assign('userFeatureRoles',$userFeatureRoles);
$smarty->assign('featureID',$featureID);
$smarty->assign('feature',$feature);
$smarty->assign('features',$features);
$smarty->display($template_dir . $default_template);
?>