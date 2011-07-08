<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @filesource	resultsReqs.php
 * @author 		Martin Havlat
 * 
 * Report requirement based results
 * 
 * @internal revisions
 * 20110207 - asimon - BUGID 4227 - Allow to choose status of requirements to be evaluated
 * 20110207 - Julian - BUGID 4228 - Add more requirement evaluation states
 * 20110207 - Julian - BUGID 4206 - Jump to latest execution for linked test cases
 * 20110207 - Julian - BUGID 4205 - Add Progress bars for a quick overview
 */

require_once("../../config.inc.php");
require_once("common.php");
require_once('requirements.inc.php');
require_once('exttable.class.php');
testlinkInitPage($db);

$templateCfg = templateConfiguration();
$tproject_mgr = new testproject($db);
$tplan_mgr = new testplan($db);
$req_mgr = new requirement_mgr($db);
$req_spec_mgr = new requirement_spec_mgr($db);
$platform_mgr = new tlPlatform($db);

$glue_char = config_get('gui_title_separator_1');
$glue_char_tc = config_get('testcase_cfg')->glue_character;

// BUGID 3439
$charset = config_get('charset');
$req_cfg = config_get('req_cfg');
$req_spec_cfg = config_get('req_spec_cfg');
$results_cfg = config_get('results');


$labels = init_labels( array('requirement' => null,'requirements' => null,
                			 'type' => null,'req_availability' => null,
      			          	 'linked_tcs' => null,'no_linked_tcs' => null,
                			 'goto_testspec' => null,'design' => null,
                			 'no_label_for_req_type'  => null,
                			 'na' => 'not_aplicable',
                             'no_srs' => 'no_srs_defined', 
                             'no_matching_reqs' => null,
                             'execution' => null,
                             'execution_history' => null));

$labels['req_types'] = init_labels($req_cfg->type_labels);
$labels['req_spec_types'] = init_labels($req_spec_cfg->type_labels);
$labels['status'] = init_labels($req_cfg->status_labels);                
               
$history_icon = TL_THEME_IMG_DIR . "history_small.png";
$exec_img = TL_THEME_IMG_DIR . "exec_icon.png";
$edit_icon = TL_THEME_IMG_DIR . "edit_icon.png";

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

// BUGID 4228 - add additional states for requirement evaluation
$eval_status_map['partially_passed'] = array('label' => lang_get('partially_passed'),
                                             'long_label' => lang_get('partially_passed_reqs'),
                                             'css_class' => 'passed_text');
$eval_status_map['uncovered'] = array('label' => lang_get('uncovered'),
                                      'long_label' => lang_get('uncovered_reqs'),
                                      'css_class' => 'not_run_text');
$eval_status_map['p_nfc'] = array('label' => lang_get('passed_nfc'),
                                  'long_label' => lang_get('passed_nfc_reqs'),
                                  'css_class' => 'passed_text');
$eval_status_map['f_nfc'] = array('label' => lang_get('failed_nfc'),
                                  'long_label' => lang_get('failed_nfc_reqs'),
                                  'css_class' => 'failed_text');
$eval_status_map['b_nfc'] = array('label' => lang_get('blocked_nfc'),
                                  'long_label' => lang_get('blocked_nfc_reqs'),
                                  'css_class' => 'blocked_text');
$eval_status_map['n_nfc'] = array('label' => lang_get('not_run_nfc'),
                                  'long_label' => lang_get('not_run_nfc_reqs'),
                                  'css_class' => 'not_run_text');
$eval_status_map['partially_passed_nfc'] = array('label' => lang_get('partially_passed_nfc'),
                                                 'long_label' => lang_get('partially_passed_nfc_reqs'),
                                                 'css_class' => 'passed_text');


// BUGID 4205 - add count for each status to show test progress
foreach ($eval_status_map as $key => $status) {
	$eval_status_map[$key]['count'] = 0;
}

$total_reqs = 0;

$args = init_args($tproject_mgr, $req_cfg);
checkRights($db,$_SESSION['currentUser'],$args);


$gui = init_gui($args);

$gui_open = config_get('gui_separator_open');
$gui_close = config_get('gui_separator_close');
$platforms = $platform_mgr->getLinkedToTestplanAsMap($args->tplan_id);
$gui->platforms = $platforms ? array(0 => $gui_open . lang_get('any') . $gui_close) + $platforms : null;


