<?

////////////////////////////////////////////////////////////////////////////////
//File:     executionFrameLeft.php
//Author:   Chad Rosen
//Purpose:  This function is needed to strip the javascript tree. If the tree
//          has any of these characters in its display, it will crash
////////////////////////////////////////////////////////////////////////////////

function stripTree($name)
{

	$name = str_replace ( ",", " ", $name); //remove comma
	$name = str_replace ( "'", "", $name); //remove apostraphy
	$name = str_replace ( "\n", "", $name); //remove newline
	$name = str_replace ( "\r", "", $name); //remove returns
	$name = str_replace ( "<", "", $name); //remove bracket
	$name = str_replace ( ">", "", $name); //remove bracket


	return $name;

}

//
// genManageTree
// Generate a management tree for a product.
// Display counts.
//
// Caller provides the "product id" and the "linkto" portion of the URL
// In order for this to work, the different elements in the navigation
// tree must have a common URL mechanism.
//
// If "hidetc" is 1, then the individual test cases will be hidden from
// the tree.  Otherwise, they will be shown.
//
function genManageTree($product, $linkto, $hidetc, $archive)
{
	// Queries to determine how many total test cases
	// there are by product. The number is then displayed
	// next to the product

	$sqlProdCount = "select count(mgttestcase.id) from mgtproduct,mgtcomponent,mgtcategory,mgttestcase where mgtproduct.id = mgtcomponent.prodid and mgtcomponent.id=mgtcategory.compid and mgtcategory.id=mgttestcase.catid and mgtproduct.id='" . $product . "'";

	$resultProdCount = mysql_query($sqlProdCount);

	$prodCount = mysql_fetch_row($resultProdCount);

	
	// Begin the section of the code that displays all of the
    // product, component, category, testcases
	//The query to grab all of the products

	$sqlPROD = "select id, name from mgtproduct where id='" . $product . "' order by name";
	$resultPROD = mysql_query($sqlPROD);
	$myrowPROD = mysql_fetch_row($resultPROD);

	echo "<script language='JavaScript'> var TREE_ITEMS = [\n\n";

		//Code to strip commas and apostraphies

		$name = stripTree($myrowPROD[1]);

		echo "['" . $name . " (" . $prodCount[0] . ")','" . $linkto . "?edit=product&data=" . $myrowPROD[0] . "',\n\n";

		//Displays the component info

		$sqlCOM = "select id, name from mgtcomponent where prodid='" . $myrowPROD[0] . "' order by name";
		$resultCOM = mysql_query($sqlCOM);
		
		while ($myrowCOM = mysql_fetch_row($resultCOM)) //loop through all Components
		{

			//Queries to determine how many total test cases there are by Component. The number is then displayed next to the component


			$sqlCOMCount = "select count(mgttestcase.id) from mgtcomponent,mgtcategory,mgttestcase where mgtcomponent.id=mgtcategory.compid and mgtcategory.id=mgttestcase.catid and mgtcomponent.id='" . $myrowCOM[0] . "'";

			$resultCOMCount = mysql_query($sqlCOMCount);
			
			$COMCount = mysql_fetch_row($resultCOMCount);
		

			//Code to strip commas and apostraphies
			
			$name = stripTree($myrowCOM[1]);

			echo "['" . $name . " (" . $COMCount[0] . ")','" . $linkto . "?prodid=" . $myrowPROD[0] . "&edit=component&data=" . $myrowCOM[0] . "',\n\n";


			//Displays the category info

			$sqlCAT = "select id, name from mgtcategory where compid='" . $myrowCOM[0] . "' order by CATorder,id";
			$resultCAT = mysql_query($sqlCAT);

			while ($myrowCAT = mysql_fetch_row($resultCAT)) //loop through all Categories
			{


				//Queries to determine how many total test cases there are by Category. The number is then displayed next to the Category


				$sqlCATCount = "select count(mgttestcase.id) from mgtcategory,mgttestcase where mgtcategory.id=mgttestcase.catid and mgtcategory.id='" . $myrowCAT[0] . "'";

				$resultCATCount = mysql_query($sqlCATCount);
			
				$CATCount = mysql_fetch_row($resultCATCount);
	
				//Code to strip commas and apostraphies
			
				$name = stripTree($myrowCAT[1]);

				echo "['" . $name . " (" . $CATCount[0] . ")','" . $linkto . "?prodid=" . $myrowPROD[0] . "&edit=category&data=" . $myrowCAT[0] . "',\n\n";

				if ($hidetc == 0) {
					//Displays the Test Case info
				
					$sqlTC = "select id, title from mgttestcase where catid='" . $myrowCAT[0] . "' order by TCorder,id";
					$resultTC = mysql_query($sqlTC);

					while ($myrowTC = mysql_fetch_row($resultTC)) //loop through all Test cases
					{
		
						//Code to strip commas and apostraphies
	
						$name = stripTree($myrowTC[1]);
					
						echo "['<b>" . $myrowTC[0] . "</b>: " . $name . "','" . $linkto . "?prodid=" . $myrowPROD[0] . "&edit=testcase&data=" . $myrowTC[0] . "'],\n\n";	

						if($archive == 0)
						{

						}

					}
				}  // end hidetc

				echo "]\n\n,"; //end the category block
			
			
			}

			echo "]\n\n,"; //end the component block


		}

		echo "]\n\n"; //end the product block

	echo "];\n\n</script>\n\n"; //end the whole tree

	echo "<script language='JavaScript'>";
	echo "new tree (TREE_ITEMS, TREE_TPL);";
	echo "</script>\n\n";

}

?>
