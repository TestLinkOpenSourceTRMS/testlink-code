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
	echo "<tr><td  bgcolor='#0066CC' align='center'><font color='#FFFFFF' size='4'>Assign Keywords To Every Test Case In This Component</td></tr>";
	echo "</table>";
	
	echo "<form method='post' ACTION='manage/keyword/keywordResults.php?type=COM&ID=" . $_GET['data'] . "'>";
	
	echo "<table width=100% class=userinfotable>";

	$sqlCOM = "select id, name from mgtcomponent where id='" . $_GET['data'] . "'";
	$resultCOM = mysql_query($sqlCOM);

	while($rowCOM = mysql_fetch_array($resultCOM)) //Display all Components
	{ 

		echo "<tr><td align='center' width='50%'><b>" . $rowCOM[1] . "</td><td>";
		
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

		?>

	<tr>
		<td colspan='2'>
			
				<br>
				<INPUT TYPE=CHECKBOX NAME='overwrite' value='overwrite' CHECKED>Overwrite existing keyword values?
				<br>
				<font color="red"><b>Note:</b> If you check this box and submit the form you will <b>overwrite existing keyword values</b>. Not checking the box will add these values to the existing ones. All keywords that have been overwritten cannot be recovered</font>
		</td>
	</tr>

	</table>

	<input type=submit></form>
	
	<?

	}
	
}

//If the user has chosen to edit a category then show this code

elseif($_GET['edit'] == 'category')
{

	echo "<table width=100% class=userinfotable>";
	echo "<tr><td  bgcolor='#0066CC' align='center'><font color='#FFFFFF' size='4'>Assign Keywords To Every Test Case In This Category</td></tr>";
	echo "</table>";
	
	echo "<form method='post' ACTION='manage/keyword/keywordResults.php?type=CAT&ID=" . $_GET['data'] . "'>";
	
	echo "<table width=100% class=userinfotable>";

	$sqlCOM = "select id, name from mgtcategory where id='" . $_GET['data'] . "'";
	$resultCOM = mysql_query($sqlCOM);

	while($rowCOM = mysql_fetch_array($resultCOM)) //Display all Components
	{ 

		echo "<tr><td align='center' width='50%'><b>" . $rowCOM[1] . "</td><td>";
		
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

?>


	<tr>
		<td colspan='2'>
			
				<br>
				<INPUT TYPE=CHECKBOX NAME='overwrite' value='overwrite' CHECKED>Overwrite existing keyword values?
				<br>
				<font color="red"><b>Note:</b> If you check this box and submit the form you will <b>overwrite existing keyword values</b>. Not checking the box will add these values to the existing ones. All keywords that have been overwritten cannot be recovered</font>
		</td>
	</tr>

	</table>

	<input type=submit></form>
	
	<?

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

		echo "<tr><td align='center' width='50%'><b>" . htmlspecialchars($rowCOM[1]) . "</td><td>";
	
		
		//SQL query to grab all of the available keywords from the product the user has selected
		$sqlKeys = "select keyword from keywords where prodid='" . $_GET['prodid'] . "'";

		//Execute the query
		$resultKeys = mysql_query($sqlKeys);

		//Find the amount of keys so that I can make the select box the right size
		
		$keySize = mysql_num_rows($resultKeys);

		//Echo out a html select. Notice how the keywords has a set of brackets after it. This is necesarry for the
		//Multiple to work

		//Get the test cases list of keywords

		$sqlKeywordCSV = "select keywords from mgttestcase where id='" . $rowCOM[0] . "'";

		$resultKeywordCSV = mysql_query($sqlKeywordCSV);

		$keywordCSV = mysql_fetch_row($resultKeywordCSV);

		$keywordArray = explode(",", $keywordCSV[0]);

		echo "<select name='keywords[]' size='" . $keySize . "' MULTIPLE>";


		while ($keys = mysql_fetch_row($resultKeys))
		{
			//check to see if the key being looped over is in the test case

			//if it is highlight it

			if (in_array($keys[0], $keywordArray)) 
			{

				echo "<OPTION VALUE='" . $keys[0] ."' SELECTED>" . $keys[0];
			}else
			{
				echo "<OPTION VALUE='" . $keys[0] ."'>" . $keys[0];
			}
									
		}//ened while

		echo "</select>";
		
		echo "</td></tr>";
	


	}

?>

	<tr>
		<td colspan='2'>
			
				<br>
				<INPUT TYPE=CHECKBOX NAME='overwrite' value='overwrite' CHECKED>Overwrite existing keyword values?
				<br>
				<font color="red"><b>Note:</b> If you check this box and submit the form you will <b>overwrite existing keyword values</b>. Not checking the box will add these values to the existing ones. All keywords that have been overwritten cannot be recovered</font>
		</td>
	</tr>

	</table>

	<input type=submit></form>
	
	<?

}


?>
