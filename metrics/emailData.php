<?

////////////////////////////////////////////////////////////////////////////////
//File:     emailData.php
//Author:   Chad Rosen
//Purpose:  This page allows users to enter their email data for a report.
////////////////////////////////////////////////////////////////////////////////

require_once("../functions/header.php");

  session_start();
  doDBConnect();
  doHeader();

require_once("priority.php"); //add the priority file so that I can show the entire project status

//Gather all of the current projects components for the dropdown box

$sqlCom = "select component.id,component.name from component, project where component.projid='" . $_SESSION['project'] . "' and project.id='" . $_SESSION['project'] . "'";

$result = mysql_query($sqlCom,$db);

$numCom = mysql_num_rows($result); //needed to check if there are any components later

//loop through all of the components and build the options for the select box			

while ($myrow = mysql_fetch_row($result))
{

$option .= "<option value='" . $myrow[0] . "'>" . $myrow[1] . "</option>";

}

//Now create the dropdown box with the build info

$sqlBuild = "select build from build where projid='" . $_SESSION['project'] . "'";

$resultBuild = mysql_query($sqlBuild,$db);

$numBuilds = mysql_num_rows($resultBuild); //needed to check if there are any existing

//loop through all of the builds and build the options for the select box

while($myrowBuild = mysql_fetch_row($resultBuild))
{

$optionBuild .= "<option value='" . $myrowBuild[0] . "'>" . $myrowBuild[0] . "</option>";

}

?>

<table border=1 width=100%  class=titletable>
<form method='post' ACTION='metrics/emailData.php'>

<tr><td class=userinfotable>To:</td class=userinfotable><td><input name='to' type=text size=75></td></tr>
<tr><td class=userinfotable>Subject:</td><td><input name='subject' type=text size=75></td></tr>
<tr><td class=userinfotable>Body:</td><td><textarea name='body' cols=60 rows=10></textarea>



<tr><td class=userinfotable>Status:</td><td>

<input type=radio name=status value=projAll CHECKED>Project Status Across All Builds<br>
<input type=radio name=status value=comAll><select name=comSelectAll><? echo $option; ?></select> Status Across All Builds

<br><input type=radio name=status value=projBuild>Project Status For Build</option> <select name=buildProj><? echo $optionBuild; ?></select><br>

<input type=radio name=status value=comBuild><select name=comSelectBuild><? echo $option; ?></select> Status For Build <select name=buildCom><? echo $optionBuild; ?></select>

</td></tr>

<?

//I don't want the user to be able to send mail if there is no project status or components imported

if($numCom > 0 && $numBuilds > 0)
{

	echo "<input type=submit name=submit value='Send Mail'>";

}else
{

	echo "You must create builds or import data to mail metrics info";

}

?>

</form>

</table>

<?

if($_POST['submit'])
{

	if($_POST['to'] == "") //check to see if the to field was blank
	{
		echo "I'm sorry but email cannot be sent with a blank to field. Please enter a valid address.";

		exit; //if it was then exit the program


	}

//print_r($_POST);

$msgBody = $_POST['body'] . "\n\n";

if($_POST['status'] == 'projAll') //if the user has chosen to sent the entire project priority info
{
	//grab all of the priority info and stuff it into the message body

	$msgBody .= displayPriority("A",$totalA,$Astatus,$passA,$failA,$blockedA,$notRunTCsA,$PercentageCompleteA,$MA);
	$msgBody .= displayPriority("B",$totalB,$Bstatus,$passB,$failB,$blockedB,$notRunTCsB,$PercentageCompleteB,$MB);
	$msgBody .= displayPriority("C",$totalC,$Cstatus,$passC,$failC,$blockedC,$notRunTCsC,$PercentageCompleteC,$MC);
	

}elseif($_POST['status'] == 'comAll') //user has chosen to send a specific component status across all builds
{
	$msgBody .= totalComponent($_POST['comSelectAll']);


}elseif($_POST['status'] == 'projBuild') //user has chosen to send the status of a particular build
{
	$msgBody .= buildStatus($_POST['buildProj']);


}else //user has chosen to send the status of a particular component for a build
{

	$msgBody .= componentBuild($_POST['comSelectBuild'], $_POST['buildCom']);


}

//echo "<br><br>" . $msgBody;

mail($_POST['to'], $_POST['subject'], $msgBody, "From:TestLink@good.com") or die("Error sending email");

echo "Your mail has been sent";


}

//this function takes all of the priority info and puts it in a variable.. I have to do this 3 times so it's easier this way

