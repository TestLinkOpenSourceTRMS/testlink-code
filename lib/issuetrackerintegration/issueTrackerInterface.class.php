<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 *
 * @filesource	issueTrackerInterface.php
 *
 * Base class for connection to issue tracking interfaces
 * For supporting a bug/issue tracking system this class has to be extended,
 * and all customization should be done in the subclass. 
 *
 * ============= Issue Entity properties on TestLink Context ===================
 *
 * IDHTMLString = string
 * statusCode = can be integer,string depending of ITS
 * statusVerbose = string human readable what user see on ITS GUI
 * statusHTMLString = string can contain additional info ready for HTML
 * summary = string what user see on ITS GUI
 * summaryHTMLString = can contain additional info ready for HTML
 *
 * other properties can be present depending on ITS.
 * =============================================================================
 *
 * @internal revisions
 * @since 1.9.5
**/
require_once(TL_ABS_PATH . "/lib/functions/database.class.php");
require_once(TL_ABS_PATH . "/lib/functions/lang_api.php");

abstract class issueTrackerInterface
{
	// members to store the bugtracking information.
	// Values are set in the actual subclasses
	var $cfg = null;  // simpleXML object
	var $tlCharSet = null;

	// private vars don't touch
	var $dbConnection = null;  // usable only if interface is done via direct DB access.
	var $dbMsg = '';
	var $connected = false;
	var $interfaceViaDB = false;  // useful for connect/disconnect methods

	var $methodOpt = array('buildViewBugLink' => array('addSummary' => false, 'colorByStatus' => false));
	
	/**
	 * Construct and connect to BTS.
	 * Can be overloaded in specialized class
	 *
	 * @param str $type (see tlIssueTracker.class.php $systems property)
	 **/
	function __construct($type,$config)
	{
	  $this->tlCharSet = config_get('charset');
		
		if( $this->setCfg($config) )
    {
  		// useful only for integration via DB
  		if( !property_exists($this->cfg,'dbcharset') )
  		{
  			$this->cfg->dbcharset = $this->tlCharSet;
  	 	}
  	  $this->connect();
	  }
	  else
	  {
	    $this->connected = false;
	  }
	}

	/**
	 *
	 **/
	function getCfg()
	{
		return $this->cfg;
	}

	/**
	 *
	 **/
	function setCfg($xmlString)
	{
	  $msg = null;
	  $signature = 'Source:' . __METHOD__;

		$xmlCfg = "<?xml version='1.0'?> " . $xmlString;
		libxml_use_internal_errors(true);
		try 
		{
  		$this->cfg = simplexml_load_string($xmlCfg);
  		if (!$this->cfg) 
  		{
  		  $msg = $signature . " - Failure loading XML STRING\n";
      	foreach(libxml_get_errors() as $error) 
      	{
       		$msg .= "\t" . $error->message;
      	}
  		}
    }
    catch(Exception $e)
    {
      $msg = $signature . " - Exception loading XML STRING\n";
      $msg .= 'Message: ' .$e->getMessage();
    }
    
    return is_null($msg);
	}

	/**
	 *
	 **/
	function getMyInterface()
  	{
		return $this->cfg->interfacePHP;
  	}

	/**
	 * return the maximum length in chars of a issue id
	 * used on TestLink GUI
	 *
	 * @return int the maximum length of a bugID
	 */
	function getBugIDMaxLength()
	{
		// CRITIC: related to execution_bugs table, you can not make it
		//		   greater WITHOUT changing table structure.	
		// 
		return 16;  
	}

	
	/**
	 * establishes the database connection to the bugtracking system
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
	 * State of connection to BTS
	 *
	 * @return bool returns true if connection with BTS is established, false else
	 *
	 **/
	function isConnected()
	{
	
		return ($this->connected && 
				((!$this->interfaceViaDB ) || is_object($this->dbConnection)) ? 1 : 0);
	}

	/**
	 * Closes the db connection (if any)
	 *
	 **/
	function disconnect()
	{
		if ($this->isConnected() && $this->interfaceViaDB)
		{
			$this->dbConnection->close();
		}
		$this->connected = false;
		$this->dbConnection = null;
	}



	/**
	 * checks a issue id for validity (NUMERIC)
	 *
	 * @return bool returns true if the bugid has the right format, false else
	 **/
	function checkBugIDSyntaxNumeric($issueID)
	{
		$valid = true;	
	  	$forbidden_chars = '/\D/i';  
		if (preg_match($forbidden_chars, $issueID))
    	{
			$valid = false;	
    	}
		else 
    	{
	    	$valid = (intval($issueID) > 0);	
    	}

      	return $valid;
	}

