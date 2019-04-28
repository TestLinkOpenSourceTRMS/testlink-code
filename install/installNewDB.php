<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * create or update TestLink database 
 * 
 * @filesource  installNewDB.php
 * @package     TestLink
 * @author      Francisco Mancardi
 * @copyright   2008,2018 TestLink community
 * @copyright   inspired by Etomite Content Management System
 *              2003, 2004 Alexander Andrew Butter 
 *
 **/

require_once("../config.inc.php");
require_once( dirname(__FILE__). '/../lib/functions/database.class.php' );
require_once("installUtils.php");
require_once("sqlParser.class.php");
require_once("../lib/functions/common.php");
require_once("../lib/functions/object.class.php");
require_once("../lib/functions/metastring.class.php");

require_once("../third_party/dBug/dBug.php");

require_once('Zend/Validate/Hostname.php');

// Better to avoid use of logger during installation
// because we do not have control on what kind of logger (db, file) to create.
// This produce the situation:dog eats dog, i.e.:
// I do not have db created, but an error rise, then logger try to write on events table
// but this table do not still yet !!.
require_once("../lib/functions/logger.class.php");

if( !isset($_SESSION) ) { 
  session_start();
}

// catch DB input data
foreach($_POST as $key => $val) {
  $_SESSION[$key] = $val;
}

//assure that no timeout happens for large data
set_time_limit(0);
$tl_and_version = "TestLink {$_SESSION['testlink_version']} ";

define('LEN_PWD_TL_1_0_4',15);
define('ADD_DIR',1);

$migration_process = '';
$sql_update_schema = array();
$sql_update_data   = array();

// Wants to sanitize some user inputs
// Because we use host:port, need to remove port before check
$db_server = trim($_SESSION['databasehost']);
$dbHost = $db_server;
$dbPort = null;

$nu = explode(':',$db_server);
$hmp = count($nu);

switch($hmp) {
  case 2:
    $dbHost = $nu[1];
    $dbPort = $nu[0];
  break;

  case 1:
  break;

  default:
    echo "No good, host name has to many ':'\n";
    die();
  break;

}

$validator = new Zend_Validate_Hostname(Zend_Validate_Hostname::ALLOW_ALL);

if (!$validator->isValid($dbHost)) {
  // hostname is invalid; print the reasons
  foreach ($validator->getMessages() as $message) {
    echo "$message\n";
  }
  die();
}

$san = '/[^A-Za-z0-9\-]/';
$db_name = trim($_SESSION['databasename']);
$db_name = preg_replace($san,'',$db_name);

$db_table_prefix = trim($_SESSION['tableprefix']);
$db_table_prefix = preg_replace($san,'',$db_table_prefix);

$db_type = trim($_SESSION['databasetype']);
$db_type = preg_replace($san,'',$db_type);

$db_admin_pass = trim($_SESSION['databaseloginpassword']);
$tl_db_passwd = trim($_SESSION['tl_loginpassword']);




// will limit length to avoi some kind of injection
// Choice: 32 
$tl_db_login = trim($_SESSION['tl_loginname']);
$tl_db_login = substr(preg_replace($san,'',$tl_db_login),0,32);

$db_admin_name = trim($_SESSION['databaseloginname']);
$db_admin_name = substr(preg_replace($san,'',$db_admin_name),0,32);



$sql_create_schema = array();
$sql_create_schema[] = "sql/{$db_type}/testlink_create_tables.sql";
$a_sql_schema = array();
$a_sql_schema[] = $sql_create_schema;

$sql_default_data = array();
$sql_default_data [] = "sql/{$db_type}/testlink_create_default_data.sql";
$a_sql_data = array();
$a_sql_data[]   = $sql_default_data;


global $g_tlLogger;
$g_tlLogger->disableLogging('db');
$inst_type_verbose=" Installation ";