function displayPriority($type,$total,$status,$pass,$fail,$blocked,$notRun,$PerCom,$MileGoal)
{

	$msgBody .= "Priority " . $type . " Test Cases\n\n";
	$msgBody .= "Total: " . $total . "\n";
	$msgBody .= "Passing: " . $pass . "\n";
	$msgBody .= "Failing: " . $fail . "\n";
	$msgBody .= "Blocked: " . $blocked . "\n";
	$msgBody .= "Not Run: " . $notRun . "\n";
	$msgBody .= "Percentage Complete: " . $perCom . "\n";
	$msgBody .= "Percentage Complete Against Current Milestone: " . $MileGoal . "\n";
	$msgBody .= "Status Against Current Milestone: " . $status . "\n\n";


	return $msgBody;


}

function totalComponent($comID)
{

	//Code to grab the entire amount of test cases per project
	
	$sql = "select count(testcase.id) from project,component,category,testcase where project.id = '" . $_SESSION['project'] . "' and component.id='" . $comID . "' and project.id = component.projid and component.id = category.compid and category.id = testcase.catid";

	$totalTCResult = mysql_query($sql);

	$totalTCs = mysql_fetch_row($totalTCResult);

	//Code to grab the results of the test case execution

	$sql = "select tcid,status from results,project,component,category,testcase where project.id = '" . $_SESSION['project'] . "' and component.id='" . $comID . "' and project.id = component.projid and component.id = category.compid and category.id = testcase.catid and testcase.id = results.tcid order by build";

	$totalResult = mysql_query($sql);

	//Setting the results to an array.. Only taking the most recent results and displaying them
	
	while($totalRow = mysql_fetch_row($totalResult))
	{

		//This is a test.. I've got a problem if the user goes and sets a previous p,f,b value to a 'n' value. The program then sees the most recent value as an not run. I think we want the user to then see the most recent p,f,b value
		
		if($totalRow[1] == 'n')
		{

		}
		else
		{
		
		$testCaseArray[$totalRow[0]] = $totalRow[1];
		
		}

	}



	//This is the code that determines the pass,fail,blocked amounts

	$arrayCounter = 0; //Counter

	//Initializing variables

	$pass = 0;
	$fail = 0;
	$blocked = 0;
	$notRun = 0;


	//I had to write this code so that the loop before would work.. I'm sure there is a better way to do it but hell if I know how to figure it out..
	
	if(count($testCaseArray) > 0)
	{

		foreach($testCaseArray as $tc)
		{

			if($tc == 'p')
			{
				
				$pass++;
				

			}

			elseif($tc == 'f')
			{

				$fail++;

			}

			elseif($tc == 'b')

			{

				$blocked++;

			}

			unset($testCaseArray);


		}//end foreach

	}//end if


	//This loop will cycle through the arrays and count the amount of p,f,b,n
	

	if($totalTCs[0] == 0)
	{
		$percentComplete= 0;

	}else
	{
		$percentComplete = ($pass + $fail + $blocked) / $totalTCs[0]; //Getting total percent complete
		$percentComplete = round((100 * ($percentComplete)),2); //Rounding the number so it looks pretty
	}


		
	$notRunTCs = $totalTCs[0] - ($pass + $fail + $blocked); //Getting the not run TCs

	//Grab the component's name

	$sqlCOMName = "select component.name from component where id=" . $comID;
	$resultCOMName = mysql_query($sqlCOMName);
	$COMName = mysql_fetch_row($resultCOMName);

	$msgBody .= "Project Status For Component: " . $COMName[0] . "\n\n";
	$msgBody .= "Total:" . $totalTCs[0] . "\n";
	$msgBody .= "Passing: " . $pass . "\n";
	$msgBody .= "Failing: " . $fail . "\n";
	$msgBody .= "Blocked: " . $blocked . "\n";
	$msgBody .= "Not Run: " . $notRunTCs . "\n";
	$msgBody .= "Percent Complete: " . $percentComplete. "\n\n";


	return $msgBody;

}

