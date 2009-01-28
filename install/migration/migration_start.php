<?php 
/* TestLink Open Source Project - http://testlink.sourceforge.net/ */
/* $Id: migration_start.php,v 1.7 2009/01/28 09:43:22 franciscom Exp $ */

// 20060428 - franciscom - added new check  check_db_loaded_extension()
//
// 20050824 - fm
require_once("../installUtils.php");
require_once('..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.
		'functions'.DIRECTORY_SEPARATOR.'configCheck.php');

session_start(); 
$tl_and_version = "TestLink {$_SESSION['testlink_version']} ";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" 
  "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<title><?php echo $tl_and_version ?>Installer</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link href="../../gui/themes/default/images/favicon.ico" rel="icon" type="image/gif"/>
	<link href="../../gui/themes/default/css/testlink.css" rel="stylesheet" type="text/css" />
        <style type="text/css">
             @import url('../css/style.css');
        </style>

		<script language="JavaScript">
		function validate() {
			var f = document.myForm;
			if(f.source_databasename.value=="") {
				alert('You need to enter a value for Source database name!');
				return false;
			}
      
			// 20060215 - franciscom
			if( f.source_databasename.value.indexOf('/') >= 0 ||
			    f.source_databasename.value.indexOf('\\') >= 0 ||
          f.source_databasename.value.indexOf('.') >= 0 )
			{
				alert('Source Database name contains forbbiden characters!');
				return false;
			}
			if(f.target_databasename.value=="") {
				alert('You need to enter a value for TARGET database name!');
				return false;
			}
			if( f.target_databasename.value.indexOf('/') >= 0 ||
			    f.target_databasename.value.indexOf('\\') >= 0 ||
          f.target_databasename.value.indexOf('.') >= 0 )
			{
				alert('TARGET Database name contains forbbiden characters!');
				return false;
			}
			if(f.databasehost.value=="") {
				alert('You need to enter a value for database host!');
				return false;
			}
			if(f.databaseloginname.value=="") {
				alert('You need to enter your database login name (with Administrative Rights)!');
				return false;
			}

      /*			
			20060831 - franciscom
			if(f.tl_loginname.value=="") {
				alert('You need to enter your TestLink database login name (For Normal TestLink Operation)!');
				return false;
			}

			if(f.tl_loginpassword.value=="") {
				alert('You need to enter your TestLink database password (For Security empty password is not allowed)!');
				return false;
			}
			*/
			
			return true;
		}
		</script>
</head>	

<?php
$inst_type = $_GET['installationType'];
$main_title = "Testlink Migration";
$explain_msg = '<p>' . $main_title . 
               ' will carry out a number of checks ' .
               " to see if everything's ready to start the process. </br>";
$the_msg = '<p><b>' . $main_title . '</b></p>' . $explain_msg;
?>

<body>
<table border="0" cellpadding="0" cellspacing="0" class="mainTable">
  <tr class="fancyRow">
    <td><span class="headers">&nbsp;<img src="../img/dot.gif" alt="" style="margin-top: 1px;" />&nbsp;<?php echo $tl_and_version ?></span></td>
    <td align="right"><span class="headers"><?php echo $inst_type?></span></td>
  </tr>
  <tr class="fancyRow2">
    <td colspan="2" class="border-top-bottom smallText" align="right">&nbsp;</td>
  </tr>
  <tr align="left" valign="top">
    <td colspan="2">

   	<table width="100%"  border="0" cellspacing="0" cellpadding="1">
      <tr align="left" valign="top">
        <td class="pad" id="content" colspan="2">

<?php
echo $the_msg;

$errors = 0;
reportCheckingSystem($errors);
reportCheckingWeb($errors);
reportCheckingPermissions($errors,$inst_type);

if($errors>0) {
?>
<br />
<br />
Unfortunately, TestLink setup cannot continue at the moment, due to the above 
<?php echo $errors > 1 ? $errors." " : "" ; ?>error<?php echo $errors > 1 ? "s" : "" ; ?> . 
<br>Please correct the error<?php echo $errors > 1 ? "s" : "" ; ?>, and try again. 
<br>If you need help figuring out how to fix the problem<?php echo $errors > 1 ? "s" : "" ; ?>, 
please visit the <a href="http://www.teamst.org" target="_blank">TestLink Forums [click here]</a>.
<br />
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
<?php
exit;
}
?>
		<h2>Database Configuration</h2>

		<form action="../license.php" method="post" name="myForm" onsubmit="return validate()">

        <input type="hidden" name="installationType" value="<?php  echo $inst_type; ?>">
        <input type="hidden" name="page2launch" value="./migration/migrate_16_to_17.php">

        <div>
		  	<div class="labelHolder">
		  		<label for="databasetype">Database Type</label>
			</div>

					<select id="databasetype" name="databasetype">
						<option value="mysql" selected>MySQL</option>
            <!---
            20060729 - franciscom -
            Migration only for MySQL
						<option value="postgres" >Postgres 8.0/8.1 (not tested)</option>
						<option value="mssql" >Microsoft SQL Server (not tested)</option>
						- -->
					</select>	
					<br />
					
					
					<div class="labelHolder">
						<label for="databasehost">Database host:</label>
					</div>
					<input type="text" id="databasehost" name="databasehost" 
					                   value="localhost" style="width:200px" /><br />
				</div>
				<p>
				
				<div class="fancy">
				 Name of your existing TestLink 1.6.2 database<p>
				<div class="labelHolder"><label for="source_databasename">Source Database name:</label></div>
					<input type="text" id="source_databasename" name="source_databasename" 
                 maxlength="50" 
					       style="width:200px" value="testlink_16"><br />
        </div>
				<p class="fancy">

				<p>
				<div class="fancy_2">
        Name of your TestLink 1.7.0 database (created using New Installation)<p>
					<div class="labelHolder"><label for="target_databasename">TARGET Database name:</label></div>
					<input type="text" id="target_databasename" name="target_databasename" 
                 maxlength="50" 
					       style="width:200px" value="testlink_17"><br />
				</div>
				</p>
  		</td>
      </tr>
      
      <tr>
			<td>
    		<p class="headers">
					Database User with administrative rights.
				</p>
				
				<p class="smallText">
				This user requires administrative permission the Database Server.<br>
        This value is used only for these installation procedures, and is not saved.
			  </p>
			
				<p>
					
					<div class="labelHolder"><label for="databaseloginname">Database login:</label></div><input type="text" id="databaseloginname" name="databaseloginname" style="width:200px" /><br />
					<div class="labelHolder"><label for="databaseloginpassword">Database password:</label></div><input type="password" id="databaseloginpassword" name="databaseloginpassword" style="width:200px" /><br />
				</p>

				<p class="headers">
					Database User for Normal Testlink use.
				</p>
        <p class="smallText">
				This user will have permission only to work on TestLink databases.<br>
        All connections to the Database Server during normal operation will be done with this user.
			  </p>

				<p>
					<div class="labelHolder">
						<label for="tl_loginname">TestLink DB login:</label>
					</div>
					<input type="text" id="tl_loginname" name="tl_loginname" style="width:200px" /><br />
					
					<div class="labelHolder">
						<label for="tl_loginpassword">TestLink DB password:</label>
					</div>
					<input type="password" id="tl_loginpassword" name="tl_loginpassword" style="width:200px" /><br />
				</p>
				
				<p> &nbsp;</p>
				<p>
					<input type="submit" value="Start migration!">
				</p>
				
			</form>	

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