    /**
     * checks id for validity (STRING)
     *
	 * @param string issueID
     *
     * @return bool returns true if the bugid has the right format, false else
     **/
    function checkBugIDSyntaxString($issueID)
    {
        $status_ok = !(trim($issueID) == "");
        if($status_ok)
        {
            $forbidden_chars = '/[!|�%&()\/=?]/';
            if (preg_match($forbidden_chars, $issueID))
            {
                $status_ok = false;
            }
        }
        return $status_ok;
    }


	/**
	 * default implementation for generating a link to the bugtracking page for viewing
	 * the bug with the given id in a new page
	 *
	 * @param int id the bug id
	 *
	 * @return string returns a complete HTML HREF to view the bug (if found in db)
	 *
	 **/
	function buildViewBugLink($issueID, $opt=null)
	{
		// $my['opt'] = array('addSummary' => false, 'colorByStatus' => false);
		$my['opt'] = $this->methodOpt[__FUNCTION__];
		$my['opt'] = array_merge($my['opt'],(array)$opt);
		
		$link = "<a href='" . $this->buildViewBugURL($issueID) . "' target='_blank'>";

		$issue = $this->getIssue($issueID);
		
		if( is_null($issue) || !is_object($issue) )
		{
			$link = '';
			return $link;
		}
		
		$useIconv = property_exists($this->cfg,'dbcharset');
		if($useIconv)
		{
				$link .= iconv((string)$this->cfg->dbcharset,$this->tlCharSet,$issue->IDHTMLString);
		}
		else
		{
				$link .= $issue->IDHTMLString;
		}
		
		if (!is_null($issue->statusHTMLString))
		{
			if($useIconv)
			{
				$link .= iconv((string)$this->cfg->dbcharset,$this->tlCharSet,$issue->statusHTMLString);
			}
			else
			{
				$link .= $issue->statusHTMLString;
			}
		}
		else
		{
			$link .= $issueID;
		}

		if($my['opt']['addSummary'])
		{
			if (!is_null($issue->summaryHTMLString))
			{
				$link .= " : ";
				if($useIconv)
				{
					$link .= iconv((string)$this->cfg->dbcharset,$this->tlCharSet,$issue->summaryHTMLString);
				}
				else
				{
					$link .= $issue->summaryHTMLString;
				}
			}
		}
		$link .= "</a>";

		if($my['opt']['colorByStatus'] && property_exists($issue,'statusColor') )
		{
      		$title = lang_get('access_to_bts');  
      		$link = "<div  title=\"{$title}\" style=\"display: inline; background: $issue->statusColor;\">$link</div>";
		}
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
	 * Returns URL to the bugtracking page for viewing ticket
	 *
	 * @param mixed issueID 
	 *				depending of BTS issueID can be a number (e.g. Mantis)
	 *				or a string (e.g. JIRA)
	 * 
	 * @return string 
	 **/
	function buildViewBugURL($issueID)
	{
		return $this->cfg->uriview . urlencode($issueID);
	}

	
	/**
	 * overload this to return the status of the bug with the given id
	 * this function is not directly called by TestLink.
	 *
	 * @param mixed issueID
	 *
	 * @return any returns the status of the given bug, or false if the bug
	 *			was not found
	 **/
	public function getIssueStatusCode($issueID)
	{
		return false;
	}

	/**
	 * overload this to return the status in a readable form for the bug with the given id
	 * This function is not directly called by TestLink
	 *
	 * @param mixed issueID
	 *
	 * @return any returns the status (in a readable form) of the given bug, or false
	 * 			if the bug is not found
	 *
	 **/
	function getIssueStatusVerbose($issueID)
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
	function getIssueSummaryString($issueID)
	{
		return '';
	}

   /**
	* checks if bug id is present on BTS
	* Function has to be overloaded on child classes
	*
	* @return bool
	**/
	function checkBugIDExistence($issueID)
	{
        throw new RuntimeException(__METHOD__ . "Not implemented - YOU must implement it in YOUR interface Class");
	}


	// How to Force Extending class to define this STATIC method ?
	// KO abstract public static function getCfgTemplate();
	public static function getCfgTemplate() 
	{
        throw new RuntimeException("Unimplemented - YOU must implement it in YOUR interface Class");
  }

  public static function checkEnv()
  {
    $ret = array();
    $ret['status'] = true;
    $ret['msg'] = 'OK';
    return $ret;
  }
  
}
?>