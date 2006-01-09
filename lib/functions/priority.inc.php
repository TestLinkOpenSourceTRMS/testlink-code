<?
/* TestLink Open Source Project - http://testlink.sourceforge.net/ */
/* $Id: priority.inc.php,v 1.7 2006/01/09 07:15:43 franciscom Exp $ */
/**
 * Functions for Priority management 
 * Precondition: require init db + session verification done (testlinkInitPage();) 
 *
 *
 * @author 20050905 - fm - reduce global cpupling
 *
 */
////////////////////////////////////////////////////////////////////////////////

require_once('../../config.inc.php');
require_once("../functions/common.php");

/**
 * Collect information about rules for priority within actual Plan
 * @return array of array: id, priority, name of item 
 */
function getPriority(&$db,$tpID)
{
	$arrData = array();
	
	// 20050807 - fm
	$sql = " SELECT id, riskImp, priority " .
	       " FROM priority WHERE projid=" . $tpID;
	$result = $db->exec_query($sql); //Run the query

	while($row = $db->fetch_array($result)){
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
function setPriority(&$db,$newArray)
{
	$i = 0; //Start the counter 
	while ($i < (count($newArray) - 1)){ //Loop for the entire size of the array

		$priID = $newArray[$i]; //Then the first value is the ID
		$priority = $newArray[$i + 1]; //The second value is the notes
		
		//SQL statement to look for the same record (tcid, build = tcid, build)
		$sql = "SELECT id, priority FROM priority WHERE id='" . $priID . "'";
		$result = $db->exec_query($sql); //Run the query
		$num = $db->num_rows($result); //How many results
		
		if($num == 1){ //If we find a matching record
	
			$myrow = $db->fetch_array($result);
			$queryPri = $myrow[1];
	
			//Update if different
			if($queryPri != $priority) {
				$sql = "UPDATE priority SET priority ='" . $priority . "' WHERE id='" . $priID . "'";
				$result = $db->exec_query($sql);
			}
		}
		$i = $i + 2; //Increment 
	}//end while
	return 'ok';
}
?>