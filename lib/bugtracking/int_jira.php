<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: int_jira.php,v $
 *
 * @version $Revision: 1.4 $
 * @modified $Date: 2005/12/28 07:12:19 $
 *
 * @author (contributor) jbarchibald@gmail.com
 *
 * Constants used throughout TestLink are defined within this file
 * they should be changed for your environment
 *
 * 20051202 - scs - added returning null in some cases
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
	var $m_showBugURL = BUG_TRACK_HREF;
	var $m_enterBugURL = BUG_TRACK_ENTER_BUG_HREF;
	
	/**
	 * Return the URL to the bugtracking page for viewing 
	 * the bug with the given id. 
	 *
	 * @param int id the bug id
	 * 
	 * @return string returns a complete URL to view the bug
	 *
	 * @version 1.0
	 * @author Andreas Morsing <schlundus@web.de>
	 * @since 22.04.2005, 21:05:25
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
	 * @version 1.1
	 * @author Francisco Mancardi
	 * @since 16.09.2005, 07:45:29
	 *
	 * @version 1.0
	 * @author Andreas Morsing <schlundus@web.de>
	 * @since 22.04.2005, 21:05:25
	 * 
	 * 2005119 - scs - fixed using of wrong index
	 **/
	function getBugStatus($id)
	{
		if (!$this->isConnected())
			return false;

		$status = false;
		$query = "SELECT issuestatus FROM {$this->m_dbName}.jiraissue WHERE pkey='$id'";
		$result = do_sql_query($query,$this->m_dbConnection);
		if ($result)
		{
			$status = $GLOBALS['db']->fetch_array($result);
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
	 * 		was found , else false
	 *
	 * @version 1.0
	 * @author Andreas Morsing <schlundus@web.de>
	 * @since 22.04.2005, 21:05:25
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
	 *
	 * @version 1.0
	 * @author Andreas Morsing 
	 * @since 22.04.2005, 21:05:25
	 **/
	function getBugSummaryString($id)
	{
		if (!$this->isConnected())
			return false;

		$status = null;
		$query = "SELECT summary FROM {$this->m_dbName}.jiraissue WHERE pkey='$id'";
		$result = do_sql_query($query,$this->m_dbConnection);
		if ($result)
		{
			$summary = $GLOBALS['db']->fetch_array($result);
			if ($summary)
				$summary = $summary[0];
			else
				$summary = null;
		}
		return $summary;
	}
}
?>