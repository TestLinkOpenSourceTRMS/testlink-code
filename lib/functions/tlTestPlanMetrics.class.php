<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource  tlTestPlanMetrics.class.php
 * @package     TestLink
 * @author      Kevin Levy, franciscom
 * @copyright   2004-2015, TestLink community 
 * @link        http://testlink.sourceforge.net/
 * @uses        config.inc.php 
 * @uses        common.php 
 *
 * @internal revisions
 * @since 1.9.14
 *
 **/


/**
 * This class is encapsulates most functionality necessary to query the database
 * for results to publish in reports.  
 * It returns data structures to the gui layer in a manner that are easy to display 
 * in smarty templates.
 * 
 * @package TestLink
 * @author kevinlevy
 */
class tlTestPlanMetrics extends testplan
{
  /** @var resource references passed in by constructor */
  var  $db = null;

  /** @var object class references passed in by constructor */
  private $tplanMgr = null;
  private $testPlanID = -1;
  private  $tprojectID = -1;
  private  $testCasePrefix='';

  private $priorityLevelsCfg='';
  private $map_tc_status;
  private $tc_status_for_statistics;
  
  private $statusCode;
  

  /** 
   * class constructor 
   * @param resource &$db reference to database handler
   **/    
  function __construct(&$db)
  {
    $this->resultsCfg = config_get('results');
    $this->testCaseCfg = config_get('testcase_cfg');

    $this->db = $db;
    parent::__construct($db);

    $this->map_tc_status = $this->resultsCfg['status_code'];
    
    // This will be used to create dynamically counters if user add new status
    foreach( $this->resultsCfg['status_label_for_exec_ui'] as $tc_status_verbose => $label)
    {
        $this->tc_status_for_statistics[$tc_status_verbose] = $this->map_tc_status[$tc_status_verbose];
    }
    if( !isset($this->resultsCfg['status_label_for_exec_ui']['not_run']) )
    {
        $this->tc_status_for_statistics['not_run'] = $this->map_tc_status['not_run'];  
    }
    // $this->notRunStatusCode = $this->tc_status_for_statistics['not_run'];
      
    $this->statusCode = array_flip(array_keys($this->resultsCfg['status_label_for_exec_ui']));
    foreach($this->statusCode as $key => $dummy)
    {
      $this->statusCode[$key] = $this->resultsCfg['status_code'][$key];  
    }
      
      // $this->execTaskCode = intval($this->assignment_types['testcase_execution']['id']);

  } // end results constructor



  public function getStatusConfig() 
  {
    return $this->tc_status_for_statistics;
  }


  /**
   * Function returns prioritized test result counter
   * 
   * @param timestamp $milestoneTargetDate - (optional) milestone deadline
   * @param timestamp $milestoneStartDate - (optional) milestone start date
   * @return array with three priority counters
   */
  public function getPrioritizedResults($tplanID,$milestoneTargetDate = null, $milestoneStartDate = null)
  {
    $output = array (HIGH=>0,MEDIUM=>0,LOW=>0);

    
    for($urgency=1; $urgency <= 3; $urgency++)
    {
      for($importance=1; $importance <= 3; $importance++)
      {  
        $sql = "SELECT COUNT(DISTINCT(TPTCV.id )) " .
          " FROM {$this->tables['testplan_tcversions']} TPTCV " .
          " JOIN {$this->tables['executions']} E ON " .
          " TPTCV.tcversion_id = E.tcversion_id " .
          " JOIN {$this->tables['tcversions']} TCV ON " .
          " TPTCV.tcversion_id = TCV.id " .
          " WHERE TPTCV.testplan_id = {$tplanID} " .
          " AND TPTCV.platform_id = E.platform_id " .
          " AND E.testplan_id = {$tplanID} " .
          " AND NOT E.status = '{$this->map_tc_status['not_run']}' " . 
          " AND TCV.importance={$importance} AND TPTCV.urgency={$urgency}";
        
        // Milestones did not handle start and target date properly
        $end_of_the_day = " 23:59:59";
        $beginning_of_the_day = " 00:00:00";
        
        if( !is_null($milestoneTargetDate) )
        {
          $sql .= " AND execution_ts < '" . $milestoneTargetDate . $end_of_the_day ."'";
        }
        
        if( !is_null($milestoneStartDate) )
        {
          $sql .= " AND execution_ts > '" . $milestoneStartDate . $beginning_of_the_day ."'";
        }
        
        $tmpResult = $this->db->fetchOneValue($sql);

        // parse results into three levels of priority
        $priority = priority_to_level($urgency*$importance);
        $output[$priority] = $output[$priority] + $tmpResult;
      }
    }
    
    return $output;
  }

  /**
   * Function returns prioritized test case counter (in Test Plan)
   * 
   * @return array with three priority counters
   */
  public function getPrioritizedTestCaseCounters($tplanID)
  {
    $output = array (HIGH=>0,MEDIUM=>0,LOW=>0);
    
    /** @TODO - REFACTOR IS OUT OF STANDARD MAGIC NUMBERS */
    for($urgency=1; $urgency <= 3; $urgency++)
    {
      for($importance=1; $importance <= 3; $importance++)
      {  
        // get total count of related TCs
        $sql = "SELECT COUNT( TPTCV.id ) FROM {$this->tables['testplan_tcversions']} TPTCV " .
            " JOIN {$this->tables['tcversions']} TCV ON TPTCV.tcversion_id = TCV.id " .
            " WHERE TPTCV.testplan_id = " . $tplanID .
              " AND TCV.importance={$importance} AND TPTCV.urgency={$urgency}";

        $tmpResult = $this->db->fetchOneValue($sql);
        
        // clean up priority usage
        $priority = priority_to_level($urgency*$importance);
        $output[$priority] = $output[$priority] + $tmpResult;
      }
    }
          
    return $output;
  }


  /**
   * 
   */
  function getMilestonesMetrics($tplanID, $milestoneSet=null)
  {        
    $results = array();

    // get amount of test cases for each execution result + total amount of test cases
    $planMetrics = $this->getExecCountersByExecStatus($tplanID);

    $milestones =  is_null($milestoneSet) ? $this->get_milestones($tplanID) : $milestoneSet;

    // get amount of test cases for each priority for test plan      
    $priorityCounters = $this->getPrioritizedTestCaseCounters($tplanID);
    $pc = array(LOW => 'result_low_percentage', MEDIUM => 'result_medium_percentage',
                HIGH => 'result_high_percentage' );
        
    $checks = array(LOW => 'low_percentage', MEDIUM => 'medium_percentage',
                    HIGH => 'high_percentage' );

    $on_off = array(LOW => 'low_incomplete', MEDIUM => 'medium_incomplete',
                    HIGH => 'high_incomplete' );
        
    // Important:
    // key already defined on item: high_percentage,medium_percentage,low_percentage
    foreach($milestones as $item)
    {
      $item['tcs_priority'] = $priorityCounters;
      $item['tc_total'] = $planMetrics['total'];

      // get amount of executed test cases for each priority before target_date
      $item['results'] = $this->getPrioritizedResults($tplanID, $item['target_date'], $item['start_date']);
      $item['tc_completed'] = 0;
            
      // calculate percentage of executed test cases for each priority
      foreach( $pc as $key => $item_key)
      {
        $target_key = $checks[$key];
        if( $item[$target_key] == 0 )
        {
          $item[$item_key] = 100;
        } 
        else 
        {
          $item[$item_key] = ($priorityCounters[$key] > 0) ? 
                             $this->get_percentage($priorityCounters[$key], $item['results'][$key]) : 0;
        }  
        $item['tc_completed'] += $item['results'][$key];
      }

      // amount of all executed tc with any priority before target_date / all test cases
      $item['percentage_completed'] = $this->get_percentage($item['tc_total'], $item['tc_completed']);

      foreach( $checks as $key => $item_key)
      {
        // add 1 decimal places to expected percentages
        $item[$checks[$key]] = number_format($item[$checks[$key]], 1);
              
        // check if target for each priority is reached
        // show target as reached if expected percentage is greater than executed percentage
        $item[$on_off[$key]] = ($item[$checks[$key]] > $item[$pc[$key]]) ? ON : OFF;
      }
      $results[$item['id']] = $item;
    }
    return $results;
  }
  
  
  /**
   * calculate percentage and format
   * 
   * @param int $total Total count
   * @param int $parameter a parameter count
   * @return string formatted percentage
   */
  function get_percentage($total, $parameter)
  {
    $percentCompleted = ($total > 0) ? (($parameter / $total) * 100) : 100;
    return number_format($percentCompleted,1);
  }



