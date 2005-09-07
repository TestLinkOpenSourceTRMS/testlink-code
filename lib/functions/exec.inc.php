<?
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: exec.inc.php,v $
 *
 * @version $Revision: 1.8 $
 * @modified $Date: 2005/09/07 09:23:03 $
 *
 * @author Martin Havlat
 *
 * Functions for execution feature (add test results) 
 *
 * 20050905 - fm - reduce global coupling
 *
 * 20050807 - fm
 * filterKeyword()   : added $idPlan to remove global coupling via _SESSION
 * createBuildMenu() : added $idPlan to remove global coupling via _SESSION
 *
 * removed deprecated: $_SESSION['project']
 *
 *
**/
require_once('../functions/common.php');

/**
 * Function just grabs number of builds
 *
 * @param numeric test plan ID
 * @return integer Count of Builds
 */  
function buildsNumber($tpID)
{
	$result = do_mysql_query("SELECT count(*) FROM build WHERE build.projid = " . $tpID);
	$buildCount = mysql_result($result, 0);
	if ($buildCount){
		return $buildCount;
	} else {
		return 0;
	}
}

/** 
 * This code here displays the keyword dropdown box for Test Plan. It's fairly interesting code
 * What it does is searches through all of the currently viewed projects test cases and puts together
 * all of the unique keywords from each testcase. It then builds a dropdown box to dispaly them
 * @todo rewrite this to use selectOptionData($sql) 
 *
 * @param $idPlan
 *
 * 20050807 - fm
 * added $idPlan to remove global coupling via _SESSION
 */
function filterKeyword($idPlan)
{
		//SQL to grab all of the keywords
		//schlundus: added DISTINCT
		$sqlKeyword = "SELECT DISTINCT(keywords) FROM project, component, category, testcase WHERE " .
				"project.id = " .  $idPlan . " AND project.id = component.projid" .
				" AND component.id = category.compid AND category.id = testcase.catid ORDER BY keywords";
		$resultKeyword = do_mysql_query($sqlKeyword);
		
		//Loop through each of the testcases
		$keyArray = null;
		while ($myrowKeyword = mysql_fetch_row($resultKeyword))
		{
			//schlundus: csvsplit and merging arrays was too slow, so we simple make a big list of the different keyword lists
			$keyArray .= $myrowKeyword[0].",";
		}
		//removed quotes and separate the list
		$keyArray = explode(",",$keyArray);
	
		//I need to make sure there are elements in the result 2 array. I was getting an error when I didn't check
		if(count($keyArray))
			$keyArray = array_unique ($keyArray);

		//Now I begin the display of the keyword dropdown
		$data = '<select name="keyword">'; //Create the select
		$data .= "<option>All</option>"; //Add a none value to the array in case the user doesn't want to sort

		$keyword = isset($_POST['keyword']) ? strings_stripSlashes($_POST['keyword']) : null;
		//For each of the unique values in the keyword array 
		//I want to loop through and display them as an option to select
		foreach($keyArray as $key=>$word)
		{
			//For some reason I'm getting a space.. Now I'll ignore any spaces
			if($word != "")
			{
				//This next if statement makes the keyword field "sticky" 
				//if the user has already selected a keyword and submitted the form
				$sel = '';
				if($word == $keyword)
					$sel = ' selected="selected"';
				$data .= "<option{$sel}>" . htmlspecialchars($word) . "</option>";
			}
		}
		$data .= "</select>";
	return $data;
}


/** Building the dropdown box of results filter */
function createResultsMenu()
{
	$data['all'] = 'All';
	$data['n'] = 'Not Run';
	$data['p'] = 'Passed';
	$data['f'] = 'Failed';
	$data['b'] = 'Blocked';

	return $data;
}//end results function


/** Building the dropdown box of builds */
// MHT 200507	refactorization; improved SQL
//
// 20050807 - fm
// added $idPlan to remove Global Coupling
function createBuildMenu($idPlan)
{
	$sql = "SELECT build,name FROM build WHERE build.projid = " . 
			$idPlan . " ORDER BY build DESC";

	return selectOptionData($sql);
}//end function


/**
 * Add editted test results to database
 *
 * 20050905 - fm
 * interface changes
 *
 */
