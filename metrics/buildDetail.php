<?


require_once("../functions/header.php");

  session_start();
  doDBConnect();
  doHeader();

?>

<LINK REL="stylesheet" TYPE="text/css" HREF="kenny.css">

<?

//Link to the main page

//require_once section. These require_onces will show the require_onced files on the screen

require_once("projectDetail.php"); //builds the project detail table

echo "<br>";

require_once("componentDetail.php"); //builds the component detail table

echo "<br>";

require_once("categoryDetail.php"); //builds the category detail table

echo "<br>";

//This next section displays the keyword info for the current build

		$sqlKeyword = "select keywords from project, component, category, testcase where project.id = " .  $_SESSION['project'] . " and project.id = component.projid and component.id = category.compid and category.id = testcase.catid order by keywords";

		//echo $sqlKeyword;

		//Execute the query

		$resultKeyword = mysql_query($sqlKeyword);

		//Loop through each of the testcases

		while ($myrowKeyword = mysql_fetch_row($resultKeyword)) 
			{

				//This next function calls the csv_split function which can be found at the very bottom of this page
				//The function takes a string of comma seperated values and returns them as an array

				$keyArray = csv_split($myrowKeyword[0]);

				//Take the array that is created from the keywords and add it to another array named result2

				$result2 = array_merge ($result2, $keyArray);


			}//END WHILE

	

		//I need to make sure there are elements in the result 2 array. I was getting an error when I didn't check


		if(count($result2) > 0) 
		{

			//This next function takes the giant array that we created, which is full of duplicate values, and
			//only keeps the unique values. LONG LIVE PHP!

			$result3 = array_unique ($result2);

			//In order to loop through the array I need to change the keys of the array so that they are numerically in order

			$i=0;

			foreach ($result3 as $key)
			{
		
				$result4[$i] = $key;
				$i++;

			}

		}


	echo "<table width='100%' class=userinfotable><tr><td bgcolor='#99CCFF' class='subTitle'>Keyword Status</td></tr></table>";

	echo "<table width='100%' class=userinfotable>";

	echo "<tr><td width='14%' bgcolor='#FFFFCC' class='boldFont' align='center'>Keyword</td><td width='14%' bgcolor='#FFFFCC' class='boldFont' align='center'>Total</td><td width='14%' bgcolor='#FFFFCC' class='boldFont' align='center'>Pass</td><td width='14%' bgcolor='#FFFFCC' class='boldFont' align='center'>Fail</td><td width='14%' bgcolor='#FFFFCC' class='boldFont' align='center'>Blocked</td><td width='14%' bgcolor='#FFFFCC' class='boldFont' align='center'>Not Run</td><td width='14%' bgcolor='#FFFFCC' class='boldFont' align='center'>% Complete</td></tr>";


		for ($i = 0; $i < count($result4); $i++)
		{

			//For some reason I'm getting a space.. Now I'll ignore any spaces

			if($result4[$i] != "")
			{
				
				
				//Code to grab the entire amount of test cases per project
	
				$sql = "select count(testcase.id) from project,component,category,testcase where project.id = '" . $_SESSION['project'] . "' and project.id = component.projid and component.id = category.compid and category.id = testcase.catid and keywords like '%" . $result4[$i] . "%'";

				//echo $sql . "<br><br>";

				$totalTCResult = mysql_query($sql);

				$totalTCs = mysql_fetch_row($totalTCResult);

				//Code to grab the results of the test case execution

				$sql = "select tcid,status from results,project,component,category,testcase where project.id = '" . $_SESSION['project'] . "' and project.id = component.projid and component.id = category.compid and category.id = testcase.catid and testcase.id = results.tcid and keywords like '%" . $result4[$i] . "%' and build='" . $_POST['build'] . "'";

				$totalResult = mysql_query($sql);

				//Setting the results to an array.. Only taking the most recent results and displaying them
	
				while($totalRow = mysql_fetch_row($totalResult))
				{

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

			$arrayCounter = 0; //Counter

			//Initializing variables

			$pass = 0;
			$fail = 0;
			$blocked = 0;
			$notRun = 0;


			//I had to write this code so that the loop before would work.. I'm sure there is a better way to do it but hell if I know how to figure it out..
			
			$sqlC = "select max(testcase.id) from testcase";

			$TTR = mysql_query($sqlC);

			$TTRC = mysql_fetch_row($TTR);


			//This loop will cycle through the arrays and count the amount of p,f,b,n

			while($arrayCounter <= $TTRC[0])
			{

				if($testCaseArray[$arrayCounter] == 'p')
				{
					
					$pass++;
					

				}

				elseif($testCaseArray[$arrayCounter] == 'f')
				{

					$fail++;

				}

				elseif($testCaseArray[$arrayCounter] == 'b')

				{

					$blocked++;

				}
			

				$arrayCounter++; //increment the counter

			}


				//destroy the testCaseArray variable

				unset($testCaseArray);

				
				$notRunTCs = $totalTCs[0] - ($pass + $fail + $blocked); //Getting the not run TCs

				
				if($totalTCs[0] == 0) //if we try to divide by 0 we get an error
				{
					$percentComplete = 0;

				}else
				{
			
					$percentComplete = ($pass + $fail + $blocked) / $totalTCs[0]; //Getting total percent complete
					$percentComplete = round((100 * ($percentComplete)),2); //Rounding the number so it looks pretty
				
				}		

				//Displaying the results
							
				echo "<td  bgcolor='#CCCCCC' class='boldFont' align='center'>" . $result4[$i] . "</td>"; //displaying the component name
				
				echo "<td class='font' align='center'>" . $totalTCs[0] . "</td>";

				echo "<td class='font' align='center'>" . $pass . "</td>";

				echo "<td class='font' align='center'>" . $fail . "</td>";

				echo "<td class='font' align='center'>" . $blocked . "</td>";

				echo "<td class='font' align='center'>" . $notRunTCs . "</td>";

				echo "<td class='font' align='center'>" . $percentComplete . "</td></tr>";


				$counter++;

			}
				
				
		
			}//


?>