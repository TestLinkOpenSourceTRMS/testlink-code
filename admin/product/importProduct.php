<?php

////////////////////////////////////////////////////////////////////////////////
//File:     importProduct.php
//Author:   Chad Rosen
//Purpose:  This page manages the importation of product data from a csv file.
////////////////////////////////////////////////////////////////////////////////

require_once("../../functions/header.php");

  session_start();
  doDBConnect();
  doHeader();
  doNavBar();

?>

<LINK REL="stylesheet" TYPE="text/css" HREF="kenny.css">

<?

//session_start();

//if($_GET['project'])
//{

  echo "<form method='post' action='admin/product/importProduct.php'>\n\n";
  
  echo "<table class=userinfotable width='100%'>";

  echo "<tr><td bgcolor='#CCCCCC'><b>Data Import</td></tr></table>";

  echo "<table class=userinfotable  width='100%'>\n\n";
  
  //This next variable shows where you can import data from

  $location =  $basehref . 'admin/product/';

  echo "<tr><td bgcolor='#99CCFF'><b>Enter Location of Data:</td><td><textarea name='location' cols='70' rows='1'>" . $location . "</textarea></td></tr>\n\n";

  echo "<tr><td><input type='Submit' name='showFiles' value='Show Directory Info'>";
  
  
  echo "</td></tr>\n\n";

  echo "</form>";


  echo "</table>";

//}

if($_POST['showFiles'])
{


$handle = $_POST['location'];



$counter=1;

if ($handle = opendir('.')) {

    while (false !== ($file = readdir($handle))) { 
		
		list($front,$end)= split ("[.]", $file);
        
		if ($file != "." && $file != ".." && $end== "csv"){ 
            
			$option .= "<option value=" . $file . ">" . $file;

			$counter++;
        } 
    }
    closedir($handle); 

	echo "<form method='post' action='admin/product/importProduct.php'>\n\n";

	echo "<select multiple name='files' size=" . $counter . ">";
	echo "<option value='All' SELECTED>All";

	echo $option;

	echo "</select>";


	echo "<br><input type='hidden' value='" . $location . "' name='location'><input type='Submit' name='showIMP' value='View Info'><br>";


	echo "</form>";

}





}

if($_POST['showIMP'])
{
	$location = $_POST['location'] . $_POST['files'];

	echo "<form method='post' action='admin/product/importProduct.php'>\n\n";
	 ///Displays the products that you can enter data into

	 $sql = "select id, name from mgtproduct";

	 echo "<select name='product'>";
	
	$result = mysql_query($sql);

	 while ($myrow = mysql_fetch_row($result))
			
		{

			echo "<option value='" . $myrow[0] . "'>" . $myrow[1] . "</option>";

		}


	  echo "</select>";
	
	echo "<input type='Submit' name='import' value='Import Data'>";
	echo "<input type='hidden' value='" . $location . "' name='location'>";
	echo "</form>";

	

	echo "<table class=userinfotable  width='100%'><tr><td><b>Importing From: </b>" . $location . "</td></tr></table>";
	
	//command to open a csv for read

	$handle = fopen ($location,"r");

	echo "<table class=userinfotable  width='100%'>";

	//Need to grab the first row of data

	while ($data = fgetcsv ($handle, 1000, ",")) {

		$arrayCom = $data[0];
		$arrayCat = $data[1];
		$arrayTC = $data[2];

		//Strips off quotation marks (") needed to import data correctly

		//$arrayCom = substr($arrayCom, 1, (count($arrayCom) - 2));
		//$arrayCat = substr($arrayCat, 1, (count($arrayCat) - 2));
		$arrayTC = substr($arrayTC, 1, (count($arrayTC) - 2));
		//$arraySummary = $data[3];
		//$arrayTCSteps = $data[4];
		//$arrayResults = $data[5];

		if(strcmp($arrayCom,$oldCom) != 0) //Is the current value equal to the old value?
		{

			echo "<tr><td bgcolor='#CCCCCC' width='3'>COM:</td><td bgcolor='#CCCCCC'>" . $arrayCom . "</td></tr>"; //No

			if(strcmp($arrayCat,$oldCat) != 0) //Is the current value equal to the old value?
			{

				echo "<tr><td bgcolor='#99CCFF' width='3'>CAT:</td><td  bgcolor='#99CCFF'>" . $arrayCat . "</td></tr>"; //No

				echo "<tr><td bgcolor='#FFFFCC' width='3'>TC:</td><td bgcolor='#FFFFCC'>" . $arrayTC . "</td></tr>"; //display TC
			
			}

			

		
		}else
		{

			if(strcmp($arrayCat,$oldCat) == 0)
			{

				echo "<tr><td bgcolor='#FFFFCC' width='3'>TC:</td><td bgcolor='#FFFFCC'>" . $arrayTC . "</td></tr>";

			}else
			{

				echo "<tr><td bgcolor='#99CCFF' width='3'>CAT:</td><td  bgcolor='#99CCFF'>" . $arrayCat . "</td></tr>"; //No

				echo "<tr><td bgcolor='#FFFFCC' width='3'>TC:</td><td bgcolor='#FFFFCC'>" . $arrayTC . "</td></tr>"; //display TC

			}





		}

		$oldCom = $arrayCom;
		$oldCat = $arrayCat;
		
	}

	echo "</table>";

	fclose ($handle);


}