list($req_spec_map, $tc_ids) = get_req_info($tproject_mgr, $args, $req_mgr, $req_spec_mgr, $gui, $labels, $total_reqs);


if(count($req_spec_map)) {
	list($req_spec_map, $testcases) = calculate($req_spec_map, $args, $tplan_mgr, $tc_ids, $status_code_map, $req_cfg);
}


if (count($req_spec_map)) {

	$columns = build_columns($args, $code_status_map, $req_cfg, $results_cfg, $labels, $eval_status_map);
	$rows = build_rows($args, $status_code_map, $tproject_mgr, $req_spec_map, $req_mgr, $edit_icon,
	                   $glue_char, $charset, $req_cfg, $labels, $eval_status_map, 
	                   $glue_char_tc, $testcases, $exec_img, $history_icon);
	
	// create table object
	$matrix = new tlExtTable($columns, $rows, 'tl_table_results_reqs');
	$matrix->title = $gui->pageTitle;
	
	// group by Req Spec and hide that column
	$matrix->setGroupByColumnName(lang_get('req_spec_short'));
	
	// sort descending by progress percentage
	$matrix->setSortByColumnName(lang_get('progress'));
	$matrix->sortDirection = 'DESC';
	
	//show long text content in multiple lines
	$matrix->addCustomBehaviour('text', array('render' => 'columnWrap'));
	
	//define toolbar
	$matrix->toolbarShowAllColumnsButton = true;
	$matrix->showGroupItemsCount = false;
	
	$gui->tableSet = array($matrix);
}


$gui->summary = $eval_status_map;
$gui->total_reqs = $total_reqs;

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


function get_req_info($tproject_mgr, $args, $req_mgr, $req_spec_mgr, &$gui, $labels, &$total_reqs) {
	$req_ids = $tproject_mgr->get_all_requirement_ids($args->tproject_id);
		
	$req_spec_map = array();
	$tc_ids = array();

	// first step: get the requirements and linked testcases with which we have to work,
	// order them into $req_spec_map by spec
	if (count($req_ids)) {
		foreach($req_ids as $id) {
			// get the information for this requirement
			$req = $req_mgr->get_by_id($id, requirement_mgr::LATEST_VERSION);
			$req_info = $req[0];
			$spec_id = $req_info['srs_id'];

			// if req is "usable" (has one of the selected states) add it
			if (in_array($req_info['status'], $args->states_to_show->selected, true)
			    || in_array("0", $args->states_to_show->selected, true)) {
				// coverage data
				$linked_tcs = (array) $req_mgr->get_coverage($id);
				$req_info['linked_testcases'] = $linked_tcs;
					
				if (!isset($req_spec_map[$spec_id])) {
					$spec_info = $req_spec_mgr->get_by_id($spec_id);
					$req_spec_map[$spec_id] = $spec_info;
					$req_spec_map[$spec_id]['requirements'] = array();
				}
				$total_reqs ++;
				$req_spec_map[$spec_id]['requirements'][$id] = $req_info;

				foreach ($linked_tcs as $tc) {
					$tc_ids[] = $tc['id'];
				}
			}
		}
		// BUGID 3439
		if (!count($req_spec_map)) {
			$gui->warning_msg = $labels['no_matching_reqs'];
		}
	} else {
		$gui->warning_msg = $labels['no_srs'];
	}
	
	return array($req_spec_map, $tc_ids);
}


