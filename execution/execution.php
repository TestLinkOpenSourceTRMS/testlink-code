<?php

////////////////////////////////////////////////////////////////////////////////
//File:     execution.php
//Author:   Chad Rosen
//Purpose:  The page builds the detailed test case execution frame
////////////////////////////////////////////////////////////////////////////////


require_once("../functions/header.php");

  session_start();
  doDBConnect();
  doHeader();

echo "<head>";

?>

<script language='javascript' src='functions/popupHelp.js'></script>
<script language='javascript' src='functions/popTestCase.js'></script>

<script language=javascript>

function showDetail(data)
{

document.write(data);

}

</script>


<?

require_once('../htmlarea/textArea.php');

echo "<LINK REL='stylesheet' TYPE='text/css' HREF='kenny.css'>";

echo "</head>";

if($_GET['edit'] == 'info') //Displayed the first time the user enters the page or if they click the info button
{

		echo "<table class=helptable width=100%>";
		echo "<tr><td class=helptabletitle>Test Case Execution</td></tr></table>";

		echo "<table class=helptable width=100%>";

		echo "<tr><td class=helptablehdr><b>Purpose:</td><td class=helptable>This Page allows the user to execute test cases by either their components or their category levels</td></tr>";
		echo "<tr><td class=helptablehdr><b>Getting Started:</td><td class=helptable>";
		
		echo "<ol><li>Click on a component to see all of its categories and all of its test cases.<li>Clicking on a category will only show that categories test cases.<li>Select a build from the drop down box above.<li>Fill out the test case result and any applicable notes or bugs</td></tr>";
		
		echo "<tr><td class=helptablehdr><b>Note:</td><td class=helptable>If there are no test cases imported into a test plan, only the info icon will be available</td></tr>";
		
		
		echo "</table>";

}

if($_GET['edit'] == 'component') //if the user has selected to view by component
{

	//build the header

	executionHeader($_GET['build']);

	//Start the display of the components
		
	//Here I create a query that will grab every component depending on the project the user picked
		
	$comResult = mysql_query("select component.id, component.name from component,project where project.id = " . $_SESSION['project'] . " and component.projId = project.id and component.id='" . $_GET['com'] . "' order by component.name",$db);
		
	while ($myrowCOM = mysql_fetch_row($comResult)) { //display all the components until we run out

		//Display the each component in a table

		//Here I create a query that will grab every category depending on the component the user picked

		if($_GET['owner']) //check to see if the user sorted the list by owner
		{

			$catResult = mysql_query("select category.id, category.name from component,category where component.id = " . $myrowCOM[0] . " and component.id = category.compid and owner='" . $_SESSION['user'] . "' order by CATorder",$db);

		}else
		{

			$catResult = mysql_query("select category.id, category.name from component,category where component.id = " . $myrowCOM[0] . " and component.id = category.compid order by CATorder",$db);

		}
			
			
		while ($myrowCAT = mysql_fetch_row($catResult)) {  //display all the categories until we run out
				
//////////////////////////////////////////////////////////////Start the display of the Categories	

			//Here I create a query that will grab every testcase depending on the category the user picked

			if($_GET['keyword'] == 'All')
			{

				$TCsql = "select testcase.id, title, summary, steps, exresult, keywords,mgttcid,version from testcase,category where category.id = " . $myrowCAT[0] . " and testcase.catid = category.id order by TCorder";

				$TCResult = mysql_query($TCsql,$db);

			}else //I show them only the keywords they selected
			{

				$TCsql = "select testcase.id, title, summary, steps, exresult, keywords,mgttcid,version from testcase,category where category.id = " . $myrowCAT[0] . " and testcase.catid = category.id and testcase.keywords like '%" . $_GET['keyword'] . "%'  order by TCorder";

				$TCResult = mysql_query($TCsql,$db);

			}
			
			//Display the test case

			//Note.. I have to pass the bugzillaOn variable all over the place because it loses
			//its scope in the functions. This is a result of globals being turned off by default by php

			displayTestCase($TCResult,$bugzillaOn);				
					
				
		}//end category (TP) loop
					
		
	}//end component loop

	echo "</form>";

}