// MHT 200507	added conversion of special chars on input - [ 900437 ] table results -- incoherent data ?
function editTestResults($login_name, $tcData, $build)
{
	global $g_bugInterfaceOn;
	
	//It is necessary to turn the $_POST map into a number valued array
	// 20050905 - fm
	unset($tcData['submitTestResults']);
	$newArray = hash2array($tcData);
	
	$build = mysql_escape_string($build);

	// todo: change this is use an associative array...
	//		already fixed bug because of not using one :)
	$i = 0; 
	while ($i < count($newArray)){ //Loop for the entire size of the array
	
			$tcID = $newArray[$i]; //Then the first value is the ID
			$tcNotes = mysql_escape_string($newArray[$i + 1]); //The second value is the notes
			$tcStatus = mysql_escape_string($newArray[$i + 2]); //The third value is the status
			$tcBugs = '';
			if ($g_bugInterfaceOn)
			{
				//The 4th value is the CSV of bugs
				$tcBugs = isset($newArray[$i + 3]) ? mysql_escape_string($newArray[$i + 3]) : ''; 
				$i++;
			}

			//SQL statement to look for the same record (tcid, build = tcid, build)
			$sql = "SELECT tcid, build, notes, status FROM results WHERE tcid='" . $tcID . 
					"' and build='" . $build . "'";
			$result = do_mysql_query($sql); //Run the query
			$num = mysql_num_rows($result); //How many results
			
			if($num == 1){ //If we find a matching record
							
				//Grabbing the values from the query above
				$myrow = mysql_fetch_row($result);
				$queryNotes = $myrow[2];
				$queryStatus = $myrow[3];
		
				//If the (notes, status) information is the same.. Do nothing
				if($queryNotes == $tcNotes && $queryStatus == $tcStatus){
					//Delete all the bugs from the bugs table
					$sqlDelete = "DELETE from bugs where tcid=" . $tcID . " and build=" . $build;
					$result = do_mysql_query($sqlDelete); //Execute query
					/////Loop to insert the new bugs into the bug table
					//Grabbing the bug info from the results table
					$bugArray = strlen($tcBugs) ?  explode(",",$tcBugs) : null;
					$counter = 0;
					while($counter < count($bugArray))	{
	
						$sql = "INSERT INTO bugs (tcid,build,bug) VALUES ('" . $tcID . "','" . 
								$build . "','" . $bugArray[$counter] . "')";
						$result = do_mysql_query($sql); //Execute query
						$counter++;
					}
				} else {
	
					//update the old result
					$sql = "UPDATE results SET runby ='" . $login_name . "', status ='" .  
							$tcStatus . "', notes='" . $tcNotes . "' where tcid='" . $tcID . 
							"' and build='" . $build . "'";
					$result = do_mysql_query($sql); //Execute query
	
					//Delete all the bugs from the bugs table
					$sqlDelete = "DELETE FROM bugs WHERE tcid=" . $tcID . " and build=" . $build;
					$result = do_mysql_query($sqlDelete); //Execute query
	
					/////Loop to insert the new bugs into the bug table
					//Grabbing the bug info from the results table
					$bugArray = strlen($tcBugs) ?  explode(",",$tcBugs) : null;
	
					$counter = 0;
					while($counter < count($bugArray))	{
	
						$sqlBugs = "INSERT INTO bugs (tcid,build,bug) VALUES ('" . $tcID . "','" . 
								$build . "','" . $bugArray[$counter] . "')";
						$result = do_mysql_query($sqlBugs); //Execute query
						$counter++;
					}
				}
			
			//If the (notes, status) information is different.. then update the record
			} 
			else //If there is no entry for the build or the build is different 
			{ 
			
				//If the notes are blank and the status is n then do nothing
				if($tcNotes == "" && $tcStatus == "n") { 
					//Delete all the bugs from the bugs table
					$sqlDelete = "DELETE from bugs where tcid=" . $tcID . " and build=" . $build;
					$result = do_mysql_query($sqlDelete); //Execute query
	
					/////Loop to insert the new bugs into the bug table
					//Grabbing the bug info from the results table
					$bugArray = strlen($tcBugs) ?  explode(",",$tcBugs) : null;
					$counter = 0;
					while($counter < count($bugArray))	{

						$sql = " INSERT INTO bugs (tcid,build,bug) " .
						       " VALUES ('" . $tcID . "','" . $build . "','" . $bugArray[$counter] . "')";
						$result = do_mysql_query($sql); //Execute query
						$counter++;
					}
	
				} else { //Else enter a new row
				
					$sql = " INSERT INTO results (build,daterun,status,tcid,notes,runby) " .
					       " VALUES ('" . $build . "',CURRENT_DATE(),'" . $tcStatus . 
					       "','" . $tcID . "','" . $tcNotes . "','" . $login_name . "')";
					$result = do_mysql_query($sql);
	
					$sqlDelete = "DELETE from bugs where tcid=" . $tcID . " and build=" . $build;
					$result = do_mysql_query($sqlDelete); //Execute query
	
					/////Loop to insert the new bugs into the bug table
					//Grabbing the bug info from the results table
					$bugArray = strlen($tcBugs) ?  explode(",",$tcBugs) : null;
					$counter = 0;
					while($counter < count($bugArray)){
						$sqlBugs = " INSERT INTO bugs (tcid,build,bug) " .
						           " VALUES ('" . $tcID . "','" . $build . "','" . $bugArray[$counter] . "')";
						$result = do_mysql_query($sqlBugs); //Execute query
						$counter++;
					}
				}
			}
	
			$i = $i + 3; //Increment 3 values to the next tcID
	
	}//end while
	
	return "<div class='info'><p>Test Results submitted.</p></div>";
}

	
/**
 * This function returns data for display test cases
 *
 * @param resource $resultTC Result of SQL query
 * @param string $build Build Id
 * @return array $arrTC
 *
 * @author Francisco Mancardi
 * from mysql_fetch_row -> mysq_fetch_assoc
 * refactoring removing global coupling (Test Plan ID)
 *
 * @author Andreas Morsing - removed unnecessary code
 */
