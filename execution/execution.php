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

echo "</head>";

//The build that the user is viewing
$build = $_GET['build'];

//the level that the user chose (component,category,testcase)
$edit = $_GET['edit'];

//the level's id (ex: testcase id 144677)
$data = $_GET['data'];

if($build && $edit != 'info')
{
	executionHeaderWithBuild($build);

}
else if(!$build && $edit != 'info')
{
	executionHeaderWithoutBuild();
	//no build yet
}

if($edit == 'info') //Displayed the first time the user enters the page or if they click the info button
{
		?>

		<table class=helptable width=100%>
		<tr><td class=helptabletitle>Test Case Execution</td></tr></table>
		<table class=helptable width=100%>
		<tr>
			<td class=helptablehdr><b>Purpose:</td>
			<td class=helptable>This Page allows the user to execute test cases by either their components or their category levels
			</td>
		</tr>
		<tr>
			<td class=helptablehdr><b>Getting Started:</td>
			<td class=helptable>
				<ol>
					<li>
						Click on a component to see all of its categories and all of its test cases.
					<li>
						Clicking on a category will only show that categories test cases.
					<li>
						Select a build from the drop down box above.
					<li>
						Fill out the test case result and any applicable notes or bugs
				</ol>
			</td>
		</tr>
		
		<tr>
			<td class=helptablehdr><b>Note:</td>
			<td class=helptable>
				If there are no test cases imported into a test plan, only the info icon will be available
			</td>
		</tr>
		</table>

		<?
}

if($edit == 'component' && $build) //if the user has selected to view by component
{

	//Start the display of the components
		
	//Here I create a query that will grab every component depending on the project the user picked
		
	$comResult = mysql_query("select component.id, component.name from component,project where project.id = " . $_SESSION['project'] . " and component.projId = project.id and component.id='" . $data . "' order by component.name",$db);
		
	while ($myrowCOM = mysql_fetch_row($comResult)) { //display all the components until we run out

		//Display the each component in a table

		$catResult = mysql_query("select category.id, category.name from component,category where component.id = " . $myrowCOM[0] . " and component.id = category.compid order by CATorder",$db);	
			
		while ($myrowCAT = mysql_fetch_row($catResult)) 
		{  
			//display all the categories until we run out
				
			//Start the display of the Categories	

			$TCsql = "select testcase.id, title, summary, steps, exresult, keywords,mgttcid,version, testcase.risk, testcase.importance from testcase,category where category.id = " . $myrowCAT[0] . " and testcase.catid = category.id order by TCorder";

			$TCResult = mysql_query($TCsql,$db);
			
			//Display the test case

			//Note.. I have to pass the bugzillaOn variable all over the place because it loses
			//its scope in the functions. This is a result of globals being turned off by default by php

			displayTestCase($TCResult,$bugzillaOn);				
						
		}//end category (TP) loop
					
		
	}//end component loop

}

if($edit == 'category' && $build) //if the user has selected to view by category
{

		//Here begins the meat of the tool. This next line grabs the category that the user selected from the left pane

		$catResult = mysql_query("select category.id, category.name from category where category.id = " . $data . " order by CATorder",$db);
	
		//$myrowCAT = mysql_fetch_row($catResult);  //display all the categories until we run out
		
		while ($myrowCAT = mysql_fetch_row($catResult)) 
		{	

			//Here I create a query that will grab every testcase depending on the category the user picked

			//If the user chose None for the keyword selection I show every keyword

			$TCsql = "select testcase.id, title, summary, steps, exresult, keywords, mgttcid, version, testcase.risk, testcase.importance  from testcase,category where category.id = " . $myrowCAT[0] . " and testcase.catid = category.id order by TCorder";

			$TCResult = mysql_query($TCsql,$db);

			//display the test case

			//Note.. I have to pass the bugzillaOn variable all over the place because it loses
			//its scope in the functions. This is a result of globals being turned off by default by php

			displayTestCase($TCResult,$bugzillaOn);				
		}

}

if($edit == 'testcase' && $build)
{

	//Here I create a query that will grab every testcase depending on the category the user picked

	//If the user chose None for the keyword selection I show every keyword

	$TCResult = mysql_query("select testcase.id, title, summary, steps, exresult, keywords,mgttcid,version,risk, importance from testcase where testcase.id = " . $data . " and testcase.active='on'",$db);

	//Display the test case

	//Note.. I have to pass the bugzillaOn variable all over the place because it loses
	//its scope in the functions. This is a result of globals being turned off by default by php

	displayTestCase($TCResult,$bugzillaOn);				
}