if($_GET['edit'] == 'category') //if the user has selected to view by category
{

		//build the header

		executionHeader($_GET['build']);

		//Here begins the meat of the tool. This next line grabs the category that the user selected from the left pane

		$compName = mysql_query("select component.name from category,component where category.id=" . $_GET['cat'] . " and category.compid=component.id",$db);

		$compNameResult = mysql_fetch_row($compName);


		$catResult = mysql_query("select category.id, category.name from category where category.id = " . $_GET['cat'] . " order by CATorder",$db);
	
		$myrowCAT = mysql_fetch_row($catResult);  //display all the categories until we run out
				
		//Here I create a query that will grab every testcase depending on the category the user picked

		//If the user chose None for the keyword selection I show every keyword

		if($_GET['keyword'] == 'All')
		{

			$TCsql = "select testcase.id, title, summary, steps, exresult, keywords,mgttcid,version from testcase,category where category.id = " . $myrowCAT[0] . " and testcase.catid = category.id order by TCorder";

			$TCResult = mysql_query($TCsql,$db);

		}else //I show them only the keywords they selected
		{

			$TCsql = "select testcase.id, title, summary, steps, exresult, keywords,mgttcid,version from testcase,category where category.id = " . $myrowCAT[0] . " and testcase.catid = category.id and testcase.keywords like '%" . $_GET['keyword'] . "%'  order by TCorder";

			$TCResult = mysql_query($TCsql,$db);

		}

		//display the test case

		//Note.. I have to pass the bugzillaOn variable all over the place because it loses
		//its scope in the functions. This is a result of globals being turned off by default by php

		displayTestCase($TCResult,$bugzillaOn);				

		echo "</form>"; //end the form

}

if($_GET['edit'] == 'testcase')
{

	//build the header

	executionHeader($_GET['build']);

	$COMCATResult = mysql_query("select component.name,category.name from component,category,testcase where testcase.id='" . $_GET['tc'] . "' and category.id=testcase.catid and component.id=category.compid",$db);

	//Grab the result

	$myrowCOMCAT = mysql_fetch_row($COMCATResult);

	//Here begins the meat of the tool. This next line grabs the category that the user selected from the left pane

	//Here I create a query that will grab every testcase depending on the category the user picked

	//If the user chose None for the keyword selection I show every keyword

	if($_GET['keyword'] == 'All')
	{

		$TCResult = mysql_query("select testcase.id, title, summary, steps, exresult, keywords,mgttcid,version from testcase where testcase.id = " . $_GET['tc'] . " and testcase.active='on'",$db);

	}else //I show them only the keywords they selected
	{

		$TCResult = mysql_query("select testcase.id, title, summary, steps, exresult, keywords,mgttcid,version from testcase where testcase.id = " . $_GET['tc'] . " and testcase.active='on' and testcase.keywords like '%" . $_GET['keyword'] . "%'",$db);


	}

	//Display the test case

	//Note.. I have to pass the bugzillaOn variable all over the place because it loses
	//its scope in the functions. This is a result of globals being turned off by default by php

	displayTestCase($TCResult,$bugzillaOn);				

	echo "</form>"; //end the form

}

