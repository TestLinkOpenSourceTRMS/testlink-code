<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @filesource $RCSfile: freeTestCases.php,v $
 * @version $Revision: 1.2 $
 * @modified $Date: 2009/05/21 19:24:05 $ by $Author: schlundus $
 * @author Francisco Mancardi - francisco.mancardi@gmail.com
 * 
 * For a test project, list FREE test cases, i.e. not assigned to a test plan.
 * 
 * rev: 20090412 - franciscom - BUGID 2363
 *
 */
require_once("../../config.inc.php");
require_once("common.php");
testlinkInitPage($db,true,false,"checkRights");

$templateCfg = templateConfiguration();
$tcase_cfg = config_get('testcase_cfg');

$args = init_args();
$tproject_mgr = new testproject($db);

$msg_key = 'all_testcases_has_testplan';

$gui = new stdClass();
$gui->freeTestCases = $tproject_mgr->getFreeTestCases($args->tproject_id);
$gui->path_info = null;
$gui->resultSet = null;
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
        $gui->tcasePrefix = $tproject_mgr->getTestCasePrefix($args->tproject_id) . $tcase_cfg->glue_character;
        $tcaseSet = array_keys($gui->freeTestCases['items']);
        $gui->path_info = $tproject_mgr->tree_manager->get_full_path_verbose($tcaseSet);
  	    $gui->resultSet = $gui->freeTestCases['items'];
  	}
}
$gui->tproject_name = $args->tproject_name;
$gui->pageTitle = lang_get('report_free_testcases_on_testproject');
$gui->warning_msg = lang_get($msg_key);


$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);
  

/**
 * init_args
 *
 * Collect all inputs (arguments) to page, that can be arrived via $_REQUEST,$_SESSION
 * and creates an stdClass() object where each property is result of mapping page inputs.
 * We have created some sort of 'namespace', thi way we can easy understand which variables
 * has been created for local use, and which have arrived on call.
 *
 */
function init_args()
{
    $iParams = array(
		"tplan_id" => array(tlInputParameter::INT_N),
		"format" => array(tlInputParameter::INT_N),
	);

	$args = new stdClass();
	$pParams = G_PARAMS($iParams,$args);

	$args->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
    $args->tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : '';

    return $args;
}

function checkRights(&$db,&$user)
{
	return $user->hasRight($db,'testplan_metrics');
}
?>


