<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* This script is distributed under the GNU General Public License 2 or later. 
*
* Filename $RCSfile: usersassign.php,v $
*
* @version $Revision: 1.1 $
* @modified $Date: 2006/02/19 13:08:05 $
* 
* Allows assigning users roles to testplans or testprojects
*/
require_once('../../config.inc.php');
require_once('users.inc.php');
testlinkInitPage($db);

$feature = isset($_GET['feature']) ? $_GET['feature'] : null;
$bProduct = false;
$bTestPlan = false;
if ($feature == "product")
{
	$featureID = isset($_SESSION['productID']) ? $_SESSION['productID'] : 0;
	$bProduct = true;
}
else if ($feature == "testplan")
{
	$bTestPlan = true;
	$featureID = isset($_GET['featureID']) ? $_GET['featureID'] : 0;
}

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
	$testPlans = getTestPlans($db,$_SESSION['productID'],$_SESSION['userID'],1);
	if (!$featureID)
	{
		if (isset($_SESSION['testPlanId']) && $_SESSION['testPlanId'])
			$featureID = $_SESSION['testPlanId'];
		else if (sizeof($testPlans))
			$featureID = $testPlans[0]['id'];
	}
	$userFeatureRoles = getTestPlanUserRoles($db,$featureID);
}

$smarty = new TLSmarty();
$smarty->assign('optRights', getListOfRoles($db));
$smarty->assign('userData', $userData);
$smarty->assign('userFeatureRoles',$userFeatureRoles);
$smarty->assign('featureID',$featureID);
$smarty->assign('feature',$feature);
$smarty->assign('result',$sqlResult);
$smarty->assign('action',$action);
$smarty->assign('testPlans',$testPlans);
$smarty->assign('productName',$_SESSION['productName']);
$smarty->assign('testPlanName',$_SESSION['testPlanName']);
$smarty->display('usersassign.tpl');
?>