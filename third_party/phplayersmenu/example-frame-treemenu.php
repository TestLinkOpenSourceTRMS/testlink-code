<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"></meta>
<link rel="stylesheet" href="layersmenu-demo.css" type="text/css"></link>
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
<script language="JavaScript" type="text/javascript" src="libjs/layerstreemenu-cookies.js"></script>
<base target="bodyframe"></base>
</head>
<body style="background-color: #ffffff; margin: 3px;">

<div class="normal">
Tree Menu
</div>
<?php
require_once 'lib/PHPLIB.php';
require_once 'lib/layersmenu-common.inc.php';
require_once 'lib/treemenu.inc.php';
$mid = new TreeMenu();
$mid->setMenuStructureFile('layersmenu-vertical-1.txt');
$mid->setIconsize(16, 16);
$mid->parseStructureForMenu('treemenu1');
$mid->newTreeMenu('treemenu1');
$mid->printTreeMenu('treemenu1');
?>

</body>
</html>
