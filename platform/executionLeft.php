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

/*$font = "black";
$menustringResult = "";
$lastRanDate = "NR";
$lastRanBuild = "";
*/
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
			
		$menustring =  $menustring . "..|" . $myrowCOM[1] . "|platform/executionData.php?edit=component&data=" . $myrowCOM[0] . "|||mainFrame|\n";

		//Here I create a query that will grab every category depending on the component the user picked

		$catResult = mysql_query("select category.id, category.name from component,category where component.id = " . $myrowCOM[0] . " and component.id = category.compid order by CATorder,category.id",$db);
			
		while ($myrowCAT = mysql_fetch_row($catResult)) 
		{  
			$menustring =  $menustring . "...|" . $myrowCAT[1] . "|platform/executionData.php?edit=category&data=" . $myrowCAT[0] . "|||mainFrame|\n";
		
			$sqlTC = "select testcase.id, title, mgttcid from testcase where catid=" . $myrowCAT[0] . " order by mgttcid";
			$resultTC = mysql_query($sqlTC);

			while ($myrowTC = mysql_fetch_row($resultTC)) 
			{  
				
				$menustring =  $menustring . "....|<b>" . $myrowTC[2] . ":</b>" . $myrowTC[1] .  "|platform/executionData.php?edit=testcase&data=" . $myrowTC[0] . "|||mainFrame|\n";

				$sqlPlatform = "select buildId, platformList, result, dateRun from platformresults where tcId=" . $myrowTC[0] . " order by buildId, platformList";

				//echo $sqlPlatform;

				$resultPlatform = mysql_query($sqlPlatform);
				
				$buildId="";

				while ($myrowPlatform = mysql_fetch_row($resultPlatform)) 
				{
					if($buildId != $myrowPlatform[0])
					{
						$menustring =  $menustring . ".....|<b>Build:" . $myrowPlatform[0] . "</b>| |||mainFrame|\n";

						$buildId = $myrowPlatform[0];

					}
					
					//make the color correspond to the result

					$font = "black";

					if($myrowPlatform[2] == "p")
					{
						$font = "green";
					}
					elseif($myrowPlatform[2] == "f")
					{
						$font = "red";
					}
					elseif($myrowPlatform[2] == "b")
					{
						$font = "blue";
					}

					$platformNameArray = explode(",",$myrowPlatform[1]);

					foreach ($platformNameArray as $platformId)
					{	
						$sqlPlatformName = "select name,containerId,id from platform where id=" . $platformId;
						$platformNameRow = mysql_fetch_row(mysql_query($sqlPlatformName)); //Run the query

						$sqlContainerName = "select name from platformcontainer where id=" . $platformNameRow[1];
						$containerResult = mysql_query($sqlContainerName);
						
						while($myrowContainer = mysql_fetch_row($containerResult))
						{
							$platformHref .= "&" . $myrowContainer[0] . "=" . $platformNameRow[2];
						}
						
						$platformNames .= $platformNameRow[0] . " ";
					}

					//echo "<a href='platform/executionData.php?edit=" . $edit . "&data=" . $data . "&build=" . $build . $platformHref . "&submit=submit'>";

					//echo $platformInfo . $platformNames;

					$menustring =  $menustring . "......|<font color=" . $font . ">" . $myrowPlatform[3] . " " . $platformNames .  "|platform/executionData.php?build=" . $myrowPlatform[0] . "&edit=testcase&data=" . $myrowTC[0] . $platformHref . "|||mainFrame|\n";

					//Reset the variables
					$platformHref="";
					$platformInfo="";
					$platformNames="";
					$font = "black";

				}

			}
		
		}

	}

	//Table title
	$tableTitle = " Test Case Execution";
	//Help link
	$helpInfo = "Click <a href='platform/executionData.php?edit=info' target='mainFrame'>here</a> for help";

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
