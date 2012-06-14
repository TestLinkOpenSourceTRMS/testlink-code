<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: testsuite.class.test.php,v $
 *
 * @version $Revision: 1.9 $
 * @modified $Date: 2010/06/24 17:25:56 $ by $Author: asimon83 $
 * @author Francisco Mancardi
 *
 * With this page you can launch a set of available methods, to understand
 * and have inside view about return type .
 *
 * rev :
 *
*/

require_once('../../../config.inc.php');
require_once('common.php');
testlinkInitPage($db);

echo "<pre> testsuite - constructor - testsuite(&\$db)";echo "</pre>";
$tsuite_mgr=new testsuite($db);
new dBug($tsuite_mgr);

$tsuite_name = 'Build Management';
echo "<pre> testsuite - get_by_name(\$name)";echo "</pre>";
echo "<pre>             get_by_name($tsuite_name)";echo "</pre>";
$tsuite_info = $tsuite_mgr->get_by_name($tsuite_name);
new dBug($tsuite_info);
die();

$tsuite_id=689;
echo "<pre> testsuite - get_children(\$id)";echo "</pre>";
echo "<pre>             get_children($tsuite_id)";echo "</pre>";
$tsuite_info=$tsuite_mgr->get_children($tsuite_id);
new dBug($tsuite_info);


$tsuite_id=676;
echo "<pre> testsuite - get_by_id(\$id)";echo "</pre>";
echo "<pre>             get_by_id($tsuite_id)";echo "</pre>";
$tsuite_info=$tsuite_mgr->get_by_id($tsuite_id);
new dBug($tsuite_info);

$tsuite_name=$tsuite_info['name'];

$tsuite_id = array();
$tsuite_id[]=676;
$tsuite_id[]=804;
$tsuite_id[]=826;


echo "<pre> testsuite - get_by_id(\$id)";echo "</pre>";
echo "<pre>             get_by_id($tsuite_id)";echo "</pre>";
$tsuite_info=$tsuite_mgr->get_by_id($tsuite_id);
new dBug($tsuite_info);
die();



echo "<pre> testsuite - get_all()";echo "</pre>";
echo "<pre>             get_all()";echo "</pre>";
$all_tsuites_in_my_tl=$tsuite_mgr->get_all();
new dBug($all_tsuites_in_my_tl);

echo "<pre> testsuite - get_by_name(\$name)";echo "</pre>";
echo "<pre>             get_by_name($tsuite_name)";echo "</pre>";
$tsuite_info=$tsuite_mgr->get_by_name($tsuite_name);
new dBug($tsuite_info);

echo "<pre> testsuite - get_testcases_deep(\$id,\$details='simple')";echo "</pre>";
echo "<pre>             get_testcases_deep($tsuite_id,'simple')";echo "</pre>";
$testcases_deep=$tsuite_mgr->get_testcases_deep($tsuite_id);
new dBug($testcases_deep);

define("GET_ONLY_TESTCASE_ID",1);
echo "<pre>             get_testcases_deep(\$tsuite_id,\$details='full')";echo "</pre>";
$testcases_deep=$tsuite_mgr->get_testcases_deep($tsuite_id,'full');
new dBug($testcases_deep);

echo "<pre> testsuite - getKeywords(\$tcID,\$kwID = null)";echo "</pre>";
echo "<pre>            getKeywords($tsuite_id)";echo "</pre>";
$keywords=$tsuite_mgr->getKeywords($tsuite_id);
new dBug($keywords);


echo "<pre> testsuite - get_keywords_map(\$id,\$order_by_clause='')";echo "</pre>";
$tsuite_id=4;
echo "<pre>               get_keywords_map($tsuite_id)";echo "</pre>";
$keywords_map=$tsuite_mgr->get_keywords_map($tsuite_id);
new dBug($keywords_map);




echo "<pre> testsuite - get_linked_cfields_at_design(\$id,\$parent_id=null,\$show_on_execution=null)";echo "</pre>";
echo "<pre>            get_linked_cfields_at_design($tsuite_id)";echo "</pre>";
$linked_cfields_at_design=$tsuite_mgr->get_linked_cfields_at_design($tsuite_id);
new dBug($linked_cfields_at_design);



echo "<pre> testsuite - get_linked_cfields_at_execution(\$id,\$parent_id=null,<br>
                                                       \$show_on_execution=null,<br>
                                                       \$execution_id=null,\$testplan_id=null)";echo "</pre>"; 
echo "<pre>            get_linked_cfields_at_execution($tsuite_id)";echo "</pre>";
$linked_cfields_at_execution=$tsuite_mgr->get_linked_cfields_at_execution($tsuite_id);
new dBug($linked_cfields_at_execution);



echo "<pre> testsuite - html_table_of_custom_field_inputs(\$id,\$parent_id=null,\$scope='design',\$name_suffix='')";echo "</pre>";
echo "<pre>            html_table_of_custom_field_inputs($tsuite_id)";echo "</pre>";
$table_of_custom_field_inputs=$tsuite_mgr->html_table_of_custom_field_inputs($tsuite_id);
echo "<pre>"; echo $table_of_custom_field_inputs; echo "</pre>";


echo "<pre> testsuite - html_table_of_custom_field_values(\$id,\$scope='design',<br>
                                                         \$show_on_execution=null,<br>
                                                         \$execution_id=null,\$testplan_id=null) ";echo "</pre>";
                                                         
echo "<pre> testsuite - html_table_of_custom_field_values($tsuite_id)";echo "</pre>";
$table_of_custom_field_values=$tsuite_mgr->html_table_of_custom_field_values($tsuite_id); 
echo "<pre><xmp>"; echo $table_of_custom_field_values; echo "</xmp></pre>";
?>
