<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: buildNew.php,v $
 *
 * @version $Revision: 1.25 $
 * @modified $Date: 2007/01/12 21:06:18 $ $Author: schlundus $
 *
 * rev :
 *       20061118 - franciscom - added check_build_name_existence()
 *
*/
require('../../config.inc.php');
require_once("../functions/common.php");
require_once("plan.inc.php");
require_once("../functions/builds.inc.php");
require_once("../../third_party/fckeditor/fckeditor.php");

testlinkInitPage($db);

$tplan_mgr = new testplan($db);

$tpID    = isset($_SESSION['testPlanId']) ? $_SESSION['testPlanId'] : 0;
$buildID = isset($_REQUEST['buildID']) ? intval($_REQUEST['buildID']) : 0;
$build_name = isset($_REQUEST['build_name']) ? trim(strings_stripSlashes($_REQUEST['build_name'])) : null;
$notes = isset($_REQUEST['notes']) ? strings_stripSlashes($_REQUEST['notes']) : null;
$tpName = $_SESSION['testPlanName'];
$the_builds = $tplan_mgr->get_builds($tpID);

$smarty = new TLSmarty();

$of = new fckeditor('notes') ;
$of->BasePath = $_SESSION['basehref'] . 'third_party/fckeditor/';
$of->ToolbarSet = 'TL_Medium';

$build_action = 'newBuild';
$button_value = lang_get('btn_create');

$can_insert_or_update = 0;
$sqlResult =  lang_get("invalid_build_id");

if (strlen($build_name))
{
	$sqlResult = lang_get("warning_duplicate_build");  
	if(sizeof($the_builds) == 0 || 
	   !$tplan_mgr->check_build_name_existence($tpID,$build_name) ||
	   (isset($the_builds[$buildID]) && $the_builds[$buildID] == $build_name))
	{
  	$sqlResult = 'ok';
	  $can_insert_or_update = 1;
	}
}

if(isset($_REQUEST['newBuild']))
{
	if ($can_insert_or_update)
	{
		if (!insertTestPlanBuild($db,$build_name,$tpID,$notes))
		{
			$sqlResult = lang_get("cannot_add_build");
		}	
	}
	$smarty->assign('sqlResult', $sqlResult);
	$build_name = '';
}

if(isset($_REQUEST['del_build']))
{
	$sqlResult = 'ok';
	if (!delete_build($db,$buildID))
	{
		$sqlResult = lang_get("cannot_delete_build");
	}
	$smarty->assign('sqlResult', $sqlResult);
	$smarty->assign('action', 'delete');
}

if(isset($_REQUEST['edit_build']))
{
	if(strcasecmp($_REQUEST['edit_build'], "load_info") == 0 )
	{
		$my_b_info = getBuild_by_id($db,$buildID);
		$build_name = $my_b_info['name'];
		$of->Value = $my_b_info['notes'];
		$build_action = 'edit_build';
		$button_value = lang_get('btn_save');
	}
	else
	{
		$build_action = 'newBuild';
		$button_value = lang_get('btn_create');
		if ($can_insert_or_update)
		{
		   	if (!updateTestPlanBuild($db,$buildID,$build_name,$notes))
		 		$sqlResult = lang_get("cannot_update_build");
		}
		$smarty->assign('sqlResult', $sqlResult);
		$build_name = '';
	}
}

// Refesh data after operation
$the_builds = $tplan_mgr->get_builds($tpID);

$smarty->assign('TPname', $tpName);
$smarty->assign('arrBuilds', $the_builds);
$smarty->assign('build_name', $build_name);
$smarty->assign('notes', $of->CreateHTML());
$smarty->assign('button_name', $build_action);
$smarty->assign('button_value', $button_value);
$smarty->display('buildNew.tpl');
?>
