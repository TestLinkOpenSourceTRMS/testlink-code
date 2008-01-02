<?php
/* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: index.php,v 1.1 2008/01/02 11:34:21 franciscom Exp $ 

Author: franciscom
*/

if( !isset($_SESSION) )
{ 
  session_start();
}
$_SESSION['session_test'] = 1;

// configure before creating a new release
$_SESSION['testlink_version']='1.8.0';
$operation='Migration from 1.7.2';
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<title>Testlink <?php echo ($_SESSION['testlink_version'] . "-" . $operation) ?> </title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
        <style type="text/css">
             @import url('../../css/style.css');
        </style>
</head>	

<body>
<table border="0" cellpadding="0" cellspacing="0" class="mainTable">
  <tr class="fancyRow">
    <td><span class="headers">&nbsp;<img src="../img/dot.gif" alt="" style="margin-top: 1px;" />&nbsp;TestLink <?php echo $_SESSION['testlink_version'] ?> </span></td>
    <td align="right"><span class="headers"><?php echo $operation ?></span></td>
  </tr>
  <tr class="fancyRow2">
    <td colspan="2" class="border-top-bottom smallText" align="right">&nbsp;</td>
  </tr>
  <tr align="left" valign="top">
    <td colspan="2"><table width="100%"  border="0" cellspacing="0" cellpadding="1">
      <tr align="left" valign="top">
        <td class="pad" id="content" colspan="2">
			<p class="headers">
      Migration Process
      </p>
      
      <ul>
      <li> Migration is supported ONLY from version 1.7.2 to 1.8.0 </li>
      <li> <span class="headers">Changes will be made to the 1.7.2 database (source database)<br>
                                 Please Backup DB before start</span></li>
      <li>    
      Migration process will add requirement specifications and requirements to the nodes hierachy table.
      </li>    
      </ul>
      <p>
      <li><span class="headers">Please Backup DB before start</span>
      <li>Click	<a href="migration_start.php"><b>here</b></a> to start the migration.
			</ul>
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