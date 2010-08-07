<?php

/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @package 	TestLink
 * @author		asimon
 * @copyright 	2005-2009, TestLink community 
 * @version    	CVS: $Id: reqSpecSearchForm.php,v 1.3 2010/08/07 22:43:12 asimon83 Exp $
 * @link 		http://www.teamst.org/index.php
 *
 * This page presents the search formular for requiremnt specifications.
 *
 * @internal Revisions:
 * 20100806 - asimon - type displayed wrong selection: req types instead of req spec types
 */

require_once("../../config.inc.php");
require_once("../functions/common.php");
testlinkInitPage($db);


$templateCfg = templateConfiguration();
$tproject_mgr = new testproject($db);

$args = init_args();
$gui = new stdClass();
$gui->tcasePrefix = '';
 
$gui->mainCaption = lang_get('testproject') . " " . $args->tprojectName;

$enabled = 1;
$no_filters = null;

$gui->design_cf = $tproject_mgr->cfield_mgr->get_linked_cfields_at_design($args->tprojectID,$enabled,
                    $no_filters,'requirement_spec');

$reqSpecSet = $tproject_mgr->getOptionReqSpec($args->tprojectID,testproject::GET_NOT_EMPTY_REQSPEC);

$gui->filter_by['design_scope_custom_fields'] = !is_null($gui->design_cf);
$gui->filter_by['requirement_doc_id'] = !is_null($reqSpecSet);

// 20100806 - asimon - type displayed wrong selection: req types instead of req spec types
$reqSpecCfg = config_get('req_spec_cfg');
$gui->types = init_labels($reqSpecCfg->type_labels);

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . 'reqSpecSearchForm.tpl');

/*
  function: 

  args:
  
  returns: 

*/
function init_args()
{              
  	$args = new stdClass();
    $args->tprojectID = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
    $args->tprojectName = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : 0;
       
    return $args;
}
?>