  /**
   * @used-by getOverallBuildStatusForRender()
   *          XML-RPC getExecCountersByBuild()
   *
   *
   * No matter we are trying to calculate metrics for BUILDS,
   * we need to consider execution status at Build and Platform level.
   *
   * Why?
   * Let's review help we provide on GUI:
   *
   * The use of platforms has impact on metrics, because
   * a test case that must be executed for N platforms is considered 
   * as N test cases on metrics.
   *
   * Example: Platform X and Y 
   *
   * Test Case  - Tester Assigned 
   *       TC1                 U1 
   *
   * user U1 has to execute TWO test cases, NOT ONE.    
   * This means that we HAVE to consider execution status ON (BUILD,PLATFORM),
   * but we are not going to display results with BUILD and PLATFORM,
   * but ONLY with BUILD indication. 
   *
   *  opt => array('getOnlyAssigned' => false, 'tprojectID' => 0, 
   *               'getPlatformSet' => false, 'processClosedBuilds' => true);
   *  filters => array('buildSet' => null);
   *
   * @internal revisions
   * @since 1.9.12
   * 20140819 - test case exec assignment to MULTIPLE TESTERS
   *
   */
  function getExecCountersByBuildExecStatus($id, $filters=null, $opt=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $safe_id = intval($id);
    list($my,$builds,$sqlStm) = $this->helperGetExecCounters($safe_id, $filters, $opt);
    

    // This subquery is BETTER than the VIEW, need to understand why
    // Last Executions By Build and Platform (LEBBP)
    $sqlLEBBP =  $sqlStm['LEBBP'];

    $sqlUnionAB  =  "/* {$debugMsg} sqlUnionAB - executions */" . 
                  " SELECT DISTINCT UA.build_id, TPTCV.tcversion_id, TPTCV.platform_id, " . 
                  " COALESCE(E.status,'{$this->notRunStatusCode}') AS status " .
                  " FROM {$this->tables['testplan_tcversions']} TPTCV " .
                  " JOIN {$this->tables['user_assignments']} UA " .
                  " ON UA.feature_id = TPTCV.id " .
                  " AND UA.build_id IN ({$builds->inClause}) AND UA.type = {$this->execTaskCode} " .

                  " /* GO FOR Absolute LATEST exec ID ON BUILD and PLATFORM */ " .
                  " JOIN ({$sqlLEBBP}) AS LEBBP " .
                  " ON  LEBBP.testplan_id = TPTCV.testplan_id " .
                  " AND LEBBP.platform_id = TPTCV.platform_id " .
                  " AND LEBBP.tcversion_id = TPTCV.tcversion_id " .
                  " AND LEBBP.testplan_id = " . $safe_id .

                  " /* Get execution status WRITTEN on DB */ " .
                  " JOIN {$this->tables['executions']} E " .
                  " ON  E.id = LEBBP.id " .
                  " AND E.build_id = UA.build_id " .
      
                  " WHERE TPTCV.testplan_id=" . $safe_id .
                  " AND UA.build_id IN ({$builds->inClause}) ";
      
    $sqlUnionBB  =  "/* {$debugMsg} sqlUnionBB - NOT RUN */" . 
                   " SELECT DISTINCT UA.build_id, TPTCV.tcversion_id, TPTCV.platform_id, " . 
                  " COALESCE(E.status,'{$this->notRunStatusCode}') AS status " .
                  " FROM {$this->tables['testplan_tcversions']} TPTCV " .
                  " JOIN {$this->tables['user_assignments']} UA " .
                  " ON UA.feature_id = TPTCV.id " .
                  " AND UA.build_id IN ({$builds->inClause}) AND UA.type = {$this->execTaskCode} " .

                  " /* Get REALLY NOT RUN => BOTH LE.id AND E.id ON LEFT OUTER see WHERE  */ " .
                  " LEFT OUTER JOIN ({$sqlLEBBP}) AS LEBBP " .
                  " ON  LEBBP.testplan_id = TPTCV.testplan_id " .
                  " AND LEBBP.platform_id = TPTCV.platform_id " .
                  " AND LEBBP.tcversion_id = TPTCV.tcversion_id " .

                  // Without this I've created issue 5272
                  " AND LEBBP.build_id = UA.build_id " .

                  " AND LEBBP.testplan_id = " . $safe_id .
                  " LEFT OUTER JOIN {$this->tables['executions']} E " .
                  " ON  E.tcversion_id = TPTCV.tcversion_id " .
                  " AND E.testplan_id = TPTCV.testplan_id " .
                  " AND E.platform_id = TPTCV.platform_id " .

                  // Without this I've created issue 5272
                  " AND E.build_id = LEBBP.build_id " .

                  " /* FILTER BUILDS in set on target test plan (not alway can be applied) */ " .
                  " WHERE TPTCV.testplan_id=" . $safe_id . 
                  " AND UA.build_id IN ({$builds->inClause}) " .
  
                  " /* Get REALLY NOT RUN => BOTH LE.id AND E.id NULL  */ " .
                  " AND E.id IS NULL AND LEBBP.id IS NULL";
      

    // 20140819 - I've not documented why I've use UNION ALL
    // UNION ALL includes duplicates, but now (@20140819) because I've implemented
    // test case exec assignment to MULTIPLE TESTERS, I need to remove duplicates
    // to avoid wrong exec_qty.
    // My choice was: add DISTINCT to each union piece.
    // May be is a wrong choice, but I need to read and test more to understand  
    $sql =  " /* {$debugMsg} UNION WITH ALL CLAUSE */" .
            " SELECT build_id,status, count(0) AS exec_qty " .
            " FROM ($sqlUnionAB UNION ALL $sqlUnionBB ) AS SQBU " .
            " GROUP BY build_id,status ";
    $exec['with_tester'] = (array)$this->db->fetchMapRowsIntoMap($sql,'build_id','status');              

    // Need to Add info regarding:
    // - Add info for ACTIVE BUILD WITHOUT any execution. ???
    //   Hmm, think about Need to check is this way is better that request DBMS to do it.
    // - Execution status that have not happened
    foreach($exec as &$elem)
    {                             
      $itemSet = array_keys($elem);
      foreach($itemSet as $itemID)
      {
        foreach($this->statusCode as $verbose => $code)
        {
          if(!isset($elem[$itemID][$code]))
          {
            $elem[$itemID][$code] = array('build_id' => $itemID,'status' => $code, 'exec_qty' => 0);      
          }                           
        }
      }
    }
    
    // get total assignments by BUILD ID
    // 20140819 - changes due to test case exec assignment to MULTIPLE TESTERS
    $sql =  "/* $debugMsg */ ".
            " SELECT COUNT(0) AS qty, TT.build_id FROM ( " .
            " SELECT DISTINCT UA.build_id, UA.feature_id " . 
            " FROM {$this->tables['user_assignments']} UA " .
            " JOIN {$this->tables['testplan_tcversions']} TPTCV ON TPTCV.id = UA.feature_id " .
            " WHERE UA. build_id IN ( " . $builds->inClause . " ) " .
            " AND UA.type = {$this->execTaskCode} " . 
            " GROUP BY build_id,feature_id) TT " .
            " GROUP BY build_id ";

    $exec['total'] = (array)$this->db->fetchRowsIntoMap($sql,'build_id');
    $exec['active_builds'] = $builds->infoSet;

    return $exec;
  }
  
                                      
  /**
   *
   * @internal revisions
   *
   * @since 1.9.4
   **/
  function getOverallBuildStatusForRender($id, $totalKey='total_assigned')
  {
    $renderObj = null;
    $code_verbose = $this->getStatusForReports();
    $labels = $this->resultsCfg['status_label'];
      
    $metrics = $this->getExecCountersByBuildExecStatus($id);
    if( !is_null($metrics) )
    {
       $renderObj = new stdClass();

      // Creating item list this way will generate a row also for
      // ACTIVE BUILDS were ALL TEST CASES HAVE NO TESTER ASSIGNMENT
      // $buildList = array_keys($metrics['active_builds']);
      
      // Creating item list this way will generate a row ONLY FOR
      // ACTIVE BUILDS were TEST CASES HAVE TESTER ASSIGNMENT
      $buildList = array_keys($metrics['with_tester']);
      $renderObj->info = array();  
      foreach($buildList as $buildID)
      {
        $totalRun = 0;
        $renderObj->info[$buildID]['build_name'] = $metrics['active_builds'][$buildID]['name'];   
        $renderObj->info[$buildID][$totalKey] = $metrics['total'][$buildID]['qty'];   

        $renderObj->info[$buildID]['details'] = array();
        
        $rf = &$renderObj->info[$buildID]['details'];
        foreach($code_verbose as $statusCode => $statusVerbose)
        {
          $rf[$statusVerbose] = array('qty' => 0, 'percentage' => 0);
          $rf[$statusVerbose]['qty'] = $metrics['with_tester'][$buildID][$statusCode]['exec_qty'];   
          
          if( $renderObj->info[$buildID][$totalKey] > 0 ) 
          {
            $rf[$statusVerbose]['percentage'] = number_format(100 * 
                                               ($rf[$statusVerbose]['qty'] / 
                                                $renderObj->info[$buildID][$totalKey]),1);
          }
          
          $totalRun += $statusVerbose == 'not_run' ? 0 : $rf[$statusVerbose]['qty'];
        }
        $renderObj->info[$buildID]['percentage_completed'] =  number_format(100 * 
                                                             ($totalRun / $renderObj->info[$buildID][$totalKey]),1);
      }
         
      foreach($code_verbose as $human)
      {
        $l10n = isset($labels[$human]) ? lang_get($labels[$human]) : lang_get($human); 
        $renderObj->colDefinition[$human]['qty'] = $l10n;
        $renderObj->colDefinition[$human]['percentage'] = '[%]';
      }
  
    }
    return $renderObj;
  }



  /** 
   * Important Notice about algorithm
   * We are trying to provide WHOLE Test Plan metrics, then BUILD INFO
   * will not be IMPORTANT.
   *
   * In addition, Keywords are attributes used on Test Case specification,
   * for this reason, our choice is that platforms will be ignored
   * for this metrics.
   *
   * Example: Platform X and Y
   * Test Case: TC1 with one Keyword K1
   *
   * we can develop this data in this way
   *
   * Test Case - Platform - Keyword - Build - Exec. ID - Exec. Status
   *       TC1          X        K1     1.0        11         FAILED
   *       TC1          Y        K1     1.0         13         BLOCKED
   *       TC1          X        K1     2.0        16         PASSED
   *       TC1          Y        K1     2.0         15         BLOCKED
   *
   *
   * We have two choices:
   * OPT 1. Platform multiplication
   *
   * consider (as was done on Builds Overall Status) 
   * TC1 as two test cases.
   * If we proceed this way, may be user will be confused, because
   * when searching test case spec according keyword, we are going to
   * find ONLY ONE.
   *
   * OPT 2. IGNORE PLAFORMS
   * Consider only LATEST execution, means we are going to count ONE test case
   * no matter how many Platforms exists on test plan.
   *    
   * Our design choice is on OPT 1
   * 
   */    
  function getExecCountersByKeywordExecStatus($id, $filters=null, $opt=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $safe_id = intval($id);
    list($my,$builds,$sqlStm) = $this->helperGetExecCounters($safe_id, $filters, $opt);
    
    
    // may be too brute force but ...
    if( ($tprojectID = $my['opt']['tprojectID']) == 0 )
    {
      $info = $this->tree_manager->get_node_hierarchy_info($safe_id);
      $tprojectID = $info['parent_id'];
    } 
    $tproject_mgr = new testproject($this->db);
    $keywordSet = $tproject_mgr->get_keywords_map($tprojectID);
    $tproject_mgr = null;
    
    
    // This subquery is BETTER than a VIEW, need to understand why
    // Latest Execution Ignoring Build => Cross Build
    $sqlLEBP = $sqlStm['LEBP'];
    
    // Development Important Notice
    // DISTINCT is needed when you what to get data ONLY FOR test cases with assigned testers,
    // because we are (to make things worst) working on a BUILD SET, not on a SINGLE build,
    // Use of IN clause, will have a NOT wanted multiplier effect on this query.
    //
    // This do not happens with other queries on other metric attributes,
    // be careful before changing other queries.
    // 
    $sqlUnionAK  =  "/* {$debugMsg} sqlUnionAK - executions */" . 
            " SELECT DISTINCT NHTCV.parent_id, TCK.keyword_id, TPTCV.platform_id," .
            " COALESCE(E.status,'{$this->notRunStatusCode}') AS status " .
            " FROM {$this->tables['testplan_tcversions']} TPTCV " .
            
            $sqlStm['getAssignedFeatures'] .

            " /* GO FOR Absolute LATEST exec ID IGNORE BUILD */ " .
            " JOIN ({$sqlLEBP}) AS LEBP " .
            " ON  LEBP.testplan_id = TPTCV.testplan_id " .
            " AND LEBP.platform_id = TPTCV.platform_id " .
            " AND LEBP.tcversion_id = TPTCV.tcversion_id " .
            " AND LEBP.testplan_id = " . $safe_id .

            " /* Get execution status WRITTEN on DB */ " .
            " JOIN {$this->tables['executions']} E " .
            " ON  E.id = LEBP.id " .

            " /* Get ONLY Test case versions that has AT LEAST one Keyword assigned */ ".
            " JOIN {$this->tables['nodes_hierarchy']} NHTCV " .
            " ON NHTCV.id = TPTCV.tcversion_id " .
            " JOIN {$this->tables['testcase_keywords']} TCK " .
            " ON TCK.testcase_id = NHTCV.parent_id " .
          
            " WHERE TPTCV.testplan_id=" . $safe_id .
            $builds->whereAddExec;

    // See Note about DISTINCT, on sqlUnionAK
    $sqlUnionBK  =  "/* {$debugMsg} sqlUnionBK - NOT RUN */" . 
            " SELECT DISTINCT NHTCV.parent_id, TCK.keyword_id, TPTCV.platform_id," .
            " COALESCE(E.status,'{$this->notRunStatusCode}') AS status " .
            " FROM {$this->tables['testplan_tcversions']} TPTCV " .
            
            $sqlStm['getAssignedFeatures'] .

            " /* Get REALLY NOT RUN => BOTH LEBP.id AND E.id ON LEFT OUTER see WHERE  */ " .
            " LEFT OUTER JOIN ({$sqlLEBP}) AS LEBP " .
            " ON  LEBP.testplan_id = TPTCV.testplan_id " .
            " AND LEBP.platform_id = TPTCV.platform_id " .
            " AND LEBP.tcversion_id = TPTCV.tcversion_id " .
            " AND LEBP.testplan_id = " . $safe_id .
            " LEFT OUTER JOIN {$this->tables['executions']} E " .
            " ON  E.tcversion_id = TPTCV.tcversion_id " .
            " AND E.testplan_id = TPTCV.testplan_id " .
            " AND E.platform_id = TPTCV.platform_id " .
            $builds->joinAdd .

            " /* Get ONLY Test case versions that has AT LEAST one Keyword assigned */ ".
            " JOIN {$this->tables['nodes_hierarchy']} NHTCV " .
            " ON NHTCV.id = TPTCV.tcversion_id " .
            " JOIN {$this->tables['testcase_keywords']} TCK " .
            " ON TCK.testcase_id = NHTCV.parent_id " .

            " /* FILTER BUILDS in set on target test plan */ " .
            " WHERE TPTCV.testplan_id=" . $safe_id . 
            $builds->whereAddNotRun .
  
            " /* Get REALLY NOT RUN => BOTH E.id AND LEBP.id  NULL  */ " .
            " AND E.id IS NULL AND LEBP.id IS NULL";

    // Due to PLATFORMS we will have MULTIPLIER EFFECT
    $sql =  " /* {$debugMsg} UNION Without ALL CLAUSE => DISCARD Duplicates */" .
        " SELECT keyword_id,status, count(0) AS exec_qty " .
        " FROM ($sqlUnionAK UNION $sqlUnionBK ) AS SQK " .
        " GROUP BY keyword_id,status ";

    $exec['with_tester'] = (array)$this->db->fetchMapRowsIntoMap($sql,'keyword_id','status');              
    $this->helperCompleteStatusDomain($exec,'keyword_id');
           
    // On next queries:
    // we need to use distinct, because IF NOT we are going to get one record
    // for each build where test case has TESTER ASSIGNMENT
    //
    // $exec['total_assigned'] = null;
    $exec['total'] = null;
    $exec['key4total'] = 'total';
    if( $my['opt']['getOnlyAssigned'] )
    {
      // $exec['key4total'] = 'total_assigned';
      $sql =   "/* $debugMsg */ ".
          " SELECT COUNT(0) AS qty, keyword_id " .
          " FROM " . 
          " ( /* Get test case,keyword pairs */ " .
          "  SELECT DISTINCT NHTCV.parent_id, TCK.keyword_id,TPTCV.platform_id " . 
          "  FROM {$this->tables['user_assignments']} UA " .
          "  JOIN {$this->tables['testplan_tcversions']} TPTCV ON TPTCV.id = UA.feature_id " .
          
          "  /* Get ONLY Test case versions that has AT LEAST one Keyword assigned */ ".
          "  JOIN {$this->tables['nodes_hierarchy']} NHTCV " .
          "  ON NHTCV.id = TPTCV.tcversion_id " .
          "  JOIN {$this->tables['testcase_keywords']} TCK " .
          "  ON TCK.testcase_id = NHTCV.parent_id " .
          "  WHERE UA. build_id IN ( " . $builds->inClause . " ) " .
          "  AND UA.type = {$execCode} ) AS SQK ".
          " GROUP BY keyword_id";
    }
    else
    {
      $sql =   "/* $debugMsg */ ".
          " SELECT COUNT(0) AS qty, keyword_id " .
          " FROM " . 
          " ( /* Get test case,keyword pairs */ " .
          "  SELECT DISTINCT NHTCV.parent_id, TCK.keyword_id,TPTCV.platform_id " . 
          "  FROM {$this->tables['testplan_tcversions']} TPTCV " .
          
          "  /* Get ONLY Test case versions that has AT LEAST one Keyword assigned */ ".
          "  JOIN {$this->tables['nodes_hierarchy']} NHTCV " .
          "  ON NHTCV.id = TPTCV.tcversion_id " .
          "  JOIN {$this->tables['testcase_keywords']} TCK " .
          "  ON TCK.testcase_id = NHTCV.parent_id " .
          "  WHERE TPTCV.testplan_id = " . $safe_id . " ) AS SQK ".
          " GROUP BY keyword_id";
    }  

    $exec[$exec['key4total']] = (array)$this->db->fetchRowsIntoMap($sql,'keyword_id');
    $exec['keywords'] = $keywordSet;

    return $exec;
  }


