<?

////////////////////////////////////////////////////////////////////////////////
//File:     categoryDetail.php
//Author:   Chad Rosen
//Purpose:  This page generates the category detail for the report.
////////////////////////////////////////////////////////////////////////////////

//Code below grabs the priority that the user has assigned

//Stats per component

echo "<table class=userinfotable width = '100%'><tr><td bgcolor='#FFFFCC'>Build Status: Category</td></tr></table>";

echo "<table class=userinfotable width='100%'><tr><td  bgcolor='#99CCFF'>Com Name</td><td  bgcolor='#99CCFF'>Cat Name</td><td  bgcolor='#99CCFF'>Total</td><td  bgcolor='#99CCFF'>Passed</td><td  bgcolor='#99CCFF'>Failed</td><td  bgcolor='#99CCFF'>Blocked</td><td  bgcolor='#99CCFF'>Not Run</td><td bgcolor='#99CCFF'>% Complete</td><tr>";

$sql = "select component.name, component.id from project,component where project.id = '" . $_SESSION['project'] . "' and project.id = component.projid";

$result = mysql_query($sql);

while ($myrow = mysql_fetch_row($result)) 
{

	$componentName = $myrow[0];
			
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

////////////Displaying each of the categories for the components
	
	$categoryQuery = "select category.name, category.id from project,component,category where project.id = '" . $_SESSION['project'] . "' and project.id = component.projid and component.id = category.compid and component.id =" . $myrow[1];

	$categoryResult = mysql_query($categoryQuery);

	while ($categoryRow = mysql_fetch_row($categoryResult)) 
	{
		$catAllSql = "select count(testcase.id) from project,component,category,testcase where project.id = '" . $_SESSION['project'] . "' and project.id = component.projid and component.id = category.compid and category.id = testcase.catid and component.id ='" . $myrow[1] . "' and category.id='" . $categoryRow[1] . "'";

		$catTotalResult = mysql_query($catAllSql);

		$totalRow = mysql_fetch_row($catTotalResult);
		
		//Passed TCs per component

		$sql = "select count(testcase.id) from project,component,category,testcase,results where project.id = '" . $_SESSION['project'] . "' and project.id = component.projid and component.id = category.compid and category.id = testcase.catid and component.id ='" . $myrow[1] . "' and testcase.id = results.tcid and results.build='" . $_POST['build'] . "' and results.status='p' and category.id='" . $categoryRow[1] . "'";

		$passedResult = mysql_query($sql);

		$passedRow = mysql_fetch_row($passedResult);

		//Failed TCs per component

		$sql ="select count(testcase.id) from project,component,category,testcase,results where project.id = '" . $_SESSION['project'] . "' and project.id = component.projid and component.id = category.compid and category.id = testcase.catid and component.id ='" . $myrow[1] . "' and testcase.id = results.tcid and results.build='" . $_POST['build'] . "' and results.status='f' and category.id='" . $categoryRow[1] . "'";

		$failedResult = mysql_query($sql);

		$failedRow = mysql_fetch_row($failedResult);

		//Blocked TCs per component

		$sql = "select count(testcase.id) from project,component,category,testcase,results where project.id = '" . $_SESSION['project'] . "' and project.id = component.projid and component.id = category.compid and category.id = testcase.catid and component.id ='" . $myrow[1] . "' and testcase.id = results.tcid and results.build='" . $_POST['build'] . "' and results.status='b' and category.id='" . $categoryRow[1] . "'";

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

		echo "<td  bgcolor='#CCCCCC'>" . $componentName . "</td>"; //prints the component name

		//echo "<td>" . $categoryRow[0] . "</td>"; //prints the category name

		echo "<td>" . $categoryRow[0] . "</td>";	
		
		echo "<td>" . $totalRow[0] . "</td>"; //prints the total test cases per category

		echo "<td>" . $passedRow[0] . "</td>"; //<td>" . round((100 * ($totalPassed/$total)),2) . "</td>";

		echo "<td>" . $failedRow[0] . "</td>"; //<td>" .  round((100 * ($totalFailed/$total)),2) . "</td>";

		echo "<td>" . $blockedRow[0] . "</td>"; //<td>" .  round((100 * ($totalBlocked/$total)),2) . "</td>";

		echo "<td>" . $notRun . "</td>"; //<td>" .  round((100 * ($notRun/$total)),2) . "</td>";

		echo "<td>" . $percentComplete . "</td></tr>"; //prints percent completed

	}


}//END WHILE

echo "</table>";

?>