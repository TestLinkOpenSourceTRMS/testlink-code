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

require_once(_ROOT_PATH . "functions/generateTreeMenu.php");

//require_once("../../functions/stripTree.php"); //require_once the function that strips the javascript tree


?>

<head>

<script language='JavaScript' src='jtree/tree.js'></script>
<script language='JavaScript' src='jtree/tree_tpl.js'></script>
<link rel="stylesheet" href="jtree/tree.css">

</head>

<?
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
			
			$menustring =  $menustring . "..|" . $myrowCOM[1] . "|admin/category/categorySelect.php?edit=component&com=" . $myrowCOM[0] . "|||mainFrame|\n";

			//Here I create a query that will grab every category depending on the component the user picked

			$catResult = mysql_query("select category.id, category.name from component,category where component.id = " . $myrowCOM[0] . " and component.id = category.compid order by CATorder,category.id",$db);
			
			while ($myrowCAT = mysql_fetch_row($catResult)) 
			{  
				$menustring =  $menustring . "...|" . $myrowCAT[1] . "|admin/category/categorySelect.php?edit=category&cat=" . $myrowCAT[0] . "|||mainFrame|\n";
			}

		}

		//Table title
		$tableTitle = " Defining Category Ownership/Priority";
		//Help link
		$helpInfo = "Click <a href='admin/category/categorySelect.php?edit=info' target='mainFrame'>here</a> for help";

		invokeMenu($menustring, $tableTitle, $helpInfo);
?>