<?php

////////////////////////////////////////////////////////////////////////////////
//File:     editData.php
//Author:   Chad Rosen
//Purpose:  This page manages all the editing of various test related data.
////////////////////////////////////////////////////////////////////////////////

require_once("../functions/header.php");
doSessionStart();
doDBConnect();
doHeader();

require_once("../functions/refreshLeft.php"); //This adds the function that refreshes the left hand frame

?>

<head>
<LINK REL="stylesheet" TYPE="text/css" HREF="kenny.css">

<?

require_once('../htmlarea/textArea.php');

echo "\n\n\n";

?>

</head>

<?

if($_POST['editTC'])
{
	
	$tc = $_POST['editTC'];

}elseif($_GET['editTC'])
{

	$tc = $_GET['editTC'];

}


//If the user hasn't selected anything show the instructions. This comes up by default the first time the user enters the screen

$product = $_SESSION['product'];

//echo "product=" . $product . "<br>";
$data = $_GET['data'];

//If the user has chosen to edit a component then show this code

if($_POST['editCOM'])
{

	$sqlTC = "select id,name,intro,scope,ref,method,lim from mgtcomponent where id=" . $data;

	$resultTC = mysql_query($sqlTC);
	$myrowTC = mysql_fetch_row($resultTC);

	echo "<Form Method='POST' ACTION='manage/editDataResults.php?product=" . $product . "'>";

		echo "<table width='100%' border=0>";
	
		echo "<tr><td align=right><input type='submit' name='editCOM' value='Save'></td></tr>";

		echo "</table>";

	echo "<table class=edittable width='350'>";
	
	echo "<tr><td class=edittablehdr><b>Edit Component</td></tr>";
	
	echo "<tr><td class=edittablesubhdr><b>Name</td></tr>";
	echo "<tr><td class=edittable><input type='hidden' name='id' value='" . $data . "'>";
	
	echo '<input type=text name=name size=75 value="' . $myrowTC[1] . '"></td></tr>';

	echo "<tr><td class=edittablesubhdr>Introduction</td></tr>";
	echo "<tr><td><textarea name='intro' cols='75' rows='6'>" . $myrowTC[2] . "</textarea></td></tr>";

	echo "<tr><td class=edittablesubhdr>Scope</td></tr>";
	echo "<tr><td><textarea name='scope' cols='75' rows='6'>" . $myrowTC[3] . "</textarea></td></tr>";

	echo "<tr><td class=edittablesubhdr>References</td></tr>";
	echo "<tr><td><textarea name='ref' cols='75' rows='6'>" . $myrowTC[4] . "</textarea></td></tr>";

	echo "<tr><td class=edittablesubhdr>Methodology</td></tr>";
	echo "<tr><td><textarea name='method' cols='75' rows='6'>" . $myrowTC[5] . "</textarea></td></tr>";

	echo "<tr><td class=edittablesubhdr>Limitations</td></tr>";
	echo "<tr><td><textarea name='lim' cols='75' rows='6'>" . $myrowTC[6] . "</textarea></td></tr>";


	echo "</table>";
	echo "</form>";

	echo "<script language='javascript1.2'>


	editor_generate('intro',config);
	editor_generate('scope',config);
	editor_generate('ref',config);
	editor_generate('method',config);
	editor_generate('lim',config);
	
	</script>";


}

//If the user has chosen to edit a category then show this code

