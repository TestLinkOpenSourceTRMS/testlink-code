<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: int_gforge.php,v $
 *
 * @version $Revision: 1.3 $
 * @modified $Date: 2009/08/18 19:58:14 $ $Author: schlundus $
 *
 * @author John Wanke - 20080825 - initial revision.
 *
 * Constants used throughout TestLink are defined within this file
 * they should be changed for your environment
 *
**/
/** Interface name */
define('BUG_INTERFACE_CLASSNAME',"gforgeInterface");

class gforgeInterface extends bugtrackingInterface
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
	 *
	 * @version 1.0
	 * @author John Wanke
   *         added query for gfproject name and replacement in $this->showBugURL.
	 *         added $URLsubstring_to_replace usage of BUG_TRACK_PROJECT value from cfg/gforge.cfg.php.
	 * @since 23.10.2008, 08:14:00
	 **/
	function buildViewBugURL($id)
	{
    	$URLsubstring_to_replace = BUG_TRACK_PROJECT;
		if ($this->isConnected())
		{
			$gfproject = null;

			/* get the project name for this GForge Tracker */
			$query = "SELECT unix_name FROM project
                                  WHERE project_id = (
                                    SELECT project_id FROM tracker
                                    WHERE tracker_id = (
                                      SELECT tracker_id FROM tracker_item 
                                      WHERE tracker_item_id = " . $id . "))";

			$result = $this->dbConnection->exec_query($query);
			if ($result)
			{
				$gfproject = $this->dbConnection->fetch_array($result);
				if ($gfproject)
				{
					/* update the showBugURL value with the actual project name */
					$this->showBugURL = preg_replace('/'.$URLsubstring_to_replace.'/',
					                                   $gfproject['unix_name'],$this->showBugURL);
				}
			}
		}

		return $this->showBugURL.$id;		
	}
	
	/**
	 * Returns the status of the bug with the given id
	 * this function is not directly called by TestLink. 
	 *
	 * @return string returns the status of the given bug (if found in the db), or null else
	 *
	 * @version 1.1
	 * @author John Wanke
	 *         updated $query value to be GForge-specific.
         *         updated to use correct associative index ['element_name'].
	 * @since 23.10.2008, 16:29:00
	 **/
	function getBugStatus($id)
	{
		if (!$this->isConnected())
			return null;
	
		$status = null;
		/* Should return either "Open" or "Closed" */
		$query = "SELECT element_name FROM tracker_extra_field_element
                          WHERE tracker_extra_field_id=(
                            SELECT tracker_extra_field_id FROM tracker_extra_field
                            WHERE field_name='Status' 
                            AND tracker_id=(
                              SELECT tracker_id FROM tracker_item 
                              WHERE tracker_item_id='" . $id."'))
                              AND status_id=(SELECT status_id FROM tracker_item WHERE tracker_item_id='" . $id."')";

		$result = $this->dbConnection->exec_query($query);
		if ($result)
		{
			$status = $this->dbConnection->fetch_array($result);
			if ($status)
			{
				$status = $status['element_name'];
			}
			else
				$status = null;	
		}
		return $status;
	}
	
	/**
	 * Returns the bug summary in a human readable format, cut down to 45 chars
	 *
	 * @return string returns the summary (in readable form) of the given bug
	 *
	 * @version 1.2
	 * @author Raphael Bosshard
	 * @author Arjen van Summeren
	 * @author John Wanke
	 *         updated $query value to be GForge-specific.
   *         updated to use associative index ['summary'] instead of [0].
	 * @since 23.10.2008, 16:29:00
	 **/
	function getBugSummaryString($id)
	{
		if (!$this->isConnected())
			return null;
        
		$status = null;
		$summary = null;

		$query = "SELECT summary FROM tracker_item WHERE tracker_item_id='" . $id."'";
		$result = $this->dbConnection->exec_query($query);
		if ($result)
		{
			$summary = $this->dbConnection->fetch_array($result);
			if ($summary)
			{
				$summary = $summary['summary'];
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
	 * @version 1.2
	 * @author Arjen van Summeren - changed to return correct status STRING and not status ID
	 * @author Andreas Morsing 
	 *
	 * @author John Wanke 
         *         corrected $this->getBUGStatus($id) to be $this->getBugStatus($id)
	 *         updated status values to reflect GForge values.
	 * @since 23.10.2008, 16:29:00
	 **/
	function getBugStatusString($id)
	{
		$status = $this->getBugStatus($id);
		
		//if the bug wasn't found the status is null and we simply display the bugID
		$str = htmlspecialchars($id);
		if (!is_null($status))
		{
			//strike through all bugs that have a resolved, verified, or closed status.. 
			if('Resolved' == $status || 'Verified' == $status || 'Closed' == $status)
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
	  	$status_ok = false;	
		$query = " SELECT tracker_id FROM tracker_item " .
		         " WHERE tracker_item_id = {$id}" ;
		$result = $this->dbConnection->exec_query($query);
		if ($result && ($this->dbConnection->num_rows($result) == 1))
		{
      		$status_ok = true;    
    	}
		return $status_ok;
	}	
}
?>
