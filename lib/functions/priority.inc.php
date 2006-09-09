<?php
/* TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * $Id: priority.inc.php,v 1.10 2006/09/09 07:13:28 franciscom Exp $ 
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
 * @param hash with key  : priority id on priorities table.
 *                  value: priority value
 *        Example:
 *                [priority] => Array
 *                (
 *                 [10] => b
 *                 [11] => b
 *                 [12] => a
 *                 [13] => b
 *                 [14] => b
 *                 [15] => b
 *                 [16] => b
 *                 [17] => b
 *                 [18] => b
 *                )
 *
 *        Important: priority ID is system wide, can not be found in more
 *                   than one test plan. 
 *                   That's the reason we are not passing the test plan id
 *                   to this function.
 *
 *      
 * @return string 'ok'
 * @todo return could depend on sql result
 *
 * 20060908 - franciscom - interface changes
 */
function setPriority(&$db,$priority_hash)
{
	foreach($priority_hash as $priID => $priority)
	{
		$sql = "SELECT id, priority FROM priorities WHERE id = " . $priID;
		$oldPriority = $db->fetchFirstRowSingleColumn($sql,'priority');
		if($oldPriority != null && $oldPriority != $priority)
		{ 
			$sql = "UPDATE priorities SET priority ='" . $priority . "' WHERE id = " . $priID;
			$result = $db->exec_query($sql);
		}
	}
	return 'ok';
}
?>