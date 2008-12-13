<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 * 
 * @filesource $RCSfile: priority.class.php,v $
 * @version $Revision: 1.5 $
 * @modified $Date: 2008/12/13 08:37:57 $ by $Author: franciscom $
 * 
 * @copyright Copyright (c) 2008, TestLink community
 * @author Martin Havlat
 * 
 * Class testPlanUrgency extends testPlan functionality by Test Urgency functions 
 *
 * Revision: 20081212 - BUGID 1922 - franciscom 
 *           20080901 - franciscom - getSuiteUrgency() - changes in return data 
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
    /* 20081212 - franciscom
       Postgres do not like this syntax
       
    $sql = 'UPDATE testplan_tcversions ' . 
           ' JOIN nodes_hierarchy NHA ON testplan_tcversions.tcversion_id = NHA.id '.
           ' JOIN nodes_hierarchy NHB ON NHA.parent_id = NHB.id' .
           ' SET urgency=' . $urgency .
		       ' WHERE testplan_tcversions.testplan_id=' . $testplan_id .
	 	       ' AND NHB.parent_id=' .	$node_id; 
	 	*/
	   $sql = " UPDATE testplan_tcversions " . 
            " SET urgency={$urgency} ".
            " WHERE testplan_id= {$testplan_id} " .
            " AND tcversion_id IN (" .
            " SELECT NHB.id " . 
            " FROM nodes_hierarchy NHA, nodes_hierarchy NHB, node_types NT" .
            " WHERE NHA.node_type_id = NT.id " .
            " AND NT.description='testcase' " . 
            " AND NHB.parent_id = NHA.id " . 
            " AND NHA.parent_id = {$node_id} )";
	$result = $this->db->exec_query($sql);

	if ($result)
		return OK;
	else
		return ERROR;
}

/**
 * Collect urgency for a Test Suite within a Test Plan
 * 
 * node_id: testsuite id
 *
 * @return array of array: testcase_id, name, urgency, tcprefix, tc_external_id 
 *
 * rev: 20081210 - franciscom - added testproject_id argument to avoid
 *                              subquery when testproject_id is available
 *
 *      20080901 - franciscom - added tcprefix, tc_external_id  in return data
 */
function getSuiteUrgency($testplan_id, $node_id, $testproject_id=null)
{
	$testcase_cfg = config_get('testcase_cfg');  
 	
 	$sql = " SELECT testprojects.prefix ".
  	     " FROM testprojects " .
  	     " WHERE testprojects.id = ";
  	     
  if( !is_null($testproject_id) )
  {
      $sql .= $testproject_id;  
  }	     
  else
  {
 	    $sql .= "( SELECT parent_id AS testproject_id FROM nodes_hierarchy " .
              " WHERE id={$testplan_id} ) ";
	}
	
	$tcprefix = $this->db->fetchOneValue($sql) . $testcase_cfg->glue_character;
	$tcprefix = $this->db->prepare_string($tcprefix);
	
	$sql = " SELECT DISTINCT '{$tcprefix}' AS tcprefix, NHB.name, NHB.node_order," .
	       " NHA.parent_id AS testcase_id, TCV.tc_external_id, testplan_tcversions.urgency".
         " FROM nodes_hierarchy NHA " .
         " JOIN nodes_hierarchy NHB ON NHA.parent_id = NHB.id " .
		     " JOIN testplan_tcversions ON testplan_tcversions.tcversion_id=NHA.id " .
		     " JOIN tcversions TCV ON TCV.id = testplan_tcversions.tcversion_id " .
		     " WHERE testplan_tcversions.testplan_id={$testplan_id}" .
	 	     " AND NHB.parent_id={$node_id}" . 
		     " ORDER BY NHB.node_order";

	return $this->db->get_recordset($sql);
}


} // end of class
?>