  /**
   *
   * @internal revisions
   *
   * @since 1.9.4
   * 20120429 - franciscom - 
   */
  function getStatusTotalsByKeywordForRender($id,$filters=null,$opt=null)
  {
    $renderObj = $this->getStatusTotalsByItemForRender($id,'keyword',$filters,$opt);
    return $renderObj;
  }



  /**
   *
   * @internal revisions
   *
   * @since 1.9.4
   * 20120429 - franciscom - 
   */
  function getExecCountersByPlatformExecStatus($id, $filters=null, $opt=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $safe_id = intval($id);  
    list($my,$builds,$sqlStm,$union,$platformSet) = $this->helperBuildSQLExecCounters($id, $filters, $opt);

    $add2key = '';
    $addOnWhere = '';
    $addOnJoin = '';
    if( isset($opt['getOnlyActiveTCVersions']) )
    {
      $add2key='Active';
      $addOnWhere = ' AND TCV.active = 1 '; 
      $addOnJoin = " JOIN {$this->tables['tcversions']} TCV ON TCV.id = TPTCV.tcversion_id ";
    }
    $sqlUnionAP  = $union['exec' . $add2key];  
    $sqlUnionBP  =  $union['not_run' . $add2key];

    $sql =  " /* {$debugMsg} UNION ALL CLAUSE => INCLUDE Duplicates */" .
            " SELECT platform_id,status, count(0) AS exec_qty " .
            " FROM ($sqlUnionAP UNION ALL $sqlUnionBP ) AS SQPL " .
            " GROUP BY platform_id,status ";

    $exec['with_tester'] = (array)$this->db->fetchMapRowsIntoMap($sql,'platform_id','status');              

    $this->helperCompleteStatusDomain($exec,'platform_id');

    // get total test cases by Platform id ON TEST PLAN (With & WITHOUT tester assignment)
    $sql =   "/* $debugMsg */ ".
        " SELECT COUNT(0) AS qty, TPTCV.platform_id " . 
        " FROM {$this->tables['testplan_tcversions']} TPTCV " .
        $addOnJoin .
        " WHERE TPTCV.testplan_id=" . $safe_id . $addOnWhere .
        " GROUP BY platform_id";

    $exec['total'] = (array)$this->db->fetchRowsIntoMap($sql,'platform_id');
    $exec['platforms'] = $platformSet;

    return $exec;
  }



  /**
   *
   * @internal revisions
   *
   * @since 1.9.4
   * 20120429 - franciscom - 
   */
  function getStatusTotalsByPlatformForRender($id,$filters=null,$opt=null)
  {
    $renderObj = $this->getStatusTotalsByItemForRender($id,'platform',$filters,$opt);
    return $renderObj;
  }



  /**
   *
   * If no build set providede, ONLY ACTIVE BUILDS will be considered
   *
   * @internal revisions
   *
   * @since 1.9.4
   *
   */
  function getExecCountersByPriorityExecStatus($id, $filters=null, $opt=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $safe_id = intval($id);
    list($my,$builds,$sqlStm) = $this->helperGetExecCounters($safe_id, $filters, $opt);
  
    
    $sqlLEBP = $sqlStm['LEBP'];

    $sqlUnionA  =  "/* {$debugMsg} sqlUnionA - executions */" . 
                  " SELECT (TPTCV.urgency * TCV.importance) AS urg_imp, " .
                  " TPTCV.tcversion_id, TPTCV.platform_id," .
                  " COALESCE(E.status,'{$this->notRunStatusCode}') AS status " .
                  " FROM {$this->tables['testplan_tcversions']} TPTCV " .
        
                  $sqlStm['getAssignedFeatures'] .
      
                  " /* Get importance  */ ".
                  " JOIN {$this->tables['tcversions']} TCV " .
                  " ON TCV.id = TPTCV.tcversion_id " .
        
                  " /* GO FOR Absolute LATEST exec ID IGNORE BUILD */ " .
                  " JOIN ({$sqlLEBP}) AS LEBP " .
                  " ON  LEBP.testplan_id = TPTCV.testplan_id " .
                  " AND LEBP.platform_id = TPTCV.platform_id " .
                  " AND LEBP.tcversion_id = TPTCV.tcversion_id " .
                  " AND LEBP.testplan_id = " . $safe_id .
  
                  " /* Get execution statuses that CAN BE WRITTEN TO DB */ " .
                  " JOIN {$this->tables['executions']} E " .
                  " ON  E.id = LEBP.id " .
                  
                  // Without this we get duplicates ?? => 20121121 CONFIRMED at least with NOT RUN WE GET DUPS
                  $builds->joinAdd .
      
                  " /* FILTER BUILD Set on target test plan */ " .
                  " WHERE TPTCV.testplan_id=" . $safe_id . 
                  $builds->whereAddExec;
            

    $sqlUnionB  =  "/* {$debugMsg} sqlUnionB - NOT RUN */" . 
                  " SELECT (TPTCV.urgency * TCV.importance) AS urg_imp, " .
                  " TPTCV.tcversion_id, TPTCV.platform_id," .
                  " COALESCE(E.status,'{$this->notRunStatusCode}') AS status " .
                  " FROM {$this->tables['testplan_tcversions']} TPTCV " .
                  
                  $sqlStm['getAssignedFeatures'] .
      
                  " /* Get importance  */ ".
                  " JOIN {$this->tables['tcversions']} TCV " .
                  " ON TCV.id = TPTCV.tcversion_id " .
        
                  " /* Get REALLY NOT RUN => BOTH LE.id AND E.id ON LEFT OUTER see WHERE  */ " .
                  " LEFT OUTER JOIN ({$sqlLEBP}) AS LEBP " .
                  " ON  LEBP.testplan_id = TPTCV.testplan_id " .
                  " AND LEBP.platform_id = TPTCV.platform_id " .
                  " AND LEBP.tcversion_id = TPTCV.tcversion_id " .
                  " AND LEBP.testplan_id = " . intval($id) .
                  " LEFT OUTER JOIN {$this->tables['executions']} E " .
                  " ON  E.tcversion_id = TPTCV.tcversion_id " .
                  " AND E.testplan_id = TPTCV.testplan_id " .
                  " AND E.platform_id = TPTCV.platform_id " .
                  $builds->joinAdd .
      
                  " /* FILTER BUILDS in set on target test plan */ " .
                  " WHERE TPTCV.testplan_id=" . $safe_id . 
                  $builds->whereAddNotRun .
  
                  " /* Get REALLY NOT RUN => BOTH LEBP.id AND E.id NULL  */ " .
                  " AND E.id IS NULL AND LEBP.id IS NULL";
      
    
    // ATTENTION:
    // Each piece of UNION has 3 fields: urg_imp,status, TPTCV.tcversion_id     
    // There is no way we can get more that ONE record with same TUPLE
    // on sqlUionA or sqlUnionB ?.
    // 
    // If we have PLATFORM we are going to get a MULTIPLIER EFFECT
    //
    $sql =  " /* {$debugMsg} UNION WITHOUT ALL => DISCARD Duplicates */" .
            " SELECT count(0) as exec_qty, urg_imp,status " .
            " FROM ($sqlUnionA UNION $sqlUnionB ) AS SU " .
            " GROUP BY urg_imp,status ";
            $rs = $this->db->get_recordset($sql);
  

    // Now we need to get priority LEVEL from (urgency * importance)
    $out = array();
    $totals = array();
    if( !is_null($rs) )
    {
      $priorityCfg = config_get('urgencyImportance');
      $loop2do = count($rs);
      for($jdx=0; $jdx < $loop2do; $jdx++)
      {
        if ($rs[$jdx]['urg_imp'] >= $priorityCfg->threshold['high']) 
        {            
          $rs[$jdx]['priority_level'] = HIGH;
          $hitOn = HIGH;
        } 
        else if( $rs[$jdx]['urg_imp'] < $priorityCfg->threshold['low']) 
        {
          $rs[$jdx]['priority_level'] = LOW;
          $hitOn = LOW;
        }        
        else
        {
          $rs[$jdx]['priority_level'] = MEDIUM;
          $hitOn = MEDIUM;
        }
                                      
                                                     
        // to improve readability                                                       
        $status = $rs[$jdx]['status'];
        if( !isset($out[$hitOn][$status]) )
        {
          $out[$hitOn][$status] = $rs[$jdx];
        }
        else
        {
          $out[$hitOn][$status]['exec_qty'] += $rs[$jdx]['exec_qty'];
        }
        
        if( !isset($totals[$hitOn]) )
        {
          $totals[$hitOn] = array('priority_level' => $hitOn, 'qty' => 0);
        }
        $totals[$hitOn]['qty'] += $rs[$jdx]['exec_qty'];
      }
      $exec['with_tester'] = $out;
      $out = null; 
    }
    
    $this->helperCompleteStatusDomain($exec,'priority_level');
    $exec['total'] = $totals;

    $levels = config_get('urgency');
    foreach($levels['code_label'] as $lc => $lbl)
    {
      $exec['priority_levels'][$lc] = lang_get($lbl);
    }

    return $exec;
  }



  /**
   *
   * @internal revisions
   *
   * @since 1.9.4
   * 20120429 - franciscom - 
   */
  function getStatusTotalsByPriorityForRender($id,$filters=null,$opt=null)
  {
    $renderObj = $this->getStatusTotalsByItemForRender($id,'priority_level',$filters,$opt);
    return $renderObj;
  }


  /**
   *
   * @internal revisions
   *
   * @since 1.9.9
   *
   */
  function getExecCountersByExecStatus($id, $filters=null, $opt=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $safe_id = intval($id);  
    list($my,$builds,$sqlStm,$union,$platformSet) = $this->helperBuildSQLExecCounters($id, $filters, $opt);
    if(count($builds) <= 0 || is_null($builds))
    {
      return null;  // >>---> Bye!
    }  


    // Latest Executions By Platform (LEBP)
    $add2key = '';
    if( isset($opt['getOnlyActiveTCVersions']) )
    {
      $add2key='Active';
    }
    $sqlUnionAP  = $union['exec' . $add2key];  //echo 'QD - <br>' . $sqlUnionAP . '<br>';
    $sqlUnionBP  =  $union['not_run' . $add2key]; //echo 'QD - <br>' . $sqlUnionBP . '<br>';
    
    $sql =  " /* {$debugMsg} UNION ALL CLAUSE => INCLUDE Duplicates */" .
            " SELECT status, count(0) AS exec_qty " .
            " FROM ($sqlUnionAP UNION ALL $sqlUnionBP ) AS SQPL " .
            " GROUP BY status ";

    $dummy = (array)$this->db->fetchRowsIntoMap($sql,'status');              

    $statusCounters = array('total' => 0);
    $codeVerbose = array_flip($this->map_tc_status);
    foreach($dummy as $code => $elem)
    {
      
      $statusCounters['total'] += $elem['exec_qty'];
      $statusCounters[$codeVerbose[$code]] = $elem['exec_qty'];
    } 
    
    return $statusCounters;
  }

