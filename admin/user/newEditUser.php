<?php

////////////////////////////////////////////////////////////////////////////////
//File:     newEditUser.php
//Author:   Chad Rosen
//Purpose:  This page manages the page that allows adding, editing, and
//          deleting users.
////////////////////////////////////////////////////////////////////////////////

  require_once("../../functions/header.php");

  session_start();
  doDBConnect();
  doHeader();

?>


<LINK REL="stylesheet" TYPE="text/css" HREF="kenny.css">


<?

if(!$_POST['editUser'] && !$_POST['newUser'])
{


	$sql = "select id,login, rightsid, first, last, email from user";

	//echo $sql;

	echo "<table class=userinfotable width='100%'><tr><td bgcolor='#CCCCCC'><b>Existing Users</td></tr></table>\n";

	echo "<table class=userinfotable width='100%'><tr><td bgcolor='#99CCFF'>Login</td><td bgcolor='#99CCFF'>First</td><td bgcolor='#99CCFF'>Last</td><td bgcolor='#99CCFF'>Email</td><td bgcolor='#99CCFF'>Role</td><td bgcolor='#99CCFF'>Delete?</td></tr>\n\n";

	$result = mysql_query($sql);

	echo "<FORM method='post' ACTION='admin/user/newEditUser.php'>";
	


	while ($myrow = mysql_fetch_row($result)) 
		{

			$id=$myrow[0];
			$login=$myrow[1];
			$rightsid=$myrow[2];
			$first = $myrow[3];
			$last = $myrow[4];
			$email = $myrow[5];


			//Grabbing all of the different rights

 
			//Determining the users rights

			$rightsSQL = "select id, role from rights where id='" . $rightsid . "'";
			$rightsResult = mysql_query($rightsSQL);
			$rights = mysql_fetch_row($rightsResult);

			
			echo "<tr><td><input type='hidden' name='id" . $id . "' " .  "value='" . $id . "'><textarea rows='1' cols='10' name='name" . $id . "'>" . $login . "</textarea></td><td><textarea rows='1' name='first" . $id. "'>" . $first . "</textarea></td></td><td><textarea rows='1' name='last" . $id. "'>" . $last . "</textarea></td></td><td><textarea rows='1' name='email" . $id. "'>" . $email . "</textarea><td>";


			//This code below fills in the select box of all the possible rights accounts and highlights
			//the current user's rights

			$allRightsSQL = "select id, role from rights";
			$allRightsResult = mysql_query($allRightsSQL);
			
			echo "<SELECT NAME='rights" . $id . "'>";

			while ($allRights = mysql_fetch_row($allRightsResult))
			{
				
				if($allRights[0] == $rights[0])
				{

					echo "<OPTION VALUE='" . $allRights[0] ."' SELECTED>" . $allRights[1];	

				}else
				{
				
					echo "<OPTION VALUE='" . $allRights[0] ."'>" . $allRights[1];	
				
				}

								
			}
			
			echo "</SELECT>";
			
			
			echo "</td><td><input type='checkbox' name='check" . $id . "'></tr>\n\n";

		}//END WHILE

	echo "</table>";

	echo "<br><input type='submit' NAME='editUser' value='Edit'>";

	echo "</form>";

	//This code grabs all the possible rights and displays them for the new user imput

	$allRightsSQL = "select id, role from rights";
	$allRightsResult = mysql_query($allRightsSQL);
			
	while ($allRights = mysql_fetch_row($allRightsResult))
	{
								
		$rightsOptions .= "<OPTION VALUE='" . $allRights[0] ."'>" . $allRights[1];

								
	}


	//Begin the new user form


	echo "<FORM method='post' ACTION='admin/user/newEditUser.php'>";

	echo "<table class=userinfotable width='45%'><tr><td bgcolor='#CCCCCC'><b>Enter New User</td></tr></table>";

	echo "<table class=userinfotable width='45%'>";

	echo "<tr><td>Login:</td><td><input type='text' name='login'></td></tr>";
	echo "<tr><td>Password:</td><td><input type='text' name='password'></td></tr>";
	echo "<tr><td>First:</td><td><input type='text' name='first'></td></tr>";
	echo "<tr><td>Last:</td><td><input type='text' name='last'></td></tr>";
	echo "<tr><td>Email:</td><td><input type='text' name='email'></td></tr>";
	echo "<tr><td>Rights:</td><td><select name='rights'>" . $rightsOptions . "</SELECT></td></tr>";
	
	echo "</table>";

	echo "<br><input type='submit' NAME='newUser' value='New'>";

	echo "</form>";

}