$install = $_SESSION['isNew'];
$upgrade = !$install;
if ($upgrade)
{
  $inst_type_verbose=" Upgrade ";
  $a_sql_data   = array();
}
$the_title = $_SESSION['title'];
?>


<!DOCTYPE html>
<head>
  <title><?php echo $the_title; ?></title>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
        <style type="text/css">
             @import url('./css/style.css');
       
     ul li { margin-top: 7px; }
        </style>
</head> 

<body>
<table border="0" cellpadding="0" cellspacing="0" class="mainTable">
  <tr class="fancyRow">
    <td><span class="headers">&nbsp;<img src="./img/dot.gif" alt="" style="margin-top: 1px;" />&nbsp;<?php echo $tl_and_version?></span></td>
    <td align="right"><span class="headers"><?php echo $the_title ?> </span></td>
  </tr>
  <tr class="fancyRow2">
    <td colspan="2" class="border-top-bottom smallText" align="right">&nbsp;</td>
  </tr>
  <tr align="left" valign="top">
    <td colspan="2"><table width="100%"  border="0" cellspacing="0" cellpadding="1">
      <tr align="left" valign="top">
        <td class="pad" id="content" colspan="2">

<?php
$check = check_db_loaded_extension($db_type);
if( $check['errors'] > 0 ) {
   echo $check['msg'];
   close_html_and_exit();
}
?>
TestLink setup will now attempt to setup the database:<br />

<?php

$update_pwd=0;
$create = false;
$errors = 0;
$table_prefix ='';
$adminname = '';
$adminpass = '';


// do some database checks
echo "<br /><b>Creating connection to Database Server:</b>";

// --------------------------------------------------------------------------
// Connect to DB Server without choosing an specific database
switch($db_type) {
  case 'mssql':
    $dbDriverName = 'mssqlnative';
  break;

  default:
    $dbDriverName = $db_type;
  break;
}

$db = new database($dbDriverName);
define('NO_DSN',FALSE);
@$conn_result = $db->connect(NO_DSN,$db_server, $db_admin_name, $db_admin_pass); 

if( $conn_result['status'] == 0 )  {
  echo '<span class="notok">Failed!</span><p />Please check the database login details and try again.';
  echo '<br>Database Error Message: ' . $db->error_msg() . "<br>";

  echo '<br>$db_server:' . $db_server . '<br>';
  echo '<br>$db_admin_name:' . $db_admin_name . '<br>';
  echo '<br>$db_admin_pass:' . $db_admin_pass . '<br>';


  close_html_and_exit();
} else {
  echo "<span class='ok'>OK!</span><p />";
}
$db->close();
$db=null;


// -------------------------------------------------------------------------
// Connect to the Database (if Succesful -> database exists)
$db = new database($dbDriverName);

@$conn_result = $db->connect(NO_DSN,$db_server, $db_admin_name, $db_admin_pass,$db_name); 

if( $conn_result['status'] == 0 ) {
  $db->close();
  echo "<br>Database $db_name does not exist. <br>";
  
  if( $upgrade ) {
    echo "Can't Upgrade";
    close_html_and_exit();     
    
    $errors += 1;
  }
  else {
    echo "Will attempt to create:";
    $create = true;
  } 
} 
else {
  echo "<br />Connecting to database `" . $db_name . "`:";
  echo "<span class='ok'>OK!</span>";
}
// -------------------


