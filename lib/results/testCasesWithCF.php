<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource $RCSfile: testCasesWithCF.php,v $
 * @version $Revision: 1.7 $
 * @modified $Date: 2009/06/10 19:36:00 $ by $Author: franciscom $
 * @author Amit Khullar - amkhullar@gmail.com
 *
 * For a test plan, list test cases with Execution Custom Field Data
 * rev:
 * 		20090504 - amitkhullar - BUGID 2465
 */
require_once("../../config.inc.php");
require_once("common.php");
testlinkInitPage($db,false,false,"checkRights");

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
    $gui->tcasePrefix .= $tcase_cfg->glue_character;

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