<?php

////////////////////////////////////////////////////////////////////////////////
//File:     importData.php
//Author:   Chad Rosen
//Purpose:  This page manages the importation of test cases into testlink.
////////////////////////////////////////////////////////////////////////////////

require_once("../functions/header.php");
doSessionStart();
doDBConnect();
doHeader();

?>

<script>
//This function takes a div tag and whether or not you want the checkboxes checked or not
//The function then goes through all of the elements of the div tag that is passed in and
//if they are checkboxes

function box(myDiv, checkBoxStatus){
	var frm;
	var elemType;

	frm = document.getElementById(myDiv).getElementsByTagName('input');
	for(var i = 0; i < frm.length; i++){
		elemType = frm[i].type;		
		
		if(elemType == "checkbox"){
			frm[i].checked = checkBoxStatus;
		}
	}
}
</script>

<?

//If the user hasn't picked anything they will see the info page

if($_GET['edit'] == 'info')
{
	?>
		<table class=helptable width=100%>
			<tr>
				<td class=helptabletitle><h2>Test Case Import</td>
			</tr>
		</table>

		<table class=helptable width=100%>
			<tr>
				<td class=helptablehdr>
					<b>Purpose:
				</td>
				<td class=helptable>
					This Page allows the user (with lead level permissions) to import test cases by either their components or their category levels into a project
				</td>
			</tr>
			<tr>
				<td class=helptablehdr>
					<b>Getting Started:
				</td>
				<td class=helptable>
					<ol>
						<li>Click on a component to see all of its categories and all of its test cases. Clicking on a category will only show that categories test cases. 
						<li>Once you've selected the component/category you wish to draw test cases from use the radio buttons or check boxes to select the test cases you wish to import. 
						<li>When you are done click the import button to import the test cases. Note: The system makes sure that the user does not import the same test case multiple times
					</ol>
				</td>
			</tr>
		</table>
	<?
}

//Display the header table

function displayHeader()
{
	//get the project name from the session
	$projectSQL = "select name from project where id=" . $_SESSION['project'];
	$resultProject = @mysql_query($projectSQL);
	$rowProject = mysql_fetch_row($resultProject);

	?>
	<table width='100%' class=userinfotable>
		<tr>
			<td width="33%" align="center" bgcolor='#CCCCCC'>
				<b>Import Into Project</b>
			</td>
			<td width="33%" align="center" bgcolor='#CCCCCC'>
				<b>Sort List By Keyword
			</td>
			<td width="33%" align="center" bgcolor='#CCCCCC'>
				<b>Import Data
			</td>
	
		<tr>
			<td align="center"><? echo $rowProject[0] ?></td>
			<td align="center">
			<form name='importForm' Method='POST' ACTION='manage/importData.php?product=<? echo $_GET['product'] ?>&id=<? echo $_GET['id'] ?>&edit=<? echo $_GET['edit'] ?>'>
			<?
			//The next block of code displays the keywords

			//SQL query to grab all of the available keywords from the product the user has selected
			$sqlKeys = "select keyword from keywords where prodid='" . $_GET['product'] . "'";

			//Execute the query
			$resultKeys = mysql_query($sqlKeys);

			//Find the amount of keys so that I can make the select box the right size
			
			$keySize = mysql_num_rows($resultKeys);

			if($keySize > 0)
			{
				echo "<select name='keywords' size='" . $keySize . "'>";
				echo "<OPTION VALUE='ALL' SELECTED>All</OPTION>";

			while ($keys = mysql_fetch_row($resultKeys))
			{
				//check to see if the key being looped over is in the test case

				//if it is highlight it

				if ($keys[0] == $_POST['keywords'])
				{
					echo "<OPTION VALUE='" . $keys[0] ."' SELECTED>" . $keys[0];
				}else
				{
					echo "<OPTION VALUE='" . $keys[0] ."'>" . $keys[0];
				}
										
			}//ened while

			echo "</select>";
			echo "<br><input type='submit' name='test' value='Sort'/>";
			echo "</form>";
			}		
		?>	
			</td>
			<td align="center">
				<form name='importForm' Method='POST' ACTION='manage/importDataResult.php'>
				<input type='submit' name='importData' value='Import'>
			</td>
		</tr>
	</table>

	<?
}


