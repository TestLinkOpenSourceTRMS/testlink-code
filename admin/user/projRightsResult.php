<?

////////////////////////////////////////////////////////////////////////////////
//File:     projRightsResult.php
//Author:   Chad Rosen
//Purpose:  This page manages the users rights on a project basis.
////////////////////////////////////////////////////////////////////////////////

require_once("../../functions/header.php");

  session_start();
  doDBConnect();
  doHeader();
 // doNavBar();

require_once("../../functions/orderArray.php"); //this reorders the post array


?>

<LINK REL='stylesheet' TYPE='text/css' HREF='kenny.css'>

<?

$projRightsArray = orderArray($_POST);




if($_GET['view'] == 'user')
{
	//First we need to delete everything from the projRights table for that user

	$sqlDelete = "delete from projrights where userid=" . $_GET['id'];

	$resultDelete = @mysql_query($sqlDelete);

	//Then we loop through the data that was passed in

	foreach($projRightsArray as $projRights)
	{

	 //ignore the first value because it is the submit button
	
	if($projRights != 'save')
	{
	
		//We then need to add the new data to the projRights table

		$sqlInsert = "insert into projrights (userid,projid) values ('" . $_GET['id'] . "','" . $projRights . "')";

		$resultDelete = @mysql_query($sqlInsert);
		
	}
	

}

echo "<br>User's Project Rights Have Been Assigned";


}elseif($_GET['view'] == 'project')
{
	//First we need to delete everything from the projRights table for that project

	$sqlDelete = "delete from projrights where projid=" . $_GET['id'];

	$resultDelete = @mysql_query($sqlDelete);

	//Then we loop through the data that was passed in

	foreach($projRightsArray as $projRights)
	{

		 //ignore the first value because it is the submit button
	
		if($projRights != 'save')
		{
			//We then need to add the new data to the projRights table

			$sqlInsert = "insert into projrights (userid,projid) values ('" . $projRights . "','" . $_GET['id'] . "')";

			$resultDelete = @mysql_query($sqlInsert);
			
		}

	}

	echo "<br>User's Project Rights Have Been Assigned";


}else
{
	echo "do nothing";
}




?>