<?php
/* TestLink Open Source Project - http://testlink.sourceforge.net/ */
/* $Id: buildNew.php,v 1.13 2005/10/09 18:13:48 schlundus Exp $ */
/* 
Purpose:  admins create new builds for a project 

@author Francisco Mancardi - 20051006 
added edit build

@author Francisco Mancardi - 20050909
refactoring from project to testplan

@author Francisco Mancardi - 20050826
htmlarea replaced with fckeditor
* 20050710 - am - refactored - removed build_label when deleting and editing
*/
require('../../config.inc.php');
require("../functions/common.php");
require_once("plan.inc.php");
require("../functions/builds.inc.php");
require_once("../../lib/functions/lang_api.php");
require_once("../../third_party/fckeditor/fckeditor.php");
testlinkInitPage();

$tpID = isset($_SESSION['testPlanId']) ? $_SESSION['testPlanId'] : 0;
$buildID = isset($_REQUEST['buildID']) ? intval($_REQUEST['buildID']) : 0;
$build_name = isset($_REQUEST['build_name']) ? trim(strings_stripSlashes($_REQUEST['build_name'])) : null;
$notes = isset($_REQUEST['notes']) ? strings_stripSlashes($_REQUEST['notes']) : null;

$the_builds = getBuilds($tpID, " ORDER BY build.name ");
$smarty = new TLSmarty();

$of = new fckeditor('notes') ;
$of->BasePath = $_SESSION['basehref'] . 'third_party/fckeditor/';
$of->ToolbarSet = 'TL_Medium';

// 20051005 - fm
$build_action = 'newBuild';
$button_value = lang_get('btn_create');

$can_insert_or_update = 0;
$sqlResult =  lang_get("invalid_build_id");
if (strlen($build_name))
{
	$sqlResult = lang_get("warning_duplicate_build");
	//20051007 - am - added check if no buildID (new build)
	if (sizeof($the_builds) == 0 || !in_array($build_name,$the_builds)
	    ||(isset($the_builds[$buildID]) && $the_builds[$buildID] == $build_name))
	{
  		$sqlResult = 'ok';
		$can_insert_or_update = 1;
	}
}

if(isset($_REQUEST['newBuild']))
{
	if ($can_insert_or_update)
	{
		if (!insertTestPlanBuild($build_name,$tpID,$notes))
		{
			$sqlResult = lang_get("cannot_add_build");
		}	
	}
	$smarty->assign('sqlResult', $sqlResult);
	$build_name = '';
}

// 20051005 - fm - refactoring
if(isset($_REQUEST['del_build']))
{
	$sqlResult = 'ok';
	// 20050910 - fm - (my typo bug)
	if (!deleteTestPlanBuild($tpID,$buildID))
	{
		$sqlResult = lang_get("cannot_delete_build");
	}
	$smarty->assign('sqlResult', $sqlResult);
	$smarty->assign('action', 'delete');
}

// 20051005 - fm - refactoring
if(isset($_REQUEST['edit_build']))
{
	if(strcasecmp($_REQUEST['edit_build'], "load_info") == 0 )
	{
		$my_b_info = getBuild_by_id($buildID);
		$build_name = $my_b_info['name'];
		$of->Value = $my_b_info['note'];
		$build_action = 'edit_build';
		$button_value = lang_get('btn_save');
	}
	else
	{
		$build_action = 'newBuild';
		$button_value = lang_get('btn_create');
		if ($can_insert_or_update)
		{
		   	if (!updateTestPlanBuild($buildID,$build_name,$notes))
		   	{
		 		$sqlResult = lang_get("cannot_update_build");
		 	}	
		}
		$smarty->assign('sqlResult', $sqlResult);
		$build_name = '';
	}
}

// 20051002 - fm - change order by
$the_builds = getBuilds($tpID, " ORDER by build.name ");
$notes = getBuildsAndNotes($tpID);

$smarty->assign('TPname', $_SESSION['testPlanName']);
$smarty->assign('arrBuilds', $the_builds);
$smarty->assign('buildNotes', $notes);
$smarty->assign('build_name', $build_name);
$smarty->assign('notes', $of->CreateHTML());
$smarty->assign('button_name', $build_action);
$smarty->assign('button_value', $button_value);
$smarty->display('buildNew.tpl');
?>