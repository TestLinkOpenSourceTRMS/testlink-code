<?php
////////////////////////////////////////////////////////////////////////////////
//File:     totalTestCase.php
//Author:   Chad Rosen
//Purpose:  This page that views metrics by individual test case.
////////////////////////////////////////////////////////////////////////////////
  

require_once("../functions/header.php");

	session_start();
  	doDBConnect();
    doHeader();
//Generate date info
$year =  date("Y");
$month = date("m");
$date = date("d");
$hour = date("H");
$minute = date("i");
$second = date("s");
print "
<table>
<tr>
<td bgcolor='#EE0000'><b>DATE:</b></td> 
<td bgcolor='999999'>$year/$month/$date $hour:$minute $second </td>
</tr>
</table> \n
";

print "
<table class=userinfotable>
<tr>
<td bgcolor='#CCCCCC' width='15%'>ID</td> 
<td bgcolor='#CCCCCC' width='15%'>Component</td> 
<td bgcolor='#CCCCCC' width='30%'>Category</td> 
<td bgcolor='#CCCCCC' width='30%'>Test Case</td> 
";
//print "session = " . $_SESSION['project']. "\n";
$sql = "select build from build,project where project.id = '" . $_SESSION['project'] . "' and project.id=build.projid";
//$sql = "select build from build,project where project.id=123 and project.id=build.projid";

//print $sql;

//Begin code to display the component

$result = mysql_query($sql);

while ($myrow = mysql_fetch_row($result)) 
{

	print "<td bgcolor='#99CCFF'>" . $myrow[0] . "</td> ";

}

print "</tr> \n";

$sql = "select component.name,category.name, testcase.title, testcase.id,mgttcid from project,component,category,testcase where project.id='" . $_SESSION['project'] . "' and component.projid=project.id and category.compid=component.id and testcase.catid=category.id";
//$sql = "select component.name,category.name, testcase.title, testcase.id,mgttcid from project,component,category,testcase where project.id=123 and component.projid=project.id and category.compid=component.id and testcase.catid=category.id";


$result = mysql_query($sql);

while ($myrow = mysql_fetch_row($result)) //Cycle through all of the test cases
{

	print 	"<tr><td bgcolor='#EEEEEE'><b>" . 
		$myrow[4] . 
		"</b></td><td bgcolor='#EEEEEE'>" . 
		$myrow[0] . 
		"</td><td bgcolor='#EEEEEE'>" . 
		$myrow[1] . 
		"</td><td bgcolor='#EEEEEE'>" .
		htmlspecialchars($myrow[2]) . 
		"</td>";
	
	$sqlBuild = "select build from build,project where project.id = '" . $_SESSION['project'] . "' and project.id=build.projid";
//	$sqlBuild = "select build from build,project where project.id=123 and project.id=build.projid";

	$resultBuild = mysql_query($sqlBuild);

	while ($myrowBuild = mysql_fetch_row($resultBuild)) //Cycle through all the builds
	{
		
		$sqlStatus = "select status from testcase,results where results.tcid='" . $myrow[3] . "' and results.build='" . $myrowBuild[0]  . "' and testcase.id=results.tcid";

		//print $sqlStatus;

		$resultStatus = mysql_query($sqlStatus);

		$myrowStatus = mysql_fetch_row($resultStatus);

	

		if($myrowStatus[0] == "p" || $myrowStatus[0] == "f" || $myrowStatus[0] == "b")
		{
			
			//This displays the pass,failed or blocked test case result
			//The hyperlink will take the user to the test case result in the execution page

			print "<td> ";

			/*	
			if(has_rights("tp_execute"))
			{

				print "<a href='execution/execution.php?keyword=All&edit=testcase&tc=" . $myrow[3] . "&build=" . $myrowBuild[0] . "' target='_blank'>";

			}
			*/
			print $myrowStatus[0] . "</a></td> ";


		}
		else
		{
			print "<td>-</td> ";
		}
		


	}

	print "</tr> \n";

}

?>

