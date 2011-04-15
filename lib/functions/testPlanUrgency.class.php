<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 * 
 * @package 	TestLink
 * @author 		Martin Havlat
 * @copyright 	2007-2009, TestLink community 
 * @version    	CVS: $Id: testPlanUrgency.class.php,v 1.4 2010/02/11 18:49:58 franciscom Exp $
 * @link 		http://www.teamst.org/index.php
 *
 * @internal Revisions:
 *  20110415 - Julian - BUGID 4418 - Clean up priority usage within Testlink
 *                    - BUGID 4419 - Added priority to output of getSuiteUrgency()
 *	20100204 - eloff - fixed typo error $break => break
 *	20091017 - franciscom - moved method getPriority() from result.class.php
 *	20081212 - BUGID 1922 - franciscom 
 *	20080901 - franciscom - getSuiteUrgency() - changes in return data 
 *
 */ 

/** parent class */
require_once('testplan.class.php');

/** 
 * Class testPlanUrgency extends testPlan functionality by Test Urgency functions 
 * - modify and list Test Urgency
 * 
 * @package TestLink
 * @author 	Martin Havlat
 * @since 	1.8 - 17.7.2008
 */
class testPlanUrgency extends testPlan
{
	/**
	 * Set Test urgency for test case version in a Test Plan
	 * 
	 * @param integer $testplan_id Test Plan ID
	 * @param integer $tc_id Test Case version to set Urgency
	 * @param integer $urgency
	 * 
	 * @return integer result code
	 */
	public function setTestUrgency($testplan_id, $tc_id, $urgency)
	{
		$sql = "UPDATE {$this->tables['testplan_tcversions']} SET urgency={$urgency} " .
			   "WHERE testplan_id={$testplan_id} AND tcversion_id={$tc_id}";
		$result = $this->db->exec_query($sql);

		return $result ? tl::OK : tl::ERROR;
	}

