<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource $RCSfile: testCasesWithCF.php,v $
 * @version $Revision: 1.31 $
 * @modified $Date: 2010/10/15 11:43:25 $ by $Author: mx-julian $
 * @author Amit Khullar - amkhullar@gmail.com
 *
 * For a test plan, list test cases with Execution Custom Field Data
 *
 * @internal Revisions:
 *  20101015 - Julian - used title_key for exttable columns instead of title to be able to use 
 *                      table state independent from localization
 *  20101012 - Julian - added html comment to properly sort by test case column
 *  20100930 - asimon - added icons for testcase execution and editing
 *  20100901 - Julian - added execution notes column
 *	20100830 - franciscom - fixed warnings on eventviewer
 *	20100828 - eloff - adapt to rendering of status column
 *	20100827 - franciscom - refactoring - removed unused variables
 *  20100827 - Julian - only show test case if at least one custom field has a value
 *	20100825 - eloff - add platform_name in table
 *	                   add test suite column
 *	20100823 - Julian - table now uses a unique table id per test project
 *	20100816 - Julian - added default column width
 *                    - added default sorting and grouping
 *	20100719 - eloff - Use tlExtTable
 *	20090504 - amitkhullar - BUGID 2465
 */
require_once("../../config.inc.php");
require_once("common.php");
require_once('exttable.class.php');
testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();
$charset = config_get('charset');
$labels = init_labels(array('design' => null, 'execution' => null, 'no_linked_tc_cf' => null));

$exec_img = TL_THEME_IMG_DIR . "exec_icon.png";
$edit_img = TL_THEME_IMG_DIR . "edit_icon.png";

$tcase_mgr = new testcase($db);
$args = init_args($db);
$gui = initializeGui($db,$args);
if( $args->doIt )
{
   	// Get executions with custom field values
   	buildResultSet($db,$gui,$args->tproject_id,$args->tplan_id);

	// Create column headers
	$columns = getColumnsDefinition($args->showPlatforms,$gui->cfields);

	// Extract the relevant data and build a matrix
	$matrixData = array();
	foreach ($gui->resultSet as $item)
	{
		$rowData = array();

		// Get test suite path
		$dummy = $tcase_mgr->getPathLayered(array($item['tcase_id']));
		$dummy = end($dummy);
		$rowData[] = $dummy['value'];

		// create linked icons
		$exec_link = "<a href=\"javascript:openExecutionWindow(" .
		             "{$item['tcase_id']}, {$item['tcversion_id']}, {$item['builds_id']}, " .
		             "{$args->tplan_id}, {$item['platform_id']});\">" .
		             "<img title=\"{$labels['execution']}\" src=\"{$exec_img}\" /></a> ";

		$edit_link = "<a href=\"javascript:openTCEditWindow({$item['tcase_id']});\">" .
					 "<img title=\"{$labels['design']}\" src=\"{$edit_img}\" /></a> ";

		$tcaseName = buildExternalIdString($gui->tcasePrefix, $item['tc_external_id']) .
					 ' : ' . $item['tcase_name'];

		$tcLink = "<!-- " . sprintf("%010d", $item['tc_external_id']) . " -->" . $exec_link . $edit_link . $tcaseName;
		$rowData[] = $tcLink;

		//$rowData[] = '<a href="lib/testcases/archiveData.php?edit=testcase&id=' . $item['tcase_id'] . '">' .
		//			 buildExternalIdString($gui->tcasePrefix, $item['tc_external_id']) .
		//			 ' : ' . $item['tcase_name'] . '</a>';
		$rowData[] = $item['tcversion_number'];
		if ($args->showPlatforms)
		{
			$rowData[] = $item['platform_name'];
		}
		$rowData[] = $item['build_name'];
		$rowData[] = $item['tester'];

		// use html comment to be able to sort table by timestamp and not by link
		// only link is visible in table but comment is used for sorting
		$dummy = null;
//		$rowData[] = "<!--{$item['execution_ts']}--><a href=\"lib/execute/execSetResults.php?" .
//					 "level=testcase&build_id={$item['builds_id']}&id={$item['tcase_id']}" .
//					 "&version_id={$item['tcversion_id']}&tplan_id={$gui->tplan_id}\">" .
//					 localize_dateOrTimeStamp(null, $dummy, 'timestamp_format', $item['execution_ts']) . '</a>';

		$rowData[] = "<!--{$item['execution_ts']}-->" .
		             localize_dateOrTimeStamp(null, $dummy, 'timestamp_format', $item['execution_ts']);

		// Use array for status to get correct rendering and sorting
		$rowData[] = array(
			'value' => $item['exec_status'],
			'text' => $gui->status_code_labels[$item['exec_status']],
			'cssClass' => $gui->code_status[$item['exec_status']] . '_text',
		);
		
		$hasValue = false;
		
		$rowData[] = strip_tags($item['exec_notes']);
		
		if($item['exec_notes']) {
			$hasValue = true;
		}
		
		foreach ($item['cfields'] as $cf_value)
		{
			$rowData[] = preg_replace('!\s+!', ' ', htmlspecialchars($cf_value, ENT_QUOTES, $charset));;
			if ($cf_value) {
				$hasValue = true;
			}
		}
		if ($hasValue) {
			$matrixData[] = $rowData;
		}
	}

	if (count($matrixData) > 0) {
		$table = new tlExtTable($columns, $matrixData, 'tl_table_tc_with_cf');
		// BUGID 4125
		// $table->addCustomBehaviour('status', array('render' => 'statusRenderer'));
		$table->addCustomBehaviour('text', array('render' => 'columnWrap'));

		$table->setGroupByColumnName(lang_get('build'));
		$table->setSortByColumnName(lang_get('date'));
		$table->sortDirection = 'DESC';

		$table->showToolbar = true;
		$table->toolbarExpandCollapseGroupsButton = true;
		$table->toolbarShowAllColumnsButton = true;

		$gui->tableSet = array($table);
	} else {
		$gui->warning_msg = $labels['no_linked_tc_cf'];
	}
}

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);