function executionHeader($build)
{
		
		//This section builds the top table with the date and build selection

		echo "<table width='100%' class=titletable>";

		echo "<td class=titletablehdr width='50%'>Build</td>";
		
		echo "<td class=titletablehdr width='50%'>Click To Record Results</b></td></tr>";


		//Setting up the form that will post to the execution results page

		echo "<form method='post' ACTION='execution/ExecutionResults.php'>";

		echo "<tr><input type='hidden' readonly name='date' value='" . date ("Y-m-d") . "'>";


		echo "<td class=titletable>";
		
		if($build)
		{
			echo $build;
			echo "<input type='hidden' name='build' value='" . $build . "'>";
		}
		else
		{
			$sqlResult = "select build from build where projid=" . $_SESSION['project'];		
			$buildResult = mysql_query($sqlResult);

			echo "<select name='build'>";

			while ($myrowResult = mysql_fetch_row($buildResult)) 
			{
				echo "<option value=" . $myrowResult[0] . ">" . $myrowResult[0] . "</option>";
			}

			echo "</select>";
		}

		echo "</td>";

		
		//echo "<input type='hidden' name='build' value='" . $build . "'></td>";

		//Display all the available builds
				
		echo "<td class=titletable><input type='submit' NAME='submit' value='submit'></td>";
		
		echo "</tr></table><P>";


}

//This next function checks to see if the user has selected anything for the status. If they have I check the appropriate selection box

function radioResult($tcid,$result)
{
	if($result == 'p') //passed
	{
					
		echo "<input type='radio' name='status" . $tcid . "' value='n'>Not Run<br><input type='radio' name='status" . $tcid . "' value='p' CHECKED>Passed<br><input type='radio' name='status" . $tcid . "' value='f'>Failed<br><input type='radio' name='status" . $tcid . "' value='b'>Blocked</td>\n\n";
							

	}elseif($result == 'f') //failed
	{
					
		echo "<input type='radio' name='status" . $tcid . "' value='n'>Not Run<br><input type='radio' name='status" . $tcid . "' value='p'>Passed<br><input type='radio' name='status" . $tcid . "' value='f' CHECKED>Failed<br><input type='radio' name='status" . $tcid . "' value='b'>Blocked</td>\n\n";
								

	}elseif($result == 'b') //blocked
	{
													
		echo "<input type='radio' name='status" . $tcid . "' value='n'>Not Run<br><input type='radio' name='status" . $tcid . "' value='p'>Passed<br><input type='radio' name='status" . $tcid . "' value='f'>Failed<br><input type='radio' name='status" . $tcid . "' value='b' CHECKED>Blocked</td>\n\n";	
								
							
	}else //not run
	{
								
		echo "<input type='radio' name='status" . $tcid . "' value='n' CHECKED>Not Run<br><input type='radio' name='status" . $tcid . "' value='p'>Passed<br><input type='radio' name='status" . $tcid . "' value='f'>Failed<br><input type='radio' name='status" . $tcid . "' value='b'>Blocked</td>\n\n";
								
							
	}
						
}//end function radioDisplay


//This function actually displays the test case

function displayTestCase($resultTC,$bugzillaOn)
{

	while ($myrow = mysql_fetch_row($resultTC)){ //display all the test cases until we run out
					
	//This makes every test case its own table
											
		//I decided to add the ability to show results of the test case if there already were some. This next query looks to see if there are any previous results for the current build

		$sql = "select notes, status from results where tcid='" . $myrow[0] . "' and build='" . $_GET['build'] . "'";
			
		$result = mysql_query($sql); //Run the query
		$num = mysql_num_rows($result); //How many results

		//If the result is empty it leaves the box blank.. This looks weird. Entering a space if it's blank

		if($myrow[1] == "")
		{
			$myrow[1] = "none";
		}

		if($myrow[2] == "")
		{
			$myrow[2] = "none";
		}

		if($myrow[3] == "")
		{
			$myrow[3] = "none";
		}

		if($myrow[4] == "")
		{
			$myrow[4] = "none";

		}

		//Set the version flag to zero.
		//If it is set to 1 that means the test case has been updated
		//If it is set to 2 that means the test case has been deleted

		$versionFlag = 0;
		
		//call the checkVersion function which will set the version flag correcly

		$versionFlag = checkVersion($myrow[6],$myrow[7]);

		//displays the test case header

		TCHeader($myrow[0],$myrowCOMCAT[0],$myrowCOMCAT[1],$versionFlag,$myrow[6],$myrow[1]);

		//displays the test case body
		
		echo "<table width=100% border=0 align=top>";

		echo "<tr valign=top><td width=50%>";

		TCBody($myrow[2],$myrow[3],$myrow[4],$myrow[5]);

		//Begin to display the build related stuff (notes,results,bugs)

		echo "</td><td width=50%>";

		results($myrow[0],$bugzillaOn);

		echo "</td><tr>";
						
		}//end TC loop
			
		
}//end function displayTestCase

