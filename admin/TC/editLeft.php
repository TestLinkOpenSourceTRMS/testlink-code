<?

////////////////////////////////////////////////////////////////////////////////
//File:     executionFrameLeft.php
//Author:   Chad Rosen
//Purpose:  This page is the left frame of the execution pages. It builds the
//          javascript trees that allow the user to jump around to any point
//	    on the screen
////////////////////////////////////////////////////////////////////////////////


require_once("../../functions/header.php");
doSessionStart();
doDBConnect();
doHeader();
require_once("../../functions/generateTreeMenu.php");


//////////////////////////////////////////////////////////////Start the display of the components
		
			$sqlProject = "select name from project where id=" . $_SESSION['project'];
		$resultProject = mysql_query($sqlProject);
		$myrowProj = mysql_fetch_row($resultProject);

		$menustring =  ".|" . $myrowProj[0] . "||||mainFrame|\n";

		//Here I create a query that will grab every component depending on the project the user picked
		
		$sql = "select component.id, component.name from component,project where project.id = " . $_SESSION['project'] . " and component.projid = project.id order by name";

		$comResult = mysql_query($sql);

		while ($myrowCOM = mysql_fetch_row($comResult)) 
		{ 
			//display all the components until we run out
			
			$menustring =  $menustring . "..|" . $myrowCOM[1] . "|admin/TC/editData.php?level=com&data=" . $myrowCOM[0] . "|||mainFrame|\n";

			//Here I create a query that will grab every category depending on the component the user picked

			$catResult = mysql_query("select category.id, category.name from component,category where component.id = " . $myrowCOM[0] . " and component.id = category.compid order by CATorder,category.id",$db);
			
			while ($myrowCAT = mysql_fetch_row($catResult)) 
			{  
				$menustring =  $menustring . "...|" . $myrowCAT[1] . "|admin/TC/editData.php?level=cat&data=" . $myrowCAT[0] . "|||mainFrame|\n";

				$sqlTestCase = "select id,title from testcase where catid=" . $myrowCAT[0] . " order by id";
				$resultTestCase = mysql_query($sqlTestCase);

				while ($myrowTC = mysql_fetch_row($resultTestCase)) 
				{
					$menustring =  $menustring . "....|<b>" . $myrowTC[0] . ":</b>" . $myrowTC[1] . "|admin/TC/editData.php?level=tc&data=" . $myrowTC[0] . "|||mainFrame|\n";
						
				}
		



			}

		}

		//Table title
		$tableTitle = "Active/Inactive Test Case";
		//Help link
		$helpInfo = "Click <a href='admin/TC/editData.php?edit=info' target='mainFrame'>here</a> for help";

		invokeMenu($menustring, $tableTitle, $helpInfo, "");


/*
require_once("../../functions/header.php");

  session_start();
  doDBConnect();
  doHeader();

require_once("../../functions/stripTree.php"); //require_once the function that strips the javascript tree


				
//////////////////////////////////////////////////////////////Start the display of the components
		
if($_SESSION['project'])
{
	$project = $_SESSION['project'];


}elseif($_GET['project'])
{

	$project = $_GET['project'];

}

		//Here I create a query that will grab every component depending on the project the user picked
		
		$sql = "select component.id, component.name from component,project where project.id = " . $project . " and component.projid = project.id order by component.name";

		$comResult = mysql_query($sql);

		//Second Tree that contains all the data

		echo "<script language='JavaScript'> var TREE_ITEMS = [\n\n";


		echo "['info','admin/TC/editData.php?edit=info',";



		while ($myrowCOM = mysql_fetch_row($comResult)) { //display all the components until we run out

			//Code to strip commas and apostraphies
				
			$name = stripTree($myrowCOM[1]); //function that removes harmful characters
			
			echo "['" . $name . "','admin/TC/editData.php?level=com&data=" . $myrowCOM[0] . "',\n\n";

		//Here I create a query that will grab every category depending on the component the user picked

			$catResult = mysql_query("select category.id, category.name from component,category where component.id = " . $myrowCOM[0] . " and component.id = category.compid order by CATorder,category.id",$db);
			
			while ($myrowCAT = mysql_fetch_row($catResult)) {  //display all the categories until we run out
				
				//Code to strip commas and apostraphies

				$name = stripTree($myrowCAT[1]); //function that removes harmful characters
			
				echo "['" . $name . "','admin/TC/editData.php?level=cat&data=" . $myrowCAT[0] . "',\n\n";

				$TCResult = mysql_query("select testcase.id, testcase.title,testcase.mgttcid from category,testcase where category.id = " . $myrowCAT[0] . " and category.id = testcase.catid order by TCorder,testcase.mgttcid",$db);

				while ($myrowTC = mysql_fetch_row($TCResult)) 
				
				{  //display all the Test cases until we run out
				
					$name = stripTree($myrowTC[1]); //function that removes harmful characters
					
					echo "['<b>" . $myrowTC[2] . "</b>:" . $name . "','admin/TC/editData.php?level=tc&data=" . $myrowTC[0] . "'],\n\n";



				}

				echo "],\n\n";

			}

			echo "],\n\n";

		}

			echo "]\n\n";

			echo "];</script>\n\n";
*/
?>