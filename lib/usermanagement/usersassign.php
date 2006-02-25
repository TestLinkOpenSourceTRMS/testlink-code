<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* This script is distributed under the GNU General Public License 2 or later. 
*
* Filename $RCSfile: usersassign.php,v $
*
* @version $Revision: 1.4 $
* @modified $Date: 2006/02/25 07:02:25 $
* 
* Allows assigning users roles to testplans or testprojects
*
* 20060224 - franciscom - changes in session product -> testproject
*            getTestPlans() -> getAllActiveTestPlans()
*/
require_once('../../config.inc.php');
require_once('users.inc.php');
testlinkInitPage($db);

$feature = isset($_GET['feature']) ? $_GET['feature'] : null;
$testprojectID = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
$tplan_name = isset($_SESSION['testPlanName']) ? $_SESSION['testPlanName'] : null;
$testprojectName = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : null;
$userID = $_SESSION['userID'];

$btestproject = false;
$bTestPlan = false;
if ($feature == "testproject")
{
	$btestproject = true;
	$featureID = $testprojectID;
}
else if ($feature == "testplan")
{
	$bTestPlan = true;
	$featureID = isset($_GET['featureID']) ? $_GET['featureID'] : 0;
}

//postback
$bUpdate = isset($_POST['do_update']) ? 1 : 0;
if ($bUpdate)
{
	$featureID = isset($_POST['featureID']) ? intval($_POST['featureID']) : 0;
	if ($featureID)
	{
		$feature = isset($_POST['feature']) ? $_POST['feature'] : null;
		if ($feature == "testproject")
			$btestproject = true;
		else if ($feature == "testplan")
			$bTestPlan = true;
	}
}

if ($featureID && $bUpdate)
{
	//remove keys which are no longer used 
	unset($_POST['featureID']);
	unset($_POST['do_update']);
	unset($_POST['feature']);
	$users = $_POST;
	
	if ($btestproject)
		deleteProductUserRoles($db,$featureID);			
	else if ($bTestPlan)
		deleteTestPlanUserRoles($db,$featureID);					

  // 20060224 - franciscom - Please remove this magic number	
	foreach($users as $userRole => $roleID)
	{
		if ($roleID && (substr($userRole,0,8) == "userRole"))
		{
			$userID = intval(substr($userRole,8));
			if ($btestproject)
				insertUserProductRole($db,$userID,$featureID,$roleID);
			else if ($bTestPlan)
				insertUserTestPlanRole($db,$userID,$featureID,$roleID);
		}
		$sqlResult = 'ok';
		$action = "updated";
	}
}
$userData = getAllUsers($db);

if ($btestproject)
{
	$userFeatureRoles = getProductUserRoles($db,$featureID);
}	
else if($bTestPlan)
{
	// $testPlans = getTestPlans($db,$testprojectID,$userID,1);
	// 20060224 - franciscom
	// We can't filter by user because will be impossible to assign role to
	// unassigned testplans
	$testPlans = getAllActiveTestPlans($db,$testprojectID,FILTER_BY_TESTPROJECT);

	
	
	//if nothing special was selected, use the one in the session or the first
	if (!$featureID)
	{
		if (isset($_SESSION['testPlanId']) && $_SESSION['testPlanId'])
			$featureID = $_SESSION['testPlanId'];
		else if (sizeof($testPlans))
			$featureID = $testPlans[0]['id'];
	}
	$userFeatureRoles = getTestPlanUserRoles($db,$featureID);
}
$roleList = getListOfRoles($db);

$smarty = new TLSmarty();
$smarty->assign('optRights', $roleList);
$smarty->assign('userData', $userData);
$smarty->assign('userFeatureRoles',$userFeatureRoles);
$smarty->assign('featureID',$featureID);
$smarty->assign('feature',$feature);
$smarty->assign('result',$sqlResult);
$smarty->assign('action',$action);
$smarty->assign('testPlans',$testPlans);
$smarty->assign('testprojectName',$testprojectName);
$smarty->assign('testPlanName',$tplan_name);
$smarty->display('usersassign.tpl');
?>