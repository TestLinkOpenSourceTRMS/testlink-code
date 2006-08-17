<?php
/* TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * $Id: priority.inc.php,v 1.9 2006/08/17 19:29:59 schlundus Exp $ 
 *
 * Functions for Priority management 
 *
*/
require_once('../../config.inc.php');
require_once("../functions/common.php");

/**
 * Collect information about rules for priority within actual Plan
 * 
 * @return array of array: id, priority, name of item 
 */
function getPriority(&$db,$tpID)
{
	$arrData = array();
	
	$sql = " SELECT id, risk_importance, priority " .
	       " FROM priorities WHERE testplan_id = " . $tpID;

	return $db->get_recordset($sql);
}


/**
 * Set rules for priority within actual Plan
 *
 * @param array $newArray $_POST input converted to simple numbered array
 * @return string 'ok'
 * @todo return could depend on sql result
 */
function setPriority(&$db,$newArray)
{
	$i = 0;
	$len = count($newArray) - 1;
	while ($i < $len)
	{
		$priID = intval($newArray[$i]); //Then the first value is the ID
		$priority = $newArray[$i+1]; //The second value is the priority
		
		$sql = "SELECT id, priority FROM priorities WHERE id = " . $priID;
		
		$oldPriority = $db->fetchFirstRowSingleColumn($sql,'priority');
		if($oldPriority != null && $oldPriority != $priority)
		{ 
			$sql = "UPDATE priorities SET priority ='" . $priority . "' WHERE id = " . $priID;
			$result = $db->exec_query($sql);
		}
		$i = $i + 2;
	}
	return 'ok';
}
?>