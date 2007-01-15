<?php
/* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: index.php,v 1.6 2007/01/15 08:03:18 franciscom Exp $ 

20070113 - franciscom - updated version string
20060218 - franciscom
*/

session_start();
$_SESSION['session_test'] = 1;

// 20060523 - franciscom - configure before creating a new release
$_SESSION['testlink_version']='1.7 beta';
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
			<a href="newInstallStart_TL.php?installationType=upgrade">Upgrade installation</a>
			</p>
			<a href="./migration/index.php">Migration from 1.6.2 to 1.7.0 </a>
			</p>
			
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