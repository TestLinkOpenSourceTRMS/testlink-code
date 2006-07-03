<?php
  /**
   *  20060615 - kevinlevy - test class which displays function calls
   *  and results into testcase.class.php
   */

require_once('../../config.inc.php');
require_once('common.php');
require_once('testcase.class.php');

$classDescription = "This page will first call an initialization method, then the testplan class will be instantiated, then we will retrieve the current testplan and testproject ids.  Once this initial information has been gathered, each method of the testplan class will be used and we will inspect the results.";

$classUsage = "initialize the page and \$db reference testlinkInitPage(\$db) testlinkInitPage(\$db) Instantiate the testplan object using the \$db reference \$tp = new testplan(\$db) \$tp = new testplan(\$db) Many of the values used by the methods can be retrieve from \$_SESSION ";

$mapOfRows = array();
/** ******************************************** */
$signature = "testcase(&\$db)";
$functionDesc = "testcase class constructor";
$testParams ="no test" ;
$returnValue = "no test";
$testResult = "N/A";
array_push($mapOfRows, array($signature,$functionDesc,$testParams,$returnValue, $testResult));
/** ******************************************** */
$signature = "create(\$parent_id, \$name, \$summary, \$steps, $\expected_results, \$author_id, \$keywords_id='',\$tc_order=null)";
$functionDesc = "creates a test case";
$testParams ="in progress" ;
$returnValue = "in progress";
$testResult = "in progress";
array_push($mapOfRows, array($signature,$functionDesc,$testParams,$returnValue, $testResult));
/** ******************************************** */
$signature = "create_tcase_only(\$parent_id, \$name)";
$functionDesc = "in progress";
$testParams ="in progress" ;
$returnValue = "in progress";
$testResult = "in progress";
array_push($mapOfRows, array($signature,$functionDesc,$testParams,$returnValue, $testResult));
/** ******************************************** */
$signature = "create_tcversion(\$id, \$version, \$summary, \$steps, \$expected_results, \$author_id)";
$functionDesc = "in progress";
$testParams ="in progress" ;
$returnValue = "in progress";
$testResult = "in progress";
array_push($mapOfRows, array($signature,$functionDesc,$testParams,$returnValue, $testResult));
/** ******************************************** */
$signature = "get_by_name(\$name)";
$functionDesc = "in progress";
$testParams ="in progress" ;
$returnValue = "in progress";
$testResult = "in progress";
array_push($mapOfRows, array($signature,$functionDesc,$testParams,$returnValue, $testResult));
/** ******************************************** */
$signature = "get_all()";
$functionDesc = "in progress";
$testParams ="in progress" ;
$returnValue = "in progress";
$testResult = "in progress";
array_push($mapOfRows, array($signature,$functionDesc,$testParams,$returnValue, $testResult));
/** ******************************************** */
$signature = "show(&\$smarty, \$id, \$user_id, \$version_id=TC_ALL_VERSIONS, \$action='', \$msg_result='', \$refresh_tree='yes')";
$functionDesc = "in progress";
$testParams ="in progress" ;
$returnValue = "in progress";
$testResult = "in progress";
array_push($mapOfRows, array($signature,$functionDesc,$testParams,$returnValue, $testResult));
/** ******************************************** */
$signature = "viewer_edit_new(\$amy_keys, \$oFCK, \$action, \$parent_id, \$id=null)";
$functionDesc = "in progress";
$testParams ="in progress" ;
$returnValue = "in progress";
$testResult = "in progress";
array_push($mapOfRows, array($signature,$functionDesc,$testParams,$returnValue, $testResult));
/** ******************************************** */
$signature = "update(\$id, \$tcversion_id, \$name, \$summary, \$steps, \$expected_results, \$user_id, \$keywords_id='', \$tc_order=null)";
$functionDesc = "in progress";
$testParams ="in progress" ;
$returnValue = "in progress";
$testResult = "in progress";
array_push($mapOfRows, array($signature,$functionDesc,$testParams,$returnValue, $testResult));
/** ******************************************** */
$signature = "check_link_and_exec_status(\$id)";
$functionDesc = "in progress";
$testParams ="in progress" ;
$returnValue = "in progress";
$testResult = "in progress";
array_push($mapOfRows, array($signature,$functionDesc,$testParams,$returnValue, $testResult));
/** ******************************************** */
$signature = "delete(\$id, \$version_id=TC_ALL_VERSIONS)";
$functionDesc = "in progress";
$testParams ="in progress" ;
$returnValue = "in progress";
$testResult = "in progress";
array_push($mapOfRows, array($signature,$functionDesc,$testParams,$returnValue, $testResult));
/** ******************************************** */
$signature = "get_linked_versions(\$id, \$status=ALL)";
$functionDesc = "in progress";
$testParams ="in progress" ;
$returnValue = "in progress";
$testResult = "in progress";
array_push($mapOfRows, array($signature,$functionDesc,$testParams,$returnValue, $testResult));
/** ******************************************** */
$signature = "_blind_delete(\$id, \$version_id=TC_ALL_VERSIONS)";
$functionDesc = "in progress";
$testParams ="in progress" ;
$returnValue = "in progress";
$testResult = "in progress";
array_push($mapOfRows, array($signature,$functionDesc,$testParams,$returnValue, $testResult));
/** ******************************************** */
$signature = "_execution_delete(\$id, \$version_id=TC_ALL_VERSIONS)";
$functionDesc = "in progress";
$testParams ="in progress" ;
$returnValue = "in progress";
$testResult = "in progress";
array_push($mapOfRows, array($signature,$functionDesc,$testParams,$returnValue, $testResult));
/** ******************************************** */
$signature = "get_testproject(\$id)";
$functionDesc = "in progress";
$testParams ="in progress" ;
$returnValue = "in progress";
$testResult = "in progress";
array_push($mapOfRows, array($signature,$functionDesc,$testParams,$returnValue, $testResult));
/** ******************************************** */
$signature = "copy_to(\$id, \$parent_id, \$user_id, \$copyKeywords = 0)";
$functionDesc = "in progress";
$testParams ="in progress" ;
$returnValue = "in progress";
$testResult = "in progress";
array_push($mapOfRows, array($signature,$functionDesc,$testParams,$returnValue, $testResult));
/** ******************************************** */

$smarty = new TLSmarty;
$smarty->assign('testFile', 'testcase.class.test.php');
$smarty->assign('classFile', 'testcase.class.php');
$smarty->assign('classDescription', $classDescription);
$smarty->assign('classUsage', $classUsage);

$smarty->assign('mapOfRows', $mapOfRows);
$smarty->display('classTester.tpl');

?>
