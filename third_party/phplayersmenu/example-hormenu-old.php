<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"></meta>

<?php
/* TO USE RELATIVE PATHS: */
$myDirPath = '';
$myWwwPath = '';
/* TO USE ABSOLUTE PATHS: */
//$myDirPath = '/home/pratesi/public_html/phplayersmenu/';
//$myWwwPath = '/~pratesi/phplayersmenu/';
?>

<link rel="stylesheet" href="<?php print $myWwwPath; ?>layersmenu-demo.css" type="text/css"></link>
<link rel="stylesheet" href="<?php print $myWwwPath; ?>layersmenu-old.css" type="text/css"></link>
<link rel="shortcut icon" href="<?php print $myWwwPath; ?>LOGOS/shortcut_icon_phplm.png"></link>
<title>The PHP Layers Menu System</title>

<script language="JavaScript" type="text/javascript">
<!--
<?php require_once $myDirPath . 'libjs/layersmenu-browser_detection.js'; ?>
// -->
</script>
<script language="JavaScript" type="text/javascript" src="<?php print $myWwwPath; ?>libjs/layersmenu-library.js"></script>
<script language="JavaScript" type="text/javascript" src="<?php print $myWwwPath; ?>libjs/layersmenu.js"></script>

<?php
require_once $myDirPath . 'lib/PHPLIB.php';
require_once $myDirPath . 'lib/layersmenu-common.inc.php';
require_once $myDirPath . 'lib/layersmenu.inc.php';

//$mid = new LayersMenu(6, 7, 2, 5, 140);
//$mid = new LayersMenu(6, 7, 2, 1);	// Gtk2-like
//$mid = new LayersMenu(3, 8, 1, 1);	// Keramik-like
//$mid = new LayersMenu(3, 9, 2, 1);	// Galaxy-like
$mid = new LayersMenu(-12, 10, 6, 5);	// "Traditional" PHPLM look

/* TO USE RELATIVE PATHS: */
//$mid->setDirroot('./');
////$mid->setLibjsdir('./libjs/');
////$mid->setImgdir('./menuimages/');
//$mid->setImgwww('menuimages/');
////$mid->setIcondir('./menuicons/');
//$mid->setIconwww('menuicons/');
/* either: */
////$mid->setTpldir('./templates/');
//$mid->setHorizontalMenuTpl('layersmenu-horizontal_menu.ihtml');
//$mid->setSubMenuTpl('layersmenu-sub_menu.ihtml');
/* or: (disregarding the tpldir) */
//$mid->setHorizontalMenuTpl('templates/layersmenu-horizontal_menu.ihtml');
//$mid->setSubMenuTpl('templates/layersmenu-sub_menu.ihtml');

/* TO USE ABSOLUTE PATHS: */
//$mid->setDirroot($myDirPath);
////$mid->setLibjsdir($myDirPath . 'libjs/');
////$mid->setImgdir($myDirPath . 'menuimages/');
//$mid->setImgwww($myWwwPath . 'menuimages/');
////$mid->setIcondir($myDirPath . 'menuicons/');
//$mid->setIconwww($myWwwPath . 'menuicons/');
////$mid->setTpldir($myDirPath . 'templates/');
//$mid->setHorizontalMenuTpl('layersmenu-horizontal_menu.ihtml');
//$mid->setSubMenuTpl('layersmenu-sub_menu.ihtml');

$mid->setHorizontalMenuTpl('layersmenu-horizontal_menu-old.ihtml');
$mid->setSubMenuTpl('layersmenu-sub_menu-old.ihtml');
//$mid->setDownArrowImg('down-arrow.png');
//$mid->setForwardArrowImg('forward-arrow.png');
$mid->setMenuStructureFile($myDirPath . 'layersmenu-horizontal-1.txt');
$mid->setIconsize(16, 16);
$mid->parseStructureForMenu('hormenu1');
$mid->newHorizontalMenu('hormenu1');

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
<b><?php print basename(__FILE__); ?> - an "old-style" file-based example with a Horizontal Layers Menu</b>
<div style="height: 5px"></div>
<a href="http://phplayersmenu.sourceforge.net/"><img border="0"
src="<?php print $myWwwPath; ?>LOGOS/powered_by_phplm.png" alt="Powered by PHP Layers Menu"
height="31" width="88" /></a>&nbsp;<a
href="http://validator.w3.org/check/referer"><img border="0"
src="<?php print $myWwwPath; ?>images/valid-xhtml10.png" alt="Valid XHTML 1.0!"
height="31" width="88" /></a>&nbsp;<a
href="http://jigsaw.w3.org/css-validator/"><img border="0"
src="<?php print $myWwwPath; ?>images/vcss.png" alt="Valid CSS!" height="31" width="88" /></a>
</div>
</div>

<div class="normalbox">
<div class="normal">
<?php require_once $myDirPath . 'README.ihtml'; ?>
</div>
</div>

<?php
$mid->printFooter();
/* alternatively:
$footer = $mid->getFooter();
print $footer;
*/
?>

</body>
</html>
