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

<table border="0" width="100%" class="tctable">
	<form method="POST" action="platform/manageResults.php">
	<input type="submit" value="submit"/>
<?


if($_POST['action'] == 'createContainer')
{
	?>
	
	<tr>
		<td colspan="2" class="tctablehdr"><b>Create Container</td>
	</tr>
	<tr>
		<td class="tctable">Name:</td>
		<td>
			<input type="text" name="name" size="50"/>
			<input type="hidden" name="projectId" value="<? echo $_POST['data'] ?>"/>
			<input type="hidden" name="action" value="createContainer"/>
		</td>
	</tr>

	<?


}
if($_POST['editContainer'] != null)
{

	$sqlTC = "select id, name from platformcontainer where id='" . $_POST['data'] . "'";
	$resultTC = mysql_query($sqlTC);
	$myrowTC = mysql_fetch_row($resultTC);

	?>
	
	<tr>
		<td class="tctablehdr" colspan="2"><b>Edit Container</td>
	</tr>
	<tr>
		<td class="tctable">Name:</td>
		<td>
			<input type="text" name="name" value="<? echo $myrowTC[1] ?>"/>
			<input type="hidden" name="id" value="<? echo $myrowTC[0] ?>"/>
			<input type="hidden" name="action" value="editContainer"/>
		</td>
	</tr>

	<?
}
if($_POST['action'] == 'deleteContainer')
{
	?>
	
	<tr>
		<td colspan="2"><b>Delete container</td>
	</tr>
	<tr>
		<td>Name:</td>
		<td>
			<? echo "name" ?>
		</td>
	</tr>

	<?
}
if($_POST['createPlatform'] != null)
{
	?>
	
	<tr>
		<td colspan="2" class="tctablehdr"><b>Create Platform</td>
	</tr>
	<tr>
		<td class="tctable">Name:</td>
		<td>
			<input type="text" name="name" size="50"/>
			<input type="hidden" name="containerId" value="<? echo $_POST['data'] ?>"/>
			<input type="hidden" name="action" value="createPlatform"/>
		</td>
	</tr>

	<?
}
if($_POST['editPlatform'] != null)
{

	$sqlTC = "select id, name from platform where id='" . $_POST['data'] . "'";
	$resultTC = mysql_query($sqlTC);
	$myrowTC = mysql_fetch_row($resultTC);

	?>
		
	<tr>
		<td class="tctablehdr" colspan="2"><b>Edit Container</td>
	</tr>
	<tr>
		<td>Name:</td>
		<td>
			<input type="text" name="name" value="<? echo $myrowTC[1] ?>"/>
			<input type="hidden" name="id" value="<? echo $myrowTC[0] ?>"/>
			<input type="hidden" name="action" value="editPlatform"/>
		</td>
	</tr>

	<?
}
if($_POST['action'] == 'deletePlatform')
{
	?>
	
	<tr>
		<td colspan="2"><b>Delete Platform</td>
	</tr>
	<tr>
		<td>Name:</td>
		<td>
			<? echo "name" ?>
		</td>
	</tr>

	<?
}

?>
	</form>
</table>