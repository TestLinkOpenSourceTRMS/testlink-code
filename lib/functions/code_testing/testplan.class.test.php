<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: testplan.class.test.php,v $
 *
 * @version $Revision: 1.1 $
 * @modified $Date: 2007/10/29 14:04:19 $ by $Author: franciscom $
 * @author Francisco Mancardi
 *
 * 
 *
 * rev :
*/

require_once('../../../config.inc.php');
require_once('common.php');
require_once('tree.class.php');
require_once('dBug.php');

testlinkInitPage($db);

echo "<hr><h2> Testplan Manager Class </h2>";
echo "<pre> testplan - constructor - testplan(&\$db)";echo "</pre>";
$tplan_mgr=new testplan($db);
new dBug($tplan_mgr);


echo "<pre> testplan - get_all()";echo "</pre>";
$all_testplans_on_tl=$tplan_mgr->get_all();
new dBug($all_testplans_on_tl);

$tplan_id=-1;
if( !is_null($all_testplans_on_tl) )
{
  $tplan_id=$all_testplans_on_tl[0]['id'];  
}

echo "<pre> testplan - get_by_id(\$id)";echo "</pre>";
echo "<pre>            get_by_id($tplan_id)";echo "</pre>";
$tplan_info=$tplan_mgr->get_by_id($tplan_id);
new dBug($tplan_info);


$tplan_name="TEST_TESTPLAN";
echo "<pre> testplan - get_by_name(\$name,\$tproject_id = 0)";echo "</pre>";
echo "<pre>            get_by_name($tplan_name)";echo "</pre>";
$tplan_info=$tplan_mgr->get_by_name($tplan_name);
new dBug($tplan_info);


echo "<pre> testplan - get_builds(\$tplan_id,\$active=null,\$open=null)";echo "</pre>";
echo "<pre>            get_builds($tplan_id)";echo "</pre>";
$all_builds=$tplan_mgr->get_builds($tplan_id);
new dBug($all_builds);


echo "<pre> testplan - count_testcases(\$tplan_id)";echo "</pre>";
echo "<pre>            count_testcases($tplan_id)";echo "</pre>";
$count_testcases=$tplan_mgr->count_testcases($tplan_id);
new dBug("Number of testcase linked to test plan=" . $count_testcases);

echo "<pre> testplan - get_linked_tcversions(\$tplan_id,\$tcase_id=null,\$keyword_id=0,\$executed=null,
                                             \$assigned_to=null,\$exec_status=null,\$build_id=0,
                                             \$cf_hash = null)";echo "</pre>";

echo "<pre>            get_linked_tcversions($tplan_id)";echo "</pre>";
$linked_tcversions=$tplan_mgr->get_linked_tcversions($tplan_id);
new dBug($linked_tcversions);



// -------------------------------------------------------------------------------------------
echo "<hr><h2> Build Manager Class </h2>";
echo "<pre> build manager - constructor - build_mgr(&\$db)";echo "</pre>";
$build_mgr=new build_mgr($db);
new dBug($build_mgr);


$all_builds=$tplan_mgr->get_builds($tplan_id);
$dummy=array_keys($all_builds);
$build_id=$dummy[0];

echo "<pre> build manager - get_by_id(\$id)";echo "</pre>";
echo "<pre>                 get_by_id($build_id)";echo "</pre>";
$build_info=$build_mgr->get_by_id($build_id);
new dBug($build_info);






