<?php
/* TestLink Open Source Project - http://testlink.sourceforge.net/ */
/* $Id: dbUpgrade.php,v 1.2 2005/08/16 17:59:48 franciscom Exp $ */
/**
* Enables users to upgrade their db from previous versions of testlink
*
* @author Asiel Brumfield <asielb@users.sourceforge.net> 
*/
require_once('..' . DIRECTORY_SEPARATOR . 'config.inc.php');

$newTableSchema = 'sql' . DS . 'testlink_create_tables';

// ---------------------------------------------------------------------------------
/** reads in sql files using command line mysql client */
function runMysqlClient($cmdPath, $dbUser, $dbPass, $dbName, $inFile)
{	
  
  $msg = "Running mysql client for " .
         str_replace("\\\\","\\",$inFile) . " .. ";
	echo $msg;

	// escape the string to remove possible bad input
  //	$cmdPath = escapeshellarg($cmdPath);
  
  // on Windows 2000 Pro, the mysql command fails if dbname is surrounded
  // by ' (mysql 4-1-3 beta)
  //
  if ( !(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')) {
	  $dbUser = escapeshellarg($dbUser);
	  $dbPass = escapeshellarg($dbPass);
	  $dbName = escapeshellarg($dbName);
	  $inFile = escapeshellarg($inFile);
  }
  
  // refactorin
  $str = $cmdPath . DIRECTORY_SEPARATOR . 'mysql -u ' . $dbUser . ' ';
	if($dbPass != "") {
  $str .= ' -p' . $dbPass . ' ';
	} 
	$str .= $dbName . ' < ' . $inFile;
		
	// execute the command
	$output = system($str, $retval);
	if ($retval == 0) {
		echo 'Ok.<br />';
	} else {
		echo 'FAIL.<br />';
		echo ' - Failed Request: <br>' . str_replace("\\\\","\\",$str) . '<br />';
	}	
	
	// return any output from executing the command
	return output;
}
// ---------------------------------------------------------------------------------



if(isset($_POST['upgrade'])) {

  echo "<html>"; 
  echo "<head><title> Testlink Upgrade results </title></head>";
  echo <<<END
 
  <style>
  html,body {
    font-family: "verdana";      
    background:white;
    color:navy;
    font-size:10pt;
  }
  
  hr{
    height:1px;
    color: navy;
  }  
  
  .error{
    color:red;
    font-weight:bold;
  }
  
  .success{
    color:##00CC00;
    font-weight:bold;
  }
  
 </style>
END;

  echo "<h1> Testlink Upgrade results </h1>";
  // ----------------------------------------------------------------------------------	
	// create a link to the old database
	$oldDBLink = mysql_connect($_POST['dbHost'],$_POST['dbUser'],$_POST['dbPass'], TRUE); 
	if(!$oldDBLink) { 
		die('Error connecting to oldDB: ' . mysql_error());
	} else {
		echo "<p>Connected to old db .. Ok.</p>";
	}
	mysql_select_db($_POST['oldDBName'], $oldDBLink);
  // ----------------------------------------------------------------------------------
	
	// ----------------------------------------------------------------------------------
	// create a link to the new database
	$newDBLink = mysql_connect($_POST['dbHost'],$_POST['dbUser'],$_POST['dbPass'], TRUE); 
	if(!$newDBLink) {
		die('error connecting to newDB: ' . mysql_error());
	} else {
		echo "<p>Connected to new db .. Ok.</p>";
	}
	mysql_select_db($_POST['newDBName'], $newDBLink);
	// ----------------------------------------------------------------------------------
	
	// ----------------------------------------------------------------------------------
	// create a temporary file for storing data
	$tmpFile = tempnam($_POST['tmpPath'], "tl_temp");		
	
	// Build the commands
	// grab everything except the users and rights
  $dataExecStr = $_POST['myDumpPath'] . DS . 'mysqldump --complete-insert -v -t -r ' 
	  		         . $tmpFile . ' -u ' . $_POST['dbUser'] . ' ';
	

  // add password if needed	
	if($_POST['dbPass'] != "") {
			$dataExecStr .= ' -p' . $_POST['dbPass'] . ' ' ;
			//$schemaExecStr .= ' -p' . $_POST['dbPass'] . ' ' ;
	}
	
	// mgttcarchive
	$dataExecStr .=  $_POST['oldDBName'] . ' bugs build category component keywords '
			           . 'mgtcategory mgtcomponent mgtproduct mgttestcase milestone '
			           . 'priority project projrights results testcase';
	// ------------------------------------------------------------------------------------------------
	
	
	// actually execute the command to export data
	echo "<p>Running mysqldump .. ";
	$output = system($dataExecStr, $retval);
	if ($retval == 0) {
		echo 'Ok.</p>';
	} else {
		echo 'FAIL.<br />';
		echo ' - Failed Request: <br>' . $dataExecStr . '</p>';
		die ('bye!');
	}	
			 	
	
	// create the schema within the new db
	$scriptPath = 'sql' . DS . 'testlink_upgrade_create_tables.sql';
	$output = runMysqlClient($_POST['myDumpPath'], $_POST['dbUser'], $_POST['dbPass'], 
		                       $_POST['newDBName'], $scriptPath);

	// --------------------------------------------------------------------------------------------
	// grab and insert all users with the password converted to md5 format
	// have to user INSERT IGNORE for newer versions of mysql and new UNIQUE index 	
	$userQuery = "INSERT IGNORE INTO " . $_POST['newDBName'] . ".user " .
			"(password,login,id,rightsid,email,first,last) SELECT " .
			"md5(user.password),user.login,user.id,user.rightsid,user.email,user.first,user.last " .
			"FROM user";
	$result = mysql_query($userQuery, $oldDBLink);
	if(!$result) {
		echo "<p>Upgrading users .. FAIL.</p>";	
		die('Had a problem getting users: ' . mysql_error());
	} 
	else
	{
		echo "<p>Upgrading users .. Ok.</p>";	
  }
  // --------------------------------------------------------------------------------------------
  
  
	// load the data into the new schema
	echo '<p>Load the data into the new schema:</p>';
	$output = runMysqlClient($_POST['myDumpPath'], $_POST['dbUser'], $_POST['dbPass'], 
		                       $_POST['newDBName'], $tmpFile);
	
	// delete the temp file
	unlink($tmpFile);				
	
	echo '<p>Upgrade ended</p>';
	
}
else
{

	$smarty = new TLSmarty;
	$smarty->assign('title', 'TestLink - Database upgrade');
	$smarty->assign('os', PHP_OS);
	$smarty->assign('self', $_SERVER["SCRIPT_NAME"]);
  
  if(isset($_POST['upgrade'])) {
	$smarty->assign('submit', 'true');
  }		
	
	$smarty->display('dbUpgrade.tpl');
}
?>