echo "</form>"; //end the form

function executionHeaderWithBuild($build)
{
		global $edit, $data;
		//This section builds the top table with the date and build selection
		
		?>
		
		<table width='50%' class=titletable align='center'>
		<form method='post' ACTION='execution/ExecutionResults.php'>
		
		<td class=titletablehdr width='33%'>Click to record results against build: <? echo $build ?></b> 
		: (<a href='execution/execution.php?edit=<? echo $edit ?>&data=<? echo $data ?>'>back</a>)
		
		</td></tr>

		<?
		//Setting up the form that will post to the execution results page

		echo "<tr><input type='hidden' readonly name='date' value='" . date ("Y-m-d") . "'>";
		
		echo "<input type='hidden' name='build' value='" . $build . "'>";		
		echo "</td>";


		//Display all the available builds
				
		echo "<td class=titletable><input type='submit' NAME='submit' value='submit'></td>";
		
		echo "</tr></table><P>";
}

function executionHeaderWithoutBuild()
{
	global $edit, $data;
		//This section builds the top table with the date and build selection

		echo "<table width='50%' class=titletable align='center'>";
		echo "<form method='get' ACTION='execution/execution.php'>";
		echo "<tr><td class=titletablehdr colspan='2'>Select Execution Parameters:</td></tr>";
		
		//Setting up the form that will post to the execution results page
		
		echo "<input type='hidden' name='edit' value='$edit'>";
		echo "<input type='hidden' name='data' value='$data'>";
		
		$sqlResult = "select build from build where projid=" . $_SESSION['project'] . " order by build desc";		
		$buildResult = mysql_query($sqlResult);
		
		echo "<td class=titletable align='left' width='33%'>Build:</td><td class=titletable>";
		?>
			<select name='build' onchange="Javascript:submit()">
		<?
		
		while ($myrowResult = mysql_fetch_row($buildResult)) 
		{
			echo "<option value=" . $myrowResult[0] . ">" . $myrowResult[0] . "</option>";
		}

		?>
		</select></td></tr>

		<tr>
			<td class=titletable align='left'>Keyword:</td>
			<td class=titletable align='left'>
				<select name='keyword'>
				<?
					//get a list of all the keywords
					$sqlKeywords = "select id, keyword from keywords";
					$resultKeyword = mysql_query($sqlKeywords);

					while ($myrowKeyword = mysql_fetch_row($resultKeyword)) 
					{
						echo "<option value=" . $myrowKeyword[0] . ">" . $myrowKeyword[1] . "</option>";
					}
				?>
				</select>
			</td>
		</tr>
		<tr><td class=titletable>Owner:</td>
			<td class=titletable>
			<select name='owner'>
			<?
				
				//get all of the users that have rights to the project so that the test cases can
				//be sorted by them

				$sqlOwner = "select user.id, user.login from user,projrights where projrights.projid=" . $_SESSION['project'];

				
				$resultOwner = mysql_query($sqlOwner);

				while ($myrowOwner = mysql_fetch_row($resultOwner)) 
				{
					echo "<option value=" . $myrowOwner[0] . ">" . $myrowOwner[1] . "</option>";
				}

			?>
			</select>


			</td>
		</tr>
		
		<tr><td class=titletable>Priority:</td><td class=titletable>
		
		<select name='priority'>
		<option value='L1'>L1</option>
		<option value='L1'>L2</option>
		<option value='L1'>L3</option>
		<option value='L1'>M1</option>
		<option value='L1'>M2</option>
		<option value='L1'>M3</option>
		<option value='L1'>H1</option>
		<option value='L1'>H2</option>
		<option value='L1'>H3</option>
		</select>
		<?
		echo "</td></tr>";
				
		//Display all the available builds
				
		echo "<tr><td colspan='2' align='center'><input type='submit' NAME='submit' value='submit'></td>";
		
		echo "</tr></table><P>";


}

//This next function checks to see if the user has selected anything for the status. If they have I check the appropriate selection box

