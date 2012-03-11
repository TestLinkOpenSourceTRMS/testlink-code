<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 *
 * @filesource	issueTrackerInterface.php
 *
 * Base class for connection to additional issue tracking interfaces
 *
 * For supporting a bug tracking system this class has to be extended
 * All bug tracking customization should be done in a sub class of this. 
 *
 *
 * @internal revisions
 * @since 1.9.4
 * 20120220 - franciscom - TICKET 4904: integrate with ITS on test project basis 
**/
require_once(TL_ABS_PATH . "/lib/functions/database.class.php");

abstract class issueTrackerInterface
{
	// members to store the bugtracking information.
	// Values are set in the actual subclasses
	var $cfg = null;  // simpleXML object
	var $tlCharSet = null;

	// private vars don't touch
	var $dbConnection = null;
	var $connected = false;
	var $dbMsg = '';


	// Force Extending class to define this method
	abstract public static function getCfgTemplate();

	/**
	 *
	 **/
	function __construct($type,$config)
	{
	    $this->tlCharSet = config_get('charset');
		
		$xmlCfg = "<?xml version='1.0'?> " . $config;
		libxml_use_internal_errors(true);
		$this->cfg = simplexml_load_string($xmlCfg);
		if (!$this->cfg) 
		{
    		echo "Failed loading XML STRING\n";
    		foreach(libxml_get_errors() as $error) 
    		{
        		echo "\t", $error->message;
    		}
		}

		if( !property_exists($this->cfg,'dbcharset') )
		{
			$this->cfg->dbcharset = $this->tlCharSet;
	 	}

		$this->cfg->interfacePHP = strtolower('int_' . $type . '.php');
	    $this->connect();
	}

	/**
	 *
	 **/
	function getCfg()
	{
		return $this->cfg;
	}
	
	/**
	 * this function establishes the database connection to the
	 * bugtracking system
	 *
	 * @return bool returns true if the db connection was established and the
	 * db could be selected, false else
	 *
	 **/
	function connect()
	{
		if (is_null($this->cfg->dbhost) || is_null($this->cfg->dbuser))
		{
			return false;
		}
       
        $this->cfg->dbtype = strtolower((string)$this->cfg->dbtype);
		$this->dbConnection = new database($this->cfg->dbtype);
		$result = $this->dbConnection->connect(false, $this->cfg->dbhost,$this->cfg->dbuser,
											   $this->cfg->dbpassword, $this->cfg->dbname);

		if (!$result['status'])
		{
			$this->dbConnection = null;
			$connection_args = "(interface: - Host:$this->cfg->dbhost - " . 
							   "DBName: $this->cfg->dbname - User: $this->cfg->dbuser) "; 
			$msg = sprintf(lang_get('BTS_connect_to_database_fails'),$connection_args);
			tLog($msg  . $result['dbms_msg'], 'ERROR');
		}
		elseif ($this->cfg->dbtype == 'mysql')
		{
			if ($this->cfg->dbcharset == 'UTF-8')
			{
				$r = $this->dbConnection->exec_query("SET CHARACTER SET utf8");
				$r = $this->dbConnection->exec_query("SET NAMES utf8");
				$r = $this->dbConnection->exec_query("SET collation_connection = 'utf8_general_ci'");
			}
			else
			{
				$r = $this->dbConnection->exec_query("SET CHARACTER SET " . $this->cfg->dbcharset);
				$r = $this->dbConnection->exec_query("SET NAMES ". $this->cfg->dbcharset);
			}
		}

		$this->connected = $result['status'] ? true : false;

		return $this->connected;
	}
	
	/**
	 * State of the db connection
	 *
	 * @return bool returns true if the db connection is established, false else
	 *
	 **/
	function isConnected()
	{
		return ($this->connected && is_object($this->dbConnection)) ? 1 : 0;
	}

	/**
	 * Closes the db connection (if any)
	 *
	 **/
	function disconnect()
	{
		if (isConnected())
		{
			$this->dbConnection->close();
		}
		$this->connected = false;
		$this->dbConnection = null;
	}



	/**
	 * checks a bug id for validity, that means numeric only
	 *
	 * @return bool returns true if the bugid has the right format, false else
	 **/
	function checkBugIDSyntax($id)
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
	 * @param boolean addSummary  default false, true => add issue summary on HREF text
	 *
	 * @return string returns a complete HTML HREF to view the bug (if found in db)
	 *
	 **/
	function buildViewBugLink($bugID, $addSummary = false)
	{

		$link = "<a href='" . $this->buildViewBugURL($bugID) . "' target='_blank'>";
		$status = $this->getBugStatusString($bugID);
		
		if (!is_null($status))
		{
			$status = iconv($this->cfg->dbcharset,$this->tlCharSet,$status);
			$link .= $status;
		}
		else
		{
			$link .= $bugID;
		}

		if ($addSummary)
		{
			$summary = $this->getBugSummaryString($bugID);
			if (!is_null($summary))
			{
				$summary = iconv($this->cfg->dbcharset,$this->tlCharSet,$summary);
				$link .= " : " . $summary;
			}
		}
		$link .= "</a>";
		return $link;
	}

	/**
	 * returns the URL which should be displayed for entering bugs
	 *
	 * @return string returns a complete URL
	 *
	 **/
	function getEnterBugURL()
	{
		return $this->cfg->uricreate;
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
	 **/
	function buildViewBugURL($id)
	{
		return '';
	}
	
	/**
	 * overload this to return the status of the bug with the given id
	 * this function is not directly called by TestLink.
	 *
	 * @param int id the bug id
	 *
	 * @return any returns the status of the given bug, or false if the bug
	 *			was not found
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
	 **/
	function getBugStatusString($id)
	{
		return '';
	}


	/**
	 * default implementation for fetching the bug summary from the
	 * bugtracking system
	 *
	 * @param int id the bug id
	 *
	 * @return string returns the bug summary (if bug is found), or ''
	 *
	 **/
	function getBugSummaryString($id)
	{
		return '';
	}

   /**
	* checks if bug id is present on BTS
	* Function has to be overloaded on child classes
	*
	* @return bool
	**/
	function checkBugIDExistence($id)
	{
		return true;
	}
}
?>