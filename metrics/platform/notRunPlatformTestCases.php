<?php

////////////////////////////////////////////////////////////////////////////////
//File:     execution.php
//Author:   Chad Rosen
//Purpose:  The page builds the detailed test case execution frame
////////////////////////////////////////////////////////////////////////////////

require_once("../../functions/header.php");

  session_start();
  doDBConnect();
  doHeader();
?>

<h2>Test Cases not run in any platform</h2>
<table width="100%" class='mainTable'>
<tr>
	<td class='userInfoTable' width="25%"><b>Component</td>
	<td class='userInfoTable' width="25%"><b>Category</td>
	<td class='userInfoTable' width="50%"><b>Test Case Title</td>
</tr>
<?

$sqlTestCase = "select project.id,project.name,component.id,component.name,category.id,category.name,testcase.id,testcase.title from project,component,category,testcase where project.id=" . $_SESSION['project'] . " and project.id=component.projid and component.id=category.compid and category.id=testcase.catid";

$resultTestCase = mysql_query($sqlTestCase);

while ($myrowTestCase = mysql_fetch_row($resultTestCase)) 
{ 
	$sqlPlatformResult = "select result from platformresults where tcid=" . $myrowTestCase[6];
	$resultPlatform = mysql_query($sqlPlatformResult);

	//echo $sqlPlatformResult . "<br>";
	//echo mysql_num_rows($resultPlatform) . "<br>";


	if(mysql_num_rows($resultPlatform) == 0)
	{
		echo "<tr><td>" . $myrowTestCase[3] . "</td>";
		echo "<td>" . $myrowTestCase[5] . "</td>";
		echo "<td>" . $myrowTestCase[7] . "</td></tr>";
	}
}

?>

</table>