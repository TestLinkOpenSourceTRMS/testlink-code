<?php

/**
 * 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource $RCSfile: reqOverview.php,v $
 * @version $$
 * @modified $$
 * @author asimon
 *
 * List requirements with (or without) Custom Field Data in an ExtJS Table.
 * See BUGID 3227 for a more detailed description of this feature.
 * 
 * rev:
 * 20100309 - asimon - initial commit
 * 		
 */

require_once("../../config.inc.php");
require_once("common.php");
require_once('exttable.class.php');
testlinkInitPage($db,false,false,"checkRights");

$cfield_mgr = new cfield_mgr($db);
$templateCfg = templateConfiguration();
$tproject_mgr = new testproject($db);
$req_mgr = new requirement_mgr($db);
$tree_mgr = new tree($db);

$args = init_args($tproject_mgr);
$gui = init_gui($args);

$glue_char = config_get('gui_title_separator_1');
$msg_key = 'no_linked_req_cf';
$charset = config_get('charset');


if (isset($args->all_versions)) {
	$gui->all_versions = $args->all_versions;
}


if($tproject_mgr->count_all_requirements($args->tproject_id) > 0)
{
	// get type labels
	$type_labels = init_labels(config_get('req_cfg')->type_labels);
	$status_labels = init_labels(config_get('req_status'));
	
	$reqIDs = array();
	$tproject_mgr->get_all_requirement_ids($args->tproject_id, $reqIDs);
	$gui->reqIDs = $reqIDs;
	
	$gui->cfields = $cfield_mgr->get_linked_cfields_at_design($args->tproject_id, 1, null, 'requirement',
                                                                 null, 'name');
    
	if(!is_null($gui->cfields)) {
		foreach($gui->cfields as $key => $values) {
			$cf_place_holder['cfields'][$key]='';
		}
	}
    
	$results = array();    
	
	foreach($reqIDs as $id) {
		
		// now get the rest of information for this requirement
		if (isset($args->all_versions)) {
			$req = $req_mgr->get_by_id($id, requirement_mgr::ALL_VERSIONS);
		} else {
			$req = $req_mgr->get_by_id($id, requirement_mgr::LATEST_VERSION);
			// see BUGID 3254:
			// above function doesn't work as expected, therefore I delete older versions manually:
			$req = array(0 => $req[0]); // <-- TODO this line can be deleted when above function is fixed
		}
		
		$fields = $req_mgr->get_linked_cfields($id);
    	
		// coverage data
		$current = count($req_mgr->get_coverage($id));

		// create the link to display
		$title = $req[0]['req_doc_id'] . $glue_char . $req[0]['title'];
		$linked_title = '<a href="javascript:openLinkedReqWindow(' . $id . ')">' . 
							htmlentities($title, ENT_COMPAT, $charset) . '</a>';
		
		// reqspec-"path" to requirement
		$path = $tree_mgr->get_path($req[0]['srs_id']);
		foreach ($path as $key => $p) {
			$path[$key] = $p['name'];
		}
		$path = htmlentities(implode("/", $path), ENT_COMPAT, $charset);
			
		foreach($req as $version) {
			
			// get content for each row to display
	    	$result = array();
	    	
	    	$result[] = $path;
	    	$result[] = $linked_title;	    	
			$result[] = $version['version'];
	    	
			// coverage
	    	$expected = $version['expected_coverage'];
	    	if ($expected != 0) {
	    		$percentage = round(100 / $expected * $current, 2);
				$coverage_string = "{$percentage}% ({$current}/{$expected})";
	    	} else {
	    		$coverage_string = "100% (0/0)";
	    	}
	    	$coverage = '<div id="tooltip-' . $id . '">' . $coverage_string . '</div>';
			$result[] = $coverage;

			$result[] = $type_labels[$version['type']];
			$result[] = $status_labels[$version['status']];
			
			// get custom field values for this req
			foreach ($fields as $cf) {
	    		$result[] = htmlentities($cf['value'], ENT_COMPAT, $charset);
	    	}
	    	
	    	$results[] = $result;
    	}
    }

    if(($gui->row_qty = count($results)) > 0 ) {
    	    	
        $gui->pageTitle .= " - " . lang_get('match_count') . ": " . $gui->row_qty;
		
        // get column titles for the table
        $columns = array(
        	array('title' => lang_get('req_spec_short'), 'width' => 200),
        	array('title' => lang_get('title'), 'width' => 150),
        	array('title' => lang_get('version'), 'width' => 50),
        	lang_get('th_coverage'),
	        lang_get('type'),
	        lang_get('status')	        
	        );

	    foreach($gui->cfields as $cf) {
	    	$columns[] = htmlentities($cf['label'], ENT_COMPAT, $charset);
	    }
	    
	    // create table object, fill it with columns and row data and give it a title
	    $matrix = new tlExtTable($columns, $results);
        $matrix->title = lang_get('requirements');        
        $gui->tableSet= array($matrix);
    } else {
    	$gui->warning_msg = lang_get($msg_key);
    }
}

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/**
 * initialize user input
 * 
 * @param resource &$tproject_mgr reference to testproject manager
 * @return array $args array with user input information
 */
function init_args(&$tproject_mgr)
{
	$args = new stdClass();

	if (isset($_REQUEST['all_versions'])) {
		$args->all_versions = true;
	}	
	
	$args->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
	$args->tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : '';

	if($args->tproject_id > 0) {
		$tproject_info = $tproject_mgr->get_by_id($args->tproject_id);
		$args->tproject_name = $tproject_info['name'];
		$args->tproject_description = $tproject_info['notes'];
	}
	
	return $args;
}


/**
 * initialize GUI
 * 
 * @param stdClass $argsObj reference to user input
 * @return stdClass $gui gui data
 */
function init_gui(&$argsObj) {
	$gui = new stdClass();
	
	$gui->pageTitle = lang_get('caption_req_overview');
	$gui->warning_msg = '';
	$gui->tproject_name = $args->tproject_name;
	$gui->tplan_name = $args->tplan_name;
	$gui->tplan_id = $args->tplan_id;
	
	return $gui;
}


/*
 * rights check function for testlinkInitPage
 */
function checkRights(&$db, &$user)
{
	return $user->hasRight($db,'testplan_metrics');
}
?>