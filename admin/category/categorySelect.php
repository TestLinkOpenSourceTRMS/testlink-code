<?php

////////////////////////////////////////////////////////////////////////////////
//File:     categorySelect.php
//Author:   Chad Rosen
//Purpose:  This file allows administrators to manage the ownership of
//          categories.
////////////////////////////////////////////////////////////////////////////////

require_once("../../functions/header.php");

  session_start();
  doDBConnect();
  doHeader();


?>

<LINK REL="stylesheet" TYPE="text/css" HREF="kenny.css">

<?


if($_GET['edit'] == 'info')
{

	echo "<table class=helptable width=100%>";
	echo "<tr><td class=helptabletitle>Defining Category Ownership/Priority</td></tr></table>";

	echo "<table class=helptable width=100%>";

	echo "<tr><td class=helptablehdr>Purpose:</td><td class=helptable>This middle section of this page allows the user to set the risk,importance, and owner of each category. The right section allows the user to set the definition of each risk/importance pairing</td></tr>";
	echo "<tr><td class=helptablehdr>Getting Started:</td><td class=helptable>Click on a component to see all of its categories. Change the risk and importance using the radio buttons. The category owner can be set using the text box.</td></tr>";
	echo "</table>";





}

if($_GET['edit'] == 'component')
{



	//Grab the component name

	$sqlName = "select name from component where component.id='" . $_GET['com'] . "'";

	$resultName = @mysql_query($sqlName);

	$rowName = mysql_fetch_array($resultName);

	echo "<form name='categorySelect' method='post' ACTION='admin/category/categoryResults.php'><input type='submit' name='AllCOM' value='Edit Component Info'>";

	//Display title

	echo "<table class=userinfotable width='100%'>";

	echo "<tr><td bgcolor='#EEEEEE'><b>Set the importance,risk and ownership of each category in this component</td></tr>";

	echo "</table>";

	//Display Data
	
	echo "<table class=userinfotable width='100%'>";

	echo "<tr><td class=edittablehdr width='55%'>Component Name</td><td class=edittablehdr width='10%'>Importance (L/M/H)</td><td class=edittablehdr width='10%'>Risk (3/2/1)</td><td class=edittablehdr width='25%' >Owner</td></tr>";

	echo "<tr><td><input type='hidden' name='COMID' value='" . $_GET['com'] . "'>" . $rowName[0] . "</td>";

	echo "<td><input type='radio' name='importance' value='L'><input type='radio' name='importance' value='M'  CHECKED><input type='radio' name='importance' value='H'></td>";

	echo "<td><input type='radio' name='risk' value='3'><input type='radio' name='risk' value='2' CHECKED><input type='radio' name='risk' value='1'></td>";

	$sqlUser = "select login from user";

			$resultUser = @mysql_query($sqlUser);

			echo "<td><select name='owner" . $id . "'>";

			echo "<option value='None'>None</option>";

		while($rowUser = mysql_fetch_array($resultUser))	
		{

			if($rowUser[0] == $owner)
			{
				echo "<option value='" . $rowUser[0] . "' Selected>" . $rowUser[0] . "</option>"; 
				
			}else
			{
				echo "<option value='" . $rowUser[0] . "'>" . $rowUser[0] . "</option>"; 
			}


		}

	echo "</select></td>";



	echo "</table>";

	echo "</form>";


	echo "<form name='categorySelect' method='post' ACTION='admin/category/categoryResults.php'><input type='submit' name='submit' value='Edit Categories'>";

	$sql = "select id, name from component where projid ='" . $_SESSION['project'] . "' and component.id='" . $_GET['com'] . "' order by name";

	$result = @mysql_query($sql);


	//Selecting all components from the selected project


while($rowCOMP = mysql_fetch_array($result)){ //loop through all components
	{

		//echo "<table border='1' width='100%'><tr><td bgcolor='#CCCCCC'><b>" . $rowCOMP[1] . "</td></tr></table>";
		
		//Selecting all categories from the components selected above

		$sqlCAT = "select id, name, importance, risk, owner from category where compid ='" . $rowCOMP[0] . "' order by CATorder";
		$resultCAT = @mysql_query($sqlCAT);

		
		echo "<table class=userinfotable width='100%'>";

		echo "<tr><td class=edittablehdr width='55%'>Category Name</td><td class=edittablehdr width='10%'>Importance (L/M/H)</td><td width='10%' class=edittablehdr>Risk (3/2/1)</td><td class=edittablehdr width='25%'>Owner</td></tr>\n\n";

		while($row = mysql_fetch_array($resultCAT)){ //loop through all categories

			//Getting and setting the variables from the query

			$id = $row['id'];
			$name = $row['name'];
			$importance = $row['importance'];
			$risk = $row['risk'];
			$owner = $row['owner'];
			
			if($importance == 'L') //If the user has selected a risk of three check the L radio button
			{
				$impRadio = "<td><input type='radio' name='importance" . $id . "' value='L' CHECKED><input type='radio' name='importance" . $id . "' value='M'><input type='radio' name='importance" . $id . "' value='H'></td>";
			
			}elseif($importance == 'M') //If the user has selected a risk of three check the M radio button
			{

				$impRadio = "<td><input type='radio' name='importance" . $id . "' value='L'><input type='radio' name='importance" . $id . "' value='M' CHECKED><input type='radio' name='importance" . $id . "' value='H'></td>";

			}elseif($importance == 'H') //If the user has selected a risk of three check the H radio button
			{

				$impRadio = "<td><input type='radio' name='importance" . $id . "' value='L'><input type='radio' name='importance" . $id . "' value='M'><input type='radio' name='importance" . $id . "' value='H' CHECKED></td>";

			}


			if($risk == '3') //If the user has selected a risk of three check the 3 radio button
			{

				$riskRadio = "<td><input type='radio' name='risk" . $id . "' value='3' CHECKED><input type='radio' name='risk" . $id . "' value='2'><input type='radio' name='risk" . $id . "' value='1'></td>";


			}elseif($risk == '2') //If the user has selected a risk of three check the 2 radio button
			{

				$riskRadio = "<td><input type='radio' name='risk" . $id . "' value='3'><input type='radio' name='risk" . $id . "' value='2' CHECKED><input type='radio' name='risk" . $id . "' value='1'></td>";

			}elseif($risk == '1') //If the user has selected a risk of three check the 1 radio button
			{

				$riskRadio = "<td><input type='radio' name='risk" . $id . "' value='3'><input type='radio' name='risk" . $id . "' value='2'><input type='radio' name='risk" . $id . "' value='1' CHECKED></td>";


			}

			//Print out the owner stuff

			echo "<tr><td><input type='hidden' name='id" . $id . "' value='" . $id . "'>" . $name . "</td>" . $impRadio . $riskRadio . "<td>";

			//Code to give the user a dropdown box to select from
			
			$sqlUser = "select login from user";

			$resultUser = @mysql_query($sqlUser);

			echo "<select name='owner" . $id . "'>";

			echo "<option value='None'>None</option>";

			while($rowUser = mysql_fetch_array($resultUser))	
			{

				if($rowUser[0] == $owner)
				{
					echo "<option value='" . $rowUser[0] . "' Selected>" . $rowUser[0] . "</option>"; 
				
				}else
				{
					echo "<option value='" . $rowUser[0] . "'>" . $rowUser[0] . "</option>"; 
				}


			}

			echo "</select>";

			//echo "<textarea name='owner" . $id . "' cols='10' rows='1'>" . $owner . "</textarea></td>";
			
			
			echo "</td></tr>\n\n";
		
		}//end category display

		echo "</table>";

	}//end component display

	

	}

	echo "</form>";




}

