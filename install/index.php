<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Navigation for installation scripts
 *
 * @package 	TestLink
 * @copyright 2007,2012 TestLink community
 * @version   index.php
 *
 * @internal revisions
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
	<style type="text/css">@import url('./css/style.css');</style>
</head>

<body>
<div class="tlPager">
<h1><img src="./img/dot.gif" alt="Dot" style="margin: 0px 10px;" />
    TestLink <?php echo $_SESSION['testlink_version'] ?> Installation</h1>
<div class="tlLiner">&nbsp;</div>
<div class="tlStory">
		<p>You are installing TestLink 1.9.5 </p>
		<p><b>Migration from 1.9.3 to 1.9.5 has to be done MANUALLY.</b></p> 
		<p>Please read Section on README file or go to www.teamst.org (Forum: TestLink 1.9.5 News,changes, etc) </p>
		<p>Open <a target="_blank" href="../docs/testlink_installation_manual.pdf">Installation manual</a>
		for more information or troubleshooting. You could also look at
		<a href="../README">README</a> or <a href="../CHANGELOG">Changes Log</a>.
		You are welcome to visit our <a target="_blank" href="http://www.teamst.org">
		forum</a> to browse or discuss.
		</p>
		<p><ul>
		<li><a href="installIntro.php?type=new">New installation</a></li>
		</ul></p>
</div>
<div class="tlLiner">&nbsp;</div>

</div>
</body>
</html>