<?

////////////////////////////////////////////////////////////////////////////////
//File:     exLeftHeader.php
//Author:   Chad Rosen
//Purpose:  This page is involved with the left header????
////////////////////////////////////////////////////////////////////////////////


require_once("../functions/header.php");

  session_start();
  doDBConnect();
  doHeader();

require_once("../functions/stripTree.php"); //include the function that strips the javascript tree

//I need the csv split function

require_once('../functions/csvSplit.php');

//Display the buildHeader only if there are builds associated with the project

//This next line of code just grabs the build number 

$result = mysql_query("select build from build,project where project.id = " . $_SESSION['project'] . " and build.projid = project.id",$db);

$numBuilds = mysql_num_rows($result);

if($numBuilds > 0) //Are there any builds created for the project
{

	buildHeader();

}else //Display the message to the user to go make some builds
{

	echo "<b>There are no builds created for this project.<br><br>Please contact a lead or admin to create one for you.";


}


function buildHeader()
{

	//setting up the top table with the date and build selection

		echo "<table class=mainTable width=100%>";

		echo "<tr><td class=mainMenu><img align=top src=icons/sym_question.gif onclick=javascript:open_popup('../help/ex_left.php');>Filter Test Cases By</td></tr>";

		echo "</table>";

		echo "<table class=navtable width=100%>";

		echo "<tr><td class=navtablehdr width='20%'>Owner</td>";
		
		echo "<td class=navtablehdr width='20%'>Keyword</td>";
		
		echo "<td class=navtablehdr width='20%'>Build</td>";
		
		echo "<td class=navtablehdr width='20%'>Result</td></tr>";

		//Starting the form that will submit to this page

		echo "<form method='post' ACTION='execution/executionFrameLeft.php?page=" . $_GET['page'] . "'>";

		echo "<tr>";
		
		
		//Allow the user to sort by their test cases by owner
		
		echo "<td class=navtable>";
		
		owner();
	
		echo "</td>";

		
		//Display the keyword drowpdown box

		echo "<td class=navtable align='center'>";

		keyword();

		echo "</td>";

		
		//Display the build dropdown box
		
		echo "<td class=navtable>";
		
		build();
		
		echo "</td>";

		//call the results function which displays the results dropdown box
		
		echo "<td class=navtable>";

		results();		
		
		echo "</select>";
		
		
		echo "</td>";

		//print out some dummy cells in the second row

		echo "</tr><tr></table>";
		
		
		echo "<br><table class=navtable width=100%><td class=navtable>";
	
		//check to see if the user has checked the cumulative checkbox.. If yes then check it

		if($_POST['cumulative'] == 'on')
		{
			echo "<input type=checkbox name=cumulative CHECKED>Color Tree By Most Current Result";
	
		}else
		{

			echo "<input type=checkbox name=cumulative>Color Tree By Most Current Result";


		}
		
		
		echo "</td><td class=navtable></td><td class=navtable></td>";

		echo "<td class=navtable>";
				
		echo "<input type='submit' NAME='submitBuild' value='Filter'></td>";

		echo "</tr></table>";

		echo "</form>";


}

function owner()
{
		echo "<select name=owner>";

		echo "<option value=All>All</option>";

		if($_POST['owner'] == $_SESSION['user'])
		{

			echo "<option value=" . $_SESSION['user'] . " SELECTED>" . $_SESSION['user'] . "</option>";
		

		}else
		{


			echo "<option value=" . $_SESSION['user'] . ">" . $_SESSION['user'] . "</option>";
		

		}

		
		
		echo "</select>";


}

