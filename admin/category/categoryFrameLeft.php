<?

////////////////////////////////////////////////////////////////////////////////
//File:     executionFrameLeft.php
//Author:   Chad Rosen
//Purpose:  This page is the left frame of the execution pages. It builds the
//	    javascript trees that allow the user to jump around to any point
//	    on the screen
////////////////////////////////////////////////////////////////////////////////


require_once("../../functions/header.php");

  session_start();
  doDBConnect();
  doHeader();


require_once("../../functions/stripTree.php"); //require_once the function that strips the javascript tree


?>

<head>

<script language='JavaScript' src='jtree/tree.js'></script>
<script language='JavaScript' src='jtree/tree_tpl.js'></script>
<link rel="stylesheet" href="jtree/tree.css">

</head>

<?

		//This next line of code just grabs the build number 

		$result = mysql_query("select build from build,project where project.id = " . $_SESSION['project'] . " and build.projid = project.id",$db);
		
		//setting up the top table with the date and build selection

//////////////////////////////////////////////////////////////Start the display of the components
		
		//Here I create a query that will grab every component depending on the project the user picked
		
		$sql = "select component.id, component.name from component,project where project.id = " . $_SESSION['project'] . " and component.projid = project.id order by name";

		$comResult = mysql_query($sql);

		
		//Second Tree that contains all the data

		echo "<script language='JavaScript'> var TREE_ITEMS = [\n\n";


		echo "['Info', 'admin/category/categorySelect.php?edit=info',";



		while ($myrowCOM = mysql_fetch_row($comResult)) { //display all the components until we run out

			//Code to strip commas and apostraphies

			$name = stripTree($myrowCOM[1]); //function that removes harmful characters
			
			echo "['" . $name . "','admin/category/categorySelect.php?edit=component&com=" . $myrowCOM[0] . "',\n\n";

		//Here I create a query that will grab every category depending on the component the user picked

			$catResult = mysql_query("select category.id, category.name from component,category where component.id = " . $myrowCOM[0] . " and component.id = category.compid order by CATorder,category.id",$db);
			
			while ($myrowCAT = mysql_fetch_row($catResult)) {  //display all the categories until we run out
				
				//Code to strip commas and apostraphies

				$name = stripTree($myrowCAT[1]); //function that removes harmful characters
				
				echo "['" . $name . "','admin/category/categorySelect.php?edit=category&cat=" . $myrowCAT[0] . "'],\n\n";

			}

			echo "],\n\n";

		}

			echo "]\n\n";

			echo "];</script>\n\n";

	
?>

<!--This code will build the javascript tree with the all of the test cases-->
	
	<script language='JavaScript'>
		new tree (TREE_ITEMS, TREE_TPL);
	</script>
