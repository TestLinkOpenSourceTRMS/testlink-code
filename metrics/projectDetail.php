<?

////////////////////////////////////////////////////////////////////////////////
//File:     projectDetail.php
//Author:   Chad Rosen
//Purpose:  This page presents the details based on projects.
////////////////////////////////////////////////////////////////////////////////

//Code below grabs the priority that the user has assigned

$sql = "select priority from project,priority where project.id = '" . $_SESSION['project'] . "' and project.id = priority.projid";

$result = mysql_query($sql);

while ($myrow = mysql_fetch_row($result)) 
{

	$priority[] = $myrow[0];

}

//print_r($priority);

$L1 = $priority[0]; 
$L2 = $priority[1];
$L3 = $priority[2];
$M1 = $priority[3];
$M2 = $priority[4];
$M3 = $priority[5];
$H1 = $priority[6];
$H2 = $priority[7];
$H3 = $priority[8];

$totalA = 0;
$passA = 0;
$failA = 0;
$blockedA = 0;
$totalB = 0;
$passB = 0;
$failB = 0;
$blockedB = 0;
$totalC = 0;
$passC = 0;
$failC = 0;
$blockedC = 0;

//

$sql = "select category.risk, category.id, category.importance from project,component, category where project.id = '" . $_SESSION['project'] . "' and project.id = component.projid and component.id = category.compid";

$result = mysql_query($sql);

