<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"></meta>
<link rel="stylesheet" href="layersmenu-demo.css" type="text/css"></link>
<?php
require_once 'lib/layersmenu-browser_detection.php';
if ($menuType == 'DOM') {
?>
<link rel="stylesheet" href="layersmenu-index.css" type="text/css"></link>
<?php
} else {
?>
<link rel="stylesheet" href="layersmenu-old.css" type="text/css"></link>
<?php
}
?>
<link rel="shortcut icon" href="LOGOS/shortcut_icon_phplm.png"></link>
<title>The PHP Layers Menu System</title>
<?php
if ($menuType != 'PLAIN') {
?>
<script language="JavaScript" type="text/javascript">
<!--
<?php
	require_once 'libjs/layersmenu-browser_detection.js';
?>
// -->
</script>
<script language="JavaScript" type="text/javascript" src="libjs/layersmenu-library.js"></script>
<script language="JavaScript" type="text/javascript" src="libjs/layersmenu.js"></script>
<?php
}
?>

<?php
require_once 'lib/PHPLIB.php';
require_once 'lib/layersmenu-common.inc.php';

if ($menuType == 'DOM' || $menuType == 'OLD') {
	require_once 'lib/layersmenu.inc.php';
	$mid = new LayersMenu();
} else {
	require_once 'lib/plainmenu.inc.php';
	$mid = new PlainMenu();
}

$mid->setMenuStructureFile('layersmenu-index.txt');
$mid->setIconsize(16, 16);
$mid->parseStructureForMenu('homemenu');

if ($menuType == 'DOM' || $menuType == 'OLD') {
	$mid->setDownArrowImg('down-keramik.png');
	$mid->setForwardArrowImg('forward-keramik.png');
}
if ($menuType == 'DOM') {
	$mid->setHorizontalMenuTpl('layersmenu-horizontal_menu-keramik-full.ihtml');
	$mid->setSubMenuTpl('layersmenu-sub_menu-keramik.ihtml');
} elseif ($menuType == 'OLD') {
	$mid->setHorizontalMenuTpl('layersmenu-horizontal_menu-old.ihtml');
	$mid->setSubMenuTpl('layersmenu-sub_menu-old.ihtml');
} else {
	$mid->setPlainMenuTpl('layersmenu-plain_menu.ihtml');
}

if ($menuType == 'DOM' || $menuType == 'OLD') {
	$mid->newHorizontalMenu('homemenu');
	$mid->printHeader();
}
?>

</head>
<body>

<?php
if ($menuType == 'DOM' || $menuType == 'OLD') {
	$mid->printMenu('homemenu');
} else {
?>
<table border="0" cellpadding="0" cellspacing="0" width="100%">
<tr>
<td valign="top">
<?php
	print $mid->newPlainMenu('homemenu');
?>
</td>
<td valign="top">
<?php
}
?>

<div class="normalbox">
<div class="normal">
<?php require_once 'README.ihtml'; ?>
</div>
</div>

<div class="normalbox">
<div class="normal" align="center">
<a href="http://phplayersmenu.sourceforge.net/"><img border="0"
src="LOGOS/powered_by_phplm.png" alt="Powered by PHP Layers Menu"
height="31" width="88" /></a>&nbsp;<a
href="http://validator.w3.org/check/referer"><img border="0"
src="images/valid-xhtml10.png" alt="Valid XHTML 1.0!"
height="31" width="88" /></a>&nbsp;<a
href="http://jigsaw.w3.org/css-validator/"><img border="0"
src="images/vcss.png" alt="Valid CSS!" height="31" width="88" /></a>
</div>
</div>

<?php
if ($menuType == 'DOM' || $menuType == 'OLD') {
	$mid->printFooter();
} else {
?>
</td>
</tr>
</table>
<?php
}
?>

</body>
</html>
