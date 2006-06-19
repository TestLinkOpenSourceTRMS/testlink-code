<?php
  /**
   *  20060615 - kevinlevy - test class which displays function calls
   *  and results into testplan.class.php
   */

require_once('../../config.inc.php');
require_once('common.php');
require_once('testplan.class.php');

print "<h3>/lib/results/testplan.class.test.php</h3>";
print "author : Kevin Levy <BR>";
print "last updated 20060618 <BR>";
print "<BR>This page displays the functions in /lib/functions/testplan.class.php and examples of their usage.  This page will first call an initialization method, then the testplan class will be instantiated, then we will retrieve the current testplan and testproject ids.  Once this initial information has been gathered, each method of the testplan class will be used and we will inspect the results.<BR>";

print "============================================== <BR> ";

print "<h3>MUST BE DONE 1st : initialize the page and \$db reference</h3>";
print "testlinkInitPage(\$db) <BR>";
testlinkInitPage($db);

print "============================================== <BR> ";
print "<h3>Instantiate the testplan object using the \$db reference</h3>";
print "\$tp = new testplan(\$db)<BR>";
$tp = new testplan($db);

print "============================================== <BR> ";

print "<h3>Many of the values used by the methods can be retrieve from \$_SESSION</h3>";
print "contents of the \$_SESSION object : <BR>";
print_r($_SESSION);
print "<BR>";

print "============================================== <BR>";

print "<h3>create(\$name,\$notes,\$testproject_id) </h3>";
$timestamp = time();
$timestamp_notes = $timestamp . "_notes";

print "\$tp->create(\$timestamp,\$timestamp_notes,\$_SESSION['testproject_id']); <BR>";
print "\$tp->create($timestamp,$timestamp_notes,$testproject_id); <BR>";

print "View the source of this file and uncomment code below to see this work. A test plan will be created in the current test project. It's name will be a timestamp created dynamically. <BR>";

//$tp->create($timestamp,$timestamp_notes,$testproject_id);

print "============================================== <BR> ";
print "<h3>update(\$id, \$name, \$notes) </h3>";
print "as of 20060618 this method has not been implemented <BR>";
print "============================================== <BR> ";

print "<h3>get_by_name(\$name) </h3>";
print "\$get_by_name_result = \$tp->get_by_name(\$_SESSION['testPlanName']) <BR>";
$get_by_name_result = $tp->get_by_name($_SESSION['testPlanName']);
print "\$get_by_name_result : <BR>";
print_r($get_by_name_result);

print "============================================== <BR> ";
print "<h3>get_by_id(\$id)</h3>";
print "\$get_by_id_result  = \$tp->get_by_id(\$_SESSION['testPlanId']) <BR>";
print "does not appear this method is working as of 20060618 <BR>";
/**
$get_by_id_result = $tp->get_by_id($_SESSION['testPlanId'])";
print "\$get_by_id_result : <BR>";
print_r($get_by_id_result);
*/

print "============================================== <BR> ";
print "<h3>get_all() </h3>";
print "\$get_all_result = \$tp->get_all() <BR>";
$get_all_result = $tp->get_all();
print "\$get_all_result : <BR>";
print_r($get_all_result);
print "<BR>";

print "============================================== <BR> ";
print "<h3>count_testcases(\$id) </h3>";
print "\$count_testcases_result = \$tp->count_testcases(\$_SESSION['testPlanId']) <BR>";
$count_testcases_result = $tp->count_testcases($_SESSION['testPlanId']);
print "\$count_testcases_result : $count_testcases_result<BR>";
print "<BR>";

print "============================================== <BR> ";
print "<h3>link_tcversions(\$id, &\$items_to_link) </h3> ";
print "this method will add a test case to the test plan <BR>";
print "The test code will add 3 test cases to the current test plan.  Don't expect this code to work as is. You will need to re-code this test to use your own testcase ids and tcversion ids (which can be found by querying the nodes_hierarchy table). The testplan used will be the current test plan selected. <BR>";

$tc_id_1 = "6";
$tcversion_id_1 = "7";

$tc_id_2 = "8";
$tcversion_id_2 = "9";

$tc_id_3 = "10";
$tcversion_id_3 = "11";

$items_to_link = array($tc_id_1 => $tcversion_id_1,$tc_id_2 => $tcversion_id_2,$tc_id_3 => $tcversion_id_3 );

