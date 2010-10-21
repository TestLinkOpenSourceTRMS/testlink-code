<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Form to set test cases search criteria
 *
 * @package 	TestLink
 * @author 		TestLink community
 * @copyright 	2007-2009, TestLink community 
 * @version    	CVS: $Id: tcSearchForm.php,v 1.6 2010/10/21 14:57:07 asimon83 Exp $
 * @link 		http://www.teamst.org/index.php
 *
 *	@internal revisions
 *  20101021 - asimon - BUGID 3716: replaced old separated inputs for day/month/year by ext js calendar
 *	20100609 - franciscom - BUGID 1627: Search Test Case by Date of Creation
 *	20100409 - franciscom - BUGID 3371 Search Test Cases based on Test Importance
 *	20090228 - franciscom - improvement on management of test case prefix
 *
 *  20090125 - franciscom - BUGID - search by requirement doc id
 *                                  available only if Req Specs exist.
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

// BUGID 3716
$gui->creation_date_from = null;
$gui->creation_date_to = null;
$gui->modification_date_from = null;
$gui->modification_date_to = null;

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

// BUGID 3716
$gui->creation_date_from = null;
$gui->creation_date_to = null;
$gui->modification_date_from = null;
$gui->modification_date_to = null;

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
