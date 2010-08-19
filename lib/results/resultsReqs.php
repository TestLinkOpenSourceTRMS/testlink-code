<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @filesource $RCSfile: resultsReqs.php,v $
 * @version $Revision: 1.27 $
 * @modified $Date: 2010/08/19 16:21:21 $ by $Author: asimon83 $
 * @author Martin Havlat
 * 
 * Report requirement based results
 * 
 * rev:
 * 20100819 - asimon - BUGIDs 3261, 3439, 3488, 3569, 3299, 3259, 3687: 
 *                     complete redesign/rewrite of requirement based report 
 * 20090506 - franciscom - requirements refactoring
 * 20090402 - amitkhullar - added TC version while displaying the Req -> TC Mapping 
 * 20090111 - franciscom - BUGID 1967 + improvements
 * 20060104 - fm - BUGID 0000311: Requirements based Report shows errors
 */

require_once("../../config.inc.php");
require_once("common.php");
require_once('requirements.inc.php');
require_once('exttable.class.php');
testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();
$tproject_mgr = new testproject($db);
$tplan_mgr = new testplan($db);
$req_mgr = new requirement_mgr($db);
$req_spec_mgr = new requirement_spec_mgr($db);

$glue_char = config_get('gui_title_separator_1');
$msg_key = 'no_linked_req';
$charset = config_get('charset');
$req_cfg = config_get('req_cfg');
$req_spec_cfg = config_get('req_spec_cfg');
$results_cfg = config_get('results');
$coverage_algorithm = $req_cfg->coverageStatusAlgorithm;
$coverage_enabled = $req_cfg->expected_coverage_management;
$external_req_mgmt = $req_cfg->external_req_management;
$req_type_labels = init_labels($req_cfg->type_labels);
$req_spec_type_labels = init_labels($req_spec_cfg->type_labels);
$status_labels = init_labels($req_cfg->status_labels);
$labels = array('requirements' => lang_get('requirements'),
                'type' => lang_get('type'),
                'na' => lang_get('not_aplicable'),
                'req_availability' => lang_get('req_availability'));

$status_code_map = array();
foreach ($results_cfg['status_label_for_exec_ui'] as $status => $label) {
	$status_code_map[$status] = $results_cfg['status_code'][$status];
}

$code_status_map = array_flip($status_code_map);
foreach ($code_status_map as $code => $status) {
	$code_status_map[$code] = array('label' => lang_get($results_cfg['status_label'][$status]),
	                                'long_label' => lang_get("req_title_" . $status),
	                                'status' => $status,
	                                'css_class' => $status . '_text');
}

$eval_status_map = $code_status_map;
$eval_status_map['partially_passed'] = array('label' => lang_get('partially_passed'),
                                             'long_label' => lang_get('partially_passed_reqs'),
                                             'css_class' => 'not_run_text');
$eval_status_map['uncovered'] = array('label' => lang_get('uncovered'),
                                      'long_label' => lang_get('uncovered_reqs'),
                                      'css_class' => 'not_run_text');

$args = init_args($tproject_mgr);
$gui = init_gui($args);

$req_ids = $tproject_mgr->get_all_requirement_ids($args->tproject_id);
$req_spec_map = array();
$tc_ids = array();
$testcases = array();


// first step: get the requirements and linked testcases with which we have to work,
// order them into $req_spec_map by spec
if (count($req_ids)) {		
	foreach($req_ids as $id) {
		// get the information for this requirement
		$req = $req_mgr->get_by_id($id, requirement_mgr::LATEST_VERSION);
		$req_info = $req[0];
		$spec_id = $req_info['srs_id'];
				
		// if req is "usable" (either finished status or all requested) add it
		if (!$args->show_only_finished || $req_info['status'] == TL_REQ_STATUS_FINISH) {
			// coverage data
			$linked_tcs = (array) $req_mgr->get_coverage($id);
			$req_info['linked_testcases'] = $linked_tcs;
			
			if (!isset($req_spec_map[$spec_id])) {
				$spec_info = $req_spec_mgr->get_by_id($spec_id);
				$req_spec_map[$spec_id] = $spec_info;
				$req_spec_map[$spec_id]['requirements'] = array();
			}
			$req_spec_map[$spec_id]['requirements'][$id] = $req_info;

			foreach ($linked_tcs as $tc) {
				$tc_ids[] = $tc['id'];
			}
		}
	}
}


