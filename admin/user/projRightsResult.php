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
  doNavBar();

require_once("../../functions/orderArray.php"); //this reorders the post array

require_once("../../functions/csvSplit.php"); //I need this to split the comma seperated array

?>

<LINK REL='stylesheet' TYPE='text/css' HREF='kenny.css'>

<?

$projRightsArray = orderArray($_POST);


//First we need to delete everything from the projRights table

$sqlDelete = "delete from projrights";

$resultDelete = @mysql_query($sqlDelete);


//Then we loop through the data that was passed in

foreach($projRightsArray as $projRights)
{

	 //ignore the first value because it is the submit button
	
	if($projRights != 'save')
	{
	
		//echo $projRights . "<br>";

		//I'm passing the data as a comma seperated value so we need to split them apart

		$projArray = csv_split($projRights);

		//We then need to add the new data to the projRights table

		$sqlInsert = "insert into projrights (userid,projid) values ('" . $projArray[0] . "','" . $projArray[1] . "')";

		$resultDelete = @mysql_query($sqlInsert);


		
	}
	


}

echo "<br>User's Project Rights Have Been Assigned";

?>