function calculate($req_spec_map, $args, $tplan_mgr, $tc_ids, $status_code_map, $req_cfg) {
	// BUGID 3439
	$testcases = array();
	if (count($tc_ids)) {
		$filters = array('tcase_id' => $tc_ids);
		// BUGID 3856
		if ($args->platform != 0) {
			$filters['platform_id'] = $args->platform;
		}
		$options = array('last_execution' => true, 'output' => 'map');
		$testcases = $tplan_mgr->get_linked_tcversions($args->tplan_id, $filters, $options);
	}

	foreach ($req_spec_map as $req_spec_id => $req_spec_info) {
		$req_spec_map[$req_spec_id]['req_counters'] = array('total' => 0);
		
		foreach ($req_spec_info['requirements'] as $req_id => $req_info) {
			$req_spec_map[$req_spec_id]['requirements'][$req_id]['tc_counters'] = array('total' => 0);
			
			// add coverage for more detailed evaluation
			$req_spec_map[$req_spec_id]['requirements'][$req_id]['tc_counters']['expected_coverage'] = 
				$req_spec_map[$req_spec_id]['requirements'][$req_id]['expected_coverage'];
			
			foreach ($req_info['linked_testcases'] as $key => $tc_info) {
				$tc_id = $tc_info['id'];
				
				// BUGID 3964
				$req_spec_map[$req_spec_id]['requirements'][$req_id]['tc_counters']['total'] ++;
				
				if (isset($testcases[$tc_id]['exec_status'])) {
					$status = $testcases[$tc_id]['exec_status'];
					
					// if the counters for this status don't exist yet, initialize them with 0
					if (!isset($req_spec_map[$req_spec_id]['requirements'][$req_id]['tc_counters'][$status])) {
						$req_spec_map[$req_spec_id]['requirements'][$req_id]['tc_counters'][$status] = 0;
					}
					
					$req_spec_map[$req_spec_id]['requirements'][$req_id]['tc_counters'][$status] ++;
					
					// BUGID 3964
					//$req_spec_map[$req_spec_id]['requirements'][$req_id]['tc_counters']['total'] ++;
				}
			}
			
			// evaluate this requirement by configured coverage algorithm
			$eval = evaluate_req($status_code_map, $req_cfg->coverageStatusAlgorithm,
			                     $req_spec_map[$req_spec_id]['requirements'][$req_id]['tc_counters']);
			$req_spec_map[$req_spec_id]['requirements'][$req_id]['evaluation'] = $eval;
			
			if (!isset($req_spec_map[$req_spec_id]['req_counters'][$eval])) {
				$req_spec_map[$req_spec_id]['req_counters'][$eval] = 0;
			}
			
			$req_spec_map[$req_spec_id]['req_counters'][$eval] ++;
			$req_spec_map[$req_spec_id]['req_counters']['total'] ++;
		}
	}
	
	return array($req_spec_map, $testcases);
} 


