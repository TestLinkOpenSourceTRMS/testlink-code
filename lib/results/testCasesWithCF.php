<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource $RCSfile: testCasesWithCF.php,v $
 * @version $Revision: 1.10 $
 * @modified $Date: 2010/08/17 14:30:35 $ by $Author: mx-julian $
 * @author Amit Khullar - amkhullar@gmail.com
 *
 * For a test plan, list test cases with Execution Custom Field Data
 *
 * @internal Revisions:
 *	20100816 - Julian - added default column width
 *                    - added default sorting and grouping
 *	20100719 - eloff - Use tlExtTable
 *	20090504 - amitkhullar - BUGID 2465
 */
require_once("../../config.inc.php");
require_once("common.php");
require_once('exttable.class.php');
testlinkInitPage($db,false,false,"checkRights");

define('TABLE_GROUP_BY_BUILD', 1);
define('TABLE_SORT_BY_DATE', 3);

$cfield_mgr = new cfield_mgr($db);
$templateCfg = templateConfiguration();

$tplan_mgr = new testplan($db);
$args = init_args($tplan_mgr);

$gui = new stdClass();
$gui->pageTitle = lang_get('caption_testCasesWithCF');
$gui->warning_msg = '';
$gui->tcasePrefix = '';
$gui->path_info = null;
$gui->resultSet = null;
$gui->tproject_name = $args->tproject_name;
$gui->tplan_name = $args->tplan_name;
$gui->tplan_id = $args->tplan_id;
$testCaseSet = array();
$msg_key = 'no_linked_tc_cf';
if($tplan_mgr->count_testcases($args->tplan_id) > 0)
{
    $resultsCfg = config_get('results');
    $tcase_cfg = config_get('testcase_cfg');

    // -----------------------------------------------------------------------------------
    // Get the mapping for the Verbose Status Description of Test Case Status
    $map_tc_status_verbose_code = $resultsCfg['code_status'];
    $map_tc_status_verbose_label = $resultsCfg['status_label'];
    foreach($map_tc_status_verbose_code as $code => $verbose)
    {
        if(isset($map_tc_status_verbose_label[$verbose]))
        {
            $label = $map_tc_status_verbose_label[$verbose];
            $gui->status_code_labels[$code] = lang_get($label);
        }
    }
    // -----------------------------------------------------------------------------------
    $gui->code_status = $resultsCfg['code_status'];
    $tproject_mgr = new testproject($db);
    $gui->tcasePrefix = $tproject_mgr->getTestCasePrefix($args->tproject_id);

    // Get the custom fields linked/enabled on execution to a test project
    // This will be used on report to give name to header of columns that hold custom field value
    $gui->cfields = $cfield_mgr->get_linked_cfields_at_execution($args->tproject_id,1,'testcase',
                                                                 null,null,null,'name');
    
    if(!is_null($gui->cfields))
    {
        foreach($gui->cfields as $key => $values)
        {
           $cf_place_holder['cfields'][$key]='';
        }
    }
   	// Now get exeutions with custom field values
    $cf_map = $cfield_mgr->get_linked_cfields_at_execution($args->tproject_id,1,'testcase',
                                                           null,null,$args->tplan_id,'exec_id');
     
    // need to transform in structure that allow easy display
    // Every row is an execution with exec data plus a column that contains following map:
    // 'cfields' => CFNAME1 => value
    //              CFNAME2 => value
    $result = array();
    if(!is_null($cf_map))
    {
        foreach($cf_map as $exec_id => $exec_info)
        {
            // Get common exec info and remove useless keys
            $result[$exec_id]=$exec_info[0];
            unset($result[$exec_id]['name']);
            unset($result[$exec_id]['label']);
            unset($result[$exec_id]['display_order']);
            unset($result[$exec_id]['id']);
            unset($result[$exec_id]['value']);

            // Collect custom fields values
            $result[$exec_id] += $cf_place_holder;
            foreach($exec_info as $cfield_data)
            {
                $result[$exec_id]['cfields'][$cfield_data['name']]=$cfield_data['value'];
            }
        }
    }

    if(($gui->row_qty=count($cf_map)) > 0 )
    {
        $msg_key = '';
        $gui->pageTitle .= " - " . lang_get('match_count') . ":" . $gui->row_qty;
        $gui->resultSet=$result;
    }


	// Create column headers
	$columns = array(
		array('title' => lang_get('test_case'), 'width' => 80),
		array('title' => lang_get('build'), 'width' => 35),
		array('title' => lang_get('th_owner'), 'width' => 60),
		array('title' => lang_get('date'), 'width' => 60),
		array('title' => lang_get('status'), 'type' => status, 'width' => 20));
	foreach ($gui->cfields as $cfield)
	{
		//if custom field is time for computing execution time do not waste space
		if($cfield['name'] == 'CF_EXEC_TIME') {
			$columns[] = array('title' => $cfield['label'], 'width' => 20);
		} else {
			$columns[] = array('title' => $cfield['label'], 'type' => 'text' );
		}
	}
	
	

	// Extract the relevant data and build a matrix
	$matrixData = array();
	foreach ($result as $arrData)
	{
		$rowData = array();
		$rowData[] = '<a href="lib/testcases/archiveData.php?edit=testcase&id=' . $arrData['tcase_id'] . '">' .
			buildExternalIdString($gui->tcasePrefix, $arrData['tc_external_id']) .
			' : ' . $arrData['tcase_name'] . '</a>';
		$rowData[] = $arrData['build_name'];
		$rowData[] = $arrData['tester'];

		$dummy = null;
		$rowData[] =
			"<!--{$arrData['execution_ts']}--><a href=\"lib/execute/execSetResults.php?" .
			"level=testcase&build_id={$arrData['builds_id']}&id={$arrData['tcase_id']}" .
			"&version_id={$arrData['tcversion_id']}&tplan_id={$gui->tplan_id}\">" .
			localize_dateOrTimeStamp(null, $dummy, 'timestamp_format', $arrData['execution_ts']) .
			'</a>';
		// let the renderer localize status
		$rowData[] = $arrData['exec_status'];

		foreach ($arrData['cfields'] as $cf_value)
		{
			$rowData[] = $cf_value;
		}

		$matrixData[] = $rowData;
	}
	$table = new tlExtTable($columns, $matrixData, 'tl_table_results_cf');
	$table->addCustomBehaviour('status', array('render' => 'statusRenderer'));
	$table->addCustomBehaviour('text', array('render' => 'columnWrap'));
	$table->groupByColumn = TABLE_GROUP_BY_BUILD;
	$table->sortByColumn = TABLE_SORT_BY_DATE;
	//define toolbar
	$table->show_toolbar = true;
	$table->toolbar_expand_collapse_groups_button = true;
	$table->toolbar_show_all_columns_button = true;
	$table->sortDirection = 'DESC';
	$gui->tableSet = array($table);
}

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);

/*
 function:

 args :

 returns:

 */
function init_args(&$tplan_mgr)
{
    $args = new stdClass();

    $args->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
    $args->tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : '';

    $args->tplan_id = isset($_REQUEST['tplan_id']) ? $_REQUEST['tplan_id'] : 0;
    $args->tplan_name = '';
    if($args->tplan_id == 0)
    {
        $args->tplan_id = isset($_SESSION['testplanID']) ? $_SESSION['testplanID'] : 0;
    }

    if($args->tplan_id > 0)
    {
        $tplan_info = $tplan_mgr->get_by_id($args->tplan_id);
        $args->tplan_name = $tplan_info['name'];
    }

    return $args;
}


function checkRights(&$db,&$user)
{
	return $user->hasRight($db,'testplan_metrics');
}
?>
