<?php

////////////////////////////////////////////////////////////////////////////////
//File:     editDataResults.php
//Author:   Chad Rosen
//Purpose:  This page presents the edit data results.
////////////////////////////////////////////////////////////////////////////////

require_once("../functions/header.php");
doSessionStart();
doDBConnect();
doHeader();

require_once("../functions/refreshLeft.php"); //This adds the function that refreshes the left hand frame
require_once("../functions/orderArray.php"); //I need this function to reorder the new test case array that is passed in

?>

<?

require_once('../htmlarea/textArea.php');

echo "\n\n\n";

?>

</head>

<?

$product = $_SESSION['product'];

if($_POST['newCOM'])
{
	$sql = "insert into mgtcomponent (name,intro,scope,ref,method,lim,prodid) values ('" . $_POST['name'] . "','" . $_POST['intro'] . "','" . $_POST['scope'] . "','" . $_POST['ref'] . "','" . $_POST['method'] . "','" . $_POST['lim'] . "','" . $product . "')";


	$result = mysql_query($sql); //Execute query

	$newCOMID =  mysql_insert_id();	 //Grab the id of the Component just entered

	echo "<hr>Click <a href='manage/archiveData.php?&edit=component&data=" . $newCOMID .  "'>here</a> to return to Component just edited";

	$highLight = "&edit=component&data=" . $newCOMID;


}elseif($_POST['newCAT'])
{

	$sql = "insert into mgtcategory (name,objective,config,data,tools,compid) values ('" . $_POST['name'] . "','" . $_POST['objective'] . "','" . $_POST['config'] . "','" . $_POST['data'] . "','" . $_POST['tools'] . "','" . $_GET['catID'] . "')";

	//echo $sql;

	echo "Category Has Been Added";

	$result = mysql_query($sql); //Execute query

	$newCATID =  mysql_insert_id();	 //Grab the id of the Component just entered

	echo "<hr>Click <a href='manage/archiveData.php?&edit=category&data=" . $newCATID .  "'>here</a> to return to Component just edited";

	$highLight = "&edit=category&data=" . $newCATID;

}elseif($_POST['newTC'])
{

	$version = 1;


	$tcArray = orderArray($_POST); //reorder the test cases so that they are numeric

	//loop through every four post variables until we've gone through every test case

	for($i = 1; $i < count($tcArray); $i = $i + 4)
	{

		$title = $tcArray[$i]; //title
		$summary = $tcArray[$i + 1]; //summary
		$steps = $tcArray[$i + 2]; //steps
		$exResult = $tcArray[$i + 3]; //expected result

		if($title == "") //if the user didn't put in a tile ignore the case
		{

			//echo "Test Cases with blank titles are ignored<br>";

		}else
		{

			$sql = "insert into mgttestcase (title,author,summary,steps,exresult,version,catid) values ('" . $title . "','" . $_SESSION['user'] . "','" . $summary . "','" . $steps . "','" . $exResult . "','" . $version . "','" . $_GET['data'] . "')";

			$result = mysql_query($sql); //Execute query

			$newTCID =  mysql_insert_id();	 //Grab the id of the Component just entered

			echo "Test Case " . $newTCID . ": " . $title . " Has Been Added<br>";
			
		
		}


	}

	echo "<hr>Click <a href='manage/archiveData.php?&edit=testcase&data=" . $newTCID .  "'>here</a> to return to Test Case just edited";

	echo "<br><br><a href='manage/archiveData.php?prodid=" . $_SESSION['product'] . "&edit=category&data=" . $_GET['data'] . "' target='mainFrame'>Add more cases to this category?</a>";

	$highLight = "&edit=testcase&data=" . $newTCID;

}



//This page displays the result of the user picking to edit something

