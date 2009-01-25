<?php
/* TestLink Open Source Project - http://testlink.sourceforge.net/
 * $Id: searchForm.php,v 1.19 2009/01/25 18:53:49 franciscom Exp $
 * Purpose:  This page presents the search results. 
 *
 * rev: 20090125 - franciscom - BUGID - search by requirement doc id
**/
require_once("../../config.inc.php");
require_once("../functions/keyword.class.php");
require_once("../functions/common.php");
testlinkInitPage($db);

$template_dir = 'testcases/';
$tproject_mgr = new testproject($db);

$args = init_args();
$gui=new stdClass();
$gui->mainCaption = lang_get('testproject') . " " . $args->tprojectName;

$enabled = 1;
$no_filters = null;
$gui->design_cf = $tproject_mgr->cfield_mgr->get_linked_cfields_at_design($args->tprojectID,$enabled,
                                                                             $no_filters,'testcase');

$gui->keywords = $tproject_mgr->getKeywords($args->tprojectID);
$reqSpecSet=$tproject_mgr->getOptionReqSpec($args->tprojectID,testproject::GET_NOT_EMPTY_REQSPEC);

$gui->filter_by['design_scope_custom_fields']=!is_null($gui->design_cf);
$gui->filter_by['keyword']=!is_null($gui->keywords);
$gui->filter_by['requirement_doc_id']=!is_null($reqSpecSet);

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($template_dir . 'tcSearchForm.tpl');

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
