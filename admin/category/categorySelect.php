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

	$sql = "select id, name from component where id=" . $_GET['com'];

	$result = @mysql_query($sql);

	$rowCOMP = mysql_fetch_row($result);

	?>

	<table class=userinfotable width='100%'>
		<tr>
			<td class=edittablehdr>Component Name</td>
			<td class=edittablehdr>Importance (L/M/H)</td>
			<td  class=edittablehdr>Risk (3/2/1)</td>
			<td class=edittablehdr>Owner</td>
		</tr>
		<tr>
			<td><? echo $rowCOMP[1] ?></td>
			<td>
				<select name="">
					<option>L</option>
					<option SELECTED>M</option>
					<option>H</option>
				</select>
			</td>
			<td>
				<select name="">
					<option>3</option>
					<option SELECTED>2</option>
					<option>1</option>
				</select>
			</td>
			<td>
				<?
			
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

			?>
			</td>
		</tr>
	</table>
</form>

<?

}

if($_GET['edit'] == 'category')
{
	?>
		<form name='categorySelect' method='post' ACTION='admin/category/categoryResults.php'>
		<input type='submit' name='submit' value='Edit Categories'>
	<?

	$sql = "select id, name from category where id=" . $_GET['cat'];

	$result = @mysql_query($sql);

	$rowCOMP = mysql_fetch_row($result);

	?>

	<table class=userinfotable width='100%'>
		<tr>
			<td class=edittablehdr>Component Name</td>
			<td class=edittablehdr>Importance (L/M/H)</td>
			<td  class=edittablehdr>Risk (3/2/1)</td>
			<td class=edittablehdr>Owner</td>
		</tr>
		<tr>
			<td><? echo $rowCOMP[1] ?></td>
			<td>
				<select name="">
					<option>L</option>
					<option SELECTED>M</option>
					<option>H</option>
				</select>
			</td>
			<td>
				<select name="">
					<option>3</option>
					<option SELECTED>2</option>
					<option>1</option>
				</select>
			</td>
			<td>
			<?
			
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

			?>
			</td>
		</tr>
	</table>
</form>

<?

}

if($_GET['edit'] == 'testcase')
{

	?>
		<form name='categorySelect' method='post' ACTION='admin/category/categoryResults.php'>
		<input type='submit' name='submit' value='Edit Test Cases'>
	<?

	$sql = "select id, title, risk, importance, owner from testcase where id=" . $_GET['tc'];

	$result = @mysql_query($sql);

	?>

	<table class=userinfotable width='100%'>
		<tr>
			<td class=edittablehdr>Test Case Name</td>
			<td class=edittablehdr>Importance (L/M/H)</td>
			<td  class=edittablehdr>Risk (3/2/1)</td>
			<td class=edittablehdr>Owner</td>
		</tr>

<?
	
	while($row = mysql_fetch_row($result)){ //loop through all categories

			//Getting and setting the variables from the query

			$id = $row[0];
			$name = $row[1];
			$importance = $row[3];
			$risk = $row[2];
			$owner = $row[4];


			$riskArray = array("1", "2", "3");

			//Print out the owner stuff

			echo "<tr><td><input type='hidden' name='id" . $id . "' value='" . $id . "'>" . $name . "</td>";

			$importanceArray = array("L", "M", "H");
	
			echo "<td><select name='importance'>";

			foreach($importanceArray as $imp)
			{
				if($imp == $importance)
				{
					echo "<option value=$imp SELECTED>$imp</option>";
				}else
				{
					echo "<option value=$imp>$imp</option>";
				}

			}
			
			echo "</select></td>";

			echo "<td><select name='risk'>";

			$riskArray = array("1", "2", "3");

			foreach($riskArray as $riskValue)
			{
				if($riskValue == $risk)
				{
					echo "<option value=$$riskValue SELECTED>$riskValue</option>";
				}else
				{
					echo "<option value=$riskValue>$riskValue</option>";
				}

			}

			echo "</select></td>";

			//Code to give the user a dropdown box to select from
			
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

			echo "</select>";
			
			echo "</td></tr>\n\n";
		
		}//end category display


}


?>
