<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: keywordsEdit.php,v $
 *
 * @version $Revision: 1.32.6.3 $
 * @modified $Date: 2011/01/10 15:38:59 $ by $Author: asimon83 $
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
testlinkInitPage($db,false,false,"checkRights");

$smarty = new TLSmarty();

$template_dir = 'keywords/';
$default_template = str_replace('.php','.tpl',basename($_SERVER['SCRIPT_NAME']));

$op = new stdClass();
$op->status = 0;
$msg = '';

$args = init_args();
$canManage = has_rights($db,"mgt_modify_key");
$tprojectMgr = new testproject($db);

$action = $args->doAction;
switch ($action)
{
	case "do_create":
	case "do_update":
	case "do_delete":
		if (!$canManage)
			break;
	case "edit":
	case "create":
		$op = $action($smarty,$args,$tprojectMgr);
	break;
}
if($op->status == 1)
{
	$default_template = $op->template;
}
else
{
	$msg = getKeywordErrorMessage($op->status);
}
$keywords = null;
if ($default_template == 'keywordsView.tpl')
{
	$keywords = $tprojectMgr->getKeywords($args->testproject_id);
}

$smarty->assign('user_feedback',$msg);
$smarty->assign('canManage',$canManage);
$smarty->assign('keywords', $keywords);
$smarty->assign('name',$args->keyword);
$smarty->assign('keyword',$args->keyword);
$smarty->assign('notes',$args->notes);
$smarty->assign('keywordID',$args->keyword_id);
$smarty->assign('mgt_view_events',has_rights($db,"mgt_view_events"));
$smarty->display($template_dir . $default_template);


/**
 * @return object returns the arguments for the page
 */
function init_args()
{
	$args = new stdClass();
	
	$bPostBack = sizeof($_POST);
	$source = $bPostBack ? "POST" : "GET";
	$iParams = array(
			"doAction" => array($source,tlInputParameter::STRING_N,0,50),
			"id" => array($source, tlInputParameter::INT_N),
			"keyword" => array($source, tlInputParameter::STRING_N,0,100),
			"notes" => array($source, tlInputParameter::STRING_N),
		);
		
	$pParams = I_PARAMS($iParams);

	$args = new stdClass();
	$args->doAction = $pParams["doAction"];
	$args->keyword_id = $pParams["id"];
	$args->keyword = $pParams["keyword"];
	$args->notes = $pParams["notes"];

	if ($args->doAction == "edit")
		$_SESSION['s_keyword_id'] = $args->keyword_id;
	else if($args->doAction == "do_update")
		$args->keyword_id = $_SESSION['s_keyword_id'];
	
	$args->testproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
	$args->testproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : 0;
	
	// BUGID 4066 - take care of proper escaping when magic_quotes_gpc is enabled
	$_REQUEST=strings_stripSlashes($_REQUEST);

	return $args;
}

/*
 *	initialize variables to launch user interface (smarty template)
 *	to get information to accomplish create task.
*/
function create(&$smarty,&$args)
{
	$ret = new stdClass();
	$ret->template = 'keywordsEdit.tpl';
	$ret->status = 1;

	$smarty->assign('submit_button_label',lang_get('btn_save'));
	$smarty->assign('submit_button_action','do_create');
	$smarty->assign('main_descr',lang_get('keyword_management'));
	$smarty->assign('action_descr',lang_get('create_keyword'));

	return $ret;
}


/*
 *	initialize variables to launch user interface (smarty template)
 *  to get information to accomplish edit task.
*/
function edit(&$smarty,&$args,&$tproject_mgr)
{
	$ret = new stdClass();
	$ret->template = 'keywordsEdit.tpl';
	$ret->status = 1;

	$action_descr = lang_get('edit_keyword');
	$keyword = $tproject_mgr->getKeyword($args->keyword_id);
	if ($keyword)
	{
		$args->keyword = $keyword->name;
		$args->notes = $keyword->notes;
		$action_descr .= TITLE_SEP . $keyword->name;
	}
	
	$smarty->assign('submit_button_label',lang_get('btn_save'));
	$smarty->assign('submit_button_action','do_update');
	$smarty->assign('main_descr',lang_get('keyword_management'));
	$smarty->assign('action_descr',$action_descr);

	return $ret;
}

/*
 * Creates the keyword
 */
function do_create(&$smarty,&$args,&$tproject_mgr)
{
	$smarty->assign('main_descr',lang_get('keyword_management'));
	$smarty->assign('action_descr',lang_get('create_keyword'));
	$smarty->assign('submit_button_label',lang_get('btn_save'));
	$smarty->assign('submit_button_action','do_create');

	$op = $tproject_mgr->addKeyword($args->testproject_id,$args->keyword,$args->notes);
	$ret = new stdClass();
	$ret->template = 'keywordsView.tpl';
	$ret->status = $op['status'];
	return $ret;
}

/*
 * Updates the keyword
*/
function do_update(&$smarty,&$args,&$tproject_mgr)
{
	$action_descr = lang_get('edit_keyword');
	$keyword = $tproject_mgr->getKeyword($args->keyword_id);
	if ($keyword)
		$action_descr .= TITLE_SEP . $keyword->name;

	$smarty->assign('submit_button_label',lang_get('btn_save'));
	$smarty->assign('submit_button_action','do_update');
	$smarty->assign('main_descr',lang_get('keyword_management'));
	$smarty->assign('action_descr',$action_descr);

	$ret = new stdClass();
	$ret->template = 'keywordsView.tpl';
	$ret->status = $tproject_mgr->updateKeyword($args->testproject_id,$args->keyword_id,
										  $args->keyword,$args->notes);

	return $ret;
}

/*
 * Deletes the keyword 
*/
function do_delete(&$smarty,&$args,&$tproject_mgr)
{
	$main_descr = lang_get('testproject') . TITLE_SEP . $args->testproject_name;

	$smarty->assign('submit_button_label',lang_get('btn_save'));
	$smarty->assign('submit_button_action','do_update');
	$smarty->assign('main_descr',$main_descr);
	$smarty->assign('action_descr',lang_get('edit_keyword'));

	$ret = new stdClass();
	$ret->template = 'keywordsView.tpl';
	$ret->status = $tproject_mgr->deleteKeyword($args->keyword_id);

	return $ret;
}


function getKeywordErrorMessage($code)
{
	switch($code)
	{
		case tlKeyword::E_NAMENOTALLOWED:
			$msg = lang_get('keywords_char_not_allowed'); 
			break;

		case tlKeyword::E_NAMELENGTH:
			$msg = lang_get('empty_keyword_no');
			break;

		case tlKeyword::E_DBERROR:
		case ERROR: 
			$msg = lang_get('kw_update_fails');
			break;

		case tlKeyword::E_NAMEALREADYEXISTS:
			$msg = lang_get('keyword_already_exists');
			break;

		default:
			$msg = 'ok';
  }
  return $msg;
}

/**
 * @param $db resource the database connection handle
 * @param $user the current active user
 * 
 * @return boolean returns true if the page can be accessed
 */
function checkRights(&$db,&$user)
{
	return $user->hasRight($db,'mgt_modify_key') && $user->hasRight($db,'mgt_view_key');
}
?>
