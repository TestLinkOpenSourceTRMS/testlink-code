<?php

/**
 * 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @package TestLink
 * @author Andreas Simon
 * @copyright 2010, TestLink community
 * @version CVS: $Id: reqOverview.php,v 1.37.2.2 2010/11/19 16:47:47 asimon83 Exp $
 *
 * List requirements with (or without) Custom Field Data in an ExtJS Table.
 * See BUGID 3227 for a more detailed description of this feature.
 * 
 * rev:
 *
 * 20101119 - asimon - BUGID 4038: clicking requirement link does not open req version
 * 20101116 - Julian - Added Author to Created column and Modifier to Last modified column
 * 20101020 - Julian - BUGID 3915 - added columns for creation and modification timestamp
 * 20101015 - Julian - used title_key for exttable columns instead of title to be able to use 
 *                     table state independent from localization
 * 20100921 - asimon - added datetime formatting and calendar week for date custom fields
 * 20100908 - Julian - BUGID 2877 -  Custom Fields linked to Req versions
 * 20100823 - Julian - table now uses a unique table id per test project
 * 20100822 - asimon - removal of magic numbers for default table sorting
 * 20100821 - asimon - replaced "show all versions" button by checkbox as requested per e-mail
 * 20100816 - Julian - added default sorting and grouping
 * 20100730 - asimon - added table ID (0) to constructor of ext table
 *                     (required by changes to ext table class to avoid warnings in log)
 * 20100629 - asimon - added display of is_open/frozen attribute,
 *                     solved problem with broken ext js table by linebreaks in textarea-cfields
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
$charset = config_get('charset');
$req_cfg = config_get('req_cfg');
$date_format_cfg = config_get('date_format');
$week_short = lang_get('calendar_week_short');
$time_format_cfg = config_get('timestamp_format');
$coverage_enabled = $req_cfg->expected_coverage_management;
$relations_enabled = $req_cfg->relations->enable;

$gui->reqIDs = $tproject_mgr->get_all_requirement_ids($args->tproject_id);

if(count($gui->reqIDs) > 0) {
	
	// get type and status labels
	$type_labels = init_labels($req_cfg->type_labels);
	$status_labels = init_labels($req_cfg->status_labels);
	
	$labels2get = array('no' => 'No', 'yes' => 'Yes', 'not_aplicable' => null,
	                    'req_spec_short' => null,'title' => null, 'version' => null, 'th_coverage' => null,
	                    'frozen' => null, 'type'=> null,'status' => null,'th_relations' => null, 'requirements' => null,
                        'number_of_reqs' => null, 'number_of_versions' => null
    );
					
	$labels = init_labels($labels2get);
	
	$gui->cfields4req = (array)$cfield_mgr->get_linked_cfields_at_design($args->tproject_id, 1, null, 'requirement', null, 'name');
	$version_option = ($args->all_versions) ? requirement_mgr::ALL_VERSIONS : requirement_mgr::LATEST_VERSION; 

    // array to gather table data row per row
	$rows = array();    
	
	foreach($gui->reqIDs as $id) {
		
		// now get the rest of information for this requirement
		$req = $req_mgr->get_by_id($id, $version_option);

		// coverage data
		$tc_coverage = count($req_mgr->get_coverage($id));

		// number of relations, if feature is enabled
		if ($relations_enabled) {
			$relations = $req_mgr->count_relations($id);
			$relations = "<!-- " . sprintf("%010d", $relations) . " -->" . $relations;
		}
		
		// create the link to display
		$title = htmlentities($req[0]['req_doc_id'], ENT_QUOTES, $charset) . $glue_char . 
				 htmlentities($req[0]['title'], ENT_QUOTES, $charset);
		
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
	    	 * 4. frozen (is_open attribute)
	    	 * 5. coverage (if enabled)
	    	 * 6. type
	    	 * 7. status
	    	 * 8. relations (if enabled)
	    	 * 9. all custom fields in order of $fields
	    	 */
	    	
	    	$result[] = $path;
	    	
	    	// use html comment to sort properly by this columns (extjs)
			// BUGID 4038
	    	$linked_title = '<!-- ' . $title . ' -->' . '<a href="javascript:openLinkedReqVersionWindow(' . $id . ',' . $version['version_id'] . ')">' . 
			                $title . '</a>';
	    	
	    	$result[] = $linked_title;
	    	
	    	// version number
	    	$padded_data = sprintf("%010d", $version['version']);
	    	// use html comment to sort properly by this columns (extjs)
	    	$result[] = "<!-- $padded_data -->{$version['version']}";
	    	
	    	// $dummy necessary to avoid warnings on event viewer because localize_dateOrTimeStamp expects
	    	// second parameter to be passed by reference
	    	$dummy = null;
	    	
	    	// use html comment to sort properly by this columns (extjs)
	    	$result[] = "<!--{$version['creation_ts']}-->" .
	    	            localize_dateOrTimeStamp(null, $dummy, 'timestamp_format', $version['creation_ts']) .
	    	            " ({$version['author']})";
			
	    	// on requirement creation motification timestamp is set to default value "0000-00-00 00:00:00"
	    	$never_modified = "0000-00-00 00:00:00";
	    	// use html comment to sort properly by this columns (extjs)
	    	$modification_ts = "<!-- 0 -->" . lang_get('never');
	    	if ($version['modification_ts'] != $never_modified) {
	    		// use html comment to sort properly by this columns (extjs)
	    		$modification_ts = "<!--{$version['modification_ts']}-->" .
	    		                   localize_dateOrTimeStamp(null, $dummy, 'timestamp_format', 
	    		                                            $version['modification_ts']) . 
	    		                   " ({$version['modifier']})";
	    	}
	    	$result[] = $modification_ts;
	    	
			// is it frozen?
			$result[] = ($version['is_open']) ? $labels['no'] : $labels['yes'];
			
			// coverage
			if ($coverage_enabled) {
		    	$expected = $version['expected_coverage'];
		    	// use html comment to sort properly by this columns (extjs)
		    	$coverage_string = "<!-- -1 -->" . $labels['not_aplicable'] . " ($tc_coverage/0)";
		    	if ($expected > 0) {
		    		$percentage = round(100 / $expected * $tc_coverage, 2);
		    		$padded_data = sprintf("%010d", $percentage); //bring all percentages to same length
		    		// use html comment to sort properly by this columns (extjs) 
					$coverage_string = "<!-- $padded_data --> {$percentage}% ({$tc_coverage}/{$expected})";
		    	}
		    	$result[] = $coverage_string;
			}
			
			$result[] = $type_labels[$version['type']];
			$result[] = $status_labels[$version['status']];
			
			if ($relations_enabled) {
				$result[] = $relations;
			}
			
			
			// BUGID 2877 -  Custom Fields linked to Req versions
			// get custom field values for this req version
			$linked_cfields = (array)$req_mgr->get_linked_cfields($id,$version['version_id']);

			foreach ($linked_cfields as $cf) {
				$verbose_type = trim($req_mgr->cfield_mgr->custom_field_types[$cf['type']]);
				$value = preg_replace('!\s+!', ' ', htmlspecialchars($cf['value'], ENT_QUOTES, $charset));

				// 20100921 - asimon - added datetime formatting and calendar week for date custom fields
				if ($verbose_type == 'date' && is_numeric($value) && $value != 0) {
					$value = strftime("$date_format_cfg ($week_short %W)", $value);
				}
				if ($verbose_type == 'datetime' && is_numeric($value) && $value != 0) {
					$value = strftime("$time_format_cfg ($week_short %W)", $value);
				}

				$result[] = $value;
	    	}
	    	
	    	$rows[] = $result;
    	}
    }
    
    if(($gui->row_qty = count($rows)) > 0 ) {
    	$version_string = ($args->all_versions) ? $labels['number_of_versions'] : $labels['number_of_reqs'];
        $gui->pageTitle .= " - " . $version_string . ": " . $gui->row_qty;
		
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
    	 * 4. frozen
    	 * 5. coverage (if enabled)
    	 * 6. type
    	 * 7. status
    	 * 8. relations (if enabled)
    	 * 9. then all custom fields in order of $fields
    	 */
        $columns = array();
        $columns[] = array('title_key' => 'req_spec_short', 'width' => 200);
        $columns[] = array('title_key' => 'title', 'width' => 150);
        $columns[] = array('title_key' => 'version', 'width' => 30);
        $columns[] = array('title_key' => 'created_on', 'width' => 55);
        $columns[] = array('title_key' => 'modified_on','width' => 55);
        $columns[] = array('title_key' => 'frozen', 'width' => 30);
        
        if ($coverage_enabled) {
	    	$columns[] = array('title_key' => 'th_coverage', 'width' => 80);
	    }
	            
        $columns[] = array('title_key' => 'type', 'width' => 60);
        $columns[] = array('title_key' => 'status', 'width' => 60);
	    
		if ($relations_enabled) {
	    	$columns[] = array('title_key' => 'th_relations', 'width' => 50);
	    }
        
	    foreach($gui->cfields4req as $cf) {
	    	$columns[] = array('title' => htmlentities($cf['label'], ENT_QUOTES, $charset), 'type' => 'text',
	    	                   'col_id' => 'id_cf_' .$cf['name']);
	    }

	    // create table object, fill it with columns and row data and give it a title
	    $matrix = new tlExtTable($columns, $rows, 'tl_table_req_overview');
        $matrix->title = $labels['requirements'];
        
        // 20100822 - asimon - removal of magic numbers
        // group by Req Spec
        $matrix->setGroupByColumnName($labels['req_spec_short']);
        
        // sort by coverage descending if enabled, otherwise by status
        $sort_name = ($coverage_enabled) ? $labels['th_coverage'] : $labels['status'];
        $matrix->setSortByColumnName($sort_name);
        $matrix->sortDirection = 'DESC';
        
        // define toolbar
        $matrix->showToolbar = true;
        $matrix->toolbarExpandCollapseGroupsButton = true;
        $matrix->toolbarShowAllColumnsButton = true;
        $matrix->toolbarRefreshButton = true;
        $matrix->showGroupItemsCount = true;
        // show custom field content in multiple lines
        $matrix->addCustomBehaviour('text', array('render' => 'columnWrap'));
        $gui->tableSet= array($matrix);
    }
} else {
    $gui->warning_msg = lang_get('no_linked_req');
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

	$all_versions = isset($_REQUEST['all_versions']) ? true : false;
	$all_versions_hidden = isset($_REQUEST['all_versions_hidden']) ? true : false;
	if ($all_versions) {
		$selection = true;
	} else if ($all_versions_hidden) {
		$selection = false;
	} else if (isset($_SESSION['all_versions'])) {
		$selection = $_SESSION['all_versions'];
	} else {
		$selection = false;
	}
	$args->all_versions = $_SESSION['all_versions'] = $selection;
	
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
	return $user->hasRight($db,'mgt_view_req');
}
?>
