<table width="450" height="100%" border="0" align="left">
  <tr>
    <td valign="top"><table width=100% border=1 align=center class="mainTable">
        <tr>
          <td bgcolor=#CC3366><font color="#FFFFFF" size=4>Your Test Plan Metrics</font></td>
        </tr>
</table>

<table width=100% border=1 align=center class="mainTable">
   <tr>
	<td width=50% class=mainSubHeader>Name</td>
    <td width=25% class=mainSubHeader>% Complete</td>
    <td width=25% class=mainSubHeader>Your % Complete</td>
   </tr>

<?

$projectSelect = "select project.name,project.id from project,projrights,user where project.id=projrights.projid and user.id=projrights.userid and user.id=" . $_SESSION['userID'];

$projectResult = mysql_query($projectSelect,$db);

$projectCount = mysql_num_rows($projectResult);

if($projectCount > 0)
{			
	while ($myrow = mysql_fetch_row($projectResult))
	{
		$completed = totalTCs($myrow[1]);
		$myTCs = myTCs($myrow[1],$_SESSION['userID']);
		
		echo "<tr><td class='mainMenu'>";
		
		echo "<a href='metrics/metricsFrameSet.php?projectId=" . $myrow[1] . "&nav= > Test Plan Metrics'>" . $myrow[0] . "</a>"; 
			
		echo "</td><td class='mainMenu'>" . $completed . "</td><td class='mainMenu'>" . $myTCs . "</td></tr>";
	}

}else
{
	echo "<tr><td class='mainMenu'><font color='#FF0000'>No Projects available</font></td><td class='mainMenu'><font color='#FF0000'>---</font></td><td class='mainMenu'><font color='#FF0000'>---</font></td></tr>";
}

function totalTCs($projectID)
{
	//Code to grab the entire amount of test cases per project
		
	$sqlTotal = "select count(testcase.id) from project,component,category,testcase where project.id = '" . $projectID . "' and project.id = component.projid and component.id = category.compid and category.id = testcase.catid";
	
	$totalResult = mysql_query($sqlTotal);
	$myrowTotal = mysql_fetch_row($totalResult);
	
	//Code to grab the results of the test case execution

	$exSql = "select tcid,status from results,project,component,category,testcase where project.id = '" . $projectID . "' and project.id = component.projid and component.id = category.compid and category.id = testcase.catid and testcase.id = results.tcid order by build";

	$exResult = mysql_query($exSql);

	//Setting the results to an array.. Only taking the most recent results and displaying them
	
	$completed = countResults($myrowTotal[0],$exResult);
	
	return $completed;

}

function myTCs($projectID,$owner)
{

		//This is really stupid.. I store the category owner as a string and not a key.. WHY?

		$sqlName = "select login,id from user where id=" . $owner;
		$sqlResult = mysql_query($sqlName);
		$ownerArray = mysql_fetch_row($sqlResult);

		$owner = $ownerArray[1];

		//Code to grab the entire amount of test cases per project
		
		$sql = "select count(testcase.id) from project,component,category,testcase where project.id = '" . $projectID . "' and project.id = component.projid and testcase.owner ='" . $owner . "' and component.id = category.compid and category.id = testcase.catid";

		$totalTCResult = mysql_query($sql);

		$totalTCs = mysql_fetch_row($totalTCResult);

		//Code to grab the results of the test case execution

		$sql = "select tcid,status from results,project,component,category,testcase where project.id = '" . $projectID . "' and testcase.owner='" . $owner . "' and project.id = component.projid and component.id = category.compid and category.id = testcase.catid and testcase.id = results.tcid order by build";
		
		$totalResult = mysql_query($sql);

		//Setting the results to an array.. Only taking the most recent results and displaying them

		$completed = countResults($totalTCs[0],$totalResult);

		return $completed;
	
}

function countResults($total, $exResult)
{
	//Setting the results to an array.. Only taking the most recent results and displaying them

	while($totalRow = mysql_fetch_row($exResult))
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

		//This loop will cycle through the arrays and count the amount of p,f,b,n

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
		}//end foreach

	}//end if

	
	if($total > 0 )
	{

		$completed = (($pass + $fail + $blocked) / $total) * 100;
		$completed = round($completed, 2);  

	}else
	{
		$completed = 0;
	}

	return $completed;

}

?>

</table>