// second step: walk through req spec map, count/calculate, store results
if(count($req_spec_map)) {
	$filters = array('tcase_id' => $tc_ids);
	$options = null;
	$testcases = $tplan_mgr->get_linked_tcversions($args->tplan_id, $filters, $options);
	
	foreach ($req_spec_map as $req_spec_id => $req_spec_info) {
		$req_spec_map[$req_spec_id]['req_counters'] = array('total' => 0);
		
		foreach ($req_spec_info['requirements'] as $req_id => $req_info) {
			$req_spec_map[$req_spec_id]['requirements'][$req_id]['tc_counters'] = array('total' => 0);
			
			foreach ($req_info['linked_testcases'] as $key => $tc_info) {
				$tc_id = $tc_info['id'];
				if (isset($testcases[$tc_id]['exec_status'])) {
					$status = $testcases[$tc_id]['exec_status'];
					
					// if the counters for this status don't exist yet, initialize them with 0
					if (!isset($req_spec_map[$req_spec_id]['requirements'][$req_id]['tc_counters'][$status])) {
						$req_spec_map[$req_spec_id]['requirements'][$req_id]['tc_counters'][$status] = 0;
					}
					
					$req_spec_map[$req_spec_id]['requirements'][$req_id]['tc_counters'][$status] ++;
					$req_spec_map[$req_spec_id]['requirements'][$req_id]['tc_counters']['total'] ++;
				}
			}
			
			// evaluate this requirement by configured coverage algorithm
			$eval = evaluate_req($status_code_map, $coverage_algorithm,
			                     $req_spec_map[$req_spec_id]['requirements'][$req_id]['tc_counters']);
			$req_spec_map[$req_spec_id]['requirements'][$req_id]['evaluation'] = $eval;
			
			if (!isset($req_spec_map[$req_spec_id]['req_counters'][$eval])) {
				$req_spec_map[$req_spec_id]['req_counters'][$eval] = 0;
			}
			
			$req_spec_map[$req_spec_id]['req_counters'][$eval] ++;
			$req_spec_map[$req_spec_id]['req_counters']['total'] ++;
		}
	}
} else {
	$gui->warning_msg = lang_get($msg_key);
	$gui->tableSet = null;
}


