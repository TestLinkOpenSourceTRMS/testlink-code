<?php

////////////////////////////////////////////////////////////////////////////////
//File:     lostPassword.php
//Author:   Chad Rosen
//Purpose:  This file generates the email with user password informations.
////////////////////////////////////////////////////////////////////////////////

  require_once("functions/header.php");

  session_start();
  doDBConnect();
  doHeader();


if(!$_POST['editUser'])
{

	//Find all rows where the posted username and password are correct

	echo "<a href='http://www.qagood.com'>Return to www.qagood.com</a><br>";

	echo "<FORM method='post' ACTION='lostPassword.php'>";

	echo "<table class=userinfotable>";

	echo "<tr><td class=userinfotablehdr><b>Enter Your User Information so that your password can be mailed to you</td><td class=userinfotablehdr>&nbsp;</td><tr>";

	echo "<tr><td><b>Login</td><td><input type=text name='login'></td></tr>";
		
	echo "</table>";

	echo "<br><input type='submit' NAME='editUser' value='Save'>";

	echo "</form>";
}
elseif($_POST['editUser'])

{

		$login=$_POST['login'];

		$passwordCompare = strcmp($password,$password2);

		$sql = "select login,password from user where login='" . $login . "'";

		$result = mysql_query($sql);

		//See if the user exists in the system

		$userExists = mysql_num_rows($result);

		if($userExists == 0)
		{

			echo "<a href='http://www.qagood.com'>Return to www.qagood.com</a><br>";

			echo "<b>I'm sorry but that user does not exist. Please try again</b>";
			
			//Find all rows where the posted username and password are correct

			echo "<FORM method='post' ACTION='lostPassword.php'>";

			echo "<table class=userinfotable>";

			echo "<tr><td class=userinfotablehdr><b>Enter Your User Information so that your password can be mailed to you</td><td class=userinfotablehdr>&nbsp;</td><tr>";

			echo "<tr><td><b>Login</td><td><input type=text name='login'></td></tr>";
						
			echo "</table>";

			echo "<br><input type='submit' NAME='editUser' value='Save'>";

			echo "</form>";



		}
		else
		{

			echo "<a href='http://www.qagood.com'>Return to www.qagood.com</a><br><br>";

			echo "Your password has been mailed to the email account you specified during your user creation.<br><br>If you have other problems please contact <a href='mailto:crosen@good.com'>Chad Rosen</a>"; 

			$sql = "select email,password from user where login='" . $login . "'";

			$result = mysql_query($sql);

			//grab the rows

			$myrow = mysql_fetch_row($result);


			//Setup the message body

			$msgBody = "Your TestLink password is '" . $myrow[1] . "'


If you have any further problems please contact Chad Rosen at crosen@good.com"; 

			//php mail function

			mail($myrow[0], "Your TestLink password", $msgBody);

			
		}

		
}

		


?>