elseif($_POST['editCAT'])
{

	$sqlTC = "select id,name,objective,config,data,tools from mgtcategory where id='" . $data . "'";
	$resultTC = mysql_query($sqlTC);
	$myrowTC = mysql_fetch_row($resultTC);

	echo "<Form Method='POST' ACTION='manage/editDataResults.php?product=" . $product . "'>";

	echo "<table width='100%' border=0>";

	echo "<tr><td align=right><input type='submit' name='editCAT' value='Save'></td></tr>";

	echo "</table>";
	
	echo "<table width='350' class=edittable>";
	echo "<tr><td class=edittablehdr>Edit Category</td></tr>";
	
	echo "<tr><td class=edittablesubhdr>Name</td></tr>";
	echo "<tr><td><input type='hidden' name='id' value='" . $data . "'>";
	
	echo '<input type=text name=name size=75 value="' . $myrowTC[1] . '"></td></tr>';

	echo "<tr><td class=edittablesubhdr>Objective</td></tr>";
	echo "<tr><td class=edittable><textarea name='objective' cols='75' rows='6'>" . $myrowTC[2] . "</textarea></td></tr>";

	echo "<tr><td class=edittablesubhdr>Configuration</td></tr>";
	echo "<tr><td><textarea name='config' cols='75' rows='6'>" . $myrowTC[3] . "</textarea></td></tr>";

	echo "<tr><td class=edittablesubhdr>Data</td></tr>";
	echo "<tr><td><textarea name='data' cols='75' rows='6'>" . $myrowTC[4] . "</textarea></td></tr>";

	echo "<tr><td class=edittablesubhdr>Tools</td></tr>";
	echo "<tr><td><textarea name='tools' cols='75' rows='6'>" . $myrowTC[5] . "</textarea></td></tr>";

	echo "<tr>";
	
	echo "<input type='hidden' name='id' value='" . $data . "'>";

		
	echo "</table>";
	echo "</form>";

	echo "<script language='javascript1.2'>
	editor_generate('objective',config);
	editor_generate('config',config);
	editor_generate('data',config);
	editor_generate('tools',config);
	
	
	</script>";

}

//If the user has chosen to edit a testcase then show this code

elseif($tc)
{

	$sqlTC = "select id,title,summary,steps,exresult,version,keywords from mgttestcase where id='" . $data . "'";
	$resultTC = mysql_query($sqlTC);
	$myrowTC = mysql_fetch_row($resultTC);

	echo "<Form Method='POST' ACTION='manage/editDataResults.php?product=" . $product . "'>";

	echo "<table width='100%' border=0>";

	echo "<tr><td align=right><input type='submit' name='editTC' value='Save'><input type='submit' name='archive' value='Save and Archive'></td></tr>";

	echo "</table>";


	echo "<table width='350' class=edittable>";
	echo "<tr><td class=edittablehdr>Edit Test Case " . $myrowTC[0] . "</tr>";
	
	echo "<tr><td class=edittablesubhdr>Name</td></tr>";
	echo '<tr><td><input type=text name=title size=75 value="' . $myrowTC[1] . '">';
	
    echo "<input type='hidden' name='id' value='" . $data . "'>";
	echo "<input type=hidden name=version value=" . $myrowTC[5] . ">";
	
	echo "</td></tr>";
	
	echo "<tr><td class=edittablesubhdr>Summary</td></tr>";
	echo "<tr><td><textarea name='summary' cols='75' rows='2'>" .$myrowTC[2] . "</textarea></td></tr>";

	echo "<tr><td class=edittablesubhdr>Steps</td></tr>";
	echo "<tr><td><textarea name='steps' cols='75' rows='10'>" . $myrowTC[3] . "</textarea></td></tr>";
	
	echo "<tr><td class=edittablesubhdr>Expected Result</td></tr>";
	echo "<tr><td><textarea name='exresult' cols='75' rows='10'>" . $myrowTC[4] . "</textarea></td></tr>";
		
	//This block of code grabs the product id from the test case number that was passed in
	
	$sqlProdID = "select mgtproduct.id from mgtproduct, mgtcomponent, mgtcategory, mgttestcase where mgttestcase.id=" . $_GET['data'] . " and mgttestcase.catid = mgtcategory.id and mgtcategory.compid=mgtcomponent.id and mgtcomponent.prodid=mgtproduct.id";

	$prodIDResult = mysql_query($sqlProdID);

	$prodID = mysql_fetch_row($prodIDResult);

	echo "<tr><td class=edittablesubhdr><a href='manage/keyword/viewKeywords.php?product=" . $prodID[0] . "' target='_blank'>Keywords</a></td></tr>";

	
	//The next block of code displays the keywords

	//SQL query to grab all of the available keywords from the product the user has selected
	$sqlKeys = "select keyword from keywords where prodid='" . $prodID[0] . "'";

	//Execute the query
	$resultKeys = mysql_query($sqlKeys);

	//Find the amount of keys so that I can make the select box the right size
	
	$keySize = mysql_num_rows($resultKeys);

	//Echo out a html select. Notice how the keywords has a set of brackets after it. This is necesarry for the
	//Multiple to work

	echo "<tr><td><select name='keywords[]' size='" . $keySize . "' MULTIPLE>";

	//Begin to echo out all of the keys

	while ($keys = mysql_fetch_row($resultKeys))
	{
							
		//This next block of code will search through the testcase and see if any of the products keywords are being used. If they are I highlight them

		//SQL statement to do the grab the test cases keys
		$sqlCompare = "select keywords from mgttestcase where id='" . $data . "' and keywords like '%" . $keys[0] . "%'";

		//Execute the query
		$resultCompare = mysql_query($sqlCompare);

		//Using the mysql_num_rows function to see how many results are returned
		$compareResult = mysql_num_rows($resultCompare);

		if($compareResult > 0) //If we find a match I highlight the value
		{

			echo "<OPTION VALUE='" . $keys[0] ."' SELECTED>" . $keys[0];

		}else //If there isnt a match just display the value without highlight
		{

			echo "<OPTION VALUE='" . $keys[0] ."'>" . $keys[0];

		}//end else
								
	}//ened while

	echo "</select></td></tr>";


		
	


	echo "</table>";
	echo "</form>";

	echo "<script language='javascript1.2'>
	editor_generate('steps',config);
	editor_generate('exresult',config);
	
	
	</script>";

}