// last step: build the table
if (count($req_spec_map)) {
	// headers
	$columns = array();
	$columns[] = array('title' => lang_get('req_spec_short'),
	                   'groupable' => 'true', 'hideable' => 'true', 'hidden' => 'true');
	$columns[] = array('title' => lang_get('title'), 'width' => 50,
	                   'groupable' => 'false', 'type' => 'text');
	$columns[] = array('title' => lang_get('version'), 'width' => 30, 'groupable' => 'false');
	
	if ($coverage_enabled) {
		$columns[] = array('title' => lang_get('th_coverage'), 'width' => 30, 'groupable' => 'false');
	}
	
	$columns[] = array('title' => lang_get('evaluation'), 'width' => 50, 'groupable' => 'false');
	$columns[] = array('title' => lang_get('type'), 'width' => 40, 'groupable' => 'false');
	
	// show status only if it was requested to show reqs with all statuses
	if (!$args->show_only_finished) {
		$columns[] = array('title' => lang_get('status'), 'width' => 40, 'groupable' => 'false');
	}
	
	foreach ($code_status_map as $code => $status) {
		$columns[] = array('title' => $status['label'], 'width' => 30, 'groupable' => 'false');
	}
	
	// complete progress
	$columns[] = array('title' => lang_get('progress'), 'width' => 40, 'groupable' => 'false');
	
	// data for rows
	$rows = array();
	
	foreach ($req_spec_map as $req_spec_id => $req_spec_info) {
		
		// build the evaluation data string and attache it to req spec name for table group feature
		$req_spec_description = build_req_spec_description($eval_status_map, $req_spec_info,
		                                                   $external_req_mgmt, $labels,
		                                                   $req_spec_type_labels);
				
		foreach ($req_spec_info['requirements'] as $req_id => $req_info) {
			$single_row = array();
			
			// first column (grouped, not shown) is req spec information
			$path = $req_mgr->tree_mgr->get_path($req_info['srs_id']);
			foreach ($path as $key => $val) {
				$path[$key] = $val['name'];
			}
			$path = implode("/", $path);
			$single_row[] = htmlentities($path, ENT_QUOTES, $charset) . $req_spec_description;
			
			// create the linked title to display
			$title = htmlentities($req_info['req_doc_id'], ENT_QUOTES, $charset) . $glue_char . 
			         htmlentities($req_info['title'], ENT_QUOTES, $charset);
			$linked_title = '<!-- ' . $title . ' -->' . 
			                '<a href="javascript:openLinkedReqWindow(' . $req_id . ')">' . 
			                $title . '</a>';
			
			$single_row[] = $linked_title;
	    	
	    	// version number
	    	$version_num = $req_info['version'];
	    	$padded_version_num = sprintf("%010d", $version_num);
	    	$version_str = "<!-- $padded_version_num -->$version_num";
			$single_row[] = $version_str;
	    	
			// coverage
			if ($coverage_enabled) {
				$expected_coverage = $req_info['expected_coverage'];
				$current = count($req_info['linked_testcases']);
		    	if ($expected_coverage) {
					$coverage_string = "<!-- -1 -->" . lang_get('not_aplicable') . " ($current/0)";
			    	if ($expected_coverage) {
			    		$percentage = 100 / $expected_coverage * $current;
			    		$coverage_string = comment_percentage($percentage) .
						                   " ({$current}/{$expected_coverage})";
			    	}
			    	$single_row[] = $coverage_string;
				} else {
					// no expected value, no percentage, just absolute number
					$single_row[] = $current;
				}
			}
			
			$eval = $req_info['evaluation'];
			$colored_eval = '<span class="' . $eval_status_map[$eval]['css_class'] . '">' . 
			                $eval_status_map[$eval]['label'] . '</span>';
			$single_row[] = $colored_eval;
			
			$single_row[] = $req_type_labels[$req_info['type']];
			
			// show status only if it was requested to show reqs with all statuses
			if (!$args->show_only_finished) {
				$single_row[] = $status_labels[$req_info['status']];
			}
			
			// add count and percentage for each possible status and progress
			$progress_percentage = 0;
			
			$total_count = ($coverage_enabled && $expected_coverage > 0) ?
			               $expected_coverage : $req_info['tc_counters']['total'];
			
			foreach ($status_code_map as $status => $code) {
				$count = isset($req_info['tc_counters'][$code]) ? $req_info['tc_counters'][$code] : 0;
				$value = 0;
				
				if ($total_count) {
					$percentage = (100 / $total_count) * $count;
		    		$percentage_string = comment_percentage($percentage) .
					                     " ({$count}/{$total_count})";
					
		    		$value = $percentage_string;
					
					// if status is not "not run", add it to progress percentage
					if ($code != $status_code_map['not_run']) {
						$progress_percentage += $percentage;
					}
				} else {
					$value = $labels['na'];
				}
				
				$single_row[] = $value;
			}
			
			// complete progress
			$single_row[] = ($total_count) ? comment_percentage($progress_percentage) : $labels['na'];
			
			$rows[] = $single_row;
		}
	}
		
	// create table object
	$matrix = new tlExtTable($columns, $rows, 0);
	$matrix->title = $gui->pageTitle;
	// group by Req Spec and hide that column
	$matrix->groupByColumn = 0;
	// sort descending by progress percentage, last column
	$matrix->sortByColumn = count($columns) - 1;
	$matrix->sortDirection = 'DESC';
	//show long text content in multiple lines
	$matrix->addCustomBehaviour('text', array('render' => 'columnWrap'));
	$matrix->toolbar_show_all_columns_button = false;
	$matrix->showGroupItemsCount = false;
	$gui->tableSet = array($matrix);
}