//This function takes a test case id and displays all of the test cases bugs

function displayBugs($tcid)
{

	//Create a textarea field to hold the bugs

	echo "<br><input type=text name='bugs" . $tcid . "' size=30 value='";

	//sql code to grab the appropriate bugs for the test case and build
						
	$sqlBugs = "select bug from bugs where tcid='" . $tcid . "' and build='" . $_GET['build'] . "'";
							
							
	$resultBugs = mysql_query($sqlBugs); //Execute the query


	while ($myrowBugs = mysql_fetch_row($resultBugs)) //For each bug that is found
	{
								
		echo $myrowBugs[0] . ","; //Display the bug and a comma after it
						

	}
													
	echo "'>"; //End the text area and show example

}

//This function checks the version of the current test case vs. the version of the management test case
//If the test case has been updated set the flag variable to true. If it has been deleted on the management side
//set the deleted variable to true

function checkVersion($mgttcid,$tcVersion)
{

	//SQL query that grabs the latest version of the currently viewed test case
			
	$sqlVersion = "select version from mgttestcase where mgttestcase.id='" . $mgttcid . "'";
	$versionResult = @mysql_query($sqlVersion);
					
	//Check to see if there is a corresponding mgt result.. There should always be unless the user has decided to delete it
					
	if(mysql_num_rows($versionResult) > 0) //If there is a result
	{		

		//Grab the information
							
		$mgtRow = mysql_fetch_array($versionResult);
							
		//Check to see if the current version is equal to the mgt version		

		if($mgtRow[0] > $tcVersion)
		{

			//Display the flag

			$flag = 1;
								
		}
					
	}else
	{

		//We want to show the x icon so that the user knows the tc has been deleted
							
		$flag = 2;

	}
						
	//Return the result of the version check

	return $flag;

}//end check version function

//This function displays the header of the test case

function TCHeader($tcid,$comName,$catName,$versionFlag,$mgttcid,$tcTitle)
{

	//Figure out what the TC component and category name is

	$sqlComCatName = "select component.name,category.name from component,category,testcase where component.id=category.compid and category.id=testcase.catid and testcase.id=" . $tcid;

	$comCatResult = mysql_query($sqlComCatName); //Run the query
	
	$comCatRow = mysql_fetch_row($comCatResult);

	$comName = $comCatRow[0];
	$catName = $comCatRow[1];

	//Start the display of the header

	echo "<table class=tctable width=100%>";

	echo "<tr><td class=tctablehdr><input type='hidden' name='tcID" . $tcid. "' value='" . $tcid . "'>";
								
	echo "<b>Component:</b> " . $comName . "<br>";

	echo "<b>Category:</b> " . $catName . "<br>";

	//Check the version of the flag
							
	if($versionFlag == 1) //if the test case has been updated
	{

		echo "<img border='0' src='icons/flag.gif'></a>";
							

	}elseif($versionFlag == 2) //If the management side test case has been deleted
	{
		echo "<img border='0' src='icons/x-icon.gif'></a>";
	}

	echo "<b>Test Case </b>";

	if(has_rights("tp_planning"))
	{
		echo "<b onclick=javascript:open_tc('../admin/TC/viewTestCases.php?id=" . $tcid . "');><font color=blue>" . $mgttcid . "</font>: </b>\n";
	} else {
		echo "<b>" . $mgttcid . ": </b>\n";
	}

	echo htmlspecialchars($tcTitle) . "</td></tr>";

	echo "</table>";



}//end function TCHeader

//displays the test case body

