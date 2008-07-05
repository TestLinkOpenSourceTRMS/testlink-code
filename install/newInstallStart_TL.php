<?php 
/* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: newInstallStart_TL.php,v 1.21 2008/07/05 12:53:56 franciscom Exp $

rev: 20080219 - franciscom - fixed dir permission checking
*/

require_once("installUtils.php");

if( !isset($_SESSION) )
{ 
  session_start();
}
$tl_and_version = "TestLink {$_SESSION['testlink_version']} ";

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" 
  "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<title><?php echo $tl_and_version ?>Installer</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
        <style type="text/css">
             @import url('./css/style.css');
        </style>
</head>	

<?php
$inst_type = $_GET['installationType'];

$main_title = "Testlink Setup";
$explain_msg = '<p>' . $main_title . 
               ' will carry out a number of checks ' .
               " to see if everything's ready to start the setup. </br>";
$the_msg = '<p><b>' . $main_title . '</b></p>' . $explain_msg;
?>

<body>
<table border="0" cellpadding="0" cellspacing="0" class="mainTable">
  <tr class="fancyRow">
    <td><span class="headers">&nbsp;<img src="./img/dot.gif" alt="" style="margin-top: 1px;" />&nbsp;<?php echo $tl_and_version ?></span></td>
    <td align="right"><span class="headers">Installation - <?php echo $inst_type; ?> </span></td>
  </tr>
  <tr class="fancyRow2">
    <td colspan="2" class="border-top-bottom smallText" align="right">&nbsp;</td>
  </tr>
  <tr>
 
  <tr align="left" valign="top">

    <td colspan="2">
    <table width="100%"  border="0" cellspacing="0" cellpadding="1">
      <tr align="left" valign="top">
    <td> <img src="./img/dot.gif" alt="" style="margin-top: 1px;" />
		<b>Important Notice</b><br>
 			 Testlink can not be installed (using this installer) on a database/schema used by another 
			 application, because part of the installation process consist on dropping all tables 
			 present on the database/schema, if you use an existing database/schema, WITHOUT ASKING YOU.<br>
    </td>
    </tr>
    <tr class="fancyRow2">
    <td colspan="2" class="border-top-bottom smallText" align="right">&nbsp;</td>
    </tr>
    
        
      <tr align="left" valign="top">
        <td class="pad" id="content" colspan="2">

	
<?php
echo $the_msg;

$errors = 0;

$check = check_php_version();
$errors += $check['errors'];
echo $check['msg'];

$check = check_php_settings();
$errors += $check['errors'];
echo $check['msg'];

// 20071107 - franciscom
// $check = check_db_loaded_extension();
// $errors += $check['errors'];
// echo $check['msg'];


$check = check_session();
$errors += $check['errors'];
echo $check['msg'];

$dirs_to_check=array('../gui/templates_c', '../logs');
$check = check_with_feedback($dirs_to_check);
echo $check['msg'];
$errors += $check['errors'];

?>



