<?php

////////////////////////////////////////////////////////////////////////////////
//File:     editDeleteProject.php
//Author:   Chad Rosen
//Purpose:  This page manages the ability to edit and delete projects.
////////////////////////////////////////////////////////////////////////////////

//session_start();

require_once("../../functions/header.php");

  session_start();
  doDBConnect();
  doHeader();
  doNavBar();

?>

<br>

<?

if(!$_POST['editProject'] && !$_POST['newProject'])
{


	$sql = "select id,login, password, rightsid from user";

	echo "<table class=userinfotable width='100%'><tr><td bgcolor='#99CCFF'>Name</td><td bgcolor='#99CCFF'>Notes</td><td bgcolor='#99CCFF'>Active</td><td bgcolor='#99CCFF'>Delete?</td></tr>\n\n";

	$result = mysql_query($sql);

	echo "<FORM method='post' ACTION='admin/project/projectResult.php'>";
	
	$sql = "select id, name, notes,active from project";
	$result = mysql_query($sql);

	while ($myrow = mysql_fetch_row($result)) 
		{

			$id=$myrow[0];
			$name=$myrow[1];
			$notes=$myrow[2];
			$active=$myrow[3];
			
			
			//Displaying the test plans

			echo "<tr><td><input type='hidden' name='" . $id . "' " .  "value='" . $id . "'><textarea rows='1' name='name" . $id . "'>" . $name . "</textarea></td><td><textarea rows='1' cols='50' name='notes" . $id. "'>" . $notes . "</textarea></td>";

			if($active=='y')
			{

				echo "<td><input type='radio' name='archive" . $id . "' value='y' CHECKED>Y<input type='radio' name='archive" . $id . "' value='n'>N</td>";



			}elseif($active=='n')
			{

				echo "<td><input type='radio' name='archive" . $id . "' value='y'>Y<input type='radio' name='archive" . $id . "' value='n' CHECKED>N</td>";

			}

			echo "<td><input type='checkbox' name='check" . $id . "'></tr>\n\n";
			

		}//END WHILE

	echo "</table>";

	echo "<br><input type='submit' NAME='editProject' value='Edit'>";

	echo "</form>";



}

