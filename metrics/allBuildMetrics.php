<?

////////////////////////////////////////////////////////////////////////////////
//File:     allBuildMetrics.php
//Author:   Chad Rosen
//Purpose:  This page displays the metrics across all builds.
////////////////////////////////////////////////////////////////////////////////

require_once("../functions/header.php");

  session_start();
  doDBConnect();
  doHeader();

?>

<LINK REL="stylesheet" TYPE="text/css" HREF="kenny.css">

<?

$result = mysql_query("select build from build,project where project.id = " . $_SESSION['project'] . " and build.projid = project.id",$db);

echo "<table class=userinfotable><tr>";

$counter = 0;

while ($myrow = mysql_fetch_row($result)) 

{

	echo "<td bgcolor='#CCCCCC'><b>" . $myrow[0] . "</b></td>";

	$buildNumber[] = $myrow[0];

	$counter++;

}

echo "<tr>";

$i = 0;

while($i < $counter)

{

$sql = "select count(testcase.id) from project,component,category,testcase where project.id =" . $_SESSION['project'] . " and project.id = component.projid and component.id = category.compid and category.id = testcase.catid";

	$sumResult = mysql_query($sql);

	$sumTCs = mysql_fetch_row($sumResult); 

	$total = $sumTCs[0];

	//Get the total # of passed testcases for the project and build

	$sql = "select count(results.tcid) from project,component,category,testcase,results where project.id =" . $_SESSION['project'] . " and project.id = component.projid and component.id = category.compid and category.id = testcase.catid and testcase.id = results.tcid and build = '" . $buildNumber[$i] . "' and status = 'p'";

	$passedResult = mysql_query($sql);

	$passedTCs = mysql_fetch_row($passedResult);

	$totalPassed = $passedTCs[0];

	//Get the total # of failed testcases for the project

	$sql = "select count(results.tcid) from project,component,category,testcase,results where project.id =" . $_SESSION['project'] . " and project.id = component.projid and component.id = category.compid and category.id = testcase.catid and testcase.id = results.tcid and build = '" . $buildNumber[$i] . "' and status = 'f'";

	$failedResult = mysql_query($sql);

	$failedTCs = mysql_fetch_row($failedResult);

	$totalFailed = $failedTCs[0];

	//Get the total # of blocked testcases for the project

	$sql = "select count(results.tcid) from project,component,category,testcase,results where project.id =" . $_SESSION['project'] . " and project.id = component.projid and component.id = category.compid and category.id = testcase.catid and testcase.id = results.tcid and build = '" . $buildNumber[$i] . "' and status = 'b'";

	$blockedResult = mysql_query($sql);

	$blockedTCs = mysql_fetch_row($blockedResult);

	$totalBlocked = $blockedTCs[0];

	//total # of testcases not run

	$notRun = $total - ($totalPassed + $totalFailed + $totalBlocked);


	echo "<td>";

	echo "<table class=userinfotable>";

	echo "<tr><td bgcolor='#99CCFF'><b>Total</td><td bgcolor='#99CCFF'><b>TCs</td><td bgcolor='#99CCFF'><b>%</td></td>";

	//Need to check if total is equal to zero

	if($total == 0)
	{
		$totalPassed = 0;
		$totalFailed = 0;
		$totalBlocked = 0;
		$notRun = 0;

		echo "<tr><td >TCs</td><td>" . $total . "</td><td>-</td></tr>";

		echo "<tr><td>Passed</td><td>" . $totalPassed . "</td><td>0</td></tr>";

		echo "<tr><td>Failed</td><td>" . $totalFailed . "</td><td>0</td></tr>";

		echo "<tr><td>Blocked</td><td>" . $totalBlocked . "</td><td>0</td></tr>";

		echo "<tr><td>Not Run</td><td>" . $notRun . "</td><td>0</td></tr>";

		echo "</table>";

		echo "</td>";


	}else
	{

		echo "<tr><td >TCs</td><td>" . $total . "</td><td>-</td></tr>";

		echo "<tr><td>Passed</td><td>" . $totalPassed . "</td><td>" . round((100 * ($totalPassed/$total)),2) . "</td></tr>";

		echo "<tr><td>Failed</td><td>" . $totalFailed . "</td><td>" .  round((100 * ($totalFailed/$total)),2) . "</td></tr>";

		echo "<tr><td>Blocked</td><td>" . $totalBlocked . "</td><td>" .  round((100 * ($totalBlocked/$total)),2) . "</td></tr>";

		echo "<tr><td>Not Run</td><td>" . $notRun . "</td><td>" .  round((100 * ($notRun/$total)),2) . "</td></tr>";

		echo "</table>";

		echo "</td>";

	}


$i++;

}

echo "</table>";




?>
