<?php

////////////////////////////////////////////////////////////////////////////////
//File:     header.php
//Author:   Chad Rosen
//Purpose:  This module contains functions for drawing the "header" for any 
//          given TestLink page.
//
//          The header has several parts:
//            1. do user authentication checks
//            2. start the http session
//            3. draw <head> information, including title, description, etc
//            4. draw the testlink navbar
//            5. draw the breadcrumb
////////////////////////////////////////////////////////////////////////////////

//include the configuration file
require_once("config.inc.php");

//inport the get rights function
require_once("getRights.php");	

/*
//import the db class
require_once("DBControl.php");

// db is a global used throughout the code when accessing the db.
$db = 0;

function doDBPearConnect()
{
	$DBControl = new DBControl;

	$dbHandle = $DBControl->connectDB();

	return $dbHandle;

}

doDBPearConnect();
*/

// doDBConnect
// How TestLink connects to the database
function doDBConnect()
{
	global $db;
	global $dbhost, $dbuser, $dbpasswd, $dbname;
	$db = mysql_connect($dbhost,$dbuser,$dbpasswd);
	if (!$db) {
		return 0;
	}
	mysql_select_db($dbname,$db);
	return 1;
}

// doHeader
// Prints out the HTML HEAD info for all TestLink Pages
//
function doHeader()
{

	doDBConnect() or die("Could not connect to DB");

	global $basehref;

	// grab the product list
	$prodQuery = "select distinct id,name from mgtproduct";
	$prodResult = mysql_query($prodQuery);

	// if the session product exists, check to see if the user has rights to it
	$prodExists = 0;
	if ($_SESSION['product']) {

		if ($prodResult) {
			while ($myrow = mysql_fetch_row($prodResult)) {
				if ($_SESSION['product'] == $myrow[0]) {
					$prodExists = 1;
				}
			}
		}
	}

	// if session product is not set, make it the first in the db list
	if (!$_SESSION['product'] || !$prodExists) {
		$myrow = mysql_fetch_row($prodResult);
		$_SESSION['product'] = $myrow[0];
	}

	// if the session project exists, check to see if the user has rights to it
	$exit = 0;
	if ($_SESSION['project']) {
		$projQuery = "select distinct id,name from project";
		$projResult = mysql_query($projQuery);
		if ($projResult) {
			while ($myrow = mysql_fetch_row($projResult)) {
				$projRightsQuery = "select projid from projrights where userid=" . $_SESSION['userID'] . " and projid=" . $myrow[0];
				$projRightsResult = mysql_query($projRightsQuery);
				if ($projRightsResult) {
					$projRightsRow = mysql_fetch_row($projRightsResult);
					if ($_SESSION['project'] == $projRightsRow[0]) {
						$exit = 1;
					}
				}
			}
		}

		if (!$exit) {
			unset($_SESSION['project']);
		}
	}

	// if session project is not set, make it the first in the db list 
	$hitFirst = 0;
	if (!$_SESSION['project']) {
		$projQuery = "select distinct id,name from project";
		$projResult = mysql_query($projQuery);
		if ($projResult) {
			while ($myrow = mysql_fetch_row($projResult) && !$hitFirst) {
				$projRightsQuery = "select projid from projrights where userid=" . $_SESSION['userID'] . " and projid=" . $myrow[0];
				$projRightsResult = mysql_query($projRightsQuery);
				if ($projRightsResult) {
					$projRightsRow = mysql_fetch_row($projRightsResult);
					$_SESSION['project'] = $projRightsRow[0];
					$hitFirst = 1;
				}
			}
		}
	}

	// Mike's color funk
	//
	// Check to see if this product has custom coloring to be done
	// If so, cache it, and generate a small, custom style sheet to
	// make it work
	//

	$sessionid = $_SESSION['product'];

	if ($sessionid) {
		// check if the product has changed.  If so, reload cache.
		$findStr = $_SESSION['product'] . "|";
		if ( strncmp ($_SESSION['productstyle'], $findStr, strlen($_SESSION['product'])+1) != 0 ) {
			$sql = "select color from mgtproduct where id = " . $sessionid;
			$result = mysql_query($sql);
			if ($result) {
				$myrow = mysql_fetch_row($result);
				if ($myrow) {
					$color = $myrow[0];
					$styles = $styles . "td.myproduct { background: $color ; }";
					$_SESSION['productstyle'] = "$sessionid|$styles";
				} else {
					$_SESSION['productstyle'] = "";
				}
			}
		} else {
			$data = split ( '\|', $_SESSION['productstyle'] );
			$styles = $data[1];
		}
	}

	// Print out the main headers
	echo <<<END
<head>
<base href='$basehref'>
<LINK REL='stylesheet' TYPE='text/css' HREF='kenny.css'>
<style>
<!--
$styles
-->
</style>
</head>
END;

	return 1;
}

