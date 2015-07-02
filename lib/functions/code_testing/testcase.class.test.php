<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename testcase.class.test.php
 * @author Francisco Mancardi
 *
 * With this page you can launch a set of available methods, to understand
 * and have inside view about return type .
 *
 * @internal revisions
 *
 */

require_once('../../../config.inc.php');
require_once('common.php');
require_once('tree.class.php');
// require_once('dBug.php');

testlinkInitPage($db);

echo "<pre> testcase - constructor - testcase(&\$db)";echo "</pre>";
$tcase_mgr=new testcase($db);
// new dBug($tcase_mgr);

try 
{
  $fullEID = 'PTJ09-1';
  echo '<br>Testing getInternalID with fullEID<br>';
  $va = $tcase_mgr->getInternalID($fullEID);
  new dBug($va);
}
catch (Exception $e)
{
  echo 'Message: ' .$e->getMessage();
}

try 
{
  $EID = 1;
  echo '<br>Testing getInternalID with ONLY NUMERIC EID<br>';
  $va = $tcase_mgr->getInternalID($EID);
  new dBug($va);
}
catch (Exception $e)
{
  echo 'Message: ' .$e->getMessage();
}

try 
{
  $EID = 1;
  echo '<br>Testing getInternalID with ONLY NUMERIC EID<br>';
  $va = $tcase_mgr->getInternalID($EID, array('tproject_id' => 282));
  new dBug($va);
}
catch (Exception $e)
{
  echo 'Message: ' .$e->getMessage();
}


die();


$items = array(1628,1626,1616,392,531);
$va = $tcase_mgr->get_last_active_version($items);
new dBug($va);

$va = $tcase_mgr->get_last_active_version($items[0]);
new dBug($va);

$options = array('access_key' => 'id', 'max_field' => 'version');
$va = $tcase_mgr->get_last_active_version($items,$options);
new dBug($options);
new dBug($items);
new dBug($va);


die();

// SELECT MAX(version) AS version, NH_TCVERSION.parent_id AS id  FROM tcversions TCV  
// JOIN nodes_hierarchy NH_TCVERSION  ON NH_TCVERSION.id = TCV.id AND TCV.active=1  
// AND NH_TCVERSION.parent_id IN ()  GROUP BY NH_TCVERSION.parent_id  ORDER BY NH_TCVERSION.parent_id , -1) called at [C:\usr\local\xampp-1.7.2\xampp\htdocs\head-20100315\lib\functions\database.class.php:593]
// #1  database->fetchRowsIntoMap(/* Class:testcase - Method: get_last_active_version 

// $old_article = file_get_contents('./old_article.txt');
// $new_article = $_REQUEST['article']; /* Let's say that someone pasted a new article to html form */
// 
// $diff = xdiff_string_diff($old_article, $new_article, 1);
// if (is_string($diff)) {
//    echo "Differences between two articles:\n";
//    echo $diff;
// }
   
$version_a=1;
$version_b=2;

$tcase_id=88;   
$va = $tcase_mgr->get_by_id($tcase_id,null,'ALL','ALL',$version_a);
$vb = $tcase_mgr->get_by_id($tcase_id,null,'ALL','ALL',$version_b);

new dBug($va);
new dBug($vb);
$diff = xdiff_string_diff($va[0]['summary'], $vb[0]['summary'], 1);
echo "Differences between two articles:\n";
echo $diff;
die();

   

// getByPathName
// function getByPathName($pathName,$pathSeparator='::')
$pathName='ZATHURA::Holodeck::Apollo 10 Simulation::Unload::Full speed unload';
$fname = 'getByPathName';
echo "<pre> testcase - $fname(\$pathName,\$pathSeparator='::')";echo "</pre>";
echo "<pre>            $fname($pathName)";echo "</pre>";
$result=$tcase_mgr->$fname($pathName);
new dBug($result);
die();


$tcase_id=318;
$tplan_id=389;
$build_id=21;
$platform_id=5;
$version_id=testcase::ALL_VERSIONS;
// $options = array('getNoExecutions' => true);
$options = null;

// function get_last_execution($id,$version_id,$tplan_id,$build_id,$platform_id,$options=null)
echo "<pre> testcase - get_last_execution(\$id,\$version_id,\$tplan_id,\$build_id,\$platform_id\$options=null)";echo "</pre>";
echo "<pre>            get_last_execution($tcase_id,$version_id,$tplan_id,$build_id,$platform_id,$options)";echo "</pre>";
$last_execution=$tcase_mgr->get_last_execution($tcase_id,$version_id,$tplan_id,$build_id,$platform_id,$options);
new dBug($last_execution);
die();

