<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 * 
 * @package     TestLink
 * @author      Martin Havlat
 * @copyright   2007-2014, TestLink community 
 * @filesource  testPlanUrgency.class.php
 * @link        http://www.testlink.org
 *
 * @internal revisions
 * @since 1.9.13
 */ 

/** 
 * Class testPlanUrgency extends testPlan functionality by Test Urgency functions 
 * - modify and list Test Urgency
 * 
 * @package TestLink
 * @author  Martin Havlat
 * @since   1.8 - 17.7.2008
 */
class testPlanUrgency extends testplan
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
    $sql = " UPDATE {$this->tables['testplan_tcversions']} SET urgency={$urgency} " .
           " WHERE testplan_id=" . $this->db->prepare_int($testplan_id) .
           " AND tcversion_id=" . $this->db->prepare_int($tc_id);
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
   *  ' AND NHB.parent_id=' . $node_id; 
   */ 
  public function setSuiteUrgency($testplan_id, $node_id, $urgency)
  {
    $sql = " UPDATE {$this->tables['testplan_tcversions']} " . 
           " SET urgency=" . $this->db->prepare_int($urgency) .
           " WHERE testplan_id= " . $this->db->prepare_int($testplan_id) .
           " AND tcversion_id IN (" .
           " SELECT NHB.id " . 
           " FROM {$this->tables['nodes_hierarchy']}  NHA, " .
           " {$this->tables['nodes_hierarchy']} NHB, {$this->tables['node_types']} NT " .
           " WHERE NHA.node_type_id = NT.id " .
           " AND NT.description='testcase' " . 
           " AND NHB.parent_id = NHA.id " . 
           " AND NHA.parent_id = " . $this->db->prepare_int($node_id) . " )";

    $result = $this->db->exec_query($sql);
    return $result ? OK : ERROR;;
  }
  
  /**
   * Collect urgency for a Test Suite within a Test Plan
   * 
   * @used-by planUrgency.php
   *
   *
   * @param integer $testplan_id Test Plan ID
   * @param integer $node_id Test Suite 
   * @param integer $testproject_id
   *
   * @return array of array testcase_id, name, urgency, tcprefix, tc_external_id 
   * 
   * @internal revisions
   */
  public function getSuiteUrgency($context,$options=null,$filters=null)
  {

    $node_id = intval($context->tsuite_id); 
    $testplan_id = intval($context->tplan_id);
    $platform_id = property_exists($context, 'platform_id') ? intval($context->platform_id) : 0;
    $testproject_id = property_exists($context, 'tproject_id') ? intval($context->tproject_id) : null;

    $testcase_cfg = config_get('testcase_cfg');  
    $moreFields = '';
    $moreJoins = '';

    $my['options'] = array('build4testers' => 0);
    $my['options'] = array_merge($my['options'], (array)$options);

    $my['filters'] = array('testcases' => null);
    $my['filters'] = array_merge($my['filters'], (array)$filters);

    if( $my['options']['build4testers'] != 0 )
    {
      $tasks = $this->assignment_types;

      // ATTENTION:
      // Remember that test case execution task can be assigned to MULTIPLE USERS
      $moreFields = ',USERS.login AS assigned_to, USERS.first, USERS.last ';

      $moreJoins = " LEFT JOIN {$this->tables['user_assignments']} UA " .
                    " ON UA.feature_id = TPTCV.id " .
                    " AND UA.type = " . $tasks['testcase_execution']['id'] .
                    " AND UA.build_id = " . $my['options']['build4testers'] .
                    " LEFT JOIN {$this->tables['users']} USERS " .
                    " ON USERS.id = UA.user_id ";
    }     



    $sql = " SELECT testprojects.prefix  FROM {$this->tables['testprojects']} testprojects " .
           " WHERE testprojects.id = ";
    
    if( !is_null($testproject_id) )
    {
      $sql .= intval($testproject_id);  
    }      
    else
    {
      $sql .= "( SELECT parent_id AS testproject_id FROM {$this->tables['nodes_hierarchy']} " .
              "  WHERE id=" . intval($testplan_id) . " ) ";
    }
    
    $tcprefix = $this->db->fetchOneValue($sql) . $testcase_cfg->glue_character;
    $tcprefix = $this->db->prepare_string($tcprefix);
    
    $sql = " SELECT DISTINCT '{$tcprefix}' AS tcprefix, NHB.name, NHB.node_order," .
           " NHA.parent_id AS testcase_id, TCV.tc_external_id, TPTCV.tcversion_id,".
           " TPTCV.urgency, TCV.importance, (TCV.importance * TPTCV.urgency) AS priority" .
           $moreFields .
           " FROM {$this->tables['nodes_hierarchy']} NHA " .
           " JOIN {$this->tables['nodes_hierarchy']} NHB ON NHA.parent_id = NHB.id " .
           " JOIN {$this->tables['testplan_tcversions']} TPTCV " .
           " ON TPTCV.tcversion_id=NHA.id " .
           " JOIN {$this->tables['tcversions']}  TCV ON TCV.id = TPTCV.tcversion_id " .
           $moreJoins;

    $sql .= " WHERE TPTCV.testplan_id=" . $this->db->prepare_int($testplan_id) .
            " AND NHB.parent_id=" . $this->db->prepare_int($node_id);

    if($platform_id > 0)
    {
      $sql .= " AND TPTCV.platform_id=" . $this->db->prepare_int($platform_id);
    }        

    if( !is_null($my['filters']['testcases']) )
    {
      // sanitize
      $loop2do = count($my['filters']['testcases']);
      for($gdx=0; $gdx < $loop2do; $gdx++)
      {
        $my['filters']['testcases'][$gdx] = intval($my['filters']['testcases'][$gdx]);
      }  
      $sql .= " AND NHB.id IN (" . implode(",", $my['filters']['testcases']) . ") ";
    }  

    $sql .= " ORDER BY NHB.node_order";

    return $this->db->fetchRowsIntoMap($sql,'tcversion_id',database::CUMULATIVE);
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
    $sql .= " SELECT (urgency * importance) AS priority,  " .
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
            $rs[$key]['priority_level'] = priority_to_level($rs[$key]['priority']);
          }
        break;

        case 'platform':
          foreach($key2loop as  $key)
          {
            $platformSet = array_keys($rs[$key]);
            foreach($platformSet as $platform_id) 
            {
              $rs[$key][$platform_id]['priority_level'] = priority_to_level($rs[$key][$platform_id]['priority']);
            }
          }
        break;
      } // switch
    } // !is_null

    return $rs;
  } 
  
} // end of class