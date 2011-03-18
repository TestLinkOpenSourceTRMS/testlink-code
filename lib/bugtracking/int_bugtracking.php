<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 *
 * @filesource	int_bugtracking.php
 *
 * @author Andreas Morsing
 *
 * Baseclass for connection to additional bug tracking interfaces
 * TestLink uses bugzilla to check if displayed bugs resolved, verified,
 * and closed bugs. If they are it will strike through them
 *
 * For supporting a bug tracking system this class has to be extended
 * All bug tracking customization should be done in a sub class of this
 * class . for an example look at the bugzilla.cfg.php and mantis.cfg.php
 *
 *
 * @internal revisions
 *	20110318 - franciscom - BUGID 
 *	20100823 - franciscom - BUGID 3699
 *	20100814 - franciscom - BUGID 3681 - new BTS youtrack (www.jetbrains.com)
 *	20100616 - eloff - Show error message if bts config is broken
 *	20100311 - Julian - BUGID 3256, BUGID 3098
 *						function checkBugID_existence() has to return true
 *						in this parent class to be able to add bugs if
 *						function has not been overloaded in child classes
 *
 *	20081217 - franciscom - BUGID 1939
 *							removed global coupling, usign config_get()
 *	20081102 - franciscom - refactored to ease configuration
 *	20080207 - needles - added notation for Seapine's TestTrackPro
 *	20070505 - franciscom - TL_INTERFACE_BUGS -> $g_interface_bugs
 *	20070304 - franciscom - added new method checkBugID_existence()
 *
 *
**/
require_once(TL_ABS_PATH. "/lib/functions/database.class.php");

// Add new bugtracking interfaces here
// If user configures an interface not declared here, pages trying to use bts
// will give error message
$btslist = array('BUGZILLA','MANTIS','JIRA', 'JIRASOAP', 'TRACKPLUS','POLARION',
		    	 'EVENTUM','TRAC','SEAPINE','REDMINE','GFORGE','FOGBUGZ','YOUTRACK');

$bts = array_flip($btslist);

//Set the bug tracking system Interface
class bugtrackingInterface
{
	//members to store the bugtracking information, these values are
	//set in the actual subclasses of this class
	var $dbHost = null;
	var $dbName = null;
	var $dbUser = null;
	var $dbPass = null;
	var $dbType = null;
	var $showBugURL = null;
	var $enterBugURL = null;
	var $dbCharSet = null;
	var $tlCharSet = null;

	//private vars don't touch
	var $dbConnection = null;
	var $Connected = false;

	/*
	*
	* FUNCTIONS NOT CALLED BY TestLink (helpers):
	*
	**/


	/**
	 * Constructor of bugtrackingInterface
	 * put special initialization in here
	 *
	 * @version 1.0
	 * @author Andreas Morsing
	 * @since 22.04.2005, 21:03:32
	 **/
	function bugtrackingInterface()
	{
	    $this->tlCharSet = config_get('charset');
		if (defined('BUG_TRACK_DB_CHARSET'))
		{
	 		$this->dbCharSet = BUG_TRACK_DB_CHARSET;
	 	}
 	  	else
	 	{
			$this->dbCharSet = $this->tlCharSet;
		}
	}

	/**
	 * this function establishes the database connection to the
	 * bugtracking system
	 *
	 * @return bool returns true if the db connection was established and the
	 * db could be selected, false else
	 *
	 * @version 1.0
	 * @author Francisco Mancardi
	 * @since 14.09.2006
	 *
	 * @version 1.0
	 * @author Andreas Morsing
	 * @since 22.04.2005, 21:05:25
	 **/
	function connect()
	{
		if (is_null($this->dbHost) || is_null($this->dbUser))
		{
			return false;
		}
       
		$this->dbConnection = new database($this->dbType);
		$result = $this->dbConnection->connect(false, $this->dbHost,$this->dbUser,$this->dbPass, $this->dbName);

		if (!$result['status'])
		{
			$this->dbConnection = null;
			$bts_type = config_get('interface_bugs');
			$connection_args = "(interface: $bts_type - Host:$this->dbHost - DBName: $this->dbName - User: $this->dbUser) "; 
			$msg = sprintf(lang_get('BTS_connect_to_database_fails'),$connection_args);
			tLog($msg  . $result['dbms_msg'], 'ERROR');
		}

		elseif (BUG_TRACK_DB_TYPE == 'mysql')
		{
			if ($this->dbCharSet == 'UTF-8')
			{
				$r = $this->dbConnection->exec_query("SET CHARACTER SET utf8");
				$r = $this->dbConnection->exec_query("SET NAMES utf8");
				$r = $this->dbConnection->exec_query("SET collation_connection = 'utf8_general_ci'");
			}
			else
			{
				$r = $this->dbConnection->exec_query("SET CHARACTER SET ".$this->dbCharSet);
				$r = $this->dbConnection->exec_query("SET NAMES ".$this->dbCharSet);
			}
		}

		$this->Connected = $result['status'] ? 1 : 0;

		return $this->Connected;
	}
	/**
	 * this function simply returns the state of the db connection
	 *
	 * @return bool returns true if the db connection is established, false else
	 *
	 * @version 1.0
	 * @author Andreas Morsing
	 * @since 22.04.2005, 21:05:25
	 **/
	function isConnected()
	{
		return ($this->Connected && is_object($this->dbConnection)) ? 1 : 0;
	}