if($_POST['import'])
{


	//not sure how i use this or if i even do anymore

	$row = 1;

	//command to open a csv for read


	$handle = fopen ($_POST['location'],"r");
	//Need to grab the first row of data

	$data = fgetcsv ($handle, 1000, ",");

	//$projID = $_SESSION['project']; //Setting the project ID which is from a previous form

	$prodID = $_POST['product'];

	//Data taken from the csv

	$arrayCom = $data[0];
	$arrayCat = $data[1];
	$arrayTC = $data[2];
	$arraySummary = $data[3];
	$arrayTCSteps = $data[4];
	$arrayResults = $data[5];
	
	//Removing the quotation marks around the stings

	//$arrayCom = substr($arrayCom, 1, (count($arrayCom) - 2));
	//$arrayCat = substr($arrayCat, 1, (count($arrayCat) - 2));
	$arrayTC = substr($arrayTC, 1, (count($arrayTC) - 2));
	$arraySummary = substr($arraySummary, 1, (count($arraySummary) - 2)); 	
	$arrayTCSteps= substr($arrayTCSteps, 1, (count($arrayTCSteps) - 2));
	$arrayResults= substr($arrayResults, 1, (count($arrayResults) - 2));

	//Grabbing the Key information from the excel sheets

	$key6 = $data[6];
	$key7 = $data[7];
	$key8 = $data[8];
	$key9 = $data[9];
	$key10 = $data[10];
	$key11 = $data[11];
	$key12 = $data[12];
		
		//Need to reinitialize the keys variable
		
		$keys = "";

		//This if block checks to see if the key exists. If it does I strip the quotes from around it and add it
		//to the keys string (which is later inserted into the DB)

		if($key6)
		{
			$key6 = substr($data[6], 1, (count($data[6]) - 2)); //Removing the quotes around the string
			$keys .= "," . $key6;

		}
		
		if($key7)
		{

			$key7 = substr($data[7], 1, (count($data[7]) - 2)); //Removing the quotes around the string
			$keys .= "," . $key7;

		}

		if($key8)
		{

			$key8 = substr($data[8], 1, (count($data[8]) - 2)); //Removing the quotes around the string
			$keys .= "," . $key8;

		}
		
		if($key9)
		{
			$key9 = substr($data[9], 1, (count($data[9]) - 2)); //Removing the quotes around the string
			$keys .= "," . $key9;
		}
		
		if($key10)
		{
			$key10 = substr($data[10], 1, (count($data[10]) - 2)); //Removing the quotes around the string
			$keys .= "," . $key10;
		}
		
		if($key11)
		{
			$key11 = substr($data[11], 1, (count($data[11]) - 2)); //Removing the quotes around the string
			$keys .= "," . $key11;
		}
		
		if($key12)
		{

			$key12 = substr($data[12], 1, (count($data[12]) - 2)); //Removing the quotes around the string
			$keys .= "," . $key12;
		}



	//Insert arrayCom into component where projID == projIDSubmit 

		$sql = "insert into mgtcomponent (name, prodid) values ('" . $arrayCom . "','" . $prodID . "')";

		$result = mysql_query($sql);

		$comID =  mysql_insert_id(); //Grab the id of the category just entered

		echo $sql . "<br><br>";

	//Select comID from component where comName == arrayCom store as comID

		$sql = "insert into mgtcategory (name, compid) values ('" . $arrayCat . "','" . $comID . "')";

		$result = mysql_query($sql);

		$catID =  mysql_insert_id(); //Grab the id of the category just entered

		echo $sql . "<br><br>";

		$sql = "insert into mgttestcase (title,steps,summary,exresult,catid,keywords) values ('" . $arrayTC . "','" . $arrayTCSteps . "','" . $arraySummary . "','" . $arrayResults . "','" . $catID . "','" . $keys . "')";

		echo $sql . "<br><br>";

		$result = mysql_query($sql);


	//Store all the old vales into a new array

		$oldCom = $arrayCom;
		$oldComNumber = $comID;
		$oldCat = $arrayCat;
		$oldCatNumber = $catID;

	//Next start the loop!!

	while ($data = fgetcsv ($handle, 1000, ",")) {

		$arrayCom = $data[0];
		$arrayCat = $data[1];
		$arrayTC = $data[2];
		$arraySummary = $data[3];
		$arrayTCSteps = $data[4];
		$arrayResults = $data[5];

		//Removing the quotation marks around the stings

		//$arrayCom = substr($arrayCom, 1, (count($arrayCom) - 2));
		//$arrayCat = substr($arrayCat, 1, (count($arrayCat) - 2));
		$arrayTC = substr($arrayTC, 1, (count($arrayTC) - 2));
		$arraySummary = substr($arraySummary, 1, (count($arraySummary) - 2)); 	
		$arrayTCSteps= substr($arrayTCSteps, 1, (count($arrayTCSteps) - 2));
		$arrayResults= substr($arrayResults, 1, (count($arrayResults) - 2));

		//Grabbing the Key information from the excel sheets

		$key6 = $data[6];
		$key7 = $data[7];
		$key8 = $data[8];
		$key9 = $data[9];
		$key10 = $data[10];
		$key11 = $data[11];
		$key12 = $data[12];

		//I need to initialize the variable

		$keys = "";

		
		//This if block checks to see if the key exists. If it does I strip the quotes from around it and add it
		//to the keys string (which is later inserted into the DB)

		if($key6)
		{
			$key6 = substr($data[6], 1, (count($data[6]) - 2)); //Removing the quotes around the string
			$keys .= "," . $key6;

		}
		
		if($key7)
		{

			$key7 = substr($data[7], 1, (count($data[7]) - 2)); //Removing the quotes around the string
			$keys .= "," . $key7;

		}

		if($key8)
		{

			$key8 = substr($data[8], 1, (count($data[8]) - 2)); //Removing the quotes around the string
			$keys .= "," . $key8;

		}
		
		if($key9)
		{
			$key9 = substr($data[9], 1, (count($data[9]) - 2)); //Removing the quotes around the string
			$keys .= "," . $key9;
		}
		
		if($key10)
		{
			$key10 = substr($data[10], 1, (count($data[10]) - 2)); //Removing the quotes around the string
			$keys .= "," . $key10;
		}
		
		if($key11)
		{
			$key11 = substr($data[11], 1, (count($data[11]) - 2)); //Removing the quotes around the string
			$keys .= "," . $key11;
		}
		
		if($key12)
		{

			$key12 = substr($data[12], 1, (count($data[12]) - 2)); //Removing the quotes around the string
			$keys .= "," . $key12;
		}
		

		//Is arrayCom = tempCom

		if($arrayCom == $oldCom)
		{
			//If yes

			//Is arrayCat = tempCat
				
			if($arrayCat == $oldCat)
			{
					//If yes
						
					//Insert arrayTC into testcase where catID = catID

					$sql = "insert into mgttestcase (title,steps,summary,exresult,catid,keywords) values ('" . $arrayTC . "','" . $arrayTCSteps . "','" . $arraySummary . "','" . $arrayResults . "','" . $oldCatNumber . "','" . $keys . "')";

					echo $sql . "<br>";

					$result = mysql_query($sql);

			//If no

			}
			else
			{				
			
			//Insert arrayCat into category where comID = comID

				$sql = "insert into mgtcategory (name, compid) values ('" . $arrayCat . "','" . $oldComNumber . "')";

				
				//echo $sql . "<br>";

				$result = mysql_query($sql);

				$catID =  mysql_insert_id(); //Grab the id of the category just entered

				$sql = "insert into mgttestcase (title,steps,summary,exresult,catid,keywords) values ('" . $arrayTC . "','" . $arrayTCSteps . "','" . $arraySummary . "','" . $arrayResults . "','" . $catID . "','" . $keys . "')";
				
				echo $sql . "<br>";

				$result = mysql_query($sql);

			}//end cat else
			 
		//If no
		}	 
		else
		{

			//Insert arrayCom into component where projID == projIDSubmit 

			$sql = "insert into mgtcomponent (name, prodID) values ('" . $arrayCom . "','" . $prodID . "')";
			
			//echo $sql . "<br>";

			$result = mysql_query($sql);

			$comID =  mysql_insert_id(); //Grab the id of the category just entered

			$sql = "insert into mgtcategory (name, compid) values ('" . $arrayCat . "','" . $comID . "')";
			
			//echo $sql . "<br>";

			$result = mysql_query($sql);

			$catID =  mysql_insert_id(); //Grab the id of the category just entered

			$sql = "insert into mgttestcase (title,steps,summary,exresult,catid,keywords) values ('" . $arrayTC . "','" . $arrayTCSteps . "','" . $arraySummary . "','" . $arrayResults . "','" . $catID . "','" . $keys . "')";
			
			echo $sql . "<br>";

			$result = mysql_query($sql);


				
		}//end com else


		$oldCom = $arrayCom;
		$oldComNumber = $comID;
		$oldCat = $arrayCat;
		$oldCatNumber = $catID;



	}

	//Close the CSV

	fclose ($handle);

	echo "Data Imported";
}


?>
