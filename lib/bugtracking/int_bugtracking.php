<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: int_bugtracking.php,v $
 *
 * @version $Revision: 1.8 $
 * @modified $Date: 2005/12/29 20:59:00 $
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
 * 20051229 - scs - added ADOdb support
 *
**/
//Add new bugtracking interfaces here
//Please use only lowercase file names!
//This holds the configuration file names for the bugtracking interfaces
//located in the cfg diectory
require_once(TL_ABS_PATH. "/lib/functions/database.class.php");

$configFiles = array(
					'BUGZILLA' => 'bugzilla.cfg.php',
					'MANTIS' => 'mantis.cfg.php',
					'JIRA' => 'jira.cfg.php',
				);
//This holds the interface defintion file names for the bugtracking interfaces
//located in the lib/bugtracking diectory
$interfaceFiles = array(
					'BUGZILLA' => 'int_bugzilla.php',
					'MANTIS' => 'int_mantis.php',
					'JIRA' => 'int_jira.php',
				);

				
//Set the bug tracking system Interface
class bugtrackingInterface
{
	//members to store the bugtracking information, these values are
	//set in the actual subclasses of this class
	var $m_dbHost = null;
	var $m_dbName = null;
	var $m_dbUser = null;
	var $m_dbPass = null;
	var $m_dbType = null;
	var $m_showBugURL = null;
	var $m_enterBugURL = null;
	
	//private vars don't touch
	var $m_dbConnection = null;	
	var $m_bConnected = false;

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
	}

	/**
	 * this function establishes the database connection to the 
	 * bugtracking system
	 *
	 * @return bool returns true if the db connection was established and the 
	 * db could be selected, false else
	 *
	 * @version 1.0
	 * @author Andreas Morsing 
	 * @since 22.04.2005, 21:05:25
	 **/
	function connect()
	{
		if (is_null($this->m_dbHost) || is_null($this->m_dbUser))
		{
			return false;
		}	
		$this->m_dbConnection = new database($this->m_dbType);
		$result = $this->m_dbConnection->connect(false, $this->m_dbHost,$this->m_dbUser,$this->m_dbPass, $this->m_dbName);
		if (!$result['status'])
			$this->m_dbConnection = null;
			
		$this->m_bConnected = $result ? 1 : 0;

		return $this->m_bConnected;
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
		return ($this->m_bConnected && is_object($this->m_dbConnection)) ? 1 : 0;
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
			$this->m_dbConnection->close();
		}	
		$this->m_bConnected = false;
		$this->m_dbConnection = null;
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
		return $this->m_enterBugURL;
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
			$link .= $status;
		else
			$link .= $bugID;
		if ($bWithSummary)
		{
			$summary = $this->getBugSummaryString($bugID);
			if (!is_null($summary))
			{
				$link .= " - " . $summary;
			}
		}

		$link .= "</a>";
		
		return $link;
	}
}	
				
//DONT TOUCH ANYTHING BELOW THIS NOTICE!				
$g_bugInterfaceOn = false;
$g_bugInterface = null;
if (isset($configFiles[TL_INTERFACE_BUGS]))
{
	require_once(TL_ABS_PATH . 'cfg/'. $configFiles[TL_INTERFACE_BUGS]);
	require_once(TL_ABS_PATH . 'lib/bugtracking/'. $interfaceFiles[TL_INTERFACE_BUGS]);
	$g_bugInterfaceName = BUG_INTERFACE_CLASSNAME;
	$g_bugInterface = new $g_bugInterfaceName();
	if ($g_bugInterface)
		$g_bugInterface->connect();
	$g_bugInterfaceOn = ($g_bugInterface && $g_bugInterface->isConnected());			
}
unset($configFiles);
?>