/*
 function:

 args :

 returns:

 */
function init_args(&$dbHandler)
{
    $argsObj = new stdClass();
	$argsObj->doIt = false;
    $argsObj->showPlatforms = false;
    $argsObj->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
    $argsObj->tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : '';

    $argsObj->tplan_name = '';
    $argsObj->tplan_id = isset($_REQUEST['tplan_id']) ? $_REQUEST['tplan_id'] : 0;
    if($argsObj->tplan_id == 0)
    {
        $argsObj->tplan_id = isset($_SESSION['testplanID']) ? $_SESSION['testplanID'] : 0;
    }

    if($argsObj->tplan_id > 0)
    {
    	$tplan_mgr = new testplan($dbHandler);
        $tplan_info = $tplan_mgr->get_by_id($argsObj->tplan_id);
        $argsObj->tplan_name = $tplan_info['name'];

		$argsObj->doIt = $tplan_mgr->count_testcases($argsObj->tplan_id) > 0;
		$argsObj->showPlatforms = $tplan_mgr->hasLinkedPlatforms($argsObj->tplan_id);
		unset($tplan_mgr);
    }

    return $argsObj;
}



function initializeGui(&$dbHandler,&$argsObj)
{
	$guiObj = new stdClass();
	$guiObj->pageTitle = lang_get('caption_testCasesWithCF');
	$guiObj->warning_msg = '';
	$guiObj->tcasePrefix = '';
	$guiObj->path_info = null;
	$guiObj->resultSet = null;
	$guiObj->tproject_name = $argsObj->tproject_name;
	$guiObj->tplan_name = $argsObj->tplan_name;
	$guiObj->tplan_id = $argsObj->tplan_id;

    $tproject_mgr = new testproject($dbHandler);
    $guiObj->tcasePrefix = $tproject_mgr->getTestCasePrefix($argsObj->tproject_id);
	unset($tproject_mgr);

    // Get the mapping for the Verbose Status Description of Test Case Status
    $resultsCfg = config_get('results');
    $guiObj->code_status = $resultsCfg['code_status'];
    foreach($guiObj->code_status as $code => $verbose)
    {
        if(isset($resultsCfg['status_label'][$verbose]))
        {
            $guiObj->status_code_labels[$code] = lang_get($resultsCfg['status_label'][$verbose]);
        }
    }
	return $guiObj; 
}