// -------------------
if($create) {
  // check database name for invalid characters (now only for MySQL)
  $db->close();
  $db = null;
  
  $db = New database($dbDriverName);
  $conn_result=$db->connect(NO_DSN,$db_server, $db_admin_name, $db_admin_pass);
  echo "<br /><b>Creating database `" . $db_name . "`</b>:";
  
  // from MySQL Manual
  // 9.2. Database, Table, Index, Column, and Alias Names
  //
  // Identifier            : Database
  // Maximum Length (bytes): 64
  // Allowed Characters    : Any character that is allowed in a directory name, except '/', '\', or '.'  
  // 
  // An identifier may be quoted or unquoted. 
  // If an identifier is a reserved word or contains special characters, you must quote it whenever you refer to it. 
  // For a list of reserved words, see Section 9.6, �Treatment of Reserved Words in MySQL�. 
  // Special characters are those outside the set of alphanumeric characters from the current character set, 
  // '_', and '$'. 
  // The identifier quote character is the backtick ('`'): 
  //
  //
  // Postgres uses as identifier quote character " (double quotes):
  $sql_create_db =$db->build_sql_create_db($db_name);
  
  if(!$db->exec_query($sql_create_db)) {
    echo "<span class='notok'>Failed!</span></b> - Could not create database: $db! " .
    $db->error_msg();
    $errors += 1;
    
    echo "<p> TestLink setup could not create the database, " .
    "and no existing database with the same name was found. <br />" .
    "Please create a database by different way (e.g. from command line)," . 
    " or with different DB root account. Run setup again then.";
    close_html_and_exit();     
  } 
  else {
    echo "<span class='ok'>OK!</span>";
  }
}

// in upgrade mode we detect the lenght of user password field
// to identify a version with uncrypted passwords
$tables = tlObject::getDBTables();
$my_ado = $db->get_dbmgr_object();
if ($upgrade) {
  $user_table=$my_ado->MetaTables('TABLES',false,'user');
  if( count($user_table) == 1 ) {
    $the_cols=$my_ado->MetaColumns('user');
    $pwd_field_len =$the_cols['PASSWORD']->max_length;
    if ( $pwd_field_len == LEN_PWD_TL_1_0_4 ) {
      $update_pwd=1;
      echo "<p>You are trying to upgrade from a pre-release of TestLink 1.5" .
      "<br />this kind of upgrade is supported by this script. Use upgrade to supported version " .
      "at first.</p>";  
      close_html_and_exit();          
    }
  }
  // -------------------------------------------------------------
  
  $a_sql_upd_dir=array();
  $a_sql_data_dir=array();
  
  $the_version_table=$my_ado->MetaTables('TABLES',false,$db_table_prefix . 'db_version');
  if( count($the_version_table) == 0 ) {
    echo "<p>You are trying to upgrade from a pre-release of TestLink 1.7" .
    "<br />this kind of upgrade is supported by this script. Use upgrade to supported version " .
    "at first.</p>";  
    close_html_and_exit();          
  }
  else {
    $migration_functions_file = '';
    $migration_process = ''; 

    // try to know what db version is installed
    // check if we need to use prefix but for some reason tlObjectWithDB::getDBTables
    // have not returned prefix.
    //
    $dbVersionTable = $tables['db_version'];
    if($dbVersionTable == 'db_version' &&  trim($db_table_prefix) != '') {
      $dbVersionTable = $db_table_prefix . $dbVersionTable;
    }
    $sql = "SELECT * FROM {$dbVersionTable} ORDER BY upgrade_ts DESC";
    $res = $db->exec_query($sql);  
    if (!$res) {
      echo "Database ERROR:" . $db->error_msg();
      exit(); 
    }
    
    $myrow = $db->fetch_array($res);
    $schema_version=trim($myrow['version']);
    
    switch ($schema_version) {
      case 'DB 1.2':
        $a_sql_upd_dir[] = "sql/alter_tables/1.9/{$db_type}/DB.1.3/step1/";
        $a_sql_data_dir[] = "sql/alter_tables/1.9/{$db_type}/DB.1.3/stepZ/";
        $migration_process = 'migrate_18_to_19'; 
        $migration_functions_file = './migration/migrate_18/migrate_18_to_19.php';
        break;
        
      case 'DB 1.3':
        echo "<p>Your DB Schema {$schema_version} NEED TO BE upgraded, but you have to do ";
        echo " this MANUALLY using a SQL client and scripts you will find on ";
        echo " directory install/sql/alter_tables/1.9.1 ";
        echo "<br /></p>";
        close_html_and_exit();          
        break;

      case 'DB 1.4':
        echo "<p>Your DB Schema {$schema_version} NEED TO BE upgraded, but you have to do ";
        echo " this MANUALLY using a SQL client and scripts you will find on ";
        echo " directory install/sql/alter_tables/1.9.4 ";
        echo "<br /></p>";
        close_html_and_exit();          
        break;

      case 'DB 1.5':
        echo "<p>Your DB Schema {$schema_version} is the last available, then you don't need to do any upgrade.";
        echo "<br />Script is finished.</p>";
        close_html_and_exit();          
        break;
        
      default:
        if( strlen($schema_version) == 0 )
        {
          echo "<p class='notok'>Information of DB schema version is missing. Don't know how to upgrade.</p>";
        }
        else
        {
          echo "<p class='notok'>This script doesn't recognize your schema version: " . $schema_version . "</p>";
        }
        echo "<p>Upgrade is not possible. Check your input data (Go back in page history).</p>";
        close_html_and_exit();          
        break;  
    }
  }
  
  $a_sql_schema = getDirSqlFiles($a_sql_upd_dir,ADD_DIR);
  $a_sql_data = getDirSqlFiles($a_sql_data_dir,ADD_DIR);
}


