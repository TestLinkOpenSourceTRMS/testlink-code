<?php

require_once("header.php");

$myDirPath = _TREE_DIR_PATH;
$myWwwPath = _TREE_WWW_PATH;

?>

<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"></meta>
<link rel="stylesheet" href="<?php print $myWwwPath; ?>layersmenu-demo.css" type="text/css"></link>
<link rel="stylesheet" href="<?php print $myWwwPath; ?>layerstreemenu.css" type="text/css"></link>
<style type="text/css">
<!--
@import url("layerstreemenu-hidden.css");
//-->
</style>
<link rel="shortcut icon" href="LOGOS/shortcut_icon_phplm.png"></link>
<script language="JavaScript" type="text/javascript">
<!--
<?php require_once $myDirPath . 'libjs/layersmenu-browser_detection.js'; ?>
// -->
</script>
<script language="JavaScript" type="text/javascript" src="<?php print $myWwwPath; ?>libjs/layerstreemenu-cookies.js"></script>

<?php

function invokeMenu($menustring, $tableTitle, $helpInfo)
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

	require_once $myDirPath . 'lib/PHPLIB.php';
	require_once $myDirPath . 'lib/layersmenu-common.inc.php';
	require_once $myDirPath . 'lib/treemenu.inc.php';
	$mid = new TreeMenu();
	$mid->setLibjsdir($myDirPath . '/libjs/');
	$mid->setImgdir($myDirPath . '/menuimages/');
	$mid->setImgwww($myWwwPath . 'menuimages/');
	$mid->setIcondir($myDirPath . '/menuicons/');
	$mid->setIconwww($myWwwPath . 'menuicons/');
	$mid->setMenuStructureFile( $myDirPath . 'layersmenu-vertical-1.txt');

	$mid->setMenuStructureString($menustring);

	$mid->setIconsize(16, 16);
	$mid->parseStructureForMenu('treemenu1');

	print $mid->newTreeMenu('treemenu1');
}

?>
</div>