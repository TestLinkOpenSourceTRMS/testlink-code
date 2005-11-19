<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: planTestersEdit.php,v ${file_name} $
 * @version $Revision: 1.6 $
 * @modified $Date: 2005/11/19 23:07:39 ${date} ${time} $ by $Author: schlundus $
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
 */
require('../../config.inc.php');
require_once('common.php');
require_once('users.inc.php');
require_once('plan.inc.php');
testlinkInitPage();

$type = isset($_GET['type']) ? $_GET['type'] : 0;
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if(!$type || !$id)
	redirect( $_SESSION['basehref'] . "/gui/instructions/planTesters.html");
	
$submit = isset($_POST['submit']) ? $_POST['submit'] : 0;
if ($submit)
{
	//remove the submit button
	unset($_POST['submit']);
	$projRightsArray = extractInput();
	
	if($type == 'users')
	{
		//delete everything from the projRights table for that user
		$resultDelete = deleteUsersProjectRights($id);
		if (sizeof($projRightsArray))
		{
			foreach($projRightsArray as $projRights)
			{
				$resultDelete = insertTestPlanUserRight($projRights,$id);
			}
		}
	}
	else if($type == 'plans')
	{
		//delete everything from the projRights table for that project
		$resultDelete = deleteTestPlanRightsForProject($id);
		if (sizeof($projRightsArray))
		{
			foreach($projRightsArray as $projRights)
			{
				$resultDelete = insertTestPlanUserRight($id,$projRights);
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
	$tps = getAllTestPlans();
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
	getUsersOfPlan($id,$arrData);
}
else
{ 	
	// if users
	$title = lang_get('title_assign_tp') . getUserLogin($id);
	getUserTestPlans1($id,$arrData);
}
$smarty = new TLSmarty();
$smarty->assign('title', $title);
$smarty->assign('arrData', $arrData);
$smarty->display('planTesters.tpl');
// =================================================================================


// =================================================================================
// 20050810 - fm - refactoring
// 20050824	MHT	corrected syntax bug, wrong variable using
function getUserTestPlans1($id,&$arrPlans)
{
	$arrPlans = null;
	$userTestplans = getUserTestplans($id);
	$Testplans = getAllTestplans();
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
							'id' => $tp[0], 
							'name' => $tp[1],
							'checked' => $checked,
						);
	}
	return 1;
}

//20051112 - scs - replaced not found with TL_Unknown
function getUserLogin($id)
{
	$users = null;

	getUserById($id,$users);
	$userInfo = '<'.lang_get('Unknown').'>';
	if (sizeof($users))
	{
		$user = $users[0];
		$userInfo = $user[1]. ' ('. $user[3] . ' ' . $user[4] . ')';
	}
	
	return $userInfo;
}
?>
