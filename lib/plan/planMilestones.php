<?php
/* TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: planMilestones.php,v $
 *
 * @version $Revision: 1.8 $
 * @modified $Date: 2007/01/13 23:45:37 $
 */
require_once('../../config.inc.php');
require_once("../functions/common.php");
require_once("plan.inc.php");
testlinkInitPage($db);

$_POST = strings_stripSlashes($_POST);
$name = isset($_POST['name']) ? $_POST['name'] : '';
$date = isset($_POST['date']) ? $_POST['date'] : null;
$A = isset($_POST['A']) ? $_POST['A'] : 0;
$B = isset($_POST['B']) ? $_POST['B'] : 0;
$C = isset($_POST['C']) ? $_POST['C'] : 0;
$newMileStone = isset($_POST['newMilestone']) ? $_POST['newMilestone'] : null;
$bDelete = isset($_GET['delete']) ? $_GET['delete'] : null;
$bUpdate = isset($_POST['update']) ? $_POST['update'] : null;
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$pid = isset($_POST['id']) ? intval($_POST['id']) : 0;
$idPlan = isset($_SESSION['testPlanId']) ? $_SESSION['testPlanId'] : 0;
$tpName = $_SESSION['testPlanName'];

$sqlResult = null;
$mileStone = null;
$action = null;

if ($id && !$bDelete && !$bUpdate && !$newMileStone)
{
	$mileStones = null;
	getTestPlanMileStones($db,$idPlan,$mileStones,$id);
	if ($mileStones)
		$mileStone = $mileStones[0];
}
else if ($bDelete && $id)
{
	$sqlResult = 'ok';
	if (!deleteMileStone($db,$id))
		$sqlResult = lang_get('milestone_delete_fails'). ' : ' . $db->error_msg();
	$action = "deleted";
}
else if($newMileStone || $bUpdate)
{
	$sqlResult = checkMileStone($name,$date,$A,$B,$C);
	if (is_null($sqlResult))
	{
		$sqlResult = 'ok';
		if ($newMileStone)
		{
			if (!insertTestPlanMileStone($db,$idPlan,$name,$date,$A,$B,$C))
				$sqlResult = lang_get("warning_milestone_add_failed") . $db->error_msg();
		}
		else if ($pid)
		{
			if (!updateMileStone($db,$pid,$name,$date,$A,$B,$C))
				$sqlResult = lang_get("warning_milestone_update_failed") . $db->error_msg();
		}
	}
	//reset info, after successful updating	
	$action = $bUpdate ? "updated" : "add";
}

$mileStones = null;
getTestPlanMileStones($db,$idPlan,$mileStones,null);

$smarty = new TLSmarty();
$smarty->assign('sqlResult', $sqlResult);
$smarty->assign('tpName', $tpName);
$smarty->assign('arrMilestone', $mileStones);
$smarty->assign('mileStone', $mileStone);
$smarty->assign('action', $action);
$smarty->display('planMilestones.tpl');
?>
