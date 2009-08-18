<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: int_fogbugz.php,v $
 *
 * @version $Revision: 1.3 $
 * @modified $Date: 2009/08/18 19:58:14 $ $Author: schlundus $
 *
 * @author Sjoerd Dirk Meijer
 *
 * Constants used throughout TestLink are defined within this file
 * they should be changed for your environment
 *
**/
/** Interface name */
define('BUG_INTERFACE_CLASSNAME',"fogbugzInterface");

class fogbugzInterface extends bugtrackingInterface
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
		
		$query = "SELECT ixStatus as s FROM bug WHERE ixBug ='" . $id."'";
		$result = $this->dbConnection->exec_query($query);
		if ($result)
		{
			$status = $this->dbConnection->fetch_array($result);
			if ($status)
			{
				$status = $status ['s'];
			}	
			else
				$status = null;
		}
		return $status;
	}

	function getBugOpen($id)
	{
		if (!$this->isConnected())
			return false;

		$open = false;
		
		$query = "SELECT fOpen as r FROM bug WHERE ixBug ='" . $id."'";
		$result = $this->dbConnection->exec_query($query);
		if ($result)
		{
			$open = $this->dbConnection->fetch_array($result);
			if ($open)
			{
				$open = $open ['r'];
			}	
			else
				$open = null;
		}
		return $open;
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
		$open = $this->getBugOpen($id);
		
		$str = htmlspecialchars($id);
		//if the bug wasn't found the status is null and we simply display the bugID
		if ($status !== false)
		{
			//strike through all bugs that have a closed status.. 
			if ($status > 1 && $open == 0)
			{	
				$str = "[closed] <del>" . $id . "</del>";
			}
			else
			//strike through and bold all bugs that have a resolved status
			if ($status > 1 && $open == 1)
			{
				$str = "<b>[resolv.]</b> <del>" . $id . "</del>";
			}
		}
		return $str;
	}

	/**
	 * Fetches the bug summary from the fogbugz db
	 *
	 * @param int id the bug id
	 * 
	 * @return string returns the bug summary if bug is found, else false
	 **/
	function getBugSummaryString($id)
	{
		if (!$this->isConnected())
			return false;

		$status = null;

		$query = "SELECT sTitle as t FROM bug WHERE ixBug ='" . $id."'";
		$result = $this->dbConnection->exec_query($query);
		if ($result)
		{
			$summary = $this->dbConnection->fetch_array($result);
	
			if ($summary)
				$summary = $summary ['t'];
			else
				$summary = null;
		}
		return $summary;
	}


  /**
	 * checks if bug id is present on BTS
	 * 
	 * @return bool 
	 **/
	function checkBugID_existence($id)
	{
	  	$status_ok = false;	
		$query = "SELECT ixStatus FROM bug WHERE ixBug ='" . $id."'";
		$result = $this->dbConnection->exec_query($query);
		if ($result && ($this->dbConnection->num_rows($result) == 1))
		{
      		$status_ok = true;    
    	}
		return $status_ok;
	}	
}
?>