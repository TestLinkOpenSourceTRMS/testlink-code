<?

////////////////////////////////////////////////////////////////////////////////
//File:     importTCs.php
//Author:   Chad Rosen
//Purpose:  This page is the left frame of the execution pages. It builds the
//	    javascript trees that allow the user to jump around to any point
//	    on the screen
////////////////////////////////////////////////////////////////////////////////


require_once("../functions/header.php");
doSessionStart();
doDBConnect();
doHeader();


echo "<head>

	<style>
	/* Style for tree item text */
	.t0i {
		font-family: Tahoma, Verdana, Geneva, Arial, Helvetica, sans-serif;
		font-size: 11px;
		color: #000000;
		background-color: #ffffff;
		text-decoration: none;
	}
	/* Style for tree item image */
	.t0im {
		border: 0px;
		width: 19px;
		height: 16px;
	}
	</style></head>";

		//This next line of code just grabs the build number 

		$result = mysql_query("select build from build,project where project.id = " . $_SESSION['project'] . " and build.projid = project.id",$db);
		
		//setting up the top table with the date and build selection

//////////////////////////////////////////////////////////////Start the display of the components
		
		//Here I create a query that will grab every component depending on the project the user picked
		
		$sql = "select component.id, component.name from component,project where project.id = " . $_SESSION['project'] . " and component.projid = project.id";

		$comResult = mysql_query($sql);

		//This code will build the home link on the javascript tree. I had to use a seperate tree because
		//the next tree opens links in the mainPage frame while I want this want to open in the _parent frame
		
		echo "<script language='JavaScript'> var TREE_ITEMS2 = [\n\n";

		echo "['Home','mainPage.php'],";
		
		echo "];</script>";


		//Second Tree that contains all the data

		echo "<script language='JavaScript'> var TREE_ITEMS = [\n\n";


		echo "['Submit','execution/execution.php?page=" . $_GET['page'] . "#top',";



		while ($myrow = mysql_fetch_row($comResult)) { //display all the components until we run out

			echo "['" . $myrow[1] . "','execution/execution.php?page=" . $_GET['page'] . "#" . $myrow[1] . "',\n\n";

			//Here I create a query that will grab every category depending on the component the user picked

			$catResult = mysql_query("select category.id, category.name from component,category where component.id = " . $myrow[0] . " and component.id = category.compid",$db);
			
			while ($CATmyrow = mysql_fetch_row($catResult)) {  //display all the categories until we run out
				
				echo "['" . $CATmyrow[1] . "','execution/execution.php?page=" . $_GET['page'] . "#" . $CATmyrow[1] . "',\n\n";

				//Here I create a query that will grab every category depending on the component the user picked

				
				$TCResult = mysql_query("select distinct testcase.title, testcase.id from testcase,category where testcase.catid = '" . $CATmyrow[0] . "'",$db);

				while ($TCmyrow = mysql_fetch_row($TCResult)) {  //display all the testcases from the categories until we run out
				
					echo "['" . $TCmyrow[0] . "','execution/execution.php?page=" . $_GET['page'] . "#" . $TCmyrow[0] . "'],\n\n";

					
				}

				echo "],\n\n";

			}

			echo "],\n\n";

		}

			echo "]\n\n";

			echo "];</script>\n\n";

	
?>

<!--This code will build the home link on the javascript tree-->

<!--This code will build the javascript tree with the all of the test cases new tree (TREE_ITEMS2, tree_tpl2);
-->

	<script language='JavaScript' src='jtree/tree.js'></script>	
	<script language='JavaScript' src='jtree/tree_tpl.js'></script>
	<script language='JavaScript' src='jtree/tree_tpl2.js'></script>
	<script language='JavaScript'>
		new tree (TREE_ITEMS, tree_tpl);
	</script>
