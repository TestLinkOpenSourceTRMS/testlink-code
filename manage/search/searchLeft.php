<?

////////////////////////////////////////////////////////////////////////////////
//File:     searchLeft.php
//Author:   Chad Rosen
//Purpose:  This page is the left frame of the search pages. It builds the
//	    javascript trees that allow the user to jump around to any point
//	    on the screen
////////////////////////////////////////////////////////////////////////////////


require_once("../../functions/header.php");
doSessionStart();
doDBConnect();
doHeader();

		//This whole block of code displays the product selection. I leave it outside of the code below because I want it to show up at all times
	

		//Query to select all products

		$sqlPROD = "select id, name from mgtproduct";

		$resultPROD = mysql_query($sqlPROD); //execute query


		//Begin to display the table

		echo "<form method='post' ACTION='manage/search/searchData.php' target='mainFrame'>";
		echo "<table width='100%' class=searchtable>";
		echo "<tr><td class=searchtablehdr colspan=2>Search Test Cases</td></tr>";
		echo "<tr><td width='20%' class=searchtable>Product<td class=searchtable>";

		//Start a select to hold all of the products
		
		echo "<SELECT NAME='product'>";
		
		while ($myrow = mysql_fetch_row($resultPROD)) 
		{

			//This if statement checks to see if the user has already selected a product.
			//Note: For some reason i use both product and prodID.. I probably should figure out why


			if($_POST['product'] == $myrow[0] || $_POST['prodID'] == $myrow[0]) //If they have
			{

				//Select that option in the dropdown

				echo "<OPTION VALUE='" . $myrow[0] ."' SELECTED>" . $myrow[1];

			}else //if not
			{
				//Just display the option

				echo "<OPTION VALUE='" . $myrow[0] ."'>" . $myrow[1];

			}//end else

		}//END WHILE

		echo "</SELECT></td>";
		
		echo "<tr><td class=searchtable>TC ID</td><td class=searchtable><input size=15 name=TCID></tr>";
		echo "<tr><td class=searchtable>Title</td><td class=searchtable><input size=35 name=title></tr>";		
		echo "<tr><td class=searchtable>Summary</td><td class=searchtable><input size=35 name=summary></tr>";
		echo "<tr><td class=searchtable>Steps</td><td class=searchtable><input size=35 name=steps></tr>";
		echo "<tr><td class=searchtable>Expected Result</td><td class=searchtable><input size=35 name=exresult></tr>";
		
		//This block of code displays the keywords dropdown box
		
		echo "<tr><td class=searchtable>Key</td><td class=searchtable>";
		
		$sqlKEY = "select keyword from keywords";
		$resultKEY = mysql_query($sqlKEY); //execute query

		echo "<select name=key>";

		echo "<option value=none>None</option>";
		
		while ($myrowKEY = mysql_fetch_row($resultKEY)) 
		{
			echo "<option value=" . $myrowKEY[0] . ">" . $myrowKEY[0] . "</option>";

		}

		echo "</select>";
		
		echo "</td></tr>";
		echo "<tr><td class=searchtable><td class=searchtable><input type='submit' value='Search' name='submit'></td></tr></table>";
		
		echo "</form>";		
