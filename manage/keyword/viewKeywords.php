<?

////////////////////////////////////////////////////////////////////////////////
//File:     viewKeywords.php
//Author:   Chad Rosen
//Purpose:  This page this allows users to view keywords.
////////////////////////////////////////////////////////////////////////////////

require_once("../../functions/header.php");
doSessionStart();
doDBConnect();
doHeader();

	//If the user is comming to this screen through the mainpage or manage screen

	if($_SESSION['product'])
	
	{
	
		require_once('../../navBar.php'); //require_once the nav bar

		echo "<br>";

		echo "<table class=userinfotable width='100%'>";

		echo "<tr><td bgcolor='#CCCCCC'><b>Keyword</td><td bgcolor='#CCCCCC'><b>Notes</td></tr>";

		$sqlKeywords = "select keyword, notes from keywords where prodid ='" . $_SESSION['product'] . "'";

		//echo $sqlKeywords;

		$resultKeywords = mysql_query($sqlKeywords);

		while ($myrowKeys = mysql_fetch_row($resultKeywords))
		{
			echo "<tr><td>" . $myrowKeys[0] . "</td><td>" . $myrowKeys[1] . "</td></tr>";

		}

		echo "</table>";

	}
	
	//If the user is comming to this page from a individual keyword link
	
	elseif($_GET['keyword'])
	{


		echo "<table class=userinfotable width='100%'>";

		echo "<tr><td bgcolor='#CCCCCC'><b>Keyword</td><td bgcolor='#CCCCCC'><b>Notes</td></tr>";

		$sqlKeywords = "select keyword, notes from keywords where keyword ='" . $_GET['keyword'] . "'";

		$resultKeywords = mysql_query($sqlKeywords);

		while ($myrowKeys = mysql_fetch_row($resultKeywords))
		{
			echo "<tr><td>" . $myrowKeys[0] . "</td><td>" . $myrowKeys[1] . "</td></tr>";

		}




	} else {
		pleaseLogin();
		exit();
	}

?>
