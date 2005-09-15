<?php
/* TestLink Open Source Project - http://testlink.sourceforge.net/ */
/* $Id: planmilestoneedit.php,v 1.3 2005/09/15 17:00:14 franciscom Exp $ */
/** 
 * Purpose:  This page allows the creation and editing of milestones.
 * @author Chad Rosen, Martin Havlat 
 *
 *
 * @author 20050807 - fm
 * refactoring:  
 * removed deprecated: $_SESSION['project']
 *
 */
require_once('../../config.inc.php');
require_once("../functions/common.php");
require_once("plan.inc.php");
require_once("../../lib/functions/lang_api.php");
testlinkInitPage();

$editMileStone = isset($_POST['editMilestone']) ? $_POST['editMilestone'] : null;
$sqlResult = null;
if($editMileStone)
{
	//It is necessary to turn the $_POST map into a number valued array
	$newArray = extractInput(true);

	$i = 0; //Start the counter 
	while ($i < (count($newArray)-1))
	{ 
		$id = ($newArray[$i]);
		$name = ($newArray[$i+1]);
		if(isset($newArray[$i + 6]) && ($newArray[$i + 6] == 'on'))
		{
			$i = $i + 7;

			$result = deleteMileStone($id);
			if ($result)
				$safeName = htmlspecialchars($name);
		}
		else
		{
			$date = $newArray[$i+2];
			$A = intval($newArray[$i+3]);
			$B = intval($newArray[$i+4]);
			$C = intval($newArray[$i+5]);
			$i = $i + 6;

			$result = updateMileStone($id,$name,$date,$A,$B,$C);
		}
	}
	$sqlResult = "ok";
}

$mileStones = null;

// 20050807 - fm
getTestPlanMileStones($_SESSION['testPlanId'],$mileStones);

$smarty = new TLSmarty;
$smarty->assign('arrMilestone', $mileStones);
$smarty->assign('sqlResult', $sqlResult);
$smarty->display('planMilestoneEdit.tpl');
?>
