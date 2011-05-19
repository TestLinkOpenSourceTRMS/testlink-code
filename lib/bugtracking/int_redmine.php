<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: int_redmine.php,v $
 *
 * @version $Revision: 1.5 $
 * @modified $Date: 2009/08/18 19:58:14 $ $Author: schlundus $
 * 
 * Constants used throughout TestLink are defined within this file
 * they should be changed for your environment
 * 
 * @author Toshiyuki Kawanishi, Hantani (Garyo), TestLink User Community in Japan
 *
 * Thanks to redMine Japanese User Community.
 * We can get advice on redMine settings from them. 
 */

/** Interface name */
define('BUG_INTERFACE_CLASSNAME',"redmineInterface");

class redmineInterface extends bugtrackingInterface
{
	//members to store the bugtracking information
	var $dbHost = BUG_TRACK_DB_HOST;
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
	 **/
	function buildViewBugURL($id)
	{
		return $this->showBugURL.urlencode($id);
	}
	
	/**
	 * Returns the status of the bug with the given id
	 * this function is not directly called by TestLink. 
	 *
	 * @return string returns the status of the given bug (if found in the db), or false else
	 **/
	function getBugStatus($id)
	{
		if (!$this->isConnected())
			return false;

		$status = false;
		$query = "SELECT status_id FROM issues WHERE id='" . $id."'";
		
		$result = $this->dbConnection->exec_query($query);
		if ($result)
		{
			$status = $this->dbConnection->fetch_array($result);
			if ($status)
			{
				$status = $status['status_id'];
			}	
			else
			{
				$status = null;
			}
		}
		return $status;
		
	}
		
	/**
	 * Returns the status in a readable form (HTML context) for the bug with the given id
	 *
	 * @param int id the bug id
	 * 
	 * @return string returns the status (in a readable form) of the given bug if the bug
	 * 		was found , else false
	 **/
	function getBugStatusString($id)
	{
		$status = $this->getBugStatus($id);
		
		$str = htmlspecialchars($status);
		$query = "SELECT name, is_closed FROM issue_statuses WHERE id='" . $status . "'";

		//if the bug wasn't found the status is null and we simply display the bugID
		if ($status !== false)
		{
			$query_results = $this->dbConnection->exec_query($query);
			if ($query_results)
			{
				$result = $this->dbConnection->fetch_array($query_results);
				if ($result)
				{
					$str = $result['name'];

 					if ($result['is_closed'] == '1')
 					{
 						$str = "<del>" . $str . "</del>";
 					}
				}	
			}			
		}
		return $str;
	}

	/**
	 * Fetches the bug summary from the matnis db
	 *
	 * @param int id the bug id
	 * 
	 * @return string returns the bug summary if bug is found, else false
	 **/
	function getBugSummaryString($id)
	{
		if (!$this->isConnected())
			return false;

		$query = "SELECT subject FROM issues WHERE id='" . $id."'";
		
		$query_results = $this->dbConnection->exec_query($query);
		if ($query_results)
		{
			$summary = $this->dbConnection->fetch_array($query_results);

			if ($summary)
			{
				$summary = $summary['subject'];
			}
			else
			{
				$summary = null;
			}
		}
		return $summary;
	}

  /**
	 * checks is bug id is present on BTS
	 * 
	 * @return bool 
	 **/
	function checkBugID_existence($id)
	{
		$status_ok = false;	
		$query = "SELECT id FROM issues WHERE id='" . $id."'";
		$query_results = $this->dbConnection->exec_query($query);
		if ($query_results && ($this->dbConnection->num_rows($query_results) == 1))
		{
			$status_ok = true;    
		}
		return $status_ok;
	}	
}
?>