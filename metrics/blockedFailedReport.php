<?

////////////////////////////////////////////////////////////////////////////////
//File:     blockedFailedReport.php
//Author:   Chad Rosen
//Purpose:  This page generates the reports for the blocked and failed
//          test cases 
////////////////////////////////////////////////////////////////////////////////


require_once("../functions/header.php");

  session_start();
  doDBConnect();
  doHeader();

?>

<LINK REL="stylesheet" TYPE="text/css" HREF="kenny.css">

<?

$rights = has_rights("tp_execute"); //something weird is going on with my rights program.. I had to add this here
//because I wanted to check rights as a workaround

$type = $_GET['type'];

if($type == 'f')
{

	$reportName = 'Failed';
}
else
{

	$reportName = 'Blocked';

}

//Setting up UI

echo "<table class=userinfotable width=100%>";

echo "<tr><td bgcolor='#FFFFCC'><b>" . $reportName . " Test Case Report</td></tr>";

echo "</table>";

echo "<table class=userinfotable width=100%>";

echo "<tr><td bgcolor='#CCCCCC'><b>COM Name</td><td bgcolor='#CCCCCC'><b>CAT Name</td><td bgcolor='#CCCCCC'><b>Title</td><td bgcolor='#CCCCCC'><b>Build</td><td bgcolor='#CCCCCC'><b>Run By</td><td bgcolor='#CCCCCC'><b>Date Run</td><td bgcolor='#CCCCCC'><b>Notes</td><td bgcolor='#CCCCCC'><b>Bugs</td></tr>";

//SQL to select the most current status of all the current test cases

$sql = "select tcid,status,build from results,project,component,category,testcase where project.id = '" . $_SESSION['project'] . "' and project.id = component.projid and component.id = category.compid and category.id = testcase.catid and testcase.id = results.tcid order by tcid,build";

$totalResult = mysql_query($sql,$db);

while($totalRow = mysql_fetch_row($totalResult))
	{
		//echo $totalRow[0] . " " . $totalRow[1] . "<br>";

		//This is a test.. I've got a problem if the user goes and sets a previous p,f,b value to a 'n' value. The program then sees the most recent value as an not run. I think we want the user to then see the most recent p,f,b value
		
		///This statement builds an array with the most current status

		if($totalRow[1] == 'n')
		{

		}
		else
		{
		
		$testCaseResArray[$totalRow[0]] = $totalRow[1];

		
		}

		//This statement builds an array with the most current test case numbers
		
		if($totalRow[1] == 'n')
		{

		}
		else
		{
		
		$testCaseNumArray[$totalRow[0]] = $totalRow[0];

		
		}

		//This statement builds an array with the most current builds
		
		if($totalRow[1] == 'n')
		{

		}
		else
		{
		
		$testCaseBuildArray[$totalRow[0]] = $totalRow[2];

		
		}

	}

	//I was getting errors if there was no array so I had to add an if statement first

	if($testCaseNumArray)
	{
	
		sort($testCaseNumArray); //sort the array so that test cases appear by component

		//Looping through all of the test cases that we found

	foreach($testCaseNumArray as $testCaseStatus)
	{

		//Grab information about the component,category,testcase, and result
		
		$sql = "select build,runby,daterun,title,results.notes,component.name, category.name, component.id, category.id,mgttcid from component,category,testcase,results where component.id=category.compid and category.id=testcase.catid and results.tcid=testcase.id and testcase.id='" . $testCaseStatus . "' and results.build='" . $testCaseBuildArray[$testCaseStatus] . "'";

		$result = mysql_query($sql,$db);

		$myrow = mysql_fetch_row($result);

		//we only want to display test cases that are blocked

		if($testCaseResArray[$testCaseStatus] == $type)
		{

			//Display the component with a hyperlink to the execution pages

			echo "<tr><td bgcolor='#EEEEEE'>";
		
			echo $myrow[5] . "</td>";	

			//Display the category with a hyper link to the execution pages

			echo "<td bgcolor='#EEEEEE'>";
		
			echo $myrow[6] . "</td>";
		
			echo "<td bgcolor='#EEEEEE'>";

			//Display the test case with a hyper link to the execution pages

			if($rights)
			{
				echo "<a href='execution/execution.php?keyword=All&edit=testcase&tc=" . $testCaseStatus . "&build=" . $testCaseBuildArray[$testCaseStatus] . "' target='_blank'>" . "<b>" . $myrow[9] . "</b>:" . htmlspecialchars($myrow[3]);//test case title

			}else

			{

				echo "<b>" . $myrow[9] . "</b>:" . htmlspecialchars($myrow[3]); //test case title


			}

			echo "</td>";
			
			
		
			echo "<td>" . $testCaseBuildArray[$testCaseStatus] . "</td>"; //Build
			echo "<td>" . $myrow[1] . "</td>"; //Run By
			echo "<td>" . $myrow[2] . "</td>"; //Date run
			echo "<td>" . $myrow[4] . "</td>"; //notes

			//Grab all of the bugs for the test case in the build
	
			echo "<td>";

			$sqlBugs = "select bug from bugs where tcid='" . $testCaseStatus . "' and build='" . $testCaseBuildArray[$testCaseStatus] . "'";
			
			$resultBugs = mysql_query($sqlBugs,$db);
	
			while ($myrowBug = mysql_fetch_row($resultBugs)) 
			{

						
				//strike through all bugs that have a resolved, verified, or closed status.. Below is the code to do it

				//query bugzilla to find out if the status of the bug is verified, resolved, or closed..

				if($bugzillaOn == true) //check to see if the user has turned on the bugzillaOn variable in the header file 
				{

					$statusQuery = "select bug_status from bugs where bug_id=" . $myrowBug[0];

					//run the query

					$statusResult = mysql_query($statusQuery,$dbPesky);
				
					//fetch the data

					$status = mysql_fetch_row($statusResult);

				
			
				
				//Check what the status is.. If it's the line below then strike through the bug

				$bugString .= "<a href='" . $bzUrl . $myrowBug[0] . "' target='_newWindow'>";

				if('RESOLVED' == $status[0] || 'VERIFIED' == $status[0] || 'CLOSED' == $status[0])
				{

					$bugString .= "<s>" . $myrowBug[0] . "</s></a>,";


				//if the bug is still open the display it normally

				}else
				{

					$bugString .= $myrowBug[0] . "</a>,";



				}


				}else //end if bugzillaOn == true
				{

					//if the user didn't choose to turn on bugzilla

					$bugString .= $myrowBug[0] . ",";



				}

			}//end while loop

		
			echo $bugString; //print the list of bugs to the screen

			unset($bugString); //destroy the variable so that the next round doesnt get the old data

			echo "</td></tr>";

		}//end for each


	}//end if


	

		

	}

?>
