<?php

////////////////////////////////////////////////////////////////////////////////
//File:     projectResult.php
//Author:   Chad Rosen
//Purpose:  This page manages the results of project adding new projects
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

if($_POST['newProject']) //if the user has pressed the create new button
{

	$name = $_POST['name'];
	$notes = $_POST['notes'];
	$copy = $_POST['copy'];
	
	
	//Insert the new project info into the project table

	$sql = "insert into project (name,notes) values ('" . $name . "','" . $notes . "')";

	$result = mysql_query($sql);

	$projID =  mysql_insert_id(); //Grab the id of the project just entered so that the priority table can be filled out

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

	if($_POST['rights'] == 'on')
	{
		$sqlRights = "INSERT into projrights (projid,userid) values (" . $projID . "," . $_SESSION['userID'] . ")";
		$rightsResult = mysql_query($sqlRights) or die(mysql_error());

	}

	//user has decided to copy an existing project. What this code does is loops through each of the components, inserts the component info, loops through the categories from the component and then adds the category, and the same thing as before with test cases.

	if($copy != 'noCopy') //if the user chose to copy then go through this code
	{

		//select all of the component info

		$sqlCom = "select id,name,mgtcompid from component where projid=" . $copy;
	
		$resultComponent = mysql_query($sqlCom);

		while ($myrowCom = mysql_fetch_row($resultComponent)) //loop through the components
		{
		
			//insert it into the component table with new ids
		
			$sqlInsertCom = "insert into component (name,projid,mgtcompid) values ('" . $myrowCom[1] . "','" . $projID . "','" . $myrowCom[2] . "')";

			$resultCom = mysql_query($sqlInsertCom); //run insert code

			$COMID =  mysql_insert_id(); //Grab the id of the project just entered so that the priority table can be filled out

			//Grab all of the currently looping components categories

			$sqlCat = "select id,name,compid,mgtcatid,CATorder from category where compid='" . $myrowCom[0] . "'";

			$resultCat = mysql_query($sqlCat); //run insert code

			while ($myrowCat = mysql_fetch_row($resultCat)) //loop through categories
			{

				//insert the new category

				$sqlInsertCat = "insert into category (name,compid,mgtcatid,CATorder) values ('" . $myrowCat[1] . "','" . $COMID . " ','" . $myrowCat[3]  . "','" . $myrowCat[4] . "')";

				$resultInsertCat = mysql_query($sqlInsertCat); //run insert code

				$CATID = mysql_insert_id(); //grab the catid from the last insert so we can use it for the test case

				//grab all of the test case info.. Anything with a default I ignore

				$sqlTC = "select title,summary,steps,exresult,mgttcid,keywords,TCorder,version from testcase where catid='" . $myrowCat[0] . "'";

				$resultTC = mysql_query($sqlTC); //run insert code

				while ($myrowTC = mysql_fetch_row($resultTC)) //loop through test case code
				{

					//insert the test case code

					$sqlInsertTC = "insert into testcase (title,summary,steps,exresult,catid,mgttcid,keywords,TCorder,version) values ('" . $myrowTC[0] . "','" . $myrowTC[1] . "','" . $myrowTC[2] . "','" . $myrowTC[3] . "','" . $CATID . "','" . $myrowTC[4] . "','" . $myrowTC[5] . "','" . $myrowTC[6] . "','" . $myrowTC[7] . "')";

					$resultInsertTC = mysql_query($sqlInsertTC); //run insert code


				}//end the tc loop

			}//end the cat loop

		}//end the com loop

	}//end the copy if statement


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

	

}

?>
