<?php

////////////////////////////////////////////////////////////////////////////////
//File:     priority.php
//Author:   Chad Rosen
//Purpose:  This page this generates stats based on priority
////////////////////////////////////////////////////////////////////////////////

//Grabbing the all of the priority information

$sql = "select priority from project,priority where project.id = '" . $_SESSION['project'] . "' and project.id = priority.projid";

$result = mysql_query($sql); //Run the query

while ($myrow = mysql_fetch_row($result)) 
{

	$priority[] = $myrow[0];

}

//Sets the priority of L,M,H 1,2,3 to whatever the user has selected in the priority table

$L1 = $priority[0]; 
$L2 = $priority[1];
$L3 = $priority[2];
$M1 = $priority[3];
$M2 = $priority[4];
$M3 = $priority[5];
$H1 = $priority[6];
$H2 = $priority[7];
$H3 = $priority[8];

//Initializing variables

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

//Begin code to display the component

$sql = "select category.risk, category.id, category.importance from project,component, category where project.id = '" . $_SESSION['project'] . "' and project.id = component.projid and component.id = category.compid";

$result = mysql_query($sql);

while ($myrow = mysql_fetch_row($result)) 
{

	//Code to grab the entire amount of test cases per project
	
	$sql = "select count(testcase.id) from project,component,category,testcase where project.id = '" . $_SESSION['project'] . "' and category.id='" . $myrow[1] . "' and project.id = component.projid and component.id = category.compid and category.id = testcase.catid";


	$totalTCResult = mysql_query($sql);

	$totalTCs = mysql_fetch_row($totalTCResult);

	//Code to grab the results of the test case execution

	$sql = "select tcid,status from results,project,component,category,testcase where project.id = '" . $_SESSION['project'] . "' and category.id='" . $myrow[1] . "' and project.id = component.projid and component.id = category.compid and category.id = testcase.catid and testcase.id = results.tcid order by build";

	//echo $sql . "<br>";

	$totalResult = mysql_query($sql);

	//Setting the results to an array.. Only taking the most recent results and displaying them
	
	while($totalRow = mysql_fetch_row($totalResult))
	{
		//echo $totalRow[0] . " " . $totalRow[1] . "<br>";

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

	unset($testCaseArray);

	$priStatus = $myrow[2] . $myrow[0]; //Concatenate the importance and priority together

//This next section figures out how many priority A,B and C test cases there and adds them together

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

	}elseif($priStatus == 'L3' && $L3 == "c")
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


//This next section gets the milestones information

$sql = "select name,date,A,B,C from milestone where projid='" . $_SESSION['project'] . "' and to_days(date) >= to_days(now()) order by date limit 1";

//echo $sql;

$result = mysql_query($sql); //Run the query

$numRows = mysql_num_rows($result); //How many rows

//Check to see if there are any milestone rows

if($numRows > 0)
{

	$currentMilestone = mysql_fetch_row($result);

	$Mname=$currentMilestone[0];
	$Mdate=$currentMilestone[1];
	$MA=$currentMilestone[2];
	$MB=$currentMilestone[3];
	$MC=$currentMilestone[4];

	//This next section figures out if the status is red yellow or green..

	//Priority A's

	//Check to see if milestone is set to zero. Will cause division error

	if($MA == 0 || $totalA ==0)
	{
	
		$AStatus = "-";

	}else
	{
		if(($percentCompleteA / $MA) >= .9)
		{
			$AStatus = "<font color='#669933'>GREEN</font>";

		}

		elseif(($percentCompleteA / $MA) >= .8)
		{
			$AStatus = '<font color="#FFCC00">YELLOW</font>';
		}

		else
		{
			$AStatus = '<font color="#FF0000">RED</font>';
		}
	}

	//Priority B's

	if($MB == 0 || $totalB ==0)
	{
	
		$BStatus = "-";
	}else
	{
		if(($percentCompleteB / $MB) >= .9)
		{
			$BStatus = "<font color='#669933'>GREEN</font>";

		}

		elseif(($percentCompleteB/ $MB) >= .8)
		{
			$BStatus = '<font color="#FFCC00">YELLOW</font>';
		}

		else
		{
			$BStatus = '<font color="#FF0000">RED</font>';
		}

	}

	//Priority C's

	if($MC == 0 || $totalC ==0)
	{
	
		$CStatus = "-";
	}else
	{
		if(($percentCompleteC / $MC) >= .9)
		{
			$CStatus = "<font color='#669933'>GREEN</font>";

		}

		elseif(($percentCompleteC / $MC) >= .8)
		{
			$CStatus = '<font color="#FFCC00">YELLOW</font>';
		}

		else
		{
			$CStatus = '<font color="#FF0000">RED</font>';
		}
	}
	
}else//If there are no milestone rows then I want to display this data
	{
	
		

		$Mname= "None";
		$Mdate= "None";
		$MA= "-";
		$MB= "-";
		$MC= "-";
		$AStatus= "-"; 
	    $BStatus= "-";
        $CStatus= "-";

	}

?>
