<?php

////////////////////////////////////////////////////////////////////////////////
//File:     viewTC.php
//Author:   Chad Rosen
//Purpose:  This files allows users to view test cases????
////////////////////////////////////////////////////////////////////////////////

   require_once("functions/header.php");

  session_start();
  doDBConnect();
  doHeader();

?>

<LINK REL="stylesheet" TYPE="text/css" HREF="kenny.css">
<title>TestLink Test Case</title>
<a href='http://www.qagood.com/'>Log Into TestLink</a>

<?

//If the user hasn't selected anything show the instructions. This comes up by default the first time the user enters the screen

//If the user has chosen to edit a testcase then show this code

$testcase = $_GET['tc'];

if($_GET['tc'])
{

	$sqlCOMCAT = "select mgtcomponent.name, mgtcategory.name from mgtcomponent,mgtcategory,mgttestcase where mgtcomponent.id=mgtcategory.compid and mgtcategory.id=mgttestcase.catid and mgttestcase.id=" . $testcase;
	$resultCOMCAT = mysql_query($sqlCOMCAT,$db);
	$myrowCOMCAT = mysql_fetch_row($resultCOMCAT);



	$sqlTC = "select id,title,summary,steps,exresult,version,keywords from mgttestcase where id='" . $testcase . "'";
	$resultTC = mysql_query($sqlTC,$db);
	$myrowTC = mysql_fetch_row($resultTC);

	if ( ! $myrowTC) {
		echo "<table width='65%' class=tctable align=center>";
		echo "<tr><td class=tctable>";
		echo "<font color=red size=+2>Sorry!</font><br>TestLink was unable to locate the requested test case.";
		echo "</table>";
		exit;
	} 

	echo "<table width='65%' class=tctable align=center>";
	
	echo "<tr><td class=tctablehdr>";
	
	echo "<b>Component:</b>" . $myrowCOMCAT[0] . "<br>";
	echo "<b>Category:</b>" . $myrowCOMCAT[1] . "<br>";
	
	echo "<b>Test Case " . $testcase . ":</b> " . $myrowTC[1] . "</td></tr>";
	
    echo "<input type='hidden' name='id' value='" . $testcase . "'>";
	echo "<input type='hidden' name='version' value='" . $myrowTC[5] . "'>";
	
	echo "</td></tr>";
	
	echo "<tr><td class=tctable><b>Summary:</b><br>" . nl2br($myrowTC[2]) . "</td></tr>";

	echo "<tr><td class=tctable><b>Steps:</b><br>" . nl2br($myrowTC[3]) . "</td></tr>";
	
	echo "<tr><td class=tctable><b>Expected Result:</b><br>" . nl2br($myrowTC[4]) . "</td></tr>";
		
	echo "<tr><td class=tctable><b>Keywords:</b><br>";
	
	echo $myrowTC[6];
	
	echo "</td></tr>";

	//Check if there are any projects created that this test case is part of
	
	$sqlProj = "select project.name, project.id from project,component,category,testcase where project.id=component.projid and component.id=category.compid and category.id=testcase.catid and testcase.mgttcid=" . $testcase . " group by project.name";

	$sqlProjResult = mysql_query($sqlProj,$db);
	
	//Grab the number of results
	
	$numProjects = mysql_num_rows($sqlProjResult);

	if($numProjects > 0)
	{

		echo "<FORM NAME='projectForm' ACTION='viewTC.php'><tr><td class=tctable>View This Test Case's Results:";

		echo "<select name=project>";

		while ($myrowProj = mysql_fetch_row($sqlProjResult))
		{
			if($myrowProj[1] == $_GET['project'])
			{

				echo "<option value=" . $myrowProj[1] . " SELECTED>" . $myrowProj[0] . "</option>";

			}
			else
			{
				
				echo "<option value=" . $myrowProj[1] . ">" . $myrowProj[0] . "</option>";
			}

		}

		echo "</select>";

		echo "<input type='submit' name='submitForm' value='Display Results'>";

		echo "<input type='hidden' name='tc' value='" . $testcase . "'>";
		
		echo "</td></tr></form>";

	}//end if($numProjects)

	echo "</table>";

	if($_GET['project'])
	{
		
		$sqlTCRes = "select status,build,results.notes,testcase.id,daterun from project,component,category,testcase,results where project.id=component.projid and component.id=category.compid and category.id=testcase.catid and testcase.id = results.tcid and testcase.mgttcid='" . $testcase . "' and project.id='" . $_GET['project'] . "' order by build asc";

		$sqlTCR = mysql_query($sqlTCRes,$db);

		echo "<table width='65%' class=tctable align='center'>";
		echo "<tr><td class=tctablehdr><b>Build</td><td class=tctablehdr><b>Result</td><td class=tctablehdr><b>Notes</td><td class=tctablehdr><b>Date Run</td><td class=tctablehdr><b>Bugs</td></tr>";

		while ($myrowTCR = mysql_fetch_row($sqlTCR))
		{	

			if($myrowTCR[0] == 'p')
			{
				$result = 'passed';

			}elseif($myrowTCR[0] == 'f')
			{
				$result = 'failed';

			}elseif($myrowTCR[0] == 'b')
			{

				$result = 'blocked';

			}


			echo "<tr><td class=tctable>" . $myrowTCR[1] . "</td><td class=tctable>" . $result . "</td><td class=tctable>" . $myrowTCR[2]. "</td><td class=tctable>" . $myrowTCR[4] . "</td><td class=tctable>";
			
			$sqlBugs = "select bug from bugs where tcid='" . $myrowTCR[3] . "' and build='" . $myrowTCR[1] . "'";
			$sqlBugRes = mysql_query($sqlBugs,$db);

			while ($myrowBugs = mysql_fetch_row($sqlBugRes))
			{
				

				
				//echo "<a href='http://box.good.com/bugzilla/show_bug.cgi?id=" . $myrowBugs[0] . "' target='_newWindow'>" . $myrowBugs[0] . "</a>,";

				//Mike asked if I could strike through all bugs that have a resolved, verified, or closed status.. Below is the code to do it

				//connect to pesky
				
				$dbPesky = mysql_connect("pesky.good.com", "dvanhorn" , "dvanhorn");

				mysql_select_db("bugs",$dbPesky);
			
				//query bugzilla to find out if the status of the bug is verified, resolved, or closed..

				$statusQuery = "select bug_status from bugs where bug_id=" . $myrowBugs[0];

				//run the query

				$statusResult = mysql_query($statusQuery,$dbPesky);
				
				//fetch the data

				$status = mysql_fetch_row($statusResult);
			
				
				//Check what the status is.. If it's the line below then strike through the bug


				if('RESOLVED' == $status[0] || 'VERIFIED' == $status[0] || 'CLOSED' == $status[0])
				{

					$bugString .= "<a href='http://box.good.com/bugzilla/show_bug.cgi?id=" . $myrowBugs[0] . "' target='_newWindow'><s>" . $myrowBugs[0] . "</s></a>,";

				//if the bug is still open the display it normally

				}else
				{

					$bugString .= "<a href='http://box.good.com/bugzilla/show_bug.cgi?id=" . $myrowBugs[0] . "' target='_newWindow'>" . $myrowBugs[0] . "</a>,";


				}



			}


			echo $bugString;


			echo "</td></tr>";



		}

		

		echo "</table>";




	}

}
else
{

	//If the user has entered a test case number that doesnt exist show this message

	echo "You have entered an invalid test case number";


}


?>