function buildStatus($build)
{

	$sql = "select count(testcase.id) from project,component,category,testcase where project.id =" . $_SESSION['project'] . " and project.id = component.projid and component.id = category.compid and category.id = testcase.catid";

	$sumResult = mysql_query($sql);

	$sumTCs = mysql_fetch_row($sumResult); 

	$total = $sumTCs[0];

	//Get the total # of passed testcases for the project and build

	$sql = "select count(results.tcid) from project,component,category,testcase,results where project.id =" . $_SESSION['project'] . " and project.id = component.projid and component.id = category.compid and category.id = testcase.catid and testcase.id = results.tcid and build = '" . $build . "' and status = 'p'";

	$passedResult = mysql_query($sql);

	$passedTCs = mysql_fetch_row($passedResult);

	$totalPassed = $passedTCs[0];

	//Get the total # of failed testcases for the project

	$sql = "select count(results.tcid) from project,component,category,testcase,results where project.id =" . $_SESSION['project'] . " and project.id = component.projid and component.id = category.compid and category.id = testcase.catid and testcase.id = results.tcid and build = '" . $build . "' and status = 'f'";

	$failedResult = mysql_query($sql);

	$failedTCs = mysql_fetch_row($failedResult);

	$totalFailed = $failedTCs[0];

	//Get the total # of blocked testcases for the project

	$sql = "select count(results.tcid) from project,component,category,testcase,results where project.id =" . $_SESSION['project'] . " and project.id = component.projid and component.id = category.compid and category.id = testcase.catid and testcase.id = results.tcid and build = '" . $build . "' and status = 'b'";

	$blockedResult = mysql_query($sql);

	$blockedTCs = mysql_fetch_row($blockedResult);

	$totalBlocked = $blockedTCs[0];

	//total # of testcases not run

	$notRun = $total - ($totalPassed + $totalFailed + $totalBlocked);

	if($total == 0)
	{
		$percentComplete= 0;

	}else
	{
		$percentComplete = ($totalPassed + $totalFailed + $totalBlocked) / $total; //Getting total percent complete
		$percentComplete = round((100 * ($percentComplete)),2); //Rounding the number so it looks pretty
	}

	$msgBody .= "Project Status For Build: " . $build . "\n\n";
	$msgBody .= "Total: " . $total . "\n";
	$msgBody .= "Passing: " . $totalPassed . "\n";
	$msgBody .= "Failing: " . $totalFailed . "\n";
	$msgBody .= "Blocked: " . $totalBlocked . "\n";
	$msgBody .= "Not Run: " . $notRun . "\n";
	$msgBody .= "Percent Complete: " . $percentComplete. "\n\n";


	return $msgBody;


}

function componentBuild($comID, $build)
{
	$sql = "select count(testcase.id) from project,component,category,testcase where project.id =" . $_SESSION['project'] . " and project.id = component.projid and component.id = category.compid and category.id = testcase.catid and component.id=" . $comID;

	$sumResult = mysql_query($sql);

	$sumTCs = mysql_fetch_row($sumResult); 

	$total = $sumTCs[0];

	//Get the total # of passed testcases for the project and build

	$sql = "select count(results.tcid) from project,component,category,testcase,results where project.id =" . $_SESSION['project'] . " and project.id = component.projid and component.id = category.compid and category.id = testcase.catid and testcase.id = results.tcid and build = '" . $build . "' and status = 'p' and component.id=" . $comID;

	$passedResult = mysql_query($sql);

	$passedTCs = mysql_fetch_row($passedResult);

	$totalPassed = $passedTCs[0];

	//Get the total # of failed testcases for the project

	$sql = "select count(results.tcid) from project,component,category,testcase,results where project.id =" . $_SESSION['project'] . " and project.id = component.projid and component.id = category.compid and category.id = testcase.catid and testcase.id = results.tcid and build = '" . $build . "' and status = 'f' and component.id=" . $comID;

	$failedResult = mysql_query($sql);

	$failedTCs = mysql_fetch_row($failedResult);

	$totalFailed = $failedTCs[0];

	//Get the total # of blocked testcases for the project

	$sql = "select count(results.tcid) from project,component,category,testcase,results where project.id =" . $_SESSION['project'] . " and project.id = component.projid and component.id = category.compid and category.id = testcase.catid and testcase.id = results.tcid and build = '" . $build . "' and status = 'b' and component.id=" . $comID;

	$blockedResult = mysql_query($sql);

	$blockedTCs = mysql_fetch_row($blockedResult);

	$totalBlocked = $blockedTCs[0];

	//total # of testcases not run

	$notRun = $total - ($totalPassed + $totalFailed + $totalBlocked);

	if($total == 0)
	{
		$percentComplete= 0;

	}else
	{
		$percentComplete = ($totalPassed + $totalFailed + $totalBlocked) / $total; //Getting total percent complete
		$percentComplete = round((100 * ($percentComplete)),2); //Rounding the number so it looks pretty
	}

	$msgBody .= "Status For Component " . $comID . " in Build: " . $build . "\n\n";
	$msgBody .= "Total: " . $total . "\n";
	$msgBody .= "Passing: " . $totalPassed . "\n";
	$msgBody .= "Failing: " . $totalFailed . "\n";
	$msgBody .= "Blocked: " . $totalBlocked . "\n";
	$msgBody .= "Not Run: " . $notRun . "\n";
	$msgBody .= "Percent Complete: " . $percentComplete. "\n\n";


	return $msgBody;


}

