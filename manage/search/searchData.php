<?php

////////////////////////////////////////////////////////////////////////////////
//File:     searchData.php
//Author:   Chad Rosen
//Purpose:  This page presents the search results.
////////////////////////////////////////////////////////////////////////////////

require_once("../../functions/header.php");
doSessionStart();
doDBConnect();
doHeader();

// If the user hasn't selected anything show the instructions. This
// comes up by default the first time the user enters the screen

if(!$_POST['submit'])
{

echo "<table width=100% class=helptable>";

echo "<tr><td class=helptablehdr>Test Case Search Page</td></tr>";

echo "</table><table width=100% class=helptable>";

echo "<tr><td class=helptablehdr>Purpose:</td>";

echo "<td class=helptable><ul><li>The New/Edit Page is a place where a user can edit any of the existing product, component, category, and test case information.<li>The user also has the ability to create new components, categories, and testcases. </td>";

echo "<tr><td class=helptablehdr><b>To edit:</td>";

echo "<td class=helptable><ol><li>Select a product, component, category, or test case on the tree view on the left<li>Edit the information on right<li>Select the edit button at the bottom of the right window</li></ol></td></tr>";

echo "<tr><td class=helptablehdr><b>Create New:</td>";

echo "<td class=helptable><ol><li>Select a product, component, category, or test case on the tree view on the left<li>Click the new button in the left windowt<li>Enter information in the table in the right window.<li>Click the submit button at the bottom of the window on the right</li></ol></td></tr>";

echo "</table>";

}

if($_POST['submit'])
{

	//Assign the values of the posts to variables

	$prodID = $_POST['product'];
	$title = $_POST['title'];
	$summary = $_POST['summary'];
	$steps = $_POST['steps'];
	$exresult = $_POST['exresult'];
	$key = $_POST['key'];
	$TCID = $_POST['TCID'];

	$sqlCOM = "select id,name from mgtcomponent where prodid='" . $prodID ."' order by name"; 

	$resultCOM = mysql_query($sqlCOM);

	echo "<table class=searchtable width=100%><tr><td class=searchtablehdr>Search Results</table><P>";
	
	while ($myrowCOM = mysql_fetch_row($resultCOM)) //Cycle through all of the components
	{
		$sqlCAT = "select id,name from mgtcategory where compid='" . $myrowCOM[0] ."' order by name";
		$resultCAT = mysql_query($sqlCAT);

		while ($myrowCAT = mysql_fetch_row($resultCAT)) //loop through all categories
		{
			
			//Check to see if the user decided to sort by a key or chose none

			if($key == 'none')
			{
				$sqlTC = "select id,title,summary,steps,exresult,keywords,version from mgttestcase where id like '%" . $TCID . "%' and catid ='" . $myrowCAT[0] ."' and title like '%" . $title . "%' and summary like '%" . $summary . "%' and steps like '%" . $steps . "%' and exresult like '%" . $exresult . "%' order by title";

			}else
			{			
			
				$sqlTC = "select id,title,summary,steps,exresult,keywords,version from mgttestcase where id like '%" . $TCID . "%' and catid ='" . $myrowCAT[0] ."' and title like '%" . $title . "%' and summary like '%" . $summary . "%' and steps like '%" . $steps . "%' and exresult like '%" . $exresult . "%' and keywords like '%" . $key . "%' order by title";

			}

			$resultTC = mysql_query($sqlTC); //execute the query

			while ($myrowTC = mysql_fetch_row($resultTC)) //loop through all products
			{
			
				echo "<table class=tctable width=100%>";
				
				echo "<tr>";

				//display title of component, category, and test case

				echo "<tr><td class=tctablehdr>";
				
				//Display component
				
				echo "<b>Component:</b> " . $myrowCOM[1] . "<br>";

				//display category name

				echo "<b>Category:</b> " . $myrowCAT[1] . "<br>";

				//display test case name

				echo "<b>Test Case ";
				
				if(has_rights("mgt_modify_tc"))
				{
				
					echo "<a href='manage/editData.php?editTC=testcase&data=" . $myrowTC[0] . "' target='editTC'>";
				
				}

				echo $myrowTC[0] . "</a>:</b> " . htmlspecialchars($myrowTC[1]) . "</td></tr>";

				
				//display summary

				echo "<tr><td class=tctable><b>Summary:</b><br>" . htmlspecialchars(nl2br($myrowTC[2])) . "</td></tr>";				
				
				//display steps
				
				echo "<tr><td class=tctable><b>Steps:</b><br>";
				echo nl2br($myrowTC[3]) . "</td></tr>";
				
				//display expected results

				echo "<tr><td class=tctable><b>Expected Results:</b><br>";
				echo nl2br($myrowTC[4]) . "</td></tr>";
				

				//This section will display all the keywords available for the product and highlight the ones
				//that this test case uses

				echo "<tr><td class=tctable><a href='manage/keyword/viewKeywords.php?product=" . $prodID . "' target='_blank'><b>Keywords:</a></b><br>";

				//Chop the trailing comma off of the end of the keywords field

				$keywords = substr("$myrowTC[5]", 0, -1); 

				echo $keywords;		
				
				
				//echo "<input type=hidden name='break" . $myrowTC[0] . "' value='break'>";

				echo "</td></tr>";


				

				echo "</table><br>";
	
			}


		}



	}


	echo "</font>";


}

?>