while ($myrow = mysql_fetch_row($result)) 
{

	//Code to grab the entire amount of test cases per project
	
	$sql = "select count(testcase.id) from project,component,category,testcase where project.id = '" . $_SESSION['project'] . "' and category.id='" . $myrow[1] . "' and project.id = component.projid and component.id = category.compid and category.id = testcase.catid";

	$totalTCResult = mysql_query($sql);

	$totalTCs = mysql_fetch_row($totalTCResult);

	//Code to grab the results of the test case execution

	$sql = "select tcid,status from results,project,component,category,testcase where project.id = '" . $_SESSION['project'] . "' and category.id='" . $myrow[1] . "' and project.id = component.projid and component.id = category.compid and category.id = testcase.catid and testcase.id = results.tcid and results.build = '" . $_POST['build'] . "'";

	$totalResult = mysql_query($sql);

	//Setting the results to an array.. Only taking the most recent results and displaying them
	
	//Initializing variables
		
	$pass = 0;
	$fail = 0;
	$blocked = 0;
	$notRun = 0;

	while($totalRow = mysql_fetch_row($totalResult))
	{

		if($totalRow[1] == 'p')

		{
			
			$pass++;
			

		}

		if($totalRow[1] == 'f')


		{

			$fail++;

		}

		if($totalRow[1] == 'b')


		{

			$blocked++;

		}

	}

	$priStatus = $myrow[2] . $myrow[0];

	if($priStatus == 'L1' && $L1 == "a")
	{

		$totalA = $totalA + $totalTCs[0];
		$passA = $passA + $pass;
		$failA = $failA + $fail;
		$blockedA = $blockedA + $blocked;

	}elseif($priStatus == 'L1' && $L1 == "b")
	{
		$totalB = $totalB + $totalTCs[0];
		$passB = $passB + $pass;
		$failB = $failB + $fail;
		$blockedB = $blockedB + $blocked;

	}elseif($priStatus == 'L1' && $L1 == "c")
	{

		$totalC = $totalC + $totalTCs[0];
		$passC = $passC + $pass;
		$failC = $failC + $fail;
		$blockedC = $blockedC + $blocked;


	}elseif($priStatus == 'L2' && $L2 == "a")
	{

		$totalA = $totalA + $totalTCs[0];
		$passA = $passA + $pass;
		$failA = $failA + $fail;
		$blockedA = $blockedA + $blocked;

	}elseif($priStatus == 'L2' && $L2 == "b")
	{
		$totalB = $totalB + $totalTCs[0];
		$passB = $passB + $pass;
		$failB = $failB + $fail;
		$blockedB = $blockedB + $blocked;

	}elseif($priStatus == 'L2' && $L2 == "c")
	{

		$totalC = $totalC + $totalTCs[0];
		$passC = $passC + $pass;
		$failC = $failC + $fail;
		$blockedC = $blockedC + $blocked;


	}elseif($priStatus == 'L3' && $L3 == "a")
	{

		$totalA = $totalA + $totalTCs[0];
		$passA = $passA + $pass;
		$failA = $failA + $fail;
		$blockedA = $blockedA + $blocked;

	}elseif($priStatus == 'L3' && $L3 == "b")
	{
		$totalB = $totalB + $totalTCs[0];
		$passB = $passB + $pass;
		$failB = $failB + $fail;
		$blockedB = $blockedB + $blocked;

	}elseif($priStatus == 'L3' && L3 == "c")
	{

		$totalC = $totalC + $totalTCs[0];
		$passC = $passC + $pass;
		$failC = $failC + $fail;
		$blockedC = $blockedC + $blocked;


	}elseif($priStatus == 'M1' && $M1 == "a")
	{

		$totalA = $totalA + $totalTCs[0];
		$passA = $passA + $pass;
		$failA = $failA + $fail;
		$blockedA = $blockedA + $blocked;

	}elseif($priStatus == 'M1' && $M1 == "b")
	{
		$totalB = $totalB + $totalTCs[0];
		$passB = $passB + $pass;
		$failB = $failB + $fail;
		$blockedB = $blockedB + $blocked;

	}elseif($priStatus == 'M1' && $M1 == "c")
	{

		$totalC = $totalC + $totalTCs[0];
		$passC = $passC + $pass;
		$failC = $failC + $fail;
		$blockedC = $blockedC + $blocked;


	}elseif($priStatus == 'M2' && $M2 == "a")
	{

		$totalA = $totalA + $totalTCs[0];
		$passA = $passA + $pass;
		$failA = $failA + $fail;
		$blockedA = $blockedA + $blocked;

	}elseif($priStatus == 'M2' && $M2 == "b")
	{
		$totalB = $totalB + $totalTCs[0];
		$passB = $passB + $pass;
		$failB = $failB + $fail;
		$blockedB = $blockedB + $blocked;

	}elseif($priStatus == 'M2' && $M2 == "c")
	{

		$totalC = $totalC + $totalTCs[0];
		$passC = $passC + $pass;
		$failC = $failC + $fail;
		$blockedC = $blockedC + $blocked;


	}elseif($priStatus == 'M3' && $M3 == "a")
	{

		$totalA = $totalA + $totalTCs[0];
		$passA = $passA + $pass;
		$failA = $failA + $fail;
		$blockedA = $blockedA + $blocked;

	}elseif($priStatus == 'M3' && $M3 == "b")
	{
		$totalB = $totalB + $totalTCs[0];
		$passB = $passB + $pass;
		$failB = $failB + $fail;
		$blockedB = $blockedB + $blocked;

	}elseif($priStatus == 'M3' && $M3 == "c")
	{

		$totalC = $totalC + $totalTCs[0];
		$passC = $passC + $pass;
		$failC = $failC + $fail;
		$blockedC = $blockedC + $blocked;


	}elseif($priStatus == 'H1' && $H1 == "a")
	{

		$totalA = $totalA + $totalTCs[0];
		$passA = $passA + $pass;
		$failA = $failA + $fail;
		$blockedA = $blockedA + $blocked;

	}elseif($priStatus == 'H1' && $H1 == "b")
	{
		$totalB = $totalB + $totalTCs[0];
		$passB = $passB + $pass;
		$failB = $failB + $fail;
		$blockedB = $blockedB + $blocked;

	}elseif($priStatus == 'H1' && $H1 == "c")
	{

		$totalC = $totalC + $totalTCs[0];
		$passC = $passC + $pass;
		$failC = $failC + $fail;
		$blockedC = $blockedC + $blocked;


	}elseif($priStatus == 'H2' && $H2 == "a")
	{

		$totalA = $totalA + $totalTCs[0];
		$passA = $passA + $pass;
		$failA = $failA + $fail;
		$blockedA = $blockedA + $blocked;

	}elseif($priStatus == 'H2' && $H2 == "b")
	{
		$totalB = $totalB + $totalTCs[0];
		$passB = $passB + $pass;
		$failB = $failB + $fail;
		$blockedB = $blockedB + $blocked;

	}elseif($priStatus == 'H2' && $H2 == "c")
	{

		$totalC = $totalC + $totalTCs[0];
		$passC = $passC + $pass;
		$failC = $failC + $fail;
		$blockedC = $blockedC + $blocked;


	}elseif($priStatus == 'H3' && $H3 == "a")
	{

		$totalA = $totalA + $totalTCs[0];
		$passA = $passA + $pass;
		$failA = $failA + $fail;
		$blockedA = $blockedA + $blocked;

	}elseif($priStatus == 'H3' && $H3 == "b")
	{
		$totalB = $totalB + $totalTCs[0];
		$passB = $passB + $pass;
		$failB = $failB + $fail;
		$blockedB = $blockedB + $blocked;

	}elseif($priStatus == 'H3' && $H3 == "c")
	{

		$totalC = $totalC + $totalTCs[0];
		$passC = $passC + $pass;
		$failC = $failC + $fail;
		$blockedC = $blockedC + $blocked;


	}

}

	$notRunTCsA = $totalA - ($passA + $failA + $blockedA); //Getting the not run TCs
	
	if($totalA == 0)
	{
		$percentCompleteA = 0;

	}else
	{
		$percentCompleteA = ($passA + $failA + $blockedA) / $totalA; //Getting total percent complete
		$percentCompleteA = round((100 * ($percentCompleteA)),2); //Rounding the number so it looks pretty
	}


	$notRunTCsB = $totalB - ($passB + $failB + $blockedB); //Getting the not run TCs

	if($totalB == 0)
	{
		$percentCompleteB = 0;

	}else
	{
		$percentCompleteB = ($passB + $failB + $blockedB) / $totalB; //Getting total percent complete
		$percentCompleteB = round((100 * ($percentCompleteB)),2); //Rounding the number so it looks pretty

	}

	$notRunTCsC = $totalC - ($passC + $failC + $blockedC); //Getting the not run TCs
	
	if($totalC == 0)
	{
		$percentCompleteC= 0;

	}else
	{
		$percentCompleteC = ($passC + $failC + $blockedC) / $totalC; //Getting total percent complete
		$percentCompleteC = round((100 * ($percentCompleteC)),2); //Rounding the number so it looks pretty
	}



