<?php
/* TestLink Open Source Project - http://testlink.sourceforge.net/ */
/* $Id: planPriority.php,v 1.7 2006/08/17 19:29:59 schlundus Exp $ */
/**
 * This feature allows to define rules for priority dependecy 
 * to importance/risk for actual Test Plan
 *          
 * @author Francisco Mancardi - 20050905 - reduce global coupling
 */
////////////////////////////////////////////////////////////////////////////////
require('../../config.inc.php');
require_once("../functions/common.php");
require_once("../functions/priority.inc.php");
testlinkInitPage($db);

$sqlResult = null;
if(isset($_POST['updatePriorityRules']))
{
	$newArray = hash2array($_POST,false);
	$sqlResult = setPriority($db,$newArray);
}

$data = getPriority($db,$_SESSION['testPlanId']);

$smarty = new TLSmarty();
$smarty->assign('optionPriority', array(
	'a' => 'A', 'b' => 'B', 'c' => 'C'));
$smarty->assign('sqlResult', $sqlResult);
$smarty->assign('arrRules', $data);
$smarty->display('planPriority.tpl');
?>