if($_POST['editCOM']) //editing a component
{

	
	$sql = "UPDATE mgtcomponent set name ='" . $_POST['name'] . "', intro ='" .  $_POST['intro'] . "', scope='" . $_POST['scope'] . "', ref='" . $_POST['ref'] . "', method='" . $_POST['method'] . "', lim='" . $_POST['lim'] . "' where id='" . $_POST['id'] . "'";

	$result = mysql_query($sql); //Execute query

	echo "Component has been edited";

	echo "<hr>Click <a href='manage/archiveData.php?edit=component&data=" . $_POST['id'] .  "'>here</a> to return to Component just edited";
	

}elseif($_POST['editCAT']) //Editing a category
{

	$sql = "UPDATE mgtcategory set name ='" . $_POST['name'] . "', objective ='" . $_POST['objective'] . "', config='" . $_POST['config'] . "', data='" . $_POST['data'] . "', tools='" . $_POST['tools'] . "' where id='" . $_POST['id'] . "'";

	$result = mysql_query($sql); //Execute query

	echo "Category has been edited";

	echo "<hr>Click <a href='manage/archiveData.php?prodid=" . $foo . "&edit=category&data=" . $_POST['id'] .  "'>here</a> to return to Category just edited";
	

}elseif($_POST['editTC']) //saving a test case but not archiving it
{

	//Since the keywords are being passed in as an array I need to seperate them into a comma seperated string

	if(count($_POST['keywords']) > 0) //if there actually are values passed in
	{

		$i=0; //counter

		foreach($_POST['keywords'] as $bob) //for each of the array values
		{
			$keywords .= $_POST['keywords'][$i] . ","; //Build this string
				
				$i++; //increment
		}

	}


	
	$version = 1 + $_POST['version']; //everytime a test case is saved I update its version

	//SQL Code to update the testcase with its new values

	$sql = "UPDATE mgttestcase set keywords='" . $keywords . "', version='" . $version . "', title='" . $_POST['title'] . "', author ='" . $_SESSION['user'] . "', summary='" . $_POST['summary'] . "', steps='" . $_POST['steps'] . "', exresult='" . $_POST['exresult'] . "' where id='" . $_POST['id'] . "'";

	$result = mysql_query($sql); //Execute query

	echo "Test Case has been edited";

	echo "<hr>Click <a href='manage/archiveData.php?&edit=testcase&data=" . $_POST['id'] .  "'>here</a> to return to Test Case just edited";

}

elseif($_POST['archive'])
{

	if(count($_POST['keywords']) > 0) //if there actually are values passed in
	{

		$i=0; //counter

		foreach($_POST['keywords'] as $bob) //for each of the array values
		{
			$keywords .= $_POST['keywords'][$i] . ","; //Build this string
				
				$i++; //increment
		}

	}

	//If the user chooses to save the test case and archive it

	$version = 1 + $_POST['version'];

	$sql = "UPDATE mgttestcase set keywords='" . $keywords ."', version='" . $version . "', title='" . $_POST['title'] . "', author ='" . $_SESSION['user'] . "', summary='" . $_POST['summary'] . "', steps='" . $_POST['steps'] . "', exresult='" . $_POST['exresult'] . "' where id='" . $_POST['id'] . "'";

	$result = mysql_query($sql); //Execute query


	//This block of code will add a new to the test case archive

	$sql = "insert into mgttcarchive (title,author,summary,steps,exresult,version,id,keywords) values ('" . $_POST['title'] . "','" . $_SESSION['user'] . "','" . $_POST['summary'] . "','" . $_POST['steps'] . "','" . $_POST['exresult'] . "','" . $version . "','" . $_POST['id'] . "','" . $keywords . "')";

	//echo $sql;

	$result = mysql_query($sql); //Execute query


	echo "Test Case has been edited and archived";

	//Refresh the left frame

}

	if($_POST['editCOM'])
	{
		$highLight = "&edit=component&data=" . $_POST['id'];
	}
	else if($_POST['editCAT'])
	{
		$highLight = "&edit=category&data=" . $_POST['id'];
	}
	else if($_POST['editTC'])
	{
		$highLight = "&edit=testcase&data=" . $_POST['id'];


	}

	$page =  $basehref . "/manage/archiveLeft.php?product=" . $product . $highLight;

	refreshFrame($page); //call the function below to refresh the left frame


//This section displays the result of the user picking to create a new object
