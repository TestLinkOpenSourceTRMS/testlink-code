<?php

////////////////////////////////////////////////////////////////////////////////
//File:     newEditMilestone.php
//Author:   Chad Rosen
//Purpose:  This page allows the creation and editing of milestones.
////////////////////////////////////////////////////////////////////////////////

//session_start();


require_once("../functions/header.php");

  session_start();
  doDBConnect();
  doHeader();
  doNavBar();


?>

<LINK REL="stylesheet" TYPE="text/css" HREF="kenny.css">

<br>

<?


if(!$_POST['editMilestone'] && !$_POST['newMilestone'])
{


	$sql = "select id,name,date,A,B,C from milestone where projid='" . $_SESSION['project'] . "' and to_days(date) >= to_days(now()) order by date";

	//echo $sql;
	
	echo "<table class=userinfotable width='100%'><tr><td class=userinfotablehdr><b>Existing Milestones (Note: Milestones must be created at today's date or greater)</td></tr></table>";

	echo "<table class=userinfotable width='100%'><tr><td class=edittablehdr>Name</td><td class=edittablehdr>Date (YYYY-MM-DD)</td><td class=edittablehdr>% A</td><td class=edittablehdr>% B</td><td class=edittablehdr>% C</td><td class=edittablehdr>Delete?</td></tr>";

	$result = mysql_query($sql);

	echo "<FORM method='post' ACTION='metrics/newEditMilestone.php'>";	

	while ($myrow = mysql_fetch_row($result)) 
		{

			$id=$myrow[0];
			$name=$myrow[1];
			$date=$myrow[2];
			$A=$myrow[3];
			$B=$myrow[4];
			$C=$myrow[5];

			echo "<tr><td><input type='hidden' name='" . $id . "' " .  "value='" . $id . "'><textarea rows='1' name='name" . $id . "'>" . $name . "</textarea></td><td><textarea rows='1' name='date" . $id . "'>" . $date . "</textarea></td><td><textarea rows='1' cols='4' name='A" . $id . "'>" . $A . "</textarea></td><td><textarea rows='1' cols='4' name='B" . $id . "'>" . $B . "</textarea></td><td><textarea rows='1' cols='4' name='C" . $id . "'>" . $C . "</textarea></td><td><input type='checkbox' name='check" . $id . "'></tr>";

		}//END WHILE

	echo "</table>";

	echo "<br><input type='submit' NAME='editMilestone' value='Edit'>";

	echo "</form>";



	echo "<FORM method='post' ACTION='metrics/newEditMilestone.php'>";

	echo "<table class=userinfotable width='45%'><tr><td class=userinfotablehdr><b>Enter New Milestone</td></tr></table>";

	echo "<table class=userinfotable width='45%'>";

	echo "<tr><td>Name:</td><td><input type='text' name='name'></td></tr>";
	echo "<tr><td>Date (YYYY-MM-DD):</td><td><input type='text' name='date'></td></tr>";
	echo "<tr><td>% A:</td><td><input type='text' name='A'></td></tr>";
	echo "<tr><td>% B:</td><td><input type='text' name='B'></td></tr>";
	echo "<tr><td>% C:</td><td><input type='text' name='C'></td></tr>";

	echo "</table>";

	echo "<br><input type='submit' NAME='newMilestone' value='New'>";

	echo "</form>";

}

elseif($_POST['newMilestone'])
{

	$name = $_POST['name'];
	$date = $_POST['date'];
	$A = $_POST['A'];
	$B = $_POST['B'];
	$C = $_POST['C'];
	$projid = $_SESSION['project'];

	//$db = mysql_connect("mercury", "root");
	//mysql_select_db("kenny",$db);

	$sql = "insert into milestone (projid,name,date,A,B,C) values ('" . $projid . "','" . $name . "','" . $date . "','" . $A . "','" . $B . "','" . $C . "')";

	echo "<table class=userinfotable width='100%'><tr><td bgcolor='#CCCCCC'>Results</td></tr></table>";

	echo "<table class=userinfotable width='100%'><tr><td bgcolor='#99CCFF'>Name</td><td bgcolor='#99CCFF'>Date</td><td bgcolor='#99CCFF'>% A</td><td bgcolor='#99CCFF'>% B</td><td bgcolor='#99CCFF'>% C</td><td bgcolor='#99CCFF'>SQL</td></tr>";

	echo "<tr><td>" . $name . "</td><td>" . $date . "</td><td>" . $A . "</td><td>" . $B . "</td><td>" . $C . "</td><td>" . $sql . "</td></tr></table>";

	$result = mysql_query($sql);



}elseif($_POST['editMilestone'])
{

	//$db = mysql_connect("mercury", "root");
	//mysql_select_db("kenny",$db);

	$i = 0; //start a counter

	//It is necessary to turn the $_POST map into a number valued array

	echo "<table class=userinfotable width='100%'><tr><td bgcolor='#CCCCCC'><h2>Results</h2></td></tr></table>";

	echo "<table class=userinfotable width='100%'>";
	echo "<tr><td bgcolor='#99CCFF'><b>ID</td><td bgcolor='#99CCFF'><b>Name</td><td bgcolor='#99CCFF'><b>Date</td><td bgcolor='#99CCFF'><b>A</td><td bgcolor='#99CCFF'><b>B</td><td bgcolor='#99CCFF'><b>C</td><td bgcolor='#99CCFF'><b>Result</td></tr>";

	foreach ($_POST as $key)
		{
		
		$newArray[$i] = $key;
		$i++;

		}

	$test = array_pop($newArray);

	$i = 0; //Start the counter at 3 because the first three variables are build,date, and submit


	while ($i < (count($newArray))) //Loop for the entire size of the array
	{

		if($newArray[$i + 6] == 'on')
		{
			$id = ($newArray[$i]);
			$name = ($newArray[$i + 1]);
			$date= ($newArray[$i + 2]);
			$A= ($newArray[$i + 3]);
			$B= ($newArray[$i + 4]);
			$C= ($newArray[$i + 5]);

			$i = $i + 7;

			//echo "delete";

			$sql = "delete from milestone where id='" . $id . "'";

			$result = mysql_query($sql);
			
			echo "<tr><td>" . $id . "</td><td>" . $name . "</td><td>" . $date . "</td><td>" . $A ."</td><td>" . $B . "</td><td>" . $C . "</td><td>" . $sql . "</td></tr>";

		}else
		{
			$id = ($newArray[$i]);
			$name = ($newArray[$i + 1]);
			$date= ($newArray[$i + 2]);
			$A= ($newArray[$i + 3]);
			$B= ($newArray[$i + 4]);
			$C= ($newArray[$i + 5]);

			$i = $i + 6;

			$sql = "update milestone set name='" . $name . "', date='" . $date . "', A='" . $A . "', B='" . $B . "', C='" . $C . "' where id='" . $id . "'";

			$result = mysql_query($sql);

			echo "<tr><td>" . $id . "</td><td>" . $name . "</td><td>" . $date . "</td><td>" . $A ."</td><td>" . $B . "</td><td>" . $C . "</td><td>" . $sql . "</td></tr>";
			
		}


}

}

?>