  /**
   * @internal revisions
   *
   * @since 1.9.6
   * 20130107 - franciscom - TICKET 5457: Incorrect data in "Report by tester per build"
   */
  function getExecCountersByBuildUAExecStatus($id, $filters=null, $opt=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $safe_id = intval($id);
    list($my,$builds,$sqlStm) = $this->helperGetExecCounters($safe_id, $filters, $opt);

    // Last Executions By Build and Platform (LEBBP)
    // Please remember that Platforms (when exists) has Multiplier effect on test cases
    //
    $sqlLEBBP = $sqlStm['LEBBP'];
    
    // 20120817 - franciscom -
    // I'm not to happy with DISTINCT I've added to do this work.
    // Do not understand why i get multiple identical records
    //
    // 20130107 - Think I've got the issue:
    // was the missing clause     
    // " AND UA.feature_id = TPTCV.id AND UA.build_id = LEBBP.build_id " .
    //
    // 20140128 - do not understand why I've added DISTINT.
    //            without it I can add also get exec times
    $sqlUnionBU  =  "/* {$debugMsg} */" . 
                    " SELECT /* DISTINCT */ UA.user_id, UA.build_id, TPTCV.tcversion_id, TPTCV.platform_id, " .
                    " COALESCE(E.status,'{$this->notRunStatusCode}') AS status, " .
                    " COALESCE(E.execution_duration,0) AS execution_duration " .
                    " FROM {$this->tables['testplan_tcversions']} TPTCV " .
                    " JOIN {$this->tables['user_assignments']} UA " .
                    " ON UA.feature_id = TPTCV.id " .
                    " AND UA.build_id IN ({$builds->inClause}) AND UA.type = {$this->execTaskCode} " .
                    
                    " /* LEFT OUTER in order to get NOT RUN */ " .
                    " LEFT OUTER JOIN ({$sqlLEBBP}) AS LEBBP " .
                    " ON  LEBBP.testplan_id = TPTCV.testplan_id " .
                    " AND LEBBP.platform_id = TPTCV.platform_id " .
                    " AND LEBBP.tcversion_id = TPTCV.tcversion_id " .
                    " AND LEBBP.testplan_id = " . $safe_id .      
                    
                    " /* Without this piece we are including results for features without tester */ ".
                    " AND UA.feature_id = TPTCV.id AND UA.build_id = LEBBP.build_id " .
                    
                    " LEFT OUTER JOIN {$this->tables['executions']} E " .
                    " ON  E.build_id = UA.build_id " .
                    " AND E.testplan_id = TPTCV.testplan_id " .
                    " AND E.platform_id = TPTCV.platform_id " .
                    " AND E.tcversion_id = TPTCV.tcversion_id " .
                    " AND E.id = LEBBP.id " . // TICKET 5192
                    " WHERE TPTCV.testplan_id=" . $safe_id .
                    " AND UA.build_id IN ({$builds->inClause}) " ;
  
    $sql =  " /* {$debugMsg} */" .
            " SELECT user_id, build_id,status, count(0) AS exec_qty, SUM(execution_duration) AS total_time" .
            " FROM ($sqlUnionBU) AS SQBU " .
            " GROUP BY user_id,build_id,status ";
           
    $keyColumns = array('build_id','user_id','status');
    $exec['with_tester'] = (array)$this->db->fetchRowsIntoMap3l($sql,$keyColumns);              

    $totals = array();
    foreach($exec as &$topLevelElem)
    {                             
      $topLevelItemSet = array_keys($topLevelElem);
      foreach($topLevelItemSet as $topLevelItemID)
      {
        $itemSet = array_keys($topLevelElem[$topLevelItemID]);
        foreach($itemSet as $itemID)
        {
          $elem = &$topLevelElem[$topLevelItemID];
          foreach($this->statusCode as $verbose => $code)
          {
            if(!isset($elem[$itemID][$code]))
            {
              $elem[$itemID][$code] = array('build_id' => $topLevelItemID, 'user_id' => $itemID,
                                            'status' => $code, 'exec_qty' => 0, 'total_time' => 0);      
            }                           

            if( !isset($totals[$topLevelItemID][$itemID]) )
            {
              $totals[$topLevelItemID][$itemID] = array('build_id' => $topLevelItemID, 
                                                        'user_id' => $itemID, 'qty' => 0,'total_time' => 0);
            }
            $totals[$topLevelItemID][$itemID]['qty'] += $elem[$itemID][$code]['exec_qty'];
            $totals[$topLevelItemID][$itemID]['total_time'] += $elem[$itemID][$code]['total_time'];
          }
        }
      }
    }
    $exec['total'] = $totals;

    return $exec;
  }



  /**
   * @see resultsByTesterPerBuild.php
   * @internal revisions
   *
   * @since 1.9.6
   */
  function getStatusTotalsByBuildUAForRender($id,$opt=null)
  {
    $my = array('opt' => array('processClosedBuilds' => true));
    $my['opt'] = array_merge($my['opt'],(array)$opt);
    
    $renderObj = null;
    $code_verbose = $this->getStatusForReports();
    $labels = $this->resultsCfg['status_label'];
    $metrics = $this->getExecCountersByBuildUAExecStatus($id,null,$my['opt']);
    
    if( !is_null($metrics) )
    {
      $renderObj = new stdClass();
      $topItemSet = array_keys($metrics['with_tester']);
      $renderObj->info = array();  
      $out = &$renderObj->info;

      $topElem = &$metrics['with_tester'];
      foreach($topItemSet as $topItemID)
      {
        $itemSet = array_keys($topElem[$topItemID]);
        foreach($itemSet as $itemID)
        {
          $elem = &$topElem[$topItemID][$itemID];

          $out[$topItemID][$itemID]['total'] = $metrics['total'][$topItemID][$itemID]['qty'];
          $progress = 0; 
          foreach($code_verbose as $statusCode => $statusVerbose)
          {
            $out[$topItemID][$itemID][$statusVerbose]['count'] = $elem[$statusCode]['exec_qty'];
            $pc = ($elem[$statusCode]['exec_qty'] / $out[$topItemID][$itemID]['total']) * 100;
            $out[$topItemID][$itemID][$statusVerbose]['percentage'] = number_format($pc, 1);

            if($statusVerbose != 'not_run')
            {
              $progress += $elem[$statusCode]['exec_qty'];
            }
          }  
          $progress = ($progress / $out[$topItemID][$itemID]['total']) * 100;
          $out[$topItemID][$itemID]['progress'] = number_format($progress,1); 
          $out[$topItemID][$itemID]['total_time'] = 
              number_format($metrics['total'][$topItemID][$itemID]['total_time'],2,'.',''); 
        }
      }
    }
    return $renderObj;
  }


  /**
   *
   * @internal revisions
   *
   * @since 1.9.4
   * 20120429 - franciscom - 
   */
  function getStatusTotalsByItemForRender($id,$itemType,$filters=null,$opt=null)
  {
    $renderObj = null;
    $code_verbose = $this->getStatusForReports();
    $labels = $this->resultsCfg['status_label'];

    
    $returnArray = false;
    switch($itemType)
    {  
      case 'keyword':    
        $metrics = $this->getExecCountersByKeywordExecStatus($id,$filters,$opt);
        $setKey = 'keywords';
      break;

      case 'platform':    
        $myOpt = array_merge(array('getPlatformSet' => true),(array)$opt);
        $metrics = $this->getExecCountersByPlatformExecStatus($id,$filters,$myOpt);
        $setKey = 'platforms';
      break;
      
      case 'priority_level':    
        $metrics = $this->getExecCountersByPriorityExecStatus($id,$filters,$opt);
        $setKey = 'priority_levels';
      break;
      
      case 'tsuite':    
        $metrics = $this->getExecCountersByTestSuiteExecStatus($id,$filters,$opt);
        $setKey = 'tsuites';
        $returnArray = true;
      break;
      
    
    }


       if( !is_null($metrics) && !is_null($metrics[$setKey]) > 0)
       {
         $renderObj = new stdClass();
      $itemList = array_keys($metrics[$setKey]);      
      $renderObj->info = array();  
        foreach($itemList as $itemID)
        {
          if( isset($metrics['with_tester'][$itemID]) )
          {
          $totalRun = 0;
            $renderObj->info[$itemID]['type'] = $itemType;
            $renderObj->info[$itemID]['name'] = $metrics[$setKey][$itemID];   
            $renderObj->info[$itemID]['total_tc'] = $metrics['total'][$itemID]['qty'];   
          $renderObj->info[$itemID]['details'] = array();
          
          $rf = &$renderObj->info[$itemID]['details'];
          $doPerc = ($renderObj->info[$itemID]['total_tc'] > 0); 
          foreach($code_verbose as $statusCode => $statusVerbose)
          {
            $rf[$statusVerbose] = array('qty' => 0, 'percentage' => 0);
            $rf[$statusVerbose]['qty'] = $metrics['with_tester'][$itemID][$statusCode]['exec_qty'];   
            
            if($doPerc) 
            {
              $rf[$statusVerbose]['percentage'] = number_format(100 * 
                                        ($rf[$statusVerbose]['qty'] / 
                                          $renderObj->info[$itemID]['total_tc']),1);
            }
            $totalRun += $statusVerbose == 'not_run' ? 0 : $rf[$statusVerbose]['qty'];
          }
          if($doPerc) 
          {
            $renderObj->info[$itemID]['percentage_completed'] =  number_format(100 * 
                                       ($totalRun/$renderObj->info[$itemID]['total_tc']),1);
                    }                                             
          }
        }
         
        foreach($code_verbose as $status_verbose)
        {
          $l10n = isset($labels[$status_verbose]) ? lang_get($labels[$status_verbose]) : lang_get($status_verbose); 
        
          $renderObj->colDefinition[$status_verbose]['qty'] = $l10n;
          $renderObj->colDefinition[$status_verbose]['percentage'] = '[%]';
        }
  
    }
    
    if($returnArray)
    {
      return array($renderObj,$metrics['staircase']);
    }
    else
    {
      unset($metrics);
      return $renderObj;
    }
  }


  /**
   *
   * @internal revisions
   *
   * @since 1.9.4
   * 20120429 - franciscom - 
   */
  function getStatusTotalsByTestSuiteForRender($id,$filters=null,$opt=null)
  {
    list($renderObj,$staircase) = $this->getStatusTotalsByItemForRender($id,'tsuite',$filters,$opt);
    unset($staircase);
    return $renderObj;
  }

  /**
   *
   * @internal revisions
   *
   * @since 1.9.4
   */
  function getStatusTotalsByTopLevelTestSuiteForRender($id,$filters=null,$opt=null)
  {
    list($rObj,$staircase) = $this->getStatusTotalsByItemForRender($id,'tsuite',$filters,$opt);
  
    $key2loop = array_keys($rObj->info);
    $template = array('type' => 'tsuite', 'name' => '','total_tc' => 0,
              'percentage_completed' => 0, 'details' => array());  

    foreach($this->statusCode as $verbose => $code)
    {
      $template['details'][$verbose] = array('qty' => 0, 'percentage' => 0);
    }
    
    $renderObj = new stdClass();
    $renderObj->colDefinition = $rObj->colDefinition;
    
    // collect qty
    $topNameCache = null;
    $execQty = null;
    foreach($key2loop as $tsuite_id)
    {
      // (count() == 1) => is a TOP LEVEL SUITE, 
      // only element contains Root node, is useless for this algorithm
      // 
      
      if( count($staircase[$tsuite_id]) > 1)
      {
        // element at position 1 is a TOP LEVEL SUITE
        $topSuiteID = &$staircase[$tsuite_id][1];
        $initName = false;
      }
      else
      {
        $topSuiteID = $tsuite_id;
        $initName = true;
      }     
      
      
      
        if( !isset($renderObj->info[$topSuiteID]) )
        {
          $renderObj->info[$topSuiteID] = $template;
          $execQty[$topSuiteID] = 0;
          $initName = true;
        }  

        if( $initName )
        {
          $dummy = $this->tree_manager->get_node_hierarchy_info($topSuiteID);
          $renderObj->info[$topSuiteID]['name'] = $dummy['name'];
          unset($dummy);
        }        
        
        
        // Loop to get executions counters
        foreach($rObj->info[$tsuite_id]['details'] as $code => &$elem)
        {
          $renderObj->info[$topSuiteID]['details'][$code]['qty'] += $elem['qty'];    
          $renderObj->info[$topSuiteID]['total_tc'] += $elem['qty'];

          if(  $code != 'not_run' )
          {
            $execQty[$topSuiteID] += $elem['qty'];
          }
        }
      }  
       
    // Last step: get percentages
    foreach($renderObj->info as $tsuite_id => &$elem)
    {
      if( $execQty[$tsuite_id] > 0 )
      {
        $elem['percentage_completed'] = number_format( 100 * ($execQty[$tsuite_id] / $elem['total_tc']),1);
      }  

      if( $elem['total_tc'] > 0 )
      {
        foreach($elem['details'] as $code => &$yumyum)
        {
          $yumyum['percentage'] = number_format( 100 * ($yumyum['qty'] / $elem['total_tc']),1);    
        }
      }
    }
    
    unset($topNameCache);
    unset($rObj);
    unset($staircase);
    unset($template);
    unset($key2loop);
    unset($execQty);

    return $renderObj;
  }

