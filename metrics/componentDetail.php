<?

////////////////////////////////////////////////////////////////////////////////
//File:     componentDetail.php
//Author:   Chad Rosen
//Purpose:  This page generates the component detail for the report.
////////////////////////////////////////////////////////////////////////////////

//Stats per component

echo "<table class=userinfotable width = '100%'><tr><td bgcolor='#FFFFCC'>Build Status: Component</td></tr></table>";

echo "<table class=userinfotable width='100%'><tr><td  bgcolor='#99CCFF'>Com Name</td><td  bgcolor='#99CCFF'>Total</td><td  bgcolor='#99CCFF'>Passed</td><td  bgcolor='#99CCFF'>Failed</td><td  bgcolor='#99CCFF'>Blocked</td><td  bgcolor='#99CCFF'>Not Run</td><td bgcolor='#99CCFF'>% Complete</td><tr>";

$sql = "select component.name, component.id from project,component where project.id = '" . $_SESSION['project'] . "' and project.id = component.projid";

$result = mysql_query($sql);

while ($myrow = mysql_fetch_row($result)) 
{
		
	//How many TCs per component
	
	$sql = "select count(testcase.id) from project,component,category,testcase where project.id = '" . $_SESSION['project'] . "' and project.id = component.projid and component.id = category.compid and category.id = testcase.catid and component.id ='" . $myrow[1] . "'";

	$totalResult = mysql_query($sql);

	$totalRow = mysql_fetch_row($totalResult);
	
	//Passed TCs per component

	$sql = "select count(testcase.id) from project,component,category,testcase,results where project.id = '" . $_SESSION['project'] . "' and project.id = component.projid and component.id = category.compid and category.id = testcase.catid and component.id ='" . $myrow[1] . "' and testcase.id = results.tcid and results.build='" . $_POST['build'] . "' and results.status='p'";

	$passedResult = mysql_query($sql);

	$passedRow = mysql_fetch_row($passedResult);

	//Failed TCs per component

	$sql ="select count(testcase.id) from project,component,category,testcase,results where project.id = '" . $_SESSION['project'] . "' and project.id = component.projid and component.id = category.compid and category.id = testcase.catid and component.id ='" . $myrow[1] . "' and testcase.id = results.tcid and results.build='" . $_POST['build'] . "' and results.status='f'";

	$failedResult = mysql_query($sql);

	$failedRow = mysql_fetch_row($failedResult);

	//Blocked TCs per component

	$sql = "select count(testcase.id) from project,component,category,testcase,results where project.id = '" . $_SESSION['project'] . "' and project.id = component.projid and component.id = category.compid and category.id = testcase.catid and component.id ='" . $myrow[1] . "' and testcase.id = results.tcid and results.build='" . $_POST['build'] . "' and results.status='b'";

	$blockedResult = mysql_query($sql);

	$blockedRow = mysql_fetch_row($blockedResult);

	//Not Run TCs per component

	$notRun = $totalRow[0] - ($passedRow[0] + $failedRow[0] + $blockedRow[0]);

	if($totalRow[0] == 0) //if we try to divide by 0 we get an error
	{
		$percentComplete = 0;

	}else
	{
	
		$percentComplete = ($passedRow[0] + $failedRow[0] + $blockedRow[0]) / $totalRow[0]; //Getting total percent complete
		$percentComplete = round((100 * ($percentComplete)),2); //Rounding the number so it looks pretty
		
	}


	echo "<td  bgcolor='#CCCCCC'>";

	//displays the component name

	echo $myrow[0] . "</td>";	
	


	echo "<td>" . $totalRow[0] . "</td>"; //display total test cases

	echo "<td>" . $passedRow[0] . "</td>"; 

	echo "<td>" . $failedRow[0] . "</td>"; 

	echo "<td>" . $blockedRow[0] . "</td>"; 

	echo "<td>" . $notRun . "</td>"; 

	echo "<td>" . $percentComplete . "</td></tr>";

////////////Displaying each of the categories for the components

	}

	echo "</table>";

//}//END WHILE

?>
