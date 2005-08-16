<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"></meta>
<link rel="stylesheet" href="layersmenu-demo.css" type="text/css"></link>
<link rel="stylesheet" href="layersmenu-gtk2.css" type="text/css"></link>
<link rel="stylesheet" href="layerstreemenu.css" type="text/css"></link>
<style type="text/css">
<!--
@import url("layerstreemenu-hidden.css");
//-->
</style>
<link rel="shortcut icon" href="LOGOS/shortcut_icon_phplm.png"></link>
<title>The PHP Layers Menu System</title>
<script language="JavaScript" type="text/javascript">
<!--
<?php require_once 'libjs/layersmenu-browser_detection.js'; ?>
// -->
</script>
<script language="JavaScript" type="text/javascript" src="libjs/layersmenu-library.js"></script>
<script language="JavaScript" type="text/javascript" src="libjs/layersmenu.js"></script>
<script language="JavaScript" type="text/javascript" src="libjs/layerstreemenu-cookies.js"></script>

<?php
require_once 'lib/PHPLIB.php';
require_once 'lib/layersmenu-common.inc.php';
require_once 'lib/layersmenu.inc.php';
$mid = new LayersMenu();
$mid->setMenuStructureFile('layersmenu-horizontal-1.txt');
$mid->setIconsize(16, 16);
$mid->parseStructureForMenu('hormenu1');
$mid->newHorizontalMenu('hormenu1');
$mid->setMenuStructureFile('layersmenu-vertical-1.txt');
$mid->parseStructureForMenu('vermenu1');
$mid->newVerticalMenu('vermenu1');
$mid->printHeader();
?>

</head>
<body>

<?php
$mid->printMenu('hormenu1');
?>

<div class="normalbox">
<div class="normal" align="center">
<b><?php print basename(__FILE__); ?> - a file-based example with two Layers Menus and two JavaScript Tree Menus</b>
</div>
</div>

<table width="100%" border="0" cellpadding="0" cellspacing="0">
<tr>
<td width="20%" valign="top">
<div style="height: 3px"></div>
<?php
$mid->printMenu('vermenu1');
?>
<div class="normalbox">
<div class="normal">
A Tree Menu
</div>
<?php
require_once 'lib/treemenu.inc.php';
$treemid = new TreeMenu();
$treemid->setMenuStructureFile('layersmenu-vertical-1.txt');
$mid->setIconsize(16, 16);
$treemid->parseStructureForMenu('treemenu1');
//$treemid->setSelectedItemByCount('treemenu1', 5);
$treemid->setSelectedItemByUrl('treemenu1', basename(__FILE__));
print $treemid->newTreeMenu('treemenu1');
?>
</div>
<div class="normalbox">
<div class="normal">
Another Tree Menu
</div>
<?php
$treemid->setMenuStructureFile('layersmenu-vertical-2.txt');
$treemid->parseStructureForMenu('treemenu2');
$treemid->setTreeMenuTheme('kde_');
print $treemid->newTreeMenu('treemenu2');
?>
</div>
<br />
<center>
<a href="http://phplayersmenu.sourceforge.net/"><img border="0"
src="LOGOS/powered_by_phplm.png" alt="Powered by PHP Layers Menu" height="31" width="88" /></a>
</center>
<br />
<center>
<a href="http://validator.w3.org/check/referer"><img border="0"
src="images/valid-xhtml10.png" alt="Valid XHTML 1.0!" height="31" width="88" /></a>
</center>
<br />
<center>
<a href="http://jigsaw.w3.org/css-validator/"><img border="0"
src="images/vcss.png" alt="Valid CSS!" height="31" width="88" /></a>
</center>
</td>
<td valign="top">
<div class="normalbox">
<div class="normal">
<?php require_once 'README.ihtml'; ?>
</div>
</div>
</td>
</tr>
</table>

<?php
$mid->printFooter();
?>

</body>
</html>
