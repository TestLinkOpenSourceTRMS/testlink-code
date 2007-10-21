<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: testproject.class.test.php,v $
 *
 * @version $Revision: 1.1 $
 * @modified $Date: 2007/10/21 16:03:34 $ by $Author: franciscom $
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

echo "<pre> testproject - constructor - testproject(&\$db)";echo "</pre>";
$tproject_mgr=new testproject($db);
new dBug($tproject_mgr);

echo "<pre> testproject - get_keywords_map(\$testproject_id)";echo "</pre>";
$tproject_id=1;
echo "<pre>               get_keywords_map($tproject_id)";echo "</pre>";
$keywords_map=$tproject_mgr->get_keywords_map($tproject_id);
new dBug($keywords_map);


echo "<pre> testproject - get_keywords_tcases(\$testproject_id, \$keyword_id=0)";echo "</pre>";
echo "<pre>               get_keywords_tcases($tproject_id)";echo "</pre>";
$keywords_tcases=$tproject_mgr->get_keywords_tcases($tproject_id);
new dBug($keywords_tcases);






/*
	function testproject(&$db)
function create($name,$color,$optReq,$notes,$active=1)
function update($id, $name, $color, $opt_req,$notes)
function get_by_name($name,$addClause = null)
function get_by_id($id)
function get_all()
function show(&$smarty,$id,$sqlResult='', $action = 'update',$modded_item_id = 0)
function count_testcases($id)
function gen_combo_test_suites($id,$exclude_branches=null,$mode='dotted')
function checkTestProjectName($name,&$msg)
function activateTestProject($id, $status)
function addKeyword($testprojectID,$keyword,$notes)
function updateKeyword($testprojectID,$id,$keyword,$notes)
function check_for_keyword_existence($testprojectID, $kw, $kwID = 0)
function getKeywords($testproject_id,$keywordID = null)
function addKeywords($testprojectID,$keywordData)
function getReqSpec($testproject_id, $id = null)
function createReqSpec($testproject_id,$title, $scope, $countReq,$user_id,$type = 'n')
function get_srs_by_title($testproject_id,$title,$ignore_case=0)
function check_srs_title($testproject_id,$title,$ignore_case=0)
function delete($id,&$error)
function get_keywords_map($testproject_id)
function get_all_testcases_id($id)
function get_keywords_tcases($testproject_id, $keyword_id=0)
function get_all_testplans($testproject_id,$get_tp_without_tproject_id=0,$plan_status=null)
function check_tplan_name_existence($tproject_id,$tplan_name,$case_sensitive=0)
function get_first_level_test_suites($tproject_id,$mode='simple')
function get_by_user_role($user_id,$role_id)
function get_linked_custom_fields($id,$node_type=null,$node_id=null) 
*/

?>
