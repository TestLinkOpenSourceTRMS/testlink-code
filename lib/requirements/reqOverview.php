<?php

/**
 * 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @package TestLink
 * @author Andreas Simon
 * @copyright 2010, TestLink community
 * @version CVS: $Id: reqOverview.php,v 1.11 2010/05/17 09:33:51 mx-julian Exp $
 *
 * List requirements with (or without) Custom Field Data in an ExtJS Table.
 * See BUGID 3227 for a more detailed description of this feature.
 * 
 * rev:
 * 20100508 - franciscom - use of $req_cfg->status_labels
 * 20100325 - asimon - added html comments with padded numbers/strings for easier and
 *                     corrent sorting to columns title/version/coverage/relations
 * 20100323 - asimon - show columns for relations and coverage only if these features are enabled.
 *                     added number of requirement relations to table.
 * 20100312 - asimon - replaced "100%"-value (in case where req has no coverage) by N/A-string
 * 20100311 - asimon - fixed a little bug (only notice) when no cfields are defined
 * 20100310 - asimon - refactoring as requested
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

$args = init_args($tproject_mgr);
$gui = init_gui($args);

$glue_char = config_get('gui_title_separator_1');
$msg_key = 'no_linked_req';
$charset = config_get('charset');
$req_cfg = config_get('req_cfg');
$coverage_enabled = $req_cfg->relations->enable;
$relations_enabled = $req_cfg->expected_coverage_management;

$gui->reqIDs = $tproject_mgr->get_all_requirement_ids($args->tproject_id);

if(count($gui->reqIDs)) {
	
	// get type and status labels
	$type_labels = init_labels($req_cfg->type_labels);
	$status_labels = init_labels($req_cfg->status_labels);
	
	$gui->cfields = $cfield_mgr->get_linked_cfields_at_design($args->tproject_id, 1, null, 'requirement',
                                                                 null, 'name');
	if (!count($gui->cfields)) {
			// manage the case where no custom fields are defined
			$gui->cfields = array();
	}
		
    // array to gather table data row per row
	$rows = array();    
	
	foreach($gui->reqIDs as $id) {
		
		// now get the rest of information for this requirement
		$version_option = ($args->all_versions) ? requirement_mgr::ALL_VERSIONS : requirement_mgr::LATEST_VERSION; 
		$req = $req_mgr->get_by_id($id, $version_option);
		
		// BUGID 3254:
		// above function doesn't work as expected, therefore I delete older versions manually
		// this if statement can be deleted when function is fixed
		// if ($version_option == requirement_mgr::LATEST_VERSION) {
			// $req = array(0 => $req[0]);
		// }
		// seems to work now
		
		$fields = $req_mgr->get_linked_cfields($id);
		if (!count($fields)) {
			// manage the case where no custom fields are defined
			$fields = array();
		}
    	
		// coverage data
		$current = count($req_mgr->get_coverage($id));

		// number of relations, if feature is enabled
		if ($relations_enabled) {
			$relations = $req_mgr->count_relations($id);
			$padded_relations = sprintf("%010d", $relations);
			$relations = "<!-- $padded_relations -->" . $relations;
		}
		
		// create the link to display
		$title = htmlentities($req[0]['req_doc_id'], ENT_QUOTES, $charset) . $glue_char . 
					htmlentities($req[0]['title'], ENT_QUOTES, $charset);
		$linked_title = '<!-- ' . $title . ' -->' . //add html comment with title for easier sorting 
							'<a href="javascript:openLinkedReqWindow(' . $id . ')">' . 
							$title . '</a>';
		
		// reqspec-"path" to requirement
		$path = $req_mgr->tree_mgr->get_path($req[0]['srs_id']);
		foreach ($path as $key => $p) {
			$path[$key] = $p['name'];
		}
		$path = htmlentities(implode("/", $path), ENT_QUOTES, $charset);
			
		foreach($req as $version) {
			
			// get content for each row to display
	    	$result = array();
	    	
	    	/**
	    	 * IMPORTANT: 
	    	 * the order of following items in this array has to be
	    	 * the same as column headers are below!!!
	    	 * 
	    	 * should be:
	    	 * 1. path
	    	 * 2. title
	    	 * 3. version
	    	 * 4. coverage (if enabled)
	    	 * 5. type
	    	 * 6. status
	    	 * 7. relations (if enabled)
	    	 * 8. all custom fields in order of $fields
	    	 */
	    	
	    	$result[] = $path;
	    	$result[] = $linked_title;

	    	// version number
	    	$version_num = $version['version'];
	    	$padded_version_num = sprintf("%010d", $version_num);
	    	$version_str = "<!-- $padded_version_num -->$version_num";
			$result[] = $version_str;
	    	
			// coverage
			if ($coverage_enabled) {
		    	$expected = $version['expected_coverage'];
		    	$coverage_string = lang_get('not_aplicable') . " ($current/0)";
		    	if ($expected) {
		    		$percentage = round(100 / $expected * $current, 2);
		    		$padded_percentage = sprintf("%010d", $percentage); //bring all percentages to same length
					$coverage_string = "<!-- $padded_percentage --> {$percentage}% ({$current}/{$expected})";
		    	}
		    	$result[] = $coverage_string;
			}
			
			$result[] = $type_labels[$version['type']];
			$result[] = $status_labels[$version['status']];
			
			if ($relations_enabled) {
				$result[] = $relations;
			}
			
			// get custom field values for this req
			foreach ($fields as $cf) {
	    		$result[] = htmlentities($cf['value'], ENT_QUOTES, $charset);
	    	}
	    	
	    	$rows[] = $result;
    	}
    }
    
    if(($gui->row_qty = count($rows)) > 0 ) {
    	    	
        $gui->pageTitle .= " - " . lang_get('match_count') . ": " . $gui->row_qty;
		
        // get column header titles for the table
        
        /**
    	 * IMPORTANT: 
    	 * the order of following items in this array has to be
    	 * the same as row content above!!!
    	 * 
    	 * should be:
    	 * 1. path
    	 * 2. title
    	 * 3. version
    	 * 4. coverage (if enabled)
    	 * 5. type
    	 * 6. status
    	 * 7. relations (if enabled)
    	 * 8. then all custom fields in order of $fields
    	 */
        $columns = array();
        $columns[] = array('title' => lang_get('req_spec_short'), 'width' => 200);
        $columns[] = array('title' => lang_get('title'), 'width' => 150);
        $columns[] = array('title' => lang_get('version'), 'width' => 50);
        
        if ($coverage_enabled) {
	    	$columns[] = lang_get('th_coverage');
	    }
	            
        $columns[] = lang_get('type');
        $columns[] = lang_get('status');
	    
		if ($relations_enabled) {
	    	$columns[] = lang_get('th_relations');
	    }
        
	    foreach($gui->cfields as $cf) {
	    	$columns[] = htmlentities($cf['label'], ENT_QUOTES, $charset);
	    }

	    // create table object, fill it with columns and row data and give it a title
	    $matrix = new tlExtTable($columns, $rows);
        $matrix->title = lang_get('requirements');
        $gui->tableSet= array($matrix);
    }
} else {
    $gui->warning_msg = lang_get($msg_key);
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

	$args->all_versions = isset($_REQUEST['all_versions']);
	
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
	$gui->tproject_name = $argsObj->tproject_name;
	$gui->all_versions = $argsObj->all_versions;
	
	return $gui;
}


/*
 * rights check function for testlinkInitPage()
 */
function checkRights(&$db, &$user)
{
	return $user->hasRight($db,'testplan_metrics');
}
?>