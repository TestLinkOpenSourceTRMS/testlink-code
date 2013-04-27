<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: testproject.class.test.php,v $
 *
 * @version $Revision: 1.6 $
 * @modified $Date: 2010/02/04 10:51:36 $ by $Author: franciscom $
 * @author Francisco Mancardi
 *
 * 
 *
 * rev :
*/

require_once('../../../config.inc.php');
require_once('common.php');
testlinkInitPage($db);

echo "<pre> testproject - constructor - testproject(&\$db)";echo "</pre>";
$tproject_mgr=new testproject($db);
new dBug($tproject_mgr);

$item = new stdClass();
$item->name = 'CRASH';
$item->notes = " Created doing test ";
$item->color = '';
$item->options = new stdClass();
//$item->options->requirement_mgmt = 1;
//$item->options->priority_mgmt = 1;
//$item->options->automated_execution = 1;
$item->active=1;
$item->is_public=1;
$item->prefix = 'TPX :: ';

try
{
  $id = $tproject_mgr->create($item, array('doChecks' => true));
}
catch (Exception $e) 
{
  echo 'Caught exception: ',  $e->getMessage(), "\n";
}
die();


// new dBug($_SESSION);

$xx=$tproject_mgr->get_accessible_for_user(1,
                       array('output' => 'map','field_set' => 'id', 'format' => 'simple'));
new dBug($xx);
die();

// create()
// function create($name,$color,$options,$notes,$active=1,$tcasePrefix='',$is_public=1)
$notes = " Created doing test ";
$color = '';
$options = new stdClass();
$options->requirement_mgmt = 1;
$options->priority_mgmt = 1;
$options->automated_execution = 1;

$active=1;
$is_public=1;

$namePrefix = 'TPX :: ';
$name = uniqid($namePrefix,true);
$tcasePrefix = uniqid('',false);
//$new_id = $tproject_mgr->create($name,$color,$options,$notes,$active,$tcasePrefix,$is_public);
//
//$name = $namePrefix . $new_id;
//$tcasePrefix = $namePrefix . $new_id;
//
//$tproject_mgr->update($new_id, $name, $color, $options->requirement_mgmt, 
//                      $options->priority_mgmt, $options->automated_execution, 
//                      $notes,$active,$tcasePrefix,$is_public);
//
//new dBug($tproject_mgr->get_by_id($new_id));
//die();

$new_id = 1157;
$tproject_mgr->copy_as(9,$new_id,1);
die();


// getKeywords($testproject_id,$keywordID = null)
$tproject_id=1;
echo "<pre> testproject - getKeywords(\$testproject_id,\$keywordID = null)";echo "</pre>";
echo "<pre>               getKeywords($tproject_id)";echo "</pre>";
$keywords=$tproject_mgr->getKeywords($tproject_id);
new dBug($keywords);

$tproject_id=1;
echo "<pre> testproject - get_first_level_test_suites($tproject_id,$mode='simple')";echo "</pre>";
echo "<pre>               get_first_level_test_suites($tproject_id,$mode='simple')";echo "</pre>";
$info=$tproject_mgr->get_first_level_test_suites($tproject_id,$mode='simple');
new dBug($info);
die();

echo "<pre> testproject - get_keywords_map(\$testproject_id)";echo "</pre>";
$tproject_id=1;
echo "<pre>               get_keywords_map($tproject_id)";echo "</pre>";
$keywords_map=$tproject_mgr->get_keywords_map($tproject_id);
new dBug($keywords_map);


echo "<pre> testproject - get_keywords_tcases(\$testproject_id, \$keyword_id=0)";echo "</pre>";
echo "<pre>               get_keywords_tcases($tproject_id)";echo "</pre>";
$keywords_tcases=$tproject_mgr->get_keywords_tcases($tproject_id);
new dBug($keywords_tcases);


echo "<pre> testproject - get_linked_custom_fields(\$id,\$node_type=null)";echo "</pre>";
echo "<pre>               get_linked_custom_fields($tproject_id)";echo "</pre>";
$linked_custom_fields=$tproject_mgr->get_linked_custom_fields($tproject_id);
new dBug($linked_custom_fields);


echo "<pre> testproject - gen_combo_test_suites(\$id,\$exclude_branches=null,\$mode='dotted')";echo "</pre>";
echo "<pre>               gen_combo_test_suites($tproject_id,null,'dotted')";echo "</pre>";
$combo_test_suites=$tproject_mgr->gen_combo_test_suites($tproject_id,null,'dotted');
new dBug($combo_test_suites);

echo "<pre>               gen_combo_test_suites($tproject_id,null,'dotted')";echo "</pre>";
$combo_test_suites=$tproject_mgr->gen_combo_test_suites($tproject_id,null,'array');
new dBug($combo_test_suites);


echo "<pre> testproject - getReqSpec(\$testproject_id, \$id = null)";echo "</pre>";
echo "<pre>               getReqSpec($tproject_id)";echo "</pre>";
$requirement_spec=$tproject_mgr->getReqSpec($tproject_id);
new dBug($requirement_spec);

$srs_id=2;
echo "<pre>               getReqSpec(\$tproject_id,\$srs_id)";echo "</pre>";
echo "<pre>               getReqSpec($tproject_id,$srs_id)";echo "</pre>";
$requirement_spec=$tproject_mgr->getReqSpec($tproject_id,$srs_id);
new dBug($requirement_spec);


$srs_title='SRS2';
echo "<pre> testproject - get_srs_by_title(\$testproject_id,\$title,\$ignore_case=0)";echo "</pre>";
echo "<pre>               get_srs_by_title($tproject_id,$srs_title)";echo "</pre>";
$srs_by_title=$tproject_mgr->get_srs_by_title($tproject_id,$srs_title);
new dBug($srs_by_title);

// function get_srs_by_title($testproject_id,$title,$ignore_case=0)



/*
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
function get_linked_custom_fields($id,$node_type=null,$node_id=null) 
*/

?>