if($_GET['edit'] == 'category')
{

	echo "<form name='categorySelect' method='post' ACTION='admin/category/categoryResults.php'><input type='submit' name='submit' value='Edit Categories'>";
	
		//Selecting all categories from the components selected above

		$sqlCAT = "select id, name, importance, risk, owner from category where id ='" . $_GET['cat'] . "' order by CATorder";
		$resultCAT = @mysql_query($sqlCAT);

		
		echo "<table class=userinfotable width='100%'>";

		echo "<tr><td class=edittablehdr width='55%'>Category Name</td><td class=edittablehdr width='10%'>Importance (L/M/H)</td><td class=edittablehdr width='10%'>Risk (3/2/1)</td><td class=edittablehdr width='25%'>Owner</td></tr>\n\n";

		while($row = mysql_fetch_array($resultCAT)){ //loop through all categories

			//Getting and setting the variables from the query

			$id = $row['id'];
			$name = $row['name'];
			$importance = $row['importance'];
			$risk = $row['risk'];
			$owner = $row['owner'];
			
			if($importance == 'L') //If the user has selected a risk of three check the L radio button
			{
				$impRadio = "<td><input type='radio' name='importance" . $id . "' value='L' CHECKED><input type='radio' name='importance" . $id . "' value='M'><input type='radio' name='importance" . $id . "' value='H'></td>";
			
			}elseif($importance == 'M') //If the user has selected a risk of three check the M radio button
			{

				$impRadio = "<td><input type='radio' name='importance" . $id . "' value='L'><input type='radio' name='importance" . $id . "' value='M' CHECKED><input type='radio' name='importance" . $id . "' value='H'></td>";

			}elseif($importance == 'H') //If the user has selected a risk of three check the H radio button
			{

				$impRadio = "<td><input type='radio' name='importance" . $id . "' value='L'><input type='radio' name='importance" . $id . "' value='M'><input type='radio' name='importance" . $id . "' value='H' CHECKED></td>";

			}


			if($risk == '3') //If the user has selected a risk of three check the 3 radio button
			{

				$riskRadio = "<td><input type='radio' name='risk" . $id . "' value='3' CHECKED><input type='radio' name='risk" . $id . "' value='2'><input type='radio' name='risk" . $id . "' value='1'></td>";


			}elseif($risk == '2') //If the user has selected a risk of three check the 2 radio button
			{

				$riskRadio = "<td><input type='radio' name='risk" . $id . "' value='3'><input type='radio' name='risk" . $id . "' value='2' CHECKED><input type='radio' name='risk" . $id . "' value='1'></td>";

			}elseif($risk == '1') //If the user has selected a risk of three check the 1 radio button
			{

				$riskRadio = "<td><input type='radio' name='risk" . $id . "' value='3'><input type='radio' name='risk" . $id . "' value='2'><input type='radio' name='risk" . $id . "' value='1' CHECKED></td>";


			}

			//Print out the owner stuff

			echo "<tr><td><A NAME='" . $name . "'><input type='hidden' name='id" . $id . "' value='" . $id . "'>" . $name . "</td>" . $impRadio . $riskRadio . "<td>";
			
			$sqlUser = "select login from user";

			$resultUser = @mysql_query($sqlUser);

			echo "<select name='owner" . $id . "'>";

			echo "<option value='None'>None</option>";

			while($rowUser = mysql_fetch_array($resultUser))	
			{

				if($rowUser[0] == $owner)
				{
					echo "<option value='" . $rowUser[0] . "' Selected>" . $rowUser[0] . "</option>"; 
				
				}else
				{
					echo "<option value='" . $rowUser[0] . "'>" . $rowUser[0] . "</option>"; 
				}


			}

			echo "</select>";

			echo "</td></tr>";
			
			
			//<textarea name='owner" . $id . "' cols='10' rows='1'>" . $owner . "</textarea></td></tr>\n\n";
		
		}//end category display

		echo "</table>";

	//}//end component display



	echo "</form>";



}


?>
