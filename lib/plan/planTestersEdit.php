<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: planTestersEdit.php,v $
 * @version $Revision: 1.17 $
 * @modified $Date: 2006/02/25 21:48:26 $ $ by $Author: schlundus $
 * 
 * @author Martin Havlat
 * 
 * This page allows assignment Users to Test Plan. 
 * 
 *
 * rev :
 * 20050810 - fm	I18N
 * 200508	MHT		Corrected syntax bugs, wrong variable using, updated header
 * 
 * @todo move functions to included script
 * 
 * 20051112 - scs - fixed wrong sql statement, because 'Save' button is 
 * 					localized
 * 20051118 - scs - wrong tp name is displayed when clicked on a tp on the left
 * 20051120 - fm - adding test plan filter by product behaivour
 * 20051130 - fm - BUGID 239
 * 20051231 - scs - changes due to ADBdb
 * 20060103 - scs - ADOdb changes
 *
 */
require('../../config.inc.php');
require_once('common.php');
require_once('users.inc.php');
require_once('plan.inc.php');
testlinkInitPage($db);

$type = isset($_GET['type']) ? $_GET['type'] : 0;
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if(!$type || !$id)
{
	redirect( $_SESSION['basehref'] . "/gui/instructions/planTesters.html");
}

// 20051120 - fm
// The current selected Product
$prod->id   = $_SESSION['testprojectID'];
$prod->name = $_SESSION['testprojectName'];

	
$submit = isset($_POST['submit']) ? $_POST['submit'] : 0;
if ($submit)
{
	//remove the submit button
	unset($_POST['submit']);
	$projRightsArray = hash2array($_POST,false);
	
	if($type == 'users')
	{
		// 20051130 - fm - BUGID 239
		// delete everything from the testplans_rights table for that user
		$resultDelete = deleteUsersTestPlanRights($db,$id, $prod->id);
		
		if (sizeof($projRightsArray))
		{
			foreach($projRightsArray as $projRights)
			{
				$resultDelete = insertTestPlanUserRight($db,$projRights,$id);
			}
		}
	}
	else if($type == 'plans')
	{
		//delete everything from the testplans_rights table for that testplan
		$resultDelete = deleteTestPlanRightsForTestPlan($db,$id);
		if (sizeof($projRightsArray))
		{
			foreach($projRightsArray as $projRights)
			{
				$resultDelete = insertTestPlanUserRight($db,$id,$projRights);
			}
		}
	}
	/** @todo return real sql result */
	$update = 'ok';
}
$arrData = null;
if ($type == 'plans')
{
	$tpName = $_SESSION['testPlanName'];
	$tps = getAllTestPlans($db);
	for($i = 0;$i < sizeof($tps);$i++)
	{
		if ($tps[$i]['id'] == $id)
		{
			$tpName = $tps[$i]['name'];
			break;
		}
	}
	$title = lang_get('title_assign_users') . $tpName;
	$name = 'selected="selected"';
	$arrData=getUsersOfPlan($db,$id);
}
else
{ 	
	// if users
	$title = lang_get('title_assign_tp') . getUserLogin($db,$id);
	// 20051120 - fm
	$arrData = getUserTestPlans1($db,$id,$prod->id);
}
$smarty = new TLSmarty();
$smarty->assign('title', $title);
$smarty->assign('arrData', $arrData);
$smarty->display('planTesters.tpl');
// =================================================================================


// =================================================================================
// 20050810 - fm - refactoring
// 20050824	MHT	corrected syntax bug, wrong variable using
// 20051120 - fm - interface changes, using product filter on test plan
function getUserTestPlans1(&$db,$userID,$prodID)
{
	$arrPlans = null;
	$userTestplans = getUserTestplans($db,$userID);

 	// 20051120 - fm
	$Testplans = getAllTestplans($db,$prodID,TP_ALL_STATUS,FILTER_BY_PRODUCT);
	$num_of_tp = sizeof($Testplans);
	$num_of_usertp = sizeof($userTestplans);
	
	for($i = 0; $i < $num_of_tp ;$i++)
	{
		$tp = $Testplans[$i];
		$checked = '';
		if ($userTestplans)
		{
			for($j = 0;$j < $num_of_usertp; $j++)
			{
				if ($userTestplans[$j][0] == $tp[0])
				{
					$checked = 'checked="checked"';
					break;
				}
			}
		}	
		$arrPlans[] = array(	
							'id' => $tp['id'], 
							'name' => $tp['name'],
							'checked' => $checked,
						);
	}
	return $arrPlans;
}


// 20051227 - fm
// 20051112 - scs - replaced not found with TL_Unknown
function getUserLogin(&$db,$id)
{
	$users = null;
	$users = getUserById($db,$id);

	$userInfo = '<'.lang_get('Unknown').'>';
	if (sizeof($users))
	{
		$user = $users[0];
		$userInfo = $user['login']. ' ('. $user['first'] . ' ' . $user['last'] . ')';
	}
	
	return $userInfo;
}
?>
