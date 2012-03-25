<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * @filesource	fogbugzrestInterface.class.php
 * @author Francisco Mancardi
 *
 * @internal IMPORTANT NOTICE
 *			 we use issueID on methods signature, to make clear that this ID 
 *			 is HOW issue in identified on Issue Tracker System, 
 *			 not how is identified internally at DB	level on TestLink
 *
 *			 Third Party Code: https://github.com/chadhutchins/fogbugz-php-api	
 *
 * @internal revisions
 * @since 1.9.4
 * 20120324 - franciscom - TICKET 4904: integrate with ITS on test project basis 
**/
require_once(TL_ABS_PATH . "/third_party/fogbugz-php-api/lib/api.php");
class fogbugzrestInterface extends issueTrackerInterface
{
    private $APIClient;

	/**
	 * Construct and connect to BTS.
	 *
	 * @param str $type (see tlIssueTracker.class.php $systems property)
	 * @param xml $cfg
	 **/
	function __construct($type,$config)
	{
		$this->interfaceViaDB = false;
	    $this->setCfg($config);
		$this->completeCfg();
	    $this->connect();
	}


	/**
	 *
	 * check for configuration attributes than can be provided on
	 * user configuration, but that can be considered standard.
	 * If they are MISSING we will use 'these carved on the stone values' 
	 * in order	to simplify configuration.
	 * 
	 *
	 **/
	function completeCfg()
	{
		$base = trim($this->cfg->uribase,"/") . '/'; // be sure no double // at end
	    if( !property_exists($this->cfg,'uriview') )
	    {
	    	$this->cfg->uriview = $base . 'default.asp?command=view&pg=pgEditBug&ixbug=';
		}
	    
	    if( !property_exists($this->cfg,'uricreate') )
	    {
	    	$this->cfg->uricreate = $base . 'default.asp?command=new&pg=pgEditBug';
		}	    
	}

	/**
     * useful for testing 
     *
     *
     **/
	function getAPIClient()
	{
		return $this->APIClient;
	}

    /**
     * checks id for validity
     *
	 * @param string issueID
     *
     * @return bool returns true if the bugid has the right format, false else
     **/
    function checkBugIDSyntax($issueID)
    {
        return $this->checkBugIDSyntaxNumeric($issueID);
    }

    /**
     * establishes connection to the bugtracking system
     *
     * @return bool 
     *
     **/
    function connect()
    {
		try
		{
			// CRITIC NOTICE for developers
			// $this->cfg is a simpleXML Object, then seems very conservative and safe
			// to cast properties BEFORE using it.
			$this->APIClient = new FogBugz((string)trim($this->cfg->username),(string)trim($this->cfg->password),
										   (string)trim($this->cfg->uribase));
	       	$this->APIClient->logon();
	       	$this->connected = true;
        }
		catch(Exception $e)
		{
			$logDetails = '';
			foreach(array('uribase','username','password') as $v)
			{
				$logDetails .= "$v={$this->cfg->$v} / "; 
			}
			$logDetails = trim($logDetails,'/ ');
			$this->connected = false;
            tLog(__METHOD__ . " [$logDetails] " . $e->getMessage(), 'ERROR');
		}
    }

    /**
     * 
     *
     **/
	function isConnected()
	{
		return $this->connected;
	}


    /**
     * 
     *
     **/
	public function getIssue($issueID)
	{
		if (!$this->isConnected())
		{
            tLog(__METHOD__ . '/Not Connected ', 'ERROR');
			return false;
		}
		
		try
		{
			$target = array('q' => intval($issueID), 'cols' => 'sTitle,sStatus');
			$xml = $this->APIClient->search($target);

			$issue = new stdClass();
	        $issue->IDHTMLString = "<b>{$issueID} : </b>";
			$issue->statusCode = (string)$xml->cases->case->sStatus;
			$issue->statusVerbose = $issue->statusCode;
			$issue->statusHTMLString = "[$issue->statusCode] ";
			$issue->summary = $issue->summaryHTMLString = (string)$xml->cases->case->sTitle;
		}
		catch(Exception $e)
		{
			tLog(__METHOD__ . '/' . $e->getMessage(),'ERROR');
			$issue = null;
		}	
		return $issue;		
	}


	/**
	 * Returns status for issueID
	 *
	 * @param string issueID
	 *
	 * @return 
	 **/
	function getIssueStatusCode($issueID)
	{
		$issue = $this->getIssue($issueID);
		return !is_null($issue) ? $issue->statusCode : false;
	}

	/**
	 * Returns status in a readable form (HTML context) for the bug with the given id
	 *
	 * @param string issueID
	 * 
	 * @return string 
	 *
	 **/
	function getIssueStatusVerbose($issueID)
	{
        return $this->getIssueStatusCode($issueID);
	}

	/**
	 *
	 * @param string issueID
	 * 
	 * @return string 
	 *
	 **/
	function getIssueSummaryHTMLString($issueID)
	{
        $issue = $this->getIssue($issueID);
        return $issue->summaryHTMLString;
	}


    /**
     *
     * @author francisco.mancardi@gmail.com>
     **/
	public static function getCfgTemplate()
  	{
  	
  		// https://testlink.fogbugz.com/fogbugz/default.asp?pg=pgEditBug&command=view&ixbug=
		$template = "<!-- Template " . __CLASS__ . " -->\n" .
					"<issuetracker>\n" .
					"<username>FOGBUGZ LOGIN NAME</username>\n" .
					"<password>FOGBUGZ PASSWORD</password>\n" .
					"<uribase>https://testlink.fogbugz.com</uribase>\n" .
					"</issuetracker>\n";
		return $template;
  	}
}
?>