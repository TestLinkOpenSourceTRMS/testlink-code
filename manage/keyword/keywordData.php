<?php

////////////////////////////////////////////////////////////////////////////////
//File:     keywordData.php
//Author:   Chad Rosen
//Purpose:  This page manages keyword data.
////////////////////////////////////////////////////////////////////////////////

require_once("../../functions/header.php");
doSessionStart();
doDBConnect();
doHeader();

//If the user hasn't selected anything show the instructions. This comes up by default the first time the user enters the screen

if(!$_GET['edit'] && !$_POST['edit'])
{

echo "<table width=100% >";

echo "<tr><td align='center' ><h2>Welcome To The Keyword Assignment Page</td></tr>";

echo "</table><table width=100% border=0>";

echo "<tr><td valign='top' bgcolor='#CCCCCC'><b>Purpose:</td>";

echo "<td valign='top' bgcolor='#EEEEEE'><ul><li>The Keyword Assignment page is the place where users can batch assign keywords to the existing component and categories test cases</td>";

echo "<tr><td valign='top' bgcolor='#CCCCCC'><b>To Assign Keywords:</td>";

echo "<td valign='top' bgcolor='#EEEEEE'><ol><li>Select a component, category, or test case on the tree view on the left<li>The top most box that shows up on the right hand side will allow you to assign available keywords to every single test case<li>The selections below allow you to assign cases at a more granular level</li></ol></td></tr>";


echo "</table>";

}

//If the user has chosen to edit a product then show this code. Note that I don't really let the user any of the pieces of the product itself. 

//If the user has chosen to edit a component then show this code

elseif($_GET['edit'] == 'component')
{

	echo "<table width=100% class=userinfotable>";
	echo "<tr><td  bgcolor='#0066CC' align='center'><font color='#FFFFFF' size='4'>Assign Keywords Every Test Case In This Component</td></tr>";
	echo "</table>";
	
	echo "<form method='post' ACTION='manage/keyword/keywordResults.php?type=COM&ID=" . $_GET['data'] . "'>";
	
	echo "<table width=100% class=userinfotable>";

	$sqlCOM = "select id, name from mgtcomponent where id='" . $_GET['data'] . "'";
	$resultCOM = mysql_query($sqlCOM);

	while($rowCOM = mysql_fetch_array($resultCOM)) //Display all Components
	{ 

		echo "<tr><td align='center'><b>" . $rowCOM[1] . "</td><td>";
		
		$sqlKeys = "select id,keyword from keywords where prodid='" . $_GET['prodid'] . "'";
		$resultKeys = mysql_query($sqlKeys);

		$resultSize = mysql_num_rows($resultKeys);
		
		echo "<select name=keywords[] multiple size=" . $resultSize . ">";
		
		while($rowKeys = mysql_fetch_array($resultKeys)) //Display all Components
		{

			echo "<option>" . $rowKeys[1] . "</option>";


		}

		echo "</select>";
		

		
		echo "</td></tr>";
	


	}

	echo "</table>";

	echo "<input type=submit></form>";


}

//If the user has chosen to edit a category then show this code

elseif($_GET['edit'] == 'category')
{

	echo "<table width=100% class=userinfotable>";
	echo "<tr><td  bgcolor='#0066CC' align='center'><font color='#FFFFFF' size='4'>Assign Keywords Every Test Case In This Category</td></tr>";
	echo "</table>";
	
	echo "<form method='post' ACTION='manage/keyword/keywordResults.php?type=CAT&ID=" . $_GET['data'] . "'>";
	
	echo "<table width=100% class=userinfotable>";

	$sqlCOM = "select id, name from mgtcategory where id='" . $_GET['data'] . "'";
	$resultCOM = mysql_query($sqlCOM);

	while($rowCOM = mysql_fetch_array($resultCOM)) //Display all Components
	{ 

		echo "<tr><td align='center'><b>" . $rowCOM[1] . "</td><td>";
		
		$sqlKeys = "select id,keyword from keywords where prodid='" . $_GET['prodid'] . "'";
		$resultKeys = mysql_query($sqlKeys);

		$resultSize = mysql_num_rows($resultKeys);
		
		echo "<select name=keywords[] multiple size=" . $resultSize . ">";
		
		while($rowKeys = mysql_fetch_array($resultKeys)) //Display all Components
		{

			echo "<option>" . $rowKeys[1] . "</option>";


		}

		echo "</select>";
		

		
		echo "</td></tr>";
	


	}

	echo "</table>";

	echo "<input type=submit></form>";

}

//If the user has chosen to edit a testcase then show this code

elseif($_GET['edit'] == 'testcase')
{

	echo "<table width=100% class=userinfotable>";
	echo "<tr><td  bgcolor='#0066CC' align='center'><font color='#FFFFFF' size='4'>Assign Keywords To This Test Case</td></tr>";
	echo "</table>";
	
	echo "<form method='post' ACTION='manage/keyword/keywordResults.php?type=TC&ID=" . $_GET['data'] . "'>";

	echo "<table width=100% class=userinfotable>";

	$sqlCOM = "select id, title from mgttestcase where id='" . $_GET['data'] . "'";
	$resultCOM = mysql_query($sqlCOM);

	while($rowCOM = mysql_fetch_array($resultCOM)) //Display all Components
	{ 

		echo "<tr><td align='center'><b>" . htmlspecialchars($rowCOM[1]) . "</td><td>";
		
		$sqlKeys = "select id,keyword from keywords where prodid='" . $_GET['prodid'] . "'";
		$resultKeys = mysql_query($sqlKeys);

		$resultSize = mysql_num_rows($resultKeys);
		
		echo "<select name=keywords[] multiple size=" . $resultSize . ">";
		
		while($rowKeys = mysql_fetch_array($resultKeys)) //Display all Components
		{

			//This next block of code will search through the testcase and see if any of the products keywords are being used. If they are I highlight them

			//SQL statement to do the grab the test cases keys
			$sqlCompare = "select keywords from mgttestcase where id='" . $rowCOM[0] . "' and keywords like '%" . $rowKeys[1] . "%'";

			//Execute the query
			$resultCompare = mysql_query($sqlCompare);

			//Using the mysql_num_rows function to see how many results are returned
			$compareResult = mysql_num_rows($resultCompare);

			if($compareResult > 0) //If we find a match I highlight the value
			{

				echo "<OPTION VALUE='" . $rowKeys[1] ."' SELECTED>" . $rowKeys[1];

			}else //If there isnt a match just display the value without highlight
			{

				echo "<OPTION VALUE='" . $rowKeys[1] ."'>" . $rowKeys[1];

			}//end else
				
			//echo "<option>" . $rowKeys[1] . "</option>";


		}

		echo "</select>";
		

		
		echo "</td></tr>";
	


	}

	echo "</table>";
	
	echo "<input type=submit></form>";

}


?>
