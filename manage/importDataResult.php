<?

////////////////////////////////////////////////////////////////////////////////
//File:     importDataResults.php
//Author:   Chad Rosen
//Purpose:  This page manages the results of the importation of test cases into testlink.
////////////////////////////////////////////////////////////////////////////////

require_once("../functions/header.php");
doSessionStart();
doDBConnect();
doHeader();

if($_POST['importData']) //If the user submits the import form
{

	$i = 0;

	//This loop goes through all of the $_POST variables and maps them to values

	foreach ($_POST as $key)
    {
	
		$newArray[$i] = $key;
		
		$i++;

	}

	echo "<br>";

	
	for($i = 1; $i < count($newArray); $i++) //Loop through all of the $_POST / $newArray variables
	{

		//If we find a testcase that has been checked then grab the value after it and increment by two

		if($newArray[$i] == 'on') 
		{

			$tcid = $newArray[$i + 1]; //If we find the test case that has been passed through it's value is always the next item in the list

			//Finding CATID for the test case
			
			$sqlMGTCATID = "select catid from mgttestcase where id='" . $tcid . "'";
			$resultMGTCATID = @mysql_query($sqlMGTCATID); //execute the query
			$rowMGTCATID = mysql_fetch_array($resultMGTCATID); //Grab the CATID
	
			//Finding the COMID from the tpid we just found

			$sqlMGTCOMID = "select compid from mgtcategory where id='" . $rowMGTCATID[0] . "'";
			$resultMGTCOMID = @mysql_query($sqlMGTCOMID); //execute the query
			$rowMGTCOMID = mysql_fetch_array($resultMGTCOMID); //Grab the CATID

			//This next long set of code looks through the kenny side of the DB and checks to see if each of the
			//Components,categories, or TCs already exist. If one of the top level items exists the function skips down to the next level and checks there. Finally if no TCs exist it does nothing.
			
			//Determining if the component already exists for the project being added to

			$sqlCOMID = "select mgtcompid,id from component where mgtcompid='" . $rowMGTCOMID[0] . "' and projid='" .  $_SESSION['project'] . "'";
			
			$resultCOMID = @mysql_query($sqlCOMID); //execute the query
			
			
			echo "<table class=userinfotable width=100%>";

			if(mysql_num_rows($resultCOMID) > 0) //Are there any existing COM?
			{

				$rowResultCOMID = mysql_fetch_row($resultCOMID); //grab the actual COM ID value this time
				
				$sqlCATID = "select mgtcatid,id from category where mgtcatid='" . $rowMGTCATID[0] . "' and compid='" . $rowResultCOMID[1] . "'";

				$resultCATID = @mysql_query($sqlCATID); //execute query
				
				if(mysql_num_rows($resultCATID) > 0) //Are there any existing CAT?
				{
					
				//echo "cat exists <br>";
							
					$rowResultCATID = mysql_fetch_row($resultCATID); //grab the actual CAT ID value this time
				
					$sqlTCID = "select mgttcid from testcase where mgttcid='" . $tcid . "' and catid='" . $rowResultCATID[1] . "'";

					$resultTCID = @mysql_query($sqlTCID); //execute query

					if(mysql_num_rows($resultTCID) > 0) //Were there any test case matches?
					{

						
				//echo "tc exists <br>";
							
						//if yes
						//Do nothin
	
					}
					else //If the test case doesn't already exist
					{

						
				//echo "tc doesnt exists <br>";
														
						//Figure out the testcase info to be added

						$sqlAddMgtTC = "select title,summary,steps,exresult,version,keywords,TCorder from mgttestcase where id='" . $tcid . "'";
						$resultAddMgtTC = mysql_query($sqlAddMgtTC);
						$rowMGTAddTC = mysql_fetch_array($resultAddMgtTC); //Grab the TCID

						//Add the testcase to the project

						$steps = stripTree($rowMGTAddTC[2]);
						$exresult = stripTree($rowMGTAddTC[3]);
						$summary = stripTree($rowMGTAddTC[1]);
										
						$sqlAddTC = "insert into testcase(title,mgttcid,catid,summary,steps,exresult,version,keywords,TCorder) values ('" . mysql_escape_string($rowMGTAddTC[0]) . "','" . $tcid . "','" . mysql_escape_string($rowResultCATID[1]) . "','" . mysql_escape_string($summary) . "','" . mysql_escape_string($steps) . "','" . mysql_escape_string($exresult) . "','" . mysql_escape_string($rowMGTAddTC[4]) . "','" . mysql_escape_string($rowMGTAddTC[5]) . "','" . mysql_escape_string($rowMGTAddTC[6]) . "')";
					

						$resultAddTC = mysql_query($sqlAddTC);
							
						//or die("can't add tc");	//execute query

						
				//	echo $sqlAddTC . "<br>";

						//echo "<br>" . htmlspecialchars($sqlAddTC);

					}

					

				}else //If the category doesn't exist

				{		

					
				//echo "cat doesnt exists <br>";
	
					
					//if no
					
					//Figure out the category info to be added

					$sqlAddMgtCAT = "select name,CATorder from mgtcategory where id='" . $rowMGTCATID[0] . "'";
					$resultAddMgtCAT = mysql_query($sqlAddMgtCAT);
					$rowMGTAddCAT = mysql_fetch_array($resultAddMgtCAT); //Grab the CATID

					//Add the category to the project
					
					$sqlAddCAT = "insert into category(name,mgtcatid,compid,CATorder) values ('" . mysql_escape_string($rowMGTAddCAT[0]) . "','" . mysql_escape_string($rowMGTCATID[0]) . "','" . mysql_escape_string($rowResultCOMID[1]) . "','" . mysql_escape_string($rowMGTAddCAT[1]) . "')";
					
					$resultAddCAT = mysql_query($sqlAddCAT); //execute the query

					$addCATID =  mysql_insert_id(); //Grab the id of the category just entered

					//Add the test case to the project
			
					//Figure out the testcase info to be added
					$sqlAddMgtTC = "select title,summary,steps,exresult,version,keywords,TCorder from mgttestcase where id='" . $tcid . "'";
					$resultAddMgtTC = mysql_query($sqlAddMgtTC);
					$rowMGTAddTC = mysql_fetch_array($resultAddMgtTC); //Grab the TCID

					$steps = stripTree($rowMGTAddTC[2]);
					$exresult = stripTree($rowMGTAddTC[3]);
					$summary = stripTree($rowMGTAddTC[1]);

					//Add the testcase to the project					
					$sqlAddTC = "insert into testcase(title,mgttcid,catid,summary,steps,exresult,version,keywords,TCorder) values ('" . mysql_escape_string($rowMGTAddTC[0]) . "','" . mysql_escape_string($tcid) . "','" . mysql_escape_string($addCATID)  . "','" . mysql_escape_string($summary) . "','" . mysql_escape_string($steps) . "','" . mysql_escape_string($exresult) . "','" . mysql_escape_string($rowMGTAddTC[4]) . "','" . mysql_escape_string($rowMGTAddTC[5]) . "','" . mysql_escape_string($rowMGTAddTC[6]) . "')";
					
					$resultAddTC = mysql_query($sqlAddTC); //execute query
				
					

				}

			}
			else//If the component doesn't exist
			{

				
				//echo "com doesnt exist <br>";

	//if no
					
					//echo "<tr><td>found no component<td>";

					//Figure out the component info to be added
					$sqlAddMgtCOM = "select name from mgtcomponent where id='" . $rowMGTCOMID[0] . "'";
					$resultAddMgtCOM = mysql_query($sqlAddMgtCOM);
					$rowMGTAddCOM = mysql_fetch_array($resultAddMgtCOM); //Grab the COMID

					//Add the component to the project					
					$sqlAddCOM = "insert into component (name,mgtcompid,projid) values ('" . mysql_escape_string($rowMGTAddCOM[0]) . "','" . mysql_escape_string($rowMGTCOMID[0]) . "','" . $_SESSION['project'] . "')";


					$resultAddCOM = mysql_query($sqlAddCOM); //execute query
							
					$addCOMID =  mysql_insert_id();	 //Grab the id of the Component just entered
				
					//Figure out the category info to be added
					$sqlAddMgtCAT = "select name,CATorder from mgtcategory where id='" . $rowMGTCATID[0] . "'";
					$resultAddMgtCAT = mysql_query($sqlAddMgtCAT);
					$rowMGTAddCAT = mysql_fetch_array($resultAddMgtCAT); //Grab the CATID

					//Add the category to the project					
					$sqlAddCAT = "insert into category(name,mgtcatid,compid,CATorder) values ('" . mysql_escape_string($rowMGTAddCAT[0]) . "','" . mysql_escape_string($rowMGTCATID[0]) . "','" . mysql_escape_string($addCOMID) . "','" . mysql_escape_string($rowMGTAddCAT[1]) . "')";

					$resultAddCAT = mysql_query($sqlAddCAT); //execute the query

					$addCATID =  mysql_insert_id(); //Grab the id of the category just entered


					//Add the test case to the project
			
					//Figure out the test case info to be added
					$sqlAddMgtTC = "select title,summary,steps,exresult,version,keywords,TCorder from mgttestcase where id='" . $tcid . "'";
					$resultAddMgtTC = mysql_query($sqlAddMgtTC);
					$rowMGTAddTC = mysql_fetch_array($resultAddMgtTC); //Grab the TCID

					//Add the category to the project
					
					$steps = stripTree($rowMGTAddTC[2]);
					$exresult = stripTree($rowMGTAddTC[3]);
					$summary = stripTree($rowMGTAddTC[1]);
					
					$sqlAddTC = "insert into testcase(title,mgttcid,catid,summary,steps,exresult,version,keywords,TCorder) values ('" . mysql_escape_string($rowMGTAddTC[0]) . "','" . mysql_escape_string($tcid) . "','" . mysql_escape_string($addCATID)  . "','" . mysql_escape_string($summary) . "','" . mysql_escape_string($steps) . "','" . mysql_escape_string($exresult) . "','" . mysql_escape_string($rowMGTAddTC[4]) . "','" . mysql_escape_string($rowMGTAddTC[5]) . "','" . mysql_escape_string($rowMGTAddTC[6]) . "')";
					

					$resultAddTC = mysql_query($sqlAddTC); //execute the query


					
			}//end else


			$i = $i + 1; //increment the counter plus an extra one to skip the testcase number

		}//end if




	}//end for

	echo "Data has been imported";


}//end if $_POST()

//Some test cases weren't being imported because of word's stupid crap that it adds to format.. I need to remove
//the apostraphies so that imports work

function stripTree($name)
{

$name = str_replace ( "'", "", $name); //remove apostraphy

return $name;

}

?>