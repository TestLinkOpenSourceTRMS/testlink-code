<?

////////////////////////////////////////////////////////////////////////////////
//File:     bugDetail.php
//Author:   Chad Rosen
//Purpose:  This page generates the bug detail reports.
////////////////////////////////////////////////////////////////////////////////


require_once("../functions/header.php");

  session_start();
  doDBConnect();
  doHeader();

?>

<LINK REL="stylesheet" TYPE="text/css" HREF="kenny.css">

<?

$sql = "select title, component.name, category.name, testcase.id,mgttcid from project,component,category,testcase where project.id='" . $_SESSION['project'] . "' and project.id=component.projid and component.id=category.compid and category.id=testcase.catid order by testcase.id";

$result = mysql_query($sql,$db);

echo "<table class=userinfotable width=100%>";

echo "<tr><td bgcolor='#FFFFCC'><b>Total Bugs For Each Test Case Report</td></tr>";

echo "</table>";

echo "<table class=userinfotable width=100%>";

echo "<tr><td bgcolor='#CCCCCC'><b>COM Name</td><td bgcolor='#CCCCCC'><b>CAT Name</td><td bgcolor='#CCCCCC'><b>Title</td><td bgcolor='#CCCCCC'><b>Bugs</td></tr>";

while ($myrow = mysql_fetch_row($result)) 
{
	
	$sqlBugs = "select bug from bugs where tcid='" . $myrow[3] . "'";
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

	}

	//if there actually was a bug string then display the data

	if($bugString != "")
	{

	echo "<tr><td bgcolor='#EEEEEE'>" . $myrow[1] . "</td>";	

	echo "<td bgcolor='#EEEEEE'>" . $myrow[2] . "</td>";

	echo "<td bgcolor='#EEEEEE'><b>" . $myrow[4] . "</b>:" . htmlspecialchars($myrow[0]) . "</td>";
	
	echo "<td>";

	echo $bugString;

	echo "</td></tr>";

	unset($bugString);

	}
	
}

echo "</table>";



?>