elseif($_POST['newCOM']) //Creating a new component
{

	echo "<Form Method='POST' ACTION='manage/editDataResults.php?product=" . $product  . "'>";

	echo "<table width='100%' border=0>";
	
	echo "<tr><td align=right><input type='submit' name='newCOM' value='Create Component' align=right></td></tr>";

	echo "</table>";

	echo "<table width='350' class=tctable>";
	
	echo "<td class=tctablehdr><font align=left><b>Component Name</font></td></tr>";

	echo "<tr><td><textarea name='name' cols='55' rows='1'></textarea></td></tr>";

	echo "<tr><td class=tctable><b>Intro</td></tr>";
	echo "<tr><td><textarea name='intro' cols='75' rows='10'></textarea></td></tr>";

	echo "<tr><td class=tctable><b>Scope</td></tr>";
	echo "<tr><td><textarea name='scope' cols='75' rows='10'></textarea></td></tr>";

	echo "<tr><td class=tctable><b>References</td></tr>";
	echo "<tr><td><textarea name='ref' cols='75' rows='10'></textarea></td></tr>";

	echo "<tr><td class=tctable><b>Methodology</td></tr>";
	echo "<tr><td><textarea name='method' cols='75' rows='10'></textarea></td></tr>";

	echo "<tr><td class=tctable><b>Limitations</td></tr>";
	echo "<tr><td><textarea name='lim' cols='75' rows='10'></textarea></td></tr>";

	echo "<tr>";
	
	echo "</table>";
	echo "</form>";

	echo "<script language='javascript1.2'>
	editor_generate('intro',config);
	editor_generate('scope',config);
	editor_generate('ref',config);
	editor_generate('method',config);
	editor_generate('lim',config);
	
	</script>";

}elseif($_POST['newCAT']) //Creating a new category
{

	echo "<Form Method='POST' ACTION='manage/editDataResults.php?catID=" . $data . "&product=" . $product . "'>";
	
	echo "<table width='100%' border=0>";

	echo "<tr><td align=right><input type='submit' name='newCAT' value='Create Category'></td></tr>";

	echo "</table>";
	
	echo "<table width='350' class=tctable>";

	echo "<tr><td class=tctablehdr><b>Category Name</td></tr>";
	echo "<tr><td><textarea name='name' cols='55' rows='1'></textarea></td></tr>";

	echo "<tr><td class=tctable><b>Objective</td></tr>";
	echo "<tr><td><textarea name='objective' cols='75' rows='10'></textarea></td></tr>";

	echo "<tr><td class=tctable><b>Configuration</td></tr>";
	echo "<tr><td><textarea name='config' cols='75' rows='10'></textarea></td></tr>";

	echo "<tr><td class=tctable><b>Data</td></tr>";
	echo "<tr><td><textarea name='data' cols='75' rows='10'></textarea></td></tr>";

	echo "<tr><td class=tctable><b>Tools</td></tr>";
	echo "<tr>";
	
	echo "<td><textarea name='tools' cols='75' rows='10'></textarea></td></tr>";
		
	echo "</table>";
	echo "</form>";

	echo "<script language='javascript1.2'>
	editor_generate('objective',config);
	editor_generate('config',config);
	editor_generate('data',config);
	editor_generate('tools',config);
	
	</script>";


}elseif($_POST['newTC']) //Creating a new test case
{


	echo "<Form Method='POST' ACTION='manage/editDataResults.php?data=" . $data . "&product=" . $product . "'>";
	
	echo "<table width='100%' border=0>";

	echo "<tr><td align=right><input type='submit' name='newTC' value='Create Test Case(s)'></td></tr>";

	echo "</table>";

	//loop through the create test case screen for each of the test cases that the user passed in from the screen before

	for($i = 0; $i < $_POST['numCases']; $i++)
	{
	
		echo "<table width='350' class=tctable>";

		echo "<tr><td class=tctablehdr><b>Test Case Title</td></tr>";
		echo "<tr><td><textarea name='title" . $i . "' cols='75' rows='2'></textarea></td></tr>";
		
		echo "<tr><td class=tctable><b>Summary</td></tr>";
		echo "<tr><td><textarea name='summary" . $i . "' cols='75' rows='2'></textarea></td></tr>";

		echo "<tr><td class=tctable><b>Steps</td></tr>";
		echo "<tr><td><textarea name='steps" . $i . "' cols='75' rows='6'></textarea></td></tr>";

		echo "<tr><td class=tctable><b>Expected Result</td></tr>";
		echo "<tr><td><textarea name='exresult" . $i . "' cols='75' rows='6'></textarea></td></tr>";
					
		echo "<tr>";
		
		echo "</table><br>";

		//render the steps and exresult fields

		echo "<script language='javascript1.2'>
		editor_generate('steps" . $i . "',config);
		editor_generate('exresult" . $i . "',config);
	
		</script>";

	}

	echo "</form>";

	

}

