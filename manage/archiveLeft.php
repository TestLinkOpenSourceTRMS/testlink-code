<?

////////////////////////////////////////////////////////////////////////////////
//File:     executionFrameLeft.php
//Author:   Chad Rosen
//Purpose:  This page is the left frame of the execution pages. It builds the
//	    javascript trees that allow the user to jump around to any point
//	    on the screen
////////////////////////////////////////////////////////////////////////////////

require_once("../functions/header.php");

doSessionStart();
doDBConnect();
doHeader();
//require_once("../../functions/stripTree.php"); //require_once the function that strips the javascript tree
require_once("../functions/generateTreeMenu.php");


$product = $_SESSION['product'];

if($product)
{
	$prodSql = "select id,name from mgtproduct where id=" . $product;

	//get the count of test cases for this product
	$prodTCCountSql = "select count(mgttestcase.id) from mgtproduct,mgtcomponent,mgtcategory,mgttestcase where mgtproduct.id=mgtcomponent.prodid and mgtcomponent.id=mgtcategory.compid and mgtcategory.id=mgttestcase.catid and mgtproduct.id=" . $product;

	$prodTCCount = mysql_fetch_row(mysql_query($prodTCCountSql));
	
	$prodResult = mysql_query($prodSql);

	while ($myrowPROD = mysql_fetch_row($prodResult))
	{

		$menustring = ".|" . $myrowPROD[1] . " (" . $prodTCCount[0] .  ")|" . "manage/archiveData.php?edit=product&data=" . $myrowPROD[0] .  "|Product||mainFrame|\n";

		$sqlCom = "select id, name from mgtcomponent where prodid=" . $myrowPROD[0] . " order by name";

		$comResult = mysql_query($sqlCom);

		while ($myrowCOM = mysql_fetch_row($comResult))
		{
			//get the count of test cases for this product
			
			$compTCCountSql = "select count(mgttestcase.id) from mgtcomponent,mgtcategory,mgttestcase where mgtcomponent.id=mgtcategory.compid and mgtcategory.id=mgttestcase.catid and mgtcomponent.id=" . $myrowCOM[0];

			$compTCCount = mysql_fetch_row(mysql_query($compTCCountSql));

			$menustring = $menustring . "..|" . $myrowCOM[1] . " (" . $compTCCount[0] . ")|" . "manage/archiveData.php?prodid=" . $myrowPROD[0] . "&edit=component&data=" . $myrowCOM[0] .  "|Component||mainFrame|\n";

			$sqlCat = "select id, name from mgtcategory where compid=" . $myrowCOM[0] . " order by CATorder, name";

			$catResult = mysql_query($sqlCat);

			while ($myrowCAT = mysql_fetch_row($catResult))
			{
				$catTCCountSql = "select count(mgttestcase.id) from mgtcategory,mgttestcase where mgtcategory.id=mgttestcase.catid and mgtcategory.id=" . $myrowCAT[0];

				$catTCCount = mysql_fetch_row(mysql_query($catTCCountSql));
				
				$menustring = $menustring . "...|" . $myrowCAT[1] . " (" . $catTCCount[0] .  ")|" . "manage/archiveData.php?prodid=" . $myrowPROD[0] . "&edit=category&data=" . $myrowCAT[0] .  "|Category||mainFrame|\n";

				$sqlTc = "select id, title from mgttestcase where catid=" . $myrowCAT[0] . " order by TCorder, id";

				$tcResult = mysql_query($sqlTc);

				while ($myrowTC = mysql_fetch_row($tcResult))
				{
					$menustring = $menustring . "....|<b>" . $myrowTC[0] . "</b>: " . $myrowTC[1] . "|" . "manage/archiveData.php?prodid=" . $myrowPROD[0] . "&edit=testcase&data=" . $myrowTC[0] .  "|Test Case||mainFrame|\n";
				
				}

			}

		}


	}

	//This variable is used when the user is using a server side tree. Ignore otherwise
	if(isset($_GET['p']))
	{
		$_SESSION['p'] = $_GET['p'];
	}

	//Table title
	$tableTitle = "Mange Your Test Case Repository";
	//Help link
	$helpInfo = "Click <a href='manage/keyword/keywordData.php' target='mainFrame'>here</a> for help";

	invokeMenu($menustring, $tableTitle, $helpInfo, "edit=" . $_GET['edit'] . "&data=" . $_GET['data'], "");
}

?>