<?

////////////////////////////////////////////////////////////////////////////////
//File:     projectRights.php
//Author:   Chad Rosen
//Purpose:  This page manages the rights on a project basis.
////////////////////////////////////////////////////////////////////////////////

require_once("../../functions/header.php");

  session_start();
  doDBConnect();
  doHeader();
  //doNavBar();

?>

<LINK REL='stylesheet' TYPE='text/css' HREF='kenny.css'>

<script>


//This function takes a div tag and whether or not you want the checkboxes checked or not
//The function then goes through all of the elements of the div tag that is passed in and
//if they are checkboxes

function box(myDiv, checkBoxStatus){
	var frm;
	var elemType;

	frm = document.getElementById(myDiv).getElementsByTagName('input');
	for(var i = 0; i < frm.length; i++){
		elemType = frm[i].type;		
		
		if(elemType == "checkbox"){
			frm[i].checked = checkBoxStatus;
		}
	}
}


</script>

<?

////////Building the header with the correct projects


if($_GET['view'] == 'user') //view all projects which this user has rights to
{
	$sqlName = "select login from user where id=" . $_GET['id'];
	$sqlNameResult = mysql_query($sqlName);
	$sqlNameRow = mysql_fetch_row($sqlNameResult);

	echo "<div id=checkAll>";
	echo "<b>Assign project rights for user " . $sqlNameRow[0] . "</b>";
	
	echo "<Form Method='POST' ACTION='admin/user/projRightsResult.php?view=user&id=" . $_GET['id'] . "'>";

	echo "<input type=submit name=submit value=save>";
	echo "<input type='button' name='foo' onclick='box(\"checkAll\", true)' value='Check All'>";
	echo "<input type='button' name='foo' onclick='box(\"checkAll\", false)' value='Uncheck All'><br>";
	
	echo "<table class=userinfotable width='100%'>";

	$sql = "select id,name from project where active='y'";
	$projectResult = mysql_query($sql);
	$showProject = mysql_num_rows($projectResult);
	
	if($showProject > 0)
	{
		echo "<tr><td class=userinfotablehdr>Project</td><td class=userinfotablehdr>Rights?</td><br>";

		//Run the query

		$userResult = mysql_query($sql);

		while ($myrowProject = mysql_fetch_row($userResult)) //Display all the users until we run out
		{
			
			//Query the projrights table

			$sql = "select userid from projrights where userid=" . $_GET['id'] . " and projid = " . $myrowProject[0];

			$rightsResult = mysql_query($sql);
			$numRows = mysql_num_rows($rightsResult);

			//does the user/project exist

			if($numRows > 0) //yes
			{

				echo "<tr><td bgcolor=" . $cellColor. ">" . $myrowProject[1] . "</td><td bgcolor=" . $cellColor. "><input type=checkbox name='proj" . $myrowProject[0] . "' value=" . $myrowProject[0] . " checked></td></tr>";

			}else //no
			{
					
				echo "<tr><td bgcolor=" . $cellColor. ">" . $myrowProject[1] . "</td><td bgcolor=" . $cellColor. "><input type=checkbox name='proj" . $myrowProject[0] . "' value=" . $myrowProject[0] . " ></td></tr>";


			}//end else
					
		}
	}		

	echo "</table>";


	echo "<br><input type=submit name=submit value=save>";


	echo "</form>";

	echo "</div>";

}elseif($_GET['view'] == 'project') //view all of the users that can currently see this project
{
		
	$sqlName = "select name from project where id=" . $_GET['id'];
	$sqlNameResult = mysql_query($sqlName);
	$sqlNameRow = mysql_fetch_row($sqlNameResult);

	echo "<div id=checkAll>";
	echo "<b>Assign these users the rights to see project " . $sqlNameRow[0] . "</b>";
	
	echo "<Form Method='POST' ACTION='admin/user/projRightsResult.php?view=project&id=" . $_GET['id'] . "'>";

	echo "<input type=submit name=submit value=save>";
	echo "<input type='button' name='foo' onclick='box(\"checkAll\", true)' value='Check All'>";
	echo "<input type='button' name='foo' onclick='box(\"checkAll\", false)' value='Uncheck All'><br>";
	
	echo "<table class=userinfotable width='100%'>";

	$sql = "select id,login from user";
	$userResult = mysql_query($sql);
	$showUser = mysql_num_rows($userResult);
	
	if($showUser > 0)
	{
		echo "<tr><td class=userinfotablehdr>User</td><td class=userinfotablehdr>Rights?</td><br>";

		//Run the query

		$projResult = mysql_query($sql);

		while ($myrowProject = mysql_fetch_row($userResult)) //Display all the users until we run out
		{
			
			//Query the projrights table

			$sql = "select userid from projrights where projid=" . $_GET['id'] . " and userid = " . $myrowProject[0];

			$rightsResult = mysql_query($sql);
			$numRows = mysql_num_rows($rightsResult);

			//does the user/project exist

			if($numRows > 0) //yes
			{
				
				echo "<tr><td bgcolor=" . $cellColor. ">" . $myrowProject[1] . "</td><td bgcolor=" . $cellColor. "><input type=checkbox name='proj" . $myrowProject[0] . "' value=" . $myrowProject[0] . " checked></td></tr>";

			}else //no
			{
					
				echo "<tr><td bgcolor=" . $cellColor. ">" . $myrowProject[1] . "</td><td bgcolor=" . $cellColor. "><input type=checkbox name='proj" . $myrowProject[0] . "' value=" . $myrowProject[0] . "></td></tr>";

			}//end else
					
		}
	}

	echo "</table>";


	echo "<br><input type=submit name=submit value=save>";


	echo "</form>";

	echo "</div>";

	

}else
{
		echo "<table class=helptable width=100%>";
		echo "<tr><td class=helptabletitle>Project/User Rights</td></tr></table>";

		echo "<table class=helptable width=100%>";

		echo "<tr><td class=helptablehdr><b>Purpose:</td><td class=helptable>This page allows users to define the list of approved users for each project</td></tr>";
		echo "<tr><td class=helptablehdr><b>Getting Started:</td><td class=helptable>";
		
		echo "<ol><li>Choose to either manage rights by users or projects<li>Clicking on a user will allow you to select all of the projects that the user is able to see<li>Selecting a project allows you to select all of the users that are a part of that project</td></tr>";
		
		echo "</table>";
	
	
}



?>
