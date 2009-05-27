<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource $RCSfile: testPlanWithCF.php,v $
 * @version $Revision: 1.5 $
 * @modified $Date: 2009/05/27 18:42:07 $ by $Author: schlundus $
 * @author Amit Khullar - amkhullar@gmail.com
 *
 * For a test plan, list associated Custom Field Data
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
$gui->pageTitle = lang_get('caption_testPlanWithCF');
$gui->warning_msg = '';
$gui->tcasePrefix = '';
$gui->path_info = null;
$gui->resultSet = null;
$gui->tproject_name = $args->tproject_name;
$gui->tplan_name = $args->tplan_name;
$testCaseSet = array();
$msg_key = 'no_linked_tc_cf';
if($tplan_mgr->count_testcases($args->tplan_id) > 0)
{
    $resultsCfg = config_get('results');
    $tcase_cfg = config_get('testcase_cfg');

    // -----------------------------------------------------------------------------------
    $gui->code_status = $resultsCfg['code_status'];
    $tproject_mgr = new testproject($db);

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
        $msg_key = '';
        $gui->pageTitle .= " - " . lang_get('match_count') . ":" . $gui->row_qty;
        $gui->resultSet = $result;
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
	$iParams = array(
		"format" => array(tlInputParameter::INT_N),
		"tplan_id" => array(tlInputParameter::INT_N),
	);

	$args = new stdClass();
	$pParams = R_PARAMS($iParams,$args);
	
    $args->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
    $args->tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : '';

    $args->tplan_name = '';
    if(!$args->tplan_id)
    {
        $args->tplan_id = isset($_SESSION['testPlanId']) ? $_SESSION['testPlanId'] : 0;
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