$tcase_id=4;
echo "<pre> testcase - get_by_id(\$id,\$version_id = TC_ALL_VERSIONS, \$active_status='ALL',\$open_status='ALL')";echo "</pre>";
echo "<pre>            get_by_id($tcase_id)";echo "</pre>";
$tcase_info=$tcase_mgr->get_by_id($tcase_id);
new dBug($tcase_info);


$set_of_tcase_id=array(4,6);
echo "<pre>            get_by_id($set_of_tcase_id)";echo "</pre>";
$set_of_tcase_info=$tcase_mgr->get_by_id($set_of_tcase_id);
new dBug($set_of_tcase_info);

$tcase_name='Configuration';
$method='get_by_name';
$tsuite_name='';
$tproject_name='';
echo "<pre>            $method('{$tcase_name}')";echo "</pre>";
$info=$tcase_mgr->$method($tcase_name);
new dBug($info);

$tcase_name='Configuration';
$tsuite_name='Bugzilla';
$tproject_name='';
$method='get_by_name';
echo "<pre>            $method('{$tcase_name}','{$tsuite_name}')";echo "</pre>";
$info=$tcase_mgr->$method($tcase_name,$tsuite_name);
new dBug($info);

$tcase_name='Configuration';
$tsuite_name='Bugzilla';
$tproject_name='IMPORT_TEST';
$method='get_by_name';
echo "<pre>            $method('{$tcase_name}','{$tsuite_name}','{$tproject_name}')";echo "</pre>";
$info=$tcase_mgr->$method($tcase_name,$tsuite_name,$tproject_name);
new dBug($info);


die();



$tcase_id=4;
echo "<pre> testcase - check_link_and_exec_status(\$id)";echo "</pre>";
echo "<pre>            check_link_and_exec_status($tcase_id)";echo "</pre>";
$link_and_exec_status=$tcase_mgr->check_link_and_exec_status($tcase_id);
new dBug($link_and_exec_status);


echo "<pre> testcase - get_linked_versions(\$id,\$exec_status='ALL',\$active_status='ALL')";
echo "<pre>            get_linked_versions($tcase_id)";
$linked_versions=$tcase_mgr->get_linked_versions($tcase_id);
new dBug($linked_versions);

$tcase_id=4;
echo "<pre> testcase - get_testproject(\$id)";
echo "<pre>            get_testproject($tcase_id)";
$testproject_id=$tcase_mgr->get_testproject($tcase_id);
new dBug("testproject id=" . $testproject_id);


$tcase_id=4;
echo "<pre> testcase - get_last_version_info(\$id)";
echo "<pre>            get_last_version_info($tcase_id)";
$last_version_info=$tcase_mgr->get_last_version_info($tcase_id);
new dBug($last_version_info);


echo "<pre> testcase - get_versions_status_quo(\$id,\$tcversion_id=null, \$testplan_id=null)";
echo "<pre>            get_versions_status_quo($tcase_id)";
$status_quo=$tcase_mgr->get_versions_status_quo($tcase_id);
new dBug($status_quo);


echo "<pre> testcase - get_exec_status(\$id)";
echo "<pre>            get_exec_status($tcase_id)";
$testcase_exec_status=$tcase_mgr->get_exec_status($tcase_id);
new dBug($testcase_exec_status);


echo "<pre> testcase - getKeywords(\$tcID,\$kwID = null)";echo "</pre>";
echo "<pre>            getKeywords($tcase_id)";echo "</pre>";
$keywords=$tcase_mgr->getKeywords($tcase_id);
new dBug($keywords);


echo "<pre> testcase - get_keywords_map(\$id,\$order_by_clause='')";echo "</pre>";
$tcase_id=4;
echo "<pre>               get_keywords_map($tcase_id)";echo "</pre>";
$keywords_map=$tcase_mgr->get_keywords_map($tcase_id);
new dBug($keywords_map);


$tcase_id=4;
$version_id=5;
$tplan_id=8;
$build_id=1;
echo "<pre> testcase - get_executions(\$id,\$version_id,\$tplan_id,\$build_id,<br>
                                      \$exec_id_order='DESC',\$exec_to_exclude=null)";echo "</pre>";

echo "<pre>            get_executions($tcase_id,$version_id,$tplan_id,$build_id)";echo "</pre>";
$executions=$tcase_mgr->get_executions($tcase_id,$version_id,$tplan_id,$build_id);
new dBug($executions);


echo "<pre> testcase - get_last_execution(\$id,\$version_id,\$tplan_id,\$build_id,\$get_no_executions=0)";echo "</pre>";
echo "<pre>            get_last_execution($tcase_id,$version_id,$tplan_id,$build_id)";echo "</pre>";
$last_execution=$tcase_mgr->get_last_execution($tcase_id,$version_id,$tplan_id,$build_id);
new dBug($last_execution);




