<?

////////////////////////////////////////////////////////////////////////////////
//File:     executionFrameLeft.php
//Author:   Chad Rosen
//Purpose:  This page is the left frame of the execution pages. It builds the
//	    javascript trees that allow the user to jump around to any point
//	    on the screen
////////////////////////////////////////////////////////////////////////////////

require_once("../functions/header.php");

doSessionStart();
doDBConnect();
doHeader();
//require_once("../../functions/stripTree.php"); //require_once the function that strips the javascript tree
require_once("../functions/generateTreeMenu.php");

$project = $_SESSION['project'];

if($project)
{

	$projectSql = "select id, name from project where id=" . $project;
	$projectResult = mysql_fetch_row(mysql_query($projectSql));
	
	$menustring = ".|" . $projectResult[1] . "|" . "platform/manageData.php?edit=project&data=" . $projectResult[0] .  "|Product||mainFrame|\n";

	$pcSql = "select id,name from platformcontainer where projId=" . $project;
	
	$platConResult = mysql_query($pcSql);

	while ($myrowPlatCon = mysql_fetch_row($platConResult))
	{
		$platConId		= $myrowPlatCon[0];
		$platConName	= $myrowPlatCon[1];

		$menustring .= "..|" . $platConName . "|" . "platform/manageData.php?edit=container&data=" . $platConId .  "|Container||mainFrame|\n";

		$sqlPlatform = "select id, name from platform where containerId=" . $platConId . " order by name";

		$platformResult = mysql_query($sqlPlatform);

		while ($myrowPlatform = mysql_fetch_row($platformResult))
		{
			$platformId		= $myrowPlatform[0];
			$platformName	= $myrowPlatform[1];

			$menustring .=  "...|" . $platformName . "|" . "platform/manageData.php?edit=platform&data=" . $platformId .  "|Platform||mainFrame|\n";
		}
	}


	//This variable is used when the user is using a server side tree. Ignore otherwise
	if(isset($_GET['p']))
	{
		$_SESSION['p'] = $_GET['p'];
	}

	//Table title
	$tableTitle = "Mange Your Test Platforms";
	//Help link
	$helpInfo = "Click <a href='platform/manageData.php' target='mainFrame'>here</a> for help";

	invokeMenu($menustring, $tableTitle, $helpInfo, "edit=" . $_GET['edit'] . "&data=" . $_GET['data'], "");
}

?>