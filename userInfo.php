<?php

////////////////////////////////////////////////////////////////////////////////
//File:     userInfo.php
//Author:   Chad Rosen
//Purpose:  This file generates and displays the users' information.
////////////////////////////////////////////////////////////////////////////////

  require_once("functions/header.php");

  session_start();
  doDBConnect();
  doHeader();
  doNavBar();

?>

<br>

<?


if(!$_POST['editUser'])
{

	//Find all rows where the posted username and password are correct

	$sql = "Select id,login,password,first, last, email from user where login='" . $_SESSION['user'] . "'";

	//Run the query

	$result = mysql_query($sql);

	$userResult = mysql_fetch_row($result);

	//Getting the rights of the user

	$id = $userResult[0];
	$login = $userResult[1];
	$password = $userResult[2];
	$first = $userResult[3];
	$last = $userResult[4];
	$email = $userResult[5];

	echo "<FORM method='post' ACTION='userInfo.php'>";
	echo "<input type='hidden' name='id' value='" . $id . "'>";
	echo "<table class=userinfotable>";
	echo "<tr><td class=userinfotablehdr colspan=2>Your User Information</td><tr>";
	echo "<tr><td>Login</td><td><input type=text name=login value='" . $login . "'></td></tr>";
	echo "<tr><td>Password</td><td><input type=password name=password value='" . $password . "'></td></tr>";
	echo "<tr><td>First Name</td><td><input type=text name=first value='" . $first . "'></td></tr>";
	echo "<tr><td>Last Name</td><td><input type=text name=last value='" . $last . "'></td></tr>";
	echo "<tr><td>Email Address</td><td><input type=text name=email value='" . $email . "'></td></tr>";
	echo "</table>";

	echo "<br><input type='submit' NAME='editUser' value='Edit'>";

	echo "</form>";
}
elseif($_POST['editUser'])

{

	$id=$_POST['id'];
	$login=$_POST['login'];
	$password=$_POST['password'];
	$first=$_POST['first'];
	$last=$_POST['last'];
	$email=$_POST['email'];

		$sql = "update user set login='" . $login . "', password='" . $password . "', first='" . $first . "', last='" . $last . "', email='" . $email . "' where id='" . $id . "'";

		$result = mysql_query($sql);

		echo "<table class=userinfotable>";
		echo "<tr><td class=userinfotablehdr colspan=2>User Information Changed!</td><tr>";
		echo "<tr><td>Login</td><td>" . $login . "</td></tr>";
		echo "<tr><td>Password</td><td>****</td></tr>";
		echo "<tr><td>First Name</td><td>" . $first . "</td></tr>";
		echo "<tr><td>Last Name</td><td>" . $last . "</td></tr>";
		echo "<tr><td>Email Address</td><td>" . $email . "</td></tr>";
		echo "</table>";
}

		


?>
