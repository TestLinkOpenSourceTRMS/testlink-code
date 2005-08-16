<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: int_mantis.php,v $
 *
 * @version $Revision: 1.2 $
 * @modified $Date: 2005/08/16 18:00:54 $
 *
 * @author Andreas Morsing
 *
 * Constants used throughout TestLink are defined within this file
 * they should be changed for your environment
 *
**/
/** Interface name */
define('BUG_INTERFACE_CLASSNAME',"mantisInterface");

class mantisInterface extends bugtrackingInterface
{
	//members to store the bugtracking information
	var $m_dbHost = BUG_TRACK_DB_HOST;
	var $m_dbName = BUG_TRACK_DB_NAME;
	var $m_dbUser = BUG_TRACK_DB_USER;
	var $m_dbPass = BUG_TRACK_DB_PASS;
	var $m_showBugURL = BUG_TRACK_HREF;

	
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
	 * @return string returns the status of the given bug (if found in the db), or null else
	 *
	 * @version 1.0
	 * @author Andreas Morsing <schlundus@web.de>
	 * @since 22.04.2005, 21:05:25
	 **/
	function getBugStatus($id)
	{
		if (!$this->isConnected())
			return null;

		$status = null;
		$query = "SELECT status FROM {$this->m_dbName}.mantis_bug_table WHERE id=" . $id;
		$result = do_mysql_query($query,$this->m_dbConnection);
		if ($result)
		{
			$status = mysql_fetch_row($result);
			if ($status)
				$status = $status[0];
		}
		return $status;
		
	}
		
	/**
	 * Returns the status in a readable form (HTML context) for the bug with the given id
	 *
	 * @param int id the bug id
	 * 
	 * @return string returns the status (in a readable form) of the given bug 
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
		if (!is_null($status))
		{
			//the status values depends on your mantis configuration at config_inc.php in $g_status_enum_string, 
			//below is the default:
			//'10:new,20:feedback,30:acknowledged,40:confirmed,50:assigned,80:resolved,90:closed'
			//strike through all bugs that have a resolved or closed status.. 
			if ($status == 80 || $status == 90)
				$str = "<del>" . $id . "</del>";
		}
		return $str;
	}
}
?>