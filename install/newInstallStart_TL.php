<?php 
/* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: newInstallStart_TL.php,v 1.23 2009/01/19 15:48:56 havlat Exp $

rev:20080914 - franciscom - check_php_resource_settings() 
    20080219 - franciscom - fixed dir permission checking
*/

require_once("installUtils.php");
require_once('..'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'functions'.DIRECTORY_SEPARATOR.'configCheck.php');

if( !isset($_SESSION) )
{ 
  session_start();
}
$tl_and_version = "TestLink {$_SESSION['testlink_version']} ";

$inst_type = $_GET['installationType'];
if ($_GET['installationType'] == "upgrade")
	$isUpgrade = TRUE;
else
	$isUpgrade = FALSE;	

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" 
  "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<title><?php echo $tl_and_version ?>Installer</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link href="gui/themes/default/images/favicon.ico" rel="icon" type="image/gif"/>
	<link href="../gui/themes/default/css/testlink.css" rel="stylesheet" type="text/css" />
    <style type="text/css"> @import url('./css/style.css');</style>

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

</head>	

<body>
<table border="0" cellpadding="0" cellspacing="0" class="mainTable">
  <tr class="fancyRow">
    <td><span class="headers">&nbsp;<img src="./img/dot.gif" alt="" style="margin-top: 1px;" />&nbsp;
    <?php echo $tl_and_version ?> - <?php echo $inst_type; ?> Installation</span></td>
  </tr>

  <tr class="fancyRow2">
    <td colspan="2" class="border-top-bottom smallText" align="right">&nbsp;</td>
  </tr>

  <tr>
    <td>TestLink will carry out a number of checks to see if everything's ready to start the setup.</td>
  </tr>

  <tr align="left" valign="top">
    <td colspan="2">

	
<?php
// Check before DB installation
$errors = 0;
reportCheckingSystem($errors);
reportCheckingWeb($errors);
reportCheckingPermissions($errors);

if($errors > 0) {
	// Stop process because of error
?>
	<p>Unfortunately, TestLink scripted setup cannot continue at the moment, due to the above 
	<?php echo $errors > 1 ? $errors." " : "" ; ?>error<?php echo $errors > 1 ? "s" : "" ; ?>. 
	<br />Please correct the error<?php echo $errors > 1 ? "s" : "" ; ?>, 
	and try again (reload page). If you need help figuring out how to fix the 
	problem<?php echo $errors > 1 ? "s" : "" ; ?>, please read Installation manual and
	visit <a href="http://www.teamst.org" target="_blank">TestLink Forums [click here]</a>.
	</p>
	</td>
  </tr>
  <tr class="fancyRow2">
    <td class="border-top-bottom smallText">&nbsp;</td>
    <td class="border-top-bottom smallText" align="right">&nbsp;</td>
  </tr>
</table>
</body>
</html>
<?php
}
else
{ // checking OK
?>
	<p class="success">Your system is prepared for TestLInk configuration (no fatal error found).</p>
	</td>
	</tr>

    <tr class="fancyRow2">
    <td colspan="2" class="border-top-bottom smallText" align="right">&nbsp;</td>
    </tr>

	<form action="license.php" method="post" name="myForm" onsubmit="return validate()">

		<input type="hidden" name="installationType" value="<?php  echo $inst_type; ?>">
        <input type="hidden" name="page2launch" value="installNewDB.php">

	<tr align="left" valign="top">
    <td colspan="2">

        <?php 
        	if($isUpgrade){
          	echo   $msg .= "<p class='error'>Warning!!! Warning!!! Warning!!! Warning!!! Warning!!!<br />" .
  			"You have requested an Upgrade, this process WILL MODIFY your TestLink Database. <br/>" .
          	"We STRONGLY recomend you to backup your Database Before starting this upgrade process</p>"; 
        	}
         ?>

				
		<h2>Database Configuration</h2>
		<p>Define input data for your database setup.</p>

		<p>
		<div class="labelHolder">
			<label for="databasetype">Database Type</label>
		</div>
		<select id="databasetype" name="databasetype">
			<option value="mysql" selected>MySQL (4.1 and later)</option>
			<option value="postgres" >Postgres (8.0 and later)</option>
			<option value="mssql" >Microsoft SQL Server (2000 and later)</option>
		</select>	
		</p>

		<p>
					<div class="labelHolder">
						<label for="databasehost">Database host:</label>
					</div>
					<input type="text" id="databasehost" name="databasehost" 
					                   value="localhost" style="width:200px" />
		</p>
		
   		<p class="tab-warning">Note: In the case that you DB connection dosn't use <b>STANDARD PORT</b> for ,
		you need to add '<b>:port_number</b>', at the end Database host parameter.
		Example: you use MySQL running on port 6606, on server matrix 
		then Database host will be <i>matrix:6606</i>
		</p>
					
		<p>
		Please enter the name of the TestLink database<?php if($isUpgrade)echo " for upgrade."; else
			echo ". The installer will attempt to create it if not exists."?>.</br>
		<div class="labelHolder"><label for="databasename">Database name:</label></div>
		<input type="text" id="databasename" name="databasename"  maxlength="50" 
					       style="width:200px" value="testlink">
					<!--
					20050611 - fm
					<div class="labelHolder"><label for="tableprefix">Table prefix:</label></div>
					<input type="text" id="tableprefix" name="tableprefix" style="width:200px" value="TestLink_">
					-->
		</p>

		<?php if(!$isUpgrade){ ?>
		<p class="tab-warning"><b>Warning:</b></br>
 			The database name can contain any character that is allowed in 
 			a directory name, except '/', '\', or '.'.<br />
			Testlink can not be installed (using this installer) on a existing database 
			used by another application, because part of the installation process consist 
			on dropping all tables present on the database/schema. The existing data 
			will be destroyed without notice.
		</p>
		<?php } ?>

		<p>Please set an existing database user with administrative rights (root).</p>
				
		<p class="smallText">
		This user requires permission to create databases on the Database Server.<br>
        This value is used only for these installation procedures, and is not saved.
		</p>
			
		<p>
			<div class="labelHolder"><label for="databaseloginname">Database login:</label></div>
			<input type="text" id="databaseloginname" name="databaseloginname" style="width:200px" /><br />
			<div class="labelHolder"><label for="databaseloginpassword">Database password:</label></div>
			<input type="password" id="databaseloginpassword" name="databaseloginpassword" style="width:200px" /><br />
		</p>

		<p>Database User for Normal Testlink use.</p>
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
				
				<?php if(!$isUpgrade)echo '<p> After successfull installation You will' .
						' have the following login for TestLink Administrator:<br />' .
				        'login name: admin <br /> password  : admin </p>';
				?>
				
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
					<input type="submit" value="Process TestLink Setup!">
				</p>
				
			</form>	

		</td>
      </tr>

	<tr class="fancyRow2">
    <td class="border-top-bottom smallText">&nbsp;</td>
    <td class="border-top-bottom smallText" align="right">&nbsp;</td>
	</tr>
</table>
</body>
</html>
<?php } // else end - checking OK ?>