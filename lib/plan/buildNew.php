<?php
/* TestLink Open Source Project - http://testlink.sourceforge.net/ */
/* $Id: buildNew.php,v 1.2 2005/08/16 18:00:57 franciscom Exp $ */
/* Purpose:  admins create new builds for a project */
require('../../config.inc.php');
require("../functions/common.php");
require_once("plan.inc.php");
require("../functions/builds.inc.php");
require_once("../../lib/functions/lang_api.php");
testlinkInitPage();

$builds = getBuilds($_SESSION['testPlanId']);
$smarty = new TLSmarty;

if(isset($_POST['newBuild']))
{
	$sqlResult = 'ok';
	$build = isset($_POST['build']) ? strings_stripSlashes($_POST['build']) : null;
	$notes = isset($_POST['notes']) ? strings_stripSlashes($_POST['notes']) : null;
	//SCHLUNDUS, we should avoid duplicate build identifiers per product
	if (strlen($build))
	{
		$projID = $_SESSION['testPlanId'];
		if (!sizeof($builds) || !in_array($build,$builds))
		{
			if (!insertProjectBuild($build,$projID,$notes))
				$sqlResult = lang_get("cannot_add_build");
		}
		else
			$sqlResult = lang_get("warning_duplicate_build");
	}
	else
		$sqlResult =  lang_get("invalid_build_id");
	
	$smarty->assign('sqlResult', $sqlResult);
	$smarty->assign('name', $build);
}
$buildID = isset($_POST['buildID']) ? intval($_POST['buildID']) : 0;

if ($buildID)
{
	$testPlanID = isset($_SESSION['testPlanId']) ? $_SESSION['testPlanId'] : 0;
	$build = isset($_POST['buildLabel']) ? strings_stripSlashes($_POST['buildLabel']) : null;
	$sqlResult = 'ok';
	if (!deleteTestPlanBuild($testPlanID,$buildID))
		$sqlResult = lang_get("cannot_delete_build");
		
	$smarty->assign('sqlResult', $sqlResult);
	$smarty->assign('name',$build);
	$smarty->assign('action', 'Delete');
}

$builds = getBuilds($_SESSION['testPlanId']);
$notes = getBuildsAndNotes($_SESSION['testPlanId']);

$smarty->assign('TPname', $_SESSION['testPlanName']);
$smarty->assign('arrBuilds', $builds);
$smarty->assign('buildNotes', $notes);
$smarty->display('buildNew.tpl');
?>