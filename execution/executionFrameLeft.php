<?

////////////////////////////////////////////////////////////////////////////////
//File:     executionFrameLeft.php
//Author:   Chad Rosen
//Purpose:  This page is the left frame of the execution pages. It builds the
//          javascript trees that allow the user to jump around to any point
//	    on the screen
////////////////////////////////////////////////////////////////////////////////

//Includes the base href and database login

require_once("../functions/header.php");

  session_start();
  doDBConnect();
  doHeader();

?>

<head>

<script language='javascript' src='functions/popupHelp.js'></script>
<link rel="stylesheet" href="kenny.css">

</head>

<?

$font = "black";
$menustringResult = "";
$lastRanDate = "NR";
$lastRanBuild = "";

require_once(_ROOT_PATH . "functions/generateTreeMenu.php");

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
			
		$menustring =  $menustring . "..|" . $myrowCOM[1] . "|execution/execution.php?edit=component&com=" . $myrowCOM[0] . "|||mainFrame|\n";

		//Here I create a query that will grab every category depending on the component the user picked

		$catResult = mysql_query("select category.id, category.name from component,category where component.id = " . $myrowCOM[0] . " and component.id = category.compid order by CATorder,category.id",$db);
			
		while ($myrowCAT = mysql_fetch_row($catResult)) 
		{  
			$menustring =  $menustring . "...|" . $myrowCAT[1] . "|execution/execution.php?edit=category&cat=" . $myrowCAT[0] . "|||mainFrame|\n";
		
			$sqlTC = "select testcase.id, title from testcase where catid=" . $myrowCAT[0] . " order by id";
			$resultTC = mysql_query($sqlTC);

			while ($myrowTC = mysql_fetch_row($resultTC)) 
			{  
				
				$sqlResult = "select tcid, build, status, daterun from results where tcid=" . $myrowTC[0] . " order by build";

				$resultResult = mysql_query($sqlResult);

				while ($myrowResult = mysql_fetch_row($resultResult)) 
				{
					
					 $font = getResultFont($myrowResult[2]);
					 $lastRanDate = $myrowResult[3];
					 $lastRanBuild = "B:" . $myrowResult[1] . " ";
				
					$menustringResult =  $menustringResult . ".....|<font color=" . $font . ">Build:" . $myrowResult[1] . " (" . $myrowResult[3] . ")" .  "|execution/execution.php?edit=testcase&tc=" . $myrowTC[0] . "&build=" . $myrowResult[1] . "|||mainFrame|\n";
				}

				//build the test case tree

				$menustring =  $menustring . "....|<font color=" . $font . "><b>" . $myrowTC[0] . ":</b>" . $myrowTC[1] . " (" . $lastRanBuild . $lastRanDate . ")"  .  "|execution/execution.php?edit=testcase&tc=" . $myrowTC[0] . "|||mainFrame|\n";
				
				$menustring = $menustring . $menustringResult;
				$font = "black";
				$menustringResult = "";
				$lastRanDate = "NR";
				$lastRanBuild = "";

			}
		
		}

	}

	//Table title
	$tableTitle = " Test Case Execution";
	//Help link
	$helpInfo = "Click <a href='execution/execution.php?edit=info' target='mainFrame'>here</a> for help";

	//This variable is used when the user is using a server side tree. Ignore otherwise
	if(isset($_GET['p']))
	{
		$_SESSION['p'] = $_GET['p'];
	}

	invokeMenu($menustring, $tableTitle, $helpInfo, "", "");

function getResultFont($result)
{
	$font = "black";

	if($result == "p")
	{
		$font = "green";
	}
	else if($result == "f")
	{
		$font = "red";
	}
	else if($result == "b")
	{
		$font = "blue";
	}

	return $font;
}

	
?>