// ------------------------------------------------------------------------
// Now proceed with user checks and user creation (if needed)
//
// Added support for different types of architecture/installations:
// webserver and dbserver on same machines      => user will be created as user
// webserver and dbserver on DIFFERENT machines => user must be created as user@webserver
//  
// if @ in tl_db_login (username) -> get the hostname using splitting, and use it
//                                   during user creation on db. 
$db->close();
$db=null;
$user_host = explode('@',$tl_db_login);
$msg = create_user_for_db($dbDriverName,$db_name, $db_server, 
                          $db_admin_name, $db_admin_pass, 
                          $tl_db_login, $tl_db_passwd);
  
echo "<br /><b>Creating Testlink DB user `" . $user_host[0] . "`</b>:";
if ( strpos($msg,'ok -') === FALSE ) {
  echo "<span class='notok'>Failed!</span></b> - Could not create user: $tl_db_login!";
  $errors += 1;
}
else {
  echo "<span class='ok'>OK! ($msg) </span>";
}


// ------------------------------------------------------------------------
// Schema Operations (CREATE, ALTER, ecc).
// Important: 
//           Postgres: do it as tl_login NOT as db_admin
//           MySQL   : do it as db_admin NOT as tl_login 
if( !is_null($db) ) {
  $db->close();
  $db=null;
}

$db = new database($dbDriverName);
switch($db_type) {
    case 'mssql':
    @$conn_result = $db->connect(NO_DSN, $db_server, $db_admin_name, $db_admin_pass, $db_name); 
    break;

    case 'mysql':
    @$conn_result = $db->connect(NO_DSN, $db_server, $db_admin_name, $db_admin_pass, $db_name); 
    break;
        
    case 'postgres':
    @$conn_result = $db->connect(NO_DSN, $db_server, $tl_db_login, $tl_db_passwd, $db_name); 
    break;
}

// ------------------------------------------------------------------------------------
if( $install && $conn_result['status'] != 0 ) {
  drop_views($db,$db_table_prefix,$db_type);
  drop_tables($db,$db_table_prefix,$db_type);
}  


// -------------------------------------------------------------------------------
$sqlParser = new SqlParser($db,$db_type,$db_table_prefix);
foreach($a_sql_schema as $sql_schema) {
  foreach ($sql_schema as $sql_file)  {
    echo "<br />Processing:" . $sql_file;
    $sqlParser->process($sql_file);
  }
  echo "<br />";
}

// Now data migration must be done if needed
if( $migration_process != '' ) {
  require_once($migration_functions_file);
  $migration_process($db,$tables);
}

