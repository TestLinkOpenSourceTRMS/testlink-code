<?php

////////////////////////////////////////////////////////////////////////////////
//File:     newEditKeywords.php
//Author:   Chad Rosen
//Purpose:  This page manages the editing and creation of keywords.
////////////////////////////////////////////////////////////////////////////////

require_once("../functions/header.php");
doSessionStart();
doDBConnect();
doHeader();
doNavbar();

?>

<br>

<?

if(!$_POST['editKey'] && !$_POST['newKey'])
{


	$sql = "select id,keyword, prodid, notes from keywords";

	echo "<table class=userinfotable width='100%'><tr><td bgcolor='#99CCFF'><b>Keyword</td><td bgcolor='#99CCFF'><b>Notes</td><td bgcolor='#99CCFF'><b>Product</td><td bgcolor='#99CCFF'><b>Delete?</td></tr>\n\n";

	$result = mysql_query($sql);

	echo "<FORM method='post' ACTION='manage/newEditKeywords.php'>";
	


	while ($myrow = mysql_fetch_row($result)) 
		{

			$id=$myrow[0];
			$keyword=$myrow[1];
			$prodid=$myrow[2];
			$notes = $myrow[3];
			
			echo "<tr><td><input type='hidden' name='id" . $id . "' " .  "value='" . $id . "'><textarea rows='1' cols='30' name='keyword" . $id . "'>" . $keyword . "</textarea></td><td><textarea rows='1' cols='50' name='notes" . $id . "'>" . $notes . "</textarea></td><td>";

			$sqlPROD = "select id,name from mgtproduct";
			$productResult = mysql_query($sqlPROD);

			echo "<SELECT NAME='product" . $id . "'>";

			while ($productSelect = mysql_fetch_row($productResult))
			{
				
				if($productSelect[0] == $prodid)
				{

					echo "<OPTION VALUE='" . $productSelect[0] ."' SELECTED>" . $productSelect[1];	

				}else
				{
				
					echo "<OPTION VALUE='" . $productSelect[0] ."'>" . $productSelect[1];	
				
				}

								
			}
			
			echo "</SELECT>";
			
			
			echo "</td><td><input type='checkbox' name='check" . $id . "'></tr>\n\n";

		}//END WHILE

	echo "</table>";

	echo "<br><input type='submit' NAME='editKey' value='Edit'>";

	echo "</form>";

	//This code grabs all the possible rights and displays them for the new user imput

	$allPRODSQL = "select id, name from mgtproduct";
	$allPRODResult = mysql_query($allPRODSQL);
			
	while ($allPROD = mysql_fetch_row($allPRODResult))
	{
								
		$PRODOptions .= "<OPTION VALUE='" . $allPROD[0] ."'>" . $allPROD[1];

								
	}


	//Begin the new user form


	echo "<FORM method='post' ACTION='manage/newEditKeywords.php'>";

	echo "<table class=userinfotable width='55%'><tr><td bgcolor='#CCCCCC'><b>Enter New Keyword</td></tr></table>";

	echo "<table class=userinfotable width='55%'>";

	echo "<tr><td>Keyword:</td><td><input type='text' name='keyword'></td></tr>";
	echo "<tr><td>Notes:</td><td><textarea name='notes' rows='1' cols='50'></textarea></td></tr>";
	
	echo "<tr><td>Product:</td><td><select name='PROD'>" . $PRODOptions . "</SELECT></td></tr>";
	
	echo "</table>";

	echo "<br><input type='submit' NAME='newKey' value='New'>";

	echo "</form>";

}

elseif($_POST['newKey'])
{

	$keyword = $_POST['keyword'];
	$prodID = $_POST['PROD'];
	$notes = $_POST['notes'];
	
	$sql = "insert into keywords (keyword,prodid,notes) values ('" . $keyword . "','" . $prodID . "','" . $notes. "')";

	$result = mysql_query($sql);

	echo "<table class=userinfotable width='100%'>";

	echo "<tr><td bgcolor='#99CCFF' wdith='14%'>keyword</td><td bgcolor='#99CCFF' wdith='14%'>prodID</td><td bgcolor='#99CCFF' wdith='14%'>Notes</td><td bgcolor='#99CCFF' wdith='14%'>sql</td></tr>";

	echo "<tr><td>" . $keyword . "</td><td>" . $prodID . "</td><td>" . $notes . "</td><td>" . $sql . "</td></tr>";

	echo "</table>";

	



}elseif($_POST['editKey'])
{

	$i = 0; //start a counter

	//It is necessary to turn the $_POST map into a number valued array

	

	foreach ($_POST as $key)
		{
		
			$newArray[$i] = $key;
			
			$i++;

			
			

		}
	
	$test = array_pop($newArray);

	echo "<table class=userinfotable width='100%'><tr><td bgcolor='#CCCCCC'><h2>Results</h2></td></tr></table>";

	echo "<table class=userinfotable width='100%'>";
	echo "<tr><td bgcolor='#99CCFF' wdith='14%'>Keyword</td><td bgcolor='#99CCFF' wdith='14%'>ProdID</td><td bgcolor='#99CCFF' wdith='14%'>sql</td></tr>";


	

	$i = 0; //Start the counter at 3 because the first three variables are build,date, and submit


	while ($i < (count($newArray))) //Loop for the entire size of the array
	{

		if($newArray[$i + 4] == 'on')
		{
			$id = ($newArray[$i]);
			$keyword = ($newArray[$i + 1]);
			$notes =($newArray[$i + 2]);
			$prodid= ($newArray[$i + 3]);

			$i = $i + 5;

			//echo "delete";

			$sql = "delete from keywords where id='" . $id . "'";

			$result = mysql_query($sql);
			
			echo "<tr><td>" . $keyword . "</td><td>" . $prodid . "</td><td>" . $notes . "</td><td>" . $sql . "</td></tr>";

		}else
		{
			$id = ($newArray[$i]);
			$keyword = ($newArray[$i + 1]);
			$notes = ($newArray[$i + 2]);
			$prodid = ($newArray[$i + 3]);

			$i = $i + 4;

			$sql = "update keywords set notes='" . $notes . "', keyword='" . $keyword . "', prodid='" . $prodid . "' where id='" . $id . "'";

			$result = mysql_query($sql);

			echo "<tr><td>" . $keyword . "</td><td>" . $prodid . "</td><td>" . $notes . "</td><td>" . $sql ."</td></tr>";
			
		}


}

}

?>
