<?php

////////////////////////////////////////////////////////////////////////////////
//File:     priorityDefinition.php
//Author:   Chad Rosen
//Purpose:  This page allows users to change the risk/importance for different
//          categories.
////////////////////////////////////////////////////////////////////////////////

require_once("../../functions/header.php");

  session_start();
  doDBConnect();
  doHeader();

?>

<LINK REL="stylesheet" TYPE="text/css" HREF="kenny.css">

<?

if($_POST['submitPri'])
{

	$i = 0; //start a counter

	foreach ($_POST as $key)
		{
		
		$newArray[$i] = $key;
		$i++;

		}


	$i = 0; //Start the counter at 0. In this version the last variable is the submit button

	while ($i < (count($newArray) - 1)) //Loop for the entire size of the array
	{

			$priID = $newArray[$i]; //Then the first value is the ID
			//$catImp = $newArray[$i + 1]; //The second value is the notes
			$priority = $newArray[$i + 1]; //The second value is the notes
			
			//SQL statement to look for the same record (tcid, build = tcid, build)

			$sql = "select id, priority from priority where id='" . $priID . "'";
		
			$result = mysql_query($sql); //Run the query
			$num = mysql_num_rows($result); //How many results

			
			
			if($num == 1) //If we find a matching record
			{
				
		
				$myrow = mysql_fetch_row($result);
				
				//$queryID = $myRow[0];
				$queryPri = $myrow[1];
		
				//If the (notes, status) information is the same.. Do nothing	

				if($queryPri != $priority)
				{

					//update the old result
		
					$sql = "UPDATE priority set priority ='" . $priority . "' where id='" . $priID . "'";

					$result = mysql_query($sql);

				}

			
			//If the (notes, status) information is different.. then update the record

			}

			$i = $i + 2; //Increment 4 values to the next catID


	}//end while



}

	echo "<form method='post' action='admin/category/priorityDefinition.php'>\n\n";

	echo "<table class=userinfotable width='100%'><tr><td bgcolor='#CCCCCC'>Priority Definition</td></tr></table>";

	echo "<table class=userinfotable width='100%'>";

	echo "<tr><td bgcolor='#99CCFF'>Risk/Importance</td><td bgcolor='#99CCFF'>Priority (C/B/A)</td></tr>\n\n";

	$sql = "select id, riskImp, priority from priority where projid='" . $_SESSION['project'] . "'";

	
	$result = mysql_query($sql); //Run the query

	while($row = mysql_fetch_array($result)){

		$id = $row[0]; 
		$riskImp = $row[1]; 
		$priority = $row[2]; 
		

		if($priority == 'a')
		{

			$priRadio = "<td><input type='radio' name='priority" . $id . "' value='c'><input type='radio' name='priority" . $id . "' value='b'><input type='radio' name='priority" . $id . "' value='a' CHECKED></td>";

		}elseif($priority == 'b')
		{


			$priRadio = "<td><input type='radio' name='priority" . $id . "' value='c'><input type='radio' name='priority" . $id . "' value='b' CHECKED><input type='radio' name='priority" . $id . "' value='a'></td>";

		}elseif($priority == 'c')
		{


			$priRadio = "<td><input type='radio' name='priority" . $id . "' value='c' CHECKED><input type='radio' name='priority" . $id . "' value='b'><input type='radio' name='priority" . $id . "' value='a'></td>";

		}

		echo "<tr><td><input type='hidden' name='id" . $id . "' value='" . $id . "'>" . $riskImp . "</td>" . $priRadio . "</tr>";
		


	}//end while

	echo "</table>";

	echo "<br><input type='Submit' name='submitPri' value='Save Information'>";
	
	echo "</form>";


?>
