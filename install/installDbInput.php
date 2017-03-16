<?php 
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Collect DB input data
 * every HTML input defined, will create an entry on $_SESSION array  automagically.
 * 
 * @filesource  installDbInput.php
 * @package   TestLink
 * @author    Martin Havlat
 * @copyright   2009,2016 TestLink community 
 *
 * @internal revisions
 * @since 1.9.15
 **/

require_once("installUtils.php");

if( !isset($_SESSION) )
{ 
  session_start();
}

$msg='';
$inst_phase = 'dbaccess';  // global variable -> absolutely wrong use as usual, used on installHead.inc 
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

      if(f.tableprefix.value != "") 
      {
        if( f.tableprefix.value.search(/^[A-Za-z0-9_]*$/) == -1)
        {
          alert('Table prefix must contain only letters,numbers and underscore!');
          return false;
        }
        
        // Check max len, after trim, really not needed because
        // first check woth regexp does not allow spaces
        // f.tableprefix.value.replace(/^\s*/, "").replace(/\s*$/, "");
        if( f.tableprefix.value.length > 8 )  // Sorry by MAGIC NUMBER
        {
          alert('Table prefix lenght <= 8!');
          return false;
        }
      }
    
      return true;
    }
  </script>

  <form action="installNewDB.php" method="post" name="myForm" onsubmit="return validate()">
  <?php echo ('<input type="hidden" id="isNew" name="isNew"  value="' . $_SESSION['isNew'] . '"/> '); ?>    

<?php if(!$_SESSION['isNew']){ ?>
  
  <div class="tlBox">
  <h2>Warning! Migration of user assignments!</h2>
  <p>TestLink version 1.9 uses a <b>new method of assigning users to test cases</b>. This assignment is now done on build level instead of only test plan level like it was in older versions. Because of this change, your <b>existing user assignments will be modified</b> during migration:<br/>
  For every test plan, <b>all existing assignments of users to test cases will be deleted and re-assigned only to the newest found build in this test plan</b>.<br/>
  You will of course have the chance to change these assignments manually in TestLink after migration. When you create a new build, existing assignments from a selected existing build can also be copied directly on creation of the new build.</p>
  </div>
  
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
      <option value="mysql" selected>MySQL/MariaDB (5.6+ / 10.+)</option>
      <option value="postgres" >Postgres (9.1 and later)</option>
      <option value="mssql" >Microsoft SQL Server 2008 and later (Experimental)</option>
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
      <input type="text" id="tableprefix" name="tableprefix" size="8" maxlength="8" value="">
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