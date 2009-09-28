<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @filesource $RCSfile: uncoveredTestCases.php,v $
 * @version $Revision: 1.8 $
 * @modified $Date: 2009/09/28 08:44:20 $ by $Author: franciscom $
 * @author Francisco Mancardi - francisco.mancardi@gmail.com
 * 
 * For a test project, list test cases that has no requirement assigned
 * 
 * rev: 20081109 - franciscom - BUGID 512
 *
 */
require_once("../../config.inc.php");
require_once("common.php");
require_once("specview.php");
testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();

$tables = tlObjectWithDB::getDBTables(array('req_coverage','nodes_hierarchy',
                                            'tcversions','node_types'));
$args = init_args();
$tproject_mgr = new testproject($db);

// get list of available Req Specification
// $reqSpec = $tproject_mgr->getOptionReqSpec($args->tproject_id);
$reqSpec = $tproject_mgr->genComboReqSpec($args->tproject_id);
$uncovered = null;
$gui = new stdClass();
$gui->items = null;
$gui->tproject_name = $args->tproject_name;
$gui->has_reqspec = count($reqSpec) > 0;
$gui->has_requirements = false;
$gui->has_tc = false;

if($gui->has_reqspec)
{
    // Check if at least one of these requirement spec are not empty.
    $reqSpecMgr = new requirement_spec_mgr($db);
    foreach($reqSpec as $reqSpecID => $name)
    {
   		if($gui->has_requirements = ($reqSpecMgr->get_requirements_count($reqSpecID) > 0))
        	break;
    }
    unset($reqSpecMgr);
}    
if($gui->has_requirements)
{    
    // get all test cases id (active/inactive) in test project
    $tcasesID = null; 
    $tproject_mgr->get_all_testcases_id($args->tproject_id,$tcasesID);  
    
    if(!is_null($tcasesID) && count($tcasesID) > 0)
    {
        $debugMsg = 'File: ' . basename(__FILE__) . ' - Line: ' . __LINE__ . ' - ';
		$sql = " /* $debugMsg */ " .
		       " SELECT NHA.id AS tc_id, NHA.name, NHA.parent_id AS testsuite_id," .
		       " NT.description, REQC.req_id " .
	           " FROM {$tables['nodes_hierarchy']} NHA " .
	           " JOIN {$tables['node_types']} NT ON NHA.node_type_id=NT.id " .
	           " LEFT OUTER JOIN {$tables['req_coverage']} REQC on REQC.testcase_id=NHA.id " .
	           " WHERE NT.description='testcase' AND NHA.id IN (" . implode(",",$tcasesID) . ") " .
	           " and REQC.req_id IS NULL " ;
        $uncovered = $db->fetchRowsIntoMap($sql,'tc_id');
   }
}


if($gui->has_tc = (!is_null($uncovered) && count($uncovered) > 0) )
{
    // Get external  ID
    $testSet = array_keys($uncovered);
    $inClause = implode(',',$testSet);
    $debugMsg = 'File: ' . basename(__FILE__) . ' - Line: ' . __LINE__ . ' - ';
    $sql = "/* $debugMsg */ " .
         " SELECT distinct NHA.id AS tc_id, TCV.tc_external_id " .
         " FROM {$tables['nodes_hierarchy']} NHA, " . 
         " {$tables['nodes_hierarchy']} NHB, " .
         " {$tables['tcversions']} TCV, {$tables['node_types']} NT " .
         " WHERE NHA.node_type_id=NT.id AND NHA.id=NHB.parent_id AND NHB.id=TCV.id " .
         " AND NHA.id IN ({$inClause})  AND NT.description='testcase' ";
    $external_id = $db->fetchRowsIntoMap($sql,'tc_id');
    foreach($external_id as $key => $value)
    {
        $uncovered[$key]['external_id'] = $value['tc_external_id'];  
    }
  	// $out = gen_spec_view($db,'uncoveredtestcases',$args->tproject_id,$args->tproject_id,null,
    //                    $uncovered,null,null,$testSet,1,0,0);
    $opt = array('write_button_only_if_linked' => 1);
    $filters = array('testcases' => $testSet);
    $out = gen_spec_view($db,'uncoveredtestcases',$args->tproject_id,$args->tproject_id,null,
                       $uncovered,null,$filters,$opt);
                       
    $gui->items = $out['spec_view'];
}

$tcase_cfg = config_get('testcase_cfg');
$gui->pageTitle = lang_get('report_testcases_without_requirement');
$gui->testCasePrefix = $tproject_mgr->getTestCasePrefix($args->tproject_id);
$gui->testCasePrefix .= $tcase_cfg->glue_character;
  
$smarty = new TLSmarty();
$smarty->assign('gui', $gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


function init_args()
{
	$args = new stdClass();
    $args->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
    $args->tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : '';
    
    return $args;
}

function checkRights(&$db,&$user)
{
	return $user->hasRight($db,'testplan_metrics');
}
?>