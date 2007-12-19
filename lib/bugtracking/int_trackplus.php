<?php

/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: int_trackplus.php,v $
 *
 * @version $Revision: 1.3 $
 * @modified $Date: 2007/12/19 18:27:06 $ $Author: schlundus $
 *
 * @author Oscar Castroviejo - 20060405 - TRA de GFI
 * email: ocastroviejo@gfi.es
 * Constants used throughout TestLink are defined within this file
 * they should be changed for your environment
 *
**/

/** Interface name */
define('BUG_INTERFACE_CLASSNAME',"trackplusInterface");

class trackplusInterface extends bugtrackingInterface
{
	//members to store the bugtracking information
	var $dbHost = BUG_TRACK_DB_HOST;
	var $dbName = BUG_TRACK_DB_NAME;
	var $dbType = BUG_TRACK_DB_TYPE;
	var $dbUser = BUG_TRACK_DB_USER;
	var $dbPass = BUG_TRACK_DB_PASS;
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
	 * @author Andreas Morsing <schlundus@web.de>
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
	 * @return string returns the status of the given bug (if found in the db), or false else
	 *
	 * @version 1.1
	 * @author Francisco Mancardi
	 * @since 16.09.2005, 07:45:29
	 * mysql_fetch_assoc
	 *
	 * @version 1.0
	 * @author Andreas Morsing <schlundus@web.de>
	 * @since 22.04.2005, 21:05:25
	 **/
	function getBugStatus($id)
	{
		if (!$this->isConnected())
			return false;

		$status = false;
		$query = "SELECT STATE FROM {$this->dbName}.TWORKITEM WHERE WORKITEMKEY=" . $id;
		$result = $this->dbConnection->exec_query($query);
		if ($result)
		{
			$status = $this->dbConnection->fetch_array($result);
			if ($status)
			{
				$status = $status['status'];
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
		$status = $this->getBUGStatus($id);
		$str = htmlspecialchars($id);
		//if the bug wasn't found the status is null and we simply display the bugID
		if ($status !== false)
		{
			//the status values depends on your trackplus configuration at config_inc.php in $g_status_enum_string, 
			//below is the default:
			//'10:new,20:feedback,30:acknowledged,40:confirmed,50:assigned,80:resolved,90:closed'
			//strike through all bugs that have a resolved or closed status.. 
			if ($status == 80 || $status == 90)
				$str = "<del>" . $id . "</del>";
		}
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
		$query = "SELECT PACKAGESYNOPSYS FROM {$this->dbName}.TWORKITEM WHERE WORKITEMKEY=" . $id;
		$result = $this->dbConnection->exec_query($query);
		if ($result)
		{
			$summary = $this->dbConnection->fetch_array($result);
			if ($summary)
				$summary = $summary[0];
			else
				$summary = null;
		}

		return $summary;
	}
}
?>
