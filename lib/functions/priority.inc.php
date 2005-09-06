<?
/* TestLink Open Source Project - http://testlink.sourceforge.net/ */
/* $Id: priority.inc.php,v 1.3 2005/09/06 06:44:07 franciscom Exp $ */
/**
 * Functions for Priority management 
 * Precondition: require init db + session verification done (testlinkInitPage();) 
 *
 *
 * @author 20050905 - fm - reduce global cpupling
 *
 * @author 20050807 - fm
 * refactoring:  
 * removed deprecated: $_SESSION['project']
 */
////////////////////////////////////////////////////////////////////////////////

require_once('../../config.inc.php');
require_once("../functions/common.php");

/**
 * Collect information about rules for priority within actual Plan
 * @return array of array: id, priority, name of item 
 */
function getPriority($tpID)
{
	$arrData = array();
	
	// 20050807 - fm
	$sql = "select id, riskImp, priority from priority where projid=" . $tpID;
	$result = do_mysql_query($sql); //Run the query

	while($row = mysql_fetch_array($result)){
		array_push($arrData, array('id' => $row['id'], 'priority'=> $row['priority'],
			'name'=>$row['riskImp']));
	}
	return $arrData;
}


/**
 * Set rules for priority within actual Plan
 *
 * @param array $newArray $_POST input converted to simple numbered array
 * @return string 'ok'
 * @todo return could depend on sql result
 */
function setPriority($newArray)
{
	$i = 0; //Start the counter 
	while ($i < (count($newArray) - 1)){ //Loop for the entire size of the array

		$priID = $newArray[$i]; //Then the first value is the ID
		$priority = $newArray[$i + 1]; //The second value is the notes
		
		//SQL statement to look for the same record (tcid, build = tcid, build)
		$sql = "select id, priority from priority where id='" . $priID . "'";
		$result = do_mysql_query($sql); //Run the query
		$num = mysql_num_rows($result); //How many results
		
		if($num == 1){ //If we find a matching record
	
			$myrow = mysql_fetch_row($result);
			$queryPri = $myrow[1];
	
			//Update if different
			if($queryPri != $priority) {
				$sql = "UPDATE priority set priority ='" . $priority . "' where id='" . $priID . "'";
				$result = do_mysql_query($sql);
			}
		}
		$i = $i + 2; //Increment 
	}//end while
	return 'ok';
}
?>