elseif($_POST['newUser'])
{

	$login = $_POST['login'];
	$password = $_POST['password'];
	$rightsid = $_POST['rights'];
	$first = $_POST['first'];
	$last = $_POST['last'];
	$email = $_POST['email'];
	
	$sql = "insert into user (login,password,rightsid,first,last,email) values ('" . $login . "','" . $password . "','" . $rightsid . "','" . $first . "','" . $last . "','" . $email . "')";

	$result = mysql_query($sql);

	echo "<table class=userinfotable width='100%'>";

	echo "<tr><td bgcolor='#99CCFF' wdith='14%'>login</td><td bgcolor='#99CCFF' wdith='14%'>password</td><td bgcolor='#99CCFF' wdith='14%'>first</td><td bgcolor='#99CCFF' wdith='14%'>last</td><td bgcolor='#99CCFF' wdith='14%'>email</td><td bgcolor='#99CCFF' wdith='14%'>sql</td></tr>";

	echo "<tr><td>" . $login . "</td><td>" . $password . "</td><td>" . $first . "</td><td>" . $last . "</td><td>" . $email . "</td><td>" . $sql . "</td></tr>";

	echo "</table>";

	



}elseif($_POST['editUser'])
{

	$i = 0; //start a counter

	//It is necessary to turn the $_POST map into a number valued array

	

	foreach ($_POST as $key)
		{
		
			$newArray[$i] = $key;
			
			$i++;

			
			

		}
	
	$test = array_pop($newArray);

	echo "<table class=userinfotable width='100%'><tr><td bgcolor='#CCCCCC'><h2>Results</h2></td></tr></table>";

	echo "<table class=userinfotable width='100%'>";
//	echo "<tr><td bgcolor='#99CCFF' wdith='14%'>login</td><td bgcolor='#99CCFF' wdith='14%'>password</td><td bgcolor='#99CCFF' wdith='14%'>first</td><td bgcolor='#99CCFF' wdith='14%'>last</td><td bgcolor='#99CCFF' wdith='14%'>email</td><td bgcolor='#99CCFF' wdith='14%'>sql</td></tr>";

	echo "<tr><td>User</td><td>Result</td></tr>";

	

	$i = 0; //Start the counter at 3 because the first three variables are build,date, and submit


	while ($i < (count($newArray))) //Loop for the entire size of the array
	{

		if($newArray[$i + 6] == 'on')
		{
			$id = ($newArray[$i]);
			$login = ($newArray[$i + 1]);
			$first = ($newArray[$i + 2]);
			$last = ($newArray[$i + 3]);
			$email = ($newArray[$i + 4]);
			$rightsid = ($newArray[$i + 5]);

			$i = $i + 7;

			//echo "delete";

			$sql = "delete from user where id='" . $id . "'";

			$result = mysql_query($sql);
			
			echo "<tr><td>" . $login . "</td><td>Deleted</td></tr>";

			//echo "<tr><td>" . $login . "</td><td>" . $password . "</td><td>" . $first . "</td><td>" . $last . "</td><td>" . $email . "</td><td>" . $sql . "</td></tr>";

		}else
		{
			$id = ($newArray[$i]);
			$login = ($newArray[$i + 1]);
			$first = ($newArray[$i + 2]);
			$last = ($newArray[$i + 3]);
			$email = ($newArray[$i + 4]);
			$rightsid = ($newArray[$i + 5]);

			$i = $i + 6;

			$sql = "update user set rightsid='" . $rightsid . "', login='" . $login . "', first='" . $first . "', last='" . $last . "', email='" . $email . "' where id='" . $id . "'";

			$result = mysql_query($sql);

			echo "<tr><td>" . $login . "</td><td>Updated</td></tr>";

			///echo "<tr><td>" . $login . "</td><td>" . $password . "</td><td>" . $first . "</td><td>" . $last . "</td><td>" . $email . "</td><td>" . $sql . "</td></tr>";
			
		}


}

}

?>
