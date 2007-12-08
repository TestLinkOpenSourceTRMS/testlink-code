<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: keywordsEdit.php,v $
 *
 * @version $Revision: 1.9 $
 * @modified $Date: 2007/12/08 18:11:52 $ by $Author: franciscom $
 *
 * allows users to manage keywords. 
 *
 * This is a fully commented model of How I think we need to develop new
 * pages of this kind, and how we need to refactor old pages.
 *
 *
**/
require_once("../../config.inc.php");
require_once("common.php");
require_once("csv.inc.php");
require_once("xml.inc.php");
require_once("keyword.class.php");

testlinkInitPage($db);
$smarty = new TLSmarty();

$template_dir='keywords/';
$template='keywordsEdit.tpl';
$op->status=0;
$msg='';

$args=init_args();
$canManage = has_rights($db,"mgt_modify_key");

$tprojectMgr = new testproject($db);
$sqlResult = null;
$action = null;

switch ($args->doAction)
{
  case "create":
  $op=create($smarty,$args);
  break;

  case "edit":
  $op=edit($smarty,$args,$tprojectMgr);
  break;

  case "do_create":
  $op=do_create($smarty,$args,$tprojectMgr);
  break;

  case "do_update":
  $op=do_update($smarty,$args,$tprojectMgr);
  break;

  case "do_delete":
  $op=do_delete($smarty,$args,$tprojectMgr);
  break;

} // switch


if( $op->status == 1)
{
  $template=$op->template;  
}
else
{
  $msg = getErrorMessage($op->status);
}

$keywords = $tprojectMgr->getKeywords($args->testproject_id);
$keyword_mgr = new tlKeyword();
$export_types=$keyword_mgr->getSupportedSerializationInterfaces();

$smarty->assign('action',$action);
$smarty->assign('sqlResult',$sqlResult);
$smarty->assign('user_feedback',$msg);

$smarty->assign('canManage',$canManage);
$smarty->assign('arrKeywords', $keywords);

$smarty->assign('name',$args->keyword);
$smarty->assign('keyword',$args->keyword);
$smarty->assign('notes',$args->notes);
$smarty->assign('keywordID',$args->keyword_id);

$smarty->display($template_dir . $template);
?>



<?php
function init_args()
{
  
  $_REQUEST = strings_stripSlashes($_REQUEST);

  $args->doAction = isset($_REQUEST['doAction']) ? $_REQUEST['doAction'] : null;

  $args->keyword_id = isset($_REQUEST['id']) ? $_REQUEST['id'] : null;
  $args->bDeleteKey = isset($_REQUEST['deleteKey']) ? 1 : 0;
  $args->keyword = isset($_REQUEST['keyword']) ? $_REQUEST['keyword'] : null;
  $args->bNewKey = isset($_REQUEST['newKey']) ? 1 : 0;
  $args->bEditKey = isset($_REQUEST['editKey']) ? 1 : 0;
  $args->notes = isset($_REQUEST['notes']) ? $_REQUEST['notes'] : null;
  $args->do_export = isset($_REQUEST['exportAll']) ? 1 : 0;
  $args->exportType = isset($_REQUEST['exportType']) ? $_REQUEST['exportType'] : null;
  $args->testproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
  $args->testproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : 0;

  return $args;
}


/*
  function: create
            initialize variables to launch user interface (smarty template)
            to get information to accomplish create task.

  args:
  
  returns: - 

*/
function create(&$smarty,&$args)
{
  $ret->template='keywordsEdit.tpl';
  $ret->status=1;
  
  $main_descr=lang_get('keyword_management');
  $action_descr=lang_get('create_keyword');

  $smarty->assign('submit_button_label',lang_get('btn_save'));
  $smarty->assign('submit_button_action','do_create');
  $smarty->assign('main_descr',$main_descr);
  $smarty->assign('action_descr',$action_descr);
  
  return $ret;
}


/*
  function: edit
            initialize variables to launch user interface (smarty template)
            to get information to accomplish edit task.

  args:
  
  returns: - 

*/
function edit(&$smarty,&$args,&$tproject_mgr)
{
  $ret->template='keywordsEdit.tpl';
  $ret->status=1;
  
  $keyword_info = $tproject_mgr->getKeyword($args->keyword_id);
  
  $main_descr=lang_get('keyword_management');
  $action_descr=lang_get('edit_keyword') . TITLE_SEP . $keyword_info['keyword'];

	$args->keyword = $keyword_info['keyword'];
	$args->notes = $keyword_info['notes'];

  $smarty->assign('submit_button_label',lang_get('btn_save'));
  $smarty->assign('submit_button_action','do_update');
  $smarty->assign('main_descr',$main_descr);
  $smarty->assign('action_descr',$action_descr);

  return $ret;

}



/*
  function: do_create 
            do operations on db

  args :
  
  returns: 

*/
function do_create(&$smarty,&$args,&$tproject_mgr)
{
  $main_descr=lang_get('keyword_management');
  $action_descr=lang_get('create_keyword');

  $smarty->assign('main_descr',$main_descr);
  $smarty->assign('action_descr',$action_descr);
  $smarty->assign('submit_button_label',lang_get('btn_save'));
  $smarty->assign('submit_button_action','do_create');
  
  $ret->template='keywordsView.tpl';
  $ret->status = $tproject_mgr->addKeyword($args->testproject_id,$args->keyword,$args->notes);
  return $ret;
}



/*
  function: do_update
            do operations on db

  args :
  
  returns: 

*/
function do_update(&$smarty,&$args,&$tproject_mgr)
{

  $keyword_info = $tproject_mgr->getKeyword($args->keyword_id);
  $main_descr=lang_get('keyword_management');
  $action_descr=lang_get('edit_keyword') . TITLE_SEP . $keyword_info['keyword'];

  $smarty->assign('submit_button_label',lang_get('btn_save'));
  $smarty->assign('submit_button_action','do_update');
  $smarty->assign('main_descr',$main_descr);
  $smarty->assign('action_descr',$action_descr);

  $ret->template='keywordsView.tpl';
  $ret->status = $tproject_mgr->updateKeyword($args->testproject_id,$args->keyword_id,
                                              $args->keyword,$args->notes);

  return $ret;
}


/*
  function: do_delete
            do operations on db

  args :
  
  returns: 

*/
function do_delete(&$smarty,&$args,&$tproject_mgr)
{
  $main_descr=lang_get('testproject') . TITLE_SEP . $args->testproject_name;
  $action_descr=lang_get('edit_keyword');

  $smarty->assign('submit_button_label',lang_get('btn_save'));
  $smarty->assign('submit_button_action','do_update');
  $smarty->assign('main_descr',$main_descr);
  $smarty->assign('action_descr',$action_descr);

  $ret->template='keywordsView.tpl';
  $ret->status = $tproject_mgr->deleteKeyword($args->keyword_id);

  return $ret;
}




/*
  function: 

  args:
  
  returns: 

*/
function getErrorMessage($code)
{

  switch($code)
	{
			case tlKeyword::KW_E_NOTALLOWED:
				$msg = lang_get('keywords_char_not_allowed'); 
				break;
	
			case tlKeyword::KW_E_EMPTY:
				$msg = lang_get('empty_keyword_no');
				break;
	
			case tlKeyword::KW_E_DBERROR:
			case ERROR: 
				$msg = lang_get('kw_update_fails');
				break;
	
			case tlKeyword::KW_E_DUPLICATE:
				$msg = lang_get('keyword_already_exists');
				break;
	
			default:
				$msg = 'ok';
  }
  return $msg;
  
} // getErrorMessage
?>