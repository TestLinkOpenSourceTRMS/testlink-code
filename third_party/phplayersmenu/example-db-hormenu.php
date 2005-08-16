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
require_once 'PEAR.php';
require_once 'DB.php';
require_once 'lib/layersmenu-common.inc.php';
require_once 'lib/layersmenu.inc.php';

$mid = new LayersMenu();

////$mid->setHorizontalMenuTpl('layersmenu-horizontal_menu-old.ihtml');
////$mid->setSubMenuTpl('layersmenu-sub_menu-old.ihtml');
//$mid->setDownArrowImg('down-arrow.png');
//$mid->setForwardArrowImg('forward-arrow.png');
//$mid->setMenuStructureFile('layersmenu-horizontal-1.txt');
//$mid->setIconsize(16, 16);
//$mid->parseStructureForMenu('hormenu1');
$mid->setDBConnParms('mysql://mysql:mysql@localhost/phplayersmenu');
//$mid->setDBConnParms('pgsql://postgres:postgres@localhost/phplayersmenu');
//$mid->setDBConnParms('sqlite:///DUMPS/phplayersmenu');
/* THE DEFAULTS FOR THE DEFAULT LANGUAGE TABLE:
$mid->setTableName('phplayersmenu');
$mid->setTableFields(array(
	'id'		=> 'id',
	'parent_id'	=> 'parent_id',
	'text'		=> 'text',
	'href'		=> 'href',
	'title'		=> 'title',
	'icon'		=> 'icon',
	'target'	=> 'target',
	'orderfield'	=> 'orderfield',
	'expanded'	=> 'expanded'
));
*/
/* UNCOMMENT THE COMMAND BELOW IF YOU DO NOT WANT TO SHOW ICONS
$mid->setTableFields(array(
	'id'		=> 'id',
	'parent_id'	=> 'parent_id',
	'text'		=> 'text',
	'href'		=> 'href',
	'title'		=> 'title',
	'icon'		=> '',
//	'icon'		=> "''",	// this is an alternative to the line above
	'target'	=> 'target',
	'orderfield'	=> 'orderfield',
	'expanded'	=> 'expanded'
));
*/
/* YOU CAN ELIMINATE ICONS ALSO THIS WAY:
$mid->setTableFields(array(
	'icon'		=> ''
));
*/
/* THE DEFAULTS FOR THE I18N TABLE:
$mid->setTableName_i18n('phplayersmenu_i18n');
$mid->setTableFields_i18n(array(
	'language'	=> 'language',
	'id'		=> 'id',
	'text'		=> 'text',
	'title'		=> 'title'
));
*/
/* HOWTO use data taken from the PgMarket 'categories'
   and 'categories_i18n' tables:
$mid->setDBConnParms('pgsql://postgres:postgres@localhost/pgmarket');
//$mid->setDBConnParms('pgsql://mysql:mysql@localhost/pgmarket');
$mid->setTableName('categories');
$mid->setTableFields(array(
	'id'		=> 'id',
	'parent_id'	=> 'parent_id',
	'text'		=> 'name',
	'href'		=> 'id',
	'title'		=> 'description',
//	'title'		=> '',
	'icon'		=> '',
	'target'	=> '',
	'orderfield'	=> 'special_level',
	'expanded'	=> ''
));
$mid->setTableName_i18n('categories_i18n');
$mid->setTableFields_i18n(array(
	'language'	=> 'lang',
	'id'		=> 'category_id',
	'text'		=> 'name',
	'title'		=> ''
));
$mid->setPrependedUrl('/~pratesi/pgmarket/shopping/index.php?id=');
//$mid->setIconsize(16, 16);
//$mid->scanTableForMenu('hormenu1', 'en');
*/
//$mid->setIconsize(16, 16);
//$mid->scanTableForMenu('hormenu1');
$mid->setIconsize(16, 16);
$mid->scanTableForMenu('hormenu1', 'it');
$mid->newHorizontalMenu('hormenu1');

$mid->printHeader();
?>

</head>
<body>

<?php
$mid->printMenu('hormenu1');
?>

<div class="normalbox">
<div class="normal" align="center">
<b><?php print basename(__FILE__); ?> - a DB-based example with a Horizontal Layers Menu</b>
<div style="height: 10px"></div>
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

<?php
$mid->printFooter();
?>

</body>
</html>
