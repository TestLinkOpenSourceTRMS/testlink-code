<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @package 	TestLink
 * @author		asimon
 * @copyright 	2005-2009, TestLink community 
 * @version    	CVS: $Id: reqSearchForm.php,v 1.4 2010/10/21 14:57:07 asimon83 Exp $
 * @link 		http://www.teamst.org/index.php
 *
 * This page presents the search formular for requirements.
 *
 * @internal revisions
 *
 * @since 1.9.4
 * 20110815 - franciscom - 	TICKET 4700: Req Search Improvements - search on log message and 
 *							provide link/url to multiple results
 *
 * @since 1.9.3
 * 20101021 - asimon - BUGID 3716: replaced old separated inputs for day/month/year by ext js calendar
 * 20100323 - asimon - added searching for req relation types (BUGID 1748)
 */

require_once("../../config.inc.php");
require_once("../functions/common.php");
testlinkInitPage($db);


$templateCfg = templateConfiguration();
$tproject_mgr = new testproject($db);
$req_mgr = new requirement_mgr($db);
$tcase_cfg = config_get('testcase_cfg');

$args = init_args();
$gui = new stdClass();

$gui->tcasePrefix = $tproject_mgr->getTestCasePrefix($args->tprojectID);
$gui->tcasePrefix .= $tcase_cfg->glue_character;
$gui->mainCaption = lang_get('testproject') . " " . $args->tprojectName;

$enabled = 1;
$no_filters = null;
$gui->creation_date_from = null;
$gui->creation_date_to = null;
$gui->modification_date_from = null;
$gui->modification_date_to = null;

$gui->design_cf = $tproject_mgr->cfield_mgr->get_linked_cfields_at_design($args->tprojectID,$enabled,
                 														  $no_filters,'requirement');

$gui->keywords = $tproject_mgr->getKeywords($args->tprojectID);
$reqSpecSet = $tproject_mgr->getOptionReqSpec($args->tprojectID,testproject::GET_NOT_EMPTY_REQSPEC);

$gui->filter_by['design_scope_custom_fields'] = !is_null($gui->design_cf);
$gui->filter_by['keyword'] = !is_null($gui->keywords);
$gui->filter_by['requirement_doc_id'] = !is_null($reqSpecSet);

$reqCfg = config_get('req_cfg');
$gui->types = init_labels($reqCfg->type_labels);
$coverageManagement = $reqCfg->expected_coverage_management;
$gui->filter_by['expected_coverage'] = !is_null($coverageManagement);

$gui->reqStatus = init_labels($reqCfg->status_labels);

$gui->filter_by['relation_type'] = $reqCfg->relations->enable;
$gui->req_relation_select = $req_mgr->init_relation_type_select();
foreach ($gui->req_relation_select['equal_relations'] as $key => $oldkey) {
	// set new key in array and delete old one
	$new_key = (int)str_replace("_source", "", $oldkey);
	$gui->req_relation_select['items'][$new_key] = $gui->req_relation_select['items'][$oldkey];
	unset($gui->req_relation_select['items'][$oldkey]);
}

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . 'reqSearchForm.tpl');



function init_args()
{              
  	$args = new stdClass();
    $args->tprojectID = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
    $args->tprojectName = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : 0;
       
    return $args;
}

?>