// -------------------------------------------------
// Data Operations
if ( count($a_sql_data > 0) ) {
  foreach($a_sql_data as $sql_data ) {
    if ( count($sql_data > 0) ) {
      foreach ($sql_data as $sql_file)  {
        $sqlParser->process($sql_file);
      }
    }
  }  
}


// -------------------------------------------------
if ($update_pwd) {
  echo "Password Conversion ...";
  // @author Francisco Mancardi - 20050918
  // Found error upgrading from 1.0.4 to 1.6 on RH
  // due to case sensitive on table name. (USER)
  
  $user_pwd = "UPDATE user SET password=MD5(password)";
  $result = $db->exec_query($user_pwd);
}


if($sqlParser->install_failed==true) 
{
  echo "<span class='notok'>Failed!</span></b> - {$inst_type_verbose} failed!";
  $errors += 1;
  
  echo "<p />" .
  "TestLink setup couldn't install the default site into the selected database. " .
  "The last error to occur was <i>" . $sqlParser->sql_errors[count($sqlParser->sql_errors)-1]["error"] .
  '</i> during the execution of SQL statement <span class="mono">' .
  strip_tags($sqlParser->sql_errors[count($sqlParser->sql_errors)-1]["sql"]). "</span>";
  
  close_html_and_exit();     
} 
else 
{
  echo "<span class='ok'>OK!</span>";
}

// -----------------------------------------------------------------------------
echo "<br />Writing configuration file:";
$data['db_host']=$db_server;
$data['db_login'] = $user_host[0];
$data['db_passwd'] = $tl_db_passwd;
$data['db_name'] = $db_name;
$data['db_type'] = $db_type;
$data['db_table_prefix'] = $db_table_prefix;


$cfg_file = "../config_db.inc.php";
$yy = write_config_db($cfg_file,$data);
// -----------------------------------------------------------------------------


if(strcasecmp('ko', $yy['status']) == 0) {
  echo "<span class='notok'>Failed!</span></b>";
  $errors += 1;
  
  echo "<p />" .
  "TestLink couldn't write the config file. Please copy the following into the " .
  '<span class="mono"> ' . $cfg_file . '</span> file:<br />' .
  '<textarea style="width:400px; height:160px;">' . $yy['cfg_string'] . "</textarea>";
  
  echo "Once that's been done, you can log into TestLink by pointing your browser at your TestLink site.";
  
  close_html_and_exit();     
} 
else 
{
  echo "<span class='ok'>OK!</span>";
}


manual_operations($db_type);

important_reminder();

// When testlink is updated do not show login data admin/admin 
// as they might not exist
$successfull_message = '</b><p /><br><div><span class="headers">' . "{$inst_type_verbose} was successful!" . '</span><br>' .
                     'You can now log in to <a href="../index.php"> Testlink';
if($create) 
{
  $successfull_message .= ' (using login name:admin / password:admin - Please Click Me!)';
}
$successfull_message .= '</a>.</div>';

echo $successfull_message;

$db->close();
close_html_and_exit();



/**
 *
 *
 */
function manual_operations($dbType) {

  echo '<h1>IMPORTANT NOTICE - IMPORTANT NOTICE - IMPORTANT NOTICE - IMPORTANT NOTICE</h1>';

  echo '<span class="headers">';
  //echo 'IMPORTANT NOTICE - IMPORTANT NOTICE - IMPORTANT NOTICE - IMPORTANT NOTICE';
  //echo '</span>';

  echo '<br><span class="headers">';
  echo '<h1>YOU NEED TO RUN MANUALLY Following Script on your DB CLIENT Application</h1>';
  echo '</span><br>';
  echo '<h1>' . dirname(__FILE__) . '/sql/'. $dbType . '/testlink_create_udf0.sql';
  echo '</h1><br><h1>THANKS A LOT </b></h1>';
}

