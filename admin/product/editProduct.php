<?php

////////////////////////////////////////////////////////////////////////////////
//File:     editProduct.php
//Author:   Chad Rosen
//Purpose:  This page allows users to edit/delete products.
////////////////////////////////////////////////////////////////////////////////

//session_start();

require_once("../../functions/header.php");

  session_start();
  doDBConnect();
  doHeader();
  doNavBar();

?>

<!--This function adds the color picking pallet-->

<script language=JavaScript src="color/picker.js"></script>

<br>

<?

if(!$_POST['editProduct'] && !$_POST['newProduct'])
{

	$product = $_SESSION['product'];

	echo "<FORM name='editProduct' method='post' ACTION='admin/product/editProduct.php'>";
	
	$sql = "select id, name, color from mgtproduct where id = " . $product;
	
	$result = mysql_query($sql);
	if (!$result) {
		echo "Unable to locate this project.";
		exit;
	}

	$myrow = mysql_fetch_row($result);
	$id = $myrow[0];
	$name = $myrow[1];
	$color = $myrow[2];

	echo "<input type='hidden' name=id value='" . $id . "'>";
	echo "<table class=edittable width=300>";
	echo "<tr><td class=edittablehdr colspan=2>Edit Product #" . $id;
	echo "<tr><td class=edittable>Name<td class=edittable><input type=text name=name value='" . $name . "'>";
	echo "<tr><td class=edittable>Color<td class=edittable><input type=text name=color value='" . $color . "'>";

	//this function below calls the color picker javascript function. It can be found in the color directory

	echo "<a href=javascript:TCP.popup(document.forms['editProduct'].elements['color'])>";
	
	echo '<img width="15" height="13" border="0" alt="Click Here to Pick up the color" src="color/img/sel.gif"></a>';

	echo "<tr><td class=edittable>Delete?<td class=edittable><input type=checkbox name=delete>";
	echo "</table>";

	echo "<br><input type='submit' NAME='editProduct' value='Update'>";

	echo "</form>";

}elseif($_POST['editProduct'])
{

	if ($_POST['delete']) {
		$id    = $_POST['id'];

		$sql = "delete from mgtproduct where id='" . $id . "'";
		$result = mysql_query($sql);
		if ($result) {
			echo "Project deleted successfully.";
			//XXX - there is a lot more we *could* cleanup
			//delete all of the components from that project
			//delete all of the categories from that project
				//delete all of the test cases from that project
			//delete all of the results from that project
			//delete all of the bugs from that project
			//delete all of the builds from that project
			//Delete all of the priority
			//Delete all of the milestone
		} else {
			echo "Error deleting project.";
		}
		exit;
	} else {
		$id    = $_POST['id'];
		$name  = $_POST['name'];
		$color = $_POST['color'];

		$sql = "update mgtproduct set name='" . $name . "', color='" . $color . "' where id='" . $id . "'";
		$result = mysql_query($sql);
		if ($result) {
			echo "Product updated.";
			// now clear the product style cache
			$_SESSION['productstyle'] = "";
		} else {
			echo "<font color=red>Error updating product.</font>";
		}
	}

}

?>
