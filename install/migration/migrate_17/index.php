<?php
/* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: index.php,v 1.5 2008/06/27 08:37:50 franciscom Exp $ 
Author: franciscom

rev :
     20080627 - franciscom - added new info for user
     20080504 - franciscom - added warning about php.ini settings
     20080103 - franciscom - fixed path to images

*/

if( !isset($_SESSION) )
{ 
  session_start();
}
$_SESSION['session_test'] = 1;

// configure before creating a new release
$operation='Migration from 1.7.2 (or greater)';
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
    <td><span class="headers">&nbsp;<img src="../../img/dot.gif" alt="" style="margin-top: 1px;" />&nbsp;TestLink <?php echo $_SESSION['testlink_version'] ?> </span></td>
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
      <li> Migration is supported ONLY from version 1.7.2 (or greater) to 1.8.0 </li>
      <li> <span class="headers">Changes will be made to the 1.7.2 database (source database)<br>
                                 Please Backup DB before start</span></li>
      <li>    
      Migration process actions:<br>
      <ul>
      <li>add requirement specifications and requirements to the nodes hierachy table.</li>
      <li>create test case prefix for every test project.</li>
      <li>assign external numeric ID (unique inside every test project) to test cases.</li>
      <li>update of new field (tcversion_number) on executions table.</li>
      </ul>
      </li>    
      </ul>
      <p>
      <span class="headers">Please Backup DB before start</span>
            <ul>
      <li><span class="headers">STEP ONE:</span> Add this page to your bookmarks or save the URL.</li>
      <li><span class="headers">STEP TWO:</span> Go back to the main installation screen and use Upgrade Database option.</li>
      <li><span class="headers">STEP THREE:</span> After successful execution of STEP TWO, return to this page and click
			<a href="migration_start.php"><b>here</b></a> to start data migration/update.</li>
			</ul>
      
      <p>
      <span class="headers">Warning: Know problems related to PHP settings </span>
      <div>
      <pre>
      Some users have reported problems due to insufficiente settings for:
      max_execution_time, max_input_time,memory_limit on php.ini <br>
      Here one set of suggested values used for migration of about 1600 Test cases:
      (contribution by fkueppers)<br>
      max_execution_time = 120
      max_input_time = 120
      memory_limit = 64M
      </pre>
      </div>
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