function radioResult($tcid,$result)
{
	if($result == 'p') //passed
	{
					
		echo "<input type='radio' name='status" . $tcid . "' value='n'>Not Run<br><input type='radio' name='status" . $tcid . "' value='p' CHECKED>Passed<br><input type='radio' name='status" . $tcid . "' value='f'>Failed<br><input type='radio' name='status" . $tcid . "' value='b'>Blocked\n\n";
							

	}elseif($result == 'f') //failed
	{
					
		echo "<input type='radio' name='status" . $tcid . "' value='n'>Not Run<br><input type='radio' name='status" . $tcid . "' value='p'>Passed<br><input type='radio' name='status" . $tcid . "' value='f' CHECKED>Failed<br><input type='radio' name='status" . $tcid . "' value='b'>Blocked\n\n";
								

	}elseif($result == 'b') //blocked
	{
													
		echo "<input type='radio' name='status" . $tcid . "' value='n'>Not Run<br><input type='radio' name='status" . $tcid . "' value='p'>Passed<br><input type='radio' name='status" . $tcid . "' value='f'>Failed<br><input type='radio' name='status" . $tcid . "' value='b' CHECKED>Blocked\n\n";	
								
							
	}else //not run
	{
								
		echo "<input type='radio' name='status" . $tcid . "' value='n' CHECKED>Not Run<br><input type='radio' name='status" . $tcid . "' value='p'>Passed<br><input type='radio' name='status" . $tcid . "' value='f'>Failed<br><input type='radio' name='status" . $tcid . "' value='b'>Blocked\n\n";
								
							
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

		TCBody($myrow[2],$myrow[3],$myrow[4],$myrow[5], $myrow[9] . $myrow[8]);

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

function TCBody($summary,$steps,$exresult,$keywords, $riskImportance)
{

	echo "<table class=tctable width=100% align='top'>";

	echo "<tr><td class=tctable><b>Summary:</b> " . htmlspecialchars(nl2br($summary)) . "</td></tr>";
								
	echo "<tr><td class=tctable><b>Steps:</b><br>" . nl2br($steps) . "</td></tr>";
								
	echo "<tr><td class=tctable><b>Expected Result:</b><br>" . nl2br($exresult) . "</td></tr>";

	//Chop the trailing comma off of the end of the keywords field

	echo "<tr><td class=tctable><b>Keywords:</b><br>" . $keywords . "</td></tr>";

	echo "<tr><td class=tctable><b>Priority and Risk/Importance:</b><br>";

	echo $riskImportance;

	echo "</table>";


}

function results($tcid,$bugzillaOn)
{
	echo "<table class=tctable width=100% align='top'>";

	//This query grabs the results from the build passed in

	$sql = "select notes,status,build,runby,daterun,status from results where tcid='" . $tcid . "' and build='" . $_GET['build'] . "'";

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

	if($_GET['build'])
	{
		echo "<tr><td bgcolor=" . $bgcolor . ">&nbsp</td></tr>";

		echo "<tr><td class=tctable><b>Build Result:</b><br>Run by " . $resultQuery[3] . " on " . $resultQuery[4] . " against Build " . $resultQuery[2] . " (".  $resultQuery[5] . ")</td></tr>";

	}
	else
	{
		//This query grabs the most recent result

		$sqlRecent = "select build,status,runby,daterun from results where tcid='" . $tcid . "' and status != 'n' order by build desc limit 1";
		
		$resultRecent = mysql_query($sqlRecent);

		$numRecent = mysql_num_rows($resultRecent);

		//If there is a result then display it

		if($numRecent > 0)
		{
			$rowRecent = mysql_fetch_row($resultRecent);


			echo "<tr><td class=tctable><b>Most recent result:</b><br>";
			
			echo "<a href='execution/executionResultHistory.php?tc=" . $tcid . "' target='_blank'>";

			echo "Run by " . $rowRecent[2] . " on " . $rowRecent[3] . " against Build " . $rowRecent[0] . " (".  $rowRecent[1] . ")";

			echo "</a></td></tr>";
			
		}else //else display not run
		{

			echo "<tr><td class=tctable><b>Most recent result:</b><br>Not Run</td></tr>";

		}

	}

	echo "<tr><td class=tctable><b>Notes:</b><br><textarea name='notes" . $tcid . "' cols=35 rows=4>" . $resultQuery[0] . "</textarea></td></tr>";
						
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
