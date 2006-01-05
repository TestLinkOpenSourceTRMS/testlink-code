<?php
/* TestLink Open Source Project - http://testlink.sourceforge.net/ */
/* $Id: planPriority.php,v 1.4 2006/01/05 07:30:34 franciscom Exp $ */
/**
 * This feature allows to define rules for priority dependecy 
 * to importance/risk for actual Test Plan
 *          
 * @author Francisco Mancardi - 20050905 - reduce global coupling
 */
////////////////////////////////////////////////////////////////////////////////
require('../../config.inc.php');
require("../functions/common.php");
require("../functions/priority.inc.php");
require_once("../../lib/functions/lang_api.php");
testlinkInitPage($db);

// update data
$sqlResult = null;
if(isset($_POST['updatePriorityRules'])){
	$newArray = extractInput();
	$sqlResult = setPriority($newArray);
}

// get actual values
// 20050905 - fm
$data = getPriority($_SESSION['testPlanId']);

$smarty = new TLSmarty;
$smarty->assign('optionPriority', array(
	'a' => 'A', 'b' => 'B', 'c' => 'C'));
$smarty->assign('sqlResult', $sqlResult);
$smarty->assign('arrRules', $data);
$smarty->display('planPriority.tpl');
?>
