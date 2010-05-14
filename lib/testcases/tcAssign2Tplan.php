<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * While in test specification feature, assign TEST CASE version to multiple
 * ACTIVE test plans
 *
 * @package 	TestLink
 * @author 		Amit Khullar - amkhullar@gmail.com
 * @copyright 	2007-2009, TestLink community 
 * @version    	CVS: $Id: tcAssign2Tplan.php,v 1.7 2010/05/14 19:36:09 franciscom Exp $
 * @link 		http://www.teamst.org/index.php
 *
 *
 *	@internal revisions
 *	20100514 - franciscom - BUGID 3189
 *	20100124 - franciscom - BUGID 3064 - add logic to manage ONLY ACTIVE test plans
 **/

require_once("../../config.inc.php");
require_once("common.php");
testlinkInitPage($db);
$templateCfg = templateConfiguration();

$tcase_mgr=new testcase($db);
$tplan_mgr=new testplan($db);
$tproject_mgr=new testproject($db);

$glue = config_get('testcase_cfg')->glue_character;
$args = init_args();
$gui = initializeGui($args);
$getOpt = array('outputFormat' => 'map', 'addIfNull' => true);
$gui->platformSet = $tplan_mgr->getPlatforms($args->tplan_id,$getOpt);  

$options['output'] = 'essential';
$tcase_all_info = $tcase_mgr->get_by_id($args->tcase_id,testcase::ALL_VERSIONS,null,$options);

if( !is_null($tcase_all_info) )
{
    foreach($tcase_all_info as $tcversion_info)
    {
        if($tcversion_info['id'] == $args->tcversion_id )
        {
            $version = $tcversion_info['version'];
            $gui->pageTitle=lang_get('test_case') . ':' . $tcversion_info['name'];
            $gui->tcaseIdentity = $tproject_mgr->getTestCasePrefix($args->tproject_id);
            $gui->tcaseIdentity .= $glue . $tcversion_info['tc_external_id'] . ':' . $tcversion_info['name'];
            break;      
        }   
    } 
}

// 20100514 - franciscom
// Why I'm filter on NOT_EXECUTED ??? -> this causes BUGID 3189
// $link_info = $tcase_mgr->get_linked_versions($args->tcase_id,'NOT_EXECUTED');
$link_info = $tcase_mgr->get_linked_versions($args->tcase_id);

// 20100124 - work only on ACTIVE TEST PLANS => array('plan_status' => 1)
if( !is_null($tplanSet = $tproject_mgr->get_all_testplans($args->tproject_id,array('plan_status' => 1))) )
{
    // Initial situation, enable link of target test case version to all test plans
    $getOpt = array('outputFormat' => 'map', 'addIfNull' => true);
    foreach($tplanSet as $tplan_id => $value)  
    {
    	$gui->tplans[$tplan_id] = array();
		$platformSet = $tplan_mgr->getPlatforms($tplan_id,$getOpt);
        foreach($platformSet as $platform_id => $platform_info)
        {
        	$gui->tplans[$tplan_id][$platform_id] = $value;
        	$gui->tplans[$tplan_id][$platform_id]['tcversion_id'] = $args->tcversion_id;
        	$gui->tplans[$tplan_id][$platform_id]['version'] = $version;
        	$gui->tplans[$tplan_id][$platform_id]['draw_checkbox'] = true;
            $gui->tplans[$tplan_id][$platform_id]['platform'] = $platform_info;                            
        }
    }
   
    // If target test case has been linked to any test plan, analise to 
    // disable operation on used test plans.
    if( !is_null($link_info) )
    {
        foreach($link_info as $tcversion_id => $info)
        {
           foreach($info as $tplan_id => $platform_info)
           {
           		foreach($platform_info as $platform_id => $value)
           		{
               		// $gui->tplans[$tplan_id][$platform_id]['tcversion_id']=$value['id'];                            
               		$gui->tplans[$tplan_id][$platform_id]['tcversion_id']=$value['tcversion_id'];                            
               		$gui->tplans[$tplan_id][$platform_id]['version']=$value['version'];
               		$gui->tplans[$tplan_id][$platform_id]['draw_checkbox'] = false;
           		}
           }
        }  
    }

    // Check if submit button can be displayed.
    // Condition there is at least one test plan where no version of
    // target test cases has been linked.
    $gui->can_do=false;  // because an OR logic will be used
    foreach($gui->tplans as $tplan_id => $platform_info)  
    {
		foreach($platform_info as $platform_id => $value)
        {
    		$gui->can_do = $gui->can_do || $gui->tplans[$tplan_id][$platform_id]['draw_checkbox'];
    	}
        
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
	$args->tplan_id = isset($_REQUEST['tplan_id']) ? $_REQUEST['tplan_id'] : $_SESSION['testplanID'];
	$args->tproject_id = isset($_REQUEST['tproject_id']) ? $_REQUEST['tproject_id'] : $_SESSION['testprojectID'];
	$args->tcase_id = isset($_REQUEST['tcase_id']) ? $_REQUEST['tcase_id'] : 0;
	$args->tcversion_id = isset($_REQUEST['tcversion_id']) ? $_REQUEST['tcversion_id'] : 0;
	// $args->goback_url = isset($_REQUEST['goback_url']) ? $_REQUEST['goback_url'] : null;

  return $args;	
}


/**
 * 
 *
 */
function initializeGui($argsObj)
{
	$guiObj = new stdClass();
	$guiObj->pageTitle='';
	$guiObj->tcaseIdentity='';
	$guiObj->mainDescription=lang_get('add_tcversion_to_plans');;
	$guiObj->tcase_id=$argsObj->tcase_id;
	$guiObj->tcversion_id=$argsObj->tcversion_id;
	$guiObj->can_do=false;
	$guiObj->item_sep=config_get('gui')->title_separator_2;
    return $guiObj;
}

?>