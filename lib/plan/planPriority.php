<?php
/* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: planPriority.php,v 1.8 2006/09/09 07:13:28 franciscom Exp $

This feature allows to define rules for priority dependency 
to importance/risk for actual Test Plan
*/
////////////////////////////////////////////////////////////////////////////////
require('../../config.inc.php');
require_once("../functions/common.php");
require_once("../functions/priority.inc.php");
testlinkInitPage($db);


$sqlResult = null;
if(isset($_POST['updatePriorityRules']))
{
  // user has submited the page
	$sqlResult = setPriority($db,$_POST['priority']);
}

$priorities = getPriority($db,$_SESSION['testPlanId']);

$smarty = new TLSmarty();
$smarty->assign('optionPriority', array(
	'a' => 'A', 'b' => 'B', 'c' => 'C'));
$smarty->assign('sqlResult', $sqlResult);
$smarty->assign('arrRules', $priorities);
$smarty->assign('testplan_name', $_SESSION['testPlanName']);  // 20060908 - franciscom
$smarty->display('planPriority.tpl');
?>
