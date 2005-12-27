<?php
/* TestLink Open Source Project - http://testlink.sourceforge.net/ */
/* $Id: planOwner.php,v 1.9 2005/12/27 11:16:12 franciscom Exp $ */
/**
 * Manage the ownership and priority of test suite
 *
 * @author Francisco Mancardi - 20050914 - refactoring         
 * @author Francisco Mancardi - 20050907 - bug on help          
 * 
 * 20051112 - scs - simplified case 'component', added localization of imp's
 * 					small cosmetic changes
 * 20051203 - scs - added filtering of tp users by tpid
 */
require('../../config.inc.php');
require("../functions/common.php");
require_once('plan.inc.php');
testlinkInitPage();

$level = isset($_GET['level']) ? $_GET['level'] : null;
$compID = isset($_GET['data']) ? intval($_GET['data']) : null;
$catID = isset($_GET['data']) ? intval($_GET['data']) : null;

$tpID = $_SESSION['testPlanId'];
$updated = null;
if(isset($_POST['updateSuiteAttribute']) && $_POST['updateSuiteAttribute'])
{
	$updated = updateSuiteAttributes($_POST);
}

$arrSuites = null;
if($level == 'root')
{
	// 20051001 - fm -BUGID 0000133: Broken link in priority assignment
	// 20050922 - fm -BUGID 0000133: Broken link in priority assignment
	redirect("../../" . TL_INSTRUCTIONS_RPATH . $_SESSION['locale'] . '/planOwnerAndPriority.html');
}	
else if($level == 'component')
{
	$arrSuites = getAllTestPlanComponentCategories($tpID,$compID);
}
else if($level == 'category')
{
	$arrSuites = getTP_category_info($catID);
}

$arrUsers = getTestPlanUsers($tpID);
echo "<pre>debug-47"; print_r($arrUsers); echo "</pre>";

$smarty = new TLSmarty();
$smarty->assign('sqlResult', $updated);
$smarty->assign('optionImportance', array(
											'L' => lang_get('opt_imp_low'),
											'M' => lang_get('opt_imp_medium'),
											'H' => lang_get('opt_imp_high'),
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