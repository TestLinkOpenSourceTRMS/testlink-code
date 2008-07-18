<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 * 
 * @filesource $RCSfile: priority.inc.php,v $
 * @version $Revision: 1.12 $
 * @modified $Date: 2008/07/18 14:26:23 $ by $Author: havlat $
 * 
 * @copyright Copyright (c) 2008, TestLink community
 * @author Martin Havlat
 * 
 * Class testPlanUrgency extends testPlan functionality by Test Urgency functions 
 *
 * Revision:
 *  None.
 *
 * ------------------------------------------------------------------------------------ */

require_once('testplan.class.php');

/** 
 * class testPlanUrgency - modify and list Test Urgency
 * @since 1.8 - 17.7.2008
 */
class testPlanUrgency extends testPlan
{


public function setTestUrgency($testplan_id, $tc_id, $urgency)
{
    $sql="UPDATE testplan_tcversions SET urgency={$urgency} " .
           "WHERE testplan_id={$testplan_id} AND tcversion_id={$tc_id}";
	$result = $this->db->exec_query($sql);

	if ($result)
		return OK;
	else
		return ERROR;
}

/**
 * Set urgency for TCs (direct child only) within a Test Suite and Test Plan
 */	
public function setSuiteUrgency($testplan_id, $node_id, $urgency)
{
    $sql='UPDATE testplan_tcversions ' . 
        ' JOIN nodes_hierarchy NHA ON testplan_tcversions.tcversion_id = NHA.id '.
        ' JOIN nodes_hierarchy NHB ON NHA.parent_id = NHB.id' .
        ' SET urgency=' . $urgency .
		' WHERE testplan_tcversions.testplan_id=' . $testplan_id .
	 	' AND NHB.parent_id=' .	$node_id; 
	$result = $this->db->exec_query($sql);

	if ($result)
		return OK;
	else
		return ERROR;
}

/**
 * Collect urgency for a Test Suite within a Test Plan
 * 
 * @return array of array: testcase_id, name, urgency 
 */
function getSuiteUrgency($testplan_id, $node_id)
{
	$sql = 'SELECT NHB.name, NHA.parent_id AS testcase_id, testplan_tcversions.urgency
         FROM nodes_hierarchy NHA
         JOIN nodes_hierarchy NHB ON NHA.parent_id = NHB.id
		 JOIN testplan_tcversions ON testplan_tcversions.tcversion_id=NHA.id ' .
		' WHERE testplan_tcversions.testplan_id=' . $testplan_id .
	 	' AND NHB.parent_id=' .	$node_id . 
		' ORDER BY NHB.node_order';

	return $this->db->get_recordset($sql);
}


} // end of class
?>