elseif($_POST['newProject']) //if the user has pressed the create new button
{

	$name = $_POST['name'];
	$notes = $_POST['notes'];
	
	$sql = "insert into project (name,notes) values ('" . $name . "','" . $notes . "')";


	$result = mysql_query($sql);

	$projID =  mysql_insert_id(); //Grab the id of the category just entered


	//Create the priority table

	$sql = "INSERT into priority (projid,riskImp) values (" . $projID . ",'L1')";
	$result = mysql_query($sql) or die(mysql_error());

	$sql = "INSERT into priority (projid,riskImp) values (" . $projID . ",'L2')";
	$result = mysql_query($sql) or die(mysql_error());

	$sql = "INSERT into priority (projid,riskImp) values (" . $projID . ",'L3')";
	$result = mysql_query($sql) or die(mysql_error());

	$sql = "INSERT into priority (projid,riskImp) values (" . $projID . ",'M1')";
	$result = mysql_query($sql) or die(mysql_error());

	$sql = "INSERT into priority (projid,riskImp) values (" . $projID . ",'M2')";
	$result = mysql_query($sql) or die(mysql_error());

	$sql = "INSERT into priority (projid,riskImp) values (" . $projID . ",'M3')";
	$result = mysql_query($sql) or die(mysql_error());

	$sql = "INSERT into priority (projid,riskImp) values (" . $projID . ",'H1')";
	$result = mysql_query($sql) or die(mysql_error());

	$sql = "INSERT into priority (projid,riskImp) values (" . $projID . ",'H2')";
	$result = mysql_query($sql) or die(mysql_error());

	$sql = "INSERT into priority (projid,riskImp) values (" . $projID . ",'H3')";
	$result = mysql_query($sql) or die(mysql_error());

	echo "Project " . $name . " Has Been Created";



}elseif($_POST['editProject'])
{

	$i = 0; //start a counter

	//It is necessary to turn the $_POST map into a number valued array so i can loop through it

	foreach ($_POST as $key)
		{
		
			$newArray[$i] = $key;
			
			$i++;

			
			

		}
	
	$test = array_pop($newArray);


	$i = 0; //Start the counter at 3 because the first three variables are build,date, and submit


	while ($i < (count($newArray))) //Loop for the entire size of the array
	{

		if($newArray[$i + 4] == 'on') //if the user has selected to delete the project
		{
			$id = ($newArray[$i]);
			$name = ($newArray[$i + 1]);
			$notes = ($newArray[$i + 2]);
			$active = ($newArray[$i + 3]);

			//Ok this is a really sketchy sketchy area. In fact right now I even have this thing completely greyed out. The sketchy thing is if I give people the abilities to delete projects they are going to completely remove all history. This is really dangerous.

			$i = $i + 5;

			//Select all of the projects priority fields

			$sqlPRIDelete = "delete from priority where projid='" . $id . "'";
			$resultPRIDelete = mysql_query($sqlPRIDelete);		

			//Select all of the projects milestones
				
			$sqlMILDelete = "delete from milestone where projid='" . $id . "'";
			$resultMILDelete = mysql_query($sqlMILDelete);
				
			//Select all of the projects builds

			$sqlBUI = "select build from build where projid='" . $id . "'";
			$resultBUI = mysql_query($sqlBUI);

			while ($myrowBUI = mysql_fetch_row($resultBUI)) 
			{
				
				//Delete all of the results associated with the project		

				$sqlRES = "delete from results where build='" . $myrowBUI[0] . "'";
				$resultRES = mysql_query($sqlRES);

				//Delete all of the bugs associated with the project

				$sqlBUG = "delete from bugs where build='" . $myrowBUI[0] . "'";
				$resultBUG = mysql_query($sqlBUG);
											

			}

			//Delete all of the builds

			$sqlBUIDelete = "delete from build where projid='" . $id . "'";
			$resultBUIDelete = mysql_query($sqlBUIDelete);


			//Select all of the projects components

			$sqlCOM = "select id from component where projid='" . $id . "'";
			$resultCOM = mysql_query($sqlCOM);
			
			while ($myrowCOM = mysql_fetch_row($resultCOM)) 
			{
				
				//Select all of the components categories

				$sqlCAT = "select id from category where compid='" . $myrowCOM[0] . "'";
				$resultCAT = mysql_query($sqlCAT);

				while ($myrowCAT = mysql_fetch_row($resultCAT)) 
				{
					
					//Delete all of the test cases corresponding to the category

					$sqlTCDelete = "delete from testcase where catid='" . $myrowCAT[0] . "'";	
					$resultTCDelete = mysql_query($sqlTCDelete);
									
				
				}

					//Delete the categories

					$sqlCATDelete = "delete from category where compid='" . $myrowCOM[0] . "'";
					$resultCATDelete = mysql_query($sqlCATDelete);

				

			}

				//Delete the components

				$sqlCOMDelete = "delete from component where projid='" . $id . "'";
				$resultCOMDelete = mysql_query($sqlCOMDelete);


			//Finally delete the project

			$sqlProj = "delete from project where id='" . $id . "'";
			$resultProj = mysql_query($sqlProj);
			
			echo "Project " . $name . " Has Been Deleted<br>";



		}else //if the user has edited the data
		{
			$id = ($newArray[$i]);
			$name = ($newArray[$i + 1]);
			$notes = ($newArray[$i + 2]);
			$active = ($newArray[$i + 3]);

			$i = $i + 4;

			$sql = "update project set active='" . $active . "', name='" . $name . "', notes='" . $notes. "' where id='" . $id . "'";

			$result = mysql_query($sql);

			echo "Project " . $name . " Has Been Edited<br>";

			
		}


}


//I need to add the deletion of project rights

//I need to delete the projects builds
	

}

?>
