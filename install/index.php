<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: index.php,v $
 * @version $Revision: 1.14 $
 * @modified $Date: 2009/05/29 11:01:53 $ by $Author: havlat $
 *
 * SCOPE: Navigation for installation scripts
 *
 * Revisions:
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
	<title>Testlink <?php echo $_SESSION['testlink_version'] ?> Install</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<style type="text/css">@import url('./css/style.css');</style>
</head>	

<body>
<table border="0" cellpadding="0" cellspacing="0" class="mainTable">
  <tr class="fancyRow">
    <td><span class="headers">&nbsp;<img src="./img/dot.gif" alt="" style="margin-top: 1px;" />
    &nbsp;TestLink <?php echo $_SESSION['testlink_version'] ?> Installation</span></td>
  </tr>
  <tr class="fancyRow2">
    <td class="border-top-bottom smallText">&nbsp;</td>
  </tr>
  <tr align="left" valign="top">
    <td>

		<p>You are installing TestLink. Open Installation manual and select your case 'New installation' 
		or 'Migration from older version'.</p>
		<p>Show <a target="_blank" href="../docs/installation_manual.pdf">Installation manual</a>,
		<a href="../README">README</a> and
		<a href="../CHANGELOG">Changes Log</a>
		</p>
		<ul>
		<li><a href="newInstallStart_TL.php?installationType=new">New installation</a></li>
   		<li>Upgrade from 1.7.1 (and later hot-fix) to 1.8.0 (and later hot-fix)<br />
   		<ol>
   			<li><a href="newInstallStart_TL.php?installationType=upgrade">Upgrade Database schema</a></li>
			<li><a href="./migration/migrate_17/index.php">Data migration </a></li>
		</ol></li>
		<li><a href="./migration/index.php">Migration from 1.6.2 to 1.7.x </a></li>
		</ul>
		<p style="margin-top: 100px;">&nbsp;</p>
			
    </td>
  </tr>
  <tr class="fancyRow2">
    <td class="border-top-bottom smallText">&nbsp;</td>
  </tr>
</table>
</body>
</html>