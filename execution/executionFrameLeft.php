<?

////////////////////////////////////////////////////////////////////////////////
//File:     executionFrameLeft.php
//Author:   Chad Rosen
//Purpose:  This page is the left frame of the execution pages. It builds the
//          javascript trees that allow the user to jump around to any point
//	    on the screen
////////////////////////////////////////////////////////////////////////////////

//Includes the base href and database login

require_once("../functions/header.php");

  session_start();
  doDBConnect();
  doHeader();

require_once("../functions/stripTree.php"); //include the function that strips the javascript tree

//I need the csv split function

require_once('../functions/csvSplit.php');


?>

<head>

<script language='javascript' src='functions/popupHelp.js'></script>

<script language='JavaScript' src='jtree/tree.js'></script>
<script language='JavaScript' src='jtree/tree_tpl.js'></script>
<link rel="stylesheet" href="jtree/tree.css">
<link rel="stylesheet" href="kenny.css">

</head>

<?

//include the header page

require_once("exLeftHeader.php");


//If the user submits the sorting form
		
if($_POST['submitBuild'])
{


	$build = $_POST['build'];
	$keyword = $_POST['keyword'];
	$owner = $_POST['owner'];
	$result = $_POST['result'];

	//////////////////////////////////////////////////////////////Start the display of the components
			
	//Here I create a query that will grab every component depending on the project the user picked
			
	$sql = "select component.id, component.name from component,project where project.id = " . $_SESSION['project'] . " and component.projid = project.id order by component.name";

	$comResult = mysql_query($sql);

	//Second Tree that contains all the data

	echo "<script language='JavaScript'> var TREE_ITEMS = [\n\n";

	echo "['Info','execution/execution.php?edit=info',";


	while ($myrowCOM = mysql_fetch_row($comResult)) { //display all the components until we run out

		$name = stripTree($myrowCOM[1]); //function that removes harmful characters

		//right now only categories are sorted by owners. This means that the entire component
		//will still show up if the user has decided to sort.
		//So since I don't want them being able to see every single test case when they click
		//the component I need to pass an owner variable to the execution page which will allow it
		//to sort by category
				
		//Here I create a query that will grab every category depending on the component the user picked


		//If the user has not selected an owner to sort by

		if($owner == 'All')
		{
			
			$catSql = "select category.id, category.name from component,category where component.id = " . $myrowCOM[0] . " and component.id = category.compid order by CATorder,category.mgttcid";

			$catResult = mysql_query($catSql);

		}else //if the user selected a user to sort by
		{

			$catSql = "select category.id, category.name from component,category where component.id = " . $myrowCOM[0] . " and component.id = category.compid and category.owner='" . $owner . "' order by CATorder,category.mgttcid";

			$catResult = mysql_query($catSql);

		}

		//check to see if there are any rows returned from the component query

		$numRowsCAT = mysql_num_rows($catResult);

		if($numRowsCAT > 0)
		{

			echo "['" . $name . "','execution/execution.php?keyword=" . $_POST['keyword'] . "&build=" . $_POST['build'] . "&owner=" . $_POST['owner'] . "&edit=component&com=" . $myrowCOM[0] . "',\n\n";

				
			while ($myrowCAT = mysql_fetch_row($catResult)) 
			
			{  //display all the categories until we run out
					
				$name = stripTree($myrowCAT[1]); //function that removes harmful characters


				//This next section displays test cases. However I need to check if there are actually are any test cases available first
					
					if($_POST['keyword'] == 'All')
					{

						//user passed in a result that isnt the default "all" and didnt select a keyword to sort by

						$TCsql = "select testcase.id, testcase.title, testcase.mgttcid from testcase where testcase.catid = " . $myrowCAT[0] . " order by TCorder,testcase.id";				



					}else
					{

						//user has selected to view a specific result and didnt select a keyword to sort by

						$TCsql = "select testcase.id, testcase.title, testcase.mgttcid from testcase where testcase.catid = " . $myrowCAT[0] . " and keywords like '%" . $_POST['keyword'] . "%' order by TCorder,testcase.id";



					}


		
			

							
				$TCResult = mysql_query($TCsql); //run the query

				$numRowsTC = mysql_num_rows($TCResult); //count the rows

				if($numRowsTC > 0) //if there are actually test cases
				{


					$testCaseInfo = catCount($myrowCAT[0],$_POST['cumulative'],$_POST['build']); //grab the test case info for this category
					
					//display the category  section.. The first part of this string displays the name of the category followed by how many passed, failed, blocked, and not run test cases there are in it

					echo "['" . $name . " (" . $testCaseInfo[0] . "," . $testCaseInfo[1] . "," . $testCaseInfo[2] . "," . $testCaseInfo[3] . ")'";
					
					
					echo ",'execution/execution.php?keyword=" . $keyword . "&build=" . $build . "&edit=category&owner=" . $_POST['owner'] . "&cat=" . $myrowCAT[0] . "',\n\n";

					//display the test case tree

					displayTCTree($TCResult);

					echo "],\n\n";


				}
						
			
				
				
			}//end CAT loop

			echo "],\n\n";

			}//end COM loop

		}//end if $numRowsCOM > 0

	echo "]\n\n";

	echo "];</script>\n\n"; //end the tree

	//Display the tree

	echo "<script language='JavaScript'>";
	echo "new tree (TREE_ITEMS, TREE_TPL);";
	echo "</script>";


}//end if submit

