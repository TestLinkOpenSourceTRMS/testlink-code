<?

////////////////////////////////////////////////////////////////////////////////
//File:     projectRights.php
//Author:   Chad Rosen
//Purpose:  This page manages the rights on a project basis.
////////////////////////////////////////////////////////////////////////////////

require_once("../../functions/header.php");

  session_start();
  doDBConnect();
  doHeader();
//  doNavBar();

?>

<LINK REL='stylesheet' TYPE='text/css' HREF='kenny.css'>

<?

//$ItemCount = 0;


echo "<table width='100%'>";
echo "<tr><td width='50%' valign='top'>"; //display the users table inside here

	echo "<table class=userinfotable width='100%'>";

	echo "<tr><td class=userinfotablehdr>User</td></tr>";

	$sql = "select id,login from user";

	//Run the query

	$userResult = mysql_query($sql);

	while ($myrowUser = mysql_fetch_row($userResult)) //Display all the users until we run out
	{
			//increment the userCount

			$userCount++;

			if($userCount%2) //Using the mod function to determine if it's even or odd

			{
				$cellColor = '#FFFFFF'; // If even set to yellow
				
			} else 

			{
				$cellColor = '#EEEEEE'; //If odd set to gray

			}


			echo "<tr><td bgcolor=" . $cellColor. "><a href='admin/user/projectRights.php?view=user&id=" . $myrowUser[0] . "' target='mainFrame'>" . $myrowUser[1] . "</a></td></tr>"; //Display the user

			//This next loop will check to see if the user already has rights for a particular project. If they do then I will check the checkbox. If not I leave it blank

			

		}


	echo "</table>";





echo "</td><td width='50%' valign='top'>"; //display the project table inside here

echo "<table class=userinfotable width='100%'>";

	echo "<tr><td class=userinfotablehdr >Project</td></tr>";

	$sql = "select id,name from project where active='y'";

	//Run the query

	$userResult = mysql_query($sql);

	while ($myrowUser = mysql_fetch_row($userResult)) //Display all the users until we run out
	{
			//increment the userCount

			$userCount++;

			if($userCount%2) //Using the mod function to determine if it's even or odd

			{
				$cellColor = '#FFFFFF'; // If even set to yellow
				
			} else 

			{
				$cellColor = '#EEEEEE'; //If odd set to gray

			}


			echo "<tr><td bgcolor=" . $cellColor. "><a href='admin/user/projectRights.php?view=project&id=" . $myrowUser[0] . "' target='mainFrame'>" . $myrowUser[1] . "</a></td></tr>"; //Display the user

			//This next loop will check to see if the user already has rights for a particular project. If they do then I will check the checkbox. If not I leave it blank

			

		}


	echo "</table>";


echo "</td></tr></table>";



?>
