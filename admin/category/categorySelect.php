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

?>
	<table class=helptable width=100%>
		<tr>
			<td class=helptabletitle>
				Defining Category Ownership/Priority
			</td>
		</tr>
	</table>

	<table class=helptable width=100%>
		<tr>
			<td class=helptablehdr>
				Purpose:
			</td>
			<td class=helptable>
				This middle section of this page allows the user to set the risk,importance, and owner of each category. The right section allows the user to set the definition of each risk/importance pairing
			</td>
		</tr>
		<tr>
			<td class=helptablehdr>
				Getting Started:
			</td>
			<td class=helptable>
				Click on a component to see all of its categories. Change the risk and importance using the radio buttons. The category owner can be set using the text box.
			</td>
		</tr>
	</table>

<?

}

if($_GET['edit'] == 'component')
{

?>

	<form name='categorySelect' method='post' ACTION='admin/category/categoryResults.php'>
		<input type='submit' name='submit' value='Edit Categories'>
	
	<?

	$sql = "select id, name from component where projid ='" . $_SESSION['project'] . "' and component.id='" . $_GET['com'] . "' order by name";

	$result = @mysql_query($sql);


	//Selecting all components from the selected project


while($rowCOMP = mysql_fetch_array($result)){ //loop through all components
	{
	
		//Selecting all categories from the components selected above

		$sqlCAT = "select id, name, importance, risk, owner from category where compid ='" . $rowCOMP[0] . "' order by CATorder";
		$resultCAT = @mysql_query($sqlCAT);
		

		
		echo "<table class=userinfotable width='100%'>";

		echo "<tr><td class=edittablehdr>Category Name</td><td class=edittablehdr>Importance (L/M/H)</td><td  class=edittablehdr>Risk (3/2/1)</td><td class=edittablehdr>Owner</td></tr>\n\n";

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
	?>
	
	<form name='categorySelect' method='post' ACTION='admin/category/categoryResults.php'>
		<input type="submit" name="submit" value="Edit This Category's test cases">

		<table class=userinfotable width='100%'>
			<tr>
				<td class=edittablehdr>
					Test Case Name
				</td>
				<td class=edittablehdr >
					Importance (L/M/H)
				</td>
				<td class=edittablehdr >
					Risk (3/2/1)
				</td>
				<td class=edittablehdr >
					Owner
				</td>
			</tr>

	<?
	
	$sqlTC = "select id, title from testcase where catid='" . $_GET['cat'] . "' order by TCOrder";

	echo $sqlTC;
	$resultCAT = @mysql_query($sqlTC);

	while($row = mysql_fetch_array($resultCAT))
	{ //loop through all categories

		//Getting and setting the variables from the query

			$id = $row['id'];
			$name = $row['title'];
			$risk = "1";
			$importance = "H";
			$owner = "none";

			echo "id:" . $id . " title: " . $name . "<br>";

			?> 
				<tr><td><? echo $name ?></td><td>
			<?

			if($importance == 'L') //If the user has selected a risk of three check the L radio button
			{
				echo "<input type='radio' name='importance" . $id . "' value='L' CHECKED>";
				echo "<input type='radio' name='importance" . $id . "' value='M'>";
				echo "<input type='radio' name='importance" . $id . "' value='H'>";
			
			}elseif($importance == 'M') //If the user has selected a risk of three check the M radio button
			{

				echo "<input type='radio' name='importance" . $id . "' value='L' CHECKED>";
				echo "<input type='radio' name='importance" . $id . "' value='M'>";
				echo "<input type='radio' name='importance" . $id . "' value='H'>";

			}elseif($importance == 'H') //If the user has selected a risk of three check the H radio button
			{

				echo "<input type='radio' name='importance" . $id . "' value='L' CHECKED>";
				echo "<input type='radio' name='importance" . $id . "' value='M'>";
				echo "<input type='radio' name='importance" . $id . "' value='H'>";

			}
			
			?></td><td><?

			if($risk == '3') //If the user has selected a risk of three check the 3 radio button
			{

				echo "<input type='radio' name='risk" . $id . "' value='3' CHECKED>";
				echo "<input type='radio' name='risk" . $id . "' value='2'>";
				echo "<input type='radio' name='risk" . $id . "' value='1'>";

			}elseif($risk == '2') //If the user has selected a risk of three check the 2 radio button
			{
				echo "<input type='radio' name='risk" . $id . "' value='3' CHECKED>";
				echo "<input type='radio' name='risk" . $id . "' value='2'>";
				echo "<input type='radio' name='risk" . $id . "' value='1'>";

			}elseif($risk == '1') //If the user has selected a risk of three check the 1 radio button
			{
				echo "<input type='radio' name='risk" . $id . "' value='3' CHECKED>";
				echo "<input type='radio' name='risk" . $id . "' value='2'>";
				echo "<input type='radio' name='risk" . $id . "' value='1'>";
			}
			
			?> 
			
			</td>
			<td>
				<? echo $owner ?>
			</td>
			
			</tr> <?

	}

}


?>