//function that displays all of the test cases

function displayTCTree($TCResult)
{

while ($myrowTC = mysql_fetch_row($TCResult)) {  //display all the test cases until we run out


	$name = stripTree($myrowTC[1]); //function that removes harmful characters

	if($_POST['cumulative'] == 'on')
	{

		$sqlResult = "select status from results where tcid='" . $myrowTC[0] . "' order by build desc limit 1";

	}else
	{

		$sqlResult = "select status from results where build='" . $_POST['build'] . " ' and tcid = " . $myrowTC[0];

	}


	$sqlBuildResult = mysql_query($sqlResult);
				
	$buildResult = mysql_fetch_row($sqlBuildResult);
						
	//I need the num results so I can do the check below on not run test cases

	$numResults = mysql_num_rows($sqlBuildResult); 

	//Determine what the build result is and apply the specific color

	if($buildResult[0] == 'p')
	{
		$font = "green";

	}elseif($buildResult[0] == 'f')
	{
		$font = "red";
						
	}elseif($buildResult[0] == 'b')
	{
		$font = "blue";

	}else
	{
		$font = "black";
					
	}

	//This is where I do the result display stuff

	if($_POST['result'] && $_POST['result'] != 'all') //If there was a result passed in and it isn't all
	{
					
		//If the user selected to view passed tcs

		if($_POST['result'] == 'p' && $buildResult[0] == 'p')
		{
									
			//call the display tc function.. This way I can change it all in one place

			//displayTC($font,$myrowTC[2],$name,$keyword,$build,$myrowTC[0],$owner);

			echo "['<font color=" . $font . "><b>" . $myrowTC[2] . ":</b> " . $name . "','execution/execution.php?keyword=" . $_POST['keyword'] . "&build=" . $_POST['build'] . "&edit=testcase&owner=" . $_POST['owner'] . "&tc=" . $myrowTC[0]. "'],\n\n";	

		}elseif($_POST['result'] == 'f' && $buildResult[0] == 'f') //failed
		{

		//call the display tc function.. This way I can change it all in one place

		//displayTC($font,$myrowTC[2],$name,$keyword,$build,$myrowTC[0],$owner);

			echo "['<font color=" . $font . "><b>" . $myrowTC[2] . ":</b> " . $name . "','execution/execution.php?keyword=" . $_POST['keyword'] . "&build=" . $_POST['build'] . "&edit=testcase&owner=" . $_POST['owner'] . "&tc=" . $myrowTC[0]. "'],\n\n";	


		}elseif($_POST['result'] == 'b' && $buildResult[0] == 'b') //blocked
		{

			//call the display tc function.. This way I can change it all in one place

			//displayTC($font,$myrowTC[2],$name,$keyword,$build,$myrowTC[0],$owner);

			echo "['<font color=" . $font . "><b>" . $myrowTC[2] . ":</b> " . $name . "','execution/execution.php?keyword=" . $_POST['keyword'] . "&build=" . $_POST['build'] . "&edit=testcase&owner=" . $_POST['owner'] . "&tc=" . $myrowTC[0]. "'],\n\n";	


		}elseif($_POST['result'] == 'n' && ($numResults == 0 || $buildResult[0] == 'n')) //not run
		{
			//call the display tc function.. This way I can change it all in one place

			//displayTC($font,$myrowTC[2],$name,$keyword,$build,$myrowTC[0],$owner);
			
			echo "['<font color=" . $font . "><b>" . $myrowTC[2] . ":</b> " . $name . "','execution/execution.php?keyword=" . $_POST['keyword'] . "&build=" . $_POST['build'] . "&edit=testcase&owner=" . $_POST['owner'] . "&tc=" . $myrowTC[0]. "'],\n\n";	

		}
								


	//Else I want to display all the test cases and their status colors

	}else
	{

		//displayTC($font,$myrowTC[2],$name,$keyword,$build,$myrowTC[0],$owner);
		
		echo "['<font color=" . $font . "><b>" . $myrowTC[2] . ":</b> " . $name . "','execution/execution.php?keyword=" . $_POST['keyword'] . "&build=" . $_POST['build'] . "&edit=testcase&owner=" . $_POST['owner'] . "&tc=" . $myrowTC[0]. "'],\n\n";	

	}

	}//end TC loop




}


function displayTC($font,$mgttcid,$name,$keyword,$build,$tcid,$owner)
{



	echo "['<font color=" . $font . "><b>" . $mgttcid . ":</b> " . $name . "','execution/execution.php?keyword=" . $keyword . "&build=" . $build . "&edit=testcase&owner=" . $owner . "&tc=" . $tcid . "'],\n\n";	


}

