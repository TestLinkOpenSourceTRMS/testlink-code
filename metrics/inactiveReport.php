<?

////////////////////////////////////////////////////////////////////////////////
//File:     inactiveReport.php
//Author:   Chad Rosen
//Purpose:  This page reports the inactive test cases.
////////////////////////////////////////////////////////////////////////////////


require_once("../functions/header.php");

  session_start();
  doDBConnect();
  doHeader();

$sql = "select title,testcase.id,component.name, category.name, component.id, category.id from project,component,category,testcase where testcase.active='off' and project.id='" . $_SESSION['project'] . "' and project.id=component.projid and component.id=category.compid and category.id=testcase.catid order by testcase.id";

//echo $sql;

$result = mysql_query($sql);

echo "<table border=1 width=100%>";

echo "<tr><td bgcolor='#FFFFCC'><b>Inactive Test Case Report</td></tr>";

echo "</table>";

echo "<table border=1 width=100%>";

echo "<tr><td bgcolor='#CCCCCC'><b>COM Name</td><td bgcolor='#CCCCCC'><b>CAT Name</td><td bgcolor='#CCCCCC'><b>TC Title</td></tr>";

while ($myrow = mysql_fetch_row($result)) 
{
	
	//echo "<tr><td bgcolor='#EEEEEE'>";
	
//	echo "<a href='execution/frameSet.php?com=" . $myrow[8] . "&build=" . $myrow[0] . "&page=detailed&nav= > Detailed Execution' target='_parent'>" . $myrow[6] . "</td>";	

//	echo "<td bgcolor='#EEEEEE'>";
	
//	echo "<a href='execution/frameSet.php?cat=" . $myrow[9] . "&build=" . $myrow[0] . "&page=detailed&nav= > Detailed Execution' target='_parent'>" . $myrow[7] . "</td>";

//	echo "<td bgcolor='#EEEEEE'>";
	
//	echo "<a href='execution/frameSet.php?tc=" . $myrow[5] . "&build=" . $myrow[0] . "&page=detailed&nav= > Detailed Execution' target='_parent'>" . $myrow[3] . "</td>";
	
	echo "<tr><td>" . $myrow[0] . "</td>";
	//echo "<td>" . $myrow[1] . "</td>";
	echo "<td>" . $myrow[2] . "</td>";
	echo "<td>" . $myrow[3] . "</td>";

	echo "</tr>";


	
}

echo "</table>";



?>
