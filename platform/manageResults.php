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
require_once("../functions/refreshLeft.php"); //This adds the function that refreshes the left hand frame

?>

<LINK REL="stylesheet" TYPE="text/css" HREF="kenny.css">

<table border="1" width="100%" class="tctable">
<tr>
	<td class="tctablehdr"><b>
<?


if($_POST['action'] == 'createContainer')
{
	$sql = "insert into platformcontainer (projid, name) values (" . $_POST['projectId'] . ",'" . $_POST['name'] . "')";
	
	$result = mysql_query($sql);

	//echo $sql;

	?>
	
	Container Created
	</td></tr>
	<tr>
	<td class="tctable">Name: <? echo $_POST['name'] ?></td>
	</tr>

	<?

}

if($_POST['action'] == 'editContainer')
{
	$sql = "UPDATE platformcontainer set name ='" . $_POST['name'] . "' where id=" . $_POST['id'];
	
	$result = mysql_query($sql);

	//echo $sql;

	?>
	
	Container Edited
	</td></tr>
	<tr>
	<td class="tctable">Name: <? echo $_POST['name'] ?></td>
	</tr>

	<?

}

if($_POST['action'] == 'deleteContainer')
{
	$sql = "insert into platformcontainer (projid, name) values (" . $_POST['projectId'] . ",'" . $_POST['name'] . "')";
	
	$result = mysql_query($sql);

	echo $sql;

}

if($_POST['action'] == 'createPlatform')
{
	$sql = "insert into platform (containerId, name) values (" . $_POST['containerId'] . ",'" . $_POST['name'] . "')";
	
	$result = mysql_query($sql);

	//echo $sql;

	?>
	
	Platform Created
	</td></tr>
	<tr>
	<td class="tctable">Name: <? echo $_POST['name'] ?></td>
	</tr>

	<?

}

if($_POST['action'] == 'editPlatform')
{
	$sql = "update platform set name ='" . $_POST['name'] . "' where id=" . $_POST['id'];
	
	$result = mysql_query($sql);

	//echo $sql;

	?>
	
	Platform Edited
	</td></tr>
	<tr>
	<td class="tctable">Name: <? echo $_POST['name'] ?></td>
	</tr>

	<?

}

if($_POST['action'] == 'deletePlatform')
{
	$sql = "insert into platformcontainer (projid, name) values (" . $_POST['projectId'] . ",'" . $_POST['name'] . "')";
	
	$result = mysql_query($sql);

	echo $sql;

}

$page =  $basehref . "/platform/manageLeft.php";

refreshFrame($page); //call the function below to refresh the left frame

?>

</tr>
</table>