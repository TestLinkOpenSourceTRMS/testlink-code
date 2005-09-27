<?php
/* TestLink Open Source Project - http://testlink.sourceforge.net/ */
/* $Id: installNewDB.php,v 1.11 2005/09/27 16:48:33 franciscom Exp $ */
/*
Parts of this file has been taken from:
Etomite Content Management System
Copyright 2003, 2004 Alexander Andrew Butter
*/

/*
@author Francisco Mancardi - 20050910
refactoring

@author Francisco Mancardi - 20050829
BUGID Mantis: 0000073: DB Creation fails with no message
wrong call to create_user_for_db()

@author Francisco Mancardi - 20050824
moved mysql version check here


*/

require_once("installUtils.php");

session_start();

// 20050926 - fm
set_time_limit(180);

$inst_type = $_SESSION['installationType'];


// 20050806 - fm
define('LEN_PWD_TL_1_0_4',15);
define('ADD_DIR',1);


$sql_create_schema = array();
$sql_default_data = array();

$sql_update_schema = array();
$sql_update_data   = array();


$sql_create_schema[1] = 'sql/testlink_create_tables.sql';
$sql_default_data [1] = 'sql/testlink_create_default_data.sql';

//$sql_upd_dir = 'sql/alter_tables/1.0.4_to_1.6/';



// -------------------------------------------------------------------
// 20050806 - fm 
$sql_schema = $sql_create_schema;
$sql_data   = $sql_default_data;
$msg_process_data = "</b><br />Importing StartUp data<b> ";

if ($inst_type == "upgrade" )
{
	$msg_process_data = "</b><br />Updating Database Contents<b> ";
  $sql_data   = array();
}
// -------------------------------------------------------------------


$the_title = "TestLink Install" . $inst_type;
?>




<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" 
  "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
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
    <td><span class="headers">&nbsp;<img src="./img/dot.gif" alt="" style="margin-top: 1px;" />&nbsp;TestLink</span></td>
    <td align="right"><span class="headers">Installation - <?php echo $inst_type; ?> </span></td>
  </tr>
  <tr class="fancyRow2">
    <td colspan="2" class="border-top-bottom smallText" align="right">&nbsp;</td>
  </tr>
  <tr align="left" valign="top">
    <td colspan="2"><table width="100%"  border="0" cellspacing="0" cellpadding="1">
      <tr align="left" valign="top">
        <td class="pad" id="content" colspan="2">

<?php
if(!isset($_POST['licenseOK']) || empty($_POST['licenseOK'])) 
{
	echo "You need to agree to the license before proceeding with the setup!";
	close_html_and_exit();
}	
?>
TestLink setup will now attempt to setup the database:<br />

<?php

$update_pwd=0;
  
 
$create = false;
$errors = 0;

// get db info from session
$db_server = $_SESSION['databasehost'];
$db_admin_name = $_SESSION['databaseloginname'];
$db_admin_pass = $_SESSION['databaseloginpassword'];
$tl_db_login = $_SESSION['tl_loginname'];
$tl_db_passwd = $_SESSION['tl_loginpassword'];
$db = $_SESSION['databasename'];


// $table_prefix = $_SESSION['tableprefix'];
$table_prefix ='';

// 20050731 - fm
//$adminname = $_SESSION['cmsadmin'];
//$adminpass = $_SESSION['cmspassword'];
$adminname = '';
$adminpass = '';


// do some database checks
echo "</b><br />Creating connection to Database Server:<b> ";

// ------------------------------------------------------------------------------------------------
// Connect to DB Server without choosing an specific database
if(!@$conn = mysql_connect($db_server, $db_admin_name, $db_admin_pass)) 
{
	echo '<span class="notok">Failed!</span><p />Please check the database login details and try again.';
	echo '<br>MySQL Error Message: ' . mysql_error() . "<br>";
	
	close_html_and_exit();
} 
else 
{
	echo "<span class='ok'>OK!</span><p />";
}
// ------------------------------------------------------------------------------------------------

// ------------------------------------------------------------------------------------------------
// 20050824 - fm
// Succesful Connection, now try to check MySQL Version
$check=check_mysql_version($conn);
if($check['errors'] > 0) 
{
	echo '<span class="notok">' . $check['msg'] .'</span><p />';
	close_html_and_exit();
}
else
{
	echo "<span class='ok'>OK!", $check['msg'], "</span><p />" ;
}	 
// ------------------------------------------------------------------------------------------------


