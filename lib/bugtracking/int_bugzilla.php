<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: int_bugzilla.php,v $
 *
 * @version $Revision: 1.17 $
 * @modified $Date: 2010/03/08 13:39:57 $ $Author: asimon83 $
 *
 * @author Arjen van Summeren - 20051010 - inserted function getBugSummary($id) again, 
 *                                         corrected getBugStatusString($id)
 * @author Raphael Bosshard - 20051010 - inserted function getBugSummary($id) again
 * @author Francisco Mancardi - 20050916 - refactoring
 *
 * Constants used throughout TestLink are defined within this file
 * they should be changed for your environment
 *
 * rev: 
 * 20100308 - Julian - added function checkBugID_existence()
 * 20080321 - franciscom - BUGID 1444 - user contribution pvmeerbe
 * 20051202 - scs - added returning null in some cases
 * 20051229 - scs - added ADOdb support
**/
/** Interface name */
define('BUG_INTERFACE_CLASSNAME',"bugzillaInterface");

class bugzillaInterface extends bugtrackingInterface
{
	//members to store the bugtracking information
	var $dbHost = BUG_TRACK_DB_HOST;
	var $dbName = BUG_TRACK_DB_NAME;
	var $dbUser = BUG_TRACK_DB_USER;
	var $dbPass = BUG_TRACK_DB_PASS;
	var $dbType = BUG_TRACK_DB_TYPE;
  	var $dbSchema = BUG_TRACK_DB_NAME;  // BUGID 1444
	var $showBugURL = BUG_TRACK_HREF;
	var $enterBugURL = BUG_TRACK_ENTER_BUG_HREF;
	/*
	   BUGID 1444
	
	*/
	function bugzillaInterface()
    {
 	    parent::bugtrackingInterface();
 	    if (defined ('BUG_TRACK_DB_SCHEMA')) 
 	    {
	    	$this->dbSchema = BUG_TRACK_DB_SCHEMA;
	    }
    }
	
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
		$query = "SELECT bug_status FROM {$this->dbSchema}.bugs WHERE bug_id='" . $id."'";
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
			return null;
    
		$status = null;
		$query = "SELECT short_desc FROM {$this->dbSchema}.bugs WHERE bug_id='" . $id."'";
		
		$result = $this->dbConnection->exec_query($query);
		$summary = null;
		if ($result)
		{
			$summary = $this->dbConnection->fetch_array($result);
			if ($summary)
			{
				$summary = array_pop ($summary);
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
		$status = $this->getBugStatus($id);
		
		//if the bug wasn't found the status is null and we simply display the bugID
		$str = htmlspecialchars($id);
		if (!is_null($status))
		{
			//strike through all bugs that have a resolved, verified, or closed status.. 
			if('RESOLVED' == $status || 'VERIFIED' == $status || 'CLOSED' == $status)
				$str = "<del>" . htmlspecialchars($id). "</del>";
		}
		return $str;
	}
	
	/**
	 * checks is bug id is present on BTS
	 * 
	 * @return bool 
	 **/
	function checkBugID_existence($id)
	{
		$status_ok=0;	
		$query = "SELECT bug_status FROM {$this->dbSchema}.bugs WHERE bug_id=".$id."";
		$result = $this->dbConnection->exec_query($query);
		if ($result && ($this->dbConnection->num_rows($result) == 1) )
		{
			$status_ok=1;   
		}
		return $status_ok;
	}
}
?>
