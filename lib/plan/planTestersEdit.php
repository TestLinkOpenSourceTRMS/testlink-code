<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: planTestersEdit.php,v ${file_name} $
 * @version $Revision: 1.3 $
 * @modified $Date: 2005/08/26 09:49:39 ${date} ${time} $ by $Author: havlat $
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
	
//update
$submit = isset($_POST['submit']) ? $_POST['submit'] : 0;
if ($submit)
{
	$projRightsArray = extractInput();

	if($type == 'users')
	{
		//First we need to delete everything from the projRights table for that user
		$resultDelete = deleteUsersProjectRights($id);
		//Then we loop through the data that was passed in
		foreach($projRightsArray as $projRights)
		{
			 //ignore the first value because it is the submit button
			if($projRights != 'Save')
				$resultDelete = insertProjectUserRight($projRights,$id);
		}
	}
	elseif($type == 'plans')
	{
		//First we need to delete everything from the projRights table for that project
		$resultDelete = deleteProjectRightsForProject($id);

		//Then we loop through the data that was passed in
		foreach($projRightsArray as $projRights)
		{
			 //ignore the first value because it is the submit button
			if($projRights != 'Save')
			{
				//We then need to add the new data to the projRights table
				$resultDelete = insertProjectUserRight($id,$projRights);
			}
		}
	}
	/** @todo return real sql result */
	$update = 'ok';
}

// collect data for display
$arrData = null;
if ($type == 'plans')
{
	$title = lang_get('title_assign_users') . $_SESSION['testPlanName'];
	$name = 'selected="selected"';
	getUsersOfPlan($id,$arrData);
}
else
{ 	
	// if users
	$title = lang_get('title_assign_tp') . getUserLogin($id);
	getUserTestPlans1($id,$arrData);
}

$smarty = new TLSmarty;
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


function getUserLogin($id)
{
	$users = null;

	getUserById($id,$users);
	$userInfo = "<Not found>";
	if (sizeof($users))
	{
		$user = $users[0];
		$userInfo = $user[1]. ' ('. $user[3] . ' ' . $user[4] . ')';
	}
	
	return $userInfo;
}
?>