// -----------------------------------------------------------
function write_config_db($filename, $data)
{
  $ret = array('status'     => 'ok', 'cfg_string' => '');
  
  $db_host  = $data['db_host'];
  $db_login = $data['db_login'];
  // if @ present in db_login, explode an take user name WITHOUT HOST
  $user_host = explode('@',$db_login);
  
  if (count($user_host) > 1 )
  {
    $db_login = $user_host[0];    
  }
  
  $db_passwd = $data['db_passwd'];
  $db_name = $data['db_name'];
  $db_type = $data['db_type'];
  $db_table_prefix = $data['db_table_prefix'];
  
  // write config.inc.php
  $configString = "<?php" . "\n" . "// Automatically Generated by TestLink Installer - " . date(DATE_RFC822) . "\n";
  $configString .= "define('DB_TYPE', '" . $db_type . "');\n";
  $configString .= "define('DB_USER', '" . $db_login . "');\n";
  $configString .= "define('DB_PASS', '" . $db_passwd . "');\n";
  $configString .= "define('DB_HOST', '" . $db_host . "');\n";
  $configString .= "define('DB_NAME', '" . $db_name . "');\n";
  $configString .= "define('DB_TABLE_PREFIX', '" . $db_table_prefix . "');\n";
  //
  // PHP CLOSING TAG Ommited, following several internet documents indications
  // example:
  // http://ellislab.com/codeigniter/user-guide/general/styleguide.html#php_closing_tag
  
  if (@!$handle = fopen($filename, 'w')) 
  {
    $ret['status'] = 'ko';
  }
  
  // Write $somecontent to our opened file.
  if (@fwrite($handle, $configString) === FALSE) 
  {
    $ret['status'] = 'ko';
  }
  @fclose($handle); 
  
  $ret['cfg_string'] = $configString;
  
  return($ret);
}



// Drop tables to allow re-run Installation
function drop_tables(&$dbHandler,$dbTablePrefix,$dbType)
{
  // From 1.9 and up we have detail of tables.
  $schema = tlObjectWithDB::getDBTables();
  
  // tables present on target db
  $my_ado=$dbHandler->get_dbmgr_object();
  $tablesOnDB =$my_ado->MetaTables('TABLES');  
  if( count($tablesOnDB) > 0 && isset($tablesOnDB[0]))
  {
    echo "<br /><b>Dropping all TL existent tables:</b><br />";
    foreach($schema as $tablePlainName => $tableFullName)
    {
      $targetTable = $dbTablePrefix . $tablePlainName;
      if( in_array($targetTable,$tablesOnDB) )
      {
        // Need to add option (CASCADE ?) to delete dependent object
        echo "Droping $targetTable" . "<br />";
        $sql="DROP TABLE $targetTable";
        $sql .= (($dbType != 'mssql') && ($dbType != 'sqlsrv')) ? " CASCADE " : ' ';
        $dbHandler->exec_query($sql);
      }   
    }
    echo "<span class='ok'>Done!</span>";
  }
}

function drop_views(&$dbHandler,$dbItemPrefix,$dbType)
{
  $schema = tlObjectWithDB::getDBViews();
  
  // views present on target db
  $my_ado = $dbHandler->get_dbmgr_object();
  $itemsOnDB =$my_ado->MetaTables('VIEWS');  
  if( count($itemsOnDB) > 0 && isset($itemsOnDB[0]))
  {
    echo "<br /><b>Dropping all TL existent views:</b><br />";
    foreach($schema as $itemPlainName => $itemFullName)
    {
      $target = $dbItemPrefix . $itemPlainName;
      if( in_array($target,$itemsOnDB) )
      {
        // Need to add option (CASCADE ?) to delete dependent object
        echo "Droping $target" . "<br />";
        $sql="DROP VIEW $target";
        $sql .= (($dbType != 'mssql') && ($dbType != 'sqlsrv')) ? " CASCADE " : ' ';
        $dbHandler->exec_query($sql);
      }   
    }
    echo "<span class='ok'>Done!</span>";
  }
}
