<?php

////////////////////////////////////////////////////////////////////////////////
//File:     ExecutionResults.php
//Author:   Chad Rosen
//Purpose:  This incredibly importatnt page takes the data submitted from 
//          the user from the execution page and adds it to the database.
////////////////////////////////////////////////////////////////////////////////


require_once("../functions/header.php");

  session_start();
  doDBConnect();
  doHeader();

require_once("../functions/csvSplit.php");




	
$i = 0; //start a counter

//It is necessary to turn the $_POST map into a number valued array

//echo "<table border = 1 width='100%'><tr><td bgcolor='#CCCCCC'><h2>Results</h2></td></tr></table>";


foreach ($_POST as $key)
    {
	
	$newArray[$i] = $key;

	$i++;

	}


//Grab the build and date values from the last form

$date = $newArray[0];
$build = $newArray[1];


$i = 3; //Start the counter at 3 because the first three variables are build,date, and submit

while ($i < count($newArray)) //Loop for the entire size of the array
{

		$tcID = $newArray[$i]; //Then the first value is the ID
		$tcNotes = $newArray[$i + 1]; //The second value is the notes
		$tcStatus = $newArray[$i + 2]; //The 3rd value is the status
		$tcBugs = $newArray[$i + 3]; //The 4th value is the CSV of bugs
	
		
		//SQL statement to look for the same record (tcid, build = tcid, build)

		$sql = "select tcid, build, notes, status from results where tcid='" . $tcID . "' and build='" . $build . "'";
	
		$result = mysql_query($sql); //Run the query
		$num = mysql_num_rows($result); //How many results

		//echo $num;

		
		if($num == 1) //If we find a matching record
		{
						
			//Grabbing the values from the query above
			
			$myrow = mysql_fetch_row($result);
		
			//$queryTCID = $myrow[0];
			//$queryBuild = $myrow[1];
			$queryNotes = $myrow[2];
			$queryStatus = $myrow[3];
			
	
			//If the (notes, status) information is the same.. Do nothing
			
			if($queryNotes == $tcNotes && $queryStatus == $tcStatus)
			{
				
				//Delete all the bugs from the bugs table

				$sqlDelete = "DELETE from bugs where tcid=" . $tcID . " and build=" . $build;

				$result = mysql_query($sqlDelete); //Execute query

				/////Loop to insert the new bugs into the bug table

				//Grabbing the bug info from the results table

				$bugArray = csv_split($tcBugs);

				//print_r($bugArray);

				$counter = 0;

				//echo count($bugArray);

				while($counter < count($bugArray))
				{

					$sql = "insert into bugs (tcid,build,bug) values ('" . $tcID . "','" . $build . "','" . $bugArray[$counter] . "')";

					//echo $counter;

					//echo $sql . "<br><br>";

					$result = mysql_query($sql); //Execute query



					$counter++;

				}

				//Don't display anything if there are no changes
/*
				echo "<table border=1 width='100%'>";
				echo "<tr><td bgcolor='#99CCFF'><b>TC ID</td><td bgcolor='#99CCFF'><b>Submitted Notes</td><td bgcolor='#99CCFF'><b>Submitted Status</td><td bgcolor='#99CCFF'><b>Results</b></td><td bgcolor='#99CCFF'><b>Bugs</td></tr>";
				
				echo "<tr><td>" . $tcID . "</td><td>" . $queryNotes . "</td><td>" . $queryStatus . "<td>info is the same.. Do nothing</td><td>Bugs Filed:" . $tcBugs . "</td></tr>\n\n";

				echo "</table>";*/
			
			}


			else

			{

				//update the old result
	
				$sql = "UPDATE results set runby ='" . $_SESSION['user'] . "', status ='" .  $tcStatus . "', notes='" . $tcNotes . "' where tcid='" . $tcID . "' and build='" . $build . "'";

				$result = mysql_query($sql); //Execute query

				
				
				//Delete all the bugs from the bugs table

				$sqlDelete = "DELETE from bugs where tcid=" . $tcID . " and build=" . $build;

				$result = mysql_query($sqlDelete); //Execute query

				/////Loop to insert the new bugs into the bug table

				//Grabbing the bug info from the results table

				$bugArray = csv_split($tcBugs);

				//print_r($bugArray);

				$counter = 0;

				//echo count($bugArray);

				while($counter < count($bugArray))
				{

					$sqlBugs = "insert into bugs (tcid,build,bug) values ('" . $tcID . "','" . $build . "','" . $bugArray[$counter] . "')";

					//echo $counter;

					//echo $sql . "<br><br>";

					$result = mysql_query($sqlBugs); //Execute query



					$counter++;

				}

				//echo $counter;

				/*echo "<table border=1 width='100%'>";
				echo "<tr><td bgcolor='#99CCFF'><b>Submitted Notes</td><td bgcolor='#99CCFF'><b>Submitted Status</td><td bgcolor='#99CCFF'><b>Result</b></td><td bgcolor='#99CCFF'>Bug Result</td><td bgcolor='#99CCFF'><b>Bugs Filed</td></tr>";

				echo "<tr><td>" . $tcNotes . "</td><td>" . $tcStatus . "</td><td>" . $sql . "</td><td>" . $sqlBugs . "</td><td>" . $tcBugs . "</td></tr>\n\n";

				echo "</table>";*/


			}

		
		//If the (notes, status) information is different.. then update the record

		}


		else //If there is no entry for the build or the build is different

		{

		
		if($tcNotes == "" && $tcStatus == "n") //If the notes are blank and the status is n then do nothing
			{
			
			//echo "<tr><td>" . $tcID . "</td><td>User entered no data so do nothing</td></tr>\n\n";

				//Delete all the bugs from the bugs table

				$sqlDelete = "DELETE from bugs where tcid=" . $tcID . " and build=" . $build;

				$result = mysql_query($sqlDelete); //Execute query

				/////Loop to insert the new bugs into the bug table

				//Grabbing the bug info from the results table

				$bugArray = csv_split($tcBugs);

				//print_r($bugArray);

				$counter = 0;

				//echo count($bugArray);

				while($counter < count($bugArray))
				{

					$sql = "insert into bugs (tcid,build,bug) values ('" . $tcID . "','" . $build . "','" . $bugArray[$counter] . "')";

					$result = mysql_query($sql); //Execute query



					$counter++;

				}
	

					//I dont want to display anything if no data was submitted
		
					/*echo "<table border=1 width='100%'>";
					echo "<tr><td bgcolor='#99CCFF'><b>TC ID</td><td bgcolor='#99CCFF'><b>Submitted Notes</td><td bgcolor='#99CCFF'><b>Submitted Status</td><td bgcolor='#99CCFF'><b>Result</b></td><td bgcolor='#99CCFF'><b>Bug Result</td><td bgcolor='#99CCFF'><b>Bugs Filed</td></tr>";

					echo "<tr><td>" . $tcID . "</td><td>" . $tcNotes . "</td><td>" . $tcStatus . "</td><td>No Data Submittied</td><td>" . $sql . "</td><td>" . $tcBugs . "</td></tr>\n\n";

					echo "</table>";*/




			}

			else //Else enter a new row
			
			{
			
			$sql = "insert into results (build,daterun,status,tcid,notes,runby) values ('" . $build . "','" . $date . "','" . $tcStatus . "','" . $tcID . "','" . $tcNotes . "','" . $_SESSION['user'] . "')";

			$result = mysql_query($sql);

			$sqlDelete = "DELETE from bugs where tcid=" . $tcID . " and build=" . $build;

			$result = mysql_query($sqlDelete); //Execute query

			/////Loop to insert the new bugs into the bug table

			//Grabbing the bug info from the results table

			$bugArray = csv_split($tcBugs);

			$counter = 0;

			while($counter < count($bugArray))
			{

				$sqlBugs = "insert into bugs (tcid,build,bug) values ('" . $tcID . "','" . $build . "','" . $bugArray[$counter] . "')";

				$result = mysql_query($sqlBugs); //Execute query

				$counter++;

			}


				/*echo "<table border=1 width='100%'>";
				echo "<tr><td bgcolor='#99CCFF'><b>Submitted Notes</td><td bgcolor='#99CCFF'><b>Submitted Status</td><td bgcolor='#99CCFF'><b>Result</b></td><td bgcolor='#99CCFF'><b>Bug Result</td><td bgcolor='#99CCFF'><b>Bugs Filed:</td></tr>";			

				echo "<tr><td>" . $tcNotes . "<td>" . $tcStatus . "</td><td>" . $sql . "</td><td>" . $sqlBugs . "<td>" . $tcBugs . "</td></tr>\n\n";

				echo "</table>";*/

			}

				

		}
	

		$i = $i + 4; //Increment 3 values to the next tcID

}//end while

echo "Results Submitted<br><br>";

echo "To continue executing test cases select another component, category, or test case from the left frame";

?>
