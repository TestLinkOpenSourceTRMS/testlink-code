<?

////////////////////////////////////////////////////////////////////////////////
//File:     executionFrameLeft.php
//Author:   Chad Rosen
//Purpose:  This page is the left frame of the execution pages. It builds the
//	    javascript trees that allow the user to jump around to any point
//	    on the screen
////////////////////////////////////////////////////////////////////////////////


require_once("../functions/header.php");

  session_start();
  doDBConnect();
  doHeader();


//require_once("../functions/stripTree.php"); //require_once the function that strips the javascript tree
require_once("../functions/generateTreeMenu.php");

?>

<head>

<script language='javascript' src='functions/popupHelp.js'></script>
<script language='JavaScript' src='jtree/tree.js'></script>
<script language='JavaScript' src='jtree/tree_tpl.js'></script>
<link rel="stylesheet" href="jtree/tree.css">

</head>

<?
$header = 'y';
$title  = 'y';
$summary= 'y';

//setting up the top table with the date and build selection

echo "<table class=mainTable width=100%>";
echo "<tr><td class=mainMenu><img align=top src=icons/sym_question.gif onclick=javascript:open_popup('../help/pr_left.php');> Filter Test Cases Print</td></tr>";

echo "</table>";

echo "<table class=navtable width=100%>";
echo "<form method='post' ACTION='print/printLeft.php?type=" . $_GET['type'] . "'>";
echo "<tr><td class=navtablelft width='70%'>Show Document Header</td>";

if($_POST['submitPrint']) {
  if($_POST['header'] == 'on') {
    echo "<td class=navtablehdr><input type=checkbox name=header CHECKED></td>";
  } else {
    echo "<td class=navtablehdr><input type=checkbox name=header></td>";
    $header = 'n';
  } 
} else {
  echo "<td class=navtablehdr><input type=checkbox name=header CHECKED></td>";
}
    
echo "</tr>";
		
echo "<tr><td class=navtablelft width='70%'>Show Test Case Body</td>";

if($_POST['submitPrint']) {
  if($_POST['titles'] != 'on') {
    echo "<td class=navtablehdr><input type=checkbox name=titles></td>";
    $title = 'n';
  } else {
    echo "<td class=navtablehdr><input type=checkbox name=titles CHECKED></td>"; 
  }
} else {
  echo "<td class=navtablehdr><input type=checkbox name=titles CHECKED></td>";
}
                
echo "</tr>";

echo "<tr><td class=navtablelft width='70%'>Show Test Case Summary</td>";

if($_POST['submitPrint']) {
  if($_POST['summary'] != 'on') {
    echo "<td class=navtablehdr><input type=checkbox name=summary></td>";
    $summary = 'n';
  } else {
    echo "<td class=navtablehdr><input type=checkbox name=summary CHECKED></td>";
  }
} else {
  echo "<td class=navtablehdr><input type=checkbox name=summary CHECKED></td>";
}

echo "<tr>";

//print out some dummy cells in the second row

echo "<td class=navtable width='100%'>";
				
echo "<input type='submit' NAME='submitPrint' value='Filter'></td><td class=navtable></td>";

echo "</form>";

echo "</tr></table>";
		//If the user has chosen the view the project test cases use this statement

		if($_GET['type'] == "project")
		{	
			
			//Since we don't know if we're using a project or product id we need to setup
			//a neutral variable

			$proID = $_SESSION['project'];

			//Grab the project name

			$sqlProjName = "select name from project where id='" . $proID . "'";


		}elseif($_GET['type'] == "product") //else use this one
		{
			//Since we don't know if we're using a project or product id we need to setup
			//a neutral variable

			$proID = $_SESSION['product'];

			//grab the product name

			$sqlProjName = "select name from mgtproduct where id='" . $proID . "'";

		}
		

		//execute the query and grab it's data

		$projResult = mysql_query($sqlProjName);
		$myRowProj = mysql_fetch_row($projResult);


//////////////////////////////////////////////////////////////Start the display of the components
		
		//Here I create a query that will grab every component or mgtcomponent depending on the project the user picked

		
		if($_GET['type'] == "project")
		{			
			$sql = "select component.id, component.name from component,project where project.id = " . $proID . " and component.projid = project.id order by component.name";


		}elseif($_GET['type'] == "product")
		{
			$sql = "select mgtcomponent.id, mgtcomponent.name from mgtcomponent,mgtproduct where mgtproduct.id = " . $proID . " and mgtcomponent.prodid = mgtproduct.id order by mgtcomponent.name";

		}
				  
		$menustring =   ".|" . $myRowProj[0] . "|print/printData.php?type=" . $_GET['type'] . "&edit=pro&proj=" . $proID . "&header=" . $header . "&title=" . $title . "&summary=" . $summary . "|test||mainFrame|\n";
	
		$comResult = mysql_query($sql);


		//Second Tree that contains all the data

		while ($myrowCOM = mysql_fetch_row($comResult)) { //display all the components until we run out

			//Code to strip commas and apostraphies

			$menustring =  $menustring . "..|" . $myrowCOM[1] . "|print/printData.php?type=" . $_GET['type'] . "&edit=component&com=" . $myrowCOM[0] . "&header=" . $header . "&title=" . $title . "&summary=" . $summary . "|test||mainFrame|\n";
	

		//Here I create a query that will grab every category or mgt depending on the component the user picked

		
		if($_GET['type'] == "project")
		{			
			$catResult = mysql_query("select category.id, category.name from component,category where component.id = " . $myrowCOM[0] . " and component.id = category.compid order by CATorder",$db);


		}elseif($_GET['type'] == "product")
		{
			$catResult = mysql_query("select mgtcategory.id, mgtcategory.name from mgtcomponent,mgtcategory where mgtcomponent.id = " . $myrowCOM[0] . " and mgtcomponent.id = mgtcategory.compid order by CATorder",$db);

		}
	
			while ($myrowCAT = mysql_fetch_row($catResult)) {  //display all the categories until we run out
				
				//Code to strip commas and apostraphies
				
				//$name = stripTree($myrowCAT[1]); //function that removes harmful characters
				
				$menustring =  $menustring . "...|" . $myrowCAT[1] . "|print/printData.php?type=" . $_GET['type'] . "&edit=category&cat=" . $myrowCAT[0] . "&header=" . $header . "&title=" . $title . "&summary=" . $summary . "|test||mainFrame|\n";

			}
		}

	//Table title
	$tableTitle = "Print Your Test Case Repository";
	//Help link
	$helpInfo = "Click <a href='print/printData.php' target='mainFrame'>here</a> for help";

	invokeMenu($menustring, $tableTitle, $helpInfo);

	
?>