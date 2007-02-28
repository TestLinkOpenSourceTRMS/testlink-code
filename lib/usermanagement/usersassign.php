<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* This script is distributed under the GNU General Public License 2 or later. 
*
* Filename $RCSfile: usersassign.php,v $
*
* @version $Revision: 1.10 $
* @modified $Date: 2007/02/28 08:04:58 $ $Author: franciscom $
* 
* Allows assigning users roles to testplans or testprojects
*
* rev :
*      20070227 - franciscom - refatoring to solve refresh problem
*                              when changing test project on navBar
*/
require_once('../../config.inc.php');
require_once('users.inc.php');
testlinkInitPage($db);

$feature = isset($_REQUEST['feature']) ? $_REQUEST['feature'] : null;
$featureID = isset($_REQUEST['featureID']) ? intval($_REQUEST['featureID']) : 0;

$testprojectID = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
$tpID = isset($_SESSION['testPlanId']) ? $_SESSION['testPlanId'] : 0;
$userID = $_SESSION['userID'];

$user_feedback='';
$no_features='';
$roles_updated='';

$testPlans = null;
$bTestproject = false;
$bTestPlan = false;
$featureID = isset($_REQUEST['featureID']) ? $_REQUEST['featureID'] : 0;

if ($feature == "testproject")
{
  $roles_updated=lang_get("test_project_user_roles_updated");
	$no_features=lang_get("no_test_projects");
	$bTestproject = true;
}
else if ($feature == "testplan")
{
  $roles_updated=lang_get("test_plan_user_roles_updated");
  $no_features=lang_get("no_test_plans");
	$bTestPlan = true;
}

$bUpdate = isset($_REQUEST['do_update']) ? 1 : 0;

if ($featureID && $bUpdate)
{
	$map_userid_roleid = $_REQUEST['userRole'];
	
	if ($bTestproject)
		deleteProductUserRoles($db,$featureID);			
		
	else if ($bTestPlan)
		deleteTestPlanUserRoles($db,$featureID);					
	
	foreach($map_userid_roleid as $user_id => $role_id)
	{
		if ($role_id)
		{
			if ($bTestproject)
				insertUserTestProjectRole($db,$user_id,$featureID,$role_id);
			else if ($bTestPlan)
				insertUserTestPlanRole($db,$user_id,$featureID,$role_id);
		}
	}
	$user_feedback=$roles_updated; 
}
$userData = getAllUsers($db);

$userFeatureRoles = null;
$features = null;
if ($bTestproject)
{
	$features = getAccessibleProducts($db);
	if (!$featureID)
	{
		if ($testprojectID)
			$featureID = $testprojectID;
		else if (sizeof($features))
		{
			$k = key($features);
			$featureID = $k;
		}
	}
	$userFeatureRoles = getProductUserRoles($db,$featureID);
}
else if($bTestPlan)
{
	$activeFeatures = getAllActiveTestPlans($db,$testprojectID,$_SESSION['filter_tp_by_product']);
	$features = array();
	if (has_rights($db,"mgt_users"))
		$features = $activeFeatures;
	else if (sizeof($activeFeatures))
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
		if ($tpID)
			$featureID = $tpID;
		else if (sizeof($features))
			$featureID = $features[0]['id'];
	}
	$userFeatureRoles = getTestPlanUserRoles($db,$featureID);
}
$roleList = getAllRoles($db);

$can_manage_users = has_rights($db,"mgt_users");

$smarty = new TLSmarty();

if( is_null($features) )
{
  $user_feedback=$no_features;
}
$smarty->assign('user_feedback',$user_feedback);

$smarty->assign('mgt_users',$can_manage_users);
$smarty->assign('role_management',has_rights($db,"role_management"));
$smarty->assign('tp_user_role_assignment', 
                $can_manage_users ? "yes" : has_rights($db,"user_role_assignment"));
$smarty->assign('tproject_user_role_assignment', 
                $can_manage_users ? "yes" : has_rights($db,"user_role_assignment",null,-1));
                
$smarty->assign('optRights', $roleList);
$smarty->assign('userData', $userData);
$smarty->assign('userFeatureRoles',$userFeatureRoles);
$smarty->assign('featureID',$featureID);
$smarty->assign('feature',$feature);
$smarty->assign('features',$features);
$smarty->display('usersassign.tpl');
?>