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
		<tr><td class=helptabletitle>Test Case Execution By Platform</td></tr></table>
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

			displayTestCase($TCResult);				
						
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

		displayTestCase($TCResult);				
	}

}

if($edit == 'testcase' && $build)
{

	//Here I create a query that will grab every testcase depending on the category the user picked

	//If the user chose None for the keyword selection I show every keyword

	$TCResult = mysql_query("select testcase.id, title, summary, steps, exresult, keywords,mgttcid,version,risk, importance from testcase where testcase.id = " . $data . " and testcase.active='on' order by mgttcid",$db);

	//Display the test case

	//Note.. I have to pass the bugzillaOn variable all over the place because it loses
	//its scope in the functions. This is a result of globals being turned off by default by php

	displayTestCase($TCResult);				
}

echo "</form>"; //end the form

function executionHeaderWithBuild($build)
{
	global $edit, $data;
	//This section builds the top table with the date and build selection
		
	?>
		
	<table width='50%' class="titletable" align='center'>
	<form method='post' ACTION='platform/executionResults.php'>
		
	<td class="titletablehdr" width='33%'>Click to record results against build: <? echo $build ?></b> 
	: (<a href='platform/executionData.php?edit=<? echo $edit ?>&data=<? echo $data ?>'>back</a>)
		
	</td></tr>

	<?
	//Setting up the form that will post to the execution results page

	echo "<tr><input type='hidden' readonly name='date' value='" . date ("Y-m-d") . "'>";
		
	echo "<input type='hidden' name='build' value='" . $build . "'>";
		
	echo "</td>";


	//Display all the available builds
				
	echo "<td class=titletable><input type='submit' NAME='submit' value='submit'></td>";

	$arrayKeys = array_keys($_GET);

	$i = 0; //start a counter

	foreach ($_GET as $key)
	{	
		if(($i > 2) && ($key != 'submit')) 
		{
			echo  "<input type='hidden' name='" . $arrayKeys[$i] . "' value='" . $key . "'/>";
		}
		$i++;
	}

	echo "<input type='hidden' name='break' value='break'/>";
		
	echo "</tr></table><P>";
}

function executionHeaderWithoutBuild()
{
	global $edit, $data;
	//This section builds the top table with the date and build selection

	echo "<table width='50%' class=titletable align='center'>";
	echo "<form method='get' ACTION='platform/executionData.php'>";
	echo "<tr><td class=titletablehdr colspan='2'>Select Execution Parameters:</td></tr>";
		
	//Setting up the form that will post to the execution results page
		
	echo "<input type='hidden' name='edit' value='$edit'>";
	echo "<input type='hidden' name='data' value='$data'>";
		
	$sqlResult = "select build from build where projid=" . $_SESSION['project'] . " order by build desc";		
	$buildResult = mysql_query($sqlResult);
		
	echo "<td class=titletable align='left' width='33%'>Build:</td><td >";
	?>
		<select name='build' onchange="Javascript:submit()">
	<?
		
	while ($myrowResult = mysql_fetch_row($buildResult)) 
	{
		echo "<option value=" . $myrowResult[0] . ">" . $myrowResult[0] . "</option>";
	}

	?>
	</select></td></tr>

	<?

	$sqlContainer = "select id, name from platformcontainer where projid=" . $_SESSION['project'];
	$containerResult = mysql_query($sqlContainer);
			
	while ($myrowContainer = mysql_fetch_row($containerResult)) 
	{
		$sqlPlatform = "select id, name from platform where containerId=" . $myrowContainer[0];
		$platformResult = mysql_query($sqlPlatform);

		if(mysql_num_rows($platformResult) != 0)
		{
			echo "<tr><td class=titletable align='left' width='33%'>" . $myrowContainer[1] . ":</td><td align='left'>";
				
			//echo "<input type='hidden' name='" . $myrowContainer[0] . "' value='" . $myrowContainer[1] . "'/>";
			echo "<select name='" . $myrowContainer[1] . "'>";
			
			while ($myrowPlatform = mysql_fetch_row($platformResult)) 
			{
				echo "<option value=" . $myrowPlatform[0] . ">" . $myrowPlatform[1] . "</option>";
			}				
		}
	}

	?>
	</select></td></tr>

	<tr><td colspan='2' align='center'>
		
	<?
	//dont show the submit button if there are no containers
	if(mysql_num_rows($containerResult) != 0)
	{
	?>
		
	<input type='submit' NAME='submit' value='submit'></td>
		
	<?
	}
	else
	{
		echo "If you wish to execute test cases by platform you need to <a href='platform/manageFrameSet.php' target='_parent'>add platforms</a>";
	}
	?>
	</tr></table><P>
<?

}

