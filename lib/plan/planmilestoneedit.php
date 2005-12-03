<?php
/* TestLink Open Source Project - http://testlink.sourceforge.net/ */
/* $Id: planmilestoneedit.php,v 1.4 2005/12/03 22:09:35 schlundus Exp $ */
/** 
 * Purpose:  This page allows the creation and editing of milestones.
 * @author Chad Rosen, Martin Havlat 
 *
 *
 * @author 20050807 - fm
 * refactoring:  
 * removed deprecated: $_SESSION['project']
 *
 * 20051203 - scs - added the same checks while updating as when creating a 
 * 					new milestone
 */
require_once('../../config.inc.php');
require_once("../functions/common.php");
require_once("plan.inc.php");
testlinkInitPage();

$editMileStone = isset($_POST['editMilestone']) ? $_POST['editMilestone'] : null;
$sqlResult = null;
if($editMileStone)
{
	//It is necessary to turn the $_POST map into a number valued array
	$newArray = extractInput(true);

	$i = 0; 
	while ($i < (count($newArray)-1))
	{ 
		$id = ($newArray[$i]);
		$name = ($newArray[$i+1]);
		if(isset($newArray[$i + 6]) && ($newArray[$i + 6] == 'on'))
		{
			$i = $i + 7;
			$result = deleteMileStone($id);
		}
		else
		{
			$date = $newArray[$i+2];
			$A = intval($newArray[$i+3]);
			$B = intval($newArray[$i+4]);
			$C = intval($newArray[$i+5]);
			$i = $i + 6;

			if (strlen($name))
			{
				if(strlen($date))
				{
					$s1 = strtotime($date." 23:59:59");
					$s2 = strtotime("now");
					if ($s1 >= $s2)
					{
						$result = updateMileStone($id,$name,$date,$A,$B,$C);
					}
					else
						$sqlResult = lang_get('warning_milestone_date');
				}
				else
					$sqlResult = lang_get("warning_enter_valid_date");
			}
			else
				$sqlResult = lang_get("warning_empty_milestone_name");
		}
		if (!is_null($sqlResult))
			break;
	}
	if (is_null($sqlResult))
		$sqlResult = 'ok';
}

$mileStones = null;
getTestPlanMileStones($_SESSION['testPlanId'],$mileStones);

$smarty = new TLSmarty();
$smarty->assign('tpName', $_SESSION['testPlanName']);
$smarty->assign('arrMilestone', $mileStones);
$smarty->assign('sqlResult', $sqlResult);
$smarty->display('planMilestoneEdit.tpl');
?>