// ------------------------------------------------------------------------------------------------
// Succesful Connection, now try to select the database
if(!@mysql_select_db($db, $conn)) 
{
	echo "</b><br>Database $db does not exist. <br>Will attempt to create:";
	$errors += 1;
	$create = true;
} 
else 
{
  echo "</b><br />Selecting database `".$db."`:<b> ";
	echo "<span class='ok'>OK!</span>";
}
// ------------------------------------------------------------------------------------------------


// ------------------------------------------------------------------------------------------------
if($create) 
{
	echo "</b><br />Creating database `".$db."`:<b> ";

  // 20050826 - fm
  // BUGID Mantis: 0000073: DB Creation fails with no message
  $sql_create = "CREATE DATABASE " . $db . " CHARACTER SET utf8 "; 
	if(!@mysql_query($sql_create, $conn)) 
	{
		echo "<span class='notok'>Failed!</span></b> - Could not create database: $db!";
		$errors += 1;
		
		echo "<p> TestLink setup could not create the database, " .
		     "and no existing database with the same name was found. <br />" .
		     "Please create a database, and run setup again.";
		close_html_and_exit();     
	} 
	else 
	{
		echo "<span class='ok'>OK!</span>";
	}
}

// 20050806 - fm
// in upgrade mode we detect the lenght of user password field
// to identify a version with uncrypted passwords
if ($inst_type == "upgrade" )
{

  $check_passwd_type = mysql_query("SELECT password FROM user");
  if (!$check_passwd_type) 
  {
     echo 'Could not run query: ' . mysql_error();
     exit;
  }
  $pwd_field_len = mysql_field_len($check_passwd_type, 0);

  if ( $pwd_field_len == LEN_PWD_TL_1_0_4 )
  {
    $update_pwd=1;
    echo "<br>You are upgrading from a TL pre 1.5" .
         "<br>user's password will be crypted using MD5"; 	
  }
}
// ------------------------------------------------------------------------------------------------

// ------------------------------------------------------------------------------------------------
// 20050908 - fm
if ( $inst_type == "upgrade") 
{
  if ($update_pwd)
  {
  	$sql_upd_dir = 'sql/alter_tables/1.0.4_to_1.6/';
  }
  else
  {
    // try to guess TL version
    $sql = "SHOW TABLES FROM {$db} LIKE 'db_version' ";
    $res = mysql_query($sql);
    
    if (!$res)
    {
      echo "MySQL ERROR:" . mysql_error();
      exit(); 
    }
    if( mysql_num_rows($res) == 0 )
    {
      // We are upgrading from a pre 1.6 version
  	  $sql_upd_dir = 'sql/alter_tables/1.5_to_1.6/';
    }
    else
    {
      // 20050927 - fm
      // try to know what db version is installed
      $sql = "SELECT * FROM db_version ORDER BY upgrade_date DESC LIMIT 1";
    
      $res = mysql_query($sql);  
      if (!$res)
      {
       echo "MySQL ERROR:" . mysql_error();
       exit(); 
      }
      $myrow = mysql_fetch_assoc($res);
      
      if ( strcmp(trim($myrow['version']), '1.6 BETA 1') == 0 )
      {
      	$sql_upd_dir = 'sql/alter_tables/1.6/';
      }
    }
  }

  //
  $sql_schema = getDirFiles($sql_upd_dir,ADD_DIR);
}
// ------------------------------------------------------------------------------------------------




// ------------------------------------------------------------------------------------------------
// Now proceed with user checks and user creation (if needed)
//
// 20050910 - fm
// Added support for different types of architecture/installations:
// 
// webserver and dbserver on same machines => user must be created as user@dbserver
// webserver and dbserver on DIFFERENT machines => user must be created as user@webserver
//  
// if @ in username -> get the hostname splitting, ignoring argument db_server
//
$msg = create_user_for_db($conn, $db, $tl_db_login, $tl_db_passwd, $db_server);


echo "</b><br />Creating Testlink DB user `" . $tl_db_login . "`:<b> ";

if ( strpos($msg,'ok -') === FALSE )
{
		echo "<span class='notok'>Failed!</span></b> - Could not create user: $tl_db_login!";
		$errors += 1;
}
else
{
		echo "<span class='ok'>OK! ($msg) </span>";
}
// ------------------------------------------------------------------------------------------------