function build_rows($args, $status_code_map, $tproject_mgr, $req_spec_map, $req_mgr, $edit_icon, $glue_char, 
                    $charset, $req_cfg, $labels, &$eval_status_map, $glue_char_tc, $testcases, $exec_img, $history_icon) {
	// data for rows
	$rows = array();
	$prefix = $tproject_mgr->getTestCasePrefix($args->tproject_id);
	
	foreach ($req_spec_map as $req_spec_id => $req_spec_info) {		
		// build the evaluation data string and attache it to req spec name for table group feature
		$req_spec_description = build_req_spec_description($eval_status_map, $req_spec_info,
		                                                   $req_cfg->external_req_management, $labels);
				
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
			
			$edit_link = "<a href=\"javascript:openLinkedReqWindow({$args->tproject_id}," . $req_id . ")\">" .
						 "<img title=\"{$labels['requirement']}\" src=\"{$edit_icon}\" /></a> ";

		    $link = $edit_link . $title;

			$single_row[] = $link;
	    	
	    	// version number
	    	$version_num = $req_info['version'];
	    	$padded_version_num = sprintf("%010d", $version_num);
	    	$version_str = "<!-- $padded_version_num -->$version_num";
			$single_row[] = $version_str;
	    	
			// coverage
			if ($req_cfg->expected_coverage_management) {
				$expected_coverage = $req_info['expected_coverage'];
				$current = count($req_info['linked_testcases']);
		    	if ($expected_coverage) {
					$coverage_string = "<!-- -1 -->" . $labels['na'] . " ($current/0)";
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
						
			// BUGID 4205 - add the count of each evaluation
			$eval_status_map[$eval]['count'] += 1;
			
			$colored_eval = '<span class="' . $eval_status_map[$eval]['css_class'] . '">' . 
			                $eval_status_map[$eval]['label'] . '</span>';
			$single_row[] = $colored_eval;
			
			// BUGID 4034
			$single_row[] = isset($labels['req_types'][$req_info['type']]) ? $labels['req_types'][$req_info['type']] : 
							sprintf($labels['no_label_for_req_type'],$req_info['type']);
			
			$single_row[] = $labels['status'][$req_info['status']];
			
			// add count and percentage for each possible status and progress
			$progress_percentage = 0;
			
			$total_count = ($req_cfg->expected_coverage_management && $expected_coverage > 0) ?
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
			
			$linked_tcs_with_status = '';
			if (count($req_info['linked_testcases']) > 0 ) {
				foreach($req_info['linked_testcases'] as $testcase) {
					$tc_id = $testcase['id'];
					
					$status = $status_code_map['not_run'];
					if(isset($testcases[$tc_id]['exec_status'])) {
						$status = $testcases[$tc_id]['exec_status'];
					}
					
					$colored_status = '<span class="' . $eval_status_map[$status]['css_class'] . '">[' . 
					                  $eval_status_map[$status]['label'] . ']</span>';
					
					$tc_name = $prefix . $glue_char_tc . $testcase['tc_external_id'] . $glue_char .
					           $testcase['name'];
					$exec_history_link = "<a href=\"javascript:openExecHistoryWindow({$tc_id});\">" .
					                     "<img title=\"{$labels['execution_history']}\" src=\"{$history_icon}\" /></a> ";
					$edit_link = "<a href=\"javascript:openTCEditWindow({$args->tproject_id},{$tc_id});\">" .
								 "<img title=\"{$labels['design']}\" src=\"{$edit_icon}\" /></a> ";

					
					$exec_link = "";
					if(isset($testcases[$tc_id]['exec_status'])) {
						$exec_link = "<a href=\"javascript:openExecutionWindow(" .
						             "{$tc_id}, {$testcases[$tc_id]['tcversion_number']}, {$testcases[$tc_id]['exec_on_build']} , " .
						             "{$testcases[$tc_id]['exec_on_tplan']}, {$testcases[$tc_id]['platform_id']});\">" .
						             "<img title=\"{$labels['execution']}\" src=\"{$exec_img}\" /></a> ";
					}

					$linked_tcs_with_status .= "{$exec_history_link} {$edit_link} {$exec_link} {$colored_status} {$tc_name}<br>";
				}
			} else  {
				$linked_tcs_with_status = $labels['no_linked_tcs'];
			}
			
			$single_row[] = $linked_tcs_with_status;
			
			$rows[] = $single_row;
		}
	}
	
	return $rows;
}


function build_columns($args, $code_status_map, $req_cfg, $results_cfg, $labels, $eval_status_map) {
	// headers
	$columns = array();
	$columns[] = array('title_key' => 'req_spec_short',
	                   'groupable' => 'true', 'hideable' => 'false', 'hidden' => 'true');
	$columns[] = array('title_key' => 'title', 'width' => 100,
	                   'groupable' => 'false', 'type' => 'text');
	$columns[] = array('title_key' => 'version', 'width' => 20, 'groupable' => 'false');
	
	if ($req_cfg->expected_coverage_management) {
		$columns[] = array('title_key' => 'th_coverage', 'width' => 60, 'groupable' => 'false');
	}
	
	$evaluation_for_filter = array();
	foreach($eval_status_map as $eval) {
		$evaluation_for_filter[] = $eval['label'];
	}
	
	$columns[] = array('title_key' => 'evaluation', 'width' => 80, 'groupable' => 'false',
	                   'filter' => 'ListSimpleMatch', 'filterOptions' => $evaluation_for_filter);
	$columns[] = array('title_key' => 'type', 'width' => 60, 'groupable' => 'false', 
	                   'filter' => 'list', 'filterOptions' => $labels['req_types']);
	$columns[] = array('title_key' => 'status', 'width' => 60, 'groupable' => 'false',
	                   'filter' => 'list', 'filterOptions' => $labels['status']);
	
	foreach ($code_status_map as $status) {
		$columns[] = array('title_key' => $results_cfg['status_label'][$status['status']], 
		                   'width' => 60, 'groupable' => 'false');
	}
	
	// complete progress
	$columns[] = array('title_key' => 'progress', 'width' => 60, 'groupable' => 'false');
	
	$columns[] = array('title_key' => 'linked_tcs', 'groupable' => 'false', 'width' => 250, 
	                   'type' => 'text');
	
	return $columns;
}


/**
 * Builds a descriptive string which will be added to the grouping column of the ExtJS table
 * for each req spec to see information about the requirements in this spec and their status.
 * 
 * @author Andreas Simon
 * @param array $evalcode_status_map
 * @param array $spec_info
 * @param bool $ext_mgmt_enabled
 * @param array $labels
 * @param array $spec_types
 * @return string description
 */
function build_req_spec_description(&$evalcode_status_map, &$spec_info, $ext_mgmt_enabled, $labels) {
	$description = "";
	$space = "&nbsp;&nbsp;&nbsp;&nbsp;";
	
	$req_count = $spec_info['req_counters']['total'];
	$external_count = isset($spec_info['total_req']) ? $spec_info['total_req'] : 0;
	
	$description .= "<br/>" . $space . $labels['type'] . ": " . $labels['req_spec_types'][$spec_info['type']];
		
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
 * 
 * @author Andreas Simon
 * @param array $status_code_map
 * @param array $algorithm_cfg
 * @param array $counters
 * @return string evaluation
 */
function evaluate_req(&$status_code_map, &$algorithm_cfg, &$counters) {
	
	// check if requirement is fully covered (">= 100%")
	$fully_covered = ($counters['total'] >= $counters['expected_coverage']) ? true : false;
	
	// count all not run test cases of the requirement
	$not_run = $counters['total'];
	foreach($counters as $key => $counter) {
		if($key != "total" && $key != 'expected_coverage') {
			$not_run = $not_run - $counter;
		}
	}
	
	$evaluation = ($fully_covered) ? 'partially_passed' : 'partially_passed_nfc';
	$break = false;
	
	// BUGID 3964
	// if no test case is linked -> uncovered
	if ($counters['total'] == 0) {
		$evaluation = 'uncovered';
		$break = true;
	}
	
	// if at least one test case is linked and all linked test cases are not run -> not run
	if ($counters['total'] == $not_run && $counters['total'] > 0) {
		$evaluation = ($fully_covered) ? 'n' : 'n_nfc';
		$break = true;
	}
	
	if (!$break) {
		foreach ($algorithm_cfg['checkOrder'] as $checkKey) {
			foreach ($algorithm_cfg['checkType'][$checkKey] as $status2check) {
				$code = $status_code_map[$status2check];
				$count = isset($counters[$code]) ? $counters[$code] : 0;
				if ($checkKey == 'atLeastOne' && $count) {
					$evaluation = ($fully_covered) ? $code : $code . "_nfc";
					$break = true;
					break;
				}
				if($checkKey == 'all' && $count == $counters['total']) {
					$evaluation = ($fully_covered) ? $code : $code . "_nfc";
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
function init_args(&$tproject_mgr, &$req_cfg)
{
	$args = new stdClass();

	$states_to_show = array(0 => "0");
	if (isset($_REQUEST['states_to_show'])) {
		$states_to_show = $_REQUEST['states_to_show'];
	} else if (isset($_SESSION['states_to_show'])) {
		$states_to_show = $_SESSION['states_to_show'];
	}
		
	$args->states_to_show = new stdClass();
	$args->states_to_show->selected = $_SESSION['states_to_show'] = $states_to_show;
	
	// get configured statuses and add "any" string to menu
	$args->states_to_show->items = array(0 => "[" . lang_get('any') . "]") + 
	                              (array) init_labels($req_cfg->status_labels);
	
	$args->tproject_name = '';
	$args->tproject_id = isset($_REQUEST['tproject_id']) ? intval($_REQUEST['tproject_id']) : 0;
	if($args->tproject_id > 0)
	{
		$dummy = $tproject_mgr->get_by_id($args->tproject_id);
		$args->tproject_name = $dummy['name'];
	}

	// 20110626 - POTENTIAL PROBLEMS
	$args->tplan_id = intval($_SESSION['resultsNavigator_testplanID']);
	$args->format = $_SESSION['resultsNavigator_format'];

	// BUGID 3856
	// remember platform selection too
	$platform = 0;
	if (isset($_REQUEST['platform'])) {
		$platform = $_REQUEST['platform'];
	} 
	$args->platform = $platform;
	
	return $args;
}


/**
 * initialize GUI object
 * 
 * @author Andreas Simon
 * @param stdClass $argsObj reference to user input
 * @return stdClass $gui gui data
 */
function init_gui(&$argsObj) 
{
	$gui = new stdClass();

	$gui->tproject_id = $argsObj->tproject_id;
	$gui->pageTitle = lang_get('title_result_req_testplan');
	$gui->warning_msg = '';
	$gui->tproject_name = $argsObj->tproject_name;
	$gui->states_to_show = $argsObj->states_to_show;
	$gui->tableSet = null;
	$gui->selected_platform = $argsObj->platform;

	return $gui;
}


/**
 * checkRights
 *
 */
function checkRights(&$db,&$userObj,$argsObj)
{
	$env['tproject_id'] = isset($argsObj->tproject_id) ? $argsObj->tproject_id : 0;
	$env['tplan_id'] = isset($argsObj->tplan_id) ? $argsObj->tplan_id : 0;
	checkSecurityClearance($db,$userObj,$env,array('testplan_metrics'),'and');
}
?>