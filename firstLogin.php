<?php

////////////////////////////////////////////////////////////////////////////////
//File:     firstLogin.php
//Author:   Chad Rosen
//Purpose:  This is an extremely important file.  This file shows the user
//	    information.
////////////////////////////////////////////////////////////////////////////////

  require_once("functions/header.php");

  session_start();
  doDBConnect();
  doHeader();


if(!$_POST['editUser'])
{

	//Find all rows where the posted username and password are correct

	displayForm($message);


}
elseif($_POST['editUser'])

{

		$login=$_POST['login'];
		$password=$_POST['password'];
		$password2 = $_POST['password2'];
		$first=$_POST['first'];
		$last=$_POST['last'];
		$email=$_POST['email'];
		$rights = 5; //All first time users get guest rights

		$passwordCompare = strcmp($password,$password2);

		$sql = "select login from user where login='" . $login . "'";

		$result = mysql_query($sql);

		$userExists = mysql_num_rows($result);

		if($login == "")
		{
						
			$message = "<b>I'm sorry but you must enter a valid user name. Please Select another one</b>";

			displayForm($message);
			

		}		
		elseif($userExists > 0)
		{

			
			$message = "<b>I'm sorry but that login name is already in use. Please Select another one</b>";

			displayForm($message);



		}
		elseif($passwordCompare != 0)
		{

			$message = "<b>The two passwords entered did not match. Note that comparison is case sensative. Please try again</b>";

			displayForm($message);
			

		}else
		{

			$sqlInsert = "insert into user (login,password,first,last,email,rightsid) values ('" . $login . "','" . $password . "','" . $first . "','" . $last . "','" . $email . "','" . $rights . "')";

			$insertResult = mysql_query($sqlInsert);

			echo "Welcome to TestLink " . $login . "!<br><br>";
			
			echo "Please return to the <a href='http://www.qagood.com'>QA Homepage</a> and login. If you have any questions please contact Chad Rosen";


			
		}

		
}

function displayForm($message)
{

			echo "<a href='http://www.qagood.com'>Return to www.qagood.com</a><br>";

			echo $message;
			
			echo "<FORM method='post' ACTION='firstLogin.php'>";

			echo "<table class=userinfotable>";

			echo "<tr><td class=userinfotablehdr><b>Enter Your User Information</td><td class=userinfotablehdr>&nbsp;</td><tr>";

			echo "<tr><td><b>Login</td><td><input type=text name='login'></td></tr>";
			
			echo "<tr><td><b>Password</td><td><input type=password name='password'></td></tr>";
			echo "<tr><td><b>Enter Password Again</td><td><input type=password name='password2'></td></tr>";
			
			echo "<tr><td><b>First Name</td><td><input type=text name='first'></td></tr>";
			
			echo "<tr><td ><b>Last Name</td><td><input type=text name='last'></td></tr>";
			
			echo "<tr><td><b>Email</td><td><input type=text name='email'></td></tr>";

			echo "</table>";

			echo "<br><input type='submit' NAME='editUser' value='Save'>";

			echo "</form>";


}
		


?>