  /** 
   *    
   *    
   *    
   *    
   */    
  function getExecCountersByTestSuiteExecStatus($id, $filters=null, $opt=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $safe_id = intval($id);
    list($my,$builds,$sqlStm) = $this->helperGetExecCounters($id, $filters, $opt);

    // Latest Execution Ignoring Build
    $sqlLEBP = $sqlStm['LEBP'];

    $sqlUnionAT  =  "/* {$debugMsg} sqlUnionAT - executions */" . 
            " SELECT NHTC.parent_id AS tsuite_id,TPTCV.platform_id," .
            " COALESCE(E.status,'{$this->notRunStatusCode}') AS status " .
            " FROM {$this->tables['testplan_tcversions']} TPTCV " .

            $sqlStm['getAssignedFeatures'] .
            
            " /* GO FOR Absolute LATEST exec ID IGNORE BUILD */ " .
            " JOIN ({$sqlLEBP}) AS LEBP " .
            " ON  LEBP.testplan_id = TPTCV.testplan_id " .
            " AND LEBP.platform_id = TPTCV.platform_id " .
            " AND LEBP.tcversion_id = TPTCV.tcversion_id " .
            " AND LEBP.testplan_id = " . $safe_id .

            " /* Get execution status WRITTEN on DB */ " .
            " JOIN {$this->tables['executions']} E " .
            " ON  E.id = LEBP.id " .

            " /* Get Test Case info from Test Case Version */ " .
            " JOIN {$this->tables['nodes_hierarchy']} NHTCV " .
            " ON  NHTCV.id = TPTCV.tcversion_id " .

            " /* Get Test Suite info from Test Case  */ " .
            " JOIN {$this->tables['nodes_hierarchy']} NHTC " .
            " ON  NHTC.id = NHTCV.parent_id " .
      
            " WHERE TPTCV.testplan_id=" . $safe_id .
            $builds->whereAddExec;
            


    $sqlUnionBT  =  "/* {$debugMsg} sqlUnionBK - NOT RUN */" . 
            " SELECT NHTC.parent_id AS tsuite_id,TPTCV.platform_id," .
            " COALESCE(E.status,'{$this->notRunStatusCode}') AS status " .
            " FROM {$this->tables['testplan_tcversions']} TPTCV " .
            
            $sqlStm['getAssignedFeatures'] .

            " /* Get REALLY NOT RUN => BOTH LEBP.id AND E.id ON LEFT OUTER see WHERE  */ " .
            " LEFT OUTER JOIN ({$sqlLEBP}) AS LEBP " .
            " ON  LEBP.testplan_id = TPTCV.testplan_id " .
            " AND LEBP.platform_id = TPTCV.platform_id " .
            " AND LEBP.tcversion_id = TPTCV.tcversion_id " .
            " AND LEBP.testplan_id = " . $safe_id .
            " LEFT OUTER JOIN {$this->tables['executions']} E " .
            " ON  E.tcversion_id = TPTCV.tcversion_id " .
            " AND E.testplan_id = TPTCV.testplan_id " .
            " AND E.platform_id = TPTCV.platform_id " .

            $builds->joinAdd .

            " /* Get Test Case info from Test Case Version */ " .
            " JOIN {$this->tables['nodes_hierarchy']} NHTCV " .
            " ON  NHTCV.id = TPTCV.tcversion_id " .

            " /* Get Test Suite info from Test Case  */ " .
            " JOIN {$this->tables['nodes_hierarchy']} NHTC " .
            " ON  NHTC.id = NHTCV.parent_id " .

            " /* FILTER BUILDS in set on target test plan (not alway can be applied) */ " .
            " WHERE TPTCV.testplan_id=" . $safe_id . 
            $builds->whereAddNotRun .
  
            " /* Get REALLY NOT RUN => BOTH LE.id AND E.id NULL  */ " .
            " AND E.id IS NULL AND LEBP.id IS NULL";

    
    $sql =  " /* {$debugMsg} UNION ALL => DO NOT DISCARD Duplicates */" .
        " SELECT tsuite_id,status, count(0) AS exec_qty " .
        " FROM ($sqlUnionAT UNION ALL $sqlUnionBT ) AS SQT " .
        " GROUP BY tsuite_id,status ";

    $exec['with_tester'] = (array)$this->db->fetchMapRowsIntoMap($sql,'tsuite_id','status');              
        
    // now we need to complete status domain
    $this->helperCompleteStatusDomain($exec,'tsuite_id');
  
    // Build item set
    $exec['tsuites_full'] = $this->get_testsuites($safe_id);
    $loop2do = count($exec['tsuites_full']);
    for($idx=0; $idx < $loop2do; $idx++)
    {
      $keySet[] = $exec['tsuites_full'][$idx]['id'];

    }
    $dx = $this->tree_manager->get_full_path_verbose($keySet,array('output_format' => 'stairway2heaven'));
    for($idx=0; $idx < $loop2do; $idx++)
    {
      $exec['tsuites'][$exec['tsuites_full'][$idx]['id']] = $dx['flat'][$exec['tsuites_full'][$idx]['id']];
    }
    $exec['staircase'] = $dx['staircase'];
    
    unset($dx);
    unset($keySet);
    return $exec;
  }




  /** 
   *    
   *    
   *    
   *    
   */    
  function getExecStatusMatrix($id, $filters=null, $opt=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $my = array();
    $my['opt'] = array('getExecutionNotes' => false, 'getTester' => false,
                       'getUserAssignment' => false, 'output' => null,
                       'getExecutionTimestamp' => false, 'getExecutionDuration' => false);

    $my['opt'] = array_merge($my['opt'], (array)$opt);
    $safe_id = intval($id);
    list($my,$builds,$sqlStm,$union) = $this->helperBuildSQLTestSuiteExecCounters($id, $filters, $my['opt']);

    $sql =  " /* {$debugMsg} UNION WITH ALL CLAUSE */ " .
            " {$union['exec']} UNION ALL {$union['not_run']} ";

    
    $keyColumns = array('tsuite_id','tcase_id','platform_id','build_id');
    $cumulative = ($my['opt']['output'] == 'cumulative');
    //switch($my['opt']['output'])
    //{
    //  case 
    // }
    $dummy = (array)$this->db->fetchRowsIntoMap4l($sql,$keyColumns,$cumulative);              

    unset($sqlStm);
    unset($union);
    unset($my);
    unset($builds);

    // now is time do some decoding
    // Key is a tuple (PARENT tsuite_id, test case id, platform id)
    //
    $item2loop = array_keys($dummy);
    $stairway2heaven = null;
    $pathway = null;
    $latestExec = null;
    $priorityCfg = config_get('urgencyImportance');

    foreach($item2loop as $item_id)
    {
      //displayMemUsage('Loop');

      $stairway2heaven = $this->tree_manager->get_path($item_id,null,'name');
      $pathway[$item_id] = implode("/",$stairway2heaven);
      unset($stairway2heaven);

      // go inside test case
      $tcase2loop = array_keys($dummy[$item_id]);
      foreach($tcase2loop as $tcase_id)
      {
        $platform2loop = array_keys($dummy[$item_id][$tcase_id]);
        foreach($platform2loop as $platform_id)
        {
          $latestExec[$platform_id][$tcase_id] = array('id' => -1, 'status' => $this->notRunStatusCode);
          $rf = &$dummy[$item_id][$tcase_id][$platform_id];
          foreach($rf as $build_id => &$exec)
          {
            $exec['suiteName'] = $pathway[$item_id];          
            if($exec['executions_id'] > $latestExec[$platform_id][$tcase_id]['id'])
            {
              $latestExec[$platform_id][$tcase_id]['id'] = $exec['executions_id'];
              $latestExec[$platform_id][$tcase_id]['status'] = $exec['status'];
              $latestExec[$platform_id][$tcase_id]['build_id'] = $exec['build_id'];
            }
            
            // -------------------------------------------------------------------
            // Now we need to get priority LEVEL from (urgency * importance)
            // we do not use a function to improve performance
            if ($exec['urg_imp'] >= $priorityCfg->threshold['high']) 
            {            
              $exec['priority_level'] = HIGH;
            } 
            else if( $exec['urg_imp'] < $priorityCfg->threshold['low']) 
            {
              $exec['priority_level'] = LOW;
            }        
            else
            {
              $exec['priority_level'] = MEDIUM;
            }
            // -------------------------------------------------------------------
          } // $rf
        } // $platform2loop    
      } // $tcase2loop
      
      unset($tcase2loop);
      unset($platform2loop);
    } //
      
    unset($pathway);
    return array('metrics' => $dummy, 'latestExec' => $latestExec);
  }
  
  
    
  /** 
   *    
   *  @used-by
   *  getExecutionsByStatus()
   *  getNotRunWithTesterAssigned()
   *  getNotRunWOTesterAssigned()
   *  getExecCountersByBuildExecStatus()
   *  getExecCountersByKeywordExecStatus()
   *  getExecCountersByPriorityExecStatus()
   *  getExecCountersByBuildUAExecStatus()
   *  getExecCountersByTestSuiteExecStatus()
   *   
   *    
   *  @internal revisions
   *  
   */    
  function helperGetExecCounters($id, $filters, $opt)
  {
    $sql = array();
    $my = array();
    $my['opt'] = array('getOnlyAssigned' => false, 'tprojectID' => 0, 
                       'getUserAssignment' => false,
                       'getPlatformSet' => false, 'processClosedBuilds' => true);
    $my['opt'] = array_merge($my['opt'], (array)$opt);
    
    $my['filters'] = array('buildSet' => null);
    $my['filters'] = array_merge($my['filters'], (array)$filters);
    
    // Build Info
    $bi = new stdClass();
    $bi->idSet = $my['filters']['buildSet']; 
    $bi->inClause = '';
    $bi->infoSet = null;
    if( is_null($bi->idSet) )
    {
      $openStatus = $my['opt']['processClosedBuilds'] ? null : 1;
      $bi->idSet = array_keys($bi->infoSet = $this->get_builds($id,testplan::ACTIVE_BUILDS,$openStatus));
    }
    
    // ==========================================================================
    // Emergency Exit !!!
    if( is_null($bi->idSet) )
    {
        throw new Exception(__METHOD__ . " - Can not work with empty build set");
    }
    // ==========================================================================
    
    
    // Things seems to be OK
    $bi->inClause = implode(",",$bi->idSet);
    if( $my['opt']['getOnlyAssigned'] )
    {
      $sql['getAssignedFeatures']   =  " /* Get feature id with Tester Assignment */ " .
                                       " JOIN {$this->tables['user_assignments']} UA " .
                                       " ON UA.feature_id = TPTCV.id " .
                                       " AND UA.build_id IN ({$bi->inClause}) " .
                                       " AND UA.type = {$this->execTaskCode} ";
      $bi->source = "UA";
      $bi->joinAdd = " AND E.build_id = UA.build_id ";
      $bi->whereAddExec = " AND {$bi->source}.build_id IN ({$bi->inClause}) "; 
      $bi->whereAddNotRun = $bi->whereAddExec; 
    }            
    else
    {
      $sql['getAssignedFeatures'] = '';
      $bi->source = "E";
      
      // TICKET 5353
      // $bi->joinAdd = "";
      $bi->joinAdd = " AND E.build_id IN ({$bi->inClause}) ";
      
      // Why ?
      // If I'm consider test cases WITH and WITHOUT Tester assignment,
      // I will have no place to go to filter for builds.
      // Well at least when trying to get EXECUTED test case, I will be able
      // to apply filter on Executions table.
      // Why then I choose to have this blank ANYWAY ?
      // Because I will get filtering on Build set through 
      // the Latest Execution queries (see below sql['LE'], sql['LEBP'].
      // 
      // Anyway we need to backup all these thoughts with a long, long test run
      // on test link itself.
      $bi->whereAddExec = " AND {$bi->source}.build_id IN ({$bi->inClause}) "; 
      $bi->whereAddNotRun = ""; 
    }               

    $sql['getUserAssignment']['not_run'] = "";
    $sql['getUserAssignment']['exec'] = "";

    if( $my['opt']['getUserAssignment'] )
    {
      $sql['getUserAssignment']['not_run'] = 
        " LEFT JOIN {$this->tables['user_assignments']} UA " .
        " ON UA.feature_id = TPTCV.id " .
        " AND UA.build_id = BU.id " .
        " AND UA.type = {$this->execTaskCode} ";
   
      $sql['getUserAssignment']['exec'] = 
        " LEFT JOIN {$this->tables['user_assignments']} UA " .
        " ON UA.feature_id = TPTCV.id " .
        " AND UA.build_id = E.build_id " .
        " AND UA.type = {$this->execTaskCode} ";
    }


    // Latest Execution IGNORING Build and Platform
    $sql['LE'] = " SELECT EE.tcversion_id,EE.testplan_id,MAX(EE.id) AS id " .
                 " FROM {$this->tables['executions']} EE " . 
                 " WHERE EE.testplan_id=" . intval($id) . 
                 " AND EE.build_id IN ({$bi->inClause}) " .
                 " GROUP BY EE.tcversion_id,EE.testplan_id ";


    // Latest Execution By Platform (ignore build)
    $sql['LEBP'] =   " SELECT EE.tcversion_id,EE.testplan_id,EE.platform_id,MAX(EE.id) AS id " .
                    " FROM {$this->tables['executions']} EE " . 
                     " WHERE EE.testplan_id=" . intval($id) . 
                    " AND EE.build_id IN ({$bi->inClause}) " .
                     " GROUP BY EE.tcversion_id,EE.testplan_id,EE.platform_id ";

    // Last Executions By Build (LEBB) (ignore platform)
    $sql['LEBB'] =  " SELECT EE.tcversion_id,EE.testplan_id,EE.build_id,MAX(EE.id) AS id " .
                    " FROM {$this->tables['executions']} EE " . 
                    " WHERE EE.testplan_id=" . intval($id) . 
                    " AND EE.build_id IN ({$bi->inClause}) " .
                    " GROUP BY EE.tcversion_id,EE.testplan_id,EE.build_id ";
  

    // Last Executions By Build and Platform (LEBBP)
    $sql['LEBBP'] = " SELECT EE.tcversion_id,EE.testplan_id,EE.platform_id,EE.build_id," .
                    " MAX(EE.id) AS id " .
                    " FROM {$this->tables['executions']} EE " . 
                    " WHERE EE.testplan_id=" . intval($id) . 
                    " AND EE.build_id IN ({$bi->inClause}) " .
                    " GROUP BY EE.tcversion_id,EE.testplan_id,EE.platform_id,EE.build_id ";


    return array($my,$bi,$sql);
  }  