	/**
	 * this function closes the db connection (if any)
	 *
	 * @version 1.0
	 * @author Andreas Morsing
	 * @since 22.04.2005, 21:05:25
	 **/
	function disconnect()
	{
		if (isConnected())
		{
			$this->dbConnection->close();
		}
		$this->Connected = false;
		$this->dbConnection = null;
	}

	/**
	 * overload this to return the URL to the bugtracking page for viewing
	 * the bug with the given id. This function is not directly called by
	 * TestLink at the moment
	 *
	 * @param int id the bug id
	 *
	 * @return string returns a complete URL to view the given bug, or false if the bug
	 * 			wasnt found
	 *
	 * @version 1.0
	 * @author Andreas Morsing
	 * @since 22.04.2005, 21:05:25
	 **/
	function buildViewBugURL($id)
	{
		return false;
	}

	/**
	 * overload this to return the status of the bug with the given id
	 * this function is not directly called by TestLink.
	 *
	 * @param int id the bug id
	 *
	 * @return any returns the status of the given bug, or false if the bug
	 *			was not found
	 * @version 1.0
	 * @author Andreas Morsing
	 * @since 22.04.2005, 21:05:25
	 **/
	function getBugStatus($id)
	{
		return false;
	}

	/**
	 * overload this to return the status in a readable form for the bug with the given id
	 * This function is not directly called by TestLink
	 *
	 * @param int id the bug id
	 *
	 * @return any returns the status (in a readable form) of the given bug, or false
	 * 			if the bug is not found
	 *
	 * @version 1.0
	 * @author Andreas Morsing
	 * @since 22.04.2005, 21:05:25
	 **/
	function getBugStatusString($id)
	{
		return false;
	}


	/*
	*
	* FUNCTIONS CALLED BY TestLink:
	*
	**/
	/**
	 * default implementation for fetching the bug summary from the
	 * bugtracking system
	 *
	 * @param int id the bug id
	 *
	 * @return string returns the bug summary (if bug is found), or false
	 *
	 * @version 1.0
	 * @author Andreas Morsing
	 * @since 22.04.2005, 21:05:25
	 **/
	function getBugSummaryString($id)
	{
		return false;
	}

	/**
	 * simply returns the URL which should be displayed for entering bugs
	 *
	 * @return string returns a complete URL
	 *
	 * @version 1.0
	 * @author Andreas Morsing
	 * @since 25.08.2005, 21:05:25
	 **/
	function getEnterBugURL()
	{
		return $this->enterBugURL;
	}

	/**
	 * checks a bug id for validity, that means numeric only
	 *
	 * @return bool returns true if the bugid has the right format, false else
	 **/
	function checkBugID($id)
	{
		$valid = true;	
	  	$forbidden_chars = '/\D/i';  
		if (preg_match($forbidden_chars, $id))
    	{
			$valid = false;	
    	}
		else 
    	{
	    	$valid = (intval($id) > 0);	
    	}

      	return $valid;
	}

	/**
	 * return the maximum length in chars of a bug id
	 * @return int the maximum length of a bugID
	 */
	function getBugIDMaxLength()
	{
		return 16;
	}

	/**
	 * default implementation for generating a link to the bugtracking page for viewing
	 * the bug with the given id in a new page
	 *
	 * @param int id the bug id
	 *
	 * @return string returns a complete URL to view the bug (if found in db)
	 *
	 * @version 1.1
	 * @author Andreas Morsing
	 * @author Raphael Bosshard
	 * @author Arjen van Summeren
	 * @since 28.09.2005, 16:02:25
	 **/
	function buildViewBugLink($bugID,$bWithSummary = false)
	{
		$link = "<a href='" .$this->buildViewBugURL($bugID) . "' target='_blank'>";
		$status = $this->getBugStatusString($bugID);

		if (!is_null($status))
		{
			$status = iconv($this->dbCharSet,$this->tlCharSet,$status);
			$link .= $status;
		}
		else
			$link .= $bugID;
		if ($bWithSummary)
		{
			$summary = $this->getBugSummaryString($bugID);

			if (!is_null($summary))
			{
				$summary = iconv($this->dbCharSet,$this->tlCharSet,$summary);
				$link .= " : " . $summary;

			}
		}

		$link .= "</a>";

		return $link;
	}

	/**
	* checks if bug id is present on BTS
	* Function has to be overloaded on child classes
	*
	* @return bool
	**/
	function checkBugID_existence($id)
	{
		// BUGID 3256, BUGID 3098
		return true;
	}
}

// -----------------------------------------------------------------------------------
// DONT TOUCH ANYTHING BELOW THIS NOTICE!
// -----------------------------------------------------------------------------------
$g_bugInterfaceOn = false;
$g_bugInterface = null;
$bts_type = config_get('interface_bugs');
if (isset($bts[$bts_type]))
{
	$btsname = strtolower($bts_type);
	$configPHP = $btsname . '.cfg.php';
	$interfacePHP = 'int_' . $btsname . '.php';

	require_once(TL_ABS_PATH . 'cfg/'. $configPHP);
	require_once(TL_ABS_PATH . 'lib/bugtracking/'. $interfacePHP);

	$g_bugInterfaceName = BUG_INTERFACE_CLASSNAME;
	$g_bugInterface = new $g_bugInterfaceName();
	if ($g_bugInterface)
	{
		$g_bugInterface->connect();
	}
	
	// Important: connect() do log if something fails
	$g_bugInterfaceOn = ($g_bugInterface && $g_bugInterface->isConnected());
}
else if ($bts_type != 'NO') {
    $errorMsg = sprintf(lang_get('BTS_integration_failure'),$bts_type);
    tLog($errorMsg, 'ERROR');
    die($errorMsg);
}
?>
