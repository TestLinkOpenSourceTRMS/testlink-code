<?

////////////////////////////////////////////////////////////////////////////////
//File:     importLeft.php
//Author:   Chad Rosen
//Purpose:  This page is the left frame of the execution pages. It builds the
//   	    javascript trees that allow the user to jump around to any point
//	    on the screen
////////////////////////////////////////////////////////////////////////////////


require_once("../functions/header.php");
doSessionStart();
doDBConnect();
doHeader();
require_once("../functions/stripTree.php"); //require_once the function that strips the javascript tree


?>

<head>
<script language='JavaScript' src='jtree/tree.js'></script>
<script language='JavaScript' src='jtree/tree_tpl.js'></script>
<link rel="stylesheet" href="jtree/tree.css">
</head>

<?

		//This whole block of code displays the product selection. I leave it outside of the code below because I want it to show up at all times
	

		//Query to select all products

		$sqlPROD = "select id, name from mgtproduct";

		$resultPROD = mysql_query($sqlPROD); //execute query

		$numPROD = mysql_num_rows($resultPROD); //check to see if there are any existing products

if($numPROD > 0) //if there are existing products then allow the user to import
{

		//Begin to display the table

		echo "<form method='post' ACTION='manage/importLeft.php'>";

		echo "<table width='100%' class=userinfotable>";
		echo "<tr><td width='50%' bgcolor='#CCCCCC'><b>Select Product</td><td bgcolor='#CCCCCC'><b>Show Test Cases</td></tr>";

		echo "<tr><td width='50%'>";

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

		echo "<td><input type='submit' value='submit' name='submit'></td></tr>";

		echo "</table>";
		
		echo "</form>";

}else //else show them this warning screen
{

	echo "There currently are no existing products. Please ask an administrator to create one for you";


}


if($_POST['submit']) 
	{

	
		//this next block of code shows the keywords selection table. I don't show it on default because the keywords are contingent on the product

		//Select all of the keywords for the chosen product
		$sqlKEY = "select id, keyword from keywords where prodid='" . $_POST['product'] . "' order by keyword";

		$resultKEY = mysql_query($sqlKEY); //execute query

		echo "<form method='post' ACTION='manage/importLeft.php'>";

		echo "<table width='100%' class=userinfotable>";
		
		echo "<tr><td width='50%' bgcolor='#EEEEEE'><b>Import By Keyword</td><td bgcolor='#EEEEEE'><b>Sort Cases</td></tr>";

		echo "<tr><td width='50%'>";

		echo "<SELECT NAME='keyword'>";

		echo "<OPTION VALUE='NONE'>NONE</OPTION>";
		
		while ($myrowKEY = mysql_fetch_row($resultKEY)) 
		{
			
			if($_POST['keyword'] == $myrowKEY[1])
			{
				echo "<OPTION VALUE='" . $myrowKEY[1] ."' SELECTED>" . $myrowKEY[1];

			}else
			{
			
				echo "<OPTION VALUE='" . $myrowKEY[1] ."'>" . $myrowKEY[1];

			}

		}//END WHILE

		echo "</SELECT></td>";

		echo "<td>";
		
		echo "<input type='hidden' value='" . $_POST['product'] . "' name='product'>";
		
		echo "<input type='submit' value='submit' name='sort'></td></tr>";

		echo "</table>";
		
		echo "</form>";
		


//////////////////////////////////////////////////////////////Start the display of the components
		
		//The query to grab all of the products

		$sqlPROD = "select id, name from mgtproduct where id=" . $_POST['product'] . " order by name";
		$resultPROD = mysql_query($sqlPROD);

		echo "<script language='JavaScript'> var TREE_ITEMS = [\n\n";

		while ($myrowPROD = mysql_fetch_row($resultPROD)) //loop through all products
		{

			//Code to strip commas and apostraphies
				
			$name = stripTree($myrowPROD[1]); //function that removes harmful characters

			echo "['" . $name . "',null,\n\n";

			//Displays the component info

			$sqlCOM = "select id, name from mgtcomponent where prodid='" . $myrowPROD[0] . "' order by name";
			$resultCOM = mysql_query($sqlCOM);
			
			while ($myrowCOM = mysql_fetch_row($resultCOM)) //loop through all Components
			{

				//Code to strip commas and apostraphies

				$name = stripTree($myrowCOM[1]); //function that removes harmful characters
				
				echo "['" . $name . "','manage/importData.php?key=NONE&edit=component&com=" . $myrowCOM[0] . "',\n\n";

				//Displays the category info

				$sqlCAT = "select id, name from mgtcategory where compid='" . $myrowCOM[0] . "' order by CATorder,id";
				$resultCAT = mysql_query($sqlCAT);

				while ($myrowCAT = mysql_fetch_row($resultCAT)) //loop through all Categories
				{

					//Code to strip commas and apostraphies
				
					$name = stripTree($myrowCAT[1]); //function that removes harmful characters
					
					echo "['" . $name . "','manage/importData.php?key=NONE&edit=category&cat=" . $myrowCAT[0] . "'],\n\n";
			
				
				}

				echo "],"; //end the component block


			}

			echo "]"; //end the product block

		}

		echo "];</script>\n\n"; //end the whole tree


	echo "<script language='JavaScript'>";
	echo "new tree (TREE_ITEMS, TREE_TPL);";
	echo "</script>";

}


 //Commenting out until I can fix this
