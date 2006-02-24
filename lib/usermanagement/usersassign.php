<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* This script is distributed under the GNU General Public License 2 or later. 
*
* Filename $RCSfile: usersassign.php,v $
*
* @version $Revision: 1.3 $
* @modified $Date: 2006/02/24 18:06:14 $
* 
* Allows assigning users roles to testplans or testprojects
*
* 20060224 - franciscom - changes in session product -> testproject
*/
require_once('../../config.inc.php');
require_once('users.inc.php');
testlinkInitPage($db);

$feature = isset($_GET['feature']) ? $_GET['feature'] : null;
$testprojectID = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
$tpName = isset($_SESSION['testPlanName']) ? $_SESSION['testPlanName'] : null;
$productName = isset($_SESSION['productName']) ? $_SESSION['productName'] : null;
$userID = $_SESSION['userID'];

$bProduct = false;
$bTestPlan = false;
if ($feature == "product")
{
	$featureID = $testprojectID;
	$bProduct = true;
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
		if ($feature == "product")
			$bProduct = true;
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
	
	if ($bProduct)
		deleteProductUserRoles($db,$featureID);			
	else if ($bTestPlan)
		deleteTestPlanUserRoles($db,$featureID);					
	
	foreach($users as $userRole => $roleID)
	{
		if ($roleID && (substr($userRole,0,8) == "userRole"))
		{
			$userID = intval(substr($userRole,8));
			if ($bProduct)
				insertUserProductRole($db,$userID,$featureID,$roleID);
			else if ($bTestPlan)
				insertUserTestPlanRole($db,$userID,$featureID,$roleID);
		}
		$sqlResult = 'ok';
		$action = "updated";
	}
}
$userData = getAllUsers($db);

if ($bProduct)
	$userFeatureRoles = getProductUserRoles($db,$featureID);
else if($bTestPlan)
{
	$testPlans = getTestPlans($db,$testprojectID,$userID,1);
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
$smarty->assign('productName',$productName);
$smarty->assign('testPlanName',$tpName);
$smarty->display('usersassign.tpl');
?>