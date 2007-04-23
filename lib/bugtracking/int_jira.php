<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: int_jira.php,v $
 *
 * @version $Revision: 1.8 $
 * @modified $Date: 2007/04/23 17:00:00 $
 *
 * @author (contributor) jbarchibald@gmail.com
 *
 * Constants used throughout TestLink are defined within this file
 * they should be changed for your environment
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
 *
 *
 *
 *
**/
/** Interface name */
define('BUG_INTERFACE_CLASSNAME',"jiraInterface");

class jiraInterface extends bugtrackingInterface
{
	//members to store the bugtracking information
	var $m_dbHost = BUG_TRACK_DB_HOST;
	var $m_dbName = BUG_TRACK_DB_NAME;
	var $m_dbUser = BUG_TRACK_DB_USER;
	var $m_dbPass = BUG_TRACK_DB_PASS;
	var $m_dbType = BUG_TRACK_DB_TYPE;
	var $m_showBugURL = BUG_TRACK_HREF;
	var $m_enterBugURL = BUG_TRACK_ENTER_BUG_HREF;
	
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
		return $this->m_showBugURL.$id;		
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
		$query = "SELECT issuestatus FROM jiraissue WHERE pkey='$id'";
		$result = $this->m_dbConnection->exec_query($query);
		if ($result)
		{
			$status = $this->m_dbConnection->fetch_array($result);
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
	 **/
	function getBugStatusString($id)
	{
		$status = $this->getBugStatus($id);
		
		$str = htmlspecialchars($id);
		//if the bug wasn't found the status is null and we simply display the bugID
		if ($status !== false)
		{
			//the status values depends on your mantis configuration at config_inc.php in $g_status_enum_string, 
			//below is the default:
			//'1"Open,3:InProgress,4:Re-Opened,5:resolved,6:closed'
			//strike through all bugs that have a resolved or closed status.. 
			if ($status == 5 || $status == 6)
				$str = "<del>" . $id . "</del>";
		}
		else
			$status	= null;
			
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
		$result = $this->m_dbConnection->exec_query($query);
		if ($result)
		{
			$summary = $this->m_dbConnection->fetch_array($result);
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
	  $status_ok=1;	
    if(strlen(trim($id)) == 0 )
    {
      $status_ok=0;	
    }
	  
	  if( $status_ok )
	  {
	    $ereg_forbidden_chars='[!|£%&/()=?]';

 		  if (eregi($ereg_forbidden_chars, $id))
		  {
			  $status_ok=0;	
		  } 	
	  }
		return $status_ok;
	}	
}
?>