//If the user has selected a component

if($_GET['edit'] == 'component')
{
			displayHeader();

			$sqlCOM = "select id, name from mgtcomponent where id='" . $_GET['id'] . "' order by name";
			$resultCOM = @mysql_query($sqlCOM);

			while($rowCOM = mysql_fetch_array($resultCOM)){

				$idCOM = $rowCOM[0];
				$nameCOM = $rowCOM[1];

				echo "<div id=COM>\n\n";
				
				echo "<font size='4' color='#FF0000'>Component: $nameCOM</font></b><br>";
				
				echo "<input type='button' name='" . $nameCOM . "' value='Check' onclick='box(\"COM\", true)'><b>Select All Categories</b><br>";
				
				echo "<input type='button' name='" . $nameCOM . "' onclick='box(\"COM\", false)' value='Uncheck' CHECKED><b>Unselect All Categories</b>";

				$sqlCAT = "select id, name from mgtcategory where compid='" . $idCOM . "' order by CATorder,id";
				$resultCAT = @mysql_query($sqlCAT);
				dispCategories($resultCAT);

			}//End while COM

	echo "</form>";
}


//If the user has selected a category

elseif($_GET['edit'] == 'category')
{

  displayHeader();

  //Query to grab all of the category information based on what was passed in by the user

  $sqlCAT = "select id, name from mgtcategory where id='" . $_GET['id'] . "' order by CATorder,id";
  $resultCAT = @mysql_query($sqlCAT);
  dispCategories($resultCAT);

  echo "</form>";
}

function dispCategories($resultCat) {
  while($rowCAT = mysql_fetch_array($resultCat)) { //loop through all categories

    $idCAT = $rowCAT[0];
    $nameCAT = $rowCAT[1];

    echo "\n\n<div id=CAT_$idCAT>\n\n";

    echo "<hr><font size='4' color='#0000FF'>Category: $nameCAT</font><br>";

    echo "<input type='button' name='$nameCAT' onclick='box(\"CAT_$idCAT\", true)' value='Check'>Select All Test Cases<br>";

    echo "<input type='button' name='$nameCAT' onclick='box(\"CAT_$idCAT\", false)' value='Uncheck'>Unselect All Test Cases<br><br>";	
    
    dispTestCases($idCAT);						
    echo "\n\n</div>\n\n";


  }//End while CAT
  
  echo "</div>\n\n";

  echo "<hr>";
}

function dispTestCases($idCAT) 
{

  $sqlTC = "select id, title, keywords from mgttestcase where catid='" . $idCAT . "' order by TCorder,id";
  $resultTC = @mysql_query($sqlTC);

  while($rowTC = mysql_fetch_array($resultTC))
  { //Display all test cases

    $idTC = $rowTC[0]; //Get the test case ID
    $titleTC = $rowTC[1]; //Get the test case title
	$keywords = $rowTC[2];

	$explodedArray = explode(",", $keywords);

	if(in_array($_POST['keywords'], $explodedArray) || $_POST['keywords'] == 'ALL' || !$_POST['keywords'])
	{

		//Displays the test case name and a checkbox next to it

		$sqlCheck = "select mgttcid from project,component,category,testcase where mgttcid=" . $idTC . " and project.id=component.projid and component.id=category.compid and category.id=testcase.catid and project.id=" . $_SESSION['project'];



		$checkResult = @mysql_query($sqlCheck);
		$checkRow = mysql_num_rows($checkResult);

		echo "<input type='checkbox' name='C" . $idTC . "'><b>" . $idTC . "</b>:" . htmlspecialchars($titleTC);

		echo " (" . $keywords . ")";

		if($checkRow > 0) 
		{  
		  echo "<img src='icons/checkmark.gif'>";
		} 

		echo "<input type='hidden' name='H" . $idTC . "' value='" . $idTC. "'>";
		echo "<br>";
	}

  }//End while TC
}