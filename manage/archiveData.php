<?php

////////////////////////////////////////////////////////////////////////////////
//File:     archiveData.php
//Author:   Chad Rosen
//Purpose:  This page allows you to manage data (test cases, categories, and
//          components.
////////////////////////////////////////////////////////////////////////////////

require_once("../functions/header.php");
doSessionStart();
doDBConnect();
doHeader();

?>

<LINK REL="stylesheet" TYPE="text/css" HREF="kenny.css">

<?

if(!$_GET['edit'] && !$_POST['edit'])
{

echo "<table width=100% class=helptable>";

echo "<tr><td class=helptablehdr>Welcome To The Test Case Edit and Archive</td></tr>";

echo "</table><table width=100% class=helptable>";

echo "<tr><td class=helptablehdr>Purpose:</td>";

echo "<td class=helptable>The test case archive is a place where a user can view all of the existing product, component, category, and test case information. A user can also look at all of the different versions of test cases</td>";

echo "<tr><td class=helptablehdr>Get Started:</td>";

echo "<td class=helptable>Select a product, component, category, or test case on the tree view on the left</td></tr></table>";

}

$data = $_GET['data'];
$product = $_SESSION['product'];

if($_GET['edit'] == 'product')
{

	$sqlTC = "select id, name from mgtproduct where id='" . $data . "'";
	$resultTC = mysql_query($sqlTC);
	$myrowTC = mysql_fetch_row($resultTC);

	if(has_rights("mgt_modify_tc"))
	{
	

	echo "<table width='100%' border=0>";

	echo "<Form Method='POST' ACTION='manage/editData.php?data=" . $data . "&product=" . $data . "'>";
	
	echo "<tr><td align=right><input type='submit' name='newCOM' value='New'></td></tr>";

	echo "</form>";

	echo "</table>";

	}

	echo "<table width='100%' class=tctable>";
	
	echo "<tr><td class=tctablehdr><b>Product Name</td></tr>";

	echo "<tr><td class=tctable>" . $myrowTC[1] . "</td></tr>";

	echo "</table>";

}elseif($_GET['edit'] == 'component')
{

	$sqlTC = "select id,name,intro,scope,ref,method,lim from mgtcomponent where id=" . $data;

	$resultTC = mysql_query($sqlTC);
	$myrowTC = mysql_fetch_row($resultTC);

	if(has_rights("mgt_modify_tc"))
	{
	
	echo "<table width='100%' border=0>";

	echo "<Form Method='POST' ACTION='manage/editData.php?product=" . $product . "&data=" . $data . "'>";
	
	echo "<tr><td align=right><td align=right><input type='submit' name='newCAT' value='New'><input type='submit' name='editCOM' value='Edit'><input type='submit' name='deleteCOM' value='Delete'><input type='submit' name='reorderCAT' value='Reorder Categories'><input type='submit' name='moveCom' value='Move'><input type='submit' name='copyCom' value='Copy'>";

	echo "</td></tr></form>";

	echo "</table>";

	}

	echo "<table width='100%' class=tctable>";

	echo "<tr><td class=tctablehdr><b>Component: </b>" . htmlspecialchars($myrowTC[1]) . "</td>";

	echo "</tr>";

	echo "<tr><td class=tctable><b>Introduction</td></tr>";
        echo "<tr><td class=tctable><ul>" . $myrowTC[2] . "</ul></td></tr>";

	echo "<tr><td class=tctable><b>Scope</td></tr>";
        echo "<tr><td class=tctable><ul>" . $myrowTC[3] . "</ul></td></tr>";

	echo "<tr><td class=tctable><b>References</td></tr>";
        echo "<tr><td class=tctable><ul>" . $myrowTC[4] . "</ul></td></tr>";

	echo "<tr><td class=tctable><b>Methodology</td></tr>";
        echo "<tr><td class=tctable><ul>" . $myrowTC[5] . "</ul></td></tr>";

	echo "<tr><td class=tctable><b>Limitations</td></tr>";
	echo "<tr><td class=tctable><ul>" . $myrowTC[6] . "</ul></td></tr>";

	echo "</table>";


}elseif($_GET['edit'] == 'category')
{

	$sqlTC = "select id,name,objective,config,data,tools from mgtcategory where id='" . $data . "'";
	$resultTC = mysql_query($sqlTC);
	$myrowTC = mysql_fetch_row($resultTC);

	if(has_rights("mgt_modify_tc"))
	{
	
	echo "<table width='100%' border=0>";

	echo "<Form Method='POST' ACTION='manage/editData.php?product=" . $product . "&data=" . $data . "'>";
	
	echo "<tr><td align=right><input type='submit' name='newTC' value='Create'> ";
	
	echo "<select name=numCases>";

	echo "<option value=1>1</option>";
	echo "<option value=2>2</option>";
	echo "<option value=3>3</option>";
	echo "<option value=4>4</option>";
	echo "<option value=5>5</option>";
	echo "<option value=6>10</option>";
	echo "<option value=7>15</option>";
	echo "<option value=8>20</option>";
	
	echo "</select>";

	echo "Test Cases ";
		
	echo "<input type='submit' name='editCAT' value='Edit'><input type='submit' name='deleteCAT' value='Delete'><input type='submit' name='reorderTC' value='Reorder Test Cases'><input type='submit' name='moveCat' value='Move'><input type='submit' name='copyCat' value='Copy'></td></tr>";

	echo "</form>";

	echo "</table>";

	}


	echo "<table width='100%' class=tctable>";

	echo "<tr><td class=tctablehdr><b>Category: </b>" . htmlspecialchars($myrowTC[1]) . "</font></td>";
	
	echo "</tr>";
	
	echo "<tr><td class=tctable><b>Objective</td></tr>";
	echo "<tr><td class=tctable><ul>" . $myrowTC[2] . "</ul></td></tr>";

	echo "<tr><td class=tctable><b>Configuration</td></tr>";
	echo "<tr><td class=tctable><ul>" . $myrowTC[3] . "</ul></td></tr>";

	echo "<tr><td class=tctable><b>Data</td></tr>";
	echo "<tr><td class=tctable><ul>" . $myrowTC[4] . "</ul></td></tr>";

	echo "<tr><td class=tctable><b>Tools</td></tr>";
	echo "<tr><td class=tctable><ul>" . $myrowTC[5] . "</ul></td></tr>";
		
	echo "</table>";

	echo "</form>";

}elseif($_GET['edit'] == 'testcase')
{

	$sqlTC = "select id,title,summary,steps,exresult,version,keywords from mgttestcase where id='" . $data . "'";
	$resultTC = mysql_query($sqlTC);
	$myrowTC = mysql_fetch_row($resultTC);

	if(has_rights("mgt_modify_tc"))
	{

	echo "<table width='100%' border=0>";

	echo "<Form Method='POST' ACTION='manage/editData.php?product=" . $product . "&data=" . $data . "'>";
	
	echo "<tr><td align=right><input type='submit' name='editTC' value='Edit'><input type='submit' name='deleteTC' value='Delete'><input type='submit' name='moveTC' value='Move'><input type='submit' name='copyTC' value='Copy'></td></tr>";

	echo "</form>";

	echo "</table>";

	}

	echo "<table width='100%' class=tctable>";
	
	echo "<tr><td class=tctablehdr><b>Test Case " . $myrowTC[0] . ": </b>" . htmlspecialchars($myrowTC[1]) . "</font></td>";
	
	echo "</tr>";


	echo "<tr><td class=tctablehdr><b>Version: </b>" . $myrowTC[5] . "</td></tr>";
	
	echo "<tr><td class=tctable><b>Summary</td></tr>";
	echo "<tr><td class=tctable>" . htmlspecialchars(nl2br($myrowTC[2])) . "</td></tr>";

	echo "<tr><td class=tctable><b>Steps</td></tr>";
	echo "<tr><td class=tctable>" . $myrowTC[3] . "</td></tr>";

	echo "<tr><td class=tctable><b>Expected Result</td></tr>";
	echo "<tr><td class=tctable>" .  $myrowTC[4] . "</td></tr>";
	
	$sqlProdID = "select mgtproduct.id from mgtproduct, mgtcomponent, mgtcategory, mgttestcase where mgttestcase.id=" . $data . " and mgttestcase.catid = mgtcategory.id and mgtcategory.compid=mgtcomponent.id and mgtcomponent.prodid=mgtproduct.id";

	$prodIDResult = mysql_query($sqlProdID);

	$prodID = mysql_fetch_row($prodIDResult);	
	
	//Chop the trailing comma off of the end of the keywords field

	$keywords = substr("$myrowTC[6]", 0, -1); 

	echo "<tr><td class=tctable><a href='manage/keyword/viewKeywords.php?product=" . $prodID[0] . "' target='_blank'><b>Keywords</b></a>: " . $keywords . "</td></tr>";

		
	echo "</table>";


}elseif($_GET['edit'] == 'archive')
{

	$sqlTC = "select id,title,summary,steps,exresult,version,keywords from mgttcarchive where id='" . $_GET['id'] . "' and version='" . $_GET['ver'] . "'";

	$resultTC = mysql_query($sqlTC);
	$myrowTC = mysql_fetch_row($resultTC);

	echo "<table width='100%' class=tctable>";
	
	echo "<tr><td class=tctable><b>Title</td></tr>";
	echo "<tr><td class=tctable>" . htmlspecialchars($myrowTC[1]) . "";
		
	echo "</td></tr>";
	
	echo "<tr><td class=tctable><b>Summary</td></tr>";
	echo "<tr><td class=tctable>" . htmlspecialchars(nl2br($myrowTC[2])) . "</td></tr>";

	echo "<tr><td class=tctable><b>Steps</td></tr>";
	echo "<tr><td class=tctable>" . nl2br($myrowTC[3]) . "</td></tr>";

	echo "<tr><td class=tctable><b>Expected Result</td></tr>";
	echo "<tr><td class=tctable>" . nl2br($myrowTC[4]) . "</td></tr>";
		
	echo "<tr><td class=tctable><b>Keywords</td></tr>";
	echo "<tr><td class=tctable>" . $myrowTC[6] . "</td></tr>";

	echo "<tr><td class=tctable><b>Version</td></tr>";
	echo "<tr><td class=tctable>" . $myrowTC[5] . "</td></tr>";
		
	echo "</table>";

}


?>