function keyword()
{
		//This code here displays the keyword dropdown box. It's fairly interesting code
		//What it does is searches through all of the currently viewed projects test cases and puts together
		//all of the unique keywords from each testcase. It then builds a dropdown box to dispaly them

		//SQL to grab all of the keywords

		$sqlKeyword = "select keywords from project, component, category, testcase where project.id = " .  $_SESSION['project'] . " and project.id = component.projid and component.id = category.compid and category.id = testcase.catid order by keywords";

		//Execute the query

		$resultKeyword = mysql_query($sqlKeyword);

		//Loop through each of the testcases

		while ($myrowKeyword = mysql_fetch_row($resultKeyword)) 
			{

				//This next function calls the csv_split function which can be found at the very bottom of this page
				//The function takes a string of comma seperated values and returns them as an array

				$keyArray = csv_split($myrowKeyword[0]);

				//Take the array that is created from the keywords and add it to another array named result2

				$result2 = array_merge ($result2, $keyArray);


			}//END WHILE

	

		//I need to make sure there are elements in the result 2 array. I was getting an error when I didn't check


		if(count($result2) > 0) 
		{

			//This next function takes the giant array that we created, which is full of duplicate values, and
			//only keeps the unique values. LONG LIVE PHP!

			$result3 = array_unique ($result2);

			//In order to loop through the array I need to change the keys of the array so that they are numerically in order

			$i=0;

			foreach ($result3 as $key)
			{
		
				$result4[$i] = $key;
				$i++;

			}

		}

		//Now I begin the display of the keyword dropdown

	
		echo "<select name='keyword'>"; //Create the select

		echo "<option>All</option>"; //Add a none value to the array in case the user doesn't want to sort

		//For each of the unique values in the keyword array I want to loop through and display them as an option to select

		for ($i = 0; $i < count($result4); $i++)
		{
			
			//For some reason I'm getting a space.. Now I'll ignore any spaces

			if($result4[$i] != "")
			{
				//This next if statement makes the keyword field "sticky" if the user has already selected a keyword and submitted the form

				if($result4[$i] == $_POST['keyword'])
				{
					echo "<option SELECTED>" . $result4[$i] . "</option>";

				}else
				{

					echo "<option>" . $result4[$i] . "</option>";

				}
			}


		}

		echo "</select>";



}

function results()
{

	//This is the new result section. It allows users to sort test cases by their result
		
		echo "<select name=result>";

		if($_POST['result'] == 'all') //if user selected all
		{
			echo "<option value='all' SELECTED>All</option>";
			echo "<option value='n'>Not Run</option>";
			echo "<option value='p'>Passed</option>";
			echo "<option value='f'>Failed</option>";
			echo "<option value='b'>Blocked</option>";

		}elseif($_POST['result'] == 'n')//if user selected not run
		{

			echo "<option value='all'>All</option>";
			echo "<option value='n' SELECTED>Not Run</option>";
			echo "<option value='p'>Passed</option>";
			echo "<option value='f'>Failed</option>";
			echo "<option value='b'>Blocked</option>";
		
		}elseif($_POST['result'] == 'p') //if user selected passed
		{

			echo "<option value='all'>All</option>";
			echo "<option value='n'>Not Run</option>";
			echo "<option value='p' SELECTED>Passed</option>";
			echo "<option value='f'>Failed</option>";
			echo "<option value='b'>Blocked</option>";
		
		}elseif($_POST['result'] == 'f') //if user selected failed
		{

			echo "<option value='all'>All</option>";
			echo "<option value='n'>Not Run</option>";
			echo "<option value='p'>Passed</option>";
			echo "<option value='f' SELECTED>Failed</option>";
			echo "<option value='b'>Blocked</option>";
		
		}elseif($_POST['result'] == 'b') //if user selected blocked
		{

			echo "<option value='all'>All</option>";
			echo "<option value='n'>Not Run</option>";
			echo "<option value='p'>Passed</option>";
			echo "<option value='f'>Failed</option>";
			echo "<option value='b' SELECTED>Blocked</option>";
		
		}else //if user selected hasnt selected anything
		{

			echo "<option value='all'>All</option>";
			echo "<option value='n'>Not Run</option>";
			echo "<option value='p'>Passed</option>";
			echo "<option value='f'>Failed</option>";
			echo "<option value='b'>Blocked</option>";
	
		}


}//end results function

function build()
{

	//Building the dropdown box of builds from the project the user picked on the previous page

	$sql = "select build from build,project where project.id = " . $_SESSION['project'] . " and build.projid = project.id order by build desc";

	$result = mysql_query($sql);
		
	//Code that displays all the available builds in a dropdown box		

	echo "<SELECT NAME='build'>";
		
		while ($myrow = mysql_fetch_row($result)) 
		{
			//This next if statement makes the build selection "sticky" if the user has already selected a build

			if($myrow[0] == $_POST['build'])
			{

				echo "<OPTION VALUE='" . $myrow[0] ."' SELECTED>" . $myrow[0];

			}else
			{
				echo "<OPTION VALUE='" . $myrow[0] ."'>" . $myrow[0];
			}

		}//END WHILE

		echo "</SELECT>";



}//end build function
	
?>