$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/**
 * Builds a descriptive string which will be added to the grouping column of the ExtJS table
 * for each req spec to see information about the requirements in this spec and their status.
 * @author Andreas Simon
 * @param array $evalcode_status_map
 * @param array $spec_info
 * @param bool $ext_mgmt_enabled
 * @param array $labels
 * @param array $spec_types
 * @return string description
 */
function build_req_spec_description(&$evalcode_status_map, &$spec_info, $ext_mgmt_enabled, 
                                    &$labels, &$spec_types) {
	$description = "";
	$space = "&nbsp;&nbsp;&nbsp;&nbsp;";
	
	$req_count = $spec_info['req_counters']['total'];
	$external_count = isset($spec_info['total_req']) ? $spec_info['total_req'] : 0;
	
	$description .= "<br/>" . $space . $labels['type'] . ": " . $spec_types[$spec_info['type']];
	
	if ($ext_mgmt_enabled && $external_count) {
		$description .= "<br/>{$space}{$labels['req_availability']}: " . 
		                "({$req_count}/{$external_count})";
	} else {
		$description .= "<br/>" . $space . $labels['requirements'] . ": " . $req_count;
	}
	
	foreach ($evalcode_status_map as $status_code => $status_info) {
		$count = isset($spec_info['req_counters'][$status_code]) ?
		         $spec_info['req_counters'][$status_code] : 0;
		if ($count) {
			$description .= "<br/>" . $space . $status_info['long_label'] . ": " . $count;
		}
	}
	
	return $description;
}


/**
 * Get the evaluation status of a single requirement.
 * @author Andreas Simon
 * @param array $status_code_map
 * @param array $algorithm_cfg
 * @param array $counters
 * @return string evaluation
 */
function evaluate_req(&$status_code_map, &$algorithm_cfg, &$counters) {
	
	$evaluation = 'partially_passed';
	$break = false;
	
	if ($counters['total'] == 0) {
		$evaluation = 'uncovered';
		$break = true;
	}
	
	if (!$break) {
		foreach ($algorithm_cfg['checkOrder'] as $checkKey) {
			foreach ($algorithm_cfg['checkType'][$checkKey] as $status2check) {
				$code = $status_code_map[$status2check];
				$count = isset($counters[$code]) ? $counters[$code] : 0;
				if ($checkKey == 'atLeastOne' && $count) {
					$evaluation = $code;
					$break = true;
					break;
				}
				if($checkKey == 'all' && $count == $counters['total']) {
					$evaluation = $status_code_map[$status2check];
					$break = true;
					break;
				}
			}  
			if($break) {
				break;
			}
		}
	}
	return $evaluation;
}


/**
 * Transform a numerical value to a string with its value as a padded html comment 
 * to make sorting on ExtJS table object easier
 * 
 * @author Andreas Simon
 * @param int $percentage
 * @return string
 */
function comment_percentage($percentage) {
	$percentage = round($percentage, 2);
    $padded_percentage = sprintf("%010d", $percentage);
	$string = "<!-- {$padded_percentage} --> {$percentage}% ";
	return $string;
}


/**
 * initialize user input
 * 
 * @author Andreas Simon
 * @param resource &$tproject_mgr reference to testproject manager
 * @return array $args array with user input information
 */