/**
 * 
 * 
 */
function buildResultSet(&$dbHandler,&$guiObj,$tproject_id,$tplan_id)
{
	
	$cfieldMgr = new cfield_mgr($dbHandler);

    // Get the custom fields linked/enabled on execution to a test project
    // This will be used on report to give name to header of columns that hold custom field value
    $guiObj->cfields = $cfieldMgr->get_linked_cfields_at_execution($tproject_id,1,'testcase',null,null,null,'name');
    
    // this way on caller can be used on array operations, without warnings
    $guiObj->cfields = (array)$guiObj->cfields;  
    if( count($guiObj->cfields) > 0 )
    {
    	foreach($guiObj->cfields as $key => $values)
    	{
    	   $cf_place_holder['cfields'][$key]='';
    	}
	}

    $cf_map = $cfieldMgr->get_linked_cfields_at_execution($tproject_id,1,'testcase',
                                                          null,null,$tplan_id,'exec_id');
     
    // need to transform in structure that allow easy display
    // Every row is an execution with exec data plus a column that contains following map:
    // 'cfields' => CFNAME1 => value
    //              CFNAME2 => value
    $guiObj->resultSet = array();

	if(!is_null($cf_map))
    {
        foreach($cf_map as $exec_id => $exec_info)
        {
            // Get common exec info and remove useless keys
            $guiObj->resultSet[$exec_id] = $exec_info[0];
            unset($guiObj->resultSet[$exec_id]['name']);
            unset($guiObj->resultSet[$exec_id]['label']);
            unset($guiObj->resultSet[$exec_id]['display_order']);
            unset($guiObj->resultSet[$exec_id]['id']);
            unset($guiObj->resultSet[$exec_id]['value']);

            // Collect custom fields values
            $guiObj->resultSet[$exec_id] += $cf_place_holder;
            foreach($exec_info as $cfield_data)
            {
                $guiObj->resultSet[$exec_id]['cfields'][$cfield_data['name']]=$cfield_data['value'];
            }
        }
    }

    if(($guiObj->row_qty=count($cf_map)) == 0 )
    {
        $guiObj->warning_msg = lang_get('no_linked_tc_cf');
    }
}


/**
 * get Columns definition for table to display
 *
 */
function getColumnsDefinition($showPlatforms,$customFields)
{

	$colDef = array(array('title_key' => 'test_suite', 'width' => 80, 'type' => 'text'),
					array('title_key' => 'test_case', 'width' => 80, 'type' => 'text'),
					array('title_key' => 'version', 'width' => 20));
		
	if ($showPlatforms)
	{
		$colDef[] = array('title_key' => 'platform', 'width' => 40);
	}
	array_push( $colDef,
				array('title_key' => 'build', 'width' => 35),
				array('title_key' => 'th_owner', 'width' => 60),
				array('title_key' => 'date', 'width' => 60),
				array('title_key' => 'status', 'type' => 'status', 'width' => 30));
				
	$colDef[] = array('title_key' => 'title_execution_notes', 'type' => 'text');


	foreach ($customFields as $cfield)
	{
		// if custom field is time for computing execution time do not waste space
		$dummy = array('title' => $cfield['label'], 'col_id' => 'id_cf_' . $cfield['name']);
		if($cfield['name'] == 'CF_EXEC_TIME') {
			$dummy['width'] = 20;
		} else {
			$dummy['type'] = 'text';
		}
		$colDef[] = $dummy;
	}

	return $colDef;
}


function checkRights(&$db,&$user)
{
	return $user->hasRight($db,'testplan_metrics');
}
?>