elseif($_POST['deleteTC'])
{
	
	if($_GET['sure'] == 'yes') //check to see if the user said he was sure he wanted to delete
	{


		$sqlTC = "delete from mgttestcase where id='" . $data . "'";

		$result = mysql_query($sqlTC); //Execute query

		$sqlArchive = "delete from mgttcarchive where id='" . $data . "'";

		$result = mysql_query($sqlArchive); //Execute query

		echo "Test case and archive deleted";

		//Refresh the left frame

		$page = $basehref . "/manage/archiveLeft.php?product=" . $product;

		refreshFrame($page); //call the function below to refresh the left frame

	}else //if the user has clicked the delete button on the archive page show the delete confirmation page
	{

		
		echo "<Form Method='POST' ACTION='manage/editData.php?sure=yes&data=" . $data . "&product=" . $product . "'>";
	
		echo "<table width='100%' border=0>";
		
		echo "<tr><td>Are you sure you wish to delete this test case?</td></tr>";

		echo "<tr><td><input type='submit' name='deleteTC' value='Yes, delete this test case'></td></tr>";

		echo "</table>";


	}


}elseif($_POST['deleteCAT'])
{

	if($_GET['sure'] == 'yes') //check to see if the user said he was sure he wanted to delete
	{



		$sqlCAT = "select id from mgtcategory where mgtcategory.id='" . $data . "'";

		$resultCAT = mysql_query($sqlCAT); //Execute query

		while($rowCAT = mysql_fetch_array($resultCAT))
		{ //Display all test cases

			$sqlTC = "select id from mgttestcase where mgttestcase.catid ='" . $data . "'";

			$resultTC = mysql_query($sqlTC); //Execute query

			while($rowTC = mysql_fetch_array($resultTC))
			{ //Display all test cases

				$sqlDeleteTC = "delete from mgttestcase where id='" . $rowTC[0] . "'";
				$resultDeleteTC = mysql_query($sqlDeleteTC); //Execute query

				$sqlDeleteTC = "delete from mgttcarchive where id='" . $rowTC[0] . "'";
				$resultDeleteTC = mysql_query($sqlDeleteTC); //Execute query

			}
			
		}

		$sqlDeleteCAT = "delete from mgtcategory where id='" . $data . "'";
		$resultDeleteCAT = mysql_query($sqlDeleteCAT); //Execute query

		echo "Category Deleted";

		//Refresh the left frame

		$page = $basehref . "/manage/archiveLeft.php?product=" . $product;

		refreshFrame($page); //call the function below to refresh the left frame

	}else //if the user has clicked the delete button on the archive page show the delete confirmation page
	{
		echo "<Form Method='POST' ACTION='manage/editData.php?sure=yes&data=" . $data . "&product=" . $product . "'>";
	
		echo "<table width='100%' border=0>";
		
		echo "<tr><td>Are you sure you wish to delete this category?</td></tr>";

		echo "<tr><td><input type='submit' name='deleteCAT' value='Yes, delete this category'></td></tr>";

		echo "</table>";

		
		
	}//end if


}elseif($_POST['deleteCOM'])
{

	if($_GET['sure'] == 'yes') //check to see if the user said he was sure he wanted to delete
	{


		$sqlCOM = "select id from mgtcomponent where mgtcomponent.id='" . $data . "'";

		$resultCOM = mysql_query($sqlCOM);

		while($rowCOM = mysql_fetch_array($resultCOM))

		{ //Display all test cases


			$sqlCAT = "select id from mgtcategory where mgtcategory.compid='" . $rowCOM[0] . "'";

			$resultCAT = mysql_query($sqlCAT); //Execute query

			while($rowCAT = mysql_fetch_array($resultCAT))
			{ //Display all test cases

				$sqlTC = "select id from mgttestcase where mgttestcase.catid ='" . $rowCAT[0] . "'";

				$resultTC = mysql_query($sqlTC); //Execute query

				while($rowTC = mysql_fetch_array($resultTC)) //Display all test cases
				{ 

					$sqlDeleteTC = "delete from mgttestcase where id='" . $rowTC[0] . "'";
					$resultDeleteTC = mysql_query($sqlDeleteTC); //Execute query

					//echo $sqlDeleteTC . "<br>";

					$sqlDeleteTC = "delete from mgttcarchive where id='" . $rowTC[0] . "'";
					$resultDeleteTC = mysql_query($sqlDeleteTC); //Execute query

				} //end the test case loop
				
				//Delete the inside categories

				$sqlDeleteCAT = "delete from mgtcategory where id='" . $rowCAT[0] . "'";
				$resultDeleteCAT = mysql_query($sqlDeleteCAT); //Execute query

				//echo $sqlDeleteCAT . "<br>";
			
			}//end the category loop


		}//end the component loop

		$sqlDeleteCOM = "delete from mgtcomponent where id='" . $data . "'";
		$resultDeleteCOM = mysql_query($sqlDeleteCOM); //Execute query



		echo "Component Deleted";

		//Refresh the left frame

		$page = $basehref . "/manage/archiveLeft.php?product=" . $product;

		refreshFrame($page); //call the function below to refresh the left frame

	
	}else //if the user has clicked the delete button on the archive page show the delete confirmation page
	{
		echo "<Form Method='POST' ACTION='manage/editData.php?sure=yes&data=" . $data . "&product=" . $product . "'>";
	
		echo "<table width='100%' border=0>";
		
		echo "<tr><td>Are you sure you wish to delete this component?</td></tr>";

		echo "<tr><td><input type='submit' name='deleteCOM' value='Yes, delete this component'></td></tr>";

		echo "</table>";


	}



}elseif($_POST['reorderCAT']) //user has chosen the reorder CAT page
{


	$sqlTC = "select id,name,CATorder from mgtcategory where compid='" . $_GET['data'] . "' order by CATorder,id";
	$resultTC = mysql_query($sqlTC);


	echo "<Form Method='POST' ACTION='manage/orderResults.php?edit=com'>";
	echo "<table width='100%' class=edittable>";
	
	echo "<tr><td class=edittablehdr>Edit This Component's Category Order</td><td class=edittablehdr></td></tr>";

	echo "<td class=edittable><input type='submit' name='editCAT' value='Save'><td class=edittable>";
	
	echo "</tr>";

	while($myrowTC = mysql_fetch_row($resultTC)){
		
	echo "<tr><td>" . $myrowTC[1] . "<td><input type=hidden name='id" . $myrowTC[0] . "' value=" . $myrowTC[0] . "><input name='order" . $myrowTC[0] . "' value=" . $myrowTC[2] . "></tr>";	
			
		
	}

		
	echo "</table>";
	echo "</form>";

}elseif($_POST['reorderTC']) //user has chosen to reorder the test cases of this category
{

	//echo "reorder TC";

	
	$sqlTC = "select id,title,TCorder from mgttestcase where catid='" . $_GET['data'] . "' order by TCorder,id";
	$resultTC = mysql_query($sqlTC);

	echo "<Form Method='POST' ACTION='manage/orderResults.php?edit=cat'>";
		
	echo "<table width='100%' class=edittable>";
	
	echo "<tr><td class=edittablehdr>Edit This Category's Test Case Order</td><td class=edittablehdr></td></tr>";

	echo "<td class=edittable><input type='submit' name='editCAT' value='Save'><td class=edittable>";
	
	echo "</tr>";

	while($myrowTC = mysql_fetch_row($resultTC)){
		
	echo "<tr><td><b>" . $myrowTC[0] . "</b>:" . $myrowTC[1] . "<td><input type=hidden name='id" . $myrowTC[0] . "' value=" . $myrowTC[0] . "><input name='order" . $myrowTC[0] . "' value=" . $myrowTC[2] . "></tr>";	
		
		
		
		
	}


		
	echo "</table>";
	echo "</form>";

}elseif($_POST['moveCom']) //user has chosen to move a component to a different product
{

	$sqlPROD = "select id, name from mgtproduct where id != '" . $_SESSION['product'] . "'";
	$resultPROD = mysql_query($sqlPROD);


	echo "<Form Method='POST' ACTION='manage/copyMoveResults.php'>";
		
	echo "<table width='100%' class=edittable>";
	
	echo "<tr><td class=edittablehdr>Move this component to a different product</td><td class=edittablehdr></td></tr>";

	$numRows = mysql_num_rows($resultPROD);

	if($numRows > 0)
	{

		echo "<td class=edittable><input type='submit' name='submit' value='moveCOM'><td class=edittable>";
	
		echo "</tr>";

		echo "<tr><td><select name=moveCopy>";

	
		while($myrowPROD = mysql_fetch_row($resultPROD)){

			echo "<option name='" . $myrowPROD[1] . "' value='" . $myrowPROD[0] . "'>" . $myrowPROD[1] . "</option>";
		
				
		}

		echo "</select>";

	}

	echo "<input type='hidden' name='id' value='" . $_GET['data'] . "'>";
		
	echo "</td></tr></table>";

	echo "</form>";

}elseif($_POST['moveCat']) //user has chosen to move a category to a different component
{

	$sqlCOM = "select compid, prodid from mgtcategory,mgtcomponent where mgtcategory.id='" . $_GET['data'] . "' and mgtcategory.compid=mgtcomponent.id";

	$resultCOM = mysql_query($sqlCOM);

	$myrowCOM = mysql_fetch_row($resultCOM);

	$sqlPROD = "select id, name from mgtcomponent where prodid=" . $myrowCOM[1] . " and id != '" . $myrowCOM[0] . "'";
	$resultPROD = mysql_query($sqlPROD);


	echo "<Form Method='POST' ACTION='manage/copyMoveResults.php'>";
		
	echo "<table width='100%' class=edittable>";
	
	echo "<tr><td class=edittablehdr>Move this category to a different component</td><td class=edittablehdr></td></tr>";

	$numRows = mysql_num_rows($resultPROD);

	if($numRows > 0)
	{


		echo "<td class=edittable><input type='submit' name='submit' value='moveCAT'><td class=edittable>";
		
		echo "</tr>";

		echo "<tr><td><select name=moveCopy>";

		while($myrowPROD = mysql_fetch_row($resultPROD)){

			echo "<option name='" . $myrowPROD[1] . "' value='" . $myrowPROD[0] . "'>" . $myrowPROD[1] . "</option>";
			
					
		}

		echo "</select>";

	}
	
	echo "<input type='hidden' name='id' value='" . $_GET['data'] . "'>";
		
	echo "</td></tr></table>";
	echo "</form>";


}elseif($_POST['moveTC']) //user has chosen to move a test case to a different category
{

	$sqlTC = "select catid, compid from mgttestcase,mgtcategory where mgttestcase.id='" . $_GET['data'] . "' and mgttestcase.catid=mgtcategory.id";

	$resultTC = mysql_query($sqlTC);

	$myrowTC = mysql_fetch_row($resultTC);

	$sqlCAT = "select id, name from mgtcategory where compid=" . $myrowTC[1] . " and id != '" . $myrowTC[0] . "'";
	$resultCAT = mysql_query($sqlCAT);


	echo "<Form Method='POST' ACTION='manage/copyMoveResults.php'>";
		
	echo "<table width='100%' class=edittable>";
	
	echo "<tr><td class=edittablehdr>Move this test case to a different category</td><td class=edittablehdr></td></tr>";

	$numRows = mysql_num_rows($resultCAT);

	if($numRows > 0)
	{


		echo "<td class=edittable><input type='submit' name='submit' value='moveTC'><td class=edittable>";
		
		echo "</tr>";

		echo "<tr><td><select name=moveCopy>";

		while($myrowCAT = mysql_fetch_row($resultCAT)){

			echo "<option name='" . $myrowCAT[1] . "' value='" . $myrowCAT[0] . "'>" . $myrowCAT[1] . "</option>";
			
					
		}

		echo "</select>";

	}
	
	echo "<input type='hidden' name='id' value='" . $_GET['data'] . "'>";
		
	echo "</td></tr></table>";
	echo "</form>";

}elseif($_POST['copyCom']) //user has chosen to copy a component to a different product
{

	$sqlPROD = "select id, name from mgtproduct";
	$resultPROD = mysql_query($sqlPROD);


	echo "<Form Method='POST' ACTION='manage/copyMoveResults.php'>";
		
	echo "<table width='100%' class=edittable>";
	
	echo "<tr><td class=edittablehdr>Copy this component into a different product</td><td class=edittablehdr></td></tr>";

	$numRows = mysql_num_rows($resultPROD);

	if($numRows > 0)
	{


		echo "<td class=edittable><input type='submit' name='submit' value='copyCOM'><td class=edittable>";
		
		echo "</tr>";

		echo "<tr><td><select name=moveCopy>";

		while($myrowPROD = mysql_fetch_row($resultPROD)){

			echo "<option value=" . $myrowPROD[0] . ">" . $myrowPROD[1] . "</option>";
			
					
		}

		echo "</select>";

	}
	
	echo "<input type='hidden' name='id' value='" . $_GET['data'] . "'>";
		
	echo "</td></tr></table>";
	echo "</form>";

}elseif($_POST['copyCat']) //user has chosen to move a category to a different component
{

	$sqlCOM = "select compid, prodid from mgtcategory,mgtcomponent where mgtcategory.id='" . $_GET['data'] . "' and mgtcategory.compid=mgtcomponent.id";

	$resultCOM = mysql_query($sqlCOM);

	$myrowCOM = mysql_fetch_row($resultCOM);

	$sqlPROD = "select id, name from mgtcomponent where prodid=" . $myrowCOM[1];
	$resultPROD = mysql_query($sqlPROD);


	echo "<Form Method='POST' ACTION='manage/copyMoveResults.php'>";
		
	echo "<table width='100%' class=edittable>";
	
	echo "<tr><td class=edittablehdr>Copy this category into a different component</td><td class=edittablehdr></td></tr>";

	$numRows = mysql_num_rows($resultPROD);

	if($numRows > 0)
	{


		echo "<td class=edittable><input type='submit' name='submit' value='copyCAT'><td class=edittable>";
		
		echo "</tr>";

		echo "<tr><td><select name=moveCopy>";

		while($myrowPROD = mysql_fetch_row($resultPROD)){

			echo "<option value=" . $myrowPROD[0] . ">" . $myrowPROD[1] . "</option>";
			
					
		}

		echo "</select>";

	}

	echo "<input type='hidden' name='id' value='" . $_GET['data'] . "'>";
		
	echo "</td></tr></table>";
	echo "</form>";


}elseif($_POST['copyTC']) //user has chosen to copy a test case to a different category
{

	$sqlTC = "select catid, compid from mgttestcase,mgtcategory where mgttestcase.id='" . $_GET['data'] . "' and mgttestcase.catid=mgtcategory.id";

	$resultTC = mysql_query($sqlTC);

	$myrowTC = mysql_fetch_row($resultTC);

	$sqlCAT = "select id, name from mgtcategory where compid=" . $myrowTC[1];
	$resultCAT = mysql_query($sqlCAT);


	echo "<Form Method='POST' ACTION='manage/copyMoveResults.php'>";
		
	echo "<table width='100%' class=edittable>";
	
	echo "<tr><td class=edittablehdr>Copy this test case into a different category</td><td class=edittablehdr></td></tr>";

	$numRows = mysql_num_rows($resultCAT);

	if($numRows > 0)
	{


		echo "<td class=edittable><input type='submit' name='submit' value='copyTC'><td class=edittable>";
		
		echo "</tr>";

		echo "<tr><td><select name=moveCopy>";

		while($myrowCAT = mysql_fetch_row($resultCAT)){

			echo "<option value=" . $myrowCAT[0] . ">" . $myrowCAT[1] . "</option>";
			
					
		}

		echo "</select>";

	}
	
	echo "<input type='hidden' name='id' value='" . $_GET['data'] . "'>";
		
	echo "</td></tr></table>";
	echo "</form>";

}


?>

