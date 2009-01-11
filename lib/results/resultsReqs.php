<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @filesource $RCSfile: resultsReqs.php,v $
 * @version $Revision: 1.13 $
 * @modified $Date: 2009/01/11 17:13:52 $ by $Author: franciscom $
 * @author Martin Havlat
 * 
 * Report requirement based results
 *
 * 20090111 - franciscom - BUGID 1967
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
$gui->coverageKeys = array('passed','failed','blocked','not_run');


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
	       " TCV.tc_external_id " .
	       " FROM requirements REQ" .
	       " LEFT OUTER JOIN req_coverage RC ON REQ.id = RC.req_id " .
	       " LEFT OUTER JOIN nodes_hierarchy NH ON RC.testcase_id=NH.id " .
	       " LEFT OUTER JOIN nodes_hierarchy NHB ON NHB.parent_id=NH.id " .
	       " LEFT OUTER JOIN tcversions TCV ON TCV.id=NHB.id " .
	       " WHERE status = '" . TL_REQ_STATUS_VALID . "' AND srs_id = {$args->req_spec_id}"; 

  // echo "<br>debug - <b><i>" . __FUNCTION__ . "</i></b><br><b>" . $sql . "</b><br>";
  	       
	$reqs = $db->fetchRowsIntoMap($sql,'req_id',1);
	$execMap = getLastExecutions($db,$tcs,$args->tplan_id);
	$gui->metrics = $req_spec_mgr->get_metrics($args->req_spec_id);
	$coveredReqs = 0;
	$gui->coverage = getReqCoverage($reqs,$execMap,$coveredReqs);
  // getNewReqCoverage($reqs,$execMap,$coveredReqs);                                                               
                                                               
	$gui->metrics['coveredByTestPlan'] = sizeof($coveredReqs);
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

function getNewReqCoverage($reqs,$execMap,&$coveredReqs)
{

  $resultsCfg=config_get('results');
  $status2check=array_keys($resultsCfg['status_label_for_exec_ui']);
 
  
  $coverage=$resultsCfg['status_label_for_exec_ui'];
  $status_counters=array();
  foreach($coverage as $status_code => $value)
  {
      $coverage[$status_code]=array();
      $status_counters[$resultsCfg['status_code'][$status_code]]=0;
  }
	$coveredReqs = null;
	$reqs_qty=count($reqs);
	//new dBug($coverage);
	//new dBug($status_counters);
	
	if($reqs_qty > 0)
	{
		foreach($reqs as $requirement_id => $req_tcase_set)
		{
			$req = array("id" => $id, "title" => "");
			foreach($status_counters as $key => $value)
			{
			    $status_counters[$key]=0;
			}

			$item_qty = count($req_tcase_set);
			if( $items_qty > 0 )
			{
				$coveredReqs[$requirement_id] = 1;
			}
				
			for($idx = 0; $idx < $item_qty; $idx++)
			{
			  //new dBug($req_tcase_set[$idx]);
			  $item_info=$req_tcase_set[$idx];
			  
			  // just to avoid useless assignments
			  if( $idx=0 )
			  {
			      $req['title']=$item_info['req_title'];  
			  } 

				// BUGID 1063
				if( $item_info['testcase_id'] > 0 )
				{
			     $req['tcList'][] = array("tcID" => $item_info['testcase_id'],"title" => $item_info['testcase_name']);
           $exec_status = $resultCfg['status_code']['not_run'];
				   if (isset($execMap[$execTc]) && sizeof($execMap[$execTc]))
				   {
				       $execInfo = end($execMap[$execTc]);
				   	   $exec_status = isset($execInfo['status']) ? $execInfo['status'] : $resultCfg['status_code']['not_run'];
				   }
				   $status_counters[$exec_status]++;
				   
        }
			} // for($idx = 0; $idx < $item_qty; $idx++)
			
			// if ($nFailed)
			// 	$arrCoverage['failed'][] = $req;
			// else if ($nBlocked)
			// 	$arrCoverage['blocked'][] = $req;
			// else if (!$nPassed)
			// 	$arrCoverage['not_run'][] = $req;
			// else if ($nPassed == $n)
			// 	$arrCoverage['passed'][] = $req;
			// else
			// 	$arrCoverage['failed'][] = $req;
		}
	}
	return $arrCoverage;
}



?>
