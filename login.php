<?php

////////////////////////////////////////////////////////////////////////////////
//File:     login.php
//Author:   Chad Rosen
//Purpose:  This file is the login screen.
////////////////////////////////////////////////////////////////////////////////



//Include the header file and start the session

require_once('functions/header.php');

echo "<LINK REL='stylesheet' TYPE='text/css' HREF='kenny.css'>";



//This next section just creates a form with a username and password section

if(!$_POST['submit'])
{

echo<<<END
<table width='35%' class=mainTable>

<tr><td
class=mainHeader><h2>TestLink</H2>$TLVersion</td></tr>

<form method='post' action=$basehref/doAuthorize.php>
<table width='35%' class=mainTable>
<tr><td><b>Login Name:</b></td>
<td><input type=text name='login' size=20></td></tr>
<tr><td><b>Password:</b></td>
<td><input type=password name='password' size=20></td></tr>
<tr><td><input type='submit' name='submit' value='Login'></td></td></tr>
</table>
</form>

<a href='firstLogin.php'>New User?</a><br>
<a href='lostPassword.php'>Lost Password?</a>

END;




}
	
?>