/*

// getKeywords($testproject_id,$keywordID = null)
$tplan_id=1;
echo "<pre> testplan - getKeywords(\$testproject_id,\$keywordID = null)";echo "</pre>";
echo "<pre>               getKeywords($tplan_id)";echo "</pre>";
$keywords=$tplan_mgr->getKeywords($tplan_id);
new dBug($keywords);


echo "<pre> testplan - get_keywords_map(\$testproject_id)";echo "</pre>";
$tplan_id=1;
echo "<pre>               get_keywords_map($tplan_id)";echo "</pre>";
$keywords_map=$tplan_mgr->get_keywords_map($tplan_id);
new dBug($keywords_map);


echo "<pre> testplan - get_keywords_tcases(\$testproject_id, \$keyword_id=0)";echo "</pre>";
echo "<pre>               get_keywords_tcases($tplan_id)";echo "</pre>";
$keywords_tcases=$tplan_mgr->get_keywords_tcases($tplan_id);
new dBug($keywords_tcases);


echo "<pre> testplan - get_linked_custom_fields(\$id,\$node_type=null)";echo "</pre>";
echo "<pre>               get_linked_custom_fields($tplan_id)";echo "</pre>";
$linked_custom_fields=$tplan_mgr->get_linked_custom_fields($tplan_id);
new dBug($linked_custom_fields);


echo "<pre> testplan - gen_combo_test_suites(\$id,\$exclude_branches=null,\$mode='dotted')";echo "</pre>";
echo "<pre>               gen_combo_test_suites($tplan_id,null,'dotted')";echo "</pre>";
$combo_test_suites=$tplan_mgr->gen_combo_test_suites($tplan_id,null,'dotted');
new dBug($combo_test_suites);

echo "<pre>               gen_combo_test_suites($tplan_id,null,'dotted')";echo "</pre>";
$combo_test_suites=$tplan_mgr->gen_combo_test_suites($tplan_id,null,'array');
new dBug($combo_test_suites);


echo "<pre> testplan - getReqSpec(\$testproject_id, \$id = null)";echo "</pre>";
echo "<pre>               getReqSpec($tplan_id)";echo "</pre>";
$requirement_spec=$tplan_mgr->getReqSpec($tplan_id);
new dBug($requirement_spec);

$srs_id=2;
echo "<pre>               getReqSpec(\$tplan_id,\$srs_id)";echo "</pre>";
echo "<pre>               getReqSpec($tplan_id,$srs_id)";echo "</pre>";
$requirement_spec=$tplan_mgr->getReqSpec($tplan_id,$srs_id);
new dBug($requirement_spec);


$srs_title='SRS2';
echo "<pre> testplan - get_srs_by_title(\$testproject_id,\$title,\$ignore_case=0)";echo "</pre>";
echo "<pre>               get_srs_by_title($tplan_id,$srs_title)";echo "</pre>";
$srs_by_title=$tplan_mgr->get_srs_by_title($tplan_id,$srs_title);
new dBug($srs_by_title);

// function get_srs_by_title($testproject_id,$title,$ignore_case=0)
*/

/*
function count_testcases($id)

function link_tcversions($id,&$items_to_link)
function get_linked_tcversions($id,$tcase_id=null,$keyword_id=0,$executed=null,
function get_linked_and_newest_tcversions($id,$tcase_id=null)
function unlink_tcversions($id,&$items)
function get_keywords_map($id,$order_by_clause='')
function get_keywords_tcases($id,$keyword_id=0)
function copy_as($id,$new_tplan_id,$tplan_name=null,$tproject_id=null)
function copy_builds($id,$new_tplan_id)
function copy_linked_tcversions($id,$new_tplan_id)
function copy_milestones($id,$new_tplan_id)
function get_milestones($id)
function copy_user_roles($id,$new_tplan_id)
function copy_priorities($id,$new_tplan_id)
function delete($id)
function get_builds_for_html_options($id,$active=null,$open=null)
function get_max_build_id($id,$active = null,$open = null)
function get_builds($id,$active=null,$open=null)
function _natsort_builds($builds_map)
function check_build_name_existence($tplan_id,$build_name,$case_sensitive=0)
function create_build($tplan_id,$name,$notes = '',$active=1,$open=1)
function get_linked_cfields_at_design($id,$parent_id=null,$show_on_execution=null) 
function get_linked_cfields_at_execution($id,$parent_id=null,$show_on_execution=null) 
function html_table_of_custom_field_inputs($id,$parent_id=null,$scope='design') 
function html_table_of_custom_field_values($id,$scope='design',$show_on_execution=null) 
} // function end
function insert_default_priorities($tplan_id)
function get_priority_rules($tplan_id,$do_lang_get=0)
function set_priority_rules($tplan_id,$priority_hash)
function filter_cf_selection ($tp_tcs, $cf_hash)

function build_mgr(&$db)
function create($tplan_id,$name,$notes = '',$active=1,$open=1)
function update($id,$name,$notes,$active=null,$open=null)
function delete($id)
function get_by_id($id)
function milestone_mgr(&$db)
function create($tplan_id,$name,$date,$A,$B,$C)
function update($id,$name,$date,$A,$B,$C)
function delete($id)
function get_by_id($id)
function get_all_by_testplan($tplan_id)


*/
?>
