<?php

////////////////////////////////////////////////////////////////////////////////
//File:     orderResults.php
//Author:   Chad Rosen
//Purpose:  This page manages the ordering of results.
////////////////////////////////////////////////////////////////////////////////

require_once("../functions/header.php");
doSessionStart();
doDBConnect();
doHeader();

require_once("../functions/refreshLeft.php"); //This adds the function that refreshes the left hand frame

require_once("../functions/orderArray.php"); //This adds the function that reorders arrays

?>

<head>

<?

echo "\n\n\n";

?>

</head>

<?


$newArray = orderArray($_POST); //Reorder the POST array to numeric

$i = 1; //Counter value that the loops below use. Ignore first value which is the product and second which is the submit button


if($_GET['edit'] == 'com') //Reordering a component's categories
{
	
	while ($i < count($newArray)) //Loop for the entire size of the array
	{

		$id = $newArray[$i];
		$order = $newArray[$i + 1];

		$sql = "UPDATE mgtcategory set CATorder='" . $order . "' where id='" . $id . "'";

		$result = mysql_query($sql); //Execute query

		$i = $i + 2;


	}

	



	echo "Categories have been edited";

	//Refresh the left frame

	

}elseif($_GET['edit'] == 'cat') //Reordering a category's test cases
{

	while ($i < count($newArray)) //Loop for the entire size of the array
	{

		$id = $newArray[$i];
		$order = $newArray[$i + 1];

		$sql = "UPDATE mgttestcase set TCorder='" . $order . "' where id='" . $id . "'";

		$result = mysql_query($sql); //Execute query

		$i = $i + 2;


	}

	echo "Test Cases have been edited";

	//Refresh the left frame

}

$page = "archiveLeft.php?prodid=" . $_SESSION['product'];

refreshFrame($page); //call the function below to refresh the left frame
	
