<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: resultsBugs.php,v 1.7 2006/10/24 20:35:02 schlundus Exp $ 
*
* @author	Martin Havlat <havlat@users.sourceforge.net>
* 
* This page show Bug Report.
*
*/
require('../../config.inc.php');
require_once('common.php');
require_once('results.inc.php');
require_once("../../lib/functions/lang_api.php");
testlinkInitPage($db);

$tpName = $_SESSION['testPlanName'];
$tp = new testplan($db);
$tpID = isset($_SESSION['testPlanId']) ?  $_SESSION['testPlanId'] : 0;
$tcs = $tp->get_linked_tcversions($tpID,null,0,1);

$query = "SELECT NHB.id tcid, NHC.id tsid, execution_ts,bug_id,NHB.name tcname,NHC.name tsname ".
		 "FROM executions e JOIN execution_bugs eb ON e.id = eb.execution_id ".
		 "JOIN nodes_hierarchy NHA ON e.tcversion_id = NHA.id ".
		 "JOIN nodes_hierarchy NHB ON NHA.parent_id = NHB.id  ".
		 "JOIN nodes_hierarchy NHC ON NHB.parent_id = NHC.id  ".
		 "WHERE testplan_id = {$tpID} ".
		 "ORDER BY NHC.node_order ASC,NHB.node_order ASC, execution_ts DESC";

		 
$result = $db->fetchRowsIntoMap($query,"tsid",1);
$tsInfos = array();
$dummy = null;
foreach($result as $tsID => $tcInfos)
{
	$tmpTcID = 0;
	
	$tsInfo = array(	"name" => "", 
						"tcInfo" => array()
					);
	$tsTCInfo = array(
						"tcName" => '',
						"executions" => array(),
					 );
	for($i = 0;$i < sizeof($tcInfos);$i++)
	{
		$tc = $tcInfos[$i];
		$currentTcID = $tc['tcid'];
		if ($i == 0)
		{
			$tsInfo['name'] = $tc['tsname'];
			$tmpTcID = $currentTcID;
			$tsTCInfo['tcName'] = $tc['tcname'];
		}
		if ($tmpTcID != $currentTcID)
		{
			$tsInfo['tcInfo'][$tmpTcID] = $tsTCInfo;
			$tmpTcID = $currentTcID;
			$tsTCInfo = array(
							  "tcName" => $tc['tcname'],
							  "executions" => array(),
							  );
		}
		$ts = localize_dateOrTimeStamp(null,$dummy,'timestamp_format',$tc['execution_ts']);
		$tsTCInfo["executions"][$ts][] = $g_bugInterface->buildViewBugLink($tc['bug_id'],1);
	}
	$tsInfo['tcInfo'][$tmpTcID] = $tsTCInfo;	
	$tsInfos[] = $tsInfo;
}


$smarty = new TLSmarty();
$smarty->assign('tpName', $tpName);
$smarty->assign('arrData', $tsInfos);
$smarty->display('resultsBugs.tpl');
?>