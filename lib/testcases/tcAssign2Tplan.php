<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @filesource $RCSfile: tcAssign2Tplan.php,v $
 * @version $Revision: 1.1 $
 * @modified $Date: 2009/03/08 11:46:35 $ by $Author: franciscom $
 * @author Amit Khullar - amkhullar@gmail.com
 * 
 * For a (test case, test case version), 
 * 
 */
require_once("../../config.inc.php");
require_once("common.php");
testlinkInitPage($db);
$templateCfg = templateConfiguration();

$tcase_mgr=new testcase($db);
$tproject_mgr=new testproject($db);

$args = init_args();
$gui = new stdClass();
// $gui->goback_url=$args->goback_url;
$gui->pageTitle='';
$gui->tcaseIdentity='';
$gui->mainDescription=lang_get('add_tcversion_to_plans');;
$gui->tcase_id=$args->tcase_id;
$gui->tcversion_id=$args->tcversion_id;
$gui->can_do=false;
$gui->item_sep=config_get('gui')->title_separator_2;

$tcase_all_info = $tcase_mgr->get_by_id($args->tcase_id);
if( !is_null($tcase_all_info) )
{
    foreach($tcase_all_info as $tcversion_info)
    {
        if($tcversion_info['id'] == $args->tcversion_id )
        {
            $version = $tcversion_info['version'];
            $gui->pageTitle=lang_get('test_case') . ':' . $tcversion_info['name'];
            $gui->tcaseIdentity = $tproject_mgr->getTestCasePrefix($args->tproject_id) .
                                  config_get('testcase_cfg')->glue_character . $tcversion_info['tc_external_id'] . 
                                  ':' . $tcversion_info['name'];
            break;      
        }   
    } 
}

$link_info = $tcase_mgr->get_linked_versions($args->tcase_id);
if( !is_null($gui->tplans = $tproject_mgr->get_all_testplans($args->tproject_id)) )
{
    // Initial situation, enable link of target test case version to all test plans
    foreach($gui->tplans as $tplan_id => $value)  
    {
        $gui->tplans[$tplan_id]['tcversion_id'] = $args->tcversion_id;
        $gui->tplans[$tplan_id]['version'] = $version;
        $gui->tplans[$tplan_id]['draw_checkbox'] = true;
    }

    // If target test case has been linked to any test plan, analise to 
    // disable operation on used test plans.
    if( !is_null($link_info) )
    {
        foreach($link_info as $tcversion_id => $info)
        {
           foreach($info as $tplan_id => $value)
           {
               $gui->tplans[$tplan_id]['tcversion_id']=$value['id'];                            
               $gui->tplans[$tplan_id]['version']=$value['version'];
               $gui->tplans[$tplan_id]['draw_checkbox'] = false;
           }
        }  
    }

    // Check if submit button can be displayed.
    // Condition there is at least one test plan where no version of
    // target test cases has been linked.
    $gui->can_do=false;  // because an OR logic will be used
    foreach($gui->tplans as $tplan_id => $value)  
    {
        $gui->can_do = $gui->can_do || $gui->tplans[$tplan_id]['draw_checkbox'];
    }    

}


$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);



/**
 * init_args
 * creates a sort of namespace
 *
 * @return  object with some REQUEST and SESSION values as members.
 */
function init_args()
{
	$_REQUEST = strings_stripSlashes($_REQUEST);

	$args = new stdClass();
	$args->tplan_id = isset($_REQUEST['tplan_id']) ? $_REQUEST['tplan_id'] : $_SESSION['testPlanId'];
	$args->tproject_id = isset($_REQUEST['tproject_id']) ? $_REQUEST['tproject_id'] : $_SESSION['testprojectID'];
	$args->tcase_id = isset($_REQUEST['tcase_id']) ? $_REQUEST['tcase_id'] : 0;
	$args->tcversion_id = isset($_REQUEST['tcversion_id']) ? $_REQUEST['tcversion_id'] : 0;
	// $args->goback_url = isset($_REQUEST['goback_url']) ? $_REQUEST['goback_url'] : null;

  return $args;	
}	
?>