  /** 
   *    
   *    
   *    
   *    
   */    
  function helperCompleteStatusDomain(&$out,$key)
  {                       
    $totalByItemID = array();
    
    // refence is critic  
    foreach($out as &$elem)
    {                             
      $itemSet = array_keys($elem);
      foreach($itemSet as $itemID)
      {             
        $totalByItemID[$itemID]['qty'] = 0;
        foreach($this->statusCode as $verbose => $code)
        {
          if(!isset($elem[$itemID][$code]))
          {
            $elem[$itemID][$code] = array($key => $itemID,'status' => $code, 'exec_qty' => 0);      
          }                           
                $totalByItemID[$itemID]['qty'] += $elem[$itemID][$code]['exec_qty'];
        }
      }
    }
    $out['total'] = $totalByItemID;
  }



  /** 
   *    
   *    
   *    
   * @internal revisions
   * @since 1.9.5
   * 20121121 - franciscom - TICKET 5353
   */    
  function helperBuildSQLExecCounters($id, $filters=null, $opt=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    try
    {
      list($my,$builds,$sqlStm) = $this->helperGetExecCounters($id, $filters, $opt);
    }
    catch(Exception $e)
    {
      return null;
    }


    $safe_id = intval($id);  
    $platformSet = null;
    if( $my['opt']['getPlatformSet'] )
    {
      $getOpt = array('outputFormat' => 'mapAccessByID', 'outputDetails' => 'name', 'addIfNull' => true);
      $platformSet = $this->getPlatforms($safe_id,$getOpt);
    }
    
    // Latest Executions By Platform (LEBP)
    $sqlLEBP =   $sqlStm['LEBP'];
    
    
    // 20121121 - franciscom
    // Need to understand if this sentence is right:
    //
    // GO FOR Absolute LATEST exec ID IGNORE BUILD
    // Is this right for each use of this method ?
    //
    $dummy['exec']  =  "/* {$debugMsg} sqlUnion - executions */" . 
                      " SELECT TPTCV.tcversion_id,TPTCV.platform_id, " .
                      " COALESCE(E.status,'{$this->notRunStatusCode}') AS status " .
                      " FROM {$this->tables['testplan_tcversions']} TPTCV " .
            
                      $sqlStm['getAssignedFeatures'] .

                      " /* GO FOR Absolute LATEST exec ID IGNORE BUILD */ " .
                      " JOIN ({$sqlLEBP}) AS LEBP " .
                      " ON  LEBP.testplan_id = TPTCV.testplan_id " .
                      " AND LEBP.platform_id = TPTCV.platform_id " .
                      " AND LEBP.tcversion_id = TPTCV.tcversion_id " .
                      " AND LEBP.testplan_id = " . $safe_id .

                      " /* Get execution status WRITTEN on DB */ " .
                      " JOIN {$this->tables['executions']} E " .
                      " ON  E.id = LEBP.id ";
    
    
    $union['exec'] = $dummy['exec'] . " WHERE TPTCV.testplan_id=" . $safe_id . $builds->whereAddExec;

    $union['execActive'] =  $dummy['exec'] .
                            " /* Used to filter ON ACTIVE TCVersion */ " .
                            " JOIN {$this->tables['tcversions']} TCV " .
                            " ON  TCV.id = TPTCV.tcversion_id " .
                            " WHERE TPTCV.testplan_id=" . $safe_id . $builds->whereAddExec .
                            " AND TCV.active = 1 ";
  

    // 20121121 - An issue was reported in this scenario:
    // Test Plan with Platforms (ONE)
    // Two Build:
    // B1 with TC1 passed, TC2 failed, TC3 not run - BUT B1 INACTIVE
    // B3 ALL TEST CASES NOT RUN
    //
    // we got WRONG figures if build set is NOT USING when trying to access Executions TABLE
    //
    $dummy['not_run'] =  "/* {$debugMsg} sqlUnion - NOT RUN */" . 
              " SELECT TPTCV.tcversion_id,TPTCV.platform_id, " .
              " COALESCE(E.status,'{$this->notRunStatusCode}') AS status " .
              " FROM {$this->tables['testplan_tcversions']} TPTCV " .
              
              $sqlStm['getAssignedFeatures'] .
    
              " /* Get REALLY NOT RUN => BOTH LE.id AND E.id ON LEFT OUTER see WHERE  */ " .
              " LEFT OUTER JOIN ({$sqlLEBP}) AS LEBP " .
              " ON  LEBP.testplan_id = TPTCV.testplan_id " .
              " AND LEBP.tcversion_id = TPTCV.tcversion_id " .
              " AND LEBP.platform_id = TPTCV.platform_id " .
              " AND LEBP.testplan_id = " . $safe_id .
              " LEFT OUTER JOIN {$this->tables['executions']} E " .
              " ON  E.tcversion_id = TPTCV.tcversion_id " .
              " AND E.testplan_id = TPTCV.testplan_id " .
              " AND E.platform_id = TPTCV.platform_id " .
              $builds->joinAdd;
    

              $union['not_run'] = $dummy['not_run'] . 
                                  " /* FILTER BUILDS in set on target test plan (not always can be applied) */ " .
                                  " WHERE TPTCV.testplan_id=" . $safe_id . 
                                  $builds->whereAddNotRun .
                                  " /* Get REALLY NOT RUN => BOTH LE.id AND E.id NULL  */ " .
                                  " AND E.id IS NULL AND LEBP.id IS NULL";
    

              $union['not_runActive'] =  $dummy['not_run'] .
                  " /* Used to filter ON ACTIVE TCVersion */ " .
                  " JOIN {$this->tables['tcversions']} TCV " .
                  " ON  TCV.id = TPTCV.tcversion_id " .
                  " WHERE TPTCV.testplan_id=" . $safe_id . 
                  $builds->whereAddNotRun .
                  " /* Get REALLY NOT RUN => BOTH LE.id AND E.id NULL  */ " .
                  " AND E.id IS NULL AND LEBP.id IS NULL" .
                  " AND TCV.active = 1 ";

    
    //echo 'QD - <br>' . $sqlUnionBP . '<br>';
    return array($my,$builds,$sqlStm,$union,$platformSet);
  }


  /** 
   *    
   *    
   *    
   * @internal revision
   * @since 1.9.8
   * 20130713 - franciscom - 
   * when getting info for executed test cases, RIGHT version number for execution 
   * is on EXECUTIONS TABLE not on testplan_tcversions TABLE.
   *
   * REMEMBER that when we update TCVERSION for executed Test Cases, we HAVE TO UPDATE
   * testplan_tcversions table.
   *
   * We also need to use E.tcversion_id and NOT TPTCV.tcversion_id.
   *
   */    
  function helperBuildSQLTestSuiteExecCounters($id, $filters=null, $opt=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $my['opt'] = array('getExecutionNotes' => false, 'getTester' => false, 
                       'getUserAssignment' => false,
                       'getExecutionTimestamp' => false, 'getExecutionDuration' => false);
    $my['opt'] = array_merge($my['opt'], (array)$opt);


    list($my,$builds,$sqlStm) = $this->helperGetExecCounters($id, $filters, $opt);

    $safe_id = intval($id);  


    // Additional Execution fields
    $moreExecFields = "";
    if($my['opt']['getExecutionNotes'])
    {
      $moreExecFields .= "E.notes AS execution_notes,";
    }  
    
    if($my['opt']['getTester'])
    {
      $moreExecFields .= "E.tester_id,";
    } 
    
    if($my['opt']['getExecutionTimestamp'])
    {
      $moreExecFields .= "E.execution_ts,";
    } 

    if($my['opt']['getExecutionDuration'])
    {
      $moreExecFields .= "E.execution_duration,";
    } 

    if($my['opt']['getUserAssignment'])
    {
      $moreExecFields .= "UA.user_id,";
    } 
  
    // Latest Executions By Build Platform (LEBBP)
    $sqlLEBBP = $sqlStm['LEBBP'];

    $union['exec']  =  "/* {$debugMsg} sqlUnion Test suites - executions */" . 
              " SELECT NHTC.parent_id AS tsuite_id,NHTC.id AS tcase_id, NHTC.name AS name," .
              " TPTCV.tcversion_id,TPTCV.platform_id," .
              " E.build_id,E.tcversion_number AS version,TCV.tc_external_id AS external_id, " .
              " E.id AS executions_id, E.status AS status, " . 
              $moreExecFields .
              " E.execution_type AS exec_type," .

              " (TPTCV.urgency * TCV.importance) AS urg_imp " .
              " FROM {$this->tables['testplan_tcversions']} TPTCV " .
  
              $sqlStm['getAssignedFeatures'] .
  
              " /* GO FOR Absolute LATEST exec ID On BUILD,PLATFORM */ " .
              " JOIN ({$sqlLEBBP}) AS LEBBP " .
              " ON  LEBBP.testplan_id = TPTCV.testplan_id " .
              " AND LEBBP.platform_id = TPTCV.platform_id " .
              " AND LEBBP.tcversion_id = TPTCV.tcversion_id " .
              " AND LEBBP.testplan_id = " . $safe_id .

              " /* Get execution status WRITTEN on DB */ " .
              " JOIN {$this->tables['executions']} E " .
              " ON  E.id = LEBBP.id " .
              " AND E.build_id = LEBBP.build_id " .

              $sqlStm['getUserAssignment']['exec'] .


              " /* Get Test Case info from Test Case Version */ " .
              " JOIN {$this->tables['nodes_hierarchy']} NHTCV " .
              " ON  NHTCV.id = TPTCV.tcversion_id " .
  
              " /* Get Test Suite info from Test Case  */ " .
              " JOIN {$this->tables['nodes_hierarchy']} NHTC " .
              " ON  NHTC.id = NHTCV.parent_id " .
              
              " /* Get Test Case Version attributes */ " .
              " JOIN {$this->tables['tcversions']} TCV " .
              // " ON  TCV.id = TPTCV.tcversion_id " .
              " ON  TCV.id = E.tcversion_id " . 

              " WHERE TPTCV.testplan_id=" . $safe_id .
              $builds->whereAddExec;

    //echo 'QD - <br>' . $union['exec'] . '<br>';
    // die();

    $union['not_run'] =  "/* {$debugMsg} sqlUnion Test suites - not run */" . 
              " SELECT NHTC.parent_id AS tsuite_id,NHTC.id AS tcase_id, NHTC.name AS name," .
              " TPTCV.tcversion_id, TPTCV.platform_id," .
              " BU.id AS build_id,TCV.version,TCV.tc_external_id AS external_id, " .
              " COALESCE(E.id,-1) AS executions_id, " .
              " COALESCE(E.status,'{$this->notRunStatusCode}') AS status, " . 
              $moreExecFields .
              " TCV.execution_type AS exec_type," . 
              " (TPTCV.urgency * TCV.importance) AS urg_imp " .
              " FROM {$this->tables['testplan_tcversions']} TPTCV " .
  
              $sqlStm['getAssignedFeatures'] .
         
              " /* Needed to be able to put a value on build_id on output set  */ " .
              " JOIN {$this->tables['builds']} BU " .
              " ON BU.id IN ({$builds->inClause}) " .

              $sqlStm['getUserAssignment']['not_run'] .
              
              " /* GO FOR Absolute LATEST exec ID On BUILD,PLATFORM */ " .
              " LEFT OUTER JOIN ({$sqlLEBBP}) AS LEBBP " .
              " ON  LEBBP.testplan_id = TPTCV.testplan_id " .
              " AND LEBBP.platform_id = TPTCV.platform_id " .
              " AND LEBBP.tcversion_id = TPTCV.tcversion_id " .
              " AND LEBBP.build_id = BU.id " .
              " AND LEBBP.testplan_id = " . $safe_id .

              " /* Get execution status WRITTEN on DB */ " .
              " LEFT OUTER JOIN {$this->tables['executions']} E " .
              " ON  E.build_id = LEBBP.build_id " .
              " AND E.testplan_id = TPTCV.testplan_id " .
              " AND E.platform_id = TPTCV.platform_id " .
              " AND E.tcversion_id = TPTCV.tcversion_id " .
  
              " /* Get Test Case info from Test Case Version */ " .
              " JOIN {$this->tables['nodes_hierarchy']} NHTCV " .
              " ON  NHTCV.id = TPTCV.tcversion_id " .
  
              " /* Get Test Suite info from Test Case  */ " .
              " JOIN {$this->tables['nodes_hierarchy']} NHTC " .
              " ON  NHTC.id = NHTCV.parent_id " .

              " /* Get Test Case Version attributes */ " .
              " JOIN {$this->tables['tcversions']} TCV " .
              " ON  TCV.id = TPTCV.tcversion_id " .
      
              " WHERE TPTCV.testplan_id=" . $safe_id .
              " AND BU.id IN ({$builds->inClause}) " .
              $builds->whereAddNotRun .
    
              " /* Get REALLY NOT RUN => BOTH LEBBP.id AND E.id NULL  */ " .
              " AND E.id IS NULL AND LEBBP.id IS NULL";
    
    //echo 'QD - <br>' . $union['not_run'] . '<br>';
    return array($my,$builds,$sqlStm,$union);
  }


