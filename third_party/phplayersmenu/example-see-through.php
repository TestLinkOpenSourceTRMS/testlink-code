<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"></meta>
<link rel="stylesheet" href="layersmenu-demo.css" type="text/css"></link>
<link rel="stylesheet" href="layersmenu-gtk2.css" type="text/css"></link>
<link rel="shortcut icon" href="LOGOS/shortcut_icon_phplm.png"></link>
<title>The PHP Layers Menu System - 'New Generation' version</title>

<script language="JavaScript" type="text/javascript">
<!--
<?php require_once 'libjs/layersmenu-browser_detection.js'; ?>
// -->
</script>
<script language="JavaScript" type="text/javascript" src="libjs/layersmenu-library.js"></script>
<script language="JavaScript" type="text/javascript" src="libjs/layersmenu.js"></script>

<?php
require_once 'lib/PHPLIB.php';
require_once 'lib/layersmenu-common.inc.php';
require_once 'lib/layersmenu.inc.php';

$mid = new LayersMenu();

$mid->setMenuStructureFile('layersmenu-horizontal-1.txt');
////$mid->setHorizontalMenuTpl('layersmenu-horizontal_menu-old.ihtml');
////$mid->setSubMenuTpl('layersmenu-sub_menu-old.ihtml');
//$mid->setDownArrowImg('down-arrow.png');
//$mid->setForwardArrowImg('forward-arrow.png');
$mid->setIconsize(16, 16);
$mid->parseStructureForMenu('hormenu1');
$mid->newHorizontalMenu('hormenu1');

//$mid->setMenuStructureFile('layersmenu-vertical-2.txt');
$mid->setMenuStructureFile('layersmenu-horizontal-1.txt');
////$mid->setVerticalMenuTpl('layersmenu-vertical_menu-old.ihtml');
$mid->parseStructureForMenu('vermenu1');
$mid->newVerticalMenu('vermenu1');

$mid->printHeader();
?>

</head>

<body>

<?php $mid->printMenu('hormenu1'); ?>

<table cellspacing="0" cellpadding="0" border="0" width="100%">
<tr>
<td valign="top">

<?php $mid->printMenu('vermenu1'); ?>

</td>
<td valign="top" width="100%">

<!-- ***** -->
<div id="phplmseethrough" class="normalbox">
<!-- ***** -->
<table cellspacing="0" cellpadding="5" border="0" width="100%">
<tr>
<td valign="top" align="right">
<form action="" class="normal">
Select list (select)
<select class="normal">
<option>GNU/Linux distributions</option>
<option>FreeBSD</option>
<option>NetBSD</option>
<option>OpenBSD</option>
</select>
<br />
Text area (textarea)<textarea cols="15" rows="2">Multiple Row and Column Text Input Field</textarea>
<br />
<br />
File upload field (input) <input type="file" size="5" /><br />
Submit button (input) <input type="submit" class="normal" /><br />
Reset button (input) <input type="reset" class="normal" /><br />
Custom button (input) <input type="button" value="Custom" class="normal" />
</form>
</td>
<td valign="top" align="right">
<form action="" class="normal">
Text field (input) <input type="text" size="5" /><br />
Password field (input) <input type="password" size="5" /><br />
Checkbox (input) <input type="checkbox" checked="checked" /><br />
Radio button (input) <input type="radio" checked="checked" />
<br />
<br />
<br />
Select list (select)
<select size="2" class="normal">
<option>Debian</option>
<option>Mandrake</option>
<option>Red Hat</option>
<option>Slackware</option>
<option>SuSE</option>
</select>
</form>
</td>
</tr>
</table>
<!-- ***** -->
</div>
<!-- ***** -->

<div class="normalbox">
<div class="normal">
<?php print basename(__FILE__); ?>
</div>
</div>

</td>
</tr>
</table>

<?php
$mid->printFooter();
?>

<script language="JavaScript" type="text/javascript" src="libjs/layersmenu-see-through.js"></script>

</body>
</html>
