<?php

////////////////////////////////////////////////////////////////////////////////
//File:     importData.php
//Author:   Chad Rosen
//Purpose:  This page manages the importation of test cases into testlink.
////////////////////////////////////////////////////////////////////////////////

function dispCategories($keyword, $resultCat) {
  while($rowCAT = mysql_fetch_array($resultCat)) { //loop through all categories

    $idCAT = $rowCAT[0];
    $nameCAT = $rowCAT[1];

    echo "\n\n<div id=CAT_$idCAT>\n\n";

    echo "<hr><font size='4' color='#0000FF'>$nameCAT</font><br>";

    echo "<input type='button' name='$nameCAT' onclick='box(\"CAT_$idCAT\", true)' value='Check'>Select All Test Cases<br>";

    echo "<input type='button' name='$nameCAT' onclick='box(\"CAT_$idCAT\", false)' value='Uncheck'>Unselect All Test Cases<br><br>";	
    
    //Check the keyword that the user has submitted.

    if($keyword == 'NONE') {
      //If they keyword is NONE then just do a regular query

      $sqlTC = "select id, title from mgttestcase where catid='" . $idCAT . "' order by TCorder,id";

    } else {

      //If they keyword is anything else query based on keyword

      $sqlTC = "select id, title from mgttestcase where catid='" . $idCAT . "' and keywords like '%" . $keyword . "%' order by TCorder,id";

    }
    
    $resultTC = @mysql_query($sqlTC);
    dispTestCases($resultTC);						
    echo "\n\n</div>\n\n";


  }//End while CAT
  
  echo "</div>\n\n";

  echo "<hr>";
}


function dispTestCases($result) {
  while($rowTC = mysql_fetch_array($result)) { //Display all test cases

    $idTC = $rowTC[0]; //Get the test case ID
    $titleTC = $rowTC[1]; //Get the test case title

    //Displays the test case name and a checkbox next to it

    $sqlCheck = "select mgttcid from project,component,category,testcase where mgttcid=" . $idTC . " and project.id=component.projid and component.id=category.compid and category.id=testcase.catid and project.id=" . $_SESSION['project'];

    $checkResult = @mysql_query($sqlCheck);
    $checkRow = mysql_num_rows($checkResult);

    if($checkRow > 0) {
      echo "<input type='checkbox' name='C" . $idTC . "'><b>" . $idTC . "</b>:" . htmlspecialchars($titleTC);

      echo "<img src='icons/checkmark.gif'>";

      echo "<input type='hidden' name='H" . $idTC . "' value='" . $idTC. "'>";
      echo "<br>";

    } else {

      echo "<input type='checkbox' name='C" . $idTC . "'><b>" . $idTC . "</b>:" . htmlspecialchars($titleTC);

      echo "<input type='hidden' name='H" . $idTC . "' value='" . $idTC. "'>";
      echo "<br>";
      }

  }//End while TC
}

require_once("../functions/header.php");
doSessionStart();
doDBConnect();
doHeader();

?>

<script>


//This function takes a div tag and whether or not you want the checkboxes checked or not
//The function then goes through all of the elements of the div tag that is passed in and
//if they are checkboxes

function box(myDiv, checkBoxStatus){
	var frm;
	var elemType;

	frm = document.getElementById(myDiv).getElementsByTagName('input');
	for(var i = 0; i < frm.length; i++){
		elemType = frm[i].type;		
		
		if(elemType == "checkbox"){
			frm[i].checked = checkBoxStatus;
		}
	}
}


</script>


<LINK REL="stylesheet" TYPE="text/css" HREF="CommonStyles.css" TITLE="CommonStyles">

<?

//Defining the keyword variable which is received from the left frame

$keyword = $_GET['key'];

//If the user hasn't picked anything they will see the info page

if($_GET['edit'] == 'info')
{

		echo "<table class=helptable width=100%>";
		echo "<tr><td class=helptabletitle><h2>Test Case Import</td></tr></table>";

		echo "<table class=helptable width=100%>";

		echo "<tr><td class=helptablehdr><b>Purpose:</td><td class=helptable>This Page allows the user (with lead level permissions) to import test cases by either their components or their category levels into a project</td></tr>";
		echo "<tr><td class=helptablehdr ><b>Getting Started:</td><td class=helptable><ol><li>Click on a component to see all of its categories and all of its test cases. Clicking on a category will only show that categories test cases. <li>Once you've selected the component/category you wish to draw test cases from use the radio buttons or check boxes to select the test cases you wish to import. <li>When you are done click the import button to import the test cases. Note: The system makes sure that the user does not import the same test case multiple times</ol></td></tr>";
		echo "</table>";



}

$projectSQL = "select name from project where id=" . $_SESSION['project'];
$resultProject = @mysql_query($projectSQL);
$rowProject = mysql_fetch_row($resultProject);

echo "<Form name='importForm' Method='POST' ACTION='manage/importData.php'>";

//Display the header table

echo "<table width='100%' class=userinfotable>";

//If the user has selected a component