//This function actually displays the test case

function displayTestCase($resultTC)
{
	global $build;

	while ($myrow = mysql_fetch_row($resultTC))
	{ 
		//display all the test cases until we run out
		$id			= $myrow[0];
		$title		= $myrow[1];
		$summary	= $myrow[2];
		$steps		= $myrow[3];
		$exresult	= $myrow[4];
		$keywords	= $myrow[5];
		$mgttcid	= $myrow[6];
		$version	= $myrow[7];
		$risk		= $myrow[8];
		$imp		= $myrow[9];
			
		//Set the version flag to zero.
		//If it is set to 1 that means the test case has been updated
		//If it is set to 2 that means the test case has been deleted

		$versionFlag = 0;
		
		//call the checkVersion function which will set the version flag correcly

		$versionFlag = checkVersion($mgttcid,$id);

		//displays the test case header

		TCHeader($id,$myrowCOMCAT[0],$myrowCOMCAT[1],$versionFlag,$mgttcid,$title);

		platformResults($id);

		//displays the test case body
		
		echo "<table width=100% border=0 align=top>";

		echo "<tr valign=top><td width=50%>";

		TCBody($summary,$steps,$exresult,$keywords, $risk . $imp);

		//Begin to display the build related stuff (notes,results,bugs)

		echo "</td><td width=50%>";

		results($id);

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

function platformResults($tcid)
{
	global $build;
	$i = 3;

	$sqlPlatformTC	= "select dateRun, result, runBy, platformList from platformresults where tcid=" . $tcid . " and buildId=" . $build;

	$platformTCResult	= mysql_query($sqlPlatformTC); //Run the query	
	
	$numRows = mysql_num_rows($platformTCResult); //How many results

	if($numRows > 0)
	{
	
	?>
		<table width="100%" class="tctable">
			<tr><td class="tctable">
			<b>Previous Platform Results for This Test Case</b><br>
			<?
			
			while($platformTCRow = mysql_fetch_array($platformTCResult)) //Display all Categories
			{
				echo $platformTCRow[0] . " " . $platformTCRow[1] . " " . $platformTCRow[2] . " ";
						
				$platformNameArray = explode(",",$platformTCRow[3]);

				foreach ($platformNameArray as $platformId)
				{	
					$sqlPlatformName = "select name from platform where id=" . $platformId;
					$platformNameRow = mysql_fetch_row(mysql_query($sqlPlatformName)); //Run the query
					
					echo $platformNameRow[0] . " ";
				}
				
				echo "<br>";
			}
			?>
			
			</td></tr>
		</table>
	<?
	}
}


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
	?>
	
	<table class=tctable width=100% align='top'>

	<tr><td class=tctable><b>Summary:</b><? echo htmlspecialchars(nl2br($summary)) ?></td></tr>
								
	<tr><td class=tctable><b>Steps:</b><br><? echo nl2br($steps) ?></td></tr>
								
	<tr><td class=tctable><b>Expected Result:</b><br><? echo nl2br($exresult) ?></td></tr>

	<tr><td class=tctable><b>Keywords:</b><br><? echo $keywords ?></td></tr>

	<tr><td class=tctable><b>Priority (Risk/Importance):</b><br>

	<?

	$sqlGetPri = "select priority from priority where projid=" . $_SESSION['project'] . " and riskimp='" . strtoupper($riskImportance) . "'";

	$priResult = mysql_fetch_row(mysql_query($sqlGetPri)); //Run the query

	echo strtoupper($priResult[0]) . " (" . strtoupper($riskImportance) . ")"; 
	
	?>

	</table>

	<?

}

function results($tcid)
{
	global $bugzillaOn,$build;

	?>
		<table class=tctable width=100% align='top'>

		<tr><td class="tctable"><b>Platform:</b><br>
	<?

		$arrayKeys = array_keys($_GET);

		$i = 0; //start a counter

		//It is necessary to turn the $_GET map into an array

		foreach ($_GET as $key)
		{	
			if(($i > 2) && ($key != 'submit')) 
			{
				$platformSql = "select name from platform where id=" . $key;

				$platform = mysql_fetch_row(mysql_query($platformSql)); //Run the query

				echo  "<BLOCKQUOTE><b>" . $arrayKeys[$i] . "</b>: " . $platform[0] . "<br></BLOCKQUOTE>";

				//create the platform array
				$platformArray[] = $key;

			}
			$i++;
		}

		//sort platforms
		sort($platformArray);
		reset($platformArray);

	?>


	</td></tr>

	<?

	if(count($platformArray > 1))
	{
		$platformCSV = implode(",", $platformArray);
	}else
	{
		$platformCSV = $platformArray[0];
	}

	//This query grabs the results from the build/platform/tcid

	$sqlPlatformStatus = "select notes,result from platformresults where tcid='" . $tcid . "' and buildId='" . $build. "' and platformList='" . $platformCSV . "'";

	//echo $sqlPlatformStatus;

	$resultPlatformStatus = mysql_query($sqlPlatformStatus); //Run the query
	$num = mysql_num_rows($resultPlatformStatus); //How many results

	$myrowPlatformStatus = mysql_fetch_row($resultPlatformStatus); //grab the data

	//check the result of the current build and give it a corresponding color

	echo "<tr><td class=tctable><b>Notes:</b><br><textarea name='notes" . $tcid . "' cols=35 rows=2>" . $myrowPlatformStatus[0] . "</textarea></td></tr>";
						
	echo "<tr><td class=tctable width=50%><b>Result:</b><br>";

	////If we find that a test case has a result record	
	
	if($num == 1) //Found a test case result
	{
		//Calls the radio result function which will return the status radio buttons
		radioResult($tcid,$myrowPlatformStatus[1]);						
	}
						
	else //If the test case has no result associated with it then I display nothing in the status and notes fields
	{			
		echo "<input type='radio' name='status" . $tcid . "' value='n' CHECKED>Not Run";
		echo " <input type='radio' name='status" . $tcid . "' value='p'>Passed";
		echo " <input type='radio' name='status" . $tcid . "' value='f'>Failed";
		echo " <input type='radio' name='status" . $tcid . "' value='b'>Blocked";
	}//end else

	echo "</td></tr>";

	?>
	</table><br>

	<?

}

//This next function checks to see if the user has selected anything for the status. If they have I check the appropriate selection box

function radioResult($tcid,$result)
{
	if($result == 'p') //passed
	{
		echo "<input type='radio' name='status" . $tcid . "' value='n'>Not Run";
		echo " <input type='radio' name='status" . $tcid . "' value='p' CHECKED>Passed";
		echo " <input type='radio' name='status" . $tcid . "' value='f'>Failed";
		echo " <input type='radio' name='status" . $tcid . "' value='b'>Blocked";
						
	}elseif($result == 'f') //failed
	{			
		echo "<input type='radio' name='status" . $tcid . "' value='n' >Not Run";
		echo " <input type='radio' name='status" . $tcid . "' value='p'>Passed";
		echo " <input type='radio' name='status" . $tcid . "' value='f' CHECKED>Failed";
		echo " <input type='radio' name='status" . $tcid . "' value='b'>Blocked";

	}elseif($result == 'b') //blocked
	{
		echo "<input type='radio' name='status" . $tcid . "' value='n' CHECKED>Not Run";
		echo " <input type='radio' name='status" . $tcid . "' value='p'>Passed";
		echo " <input type='radio' name='status" . $tcid . "' value='f'>Failed";
		echo " <input type='radio' name='status" . $tcid . "' value='b' CHECKED>Blocked";				
	}else //not run
	{
		echo "<input type='radio' name='status" . $tcid . "' value='n' CHECKED>Not Run";
		echo " <input type='radio' name='status" . $tcid . "' value='p'>Passed";
		echo " <input type='radio' name='status" . $tcid . "' value='f'>Failed";
		echo " <input type='radio' name='status" . $tcid . "' value='b'>Blocked";				
	}
						
}//end function radioDisplay

?>