//Get the total # of testcases for the project

$sql = "select count(testcase.id) from project,component,category,testcase where project.id = '" . $_SESSION['project'] . "' and project.id = component.projid and component.id = category.compid and category.id = testcase.catid";

$result = mysql_query($sql);
$myrow = mysql_fetch_row($result); 
$total = $myrow[0];

//Get the total # of passed testcases for the project and build

$sql = "select count(results.tcid) from project,component,category,testcase,results where project.id = '" . $_SESSION['project'] . "' and project.id = component.projid and component.id = category.compid and category.id = testcase.catid and testcase.id = results.tcid and build = '" . $_POST['build'] . "' and status = 'p'";

$result = mysql_query($sql);
$myrow = mysql_fetch_row($result);
$totalPassed = $myrow[0];

//Get the total # of failed testcases for the project

$sql = "select count(results.tcid) from project,component,category,testcase,results where project.id = '" . $_SESSION['project'] . "' and project.id = component.projid and component.id = category.compid and category.id = testcase.catid and testcase.id = results.tcid and build = '" . $_POST['build'] . "' and status = 'f'";

$result = mysql_query($sql);
$myrow = mysql_fetch_row($result);
$totalFailed = $myrow[0];

//Get the total # of blocked testcases for the project

$sql = "select count(results.tcid) from project,component,category,testcase,results where project.id = '" . $_SESSION['project'] . "' and project.id = component.projid and component.id = category.compid and category.id = testcase.catid and testcase.id = results.tcid and build = '" . $_POST['build'] . "' and status = 'b'";

$result = mysql_query($sql);
$myrow = mysql_fetch_row($result);
$totalBlocked = $myrow[0];

//total # of testcases not run

$notRun = $total - ($totalPassed + $totalFailed + $totalBlocked);

//Getting the project name

$projectSql = "select name from project where id='" . $_SESSION['project'] . "'";
$projectNameResult = mysql_query($projectSql);
$projectName = mysql_fetch_row($projectNameResult);

//Displaying the results

echo "<h2>Test Plan " . $projectName[0] . " Build " . $_POST['build'] . " Results</h2>";

echo "<table class=userinfotable width='50%'><tr><td bgcolor='#FFFFCC'>Build Status: Test Plan</td></tr></table>";
echo "<table class=userinfotable width='50%'>";

echo "<tr><td bgcolor='#99CCFF'>Total</td><td bgcolor='#99CCFF'>TCs</td><td bgcolor='#99CCFF'>%</td><td bgcolor='#99CCFF'>Pri A</td><td bgcolor='#99CCFF'>%</td><td bgcolor='#99CCFF'>Pri B</td><td bgcolor='#99CCFF'>%</td><td bgcolor='#99CCFF'>Pri C</td><td bgcolor='#99CCFF'>%</td></tr>";

