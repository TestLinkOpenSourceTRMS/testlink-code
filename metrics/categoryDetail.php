<?

////////////////////////////////////////////////////////////////////////////////
//File:     categoryDetail.php
//Author:   Chad Rosen
//Purpose:  This page generates the category detail for the report.
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


//Stats per component

echo "<table class=userinfotable width = '100%'><tr><td bgcolor='#FFFFCC'>Build Status: Category</td></tr></table>";

echo "<table class=userinfotable width='100%'><tr><td  bgcolor='#99CCFF'>Com Name</td><td  bgcolor='#99CCFF'>Cat Name</td><td  bgcolor='#99CCFF'>Risk</td><td  bgcolor='#99CCFF'>Importance</td><td  bgcolor='#99CCFF'>Priority</td><td  bgcolor='#99CCFF'>Total</td><td  bgcolor='#99CCFF'>Passed</td><td  bgcolor='#99CCFF'>Failed</td><td  bgcolor='#99CCFF'>Blocked</td><td  bgcolor='#99CCFF'>Not Run</td><td bgcolor='#99CCFF'>% Complete</td><tr>";

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
	
	$categoryQuery = "select category.name, category.id, risk, importance from project,component,category where project.id = '" . $_SESSION['project'] . "' and project.id = component.projid and component.id = category.compid and component.id =" . $myrow[1];

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

		//Determining Priority from risk and importance

		$priorityStatus = $categoryRow[3] . $categoryRow[2];

	
		if($priorityStatus == 'L1')
		{

		$priority = $L1;

		}

		elseif($priorityStatus == 'L2')
		{

		$priority = $L2;

		}

		elseif($priorityStatus == 'L3')
		{

		$priority = $L3;

		}

		elseif($priorityStatus == 'M1')
		{

		$priority = $M1;

		}

		elseif($priorityStatus == 'M2')
		{

		$priority = $M2;

		}

		elseif($priorityStatus == 'M3')
		{

		$priority = $M3;

		}

		elseif($priorityStatus == 'H1')
		{

		$priority = $H1;

		}
		elseif($priorityStatus == 'H2')
		{

		$priority = $H2;

		}

		elseif($priorityStatus == 'H3')
		{

		$priority = $H3;

		}

		echo "<td  bgcolor='#CCCCCC'>" . $componentName . "</td>"; //prints the component name

		//echo "<td>" . $categoryRow[0] . "</td>"; //prints the category name

		echo "<td>" . $categoryRow[0] . "</td>";	


		echo "<td>" . $categoryRow[2] . "</td>"; //prints the risk

		echo "<td>" . $categoryRow[3] . "</td>"; //prints the importance

		echo "<td>" . $priority . "</td>";
		
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
