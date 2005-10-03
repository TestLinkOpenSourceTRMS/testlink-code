<?
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: exec.inc.php,v $
 *
 * @version $Revision: 1.14 $
 * @modified $Date: 2005/10/03 07:21:42 $ $Author: franciscom $
 *
 * @author Martin Havlat
 *
 * Functions for execution feature (add test results) 
 *
 * 20050926 - fm - db changes build -> build_id
 * 20050919 - fm - editTestResults() refactoring
 * 20050911 - fm - editTestResults() refactoring
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
function buildsNumber($tpID=0)
{
	// 20050929 - fm - seems sometimes we receive no tpID
	$sql = "SELECT count(*) AS num_builds FROM build WHERE build.projid = " . $tpID;
  $buildCount=0;
  if ($tpID)
  {
	 $result = do_mysql_query($sql);
	 $myrow = mysql_fetch_assoc($result);
	 $buildCount = $myrow['num_builds'];
	}
	return ($buildCount);
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
// 20050921 - fm - build.build -> build.id
function createBuildMenu($tpID)
{
	$sql = " SELECT build.id, build.name " .
	       " FROM build WHERE build.projid = " .  $tpID . 
	       " ORDER BY build.id DESC";

	return selectOptionData($sql);
}//end function


/**
 * Add editted test results to database
 *
 * 20050911 - fm - refactoring
 *
 * 20050905 - fm
 * interface changes
 *
 */
// MHT 200507	added conversion of special chars on input - [ 900437 ] table results -- incoherent data ?
function editTestResults($login_name, $tcData, $buildID)
{
	global $g_bugInterfaceOn, $g_tc_status;
	
	// $build = mysql_escape_string($build);

	$num_tc = count($tcData['tc']);
	
	for ($idx=0; $idx < $num_tc; $idx++ )
	{
		$tcID = $tcData['tc'][$idx];
		$tcNotes = mysql_escape_string(trim($tcData['notes'][$idx])); 
		$tcStatus = mysql_escape_string($tcData['status'][$idx]); 

		$tcBugs = '';
		if ($g_bugInterfaceOn)
		{
			$tcBugs = isset($tcData['bugs'][$idx]) ? mysql_escape_string($tcData['bugs'][$idx]) : ''; 
		}

		// Does exist a result for this (tcid, build) ?
	  /*
		$sql = " SELECT tcid, build, notes, status FROM results " .
		       " WHERE tcid=" . $tcID .  
		       " AND build=" . $buildID;
	  */
	  $sql = " SELECT tcid, build_id, notes, status FROM results " .
		       " WHERE tcid=" . $tcID .  
		       " AND build_id=" . $buildID;

	  
		$result = do_mysql_query($sql); 
		$num = mysql_num_rows($result); 


		if($num == 1)
		{ 
			// We will only update the results if (notes, status) information has changed ...
			$myrow = mysql_fetch_assoc($result);
			if(! ($myrow['notes'] == $tcNotes && $myrow['status'] == $tcStatus) )
			{
				$sql = " UPDATE results " .
				       " SET runby ='" . $login_name . "', " . "status ='" .  $tcStatus . "', " .
				       " notes='" . $tcNotes . "' " .
						   " WHERE tcid=" . $tcID . " AND build_id=" . $buildID;
				$result = do_mysql_query($sql); 
			}
    }
    else
    {
    	// Check to know if we need to insert a new result
			if( !($tcNotes == "" && $tcStatus == $g_tc_status['not_run']) )
			{ 
				$sql = " INSERT INTO results (build_id,daterun,status,tcid,notes,runby) " .
				       " VALUES (" . $buildID . ",CURRENT_DATE(),'" . $tcStatus . 
				       "'," . $tcID . ",'" . $tcNotes . "','" . $login_name . "')";
				$result = do_mysql_query($sql);
      }  
    }
    // -------------------------------------------------------------------------


    // -------------------------------------------------------------------------
    // Update Bug information (delete+insert) 
	  $sqlDelete = "DELETE FROM bugs WHERE tcid=" . $tcID . " and build_id=" . $buildID;
	  $result = do_mysql_query($sqlDelete);

	  $bugArray = strlen($tcBugs) ?  explode(",",$tcBugs) : null;
	  $counter = 0;
	  $num_bugs = count($bugArray);
	  while($counter < $num_bugs)	
	  {

		  $sql = "INSERT INTO bugs (tcid,build,bug) VALUES (" . $tcID . ",'" . 
			  	   $buildID . "','" . $bugArray[$counter] . "')";
		  $result = do_mysql_query($sql); 
		  $counter++;
	  }
    // -------------------------------------------------------------------------
	}

	return ("<div class='info'><p>" . lang_get("Test Results submitted.") . "</p></div>");
}
// -----------------------------------------------------------------------------

	
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
function createTestInput($resultTC,$buildID,$tpID)
{
	global $g_bugInterfaceOn,$g_tc_status;;
	$arrTC = array();
	while ($myrow = mysql_fetch_assoc($resultTC))
	{ 

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
		/* $sql = " SELECT notes, status FROM results WHERE tcid='" . $myrow['tcid']. "' " .
		       " AND build='" . $buildID . "'";
    */
    // 20050926 - fm
    $sql = " SELECT notes, status FROM results " .
           " WHERE tcid=" . $myrow['tcid'] .
		       " AND build_id=" . $buildID;
    
		$resultStatus = do_mysql_query($sql);
		
		$dataStatus = mysql_fetch_row($resultStatus);

		//This query grabs the most recent result
		/*
		$sqlRecentResult = " SELECT build.name AS build_name,status,runby,daterun " .
		                   " FROM results,build " .
				               " WHERE tcid=" . $myrow['tcid'] . " AND status != '" . $g_tc_status['not_run'] . "' " .
				               " AND results.build = build.id " .
				               " AND projid = " . $tpID ." ORDER by build.id " .	"DESC limit 1";
				            
		*/
		$sqlRecentResult = " SELECT build.name AS build_name,status,runby,daterun " .
		                   " FROM results,build " .
				               " WHERE tcid=" . $myrow['tcid'] . " AND status != '" . $g_tc_status['not_run'] . "' " .
				               " AND results.build_id = build.id " .
				               " AND projid = " . $tpID ." ORDER by build.id " .	"DESC limit 1";
				               
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
			$sqlBugs = "SELECT bug FROM bugs WHERE tcid='" . $myrow['tcid'] . "' and build=" . $buildID;
			$resultBugs = do_mysql_query($sqlBugs);

			//For each bug that is found
			while ($myrowBugs = mysql_fetch_assoc($resultBugs))
			{ 
				if (!is_null($resultBugList))
				{
					$resultBugList .= ",";
				}	
				$bugID = $myrowBugs['bug'];
				$resultBugList .= $bugID;
				$bugLinkList[] = $g_bugInterface->buildViewBugLink($bugID,true);
			}
		}
		// add to output array
		$arrTC[] = array( 'id' => $myrow['tcid'],
   						'title' => $myrow['title'],
						'summary' => $myrow['summary'], 
	   					'steps' => $myrow['steps'],
						'outcome' => $myrow['exresult'],
   						'mgttcid' => $myrow['mgttcid'],
						'version' => $myrow['version'],
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