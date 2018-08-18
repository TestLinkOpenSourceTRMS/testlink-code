<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Navigation for installation scripts
 *
 * @package 	TestLink
 * @copyright 	2007, TestLink community
 * @version    	CVS: $Id: index.php,v 1.18 2010/12/12 13:45:47 franciscom Exp $
 *
 * @internal Revisions:
 *  20091103 - havlatm - Total GUI redesign
 *  20091003 - franciscom - removed option to upgrade/migrate from 1.6.x and 1.7.x
 *	20090127 - franciscom - removed upgrade block
 *	20080120 - franciscom - added link to README
 *	20080103 - franciscom - minor adjustments on link descriptions
 */

require_once("../cfg/const.inc.php");

session_start();
$_SESSION['session_test'] = 1;
$_SESSION['testlink_version'] = TL_VERSION;

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<title>Testlink <?php echo $_SESSION['testlink_version'] ?> Installation procedure</title>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
	<link href="../gui/themes/default/images/favicon.ico" rel="icon" type="image/gif"/>

	<!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
        <!-- jQuery library -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        <!-- Latest compiled JavaScript -->
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

	<style type="text/css">@import url('./css/style.css');</style>
</head>

<body>
<div class="tlPager">
<h1><img src="./img/dot.gif" alt="Dot" style="margin: 0px 10px;" />
    TestLink <?php echo $_SESSION['testlink_version'] ?> Installation</h1>
<div class="tlLiner">&nbsp;</div>
<div class="tlStory">
		<p>You are installing TestLink. Select your case 'New installation'
		or 'Upgrade from older version'.</p>
		<p>Open <a target="_blank" href="../docs/testlink_installation_manual.pdf">Installation manual</a>
		for more information or troubleshooting. You could also look at
		<a href="../README">README</a> or <a href="../CHANGELOG">Changes Log</a>.
		You are welcome to visit our <a target="_blank" href="http://www.teamst.org">
		forum</a> to browse or discuss.
		</p>
		<p><ul>
		<li><a href="installIntro.php?type=new">New installation</a></li>
   		<!--
   		Removed till time when will be avilable
   		<li><a href="installIntro.php?type=upgrade_1.9_to_2.0">Upgrade from 1.9.0
   		versions to 2.0 </a>. Older releases should be migrated to 1.9.0 version at first.</li>
   		-->
		</ul></p>
</div>
<div class="tlLiner">&nbsp;</div>

</div>
</body>
</html>