<?php
if($errors>0) {
?>
<br />
<br />
Unfortunately, TestLink setup cannot continue at the moment, due to the above <?php echo $errors > 1 ? $errors." " : "" ; ?>error<?php echo $errors > 1 ? "s" : "" ; ?> . 
<br>Please correct the error<?php echo $errors > 1 ? "s" : "" ; ?>, 
and try again. If you need help figuring out how to fix the problem<?php echo $errors > 1 ? "s" : "" ; ?>, 
please visit <a href="http://www.teamst.org" target="_blank">TestLink Forums [click here]</a>.
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
<br />
<br />
		<script language="JavaScript">
		function validate() {
			var f = document.myForm;
			if(f.databasename.value=="") {
				alert('You need to enter a value for database name!');
				return false;
			}
      
			// 20060215 - franciscom
			if( f.databasename.value.indexOf('/') >= 0 ||
			    f.databasename.value.indexOf('\\') >= 0 ||
          f.databasename.value.indexOf('.') >= 0 )
			{
				alert('Database name contains forbbiden characters!');
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
			
			if(f.tl_loginname.value=="") {
				alert('You need to enter your TestLink database login name (For Normal TestLink Operation)!');
				return false;
			}

			if(f.tl_loginpassword.value=="") {
				alert('You need to enter your TestLink database password (For Security empty password is not allowed)!');
				return false;
			}

			
			/*
			if(f.cmsadmin.value=="") {
				alert('You need to enter a username for the TestLink admin account!');
				return false;
			}
			if(f.cmspassword.value=="") {
				alert('You need to a password for the TestLink admin account!');
				return false;
			}
			if(f.cmspassword.value!=f.cmspasswordconfirm.value) {
				alert('The administrator password and the confirmation don\'t match!');
				return false;
			}
			*/
			return true;
		}
		</script>
				<form action="license.php" method="post" name="myForm" onsubmit="return validate()">

         <input type="hidden" name="installationType" value="<?php  echo $inst_type; ?>">
         <input type="hidden" name="page2launch" value="installNewDB.php">
         <?php 
          echo ewigth($inst_type); 
         ?>

         <table width="100%"  border="0" cellspacing="0" cellpadding="1">
               <tr align="left" valign="top">
             <td> <img src="./img/dot.gif" alt="" style="margin-top: 1px;" />
         		<b>Important Notice</b><br>
				 	if You DO NOT USE STANDARD Port for DB connection,
				 	you need to add '<b>:port_number</b>', at the end Database host parameter.<br>
				 	Example:	you use MySQL running on port 6606, on server matrix 
				 	then Database host will be -> <b>matrix:6606</b><br>
             </td>
             </tr>
    <tr class="fancyRow2">
    <td colspan="2" class="border-top-bottom smallText" align="right">&nbsp;</td>
    </tr>

         </table>
         <p />
					
					Database Configuration:<p />
					<div class="labelHolder">
						<label for="databasetype">Database Type</label>
					</div>
					<select id="databasetype" name="databasetype">
						<option value="mysql" selected>MySQL</option>
						<option value="postgres" >Postgres 8.0/8.1</option>
						<option value="mssql" >Microsoft SQL Server (2000/2005)</option>
					</select>	
					<br />
					
					
					<div class="labelHolder">
						<label for="databasehost">Database host:</label>
					</div>
					<input type="text" id="databasehost" name="databasehost" 
					                   value="localhost" style="width:200px" /><br />
					
				<p>
         <?php echo db_msg($inst_type); ?>
				</p>
				<p>
				  <!-- 20060215 - franciscom -->
					<div class="labelHolder"><label for="databasename">Database name:</label></div>
					<input type="text" id="databasename" name="databasename" 
                 maxlength="50" 
					       style="width:200px" value="testlink"><br />
					<!--
					20050611 - fm
					<div class="labelHolder"><label for="tableprefix">Table prefix:</label></div><input type="text" id="tableprefix" name="tableprefix" style="width:200px" value="TestLink_">
					-->
				</p>

				<p>
					Database User with administrative rights.
				</p>
				
				<p class="smallText">
				This user requires permission to create databases on the Database Server.<br>
        This value is used only for these installation procedures, and is not saved.
			  </p>
			
				<p>
					
					<div class="labelHolder"><label for="databaseloginname">Database login:</label></div><input type="text" id="databaseloginname" name="databaseloginname" style="width:200px" /><br />
					<div class="labelHolder"><label for="databaseloginpassword">Database password:</label></div><input type="password" id="databaseloginpassword" name="databaseloginpassword" style="width:200px" /><br />
				</p>

				<p>
					Database User for Normal Testlink use.
				</p>
        <p class="smallText">
				This user will have permission only to work on TestLink database.<br>
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
					<?php echo tl_admin_msg($inst_type); ?>
				</p>
				
				<!--
				<p>
					Now you'll need to enter some details for the main TestLink administrator account.<br />
					You can fill in your own name here, and a password you're not likely to forget. <br />
					You'll need these to log into TestLink once setup is complete.
				</p>
				<p>
					<div class="labelHolder"><label for="cmsadmin">Administrator username:</label></div><input type="text" id="cmsadmin" name="cmsadmin" style="width:200px" value="admin" /><br />
					<div class="labelHolder"><label for="cmspassword">Administrator password:</label></div><input type="password" id="cmspassword" name="cmspassword" style="width:200px" value="" /><br />
					<div class="labelHolder"><label for="cmspasswordconfirm">Confirm password:</label></div><input type="password" id="cmspasswordconfirm" name="cmspasswordconfirm" style="width:200px" value="" /><br />
				</p>
        -->
        
				<p>
					<input type="submit" value="Setup TestLink!">
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


