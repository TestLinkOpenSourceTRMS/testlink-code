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

?>
	<table width=100% class=helptable>
		<tr>
			<td class=helptablehdr>Welcome To The Test Platform Management Section</td>
		</tr>
		</table>
		
		<table width=100% class=helptable>
		<tr>
			<td class=helptablehdr width="15%">Purpose:</td>
			<td class=helptable>Test</td>
		<tr>
			<td class=helptablehdr>Get Started:</td>
			<td class=helptable>Test</td>
		</tr>
	</table>
<?

}

$data = $_GET['data'];
$project = $_SESSION['project'];

if($_GET['edit'] == 'project')
{
	$sqlTC = "select id, name from project where id='" . $data . "'";
	$resultTC = mysql_query($sqlTC);
	$myrowTC = mysql_fetch_row($resultTC);

?>
	<table width='100%' border="1" class=tctable>
		<form Method='POST' ACTION='platform/manageData2.php'>
		<input type='submit' name='newCon' value='Create Platform Container'>
		<tr>
			<td class="tctablehdr">
				<b>Project</b>
				
				<input type='hidden' name="data" value="<? echo $data ?>"/>
				<input type='hidden' name="action" value="createContainer"/>
			</td>
		</tr>
		<tr>
			<td class="tctable">
				<? echo $myrowTC[1] ?>
			</td>
		</tr>
		</form>
	</table>
<?
}
if($_GET['edit'] == 'container')
{
	$sqlTC = "select id, name from platformcontainer where id='" . $data . "'";
	$resultTC = mysql_query($sqlTC);
	$myrowTC = mysql_fetch_row($resultTC);
	
?>
	<table width='100%' border="1" class="tctable">
		<form Method='POST' ACTION='platform/manageData2.php'>
		<input type='submit' name='editContainer' value='Edit This Container'> 
		<input type='submit' name='createPlatform' value='Create Platform'>
		<tr>
			<td class="tctablehdr">
				<b>Platform Container</b>
				
				<input type='hidden' name="data" value="<? echo $data ?>"/>
			</td>
		</tr>
		<tr>
			<td class="tctable">
				<? echo $myrowTC[1] ?>
			</td>
		</tr>
		</form>
	</table>
<?

}
elseif($_GET['edit'] == 'platform')
{

	$sqlTC = "select id,name from platform where id=" . $data;

	$resultTC = mysql_query($sqlTC);
	$myrowTC = mysql_fetch_row($resultTC);
	
	?>	
	<table width='100%' border="1" class="tctable">
		<form Method='POST' ACTION='platform/manageData2.php'>
		<input type='submit' name='editPlatform' value='Edit This Platform '>
		<tr>
			<td class="tctablehdr">
				<b>Platform</b>
				
				<input type='hidden' name="data" value="<? echo $data ?>"/>
			</td>
		</tr>
		<tr>
			<td class="tctable">
				<? echo $myrowTC[1] ?>
			</td>
		</tr>
		</form>
	</table>	
<?
}
?>