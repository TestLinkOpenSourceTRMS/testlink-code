<?

////////////////////////////////////////////////////////////////////////////////
//File:     executionFrameLeft.php
//Author:   Chad Rosen
//Purpose:  This page is the left frame of the execution pages. It builds the
//	    javascript trees that allow the user to jump around to any point
//	    on the screen
////////////////////////////////////////////////////////////////////////////////

require_once("../../functions/header.php");

doSessionStart();
doDBConnect();
doHeader();
//require_once("../../functions/stripTree.php"); //require_once the function that strips the javascript tree
require_once(_ROOT_PATH . "functions/generateTreeMenu.php");

?>

<head>

<script language='JavaScript' src='jtree/tree.js'></script>
<script language='JavaScript' src='jtree/tree_tpl.js'></script>
<link rel="stylesheet" href="jtree/tree.css">

</head>

<?

if ($_SESSION['product']) {
	$product = $_SESSION['product'];
}


$product = $_SESSION['product'];

if($product)
{
	$prodSql = "select id,name from mgtproduct where id=" . $product;

	$prodResult = mysql_query($prodSql);

	while ($myrowPROD = mysql_fetch_row($prodResult))
	{
		$menustring = $menustring . ".|" . $myrowPROD[1] . "||test||mainFrame|\n";

		$sqlCom = "select id, name from mgtcomponent where prodid=" . $myrowPROD[0];

		$comResult = mysql_query($sqlCom);

		while ($myrowCOM = mysql_fetch_row($comResult))
		{
			$menustring = $menustring . "..|" . $myrowCOM[1] . "|manage/keyword/keywordData.php?prodid=" . $myrowPROD[0] . "&edit=component&data=" . $myrowCOM[0] . "|test||mainFrame\n";

			$sqlCat = "select id, name from mgtcategory where compid=" . $myrowCOM[0];

			$catResult = mysql_query($sqlCat);

			while ($myrowCAT = mysql_fetch_row($catResult))
			{
				$menustring = $menustring . "...|" . $myrowCAT[1] . "|manage/keyword/keywordData.php?prodid=" . $myrowPROD[0] . "&edit=category&data=" . $myrowCAT[0] . "|test||mainFrame\n";

				$sqlTc = "select id, title from mgttestcase where catid=" . $myrowCAT[0];

				$tcResult = mysql_query($sqlTc);

				while ($myrowTC = mysql_fetch_row($tcResult))
				{
					$menustring = $menustring . "....|<b>" . $myrowTC[0] . "</b>: " . $myrowTC[1] . "|manage/keyword/keywordData.php?prodid=" . $myrowPROD[0] . "&edit=testcase&data=" . $myrowTC[0] . "|test||mainFrame\n";
				}

			}

		}


	}
	//Table title
	$tableTitle = "Mange Your Keywords";
	//Help link
	$helpInfo = "Click <a href='manage/keyword/keywordData.php' target='mainFrame'>here</a> for help";

	invokeMenu($menustring, $tableTitle, $helpInfo, "");
}

?>