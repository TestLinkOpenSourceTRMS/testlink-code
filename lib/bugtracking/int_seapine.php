<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename: int_seapine.php
 *
 * @author needles - 20080207 - created
 * 20110227 - franciscom - BUGID 4266 - removed $m_ prefix from class properties
 * 20080207 - needles - created
**/
/** Interface name */
define('BUG_INTERFACE_CLASSNAME',"seapineInterface");

class seapineInterface extends bugtrackingInterface
{
	//members to store the bugtracking information
	var $dbHost = BUG_TRACK_DB_HOST;
	var $dbProjectID = BUG_TRACK_PROJECT_ID;
	var $dbName = BUG_TRACK_DB_NAME;
	var $dbUser = BUG_TRACK_DB_USER;
	var $dbPass = BUG_TRACK_DB_PASS;
	var $dbType = BUG_TRACK_DB_TYPE;
	var $showBugURL = BUG_TRACK_HREF;
	var $enterBugURL = BUG_TRACK_ENTER_BUG_HREF;
	
	/**
	 * Return the URL to the bugtracking page for viewing 
	 * the bug with the given id. 
	 *
	 * @param int id the bug id
	 * 
	 * @return string returns a complete URL to view the bug
	 *
	 * @version 1.0
	 * @author Andreas Morsing 
	 * @since 22.04.2005, 21:05:25
	 **/
	function buildViewBugURL($id)
	{
		return $this->showBugURL.$id;		
	}
	
	/**
	 * Returns the status of the bug with the given id
	 * this function is not directly called by TestLink. 
	 *
	 * @return string returns the status of the given bug (if found in the db), or null else
	 **/
	function getBugStatus($id)
	{
		if (!$this->isConnected())
			return null;
	
		$status = null;
		$query = "SELECT Name as bug_status from {$this->dbName}.defects, {$this->dbName}.states
				WHERE defects.status = states.idrecord AND DefectNum='" . $id."' 
				AND states.projectid ='{$this->dbProjectID}' 
				AND defects.projectid ='{$this->dbProjectID}'";
		
		$result = $this->dbConnection->exec_query($query);
		if ($result)
		{
			$status = $this->dbConnection->fetch_array($result);
			if ($status)
			{
				$status = $status['bug_status'];
			}
			else
				$status = null;	
		}
		return $status;
	}
	
	/**
	 * Returns the bug summary in a human redable format, cutted down to 45 chars
	 *
	 * @return string returns the summary (in readable form) of the given bug
	 *
	 * @version 1.0
	 * @author Raphael Bosshard
	 * @author Arjen van Summeren
	 * @since 28.09.2005, 16:06:25
	 **/
	function getBugSummaryString($id)
	{
		if (!$this->isConnected())
		{
			return null;
    }
    
		$status = null;
		$query = "SELECT Summary as shrt_desc FROM {$this->dbName}.defects WHERE DefectNum='" . $id."' 
		AND defects.projectid ='{$this->dbProjectID}'";

		$result = $this->dbConnection->exec_query($query);
		$summary = null;
		if ($result)
		{
			$summary = $this->dbConnection->fetch_array($result);
			if ($summary)
			{
				$summary = $summary[0];
				if(tlStringLen($summary) > 45)
				{
					$summary = tlSubStr($summary, 0, 42) . "...";
				}
			}
			else
				$summary = null;
		}
		
		return $summary;
	}	
	
	
	
	/**
	 * Returns the status in a readable form (HTML context) for the bug with the given id
	 *
	 * @param int id the bug id
	 * 
	 * @return string returns the status (in a readable form) of the given bug 
	 *
	 * @version 1.1
	 * @author Arjen van Summeren - changed to return correct status STRING and not status ID
	 * @author Andreas Morsing 
	 * @since 10.10.2005, 17:40:32
	 **/
	function getBugStatusString($id)
	{
		$status = $this->getBUGStatus($id);
		
		//if the bug wasn't found the status is null and we simply display the bugID
		$str = htmlspecialchars($id);
		if (!is_null($status))
		{
			//strike through all bugs that have a resolved, verified, or closed status.. 
			if('RESOLVED' == $status || 'VERIFIED' == $status || 'Closed' == $status)
				$str = "<del>" . htmlspecialchars($id). "</del>";
		}
		return $str;
	}
}
?>
