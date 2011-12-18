<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Form to set test cases search criteria
 *
 * @filesource	tcSearchForm.php
 * @package 	TestLink
 * @author 		TestLink community
 * @copyright 	2007-2011, TestLink community 
 * @link 		http://www.teamst.org/index.php
 *
 *	@internal revisions
 *	@since 1.9.4
 *  20111218 - franciscom - added new user hints
 *
**/
require_once("../../config.inc.php");
require_once("../functions/common.php");
testlinkInitPage($db);
$templateCfg = templateConfiguration();
$tproject_mgr = new testproject($db);

$args = init_args();

$gui = new stdClass();
$gui->tcasePrefix = $tproject_mgr->getTestCasePrefix($args->tprojectID) . config_get('testcase_cfg')->glue_character;
$gui->mainCaption = lang_get('testproject') . " " . $args->tprojectName;
$gui->importance = config_get('testcase_importance_default');
$gui->creation_date_from = null;
$gui->creation_date_to = null;
$gui->modification_date_from = null;
$gui->modification_date_to = null;
$gui->search_important_notice = sprintf(lang_get('search_important_notice'),$args->tprojectName);

$enabled = 1;
$no_filters = null;
$gui->design_cf = $tproject_mgr->cfield_mgr->get_linked_cfields_at_design($args->tprojectID,$enabled,
                                                                          $no_filters,'testcase');

$gui->keywords = $tproject_mgr->getKeywords($args->tprojectID);
$reqSpecSet = $tproject_mgr->genComboReqSpec($args->tprojectID);

$gui->filter_by['design_scope_custom_fields'] = !is_null($gui->design_cf);
$gui->filter_by['keyword'] = !is_null($gui->keywords);
$gui->filter_by['requirement_doc_id'] = !is_null($reqSpecSet);

$gui->option_importance = array(0 => '',HIGH => lang_get('high_importance'),MEDIUM => lang_get('medium_importance'), 
                                LOW => lang_get('low_importance'));

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . 'tcSearchForm.tpl');


/**
 * 
 *
 */
function init_args()
{              
  	$args = new stdClass();
    $args->tprojectID = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
    $args->tprojectName = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : 0;
       
    return $args;
}
?>