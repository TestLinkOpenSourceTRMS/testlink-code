<?php
/* TestLink Open Source Project - http://testlink.sourceforge.net/ */
/* $Id: planOwner.php,v 1.6 2005/10/03 07:20:14 franciscom Exp $ */
/**
 * Manage the ownership and priority of test suite
 *
 * @author Francisco Mancardi - 20050914 - refactoring         
 * @author Francisco Mancardi - 20050907 - bug on help          
 */
////////////////////////////////////////////////////////////////////////////////

require('../../config.inc.php');
require("../functions/common.php");
require_once('plan.inc.php');
require_once("../../lib/functions/lang_api.php");
testlinkInitPage();

// collect available users
$arrUsers = getTestPlanUsers();
$updated = null;

// process update request
if(isset($_POST['updateSuiteAttribute']) && $_POST['updateSuiteAttribute'])
{
	$updated = updateSuiteAttributes($_POST);
}

$level = isset($_GET['level']) ? $_GET['level'] : null;
$compID = isset($_GET['data']) ? intval($_GET['data']) : null;
$catID = isset($_GET['data']) ? intval($_GET['data']) : null;
$arrSuites = null;

if($level == 'root')
{
	// 20051001 - fm -BUGID 0000133: Broken link in priority assignment
	// 20050922 - fm -BUGID 0000133: Broken link in priority assignment
	redirect("../../" . TL_INSTRUCTIONS_RPATH . $_SESSION['locale'] . '/planOwnerAndPriority.html');
}	
else if($level == 'component')
{
	$categories = getAllTestPlanComponentCategories($_SESSION['testPlanId'],$compID);
	$num_cat = sizeof($categories);
	for($idx = 0; $idx < $num_cat; $idx++)
	{
		$arrSuites[] = $categories[$idx];
	}
	
}
else if($level == 'category')
{
	$arrSuites = getTP_category_info($catID);
}


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