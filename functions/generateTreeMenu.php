<?php

require_once("header.php");

$myDirPath = _TREE_DIR_PATH;
$myWwwPath = _TREE_WWW_PATH;

require_once $myDirPath . 'lib/PHPLIB.php';
require_once $myDirPath . 'lib/layersmenu-common.inc.php';
require_once $myDirPath . 'lib/treemenu.inc.php';
require_once $myDirPath . 'lib/phptreemenu.inc.php';

?>

<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"></meta>
<link rel="stylesheet" href="<?php print $myWwwPath; ?>layersmenu-demo.css" type="text/css"></link>
<link rel="stylesheet" href="<?php print $myWwwPath; ?>layerstreemenu.css" type="text/css"></link>
<style type="text/css">
<!--
@import url("layerstreemenu-hidden.css");
//-->
</style>

<script language="JavaScript" type="text/javascript">
<!--
<?php require_once $myDirPath . 'libjs/layersmenu-browser_detection.js'; ?>
// -->
</script>
<script language="JavaScript" type="text/javascript" src="<?php print $myWwwPath; ?>libjs/layerstreemenu-cookies.js"></script>


<?php


function invokeMenu($menustring, $tableTitle, $helpInfo, $highLight, $menuCompileSource)
{
	//define variables as global so that we can use them in the function
	global $myDirPath, $myWwwPath;

	if($tableTitle)
	{

	?>

	<div class="normalbox">
	<div class="normal" align="center">
	<b>
		<? echo $tableTitle; ?>
	</b>
	</div>
	</div>

	<?

	}

	if($helpInfo)
	{
	?>
	<div class="normalbox">
	<div class="normal" align="center">
		<? echo $helpInfo; ?>
	</div>
	</div>
	<?
	}
	
	//Check to see if the user wants to create a server side or client side tree
	//See comments below for more info
	if(_MENU_COMPILE_SOURCE == "SERVER" || $menuCompileSource == "SERVER")
	{
		//server side
		$mid = new PHPTreeMenu();
	}
	else
	{	
		//client side
		$mid = new TreeMenu();
	}

	$mid->setLibjsdir($myDirPath . '/libjs/');
	$mid->setImgdir($myDirPath . '/menuimages/');
	$mid->setImgwww($myWwwPath . 'menuimages/');
	$mid->setIcondir($myDirPath . '/menuicons/');
	$mid->setIconwww($myWwwPath . 'menuicons/');

	$mid->setMenuStructureString($menustring);
	$mid->parseStructureForMenu('treemenu1');
	
	//I had to figure this one out on my own.
	//The method I'm using will color an item in the tree if you pass it a value
	if($highLight != "")
	{
		$mid->setSelectedItemByUrl('treemenu1', $highLight);
	}

	/*
		Here I'm checking if the user wants to display the tree as a server side or client side menu

		If the user set the _MENU_COMPILE_SOURCE variable to SERVER in the config then all trees will default to 
		server side. Trees can be set to client in the config file but also be overridden by setting the 5th
		variable in the invokeMenu function to SERVER
	*/

	if(_MENU_COMPILE_SOURCE == "SERVER" || $menuCompileSource == "SERVER")
	{
		/*
			By default the php tree does not stay open unless you specifically
			tell it to using the setPHPTreeMenuDefaultExpansion.

			So, instead of passing it around to every single page I'm just setting it on the session.
			If it is set it will open the menu to the propper place. Otherwise it does nothing
		*/

		if(isset($_SESSION['p']))
		{
			$mid->setPHPTreeMenuDefaultExpansion($_SESSION['p']);
		}
		print $mid->newPHPTreeMenu('treemenu1');
	}
	else
	{
		//print the client side menu
		print $mid->newTreeMenu('treemenu1');
	}
	
}

?>
</div>