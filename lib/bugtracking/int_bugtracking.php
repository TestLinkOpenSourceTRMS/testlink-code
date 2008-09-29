<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: int_bugtracking.php,v $
 *
 * @version $Revision: 1.22 $
 * @modified $Date: 2008/09/29 19:48:06 $ $Author: schlundus $
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
 * 20080207 - needles - added notation for Seapine's TestTrackPro
 * 20070505 - franciscom - TL_INTERFACE_BUGS -> $g_interface_bugs
 * 20070304 - franciscom - added new method checkBugID_existence()
 *
 *
**/
//Add new bugtracking interfaces here
//Please use only lowercase file names!
//This holds the configuration file names for the bugtracking interfaces
//located in the cfg directory
require_once(TL_ABS_PATH. "/lib/functions/database.class.php");

$configFiles = array(
					'BUGZILLA' => 'bugzilla.cfg.php',
					'MANTIS' => 'mantis.cfg.php',
					'JIRA' => 'jira.cfg.php',
					'TRACKPLUS' => 'trackplus.cfg.php',
					'EVENTUM' => 'eventum.cfg.php',
					'TRAC' => 'trac.cfg.php',
					'SEAPINE' => 'seapine.cfg.php',
					'REDMINE' => 'redmine.cfg.php'
				);
//This holds the interface defintion file names for the bugtracking interfaces
//located in the lib/bugtracking diectory
$interfaceFiles = array(
					'BUGZILLA' => 'int_bugzilla.php',
					'MANTIS' => 'int_mantis.php',
					'JIRA' => 'int_jira.php',
					'TRACKPLUS' => 'int_trackplus.php',
					'EVENTUM' => 'int_eventum.php',
					'TRAC' => 'int_trac.php',
					'SEAPINE' => 'int_seapine.php',
					'REDMINE' => 'int_redmine.php'
				);

				
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
	
	//private vars don't touch
	var $dbConnection = null;	
	var $bConnected = false;

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
		global $tlCfg;
		$this->dbCharSet = $tlCfg->charset;
		if (defined('BUG_TRACK_DB_CHARSET')) 
 	    	$this->dbCharSet = BUG_TRACK_DB_CHARSET;
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
			$this->dbConnection = null;
			
		$this->bConnected = $result['status'] ? 1 : 0;

		return $this->bConnected;
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
		return ($this->bConnected && is_object($this->dbConnection)) ? 1 : 0;
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
		$this->bConnected = false;
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
	 * checks a bug id for validity  
	 * 
	 * @return bool returns true if the bugid has the right format, false else
	 **/
	function checkBugID($id)
	{
		return (intval($id) > 0);
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
		global $tlCfg;
		$link = "<a href='" .$this->buildViewBugURL($bugID) . "' target='_blank'>";
		
		$status = $this->getBugStatusString($bugID);
		
		if (!is_null($status))
		{
			$status = iconv($this->dbCharSet,$tlCfg->charset,$status);
			$link .= $status;
		}
		else
			$link .= $bugID;
		if ($bWithSummary)
		{
			$summary = $this->getBugSummaryString($bugID);
			if (!is_null($summary))
			{
				$summary = iconv($this->dbCharSet,$tlCfg->charset,$summary);
				$link .= " - " . $summary;
			}
		}

		$link .= "</a>";
		
		return $link;
	}
	
	/**
	* checks is bug id is present on BTS
	* 
	* @return bool 
	**/
	function checkBugID_existence($id)
	{
		return 1;
	}	
}	
				
//DONT TOUCH ANYTHING BELOW THIS NOTICE!				
$g_bugInterfaceOn = false;
$g_bugInterface = null;

global $g_interface_bugs;

if (isset($configFiles[$g_interface_bugs]))
{
	require_once(TL_ABS_PATH . 'cfg/'. $configFiles[$g_interface_bugs]);
	require_once(TL_ABS_PATH . 'lib/bugtracking/'. $interfaceFiles[$g_interface_bugs]);
	$g_bugInterfaceName = BUG_INTERFACE_CLASSNAME;
	$g_bugInterface = new $g_bugInterfaceName();
	if ($g_bugInterface)
		$g_bugInterface->connect();
	$g_bugInterfaceOn = ($g_bugInterface && $g_bugInterface->isConnected());			
}

unset($configFiles);
?>