	/**
	 * Set urgency for TCs (direct child only) within a Test Suite and Test Plan
	 * 
	 * @param integer $testplan_id Test Plan ID
	 * @param integer $node_id Test Suite to set Urgency
	 * @param integer $urgency
	 * 
	 * @return integer result code
	 * 
	 * @internal 
	 * 20081212 - franciscom - Postgres do not like SQL syntax with JOIN
	 *  $sql = 'UPDATE testplan_tcversions ' .
	 *  ' JOIN nodes_hierarchy NHA ON testplan_tcversions.tcversion_id = NHA.id '.
	 *  ' JOIN nodes_hierarchy NHB ON NHA.parent_id = NHB.id' .
	 *  ' SET urgency=' . $urgency .
	 *  ' WHERE testplan_tcversions.testplan_id=' . $testplan_id .
	 *  ' AND NHB.parent_id=' .	$node_id; 
	 */	
	public function setSuiteUrgency($testplan_id, $node_id, $urgency)
	{
		$sql = " UPDATE {$this->tables['testplan_tcversions']} " . 
			   " SET urgency={$urgency} ".
			   " WHERE testplan_id= {$testplan_id} " .
			   " AND tcversion_id IN (" .
			   " SELECT NHB.id " . 
			   " FROM {$this->tables['nodes_hierarchy']}  NHA, " .
			   " {$this->tables['nodes_hierarchy']} NHB, {$this->tables['node_types']} NT " .
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
	 * @param integer $testplan_id Test Plan ID
	 * @param integer $node_id Test Suite 
	 * @param integer $testproject_id
	 *
	 * @return array of array testcase_id, name, urgency, tcprefix, tc_external_id 
	 * 
	 * @internal Revisions:
	 *  20090616 - Eloff - added tcversion id in return data
	 * 	20081210 - franciscom - added testproject_id argument to avoid
	 *                              subquery when testproject_id is available
	 *	20080901 - franciscom - added tcprefix, tc_external_id  in return data
	 */
	public function getSuiteUrgency($testplan_id, $node_id, $testproject_id=null)
	{
		$testcase_cfg = config_get('testcase_cfg');  
		
		$sql = " SELECT testprojects.prefix  FROM {$this->tables['testprojects']} testprojects " .
			   " WHERE testprojects.id = ";
		
		if( !is_null($testproject_id) )
		{
			$sql .= $testproject_id;  
		}	     
		else
		{
			$sql .= "( SELECT parent_id AS testproject_id FROM {$this->tables['nodes_hierarchy']} " .
				    " WHERE id={$testplan_id} ) ";
		}
		
		$tcprefix = $this->db->fetchOneValue($sql) . $testcase_cfg->glue_character;
		$tcprefix = $this->db->prepare_string($tcprefix);
		
		$sql = " SELECT DISTINCT '{$tcprefix}' AS tcprefix, NHB.name, NHB.node_order," .
			   " NHA.parent_id AS testcase_id, TCV.tc_external_id, testplan_tcversions.tcversion_id,".
			   " testplan_tcversions.urgency, TCV.importance, (TCV.importance * testplan_tcversions.urgency) AS priority" .
			   " FROM {$this->tables['nodes_hierarchy']} NHA " .
			   " JOIN {$this->tables['nodes_hierarchy']} NHB ON NHA.parent_id = NHB.id " .
			   " JOIN {$this->tables['testplan_tcversions']} testplan_tcversions " .
			   " ON testplan_tcversions.tcversion_id=NHA.id " .
			   " JOIN {$this->tables['tcversions']}  TCV ON TCV.id = testplan_tcversions.tcversion_id " .
			   " WHERE testplan_tcversions.testplan_id={$testplan_id}" .
			   " AND NHB.parent_id={$node_id}" . 
			   " ORDER BY NHB.node_order";
		
		return $this->db->get_recordset($sql);
	}
	
    /**
	 * Returns priority (urgency * importance) as HIGH, MEDUIM or LOW depending on value
     * 
     *
	 * @param integer $testplan_id Test Plan ID
     * @param  $filters: optional, map with following keys
     * @param  $options: optional, map with following keys
     *
	 * @return 
	 */
	public function getPriority($testplan_id, $filters=null, $options=null)
	{
		
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
		$rs = null;
        $my = array ('filters' => array('platform_id' => null, 'tcversion_id' =>null), 
                     'options' => array('details' => 'tcversion'));
		$my['filters'] = array_merge($my['filters'], (array)$filters);
		$my['options'] = array_merge($my['options'], (array)$options);

        $sqlFilter = '';
		if( !is_null($my['filters']['platform_id']) )
		{
			$sqlFilter .= " AND TPTCV.platform_id = {$my['filters']['platform_id']} ";
	    }

		if( !is_null($my['filters']['tcversion_id']) )
		{
		    $dummy = implode(',',(array)$my['filters']['tcversion_id']);
        	$sqlFilter .= " AND TPTCV.tcversion_id IN ({$dummy}) ";
	    }
        
		$sql = "/* $debugMsg */ ";
		$sql .=	" SELECT (urgency * importance) AS priority,  " .
		        " urgency,importance, " .
		        LOW . " AS priority_level, TPTCV.tcversion_id %CLAUSE%" .
		        " FROM {$this->tables['testplan_tcversions']} TPTCV " .
			    " JOIN {$this->tables['tcversions']} TCV ON TPTCV.tcversion_id = TCV.id " .
			    " WHERE TPTCV.testplan_id = {$testplan_id} {$sqlFilter}";
			    
		switch($my['options']['details'])
		{
			case 'tcversion':
			    $sql = str_ireplace("%CLAUSE%", "", $sql);
				$rs = $this->db->fetchRowsIntoMap($sql,'tcversion_id');
			break;

			case 'platform':
			    $sql = str_ireplace("%CLAUSE%", ", TPTCV.platform_id", $sql);
 				$rs = $this->db->fetchMapRowsIntoMap($sql,'tcversion_id','platform_id');
			break;
		} 	    
		
		if( !is_null($rs) )
		{
			$key2loop = array_keys($rs);
		    switch($my['options']['details'])
		    {
				case 'tcversion':
					foreach($key2loop as $key)
					{
						//BUGID 4418 - clean up priority usage
						$rs[$key]['priority_level'] = priority_to_level($rs[$key]['priority']);
					}
				break;

				case 'platform':
					foreach($key2loop as  $key)
					{
						$platformSet = array_keys($rs[$key]);
						foreach($platformSet as $platform_id) 
						{
							//BUGID 4418 - clean up priority usage
							$rs[$key][$platform_id]['priority_level'] = priority_to_level($rs[$key][$platform_id]['priority']);
						}
					}
				break;
		    } // switch
		} // !is_null

    	return $rs;
	} // end public function getPriority
	
} // end of class
?>
