<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 * 
 * @filesource $RCSfile: priority.class.php,v $
 * @version $Revision: 1.6 $
 * @modified $Date: 2009/06/06 17:51:40 $ by $Author: franciscom $
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
    $sql="UPDATE {$this->testplan_tcversions_table} SET urgency={$urgency} " .
         "WHERE testplan_id={$testplan_id} AND tcversion_id={$tc_id}";
	$result = $this->db->exec_query($sql);
    $retval=$result ? OK : ERROR;
	return $retval;
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
	   $sql = " UPDATE {$this->testplan_tcversions_table} " . 
            " SET urgency={$urgency} ".
            " WHERE testplan_id= {$testplan_id} " .
            " AND tcversion_id IN (" .
            " SELECT NHB.id " . 
            " FROM {$this->nodes_hierarchy_table}  NHA, "
            " {$this->nodes_hierarchy_table} NHB, {$this->node_types_table}  NT" .
            " WHERE NHA.node_type_id = NT.id " .
            " AND NT.description='testcase' " . 
            " AND NHB.parent_id = NHA.id " . 
            " AND NHA.parent_id = {$node_id} )";
	$result = $this->db->exec_query($sql);

    $retval=$result ? OK : ERROR;
	return $retval;
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
 	
 	
 	$sql = " SELECT testprojects.prefix  FROM {$this->testprojects_table} testprojects " .
  	       " WHERE testprojects.id = ";
  	
    
  if( !is_null($testproject_id) )
  {
      $sql .= $testproject_id;  
  }	     
  else
  {
 	    $sql .= "( SELECT parent_id AS testproject_id FROM {$this->nodes_hierarchy_table} " .
                " WHERE id={$testplan_id} ) ";
	}
	
	$tcprefix = $this->db->fetchOneValue($sql) . $testcase_cfg->glue_character;
	$tcprefix = $this->db->prepare_string($tcprefix);
	
	$sql = " SELECT DISTINCT '{$tcprefix}' AS tcprefix, NHB.name, NHB.node_order," .
	       " NHA.parent_id AS testcase_id, TCV.tc_external_id, testplan_tcversions.urgency".
           " FROM {$this->nodes_hierarchy_table} NHA " .
           " JOIN {$this->nodes_hierarchy_table} NHB ON NHA.parent_id = NHB.id " .
		     " JOIN {$this->testplan_tcversions_table} testplan_tcversions " .
		     " ON testplan_tcversions.tcversion_id=NHA.id " .
		     " JOIN {$this->tcversions_table}  TCV ON TCV.id = testplan_tcversions.tcversion_id " .
		     " WHERE testplan_tcversions.testplan_id={$testplan_id}" .
	 	     " AND NHB.parent_id={$node_id}" . 
		     " ORDER BY NHB.node_order";

	return $this->db->get_recordset($sql);
}
} // end of class
?>