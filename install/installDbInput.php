<?php 
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Collect DB input data
 * every HTML input defined, will create an entry on $_SESSION array  automagically.
 * 
 * @package 	TestLink
 * @author 		Martin Havlat
 * @copyright 	2009, TestLink community 
 * @version    	CVS: $Id: installDbInput.php,v 1.2 2010/01/24 15:22:07 franciscom Exp $
 *
 * @internal Revisions:
 * 20090603 - franciscom - added table prefix management
 * 
 **/

require_once("installUtils.php");

if( !isset($_SESSION) )
{ 
  session_start();
}

$msg='';
$inst_phase = 'dbaccess';
//$inst_type = $_GET['installationType'];
//$isUpgrade = ($inst_type == "upgrade") ? TRUE: FALSE;

include 'installHead.inc';
?>
<div class="tlStory">

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

	<form action="installNewDB.php" method="post" name="myForm" onsubmit="return validate()">

<?php if(!$_SESSION['isNew']){ ?>
	<h2>Database Backup</h2>
	<p>Warning! You have requested an Upgrade, this process will MODIFY your current
  	TestLink Database. <br/>
    We STRONGLY recomend you to backup your database before this point.
    See DB manual for more</p>
    <p>
    	<div class="labelHolder">
			<label for="databasetype">I have the back-up</label>
		</div>
        <input type="checkbox" id="backupdone" name="backupdone" />
	</p> 
<?php } ?>

				
		<h2>Database Configuration</h2>
		<p>Define your database to store TestLink data:</p>

		<p>
		<div class="labelHolder">
			<label for="databasetype">Database Type</label>
		</div>
		<select id="databasetype" name="databasetype">
			<option value="mysql" selected>MySQL (5.0 and later)</option>
			<option value="postgres" >Postgres (8.0 and later)</option>
			<!--- 
			20100124 - franciscom - Not ready => disabled
			<option value="mssql" >Microsoft SQL Server 2000</option> --->
		</select>	
		</p>
		<p>
			<div class="labelHolder">
				<label for="databasehost">Database host</label>
			</div>
			<input type="text" id="databasehost" name="databasehost" 
			                  value="localhost" style="width:200px" />
		</p>
		<p>
   		<div class="tlBox">Note: In the case that you DB connection dosn't use <b>STANDARD PORT</b> for ,
		you need to add '<b>:port_number</b>', at the end Database host parameter.
		Example: you use MySQL running on port 6606, on server matrix 
		then Database host will be <i>matrix:6606</i>
		</div>
		</p>			
		<p>Enter the name of the TestLink database <?php if(!$_SESSION['isNew'])echo " for upgrade."; else
			echo ". The installer will attempt to create it if not exists."?></br>
		<div class="labelHolder"><label for="databasename">Database name</label></div>
		<input type="text" id="databasename" name="databasename"  maxlength="50" 
					       style="width:200px" value="testlink">
		</p>
		<?php if($_SESSION['isNew']){ ?>
		<p>
        <div class="tlBox">Disallowed characters in Database Name:<br />
 			The database name can contains any character that is allowed in 
 			a directory name, except '/', '\', or '.'.
		</div>
		</p>
		<?php } ?>
		<p>
	    <div class="labelHolder"><label for="tableprefix">Table prefix</label></div>
	    <input type="text" id="tableprefix" name="tableprefix" style="width:200px" value="">
	    (optional)
		</p>
		<?php if($_SESSION['isNew']){ ?>
		<p>
		<div class="tlBox">
			Note: This parameter should be empty for the most of cases.<br />
			<b>Using a Database shared with other applications:</b>
			Testlink can be installed (using this installer) on a existing database 
			used by another application, using a table prefix.<br />
			Warning! PART OF INSTALLATION PROCESS CONSISTS 
			on dropping all TestLink tables present on the database/schema (if any TestLink table exists). 
			Backup your Database Before installing and load after this process.
		</div>
		</p>
		<?php } ?>

		<p>Set an existing database user with administrative rights (root):</p>
		<p>
			<div class="labelHolder"><label for="databaseloginname">Database admin login</label></div>
			<input type="text" id="databaseloginname" name="databaseloginname" style="width:200px" /><br />
			<div class="labelHolder"><label for="databaseloginpassword">Database admin password</label></div>
			<input type="password" id="databaseloginpassword" name="databaseloginpassword" style="width:200px" /><br />
		</p>
		<p>
		<div class="tlBox">
			This user requires permission to create databases and users on the Database Server.<br/>
    	    These values are used only for this installation procedures, and is not saved.
		</div>
		</p>

		<p>Define database User for Testlink access:</p>
			<div class="labelHolder">
				<label for="tl_loginname">TestLink DB login</label>
			</div>
			<input type="text" id="tl_loginname" name="tl_loginname" style="width:200px" /><br />
					
			<div class="labelHolder">
				<label for="tl_loginpassword">TestLink DB password</label>
			</div>
			<input type="password" id="tl_loginpassword" name="tl_loginpassword" style="width:200px" /><br />
		</p>
		<p>
		<div class="tlBox">
			This user will have permission only to work on TestLink database and will be 
			stored in TestLink configuration.<br />
    	    All TestLink requests to the Database will be done with this user.
		</div>
		</p>
				
		<p>
			<?php if($_SESSION['isNew'])echo 'After successfull installation You will' .
						' have the following login for TestLink Administrator:<br />' .
				        'login name: admin <br /> password  : admin';
			?>
		</p>
		<p>
			<input type="submit" value="Process TestLink Setup!">
		</p>
				
	</form>	

</div>
<?php include 'installFooter.inc'; ?>