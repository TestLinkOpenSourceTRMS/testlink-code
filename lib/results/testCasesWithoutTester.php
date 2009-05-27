<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @filesource $RCSfile: testCasesWithoutTester.php,v $
 * @version $Revision: 1.2 $
 * @modified $Date: 2009/05/27 18:42:07 $ by $Author: schlundus $
 * @author Francisco Mancardi - francisco.mancardi@gmail.com
 * 
 * For a test plan, list test cases that has no tester assigned
 * 
 */
require_once("../../config.inc.php");
require_once("common.php");
testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();
$tplan_mgr = new testplan($db);
$args = init_args($tplan_mgr);

$gui = new stdClass();
$gui->pageTitle = lang_get('caption_testCasesWithoutTester');
$gui->warning_msg = '';
$gui->tcasePrefix = '';
$gui->path_info = null;
$gui->resultSet = null;
$gui->tproject_name = $args->tproject_name;
$gui->tplan_name = $args->tplan_name;

$msg_key = 'no_linked_tcversions';
if($tplan_mgr->count_testcases($args->tplan_id) > 0)
{
    $msg_key = 'all_testcases_have_tester';
    $testCaseSet = $tplan_mgr->get_linked_tcversions($args->tplan_id,null,0,null,TL_USER_NOBODY);
    if(($gui->row_qty = count($testCaseSet)) > 0)
    {
        $msg_key = '';
        $gui->pageTitle .= " - " . lang_get('match_count') . ":" . $gui->row_qty;
        $tree_mgr = new tree($db);   
        $tproject_mgr = new testproject($db);   
    
      	$tcase_cfg = config_get('testcase_cfg');
        $tcase_set = array_keys($testCaseSet);
        $gui->path_info = $tree_mgr->get_full_path_verbose($tcase_set);
    
        $gui->tcasePrefix = $tproject_mgr->getTestCasePrefix($args->tproject_id);
        $gui->tcasePrefix .= $tcase_cfg->glue_character;
    	$gui->resultSet = $testCaseSet;
    }
}

$gui->warning_msg = lang_get($msg_key);
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