<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource	testPlanWithCF.php
 * @author Amit Khullar - amkhullar@gmail.com
 *
 * Scope test plan, analize ONLY CF that have 'Test Plan Design SCOPE'
 *
 * @internal revisions
 */
require_once("../../config.inc.php");
require_once("common.php");
require_once('exttable.class.php');
testlinkInitPage($db,false,false,"checkRights");

$smarty = new TLSmarty();
$imgSet = $smarty->getImages();

$cfield_mgr = new cfield_mgr($db);
$templateCfg = templateConfiguration();
$tproject_mgr = new testproject($db);
$tplan_mgr = new testplan($db);
$tcase_mgr = new testcase($db);
$args = init_args($tplan_mgr);

$gui = new stdClass();
$gui->warning_msg = '';
$gui->path_info = $gui->resultSet = $gui->tableSet = null;
$gui->pageTitle = lang_get('caption_testPlanWithCF');
$gui->tproject_name = $args->tproject_name;
$gui->tplan_name = $args->tplan_name;
$gui->tcasePrefix = $tproject_mgr->getTestCasePrefix($args->tproject_id);


$labels = init_labels(array('design' => null));
$testCaseSet = array();

if($tplan_mgr->count_testcases($args->tplan_id) > 0)
{
    $resultsCfg = config_get('results');
    $tcase_cfg = config_get('testcase_cfg');

    // -----------------------------------------------------------------------------------
    $gui->code_status = $resultsCfg['code_status'];

    // Get the custom fields linked/enabled on execution to a test project
    // This will be used on report to give name to header of columns that hold custom field value
    $gui->cfields = $cfield_mgr->get_linked_cfields_at_testplan_design($args->tproject_id,1,'testcase',
                                                                       null,null,null,'name');
                                                                       
    if(!is_null($gui->cfields))
    {
        foreach($gui->cfields as $key => $values)
        {
            $cf_place_holder['cfields'][$key] = '';
        }
    }
   	// Now get TPlan -> Test Cases with custom field values
    $cf_map = $cfield_mgr->get_linked_cfields_at_testplan_design($args->tproject_id,1,'testcase',
                                                                 null,null,$args->tplan_id);
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
            $result[$exec_id] = $exec_info[0];
            // Collect custom fields values
            $result[$exec_id] += $cf_place_holder;
            foreach($exec_info as $cfield_data)
            {
                $result[$exec_id]['cfields'][$cfield_data['name']]=$cfield_data['value'];
            }
        }
    }
    if(($gui->row_qty = count($cf_map)) > 0 )
    {
        $gui->warning_msg = '';
        $gui->resultSet = $result;
    } else {
		$gui->warning_msg = lang_get('no_linked_tplan_cf');
	}
}

$table = buildExtTable($gui,$tcase_mgr, $tplan_mgr, $args->tplan_id,$labels, $imgSet['edit_icon']);

if (!is_null($table)) 
{
	$gui->tableSet[] = $table;
}
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);

/**
 * 
 *
 */
function buildExtTable($gui,$tcase_mgr,$tplan_mgr, $tplan_id, $labels, $edit_icon)
{
	
	$charset = config_get('charset');
	$title_sep = config_get('gui_title_separator_1');
	
	$table = null;
	if(count($gui->resultSet) > 0) 
	{
		$columns = array();
		$columns[] = array('title_key' => 'test_suite');
		$columns[] = array('title_key' => 'test_case', 'width' => 80, 'type' => 'text');
		
		foreach ($gui->cfields as $cfield)
		{
			$dummy = array('title' => $cfield['label'], 'col_id' => 'id_cf_' . $cfield['name'], 'type' => 'text');
			$columns[] = $dummy;
		}
	
		// Extract the relevant data and build a matrix
		$matrixData = array();

		foreach ($gui->resultSet as $item)
		{
			$rowData = array();

			// Get test suite path
			$dummy = $tcase_mgr->getPathLayered(array($item['tcase_id']));
			$dummy = end($dummy);
			$rowData[] = $dummy['value'];

			$name = buildExternalIdString($gui->tcasePrefix, $item['tc_external_id']) .
			                              $title_sep . $item['tcase_name'];

			// create linked icons
			$edit_link = "<a href=\"javascript:openTCEditWindow({$item['tcase_id']});\">" .
						 "<img title=\"{$labels['design']}\" src=\"{$edit_icon}\" /></a> ";

			$rowData[] = "<!-- " . sprintf("%010d", $item['tc_external_id']) . " -->" . $edit_link . $name;;
			$hasValue = false;
			foreach ($item['cfields'] as $cf_value)
			{
				$rowData[] = preg_replace('!\s+!', ' ', htmlentities($cf_value, ENT_QUOTES, $charset));
				$hasValue = $cf_value ? true : false;
			}
			
			if ($hasValue) 
			{
				$matrixData[] = $rowData;
			}
		}
		
		$table = new tlExtTable($columns, $matrixData, 'tl_table_tplan_with_cf');

		$table->addCustomBehaviour('text', array('render' => 'columnWrap'));
		
		$table->setGroupByColumnName(lang_get('test_suite'));
		$table->setSortByColumnName(lang_get('test_case'));
		$table->sortDirection = 'ASC';
		
		$table->showToolbar = true;
		$table->toolbarExpandCollapseGroupsButton = true;
		$table->toolbarShowAllColumnsButton = true;
	}
	return($table);
}

/*
 function:

 args :

 returns:

 */
function init_args(&$tplan_mgr)
{
	$iParams = array("format" => array(tlInputParameter::INT_N),
					 "tplan_id" => array(tlInputParameter::INT_N));

	$args = new stdClass();
	$pParams = R_PARAMS($iParams,$args);
	
    $args->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
    $args->tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : '';

    $args->tplan_name = '';
    if(!$args->tplan_id)
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