print "\$items_to_link = ";
print_r($items_to_link);

print "\$tp->link_tcversions(\$_SESSION['testPlanId'],\$items_to_link) <BR>";

print "uncomment the following line of code to execute this write action. <BR>"; 
//$tp->link_tcversions($_SESSION['testPlanId'], $items_to_link);
print "<BR>";

print "============================================== <BR> ";
print "<h3>get_linked_tcversions(\$id, \$tcase_id=null, \$keyword_id=0, \$executed=null) </h3>";

print "\$get_linked_tcversions_result = \$tp->get_linked_tcversions(\$_SESSION['testPlanId']) <BR>";
$get_linked_tcversions_result = $tp->get_linked_tcversions($_SESSION['testPlanId']);
print "\$get_linked_tcversions_result : <BR>";
print_r($get_linked_tcversions_result);
print "<BR>";

print "============================================== <BR> ";
print "<h3>get_builds_for_html_options(\$id) </h3>";
print "\$get_builds_for_html_options_result = \$tp->get_builds_for_html_options(\$_SESSION['testPlanId']) <BR>";
$get_builds_for_html_options_result = $tp->get_builds_for_html_options($_SESSION['testPlanId']);
print "\$get_builds_for_html_options_result (id-name pairs): <BR>";
print_r($get_builds_for_html_options_result);
print "<BR>";

print "============================================== <BR> ";
print "<h3>get_max_build_id(\$id) </h3>";
print "\$get_max_build_id_result = \$tp->get_max_build_id(\$_SESSION['testPlanId']) <BR>";
$get_max_build_id_result = $tp->get_max_build_id($_SESSION['testPlanId']);
print "\$get_max_build_id_result (id for build)= $get_max_build_id_result <BR>";
print "============================================== <BR> ";
print "<h3>get_builds(\$id) </h3>";
print "\$get_builds_result = \$tp->get_builds(\$_SESSION['testPlanId']) <BR> ";
$get_builds_result = $tp->get_builds($_SESSION['testPlanId']);
print "\$get_builds_result : <BR> ";
print_r($get_builds_result);
print "<BR>";

print "============================================== <BR> ";
print "<h3>unlink_tcversions(\$id, &\$items) </h3>";

print "this method will remove test cases from the test plan <BR>";
print "The test code will remove 3 test cases to the current test plan.  Don't expect this code to work as is. You will need to re-code this test to use your own testcase ids and tcversion ids (which can be found by querying the nodes_hierarchy table). The testplan used will be the current test plan selected. <BR>";

$tc_id_1 = "6";
$tcversion_id_1 = "7";

$tc_id_2 = "8";
$tcversion_id_2 = "9";

$tc_id_3 = "10";
$tcversion_id_3 = "11";

$items_to_unlink = array($tc_id_1 => $tcversion_id_1,$tc_id_2 => $tcversion_id_2,$tc_id_3 => $tcversion_id_3 );

print "\$items_to_unlink = ";
print_r($items_to_unlink);

print "\$tp->unlink_tcversions(\$_SESSION['testPlanId'],\$items_to_unlink) <BR>";

print "uncomment the following line of code to execute this write action. <BR>"; 
print "NOTE: when I ran this on 20060618 - I did not see the test cases removed from the current test plan <BR>";
//$tp->unlink_tcversions($_SESSION['testPlanId'], $items_to_link);
print "<BR>";

print "============================================== <BR> ";
print "<h3>get_keywords_map(\$id, \$order_by_clause='') </h3> ";
print "\$get_keywords_map_result = \$tp->get_keywords_map(\$_SESSION['testPlanId']) <BR>";
$get_keywords_map_result = $tp->get_keywords_map($_SESSION['testPlanId']);
print "\$get_keywords_map_result : <BR>";
print_r($get_keywords_map_result);
print "<BR>";

print "============================================== <BR> ";
print "<h3>get_keywords_tcases(\$id, \$keyword_id=0) </h3> ";
print "\$get_keywords_tcases_result = \$tp->get_keywords_tcases(\$_SESSION['testPlanId']) <BR>";
$get_keywords_tcases_result = $tp->get_keywords_tcases($_SESSION['testPlanId']);
print "\$get_keywords_tcases_result : <BR>";
print_r($get_keywords_tcases_result);
print "<BR>";
print "============================================== <BR> ";

?>
