<?

////////////////////////////////////////////////////////////////////////////////
//File:     categoryResults.php
//Author:   Chad Rosen
//Purpose:  This file is for generating category results.
////////////////////////////////////////////////////////////////////////////////

require_once("../../functions/header.php");

  session_start();
  doDBConnect();
  doHeader();

$edit = $_GET['edit'];
$id = $_POST['id'];
$ownerId = $_POST['owner'];
$importance = $_POST['importance'];
$risk = $_POST['risk'];

if($ownerId != 0)
{
	$sqlOwner = "select login from user where id=$ownerId";
	$result = mysql_fetch_array(@mysql_query($sqlOwner)) or die ("cannot get owner");
	$owner = $result[0];
	
}
else
{
	$owner = "None";
}

if($edit == "component")
{
	$sqlCom = "select testcase.id,mgttcid,title from component,category,testcase where component.id=$id and component.id=category.compid and category.id=testcase.catid";

	$result = @mysql_query($sqlCom);

	editTestCase($result);

}

if($edit == "category")
{
	$sqlCat = "select testcase.id,mgttcid,title from category,testcase where category.id=$id and category.id=testcase.catid";
	
	$result = @mysql_query($sqlCat);

	editTestCase($result);

}

if($edit == "testcase")
{
	$sqlTc = "select testcase.id,mgttcid,title from testcase where id=$id";
	$result = @mysql_query($sqlTc);
	editTestCase($result);
}

function editTestCase($result)
{
	global $owner, $importance, $risk;
	
	?>
	<table border = 1 width='100%'>
		<tr>
			<td bgcolor='#CCCCCC'>
				<h2>Results</h2>
			</td>
		</tr>
	</table>

	<table border=1 width='100%'>
		<tr>
			<td bgcolor='#99CCFF'>
				<b>Id
			</td>
			<td bgcolor='#99CCFF'>
				<b>Test Case Name
			</td>
			<td bgcolor='#99CCFF'>
				<b>Importance
			</td>
			<td bgcolor='#99CCFF'>
				<b>Risk
			</td>
			<td bgcolor='#99CCFF'>
				<b>Owner
			</td>
		</tr>
	<?

	while($row = mysql_fetch_array($result))
	{

		$sqlUpdate = "update testcase set importance='$importance', risk='$risk', owner='$owner' where id=$row[0]";
		@mysql_query($sqlUpdate) or die("could not edit testcase $row[0]");

		?>
		<tr>
			<td>
				<? echo $row[1] ?>
			</td>
			<td>
				<? echo $row[2] ?>
			</td>
			<td>
				<? echo $importance?>
			</td>
			<td>
				<? echo $risk ?>
			</td>
			<td>
				<? echo $owner ?>
			</td>
		</tr>
		<?
	}
	
	?> </table> <?
}
?>