// doNavBar
// Displays the basic NavBar for all TestLink Pages
function doNavBar()
{
	global $loginurl;

	// Figure out if the user can do user admin
	if (has_rights("mgt_users")) {
		$useradmin = "<a href='admin/user/newEditUser.php'> User Administration</a> | ";
	} else {
		$useradmin = "";
	}

	//Check to see the product type that was passed in from the frameset and add the corresponding breadcrumb

	if($_GET['type'] == 'product')
	{
		$sqlProduct = "select name from mgtproduct where id=" . $_SESSION['product'];
		$productResult = mysql_query($sqlProduct);
		$myrowProd = mysql_fetch_row($productResult);

		// Figure out the breadcrumb
		$breadcrumb = " > Product > " . $myrowProd[0] . $_GET['nav'] . "</a>";

	}elseif($_GET['type'] == 'project')
	{		
		$sqlProject = "select name from project where id=" . $_SESSION['project'];
		$projectResult = mysql_query($sqlProject);

		$myrowProj = mysql_fetch_row($projectResult);

		// Figure out the breadcrumb
		$breadcrumb = " > Project > " . $myrowProj[0] . $_GET['nav'] . "</a>";


	}else
	{
		
		// Figure out the breadcrumb
		$breadcrumb = " " . $_GET['nav'] . "</a>";

	}


	// Figure out the username
	$user = $_SESSION['user'];
	if ( ! $user ) {
		pleaseLogin();
		exit;
	}

	//This is annoying.. PHP has no globlals and I wanted to display the version in the header..
	//so to get around the problem i now store the version in a session variable..

	//Also, when I tried to simply use the session variable below in the header it barfs.
	//So, I needed to set it to another variable..

	 $ver = $_SESSION['version'];

	// Print out the navbar
	echo <<<END
<table width=100% class=navbar valign='top'>
  <tr bgcolor="#999999"> 
    <td width="22%" rowspan="2" align="left"><font size="6">TestLink</font> $ver </td>
    <td width="78%" align="right"> 
        <b>Welcome <font color='#FF0000' size='+1'> $user </font><br>
    </td>
  </tr>
  <tr> 
    <td align="right" bgcolor="#999999">
      $useradmin
      <a href='userInfo.php'>My User Info</a> 
      | <a href='http://testlink.sourceforge.net' target='_blank'>Documentation</a> 
      | <a href='logout.php' target='_parent'>Log Out</a></td>
  </tr>
  <tr>
    <td colspan="2" bgcolor="#CCCCCC"><a href='mainPage.php' target='_parent'>Home</a>
      $breadcrumb
    </td>
  </tr>
</table>
END;

	return 1;
}

function doSessionStart()
{
	session_set_cookie_params(99999);
	session_start();
	// Allow user to override the session product/product via the
	// query string on any page

	if ($_GET['project']) {
		$_SESSION['project'] = $_GET['project']; 
	}
	if ($_GET['product']) {
		$_SESSION['product'] = $_GET['product'];
	}
	return 1;
}

function testlinkPageStart()
{
	doDBConnect() or die("Could not connect to DB");
	doSessionStart() or die("Could not start session");
	doHeader() or die("Could not create page header");
	doNavBar() or die("Could not create testlink navbar");
}

function pleaseLogin()
{
	global $loginurl;

	echo "<font size=+1 color=red>Your session has expired.  Please <a href=$loginurl>login</a>.</font>";

}

function mainPage()
{

	unset($_SESSION['project']);
	unset($_SESSION['product']);

}
?>
