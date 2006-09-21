<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: planEdit.php,v $
 *
 * @version $Revision: 1.20 $
 * @modified $Date: 2006/09/21 08:32:24 $ $Author: franciscom $
 *
 * Purpose:  ability to edit and delete testplans
 *
 */
require('../../config.inc.php');
require_once("../functions/common.php");
require_once("plan.inc.php");
testlinkInitPage($db);

$tproject_id = $_SESSION['testprojectID'];
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$bDelete = isset($_GET['deleteTP']) ? intval($_GET['deleteTP']) : 0;

$tplan_mgr=new testplan($db);
$generalResult = null;
$editResult = null;
if($bDelete)
{

		//unset the session tp if its deleted
		if (isset($_SESSION['testPlanId']) && ($_SESSION['testPlanId'] = $id))
		{
			$_SESSION['testPlanId'] = 0;
			$_SESSION['testPlanName'] = null;
		}
}
$smarty = new TLSmarty();
$smarty->assign('editResult',$generalResult);
$smarty->assign('arrPlan', getAllTestPlans($db,$tproject_id,TP_ALL_STATUS,FILTER_BY_PRODUCT));
$smarty->display('planEdit.tpl');
?>