  /** 
   * get executions (Not Run is not included)   
   *    
   * @param int $id test plan id
   * @param char $status status code (one char)
   * @param mixed $filters
   *        keys: 'buildSet'
   *
   * @param mixed opt    
   *        keys: 'output' elem domain 'map','array'
   *    
   */    
  function getExecutionsByStatus($id,$status,$filters=null,$opt=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    list($my,$builds,$sqlStm) = $this->helperGetExecCounters($id, $filters, $opt);
    
    // particular options
    $my['opt'] = array_merge(array('output' => 'map'),$my['opt']);    
    $safe_id = intval($id);  

    $fullEID = $this->helperConcatTCasePrefix($safe_id);


    $sqlLEBBP = $sqlStm['LEBBP'];
    $sql =  "/* {$debugMsg} executions with status WRITTEN on DB => not run is not present */" . 
            " SELECT NHTC.parent_id AS tsuite_id,NHTC.id AS tcase_id, NHTC.name AS name," .
            " TPTCV.tcversion_id,TPTCV.platform_id," .
            " E.tcversion_number, E.build_id,E.id AS executions_id, E.status AS status, " .
            " E.notes AS execution_notes, E.tester_id,E.execution_ts," .
            " TCV.version,TCV.tc_external_id AS external_id, " .
            " $fullEID AS full_external_id," .
            " (TPTCV.urgency * TCV.importance) AS urg_imp " .
            " FROM {$this->tables['testplan_tcversions']} TPTCV " .
            
            " /* GO FOR Absolute LATEST exec ID On BUILD,PLATFORM */ " .
            " JOIN ({$sqlLEBBP}) AS LEBBP " .
            " ON  LEBBP.testplan_id = TPTCV.testplan_id " .
            " AND LEBBP.platform_id = TPTCV.platform_id " .
            " AND LEBBP.tcversion_id = TPTCV.tcversion_id " .
            " AND LEBBP.testplan_id = " . $safe_id .

            $sqlStm['getAssignedFeatures'] .
            
            " /* Get execution status WRITTEN on DB */ " .
            " JOIN {$this->tables['executions']} E " .
            " ON  E.id = LEBBP.id " .
            " AND E.build_id = LEBBP.build_id " .
            $builds->joinAdd .


            
            " /* Get Test Case info from Test Case Version */ " .
            " JOIN {$this->tables['nodes_hierarchy']} NHTCV " .
            " ON  NHTCV.id = TPTCV.tcversion_id " .
            
            " /* Get Test Suite info from Test Case  */ " .
            " JOIN {$this->tables['nodes_hierarchy']} NHTC " .
            " ON  NHTC.id = NHTCV.parent_id " .
            
            " /* Get Test Case Version attributes */ " .
            " JOIN {$this->tables['tcversions']} TCV " .
            " ON  TCV.id = TPTCV.tcversion_id " .

            
            " WHERE TPTCV.testplan_id=" . $safe_id .
            " AND E.status='{$status}' " .
            $builds->whereAddExec;
              
                                   
    switch($my['opt']['output'])
    {
      case 'array':
        $dummy = (array)$this->db->get_recordset($sql);              
      break;

      case 'mapByExecID':
        $dummy = (array)$this->db->fetchRowsIntoMap($sql,'executions_id');              
      break;


      case 'map':
      default:
        $keyColumns = array('tsuite_id','tcase_id','platform_id','build_id');
        $dummy = (array)$this->db->fetchRowsIntoMap4l($sql,$keyColumns);              
      break;
    }

    return $dummy;
    
  }


  /** 
   * get just Not Run test case on test plan, but ONLY THESE
   * that has tester assigned.
   * This is critic:
   *
   * example:
   * test plan with 11 test cases linked.
   * two Builds B1, B2
   * 1. Assign tester to all test cases on BUILD B1
   * 2. run getNotRunWithTesterAssigned()
   *    you will get 11 records all for B1
   *
   * 3. Assign tester to 4 test cases on BUILD B2   
   * 4. run getNotRunWithTesterAssigned()
   *    you will get: 15 records
   *    11 records for B1
   *     4 records for B2
     *
   * @param int $id test plan id
   * @param char $status status code (one char)
   * @param mixed $filters
   *        keys: 'buildSet'
   *
   * @param mixed opt    
   *        keys: 'output' elem domain 'map','array'
   *    
   */    
  function getNotRunWithTesterAssigned($id,$filters=null,$opt=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    list($my,$builds,$sqlStm) = $this->helperGetExecCounters($id, $filters, $opt);

    
    // particular options
    $my['opt'] = array_merge(array('output' => 'map'),$my['opt']);    
    $safe_id = intval($id);  

    $fullEID = $this->helperConcatTCasePrefix($safe_id);
    // $sqlLEBBP = $sqlStm['LEBBP'];

    // Because we now allow assignment of MULTIPLE testers to same test case
    // we need to remove UA.user_id, in order to avoid duplication
    // UA.user_id,
    // we will need a second step to populate this info.
    //
    $sql =  "/* {$debugMsg} Not Run */" . 
        " SELECT DISTINCT NHTC.parent_id AS tsuite_id,NHTC.id AS tcase_id, NHTC.name AS name," .
        " TPTCV.tcversion_id,TPTCV.platform_id,TPTCV.id AS feature_id," .
        " TCV.version AS tcversion_number, B.id AS build_id," . 
        " '{$this->notRunStatusCode}' AS status, " .
        " TCV.version,TCV.tc_external_id AS external_id, " .
        " $fullEID AS full_external_id," .
        " (TPTCV.urgency * TCV.importance) AS urg_imp, TCV.summary " .
        " FROM {$this->tables['testplan_tcversions']} TPTCV " .

        " JOIN {$this->tables['builds']} B " .
        " ON  B.testplan_id = TPTCV.testplan_id " .

        " /* Get Test Case info from Test Case Version */ " .
        " JOIN {$this->tables['nodes_hierarchy']} NHTCV " .
        " ON  NHTCV.id = TPTCV.tcversion_id " .
  
        " /* Get Test Suite info from Test Case  */ " .
        " JOIN {$this->tables['nodes_hierarchy']} NHTC " .
        " ON  NHTC.id = NHTCV.parent_id " .
        
        " /* Get Test Case Version attributes */ " .
        " JOIN {$this->tables['tcversions']} TCV " .
        " ON  TCV.id = TPTCV.tcversion_id " .

        " JOIN {$this->tables['user_assignments']} UA " .
        " ON  UA.feature_id = TPTCV.id " .
        " AND UA.build_id = B.id " .
        " AND UA.type = {$this->execTaskCode} " .

        " LEFT OUTER JOIN {$this->tables['executions']} E " .
        " ON  E.testplan_id = TPTCV.testplan_id " .
        " AND E.platform_id = TPTCV.platform_id " .
        " AND E.tcversion_id = TPTCV.tcversion_id " .
        " AND E.build_id = B.id ".


        " WHERE TPTCV.testplan_id=" . $safe_id .
        " AND E.id IS NULL " .
        " AND B.id IN ({$builds->inClause}) "; 

      
    switch($my['opt']['output'])
    {
      case 'array':
        $dummy = (array)$this->db->get_recordset($sql);              
        
        // Second Loop
        // get features to get testers
        if(!is_null($dummy))
        {
          // will try with a query
              $sql =  "/* {$debugMsg} Not Run */" . 
        " SELECT UA.user_id, UA.feature_id,UA.build_id" .
        " FROM {$this->tables['testplan_tcversions']} TPTCV " .

        " JOIN {$this->tables['builds']} B " .
        " ON  B.testplan_id = TPTCV.testplan_id " .

        " JOIN {$this->tables['user_assignments']} UA " .
        " ON  UA.feature_id = TPTCV.id " .
        " AND UA.build_id = B.id " .
        " AND UA.type = {$this->execTaskCode} " .

        " LEFT OUTER JOIN {$this->tables['executions']} E " .
        " ON  E.testplan_id = TPTCV.testplan_id " .
        " AND E.platform_id = TPTCV.platform_id " .
        " AND E.tcversion_id = TPTCV.tcversion_id " .
        " AND E.build_id = B.id ".

        " WHERE TPTCV.testplan_id=" . $safe_id .
        " AND E.id IS NULL " .
        " AND B.id IN ({$builds->inClause}) "; 

        $dx = $this->db->get_recordset($sql); 

        $l2do = count($dx);
        $loop2do = count($dummy);
        for($vdx=0; $vdx < $l2do; $vdx++)
        { 
          for($fdx=0; $fdx < $loop2do; $fdx++)
          {
            if($dummy[$fdx]['feature_id'] == $dx[$vdx]['feature_id'] &&
               $dummy[$fdx]['build_id'] == $dx[$vdx]['build_id'] 
              )
            {
              $dummy[$fdx]['user_id'][$dx[$vdx]['user_id']] = $dx[$vdx]['user_id'];
              break;
            }  
          }  
        }  
 
        }  


      break;

      case 'map':
      default:
        throw new Exception("NOT REFACTORED YET for output 'map'", 1);
        $keyColumns = array('tsuite_id','tcase_id','platform_id','build_id');
        $dummy = (array)$this->db->fetchRowsIntoMap4l($sql,$keyColumns);              
      break;
    }

    return $dummy;
      
  }


  /*
   *
   * @used-by lib/results/testCasesWithoutTester.php
   * @internal revisions
   * @since 1.9.4
   *                     
   * @internal revisions
   * @since 1.9.6
   * IMPORTANT NOTICE
   * When doing count() with having, if there are platforms defined
   * we have to consider for having clause BuildQty * PlatformQty,
   * or we are going to get WRONG results.
   */
  function getNotRunWOTesterAssigned($id,$buildSet=null,$filters=null,$opt=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    list($my,$builds,$sqlStm) = $this->helperGetExecCounters($id, $filters, $opt);
    list($safe_id,$buildsCfg,$sqlLEX) = $this->helperGetHits($id,null,$buildSet,
                                                             array('ignorePlatform' => true));
    // particular options
    $my['opt'] = array_merge(array('output' => 'map','ignoreBuild' => false),$my['opt']);    
    $safe_id = intval($id);  

    $fullEID = $this->helperConcatTCasePrefix($safe_id);

    // $sqlLEBBP = $sqlStm['LEBBP'];
    $add2select = ' DISTINCT ';
    $buildInfo = '';
    
    // 20130106 - TICKET 5451 - added A_TPTCV.platform_id on GROUP BY
    // this query try to indentify test cases that has NO ASSIGNMENT ON ALL Builds 
    // for EACH PLATFORM.
    $sqlc = "/* $debugMsg */ " .
            " SELECT count(0) AS TESTER_COUNTER ,A_NHTCV.parent_id AS tcase_id,A_TPTCV.platform_id  " .
            " FROM {$this->tables['testplan_tcversions']} A_TPTCV " .
            " JOIN {$this->tables['builds']} A_B ON A_B.testplan_id = A_TPTCV.testplan_id " .
            str_replace('B.active','A_B.active',$buildsCfg['statusClause']) .
            
            " JOIN {$this->tables['nodes_hierarchy']} A_NHTCV ON " .
            " A_NHTCV.id = A_TPTCV.tcversion_id " .
            
            " LEFT OUTER JOIN {$this->tables['executions']} A_E " .
            " ON  A_E.testplan_id = A_TPTCV.testplan_id " .
            " AND A_E.platform_id = A_TPTCV.platform_id " .
            " AND A_E.tcversion_id = A_TPTCV.tcversion_id " .
            " AND A_E.build_id = A_B.id " .
            
            " LEFT OUTER JOIN {$this->tables['user_assignments']} A_UA " .
            " ON  A_UA.feature_id = A_TPTCV.id " .
            " AND A_UA.build_id = A_B.id " .
            " AND A_UA.type = {$this->execTaskCode} " .
            
            " WHERE A_TPTCV.testplan_id = " . $safe_id  . 
            " AND A_E.status IS NULL " .
            " AND A_UA.user_id IS NULL ";


    // http://stackoverflow.com/questions/7511064/postresql-aliases-column-and-having
    //
    //if( DB_TYPE == 'mssql' )
    //{    
    //  $sqlc .= " GROUP BY tcase_id " .
    //           " HAVING TESTER_COUNTER = " . intval($buildsCfg['count']) ; 
    //}
    //else
    //{
    //  $sqlc .= " GROUP BY A_NHTCV.parent_id " .
    //           " HAVING count(0) = " . intval($buildsCfg['count']) ; 
    //}
    $sqlc .= " GROUP BY A_NHTCV.parent_id, A_TPTCV.platform_id " .                      
             " HAVING count(0) = " . intval($buildsCfg['count']) ; 
    
    
    $sql =  "/* {$debugMsg} Not Run */" . 
        " SELECT $add2select NHTC.parent_id AS tsuite_id,NHTC.id AS tcase_id, NHTC.name AS name," .
        " TPTCV.tcversion_id,TPTCV.platform_id," .
        " TCV.version AS tcversion_number, {$buildInfo}" . 
        " '{$this->notRunStatusCode}' AS status, " .
        " TCV.version,TCV.tc_external_id AS external_id, " .
        " $fullEID AS full_external_id,UA.user_id," .
        " (TPTCV.urgency * TCV.importance) AS urg_imp, TCV.summary  " .
        " FROM {$this->tables['testplan_tcversions']} TPTCV " .

        " JOIN {$this->tables['builds']} B " .
        " ON  B.testplan_id = TPTCV.testplan_id " .

        " /* Get Test Case info from Test Case Version */ " .
        " JOIN {$this->tables['nodes_hierarchy']} NHTCV " .
        " ON  NHTCV.id = TPTCV.tcversion_id " .
  
        " /* Get Test Suite info from Test Case  */ " .
        " JOIN {$this->tables['nodes_hierarchy']} NHTC " .
        " ON  NHTC.id = NHTCV.parent_id " .
        
        " /* Get Test Case Version attributes */ " .
        " JOIN {$this->tables['tcversions']} TCV " .
        " ON  TCV.id = TPTCV.tcversion_id " .

        " JOIN ({$sqlc}) AS NR " .
        " ON  NR.tcase_id = NHTC.id " .

        " LEFT OUTER JOIN {$this->tables['user_assignments']} UA " .
        " ON  UA.feature_id = TPTCV.id " .
        " AND UA.build_id = B.id " .
        " AND UA.type = {$this->execTaskCode} " .

        " LEFT OUTER JOIN {$this->tables['executions']} E " .
        " ON  E.testplan_id = TPTCV.testplan_id " .
        " AND E.platform_id = TPTCV.platform_id " .
        " AND E.tcversion_id = TPTCV.tcversion_id " .
        " AND E.build_id = B.id ".


        " WHERE TPTCV.testplan_id=" . $safe_id .
        " AND E.id IS NULL AND UA.user_id IS NULL " .
        
        // 20130106 - TICKET 5451 - added CONDITION ON NR.platform_id
        " AND B.id IN ({$builds->inClause}) AND TPTCV.platform_id = NR.platform_id "; 

    switch($my['opt']['output'])
    {
      case 'array':
        $dummy = $this->db->get_recordset($sql);  
      break;

      case 'map':
      default:
        $keyColumns = array('tsuite_id','tcase_id','platform_id','build_id');
        $dummy = $this->db->fetchRowsIntoMap4l($sql,$keyColumns);              
      break;
    }
    return (array)$dummy;
  }