//  ------------------------------------------------------------------------------------------
include "sqlParser.class.php";

// 20050804 - fm
// Schema Operations (CREATE, ALTER, ecc).
$sqlParser = new SqlParser($db_server, $db_admin_name, $db_admin_pass, 
                           $db, $table_prefix, $adminname, $adminpass);

$sqlParser->connect();
foreach ($sql_schema as $sql_file) 
{
	echo "<br>Processing:" . $sql_file;
	$sqlParser->process($sql_file);
}
echo "<br>";
$sqlParser->close();


/*
echo "<pre>";
foreach ($sqlParser->mysqlErrors as $v)
 echo $v . "<br>";
echo "</pre>";
*/


// 20050804 - fm
// Data Operations
if ( count($sql_data > 0) )
{
	echo $msg_process_data;

	$sqlParser = new SqlParser($db_server, $db_admin_name, $db_admin_pass, 
  	                         $db, $table_prefix, $adminname, $adminpass);
	$sqlParser->connect();
	
  foreach ($sql_data as $sql_file) 
  {
	  $sqlParser->process($sql_file);
  }
	
	$sqlParser->close();
}

// 20050806 - fm
if ($update_pwd)
{
  $conn = mysql_connect($db_server, $db_admin_name, $db_admin_pass);
  mysql_select_db($db, $conn);

	echo "Password Conversion ...";
	$user_pwd = "UPDATE USER SET PASSWORD=MD5(PASSWORD)";
	$result = mysql_query($user_pwd);
}


if($sqlParser->installFailed==true) 
{

	echo "<span class='notok'>Failed!</span></b> - Installation failed!";
	$errors += 1;

  echo "<p />" .
       "TestLink setup couldn't install the default site into the selected database. " .
       "The last error to occur was <i>" . $sqlParser->mysqlErrors[count($sqlParser->mysqlErrors)-1]["error"] .
       '</i> during the execution of SQL statement <span class="mono">' .
       strip_tags($sqlParser->mysqlErrors[count($sqlParser->mysqlErrors)-1]["sql"]). "</span>";
       
	close_html_and_exit();     
} 
else 
{
	echo "<span class='ok'>OK!</span>";
}

// -----------------------------------------------------------------------------
echo "</b><br />Writing configuration file:<b> ";
$data['db_host']=$db_server;

// 20050723 - fm
$data['db_login']=$tl_db_login;
$data['db_passwd']=$tl_db_passwd;

$data['db_name']=$db;
$cfg_file = "../config_db.inc.php";
$yy = write_config_db($cfg_file,$data);
// -----------------------------------------------------------------------------


if(strcasecmp('ko', $yy['status']) == 0)
{
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

echo "</b><p />" . 'Installation was successful! You can now log into the <a href="../index.php">TestLink (Please Click Me!)</a>.';
close_html_and_exit();     

?>

<?php
// -----------------------------------------------------------
// 20050910 - fm
function write_config_db($filename, $data)
{

$ret = array('status'     => 'ok',
             'cfg_string' => '');

               
$db_host = $data['db_host'];
$db_login = $data['db_login'];

// 20050910 - fm
// if @ present in db_login, explode an take user name WITHOUT HOST
$the_host = $db_login;
if (count($user_host) > 1 )
{
  $db_login = $user_host[0];    
}

$db_passwd = $data['db_passwd'];
$db_name = $data['db_name'];

// write config.inc.php
$configString = "<?php" . "\n" . "// Automatically Generated by TestLink Installer\n";
$configString .= "define('DB_TYPE', 'mysql');\n";
$configString .= "define('DB_USER', '" . $db_login . "');\n";
$configString .= "define('DB_PASS', '" . $db_passwd . "');\n";
$configString .= "define('DB_HOST', '" . $db_host . "');\n";
$configString .= "define('DB_NAME', '" . $db_name . "');\n";
$configString .= "?>";


if (@!$handle = fopen($filename, 'w')) {
	$ret['status'] = 'ko';
}

// Write $somecontent to our opened file.
if (@fwrite($handle, $configString) === FALSE) {
	$ret['status'] = 'ko';
}
@fclose($handle);	

$ret['cfg_string'] = $configString;

return($ret);

}  //function end
// --------------------------------------------------------------------------

?>
