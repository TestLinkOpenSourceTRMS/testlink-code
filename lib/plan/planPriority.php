<?php
/* TestLink Open Source Project - http://testlink.sourceforge.net/ */
/* $Id: planPriority.php,v 1.2 2005/08/16 18:00:57 franciscom Exp $ */
/**
 * This feature allows to define rules for priority dependecy 
 * to importance/risk for actual Test Plan
 *          
 */
////////////////////////////////////////////////////////////////////////////////
require('../../config.inc.php');
require("../functions/common.php");
require("../functions/priority.inc.php");
require_once("../../lib/functions/lang_api.php");
testlinkInitPage();

// update data
$sqlResult = null;
if(isset($_POST['updatePriorityRules'])){
	$newArray = extractInput();
	$sqlResult = setPriority($newArray);
}

// get actual values
$data = getPriority();

$smarty = new TLSmarty;
$smarty->assign('optionPriority', array(
	'a' => 'A', 'b' => 'B', 'c' => 'C'));
$smarty->assign('sqlResult', $sqlResult);
$smarty->assign('arrRules', $data);
$smarty->display('planPriority.tpl');
?>
