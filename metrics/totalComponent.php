<?php

////////////////////////////////////////////////////////////////////////////////
//File:     totalComponent.php
//Author:   Chad Rosen
//Purpose:  This page displays test cases by component.
////////////////////////////////////////////////////////////////////////////////

////The table and the top row

echo "<br><table width='100%' class=userinfotable><tr><td bgcolor='#99CCFF' class='subTitle'>Status By Component</td></tr></table>";

echo "<table width='100%' class=userinfotable>";

echo "<tr><td width='14%' class=tctablehdrCenter>Component</td><td width='14%' class=tctablehdrCenter>Total</td><td width='14%' class=tctablehdrCenter>Pass</td><td width='14%' class=tctablehdrCenter>Fail</td><td width='14%' class=tctablehdrCenter>Blocked</td><td width='14%' class=tctablehdrCenter>Not Run</td><td width='14%' class=tctablehdrCenter>% Complete</td></tr>";

//Begin code to display the component


$sql = "select component.name, component.id from project,component where project.id = '" . $_SESSION['project'] . "' and project.id = component.projid";

$result = mysql_query($sql);

while ($myrow = mysql_fetch_row($result)) 
{

	//Code to grab the entire amount of test cases per project
	
	$sql = "select count(testcase.id) from project,component,category,testcase where project.id = '" . $_SESSION['project'] . "' and component.id='" . $myrow[1] . "' and project.id = component.projid and component.id = category.compid and category.id = testcase.catid";

	$totalTCResult = mysql_query($sql);

	$totalTCs = mysql_fetch_row($totalTCResult);

	//Code to grab the results of the test case execution

	$sql = "select tcid,status from results,project,component,category,testcase where project.id = '" . $_SESSION['project'] . "' and component.id='" . $myrow[1] . "' and project.id = component.projid and component.id = category.compid and category.id = testcase.catid and testcase.id = results.tcid order by build";

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

	
	

	//Displaying the results
				
	echo "<td  bgcolor='#CCCCCC' class='boldFont' align='center'>" . $myrow[0] . "</td>"; //displaying the component name
	
	echo "<td class='font' align='center'>" . $totalTCs[0] . "</td>";

	echo "<td class='font' align='center'>" . $pass . "</td>";

	echo "<td class='font' align='center'>" . $fail . "</td>";

	echo "<td class='font' align='center'>" . $blocked . "</td>";

	echo "<td class='font' align='center'>" . $notRunTCs . "</td>"; //<td>" .  round((100 * ($notRun/$total)),2) . "</td>";

	echo "<td class='font' align='center'>" . $percentComplete . "</td></tr>";


}

echo "</table><br>";

?>
