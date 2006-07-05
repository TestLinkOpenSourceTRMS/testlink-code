<?php

class results
{
  var $db;
  var $tp;
  var $mySuiteList;

  function results(&$db, &$tp)
  {
    $this->db = &$db;	
    $this->tp = &$tp;
    
    $this->mySuiteList = new suiteResultList();

  }

  /**
      testsuite_id -> list of results
  */

  // map suite ids to arrays of results for those suites
  function buildResultTree(){
    $linked_tcversions = $this->tp->get_linked_tcversions($_SESSION['testPlanId']);
    
    while ($testcaseID = key($linked_tcversions)){
      $info = $linked_tcversions[$testcaseID];
      //$notSure = $info[0];
      $testsuite_id = $info[testsuite_id];

      $currentSuiteObject;
      if (!($this->mySuiteList->contains($testsuite_id))){
	$currentSuiteObject = new suiteResult($testsuite_id);
      }

      //$notSure2 = $info[1];
      //$tc_id = $info[tc_id];
      $tcversion_id = $info[tcversion_id];
      //$notSure3 = $info[3];
      $executed = $info[executed];
      $executionExists = 1;

      if ($tcversion_id != $executed){
	// this test case not been executed in this test plan
	$executionExists = 0;
      }

      // select * from executions where tcversion_id = $executed;

      if ($executionExists) {
	// NOTE TO SELF - this is where we can include the searching of results
	// over multiple test plans - by modifying this select statement slightly
	// to include multiple test plan ids

	$execQuery = $this->db->fetchArrayRowsIntoMap("select * from executions where tcversion_id = $executed AND testplan_id = $_SESSION[testPlanId]", 'id');
	//    print_r($execQuery);
        while($executions_id = key($execQuery)){
	  $notSureA = $execQuery[$executions_id];
	  $exec_row = $notSureA[0];
	  $build_id = $exec_row[build_id];
	  $tester_id = $exec_row[tester_id];
	  $execution_ts = $exec_row[execution_ts];
	  $status = $exec_row[status];
	  $testplan_id = $exec_row[testplan_id];
	  $notes = $exec_row[notes];
	  next($execQuery);
	}
      }
      next($linked_tcversions);
    } 
    
  } // end get array of results
} // end class result

class suiteResult {
  function suiteResult($suite_id){

  }
} // end class resultNode


class suiteResultList {
  function suiteResultList(){

  }

  function contains($suite_id){
    return 0;
  }
}

?>