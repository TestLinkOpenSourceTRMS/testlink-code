<?php
/* TestLink Open Source Project - http://testlink.sourceforge.net/ */
/* $Id: planMilestones.php,v 1.5 2005/12/28 07:34:55 franciscom Exp $ */
/** 
 * Purpose:  This page allows the creation and editing of milestones.
 * @author Chad Rosen, Martin Havlat 
 *
 * @author 20050807 - fm
 * refactoring:  
 * removed deprecated: $_SESSION['project']
 *
 * 20051203 - scs - added displaying of tpname, cosmetic changes
 */
require_once('../../config.inc.php');
require_once("../functions/common.php");
require_once("plan.inc.php");
testlinkInitPage();

$newMileStone = isset($_POST['newMilestone']) ? $_POST['newMilestone'] : null;
$name = isset($_POST['name']) ? strings_stripSlashes($_POST['name']) : '';
$date = isset($_POST['date']) ? strings_stripSlashes($_POST['date']) : null;
$A = isset($_POST['A']) ? intval($_POST['A']) : 0;
$B = isset($_POST['B']) ? intval($_POST['B']) : 0;
$C = isset($_POST['C']) ? intval($_POST['C']) : 0;
$idPlan = isset($_SESSION['testPlanId']) ? $_SESSION['testPlanId'] : 0;

$sqlResult = null;
if($newMileStone)
{
	if (strlen($name))
	{
		if(strlen($date))
		{
			$s1 = strtotime($date." 23:59:59");
			$s2 = strtotime("now");
			if ($s1 >= $s2)
			{
				$result = insertTestPlanMileStone($idPlan,$name,$date,$A,$B,$C);
				if ($result)
					$sqlResult = 'ok';
				else
					$sqlResult = lang_get("warning_milestone_add_failed") . $GLOBALS['db']->error_msg();
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
$smarty = new TLSmarty();
$smarty->assign('sqlResult', $sqlResult);
$smarty->assign('tpName', $_SESSION['testPlanName']);
$smarty->assign('name', $name);
$smarty->display('planMilestonenew.tpl');
?>