function createTestInput($resultTC,$build,$tpID)
{
	global $g_bugInterfaceOn,$g_tc_status;;
	$arrTC = array();
	while ($myrow = mysql_fetch_array($resultTC)){ 

		//display all the test cases until we run out
		//If the result is empty it leaves the box blank.. This looks weird. 
		//Entering a space if it's blank
 	  $a_keys = array('title','summary','steps','exresult');
    foreach($a_keys as $field_name)
    {
		  if(trim($myrow[$field_name]) == "")
		  {
		    $myrow[$field_name] = "none";
		  }
		}
			
		//This query grabs the results from the build passed in
		$sql = " SELECT notes, status FROM results WHERE tcid='" . $myrow['id']. "' " .
		       " AND build='" . $build . "'";
		$resultStatus = do_mysql_query($sql);
		$dataStatus = mysql_fetch_row($resultStatus);

		//This query grabs the most recent result
		$sqlRecentResult = "SELECT build.name AS build,status,runby,daterun FROM results,build " .
				"WHERE tcid=" . $myrow[0] . " AND status != '" . $g_tc_status['not_run'] . 
				"' AND results.build = build.build AND projid = " . 
				$tpID ." ORDER by build.build " .
				"DESC limit 1";
		$dataRecentResult = do_mysql_query($sqlRecentResult);
		$rowRecent = mysql_fetch_assoc($dataRecentResult);
		
		//routine that collect the test cases bugs.
		//Check to see if the user is using a bug system
		$resultBugList = null;
		//20050825 - scs - added code to show the related bugs of the tc
		$bugLinkList = null;
		if($g_bugInterfaceOn)
		{
			global $g_bugInterface ;
			//sql code to grab the appropriate bugs for the test case and build
			$sqlBugs = "SELECT bug FROM bugs WHERE tcid='" . $myrow[0] . "' and build='" . $build . "'";
			$resultBugs = do_mysql_query($sqlBugs);

			//For each bug that is found
			while ($myrowBugs = mysql_fetch_assoc($resultBugs))
			{ 
				if (!is_null($resultBugList))
					$resultBugList .= ",";
				$bugID = $myrowBugs['bug'];
				$resultBugList .= $bugID;
				$bugLinkList[] = $g_bugInterface->buildViewBugLink($bugID,true);
			}
		}
		// add to output array
		$arrTC[] = array( 'id' => $myrow[0],
   						'title' => $myrow[1],
						'summary' => $myrow[2], 
	   					'steps' => $myrow[3],
						'outcome' => $myrow[4],
   						'mgttcid' => $myrow[6],
						'version' => $myrow[7],
						'status' => $dataStatus[1],
   						'note' => $dataStatus[0], 
   						'bugs' => $resultBugList, 
						'recentResult' => $rowRecent,
						'bugLinkList' => $bugLinkList,
						);
	}
			
	return $arrTC;	
}
	
	
/** 
* Determine what the build result is and apply the specific color 
* @param string $buildResult [p,f,...]
* @return string CSS class
*/
function defineColor($buildResult)
{
	//Determine what the build result is and apply the specific color
	switch ($buildResult)
	{
		case 'p':
			return "green";
			break;
		case 'f':
			return "red";
			break;
		case 'b':
			return "blue";
			break;
		default:
			return "black";
	}
}
?>