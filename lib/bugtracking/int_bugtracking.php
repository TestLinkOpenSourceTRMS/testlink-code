<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: int_bugtracking.php,v $
 *
 * @version $Revision: 1.2 $
 * @modified $Date: 2005/08/16 18:00:53 $
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
**/
//Add new bugtracking interfaces here
//Please use only lowercase file names!
//This holds the configuration file names for the bugtracking interfaces
//located in the cfg diectory
$configFiles = array(
					'BUGZILLA' => 'bugzilla.cfg.php',
					'MANTIS' => 'mantis.cfg.php',
				);
//This holds the interface defintion file names for the bugtracking interfaces
//located in the lib/bugtracking diectory
$interfaceFiles = array(
					'BUGZILLA' => 'int_bugzilla.php',
					'MANTIS' => 'int_mantis.php',
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
	var $m_showBugURL = null;
	
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
	 * @author Andreas Morsing <schlundus@web.de>
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
	 * @author Andreas Morsing <schlundus@web.de>
	 * @since 22.04.2005, 21:05:25
	 **/
	function connect()
	{
		if (is_null($this->m_dbHost) || is_null($this->m_dbUser))
			return false;
		$result	= null;
		$this->m_dbConnection = mysql_connect($this->m_dbHost,$this->m_dbUser,$this->m_dbPass,true); 
		if (!$this->m_dbConnection)
			$this->m_dbConnection = null;
		else
		{
			$result = mysql_select_db($this->m_dbName, $this->m_dbConnection);
			if (!$result)
			{
				mysql_close($this->m_dbConnection);
				$this->m_dbConnection = null;
			}
		}			
		$this->m_bConnected = $result ? 1 : 0;
		
		return $this->m_bConnected;
	}
	/**
	 * this function simply returns the state of the db connection 
	 *
	 * @return bool returns true if the db connection is established, false else
	 *
	 * @version 1.0
	 * @author Andreas Morsing <schlundus@web.de>
	 * @since 22.04.2005, 21:05:25
	 **/
	function isConnected()
	{
		return ($this->m_bConnected && is_resource($this->m_dbConnection)) ? 1 : 0;
	}
	
	/**
	 * this function closes the db connection (if any) 
	 *
	 * @version 1.0
	 * @author Andreas Morsing <schlundus@web.de>
	 * @since 22.04.2005, 21:05:25
	 **/
	function disconnect()
	{
		if (isConnected())
			mysql_close($this->m_dbConnection);
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
	 * @return string returns a complete URL to view the given bug
	 *
	 * @version 1.0
	 * @author Andreas Morsing <schlundus@web.de>
	 * @since 22.04.2005, 21:05:25
	 **/
	function buildViewBugURL($id)
	{
		return null;		
	}
	
	/**
	 * overload this to return the status of the bug with the given id
	 * this function is not directly called by TestLink. 
	 *
	 * @param int id the bug id
	 * 
	 * @return any returns the status of the given bug
	 *
	 * @version 1.0
	 * @author Andreas Morsing <schlundus@web.de>
	 * @since 22.04.2005, 21:05:25
	 **/
	function getBugStatus($id)
	{
		return null;
	}
		
	/**
	 * overload this to return the status in a readable form for the bug with the given id
	 * This function is not directly called by TestLink 
	 *
	 * @param int id the bug id
	 * 
	 * @return any returns the status (in a readable form) of the given bug 
	 *
	 * @version 1.0
	 * @author Andreas Morsing <schlundus@web.de>
	 * @since 22.04.2005, 21:05:25
	 **/
	function getBugStatusString($id)
	{
		return null;
	}
	
	/*
	* 
	* FUNCTIONS CALLED BY TestLink:
	* 
	**/
	
	/**
	 * default implementation for generating a link to the bugtracking page for viewing 
	 * the bug with the given id in a new page
	 *
	 * @param int id the bug id
	 * 
	 * @return string returns a complete URL to view the bug (if found in db)
	 *
	 * @version 1.0
	 * @author Andreas Morsing <schlundus@web.de>
	 * @since 22.04.2005, 21:05:25
	 **/
	function buildViewBugLink($bugID)
	{
		$link = "<a href='" .$this->buildViewBugURL($bugID) . "' target='_blank'>";
		$link .= $this->getBUGStatusString($bugID);
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