if($_GET['edit'] == 'component')
{
	echo "<tr><td bgcolor='#CCCCCC'><b>Import Into Project</td><td bgcolor='#CCCCCC'><b>Import By Keyword</td><td bgcolor='#CCCCCC'><b>Import Data</td>";
	
	echo "<tr><td>" . $rowProject[0] . "</td><td>" . $keyword . "</td><td><input type='submit' name='importData' value='Import'></td></tr></table>";

			$sqlCOM = "select id, name from mgtcomponent where id='" . $_GET['com'] . "' order by name";
			$resultCOM = @mysql_query($sqlCOM);

			while($rowCOM = mysql_fetch_array($resultCOM)){

				$idCOM = $rowCOM[0];
				$nameCOM = $rowCOM[1];

				echo "<div id=COM>\n\n";
				
				echo "<font size='4' color='#FF0000'>" . $nameCOM . "</font></b><br>";
				
				echo "<input type='button' name='" . $nameCOM . "' value='Check' onclick='box(\"COM\", true)'><b>Select All Categories</b><br>";
				
				echo "<input type='button' name='" . $nameCOM . "' onclick='box(\"COM\", false)' value='Uncheck' CHECKED><b>Unselect All Categories</b>";


				$sqlCAT = "select id, name from mgtcategory where compid='" . $idCOM . "' order by CATorder,id";
				$resultCAT = @mysql_query($sqlCAT);
				dispCategories($keyword, $resultCAT);

			}//End while COM

	echo "</form>";
}


//If the user has selected a category

elseif($_GET['edit'] == 'category')
{

  //Start to display the form

  echo "<tr><td bgcolor='#CCCCCC'><b>Insert Into Project</td><td bgcolor='#CCCCCC'><b>Sorted By Keyword</td><td bgcolor='#CCCCCC'><b>Import Data</td>";

  //Display the actual info in the table

  echo "<tr><td><b>" . $rowProject[0] . "</td><td>" . $keyword . "</td><td><input type='submit' name='importData' value='Import'></td></tr></table>";

  //Query to grab all of the category information based on what was passed in by the user

  $sqlCAT = "select id, name from mgtcategory where id='" . $_GET['cat'] . "' order by CATorder,id";
  $resultCAT = @mysql_query($sqlCAT);
  dispCategories($keyword, $resultCAT);

  echo "</form>";
}


elseif($_POST['importData']) //If the user submits the import form
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
				//echo "com exists <br>";
				
				//echo "com exists <br>";

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
										
						$sqlAddTC = "insert into testcase(title,mgttcid,catid,summary,steps,exresult,version,keywords,TCorder) values ('" . $rowMGTAddTC[0] . "','" . $tcid . "','" . $rowResultCATID[1] . "','" . $summary . "','" . $steps . "','" . $exresult . "','" . $rowMGTAddTC[4] . "','" . $rowMGTAddTC[5] . "','" . $rowMGTAddTC[6] . "')";
					

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
					
					$sqlAddCAT = "insert into category(name,mgtcatid,compid,CATorder) values ('" . $rowMGTAddCAT[0] . "','" . $rowMGTCATID[0] . "','" . $rowResultCOMID[1] . "','" . $rowMGTAddCAT[1] . "')";
					
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
					$sqlAddTC = "insert into testcase(title,mgttcid,catid,summary,steps,exresult,version,keywords,TCorder) values ('" . $rowMGTAddTC[0] . "','" . $tcid . "','" . $addCATID  . "','" . $summary . "','" . $steps . "','" . $exresult . "','" . $rowMGTAddTC[4] . "','" . $rowMGTAddTC[5] . "','" . $rowMGTAddTC[6] . "')";
					
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
					$sqlAddCOM = "insert into component (name,mgtcompid,projid) values ('" . $rowMGTAddCOM[0] . "','" . $rowMGTCOMID[0] . "','" . $_SESSION['project'] . "')";


					$resultAddCOM = mysql_query($sqlAddCOM); //execute query
							
					$addCOMID =  mysql_insert_id();	 //Grab the id of the Component just entered
				
					//Figure out the category info to be added
					$sqlAddMgtCAT = "select name,CATorder from mgtcategory where id='" . $rowMGTCATID[0] . "'";
					$resultAddMgtCAT = mysql_query($sqlAddMgtCAT);
					$rowMGTAddCAT = mysql_fetch_array($resultAddMgtCAT); //Grab the CATID

					//Add the category to the project					
					$sqlAddCAT = "insert into category(name,mgtcatid,compid,CATorder) values ('" . $rowMGTAddCAT[0] . "','" . $rowMGTCATID[0] . "','" . $addCOMID . "','" . $rowMGTAddCAT[1] . "')";

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
					
					$sqlAddTC = "insert into testcase(title,mgttcid,catid,summary,steps,exresult,version,keywords,TCorder) values ('" . $rowMGTAddTC[0] . "','" . $tcid . "','" . $addCATID  . "','" . $summary . "','" . $steps . "','" . $exresult . "','" . $rowMGTAddTC[4] . "','" . $rowMGTAddTC[5] . "','" . $rowMGTAddTC[6] . "')";
					

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

