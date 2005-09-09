<?php
/* TestLink Open Source Project - http://testlink.sourceforge.net/ */
/* $Id: buildNew.php,v 1.7 2005/09/09 08:36:07 franciscom Exp $ */
/* 
Purpose:  admins create new builds for a project 

@author Francisco Mancardi - 20050909
refactoring from project to testplan

@author Francisco Mancardi - 20050826
htmlarea replaced with fckeditor
*/

require('../../config.inc.php');
require("../functions/common.php");
require_once("plan.inc.php");
require("../functions/builds.inc.php");
require_once("../../lib/functions/lang_api.php");

// 20050826 - fm
require_once("../../third_party/fckeditor/fckeditor.php");
testlinkInitPage();


$tpID = isset($_SESSION['testPlanId']) ? $_SESSION['testPlanId'] : 0;
$buildID = isset($_POST['buildID']) ? intval($_POST['buildID']) : 0;

$builds = getBuilds($tpID);
$smarty = new TLSmarty;

$of = new fckeditor('notes') ;
$of->BasePath = $_SESSION['basehref'] . 'third_party/fckeditor/';
$of->ToolbarSet='TL_Medium';


if(isset($_POST['newBuild']))
{
	$sqlResult = 'ok';
	$build = isset($_POST['build']) ? strings_stripSlashes($_POST['build']) : null;
	$notes = isset($_POST['notes']) ? strings_stripSlashes($_POST['notes']) : null;


	//we should avoid duplicate build identifiers per product
	if (strlen($build))
	{
		if (!sizeof($builds) || !in_array($build,$builds))
		{
			if (!insertTestPlanBuild($build,$tpID,$notes))
				$sqlResult = lang_get("cannot_add_build");
		}
		else{
			$sqlResult = lang_get("warning_duplicate_build");
		}	
	}
	else{
		$sqlResult =  lang_get("invalid_build_id");
  }

	
	$smarty->assign('sqlResult', $sqlResult);
	$smarty->assign('name', $build);
	

}

if ($buildID)
{
	$build = isset($_POST['buildLabel']) ? strings_stripSlashes($_POST['buildLabel']) : null;
	$sqlResult = 'ok';
	if (!deleteTestPlanBuild($testPlanID,$buildID))
	{
		$sqlResult = lang_get("cannot_delete_build");
	}
		
	$smarty->assign('sqlResult', $sqlResult);
	$smarty->assign('name',$build);
	$smarty->assign('action', 'Delete');
}

$builds = getBuilds($tpID);
$notes = getBuildsAndNotes($tpID);

$smarty->assign('TPname', $_SESSION['testPlanName']);
$smarty->assign('arrBuilds', $builds);
$smarty->assign('buildNotes', $notes);

$smarty->assign('notes', $of->CreateHTML());


$smarty->display('buildNew.tpl');
?>