function TCBody($summary,$steps,$exresult,$keywords)
{

	echo "<table class=tctable width=100% align='top'>";

	echo "<tr><td class=tctable><b>Summary:</b> " . htmlspecialchars(nl2br($summary)) . "</td></tr>";
								
	echo "<tr><td class=tctable><b>Steps:</b><br>" . nl2br($steps) . "</td></tr>";
								
	echo "<tr><td class=tctable><b>Expected Result:</b><br>" . nl2br($exresult) . "</td></tr>";

	//Chop the trailing comma off of the end of the keywords field

	$keywords = substr("$keywords", 0, -1); 

	echo "<tr><td class=tctable><b>Keywords:</b><br>" . $keywords . "</td></tr>";

	echo "</table>";


}

function results($tcid,$bugzillaOn)
{
	echo "<table class=tctable width=100% align='top'>";

	//This query grabs the results from the build passed in

	$sql = "select notes, status from results where tcid='" . $tcid . "' and build='" . $_GET['build'] . "'";
			
	$result = mysql_query($sql); //Run the query
	$num = mysql_num_rows($result); //How many results

	$resultQuery = mysql_fetch_row($result); //grab the data

	//check the result of the current build and give it a corresponding color

	if($resultQuery[1] == 'p')
	{

		$bgcolor = "green";

	}elseif($resultQuery[1] == 'f')
	{
		$bgcolor = "red";

	}elseif($resultQuery[1] == 'b')
	{
		$bgcolor = "blue";

	}else
	{
		$bgcolor= "black";

	}
	
	//display the row	

	echo "<tr><td bgcolor=" . $bgcolor . ">&nbsp</td></tr>";

	//This query grabs the most recent result

	$sqlRecent = "select build,status,runby,daterun from results where tcid='" . $tcid . "' and status != 'n' order by build desc limit 1";
	
	$resultRecent = mysql_query($sqlRecent);

	$numRecent = mysql_num_rows($resultRecent);

	//If there is a result then display it

	if($numRecent > 0)
	{
		$rowRecent = mysql_fetch_row($resultRecent);


		echo "<tr><td class=tctable><b>Most recent result:</b><br>Run by " . $rowRecent[2] . " on " . $rowRecent[3] . " against Build " . $rowRecent[0] . " (".  $rowRecent[1] . ")</td></tr>";

	}else //else display not run
	{

		echo "<tr><td class=tctable><b>Most recent result:</b><br>Not Run</td></tr>";

	}


	echo "<tr><td class=tctable><b>Notes:</b><br><textarea name='notes" . $tcid . "' cols=35 rows=4>" . $resultQuery[0] . "</textarea></td></tr>";

	//echo "<script language='javascript1.2'>";
	//echo "editor_generate('notes" . $tcid . "',config);";
	//echo "</script>";
						
	echo "<tr><td class=tctable width=50%><b>Result:</b><br>";

		////If we find that a test case has a result record	
	

		if($num == 1) //Found a test case result
		{

			//Calls the radio result function which will return the status radio buttons

			radioResult($tcid,$resultQuery[1]);
							
		}
						
		else //If the test case has no result associated with it then I display nothing in the status and notes fields

		{
										
			echo "<input type='radio' name='status" . $tcid . "' value='n' CHECKED>Not Run<br><input type='radio' name='status" . $tcid . "' value='p'>Passed<br><input type='radio' name='status" . $tcid . "' value='f'>Failed<br><input type='radio' name='status" . $tcid . "' value='b'>Blocked";

		}//end else

		echo "</td></tr>";

							
		//Call the function that displays the test cases bugs.
		
		//Check to see if the user is using a bug system

		if($bugzillaOn == true)
		{
			echo "<tr><td class=tctable><b>Bugs (Enter As CSV ex: 1235,11718,1892):</b>";
		
			displayBugs($tcid); //call the display bugs function

			echo "</td></tr>"; //End the row

		}

		

	echo "</table><br>";


}

?>
