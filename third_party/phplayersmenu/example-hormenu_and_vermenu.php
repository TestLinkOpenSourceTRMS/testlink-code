<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"></meta>
<link rel="stylesheet" href="layersmenu-demo.css" type="text/css"></link>
<link rel="stylesheet" href="layersmenu-gtk2.css" type="text/css"></link>
<link rel="shortcut icon" href="LOGOS/shortcut_icon_phplm.png"></link>
<title>The PHP Layers Menu System</title>

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

//$mid->setDirroot('./');
////$mid->setLibjsdir('./libjs/');
////$mid->setImgdir('./menuimages/');
//$mid->setImgwww('menuimages/');
////$mid->setIcondir('./menuicons/');
//$mid->setIconwww('menuicons/');
////$mid->setTpldir('./templates/');
//$mid->setHorizontalMenuTpl('layersmenu-horizontal_menu.ihtml');
//$mid->setVerticalMenuTpl('layersmenu-vertical_menu.ihtml');
//$mid->setSubMenuTpl('layersmenu-sub_menu.ihtml');

//$mid->setDownArrowImg('down-arrow.png');
//$mid->setForwardArrowImg('forward-arrow.png');
$mid->setMenuStructureFile('layersmenu-horizontal-1.txt');
$mid->setIconsize(16, 16);
$mid->parseStructureForMenu('hormenu1');
$mid->newHorizontalMenu('hormenu1');
$mid->setMenuStructureFile('layersmenu-vertical-2.txt');
$mid->parseStructureForMenu('vermenu1');
$mid->newVerticalMenu('vermenu1');

$mid->printHeader();
/* alternatively:
$header = $mid->getHeader();
print $header;
*/
?>

</head>
<body>

<?php
$mid->printMenu('hormenu1');
/* alternatively:
$hormenu1 = $mid->getMenu('hormenu1');
print $hormenu1;
*/
?>

<div class="normalbox">
<div class="normal" align="center">
<b><?php print basename(__FILE__); ?> - a file-based example with a Horizontal and a Vertical Layers Menu</b>
</div>
</div>

<table width="100%" border="0" cellpadding="0" cellspacing="0">
<tr>
<td width="20%" valign="top">
<div style="height: 3px"></div>
<?php
$mid->printMenu('vermenu1');
/* alternatively:
$vermenu1 = $mid->getMenu("vermenu1");
print $vermenu1;
*/
?>
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
/* alternatively:
$footer = $mid->getFooter();
print $footer;
*/
?>

</body>
</html>