//this function gathers all of the passed,failed,blocked, and not run information about the test cases so that it can be displayed next to the category

function catCount($catID,$cumulative,$build)
{


	//check to see if the user selected the cumulative checkbox

	//destroying old variables.. Shouldnt really matter but since I copied this from another place i'm leaving it in

	unset($returnValues);
	unset($totalRow);
	unset($testCaseArray);
	unset($totalTCs);

	if($cumulative == 'on') //if they did then use these queries
	{
		$arrayCounter = 0; //Counter

		//Initializing variables

		$pass = 0;
		$fail = 0;
		$blocked = 0;
		$notRun = 0;

		//Code to grab the entire amount of test cases per project
		
		$sql = "select count(testcase.id) from category,testcase where category.id='" . $catID . "' and category.id = testcase.catid";

		$totalTCResult = mysql_query($sql);

		$totalTCs = mysql_fetch_row($totalTCResult);

		//Now grab all of the test cases and their results	

		$sql = "select tcid,status from results,category,testcase where category.id='" . $catID . "' and category.id = testcase.catid and testcase.id = results.tcid order by build";

		$totalResult = mysql_query($sql);

		//Setting the results to an array.. Only taking the most recent results and displaying them
	
		while($totalRow = mysql_fetch_row($totalResult))
		{
			//echo $totalRow[0] . " " . $totalRow[1] . "<br>";

			//This is a test.. I've got a problem if the user goes and sets a previous p,f,b value to a 'n' value. The program then sees the most recent value as an not run. I think we want the user to then see the most recent p,f,b value
			
			if($totalRow[1] == 'n')
			{

			}
			else
			{
			
			$testCaseArray[$totalRow[0]] = $totalRow[1];
			
			}

		}

		
		//This is the code that determines the pass,fail,blocked amounts


	//I had to write this code so that the loop before would work.. I'm sure there is a better way to do it but hell if I know how to figure it out..
		

	if(count($testCaseArray) > 0)
		{

			//This loop will cycle through the arrays and count the amount of p,f,b,n

			foreach($testCaseArray as $tc)
			{

				if($tc == 'p')
				{
					
					$pass++;
					

				}

				elseif($tc == 'f')
				{

					$fail++;

				}

				elseif($tc == 'b')

				{

					$blocked++;

				}


			}//end foreach

	
		}//end if

		//setup the return values

		$returnValues[0] = "<font color=green>" . $pass . "</font>"; //pass
		$returnValues[1] = "<font color=red>" . $fail . "</font>"; //fail
		$returnValues[2] = "<font color=blue>" . $blocked . "</font>"; //blocked
	
		$returnValues[3] = $totalTCs[0] - ($pass + $fail + $blocked); //the equation for not run
		
		$returnValues[3] = "<font color=black>" . $returnValues[3] . "</font>"; //append font to front and back
		


	}else //else use a specific build
	{

		$sqlPassed = "select count(testcase.id) from category,testcase,results where category.id='" . $catID . "' 	and category.id=testcase.catid and testcase.id=results.tcid and results.status='p' and build='" . $build . "'";

		$sqlFailed = "select count(testcase.id) from category,testcase,results where category.id='" . $catID . "' and category.id=testcase.catid and testcase.id=results.tcid and results.status='f' and build='" . $build . "'";
		
		$sqlBlocked = "select count(testcase.id) from category,testcase,results where category.id='" . $catID . "' and category.id=testcase.catid and testcase.id=results.tcid and results.status='b' and build='" . $build . "'";
		
		$sqlTotal = "select count(testcase.id) from category,testcase where category.id='" . $catID . "' and category.id=testcase.catid";

		//Gather the passed results
	
		$passedResult = mysql_query($sqlPassed);
		$passed = mysql_fetch_row($passedResult);

		//Gather the failed results

		$failedResult = mysql_query($sqlFailed);
		$failed = mysql_fetch_row($failedResult);

		//Gather the blocked results

		$blockedResult = mysql_query($sqlBlocked);			
		$blocked = mysql_fetch_row($blockedResult);

		//Gather the total results

		$totalResult = mysql_query($sqlTotal);
		$total = mysql_fetch_row($totalResult);

		//Set the passed,failed, and blocked results to the returned value

		$returnValues[0] = "<font color=green>" . $passed[0] . "</font>";  //pass
		$returnValues[1] = "<font color=red>" . $failed[0] . "</font>"; //failed
		$returnValues[2] = "<font color=blue>" . $blocked[0] . "</font>"; //blocked

		//Determine the amount of not run cases through the equation below

		$returnValues[3] = $total[0] - ($passed[0] + $failed[0] + $blocked[0]); //equation for not run
		
		$returnValues[3] = "<font color=black>" . $returnValues[3] . "</font>"; //append font to front and back

		
	}

return $returnValues;
	


}//end function catCount
	
?>
