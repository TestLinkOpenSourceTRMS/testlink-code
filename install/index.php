<?php
/* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: index.php,v 1.11 2008/01/20 15:37:19 franciscom Exp $ 

rev:
    20080120 - franciscom - added link to README
    20080103 - franciscom - minor adjustments on link descriptions
*/
require_once("../cfg/const.inc.php");

session_start();
$_SESSION['session_test'] = 1;
$_SESSION['testlink_version']=TL_VERSION;
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<title>Testlink <?php echo $_SESSION['testlink_version'] ?> Install</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
        <style type="text/css">
             @import url('./css/style.css');
        </style>
</head>	

<body>
<table border="0" cellpadding="0" cellspacing="0" class="mainTable">
  <tr class="fancyRow">
    <td><span class="headers">&nbsp;<img src="./img/dot.gif" alt="" style="margin-top: 1px;" />&nbsp;TestLink <?php echo $_SESSION['testlink_version'] ?> </span></td>
    <td align="right"><span class="headers">Installation</span></td>
  </tr>
  <tr class="fancyRow2">
    <td colspan="2" class="border-top-bottom smallText" align="right">&nbsp;</td>
  </tr>
  <tr align="left" valign="top">
    <td colspan="2"><table width="100%"  border="0" cellspacing="0" cellpadding="1">
      <tr align="left" valign="top">
        <td class="pad" id="content" colspan="2">

			<p><b>TestLink Setup</b></p>
			<a href="newInstallStart_TL.php?installationType=new">New installation</a>
			<p />
			<a href="newInstallStart_TL.php?installationType=upgrade">Upgrade Database</a>
			<p />
			<a href="./migration/index.php">Migration from 1.6.2 to 1.7.x </a>
			<p />
			<a href="./migration/migrate_17/index.php">Migration from 1.7.2 (or greater) to 1.8.0 </a>
			<p />
			<p />
			<a href="../README">Access README for install/upgrade details </a>
			
			
		</td>
      </tr>
    </table></td>
  </tr>
  <tr class="fancyRow2">
    <td class="border-top-bottom smallText">&nbsp;</td>
    <td class="border-top-bottom smallText" align="right">&nbsp;</td>
  </tr>
</table>
</body>
</html>