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

    <section class="col-lg-6 col-md-8 col-sm-10 col-xs-12 col-lg-offset-3 col-md-offset-2 col-sm-offset-1 tl-box-main">
      <form action="installNewDB.php" method="post" name="myForm" onsubmit="return validate()" class="form-horizontal">
      <?php echo ('<input type="hidden" id="isNew" name="isNew"  value="' . $_SESSION['isNew'] . '"/> '); ?>
      <?php
        if(!$_SESSION['isNew']){
      ?>
          <h2>Warning! Migration of user assignments!</h2>
          <p>TestLink version 1.9 uses a <b>new method of assigning users to test cases</b>. This assignment is now done on build level instead of only test plan level like it was in older versions. Because of this change, your <b>existing user assignments will be modified</b> during migration:<br/>
  For every test plan, <b>all existing assignments of users to test cases will be deleted and re-assigned only to the newest found build in this test plan</b>.<br/>
  You will of course have the chance to change these assignments manually in TestLink after migration. When you create a new build, existing assignments from a selected existing build can also be copied directly on creation of the new build.</p>
          <h2>Database Backup</h2>
          <p>Warning! You have requested an Upgrade, this process will MODIFY your current</p>
          <p>TestLink Database. </p>
          <p>We STRONGLY recomend you to backup your database before this point.</p>
          <p> See DB manual for more</p>
          <p>
            <div class="labelHolder">
              <label for="databasetype">I have the back-up</label>
            </div>
            <input type="checkbox" id="backupdone" name="backupdone" />
          </p>
      <?php
        }
      ?>
      <h2 class="tl-title">Database Configuration</h2>
      <p class="tl-desc">Define your database to store TestLink data</p>
      <div class="form-group">
        <label id="lblDatabaseType" for="databasetype" class="col-lg-3 control-label">Database type</label>
        <div class="col-lg-9">
          <select id="databasetype" name="databasetype" class="form-control" required>
            <option value="mysql" selected>MySQL/MariaDB (5.6+ / 10.+)</option>
            <option value="postgres" >Postgres (9.1 and later)</option>
            <option value="mssql" >Microsoft SQL Server 2008 and later (Experimental)</option>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label id="lblDatabaseHost" for="databasehost" class="col-lg-3 control-label">Database host</label>
        <div class="col-lg-9">
          <input id="txtDatabaseHost" type="text" id="databasehost" name="databasehost" value="127.0.0.1" class="form-control" placeholder="Type your database host..." required/>
        </div>
      </div>
      <blockquote class="col-lg-12 alert alert-info tl-note">
        <p>
          Note: In the case that you DB connection dosn't use 
          <span class="text-font-bold">STANDARD PORT</span> for, you need to add 
          '<b>:port_number</b>'
          , at the end Database host parameter
          . Example: you use MySQL running on port 3306
          , on server matrix then Database host will be
          <cite class="tl-text-italic">matrix:3306</cite>
        </p>
      </blockquote>

      <p>
        Enter the name of the TestLink database
      <?php
        if(!$_SESSION['isNew']) echo " for upgrade.";
        else echo ". The installer will attempt to create it if not exists.<br/>"
      ?>
      <div class="form-group">
        <label id="lblDatabaseName" for="databasename" class="col-lg-3 control-label">Database name</label>
        <div class="col-lg-9">
          <input type="text" id="databasename" name="databasename" maxlength="50" value="testlink" class="form-control" placeholder="Type your database name..." required>
        </div>
      </div>
      <?php
        if($_SESSION['isNew']){
      ?>
          <blockquote class="col-lg-12 alert alert-info tl-note">
            <p>
              Disallowed characters in <span class="text-font-bold">database name</span>
              . The database name can contains any character that is allowed in a directory name
              , except 
              <code>'/', '\', or '.'.</code>
            </p>
          </blockquote>
      <?php 
        }
      ?>
      <div class="form-group">
        <label id="lblTablePrefix" for="tableprefix" class="col-lg-3 control-label">Table prefix</label>
        <div class="col-lg-9">
          <input type="text" id="tableprefix" name="tableprefix" size="8" maxlength="8" value="tl_" class="form-control" placeholder="Type your database tables prefix...">
        </div>
      </div>
      <?php
        if($_SESSION['isNew']){
      ?>
          <blockquote class="col-lg-12 alert alert-info tl-note">
            <p>
              This parameter should be empty for the most of cases
              . <span class="text-font-bold">Using a Database shared with other applications</span> Testlink can be installed (using this installer) on a existing database used by another application, using a table prefix
              . Warning! <span class="text-font-bold">PART OF INSTALLATION PROCESS CONSISTS</span> on dropping all TestLink tables present on the database/schema (if any TestLink table exists)
              . Backup your Database Before installing and load after this process.
            </p>
          </blockquote>
      <?php
        }
      ?>
      <h3 class="tl-title">Set an existing database user with administrative rights (root)</h3>
      <div class="form-group">
        <label id="lblDatabaseLoginName" for="databaseloginname" class="col-lg-4 control-label">Database admin username</label>
        <div class="col-lg-8">
          <input type="text" id="databaseloginname" name="databaseloginname" class="form-control" value="testlink" placeholder="Type your database user name..."/>
        </div>
      </div>
      <div class="form-group">
        <label id="lblDatabaseLoginPassword" for="databaseloginpassword" class="col-lg-4 control-label">Database admin password</label>
        <div class="col-lg-8">
          <input type="password" id="databaseloginpassword" name="databaseloginpassword" class="form-control" value="testlink" placeholder="Type your database user password..."/>
        </div>
      </div>

      <blockquote class="col-lg-12 alert alert-warning tl-note">
        <p>This user requires permission to create databases and users on the Database Server.</p>
        <p>These values are used only for this installation procedures, and is not saved.</p>
      </blockquote>

      <h3 class="tl-title">Define database User for Testlink access</h3>

      <div class="form-group">
        <label id="lblTestlinkUserName" for="tl_loginname" class="col-lg-4 control-label">TestLink DB user</label>
        <div class="col-lg-8">
          <input type="text" id="tl_loginname" name="tl_loginname" class="form-control" value="admin" placeholder="admin" />
        </div>
      </div>
      <div class="form-group">
        <label id="lblTestlinkUserPass" for="tl_loginpassword" class="col-lg-4 control-label">TestLink DB password</label>
        <div class="col-lg-8">
          <input type="password" id="tl_loginpassword" name="tl_loginpassword" class="form-control" value="value" placeholder="admin" />
        </div>
      </div>
      <blockquote class="col-lg-12 alert alert-info tl-note">
        <p>This user will have permission only to work on TestLink database and will be stored in TestLink configuration.</p>
        <p>All TestLink requests to the Database will be done with this user.</p>
      </blockquote>
      <?php
        if($_SESSION['isNew']){
      ?>
          <h3 class="tl-title"> After successfull installation</h3>
          <p class="tl-desc">You will have the following login for TestLink Administrator</p>
          <table class="table table-striped">
            <thead>
              <tr>
                <th scope="col">Default Username</th>
                <th scope="col">Default Password</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>admin</td>
                <td>admin</td>
              </tr>
            </tbody>
          </table>
      <?php
        }
      ?>
      <div class="form-group">
        <div class="col-lg-12">
          <input type="submit" value="Process TestLink Setup!" class="form-control btn btn-success" >
        </div>
      </div>
      </form>
    </section>

<?php include 'installFooter.inc'; ?>
