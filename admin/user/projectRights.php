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
  doNavBar();

?>

<LINK REL='stylesheet' TYPE='text/css' HREF='kenny.css'>

<?

////////Building the header with the correct projects

echo "<Form Method='POST' ACTION='admin/user/projRightsResult.php'>";

echo "<table class=userinfotable width='100%'>";

echo "<tr><td class=userinfotablehdr></td>";

//Grab all of the projects and list them across the top bar and then store them in an array for later

$sql = "select name,id from project where active='y'";

$result = mysql_query($sql);

$projectCount = mysql_num_rows($result);

//I need to check if there are any available project. Before it was throwing errors up

if($projectCount > 0)

{
	
	while ($myrowProj = mysql_fetch_row($result)) 
	{

		echo "<td class=userinfotablehdr>" . $myrowProj[0] . "</td>"; //display the 

		$projArray[] = $myrowProj[1];

	}

}

//Count the number of users and list the number next to them

$userCount = 0;

//Grabbing all the users to list along the left column

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


		echo "<tr><td bgcolor=" . $cellColor. "><b>" . $userCount . ". " . $myrowUser[1] . "</b></td>"; //Display the user

		//This next loop will check to see if the user already has rights for a particular project. If they do then I will check the checkbox. If not I leave it blank

		

		
		//first I need to check if there actually are any projects created

		if($projectCount > 0)

		{
		
			foreach($projArray as $project) //loop through project array
			{

				//Query the projrights table

				$sql = "select userid from projrights where userid=" . $myrowUser[0] . " and projid = " . $project;

				$rightsResult = mysql_query($sql);

				$numRows = mysql_num_rows($rightsResult);

				//does the user/project exist

				if($numRows > 0) //yes
				{
			
					echo "<td bgcolor=" . $cellColor. "><input type=checkbox name=proj" . $project . "user" . $myrowUser[0] . " value=" . $myrowUser[0] . "," . $project . " checked></td>";

				}else //no
				{
				
					echo "<td bgcolor=" . $cellColor. "><input type=checkbox name=proj" . $project . "user" . $myrowUser[0] . " value=" . $myrowUser[0] . "," . $project . "></td>";

				}//end else

				


			}//end foreach
		
		
		}//end if 



	}


echo "</table>";


echo "<br><input type=submit name=submit value=save>";


echo "</form>";


?>
