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

//I've seen some serious include path weirdness when trying to install on a couple servers
//It seems that setting the include path variable (at least locally) stops this problem from happening

ini_set('include_path', '.');

//Not sure if this works or not.. A lot of servers have a default session expire of like 3 minutes. This can be agrivating to users. Users can set their servers cache expire here

//ini_set('session.cache_expire',900);

///////////////////////////////////////////////
//
// Globals you should change for your environment
//
///////////////////////////////////////////////

$dbhost     = "mercury"; //the host name for the server. Use either localhost,server name, or IP

$dbuser     = "root"; //the mysql user
$dbpasswd   = "root"; //the mysql password
$dbname     = "testlink"; //the name of the database

$basehref   = "http://www.qagood.com/kenny/"; //Sets the basehref variable. Important to note that a forward slash "/" is needed in the end

$loginurl   = "http://www.qagood.com";  // where you go back to login

//If you want to use bugzilla then you'll need to set these variables


$bugzillaOn = true; //do you want to use bugzilla to strike through resolved, verified, and closed bugs. By default this is on.

if($bugzillaOn == true) //if the user wants to use bugzilla
{
	
			
	$bzHost= "pesky.good.com"; //bugzilla host
	$bzUser= "dvanhorn"; //bugzilla user
	$bzPasswd= "dvanhorn"; //bugzilla password
	$bzName = "bugs"; //bugzilla default db

	$dbPesky = mysql_connect($bzHost, $bzUser , $bzPasswd); //connect to bugzilla

	mysql_select_db($bzName,$dbPesky); //use the bugs DB
	
	$bzUrl = "http://box.good.com/bugzilla/show_bug.cgi?id="; //this line creates the link to bugzilla

}



///////////////////////////////////////////////
// End of Globals
///////////////////////////////////////////////

require_once("getRights.php");

// db is a global used throughout the code when accessing the db.
$db = 0;

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
		$useradmin = "<a href='admin/user/newEditUser.php' target='main'> User Administration</a> | ";
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


	// Print out the navbar
	echo <<<END
<table width=100% class=navbar valign='top'>
  <tr bgcolor="#999999"> 
    <td width="22%" rowspan="2" align="left"><font size="6">TestLink</font></td>
    <td width="78%" align="right"> 
        <b>Welcome <font color='#FF0000' size='+1'> $user </font><br>
    </td>
  </tr>
  <tr> 
    <td align="right" bgcolor="#999999">
      $useradmin
      <a href='userInfo.php' target='main'>My User Info</a> 
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