  /**
   *
   * @internal revisions
   *
   * @since 1.9.4
   */
  function helperGetUserIdentity($idSet=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $sql = " SELECT id,login,first,last " .
         " FROM {$this->tables['users']}";

    $inClause = '';
    if( !is_null($idSet) && ((array)$idSet >0))
    {
      if( ($dummy=implode(',',(array)$idSet)) != '' )
      {
        $inClause = " WHERE id IN ({$dummy}) ";    
      }  
    }

    $rs = $this->db->fetchRowsIntoMap($sql . $inClause,'id');
    return $rs;
  }


  /**
   *
   * @used-by /lib/results/resultsMoreBuilds.php
   *
   * @internal revisions
   *
   * @since 1.9.4
   */
  function queryMetrics($id,$filters=null,$options=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $safe = array();
    $safe['tplan_id'] = intval($id);

    $my = array();
    list($my,$sqlLEX) = $this->initQueryMetrics($safe['tplan_id'],$filters,$options);
  

    // -------------------------------------------------------------------------------------------    
    // We will work always using last execution result as filter criteria.
    // -------------------------------------------------------------------------------------------
  
    // we will need a union to manage 'not run' (remember this status is NEVER WRITTEN to DB)
    // and other statuses
    // This logic have been borrowed from testplan.class.php - getLinkedForExecTree().
    //
    $key2check = array('builds' => 'build_id', 'platforms' => 'platform_id');
    $ejoin = array();
    foreach($key2check as $check => $field)
    {
      $ejoin[$check] = is_null($my['filters'][$check]) ? '' : 
               " AND E.$field IN (" . implode(',',(array)$my['filters'][$check]) . ')';  
    }
    
  
    
    $union['not_run'] = "/* {$debugMsg} sqlUnion - not run */" .
              " SELECT NH_TCASE.id AS tcase_id,TPTCV.tcversion_id,TCV.version," .
              " TCV.tc_external_id AS external_id, " .
              " COALESCE(E.status,'" . $this->notRunStatusCode . "') AS exec_status " .
              
                 " FROM {$this->tables['testplan_tcversions']} TPTCV " .                          
                 " JOIN {$this->tables['tcversions']} TCV ON TCV.id = TPTCV.tcversion_id " .
                 " JOIN {$this->tables['nodes_hierarchy']} NH_TCV ON NH_TCV.id = TPTCV.tcversion_id " .
                 " JOIN {$this->tables['nodes_hierarchy']} NH_TCASE ON NH_TCASE.id = NH_TCV.parent_id " .
              $my['join']['ua'] .
              $my['join']['keywords'] .
              " LEFT OUTER JOIN {$this->tables['platforms']} PLAT ON PLAT.id = TPTCV.platform_id " .
              
              " /* Get REALLY NOT RUN => BOTH LE.id AND E.id ON LEFT OUTER see WHERE  */ " .
              " LEFT OUTER JOIN ({$sqlLEX}) AS LEX " .
              " ON  LEX.testplan_id = TPTCV.testplan_id " .
              " AND LEX.tcversion_id = TPTCV.tcversion_id " .
              " AND LEX.platform_id = TPTCV.platform_id " .
              " AND LEX.testplan_id = " . $safe['tplan_id'] .
              " LEFT OUTER JOIN {$this->tables['executions']} E " .
              " ON  E.tcversion_id = TPTCV.tcversion_id " .
              " AND E.testplan_id = TPTCV.testplan_id " .
              " AND E.platform_id = TPTCV.platform_id " .
              " AND E.build_id = " . $my['filters']['build_id'] .

              " WHERE TPTCV.testplan_id =" . $safe['tplan_id'] .
              $my['where']['where'] .
              " /* Get REALLY NOT RUN => BOTH LE.id AND E.id NULL  */ " .
              " AND E.id IS NULL AND LEX.id IS NULL";
    // die();    
    
    
    // executions
    $sex = "/* $debugMsg */" .
         "SELECT E.status,E.notes,E.tcversion_number,E.execution_ts,E.build_id,E.platform_id " .
         "FROM {$this->tables['testplan_tcversions']} TPTCV " .
         "JOIN {$this->tables['executions']} E " .
         "ON E.tcversion_id = TPTCV.tcversion_id " .
         "AND E.testplan_id = TPTCV.testplan_id " .
         "AND E.platform_id = TPTCV.platform_id ";
    
    
         
    // build up where clause
    $where = "WHERE TPTCV.testplan_id = " . $safe['tplan_id'];

    $key2check = array('builds' => 'build_id', 'platforms' => 'platform_id');
    foreach($key2check as $check => $field)
    {
      if( !is_null($my['filters'][$check]) )
      {
        $where .= " AND E.$field IN (" . implode(',',(array)$my['filters'][$check]) . ')';  
      }
    }
        
    $sql = $sex . $where;

    //
    echo $sql;
    $rs = $this->db->get_recordset($sql);           
    return $rs;
  }
  
  
  
  /*
   *
   * @used-by 
   *            
   *
   * @internal revisions
   * @since 1.9.4
   */
  function initQueryMetrics($tplanID,$filtersCfg,$optionsCfg)
  {
    $ic = array();

    $ic['join'] = array();
    $ic['join']['ua'] = '';

    $ic['where'] = array();
    $ic['where']['where'] = '';
    $ic['where']['platforms'] = '';

    $ic['green_light'] = true;
  
    $ic['filters'] = array('exec_ts_from' => null, 'exec_ts_to' => null,
                 'assigned_to' => null, 'tester_id' => null,
                 'keywords' => null, 'builds' => null,
                 'platforms' => null, 'top_level_tsuites' => null);

    $ic['filters'] = array_merge($ic['filters'],(array)$filtersCfg);

    
    // ---------------------------------------------------------------------------------------------
    $sqlLEX = " SELECT EE.tcversion_id,EE.testplan_id,EE.platform_id,EE.build_id," .
          " MAX(EE.id) AS id " .
          " FROM {$this->tables['executions']} EE " . 
          " WHERE EE.testplan_id = " . $tplanID;
  
    $key2check = array('builds' => 'build_id', 'platforms' => 'platform_id');
    foreach($key2check as $check => $field)
    {
      $ic['where'][$check] = '';
      if( !is_null($ic['filters'][$check]) )
      {
        $sqlLEX .= " AND EE.$field IN (" . implode(',',(array)$ic['filters'][$check]) . ')';  
        $ic['where'][$check] = " AND TPTCV.$field IN (" . implode(',',(array)$ic['filters'][$check]) . ')';  
      }
    }
    $sqlLEX  .= " GROUP BY EE.tcversion_id,EE.testplan_id,EE.platform_id,EE.build_id ";
    // ---------------------------------------------------------------------------------------------

    if( !is_null($ic['filters']['keywords']) )
    {    
      list($ic['join']['keywords'],$ic['where']['keywords']) = 
        $this->helper_keywords_sql($ic['filters']['keywords'],array('output' => 'array'));

      $ic['where']['where'] .= $ic['where']['keywords']; // **** // CHECK THIS CAN BE NON OK
    }


    return array($ic,$sqlLEX);    
  }

  /** 
   *    
   *    
   */    
  function getExecStatusMatrixFlat($id, $filters=null, $opt=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $my = array();
    $my['opt'] = array('getExecutionNotes' => false, 'getTester' => false,
                       'getUserAssignment' => false, 'output' => null,
                       'getExecutionTimestamp' => false, 'getExecutionDuration' => false);

    $my['opt'] = array_merge($my['opt'], (array)$opt);
    $safe_id = intval($id);
    list($my,$builds,$sqlStm,$union) = $this->helperBuildSQLTestSuiteExecCounters($id, $filters, $my['opt']);

    $sql =  " /* {$debugMsg} UNION WITH ALL CLAUSE */ " .
            " {$union['exec']} UNION ALL {$union['not_run']} ";

    //echo $sql;
    $rs = $this->db->get_recordset($sql);              
    // new dBug($rs);

    $ltx = null;
    if(!is_null($rs))
    {
      $priorityCfg = config_get('urgencyImportance');
      $cache = array('tsuite' => null, 'tcase' => null);

      $loop2do = count($rs);
      $gnOpt = array('fields' => 'name');

      for($adx=0; $adx < $loop2do; $adx++)
      {
        if(!isset($cache['tsuite'][$rs[$adx]['tsuite_id']]))
        {
          $stairway2heaven = $this->tree_manager->get_path($rs[$adx]['tsuite_id'],null,'name');
          $cache['tsuite'][$rs[$adx]['tsuite_id']] = implode("/",$stairway2heaven);
        }
        $rs[$adx]['suiteName'] = $cache['tsuite'][$rs[$adx]['tsuite_id']];

        if($rs[$adx]['urg_imp'] >= $priorityCfg->threshold['high']) 
        {            
          $rs[$adx]['priority_level'] = HIGH;
        } 
        else if( $rs[$adx]['urg_imp'] < $priorityCfg->threshold['low']) 
        {
          $rs[$adx]['priority_level'] = LOW;
        }        
        else
        {
          $rs[$adx]['priority_level'] = MEDIUM;
        }

        $kyy = $rs[$adx]['platform_id'] . '-' . $rs[$adx]['tcase_id'];
      
        // $keyExists = isset($ltx[$kyy]);
        $keyExists = isset($ltx[$rs[$adx]['platform_id']][$rs[$adx]['tcase_id']]);
        $doSet = !$keyExists;
        if( $keyExists )
        {
          $doSet = ($ltx[$rs[$adx]['platform_id']][$rs[$adx]['tcase_id']]['id'] < 
                    $rs[$adx]['executions_id']);
        }  
        if( $doSet )
        {
          $ltx[$rs[$adx]['platform_id']][$rs[$adx]['tcase_id']] = 
             array('id' => $rs[$adx]['executions_id'],'build_id' => $rs[$adx]['build_id'],
                   'status' => $rs[$adx]['status']);
        }
      }  
    }  
    //new dBug($cache);
    //new dBug($rs);
    //new dBug($ltx);
 
    return array('metrics' => $rs, 'latestExec' => $ltx);
  }


  

}
