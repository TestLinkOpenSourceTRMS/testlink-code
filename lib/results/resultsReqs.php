<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @filesource $RCSfile: resultsReqs.php,v $
 * @version $Revision: 1.16 $
 * @modified $Date: 2009/04/09 11:00:30 $ by $Author: amkhullar $
 * @author Martin Havlat
 * 
 * Report requirement based results
 * 
 * rev:
 * 20090402 - amitkhullar - added TC version while displaying the Req -> TC Mapping 
 * 20090111 - franciscom - BUGID 1967 + improvements
 * 20060104 - fm - BUGID 0000311: Requirements based Report shows errors 
 *
 * 
 */
require_once("../../config.inc.php");
require_once("common.php");
require_once('requirements.inc.php');
testlinkInitPage($db);

$templateCfg = templateConfiguration();

$args = init_args();
$gui = new stdClass();
$gui->tproject_name = $args->tproject_name;
$gui->tplan_name = $args->tplan_name;
$gui->allow_edit_tc = ( has_rights($db,"mgt_modify_tc") == 'yes') ? 1 : 0;

$gui->coverage = null;
$gui->metrics =  null;

// in this order will be displayed on report
// IMPORTANT: values are keys in coverage map
$gui->coverageKeys = config_get('req_cfg')->coverageStatusAlgorithm['displayOrder'];

$tproject_mgr=new testproject($db);

$tcasePrefix = $tproject_mgr->getTestCasePrefix($args->tproject_id);
$gui->prefixStr = $tcasePrefix . config_get('testcase_cfg')->glue_character;
$gui->pieceSep = config_get('gui_title_separator_1');

$req_spec_mgr = new requirement_spec_mgr($db); 

//get list of available Req Specification
$gui->reqSpecSet = $tproject_mgr->getOptionReqSpec($args->tproject_id);

//set the first ReqSpec if not defined via $_GET
if (!$args->req_spec_id && count($gui->reqSpecSet))
{
	reset($gui->reqSpecSet);
	$args->req_spec_id = key($gui->reqSpecSet);
	tLog('Set a first available SRS ID: ' . $args->req_spec_id);
}

if(!is_null($args->req_spec_id))
{
	$tplan_mgr = new testplan($db);
	$tcs = $tplan_mgr->get_linked_tcversions($args->tplan_id,null,0,1);
	
	// BUGID 1063
	// $sql = " SELECT REQ.id, req_coverage.testcase_id,title,status, NH.name AS testcase_name " .
	//        " FROM requirements REQ" .
	//        " LEFT OUTER JOIN req_coverage ON REQ.id = req_coverage.req_id " .
	//        " LEFT OUTER JOIN nodes_hierarchy NH ON req_coverage.testcase_id=NH.id " .
	//        " WHERE status = '" . TL_REQ_STATUS_VALID . "' AND srs_id = {$args->req_spec_id}"; 

  // 
	// $sql = " SELECT DISTINCT REQ.id, RC.testcase_id,title,status, NH.name AS testcase_name, " .
	//        " TCV.tc_external_id " .
	//        " FROM requirements REQ " .
	//        " JOIN req_coverage RC ON REQ.id = RC.req_id " .
	//        " JOIN nodes_hierarchy NH ON RC.testcase_id=NH.id " .
	//        " JOIN nodes_hierarchy NHB ON NHB.parent_id=NH.id " .
	//        " JOIN tcversions TCV ON TCV.id=NHB.id " .
	//        " WHERE status = '" . TL_REQ_STATUS_VALID . "' AND srs_id = {$args->req_spec_id}"; 
  // 
  // $covered_reqs = $db->fetchRowsIntoMap($sql,'id',database::CUMULATIVE);
  // 
  // $exclude_id = 
	// $sql = " SELECT REQ.id " .
	//        " FROM requirements REQ " .
	//        " WHERE status = '" . TL_REQ_STATUS_VALID . "' AND srs_id = {$args->req_spec_id}"; 
  // 
  // $all_reqs = $db->fetchRowsIntoMap($sql,'id',database::CUMULATIVE);
  
	$sql = " SELECT DISTINCT REQ.id AS req_id, COALESCE(RC.testcase_id,0) AS testcase_id, " .
	       " title AS req_title,status AS req_status, NH.name AS testcase_name, " .
	       " TCV.tc_external_id,TCV.version " .
	       " FROM requirements REQ" .
	       " LEFT OUTER JOIN req_coverage RC ON REQ.id = RC.req_id " .
	       " LEFT OUTER JOIN nodes_hierarchy NH ON RC.testcase_id=NH.id " .
	       " LEFT OUTER JOIN nodes_hierarchy NHB ON NHB.parent_id=NH.id " .
	       " LEFT OUTER JOIN tcversions TCV ON TCV.id=NHB.id " .
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

$gui->req_spec_id=$args->req_spec_id;
$gui->reqSpecName=$gui->reqSpecSet[$gui->req_spec_id];

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);



/*
  function: init_args 

  args:
  
  returns: 

*/
function init_args()
{
    $args = new stdClass();

    $_REQUEST = strings_stripSlashes($_REQUEST);
    $args->req_spec_id = isset($_REQUEST['req_spec_id']) ? $_REQUEST['req_spec_id'] : null;
    $args->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
    $args->tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : null;
    $args->tplan_id = isset($_SESSION['testPlanId']) ? $_SESSION['testPlanId'] : 0;
    $args->tplan_name = isset($_SESSION['testPlanName']) ? $_SESSION['testPlanName'] : 0;
    
    return $args;
}
?>