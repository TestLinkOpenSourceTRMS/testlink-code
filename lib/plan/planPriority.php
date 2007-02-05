<?php
/* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: planPriority.php,v 1.10 2007/02/05 08:34:22 franciscom Exp $

This feature allows to define rules for priority dependency 
to importance/risk for actual Test Plan
*/
require('../../config.inc.php');
require_once("../functions/common.php");
require_once("../functions/priority.inc.php");
testlinkInitPage($db);

$tplan_mgr = New Testplan($db);

$sqlResult = null;
if(isset($_POST['updatePriorityRules']))
{
	$sqlResult = $tplan_mgr->set_priority_rules($_SESSION['testPlanId'],$_REQUEST['priority']);
}

$rip_rules = $tplan_mgr->get_priority_rules($_SESSION['testPlanId'],DO_LANG_GET);

$smarty = new TLSmarty();
$smarty->assign('optionPriority',html_option_priorities());
$smarty->assign('sqlResult', $sqlResult);
$smarty->assign('rip_rules', $rip_rules);
$smarty->assign('testplan_name', $_SESSION['testPlanName']);
$smarty->display('planPriority.tpl');
?>

<?php
function html_option_priorities()
{
  $ap=config_get('priority');
  $ret=array();
  
  foreach($ap as $key => $value)
  {
    $ret[$key]=lang_get($value);  
  }
  return($ret);
}  
