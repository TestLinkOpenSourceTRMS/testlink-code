<?php
/* TestLink Open Source Project - http://testlink.sourceforge.net/ */
/* $Id: planOwner.php,v 1.3 2005/09/07 06:23:06 franciscom Exp $ */
/**
 * Manage the ownership and priority of test suite
 *
 * @author Francisco Mancardi - 20050907 - bug on help          
 */
////////////////////////////////////////////////////////////////////////////////

require('../../config.inc.php');
require("../functions/common.php");
require_once('plan.inc.php');
require_once("../../lib/functions/lang_api.php");
testlinkInitPage();

// collect available users
$arrUsers = getProjectUsers();
$updated = null;

// process update request
if(isset($_POST['updateSuiteAttribute']) && $_POST['updateSuiteAttribute'])
{
	$updated = updateSuiteAttributes($_POST);
}

// collect data
$level = isset($_GET['level']) ? $_GET['level'] : null;
$data = isset($_GET['data']) ? intval($_GET['data']) : null;
$arrSuites = null;
if($level == 'root')
{
	// 20050906 - fm
	redirect(TL_INSTRUCTIONS_RPATH . $_SESSION['locale'] . './planOwnerAndPriority.html');
}	
else if($level == 'component')
{
	//Selecting all categories from the components selected above
	$categories = null;
	$result = getAllTestPlanComponentCategories($_SESSION['testPlanId'],$data,$categories);
	for($i = 0;$i < sizeof($categories);$i++)
	{
		$oneSuite = getTestSuiteParameters($categories[$i]['id']);
		$arrSuites[] = $oneSuite[0];
	}
}
else if($level == 'category')
	$arrSuites = getTestSuiteParameters($data);

$smarty = new TLSmarty;
$smarty->assign('sqlResult', $updated);
$smarty->assign('optionImportance', array(
											'L' => 'Low',
											'M' => 'Medium',
											'H' => 'High'
										)
				);
$smarty->assign('optionRisk', array(
										'3' => '3',
										'2' => '2',
										'1' => '1'
									)
				);
$smarty->assign('arrUsers', $arrUsers);
$smarty->assign('arrSuites', $arrSuites);
$smarty->display('planOwner.tpl');
?>