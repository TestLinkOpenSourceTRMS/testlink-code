<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"></meta>
<link rel="stylesheet" href="layersmenu-demo.css" type="text/css"></link>
<link rel="stylesheet" href="layersmenu-old.css" type="text/css"></link>
<link rel="shortcut icon" href="LOGOS/shortcut_icon_phplm.png"></link>
<title>The PHP Layers Menu System</title>
</head>
<body>

<div class="normalbox">
<div class="normal" align="center">
<?php
require_once 'lib/PHPLIB.php';
require_once 'lib/layersmenu-common.inc.php';
require_once 'lib/plainmenu.inc.php';
$hpmid = new PlainMenu();
//$hpmid->setHorizontalPlainMenuTpl('layersmenu-horizontal_plain_menu.ihtml');
$hpmid->setMenuStructureFile('layersmenu-horizontal-2.txt');
$hpmid->setIconsize(16, 16);
$hpmid->parseStructureForMenu('phormenu');
print $hpmid->newHorizontalPlainMenu('phormenu');
?>
</div>
</div>

<div class="normalbox">
<div class="normal" align="center">
<b><?php print basename(__FILE__); ?> - a file-based example with a Horizontal Plain Menu</b>
<div style="height: 5px"></div>
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

<div class="normalbox">
<div class="normal">
<?php require_once 'README.ihtml'; ?>
</div>
</div>

</body>
</html>