function init_args(&$tproject_mgr)
{
	$args = new stdClass();

	$args->show_all = isset($_REQUEST['show_all']) ? 1 : 0;
	
	$show_only_finished = isset($_REQUEST['show_only_finished']) ? true : false;
	$show_only_finished_hidden = isset($_REQUEST['show_only_finished_hidden']) ? true : false;
	if ($show_only_finished) {
		$selection = true;
	} else if ($show_only_finished_hidden) {
		$selection = false;
	} else if (isset($_SESSION['show_only_finished'])) {
		$selection = $_SESSION['show_only_finished'];
	} else {
		$selection = true;
	}
	$args->show_only_finished = $_SESSION['show_only_finished'] = $selection;
	
	$args->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
	$args->tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : null;

	$args->tplan_id = intval($_SESSION['resultsNavigator_testplanID']);
	
	$args->format = $_SESSION['resultsNavigator_format'];
	
	return $args;
}


/**
 * initialize GUI object
 * 
 * @author Andreas Simon
 * @param stdClass $argsObj reference to user input
 * @return stdClass $gui gui data
 */
function init_gui(&$argsObj) {
	$gui = new stdClass();
	
	$gui->pageTitle = lang_get('title_result_req_testplan');
	$gui->warning_msg = '';
	$gui->tproject_name = $argsObj->tproject_name;
	$gui->show_only_finished = $argsObj->show_only_finished;
	
	return $gui;
}


/**
 * Enter description here ...
 * @author Andreas Simon
 * @param unknown_type $db
 * @param unknown_type $user
 */
function checkRights(&$db,&$user)
{
	return $user->hasRight($db,'testplan_metrics');
}


// old code kept for reference:

//require_once("../../config.inc.php");
//require_once("common.php");
//require_once('requirements.inc.php');
//testlinkInitPage($db,true,false,"checkRights");
//
//$templateCfg = templateConfiguration();
//$tables = tlObjectWithDB::getDBTables(array('req_coverage','nodes_hierarchy','tcversions',
//                                            'requirements','req_versions'));
//
//$args = init_args();
//$gui = new stdClass();
//$gui->tproject_name = $args->tproject_name;
//$gui->allow_edit_tc = (has_rights($db,"mgt_modify_tc") == 'yes') ? 1 : 0;
//$gui->coverage = null;
//$gui->metrics =  null;
//
//// in this order will be displayed on report
//// IMPORTANT: values are keys in coverage map
//$gui->coverageKeys = config_get('req_cfg')->coverageStatusAlgorithm['displayOrder'];
//
//$tproject_mgr = new testproject($db);
//$tcasePrefix = $tproject_mgr->getTestCasePrefix($args->tproject_id);
//$gui->prefixStr = $tcasePrefix . config_get('testcase_cfg')->glue_character;
//$gui->pieceSep = config_get('gui_title_separator_1');
//
//$req_spec_mgr = new requirement_spec_mgr($db); 
//
////get list of available Req Specification
//// $gui->reqSpecSet = $tproject_mgr->getOptionReqSpec($args->tproject_id);
//$gui->reqSpecSet = $tproject_mgr->genComboReqSpec($args->tproject_id);
//$gui->reqSpecSet = ($reqSpecQty = count($gui->reqSpecSet)) > 0 ? $gui->reqSpecSet : null;
//
////set the first ReqSpec if not defined via request
//if($reqSpecQty > 0 && !$args->req_spec_id)
//{
//	reset($gui->reqSpecSet);
//	$args->req_spec_id = key($gui->reqSpecSet);
//	tLog('Set a first available SRS ID: ' . $args->req_spec_id);
//}
//
//$tplan_mgr = new testplan($db);
//$tplanInfo = $tplan_mgr->get_by_id($args->tplan_id);
//$gui->tplan_name = $tplanInfo["name"];
//$gui->withoutTestCase = '';
//$gui->req_spec_id = null;
//$gui->reqSpecName = '';
//
//if(!is_null($args->req_spec_id))
//{
//	$gui->req_spec_id = $args->req_spec_id;
//	$gui->reqSpecName = $gui->reqSpecSet[$gui->req_spec_id];
//
//    $opt = array('only_executed' => true);
//    // 20100309 - What about platforms ? franciscom 
//	$tcs = $tplan_mgr->get_linked_tcversions($args->tplan_id,$opt);
//	$execMap = getLastExecutions($db,$tcs,$args->tplan_id);
//	
//	// BUGID 1063
//    // 20090506 - franciscom - Requirements Refactoring
//	$sql = " SELECT MAX(REQV.version), REQ.id AS req_id, NH_REQ.name AS req_title,REQ.req_doc_id, " .
//	       " REQV.status AS req_status, " .
//	       " COALESCE(RC.testcase_id,0) AS testcase_id, NH.name AS testcase_name, " .
//	       " TCV.tc_external_id AS testcase_external_id,TCV.version AS testcase_version " .
//	       " FROM {$tables['requirements']} REQ" .
//	       " JOIN {$tables['nodes_hierarchy']} NH_REQ ON NH_REQ.id = REQ.id " .
//	       " JOIN {$tables['nodes_hierarchy']} NH_REQV ON NH_REQV.parent_id = REQ.id " .
//	       " JOIN {$tables['req_versions']} REQV ON REQV.id = NH_REQV.id AND REQV.active=1 " .
//	       " LEFT OUTER JOIN {$tables['req_coverage']}  RC ON REQ.id = RC.req_id " .
//	       " LEFT OUTER JOIN {$tables['nodes_hierarchy']} NH ON RC.testcase_id = NH.id " .
//	       " LEFT OUTER JOIN {$tables['nodes_hierarchy']} NHB ON NHB.parent_id = NH.id " .
//	       " LEFT OUTER JOIN {$tables['tcversions']} TCV ON TCV.id=NHB.id " .
//	       " WHERE REQV.status = '" . TL_REQ_STATUS_VALID . "' AND srs_id = {$args->req_spec_id}" .
//	       " GROUP BY REQ.id,NH_REQ.name,REQ.req_doc_id,REQV.status, " .
//	       " COALESCE(RC.testcase_id,0),NH.name,TCV.tc_external_id,TCV.version";
//
//	// Why CUMULATIVE ?
//	// because can be linked to different test cases ?
//	$reqs = $db->fetchRowsIntoMap($sql,'req_id',database::CUMULATIVE);
//	new dBug($reqs);
//
//	$gui->metrics = $req_spec_mgr->get_metrics($args->req_spec_id);
//
//	$coverage = getReqCoverage($db,$reqs,$execMap);                                                               
//	$gui->coverage = $coverage['byStatus'];
//	$gui->withoutTestCase = $coverage['withoutTestCase'];
//                                                               
//	$gui->metrics['coveredByTestPlan'] = sizeof($coverage['withTestCase']);
//	$gui->metrics['uncoveredByTestPlan'] = $gui->metrics['expectedTotal'] - $gui->metrics['coveredByTestPlan'] - 
//	                                       $gui->metrics['notTestable'];
//}
//
//
//$smarty = new TLSmarty();
//$smarty->assign('gui',$gui);
//$smarty->display($templateCfg->template_dir . $templateCfg->default_template);
//
//
///**
// * 
// *
// */
//function init_args()
//{
//	$iParams = array("req_spec_id" => array(tlInputParameter::INT_N));
//	
//	$args = new stdClass();
//	R_PARAMS($iParams,$args);
//
//	$args->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
//    $args->tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : null;
//	$args->tplan_id = intval($_SESSION['resultsNavigator_testplanID']);
//	$args->format = $_SESSION['resultsNavigator_format'];
//	
//    return $args;
//}
//
//function checkRights(&$db,&$user)
//{
//	return $user->hasRight($db,'testplan_metrics');
//}
?>