$tcversion_id=5;
$tplan_id=8;
echo "<pre> testcase - get_version_exec_assignment(\$tcversion_id,\$tplan_id)";echo "</pre>";
echo "<pre>            get_version_exec_assignment($tcversion_id,$tplan_id)";echo "</pre>";
$version_exec_assignment=$tcase_mgr->get_version_exec_assignment($tcversion_id,$tplan_id);
new dBug($version_exec_assignment);


echo "<pre> testcase - get_linked_cfields_at_design(\$id,\$parent_id=null,\$show_on_execution=null)";echo "</pre>";
echo "<pre>            get_linked_cfields_at_design($tcase_id)";echo "</pre>";
$linked_cfields_at_design=$tcase_mgr->get_linked_cfields_at_design($tcase_id);
new dBug($linked_cfields_at_design);



echo "<pre> testcase - get_linked_cfields_at_execution(\$id,\$parent_id=null,<br>
                                                       \$show_on_execution=null,<br>
                                                       \$execution_id=null,\$testplan_id=null)";echo "</pre>"; 
echo "<pre>            get_linked_cfields_at_execution($tcase_id)";echo "</pre>";
$linked_cfields_at_execution=$tcase_mgr->get_linked_cfields_at_execution($tcase_id);
new dBug($linked_cfields_at_execution);



echo "<pre> testcase - html_table_of_custom_field_inputs(\$id,\$parent_id=null,\$scope='design',\$name_suffix='')";echo "</pre>";
echo "<pre>            html_table_of_custom_field_inputs($tcase_id)";echo "</pre>";
$table_of_custom_field_inputs=$tcase_mgr->html_table_of_custom_field_inputs($tcase_id);
echo "<pre>"; echo $table_of_custom_field_inputs; echo "</pre>";


echo "<pre> testcase - html_table_of_custom_field_values(\$id,\$scope='design',<br>
                                                         \$show_on_execution=null,<br>
                                                         \$execution_id=null,\$testplan_id=null) ";echo "</pre>";
                                                         
echo "<pre> testcase - html_table_of_custom_field_values($tcase_id)";echo "</pre>";
$table_of_custom_field_values=$tcase_mgr->html_table_of_custom_field_values($tcase_id); 
echo "<pre><xmp>"; echo $table_of_custom_field_values; echo "</xmp></pre>";








/*
	function testcase(&$db)
function get_by_name($name)
function get_all()
function show(&$smarty,$id, $user_id, $version_id=TC_ALL_VERSIONS, $action='', 
function update($id,$tcversion_id,$name,$summary,$steps,
function check_link_and_exec_status($id)
function delete($id,$version_id = TC_ALL_VERSIONS)
function get_linked_versions($id,$exec_status="ALL",$active_status='ALL')
function _blind_delete($id,$version_id=TC_ALL_VERSIONS,$children=null)
function _execution_delete($id,$version_id=TC_ALL_VERSIONS,$children=null)
function get_testproject($id)
function copy_to($id,$parent_id,$user_id,
function create_new_version($id,$user_id)
function get_last_version_info($id)
function copy_tcversion($from_tcversion_id,$to_tcversion_id,$as_version_number,$user_id)
function get_by_id_bulk($id,$version_id=TC_ALL_VERSIONS, $get_active=0, $get_open=0)
function get_by_id($id,$version_id = TC_ALL_VERSIONS, $active_status='ALL',$open_status='ALL')
function get_versions_status_quo($id, $tcversion_id=null, $testplan_id=null)
function get_exec_status($id)
function getKeywords($tcID,$kwID = null)
function get_keywords_map($id,$order_by_clause='')
function addKeyword($id,$kw_id)
function addKeywords($id,$kw_ids)
function copyKeywordsTo($id,$destID)
function deleteKeywords($tcID,$kwID = null)
function get_executions($id,$version_id,$tplan_id,$build_id,$exec_id_order='DESC',$exec_to_exclude=null)
function get_last_execution($id,$version_id,$tplan_id,$build_id,$get_no_executions=0)
function exportTestCaseDataToXML($tcase_id,$tcversion_id,$bNoXMLHeader = false,$optExport = array())
function get_version_exec_assignment($tcversion_id,$tplan_id)
function update_active_status($id,$tcversion_id,$active_status)
function copy_attachments($source_id,$target_id)
function deleteAttachments($id)
function get_linked_cfields_at_design($id,$parent_id=null,$show_on_execution=null) 
function html_table_of_custom_field_inputs($id,$parent_id=null,$scope='design',$name_suffix='') 
function html_table_of_custom_field_values($id,$scope='design',$show_on_execution=null,
function get_linked_cfields_at_execution($id,$parent_id=null,$show_on_execution=null,
function copy_cfields_design_values($from_id,$to_id)                                         
*/?>
