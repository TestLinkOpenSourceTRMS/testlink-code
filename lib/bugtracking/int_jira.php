<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: int_jira.php,v $
 *
 * @version $Revision: 1.13 $
 * @modified $Date: 2009/08/18 19:58:14 $ $Author: schlundus $
 *
 * @author (contributor) jbarchibald@gmail.com
 *
 * Constants used throughout TestLink are defined within this file
 * they should be changed for your environment
 *
 * 20070818 - franciscom - BUGID 973 - Patch by hseffler
 *
 *
 * 20070421 - franciscom - BUGID 805
 * Seems similar to old problem with mantis interface when using MS SQL,
 * but this time with PostgreSQL.
 * Problems on getBugSummaryString($id), fetch_array() does not returns numeric indexes, 
 * then only choice is accessing my field name (IMHO better)
 *
 * Removed also DBNAME on Queries because causes problems.
 * 
 *
 * 20070403 - franciscom - 
 * 1. added an specialized version of checkBugID
 *
**/
/** Interface name */
define('BUG_INTERFACE_CLASSNAME',"jiraInterface");

class jiraInterface extends bugtrackingInterface
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
		return $this->showBugURL.$id;		
	}
	
	/**
	 * Returns the status of the bug with the given id
	 * this function is not directly called by TestLink. 
	 *
	 * @return string returns the status of the given bug (if found in the db), or false else
	 *
 	 * 2005119 - scs - fixed using of wrong index
	 **/
	function getBugStatus($id)
	{
		if (!$this->isConnected())
			return false;

		$status = false;
		
		// 20070818 - francisco.mancardi@gruppotesi.com
		// $query = "SELECT issuestatus FROM jiraissue WHERE pkey='$id'";
		$query = "SELECT s.pname as issuestatus " .
		         "FROM issuestatus s, jiraissue i " .
		         "WHERE i.pkey='$id' AND i.issuestatus = s.ID";
		
		$result = $this->dbConnection->exec_query($query);
		if ($result)
		{
			$status = $this->dbConnection->fetch_array($result);
			if ($status)
			{
				$status = $status['issuestatus'];
			}	
			else
				$status = null;
		}
		return $status;
		
	}
		
	/**
	 * Returns the status in a readable form (HTML context) for the bug with the given id
	 *
	 * @param int id the bug id
	 * 
	 * @return string returns the status (in a readable form) of the given bug if the bug
	 * 		was found, else false
	 *
	 * rev: 
	 *      20070818 - franciscom - BUGID
	 **/
	function getBugStatusString($id)
	{
		$status = $this->getBugStatus($id);
		
		$str = htmlspecialchars($id);
		
		//if the bug wasn't found the status is null and we simply display the bugID
		if ($status !== false)
		{
			$str = $str . " - " . $status;
		    if (strcasecmp($status, 'closed') == 0 || strcasecmp($status, 'resolved') == 0 )
		    {
		    	$str = "<del>" . $str . "</del>";
		    }  
		}
		else
		{
			$str = $id;
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

		$status = null;
		$query = "SELECT summary FROM jiraissue WHERE pkey='$id'";
		$result = $this->dbConnection->exec_query($query);
		if ($result)
		{
			$summary = $this->dbConnection->fetch_array($result);
			if ($summary)
				$summary = $summary['summary'];
			else
				$summary = null;
		}
		return $summary;
	}
	
	
  /**
	 * checks a bug id for validity  
	 * 
	 * @return bool returns true if the bugid has the right format, false else
	 **/
	function checkBugID($id)
	{
		$status_ok = true;
        if(trim($id) == "")
        {
            $status_ok = false;
        }
        if($status_ok)
        {
            $forbidden_chars = '/[!|£%&/()=?]/';
            if (preg_match($forbidden_chars, $id))
            {
                $status_ok = false;
            }
        }
        return $status_ok;
	}	
}
?>
