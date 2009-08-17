<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @filesource $RCSfile: resultsReqs.php,v $
 * @version $Revision: 1.21 $
 * @modified $Date: 2009/08/17 08:01:25 $ by $Author: franciscom $
 * @author Martin Havlat
 * 
 * Report requirement based results
 * 
 * rev:
 * 20090506 - franciscom - requirements refactoring
 * 20090402 - amitkhullar - added TC version while displaying the Req -> TC Mapping 
 * 20090111 - franciscom - BUGID 1967 + improvements
 * 20060104 - fm - BUGID 0000311: Requirements based Report shows errors 
 *
 * 
 */
require_once("../../config.inc.php");
require_once("common.php");
require_once('requirements.inc.php');
testlinkInitPage($db,true,false,"checkRights");

$templateCfg = templateConfiguration();
$tables = tlObjectWithDB::getDBTables(array('req_coverage','nodes_hierarchy',
                                            'tcversions','requirements'));

$args = init_args();
$gui = new stdClass();
$gui->tproject_name = $args->tproject_name;
$gui->allow_edit_tc = (has_rights($db,"mgt_modify_tc") == 'yes') ? 1 : 0;

$gui->coverage = null;
$gui->metrics =  null;

// in this order will be displayed on report
// IMPORTANT: values are keys in coverage map
$gui->coverageKeys = config_get('req_cfg')->coverageStatusAlgorithm['displayOrder'];

$tproject_mgr = new testproject($db);

$tcasePrefix = $tproject_mgr->getTestCasePrefix($args->tproject_id);
$gui->prefixStr = $tcasePrefix . config_get('testcase_cfg')->glue_character;
$gui->pieceSep = config_get('gui_title_separator_1');

$req_spec_mgr = new requirement_spec_mgr($db); 

//get list of available Req Specification
$gui->reqSpecSet = $tproject_mgr->getOptionReqSpec($args->tproject_id);

//set the first ReqSpec if not defined via request
if (!$args->req_spec_id && count($gui->reqSpecSet))
{
	reset($gui->reqSpecSet);
	$args->req_spec_id = key($gui->reqSpecSet);
	tLog('Set a first available SRS ID: ' . $args->req_spec_id);
}

$tplan_mgr = new testplan($db);
$tplanInfo = $tplan_mgr->get_by_id($args->tplan_id);
$gui->tplan_name = $tplanInfo["name"];

if(!is_null($args->req_spec_id))
{
    $opt = array('only_executed' => true);
	// $tcs = $tplan_mgr->get_linked_tcversions($args->tplan_id,null,0,1);
	$tcs = $tplan_mgr->get_linked_tcversions($args->tplan_id,$opt);
	
	// BUGID 1063
    // 20090506 - franciscom - Requirements Refactoring
	$sql = " SELECT DISTINCT REQ.id AS req_id, COALESCE(RC.testcase_id,0) AS testcase_id, " .
	       " NH_REQ.name AS req_title,status AS req_status, NH.name AS testcase_name, " .
	       " TCV.tc_external_id,TCV.version " .
	       " FROM {$tables['requirements']} REQ" .
	       " JOIN {$tables['nodes_hierarchy']} NH_REQ ON NH_REQ.id = REQ.id " .
	       " LEFT OUTER JOIN {$tables['req_coverage']}  RC ON REQ.id = RC.req_id " .
	       " LEFT OUTER JOIN {$tables['nodes_hierarchy']} NH ON RC.testcase_id = NH.id " .
	       " LEFT OUTER JOIN {$tables['nodes_hierarchy']} NHB ON NHB.parent_id = NH.id " .
	       " LEFT OUTER JOIN {$tables['tcversions']} TCV ON TCV.id=NHB.id " .
	       " WHERE status = '" . TL_REQ_STATUS_VALID . "' AND srs_id = {$args->req_spec_id}"; 

	$reqs = $db->fetchRowsIntoMap($sql,'req_id',database::CUMULATIVE);
	$execMap = getLastExecutions($db,$tcs,$args->tplan_id);
	$gui->metrics = $req_spec_mgr->get_metrics($args->req_spec_id);

	$coverage = getReqCoverage($db,$reqs,$execMap);                                                               
	$gui->coverage = $coverage['byStatus'];
	$gui->withoutTestCase = $coverage['withoutTestCase'];
                                                               
	$gui->metrics['coveredByTestPlan'] = sizeof($coverage['withTestCase']);
	$gui->metrics['uncoveredByTestPlan'] = $gui->metrics['expectedTotal'] - $gui->metrics['coveredByTestPlan'] - 
	                                       $gui->metrics['notTestable'];
}

$gui->req_spec_id = $args->req_spec_id;
$gui->reqSpecName = $gui->reqSpecSet[$gui->req_spec_id];

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


function init_args()
{
	$iParams = array("format" => array(tlInputParameter::INT_N),
		             "req_spec_id" => array(tlInputParameter::INT_N),
		             "tplan_id" => array(tlInputParameter::INT_N));

	$args = new stdClass();
	R_PARAMS($iParams,$args);

    $args->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
    $args->tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : null;

    return $args;
}

function checkRights(&$db,&$user)
{
	return $user->hasRight($db,'testplan_metrics');
}
?>