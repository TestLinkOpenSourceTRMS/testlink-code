<?

////////////////////////////////////////////////////////////////////////////////
//File:     categoryResults.php
//Author:   Chad Rosen
//Purpose:  This file is for generating category results.
////////////////////////////////////////////////////////////////////////////////

require_once("../../functions/header.php");

  session_start();
  doDBConnect();
  doHeader();


if($_POST['submit'])
{

$i = 0; //start a counter

//It is necessary to turn the $_POST map into a number valued array

echo "<table border = 1 width='100%'><tr><td bgcolor='#CCCCCC'><h2>Results</h2></td></tr></table>";

echo "<table border=1 width='100%'>";
echo "<tr><td bgcolor='#99CCFF'><b>Category ID</td><td bgcolor='#99CCFF'><b>Submitted Importance</td><td bgcolor='#99CCFF'><b>Submitted Risk</td><td bgcolor='#99CCFF'><b>Submitted Owner</td><td bgcolor='#99CCFF'><b>Change</td></tr>";

//print_r($_POST);

foreach ($_POST as $key)
    {
	
	$newArray[$i] = $key;
	$i++;

	}


$i = 1; //Start the counter at 1 because the first variable is the submit button

while ($i < count($newArray)) //Loop for the entire size of the array
{

		$catID = $newArray[$i]; //Then the first value is the ID
		$catImp = $newArray[$i + 1]; //The second value is the notes
		$catRisk = $newArray[$i + 2]; //The 3rd value is the status
		$catOwner = $newArray[$i + 3]; //And the 4th value is owner
		
		//SQL statement to look for the same record (tcid, build = tcid, build)

		$sql = "select id, importance, risk, owner from category where id='" . $catID . "'";
	
		$result = mysql_query($sql); //Run the query
		$num = mysql_num_rows($result); //How many results

		//echo $num;

		
		if($num == 1) //If we find a matching record
		{
			
	
			$myrow = mysql_fetch_row($result);
			
			//$queryID = $myRow[0];
			$queryImp = $myrow[1];
			$queryRisk = $myrow[2];
			$queryOwner = $myrow[3];
	
			//If the (notes, status) information is the same.. Do nothing
			
			if($queryImp == $catImp && $queryRisk == $catRisk && $queryOwner == $catOwner)
			{
				
			}

			else

			{

				//update the old result
	
				$sql = "UPDATE category set importance ='" . $catImp . "', risk ='" .  $catRisk . "', owner='" . $catOwner . "' where id='" . $catID . "'";

				$result = mysql_query($sql);

				//echo $sql;

				echo "<tr><td>" . $catID . "</td><td>" . $catImp . "</td><td>" . $catRisk . "</td><td>" . $catOwner . "</td><td>" . $sql . "</td></tr>\n\n";


			}

		
		//If the (notes, status) information is different.. then update the record

		}


		else //If there is no entry for the build or the build is different

		{

		

	

		}
	

		$i = $i + 4; //Increment 4 values to the next catID


}//end while


echo "</table>";

}//end _POST['submit']

elseif($_POST['AllCOM'])
{

//echo "all com";

//print_r($_POST);

$id = $_POST['COMID'];
$risk = $_POST['risk'];
$imp = $_POST['importance'];
$owner = $_POST['owner'];

$sqlCAT = "select id,name from category where compid='" . $id . "'";

$resultCAT = mysql_query($sqlCAT);

while($rowCAT = mysql_fetch_array($resultCAT))
{
	//echo $rowCAT[0] . " " . $rowCAT[1] . "<br>";

	$sqlUpdate = "UPDATE category set importance ='" . $imp . "', risk ='" .  $risk . "', owner='" . $owner . "' where id='" . $rowCAT[0] . "'";

	//cho $sqlUpdate . "<br>";

	$resultUpdate = mysql_query($sqlUpdate);
	


}

echo "<br>All Categories have been changed.<br><br>";

echo "Risk:" . $risk . "<br>";

echo "Importance: " . $imp . "<br>";

echo "Owner: " . $owner . "<br>";


}//end elseif

?>
