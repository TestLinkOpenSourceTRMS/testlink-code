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
require_once("../functions/generateTreeMenu.php");


//////////////////////////////////////////////////////////////Start the display of the components
		
	//The query to grab all of the products

	$sqlPROD = "select id, name from mgtproduct order by name";
	$resultPROD = mysql_query($sqlPROD);

	while ($myrowPROD = mysql_fetch_row($resultPROD)) //loop through all products
	{
		$menustring = $menustring . ".|" . $myrowPROD[1] . "||||mainFrame|\n";

		//Displays the component info
		$sqlCOM = "select id, name from mgtcomponent where prodid='" . $myrowPROD[0] . "' order by name";
		$resultCOM = mysql_query($sqlCOM);
			
		while ($myrowCOM = mysql_fetch_row($resultCOM)) //loop through all Components
		{
			$menustring = $menustring . "..|" . $myrowCOM[1] . "|manage/importData.php?product=" . $myrowPROD[0] . "&edit=component&id=" . $myrowCOM[0] . "|||mainFrame|\n";

			$sqlCAT = "select id, name from mgtcategory where compid='" . $myrowCOM[0] . "' order by CATorder,id";
			$resultCAT = mysql_query($sqlCAT);

			while ($myrowCAT = mysql_fetch_row($resultCAT)) //loop through all Categories
			{
				$menustring = $menustring . "...|" . $myrowCAT[1] . "|manage/importData.php?product=" . $myrowPROD[0] . "&edit=category&id=" . $myrowCAT[0] . "|||mainFrame|\n";
			
				
			}//end cat loop
		}//end comp loop
	}//end product loop

	//Table title
	$tableTitle = "Import Test Cases into a Test Plan";
	//Help link
	$helpInfo = "Click <a href='manage/importData.php?edit=info' target='mainFrame'>here</a> for help";

	invokeMenu($menustring, $tableTitle, $helpInfo, "");
?>
