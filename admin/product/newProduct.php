<?php

////////////////////////////////////////////////////////////////////////////////
//File:     newProduct.php
//Author:   Chad Rosen
//Purpose:  This page manages the creation of new products.
////////////////////////////////////////////////////////////////////////////////

//session_start();

require_once("../../functions/header.php");

  session_start();
  doDBConnect();
  doHeader();
  doNavBar();

?>

<LINK REL="stylesheet" TYPE="text/css" HREF="kenny.css">

<br>

<?

if(!$_POST['editProduct'] && !$_POST['newProduct'])
{
	$sql = "select id,login, password, rightsid from user";
	$result = mysql_query($sql);

	echo "<FORM method='post' ACTION='admin/product/newProduct.php'>";

	echo "<table class=userinfotable width='45%'><tr><td bgcolor='#CCCCCC'><b>Enter New Product</td></tr></table>";

	echo "<table class=userinfotable width='45%'>";

	echo "<tr><td>Name:</td><td><input type='text' name='name'></td></tr>";
	
	echo "</table>";

	echo "<br><input type='submit' NAME='newProduct' value='New'>";

	echo "</form>";

}

elseif($_POST['newProduct']) //if the user has pressed the create new button
{

	$name = $_POST['name'];
	
	$sql = "insert into mgtproduct (name) values ('" . $name . "')";

	if ( mysql_query($sql) ) {
		echo "Successfully added new product " . $name;
	} else {
		echo "Failed to add new product " . $name . " (does a product with this name already exist?)";
	}
}

?>