elseif($_POST['sort'])
{
		//echo $_

		//this next block of code shows the keywords selection table. I don't show it on default because the keywords are contingent on the product

		//Select all of the keywords for the chosen product
		$sqlKEY = "select id, keyword from keywords where prodid='" . $_POST['product'] . "' order by keyword";

		$resultKEY = mysql_query($sqlKEY); //execute query

		echo "<form method='post' ACTION='manage/importLeft.php'>";

		echo "<table width='100%' class=userinfotable>";
		
		echo "<tr><td width='50%' bgcolor='#EEEEEE'><b>Sort By Keyword</td><td bgcolor='#EEEEEE'><b>Sort Cases</td></tr>";

		echo "<tr><td width='50%'>";

		echo "<SELECT NAME='keyword'>";

		echo "<OPTION VALUE='NONE'>NONE</OPTION>";
		
		while ($myrowKEY = mysql_fetch_row($resultKEY)) 
		{
			if($_POST['keyword'] == $myrowKEY[1])
			{
				echo "<OPTION VALUE='" . $myrowKEY[1] ."' SELECTED>" . $myrowKEY[1];

			}else
			{
			
				echo "<OPTION VALUE='" . $myrowKEY[1] ."'>" . $myrowKEY[1];

			}

		}//END WHILE

		echo "</SELECT></td>";

		echo "<td>";
		
		echo "<input type='hidden' value='" . $_POST['product'] . "' name='product'>";
		
		echo "<input type='submit' value='submit' name='sort'></td></tr>";

		echo "</table>";
		
		echo "</form>";


//////////////////////////////////////////////////////////////Start the display of the components
		
		//The query to grab all of the products

		$sqlPROD = "select id, name from mgtproduct where id=" . $_POST['product'] . " order by name";
		$resultPROD = mysql_query($sqlPROD);

		echo "<script language='JavaScript'> var TREE_ITEMS = [\n\n";

		while ($myrowPROD = mysql_fetch_row($resultPROD)) //loop through all products
		{

			//Code to strip commas and apostraphies
				
			$name = stripTree($myrowPROD[1]); //function that removes harmful characters
			
			echo "['" . $name . "','',\n\n";

			//Displays the component info

			$sqlCOM = "select id, name from mgtcomponent where prodid='" . $myrowPROD[0] . "' order by name";
			$resultCOM = mysql_query($sqlCOM);
			
			while ($myrowCOM = mysql_fetch_row($resultCOM)) //loop through all Components
			{

				//Code to strip commas and apostraphies
				
				$name = stripTree($myrowCOM[1]); //function that removes harmful characters
				
				echo "['" . $name . "','manage/importData.php?key=" . $_POST['keyword'] . "&edit=component&com=" . $myrowCOM[0] . "',\n\n";

				//Displays the category info

				$sqlCAT = "select id, name from mgtcategory where compid='" . $myrowCOM[0] . "' order by CATorder";
				$resultCAT = mysql_query($sqlCAT);

				while ($myrowCAT = mysql_fetch_row($resultCAT)) //loop through all Categories
				{

					//Code to strip commas and apostraphies

					$name = stripTree($myrowCAT[1]); //function that removes harmful characters
					
					echo "['" . $name . "','manage/importData.php?key=" . $_POST['keyword'] . "&edit=category&cat=" . $myrowCAT[0] . "'],\n\n";
			
				
				}

				echo "],"; //end the component block


			}

			echo "]"; //end the product block

		}

		echo "];</script>\n\n"; //end the whole tree


	echo "<script language='JavaScript'>";
	echo "new tree (TREE_ITEMS, TREE_TPL);";
	echo "</script>";



}

?>