echo "<tr><td  bgcolor='#CCCCCC'>TCs</td><td>" . $total . "</td><td bgcolor='#CCCCCC'>-</td><td>" . $totalA . "</td><td bgcolor='#CCCCCC'>-</td><td>" . $totalB . "</td><td bgcolor='#CCCCCC'>-</td><td>" . $totalC . "<td bgcolor='#CCCCCC'>-</td></tr>";

//Have to take into account cases where you might divide 0 by 0

if($total == 0)
{
	$percentPass = 0;
	$percentFail = 0;
	$percentBlocked = 0;
	$percentNotRun = 0;
}else
{

	$percentPass = round((100 * ($totalPassed/$total)),2);
	$percentFail = round((100 * ($totalFailed/$total)),2);
	$percentBlocked = round((100 * ($totalBlocked/$total)),2);
	$percentNotRun = round((100 * ($notRun/$total)),2);

}

if($totalA == 0)
{

	$percentPassA = 0;
	$percentFailA = 0;
	$percentBlockedA = 0;
	$percentNotRunA = 0;

}else
{
	$percentPassA = round((100 * ($passA/$totalA)),2);
	$percentFailA = round((100 * ($failA/$totalA)),2);
	$percentBlockedA = round((100 * ($blockedA/$totalA)),2);
	$percentNotRunA = round((100 * ($notRunTCsA/$totalA)),2);
}


if($totalB == 0)
{
	$percentPassB = 0;
	$percentFailB = 0;
	$percentBlockedB = 0;
	$percentNotRunB = 0;
}else
{
	$percentPassB = round((100 * ($passB/$totalB)),2);
	$percentFailB = round((100 * ($failB/$totalB)),2);
	$percentBlockedB = round((100 * ($blockedB/$totalB)),2);
	$percentNotRunB = round((100 * ($notRunTCsB/$totalB)),2);
}


if($totalC == 0)
{
	$percentPassC = 0;
	$percentFailC = 0;
	$percentBlockedC = 0;
	$percentNotRunC = 0;
}else
{
	$percentPassC = round((100 * ($passC/$totalC)),2);
	$percentFailC = round((100 * ($failC/$totalC)),2);
	$percentBlockedC = round((100 * ($blockedC/$totalC)),2);
	$percentNotRunC = round((100 * ($notRunTCsC/$totalC)),2);
}


echo "<tr><td  bgcolor='#CCCCCC'>Passed</td><td>" . $totalPassed . "</td><td bgcolor='#CCCCCC'>" . $percentPass . "</td><td>" . $passA . "</td><td bgcolor='#CCCCCC'>" . $percentPassA . "</td><td>" . $passB . "</td><td bgcolor='#CCCCCC'>" . $percentPassB . "</td><td>" . $passC . "</td><td bgcolor='#CCCCCC'>" . $percentPassC . "</td></tr>";

echo "<tr><td  bgcolor='#CCCCCC'>Failed</td><td>" . $totalFailed . "</td><td bgcolor='#CCCCCC'>" .  $percentFail . "</td><td>" . $failA . "</td><td bgcolor='#CCCCCC'>" . $percentFailA . "</td><td>" . $failB . "</td><td bgcolor='#CCCCCC'>" . $percentFailB . "</td><td>" . $failC . "</td><td bgcolor='#CCCCCC'>" . $percentFailC . "</td></tr>";

echo "<tr><td  bgcolor='#CCCCCC'>Blocked</td><td>" . $totalBlocked . "</td><td bgcolor='#CCCCCC'>" .  $percentBlocked . "</td><td>" . $blockedA . "</td><td bgcolor='#CCCCCC'>" . $percentBlockedA . "</td><td>" . $blockedB . "</td><td bgcolor='#CCCCCC'>" . $percentBlockedB . "</td><td>" . $blockedC . "</td><td bgcolor='#CCCCCC'>" . $percentBlockedC . "</td></tr>";

echo "<tr><td  bgcolor='#CCCCCC'>Not Run</td><td>" . $notRun . "</td><td bgcolor='#CCCCCC'>" .  $percentNotRun . "</td><td>" . $notRunTCsA . "</td><td bgcolor='#CCCCCC'>" . $percentNotRunA . "</td><td>" . $notRunTCsB . "</td><td bgcolor='#CCCCCC'>" . $percentNotRunB . "</td><td>" . $notRunTCsC . "</td><td bgcolor='#CCCCCC'>" . $percentNotRunC . "</td></tr>";


echo "</table>";

?>
