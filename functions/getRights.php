<?

////////////////////////////////////////////////////////////////////////////////
//File:     getRights.php
//Author:   Chad Rosen
//Purpose:  This file provides the get_rights and has_rights functions for
//          verifying user level permissions.
////////////////////////////////////////////////////////////////////////////////

require_once("csvSplit.php"); //include the csv_split function

//this function will grab the current users rights


function get_rights()
{

	//Grab the users rights from the rights table

	$sqlGetRights = "select rights from rights where role='" . $_SESSION['role'] . "'";
	
	//execute the query

	$resultGetRights = mysql_query($sqlGetRights);
	
	//fetch the rights
	
	$myrowGetRights = mysql_fetch_row($resultGetRights);

	//Pass the csv string returned from the query to the csv_split function which returns the string as an array

	$roles = csv_split($myrowGetRights[0]);

	//return the value

	return $roles;

}

//this function takes a roleQuestion from a specified link and returns whether the user has rights to view it

function has_rights($roleQuestion)
{

	//Get the roles from the get_rights function
	
	$roles = get_rights();

	array_unshift($roles, "dummyValue"); //I had to prepend a variable to the front of the array.. Ask Chad
	//if you want to know why

	//check to see if the $roleQuestion variable appears in the $roles variable

	$permission = array_search($roleQuestion, $roles);
	

	return $permission;
}
