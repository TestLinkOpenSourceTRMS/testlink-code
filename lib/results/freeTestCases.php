<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @filesource	freeTestCases.php
 * @author 		  Francisco Mancardi - francisco.mancardi@gmail.com
 * 
 * For a test project, list FREE test cases, i.e. not assigned to a test plan.
 * 
 * @internal revisions
 *
 */
require_once("../../config.inc.php");
require_once("common.php");
require_once('exttable.class.php');
testlinkInitPage($db);

$templateCfg = templateConfiguration();
$tcase_cfg = config_get('testcase_cfg');

$importance_levels = config_get('importance_levels');

$tproject_mgr = new testproject($db);
$args = init_args($tproject_mgr);
checkRights($db,$_SESSION['currentUser'],$args);

$priorityMgmtEnabled = $args->tprojectOptions->testPriorityEnabled;

$msg_key = 'all_testcases_has_testplan';
$edit_label = lang_get('design');
$edit_img = TL_THEME_IMG_DIR . "edit_icon.png";

$gui = new stdClass();
$gui->freeTestCases = $tproject_mgr->getFreeTestCases($args->tproject_id);
$gui->path_info = null;
$gui->tableSet = null;
if(!is_null($gui->freeTestCases['items']))
{
  if($gui->freeTestCases['allfree'])
  { 
    // has no sense display all test cases => display just message.
    $msg_key = 'all_testcases_are_free';
  }   
  else
  {
        $msg_key = '';    
        $tcasePrefix = $tproject_mgr->getTestCasePrefix($args->tproject_id) . $tcase_cfg->glue_character;
        $tcaseSet = array_keys($gui->freeTestCases['items']);
        $options = array('output_format' => 'path_as_string');
        $tsuites = $tproject_mgr->tree_manager->get_full_path_verbose($tcaseSet,$options);
        $titleSeperator = config_get('gui_title_separator_1');
  	    
		  $columns = getColumnsDefinition($priorityMgmtEnabled);
	
		// Extract the relevant data and build a matrix
		$matrixData = array();
		  
		$impCfg = config_get('importance');
		$impCodeVerbose = array_flip($impCfg['verbose_code']);
    foreach($impCfg['verbose_label'] as $verbose => $lblkey)
    {
      $impCodeLabel[$impCfg['verbose_code'][$verbose]] = lang_get($lblkey);
    }		 

		foreach($gui->freeTestCases['items'] as $tcases) 
		{
			$rowData = array();
			
			$rowData[] = strip_tags($tsuites[$tcases['id']]);
			//build test case link

			$edit_link = "<a href=\"javascript:openTCEditWindow({$gui->tproject_id},{$tcases['id']});\">" .
						 "<img title=\"{$edit_label}\" src=\"{$edit_img}\" /></a> ";
			$tcaseName = $tcasePrefix . $tcases['tc_external_id'] . $titleSeperator .
			             strip_tags($tcases['name']);
		    $tcLink = "<!-- " . sprintf("%010d", $tcases['tc_external_id']) . " -->" . $edit_link . $tcaseName;
			$rowData[] = $tcLink;
			
			// only add importance column if 
			if($priorityMgmtEnabled)
			{                              
			  if( isset($impCodeVerbose[$tcases['importance']]) )
			  {
						$rowData[] = "<!-- " . $tcases['importance'] . " -->" . $impCodeLabel[$tcases['importance']];
			  }
			}
			
			$matrixData[] = $rowData;
		}
		
		$table = new tlExtTable($columns, $matrixData, 'tl_table_test_cases_not_assigned_to_any_test_plan');
		
		$table->setGroupByColumnName(lang_get('test_suite'));
		
		$sort_by_column = ($priorityMgmtEnabled) ? 'importance' : 'test_case';
		
		$table->setSortByColumnName(lang_get($sort_by_column));
		$table->sortDirection = 'DESC';
	
		$gui->tableSet = array($table);
  	    
  	}
}


$gui->tproject_name = $args->tproject_name;
$gui->pageTitle = lang_get('report_free_testcases_on_testproject');
$gui->warning_msg = lang_get($msg_key);


$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/**
 * get Columns definition for table to display
 *
 */
function getColumnsDefinition($priorityMgmtEnabled)
{
	$colDef = array();
	
	$colDef[] = array('title_key' => 'test_suite', 'type' => 'text');
	$colDef[] = array('title_key' => 'test_case', 'type' => 'text');
	if ($priorityMgmtEnabled) {
		$urgencies_for_filter = array(lang_get('urgency_low'),lang_get('urgency_medium'),lang_get('urgency_high'));
		$colDef[] = array('title_key' => 'importance', 'width' => 20, 'filter' => 'ListSimpleMatch', 'filterOptions' => $urgencies_for_filter);
	}

	return $colDef;
}

/**
 * init_args
 *
 * Collect all inputs (arguments) to page, that can be arrived via $_REQUEST,$_SESSION
 * and creates an stdClass() object where each property is result of mapping page inputs.
 * We have created some sort of 'namespace', thi way we can easy understand which variables
 * has been created for local use, and which have arrived on call.
 *
 */
function init_args(&$tprojectMgr)
{
    $iParams = array("tproject_id" => array(tlInputParameter::INT_N),
    				 "tplan_id" => array(tlInputParameter::INT_N),
					 "format" => array(tlInputParameter::INT_N));

	$args = new stdClass();
	R_PARAMS($iParams,$args);

	$args->tproject_name = '';
	if($args->tproject_id > 0)
	{
		$dummy = $tprojectMgr->get_by_id($args->tproject_id);
		$args->tproject_name = $dummy['name'];
		$args->tprojectOptions = $dummy['opt'];
	}

    return $args;
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