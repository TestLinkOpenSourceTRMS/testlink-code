<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * @filesource	redminerestInterface.class.php
 * @author Francisco Mancardi
 *
 *
 * @internal revisions
 * @since 1.9.4
 * 20120324 - franciscom - TICKET 4904: integrate with ITS on test project basis 
**/
require_once(TL_ABS_PATH . "/third_party/redmine-php-api/lib/redmine-rest-api.php");
class redminerestInterface extends issueTrackerInterface
{
  private $APIClient;
  private $issueDefaults;

	/**
	 * Construct and connect to BTS.
	 *
	 * @param str $type (see tlIssueTracker.class.php $systems property)
	 * @param xml $cfg
	 **/
	function __construct($type,$config)
	{
		$this->interfaceViaDB = false;
		$this->methodOpt['buildViewBugLink'] = array('addSummary' => true, 'colorByStatus' => false);
		
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
      $this->cfg->uriview = $base . 'issues/show/';
		}
	    
	  if( !property_exists($this->cfg,'uricreate') )
	  {
      $this->cfg->uricreate = $base;
		}	    

		$this->issueDefaults = array('trackerid' => 1);
    foreach($this->issueDefaults as $prop => $default)
    {
  	  $this->cfg->$prop = (string)(property_exists($this->cfg,$prop) ? $this->cfg->$prop : $default);
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
			  $this->APIClient = new redmine((string)trim($this->cfg->uribase),(string)trim($this->cfg->apikey));
	      $this->connected = true;
      }
		catch(Exception $e)
		{
			$logDetails = '';
			foreach(array('uribase','apikey') as $v)
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
		
		$issue = null;
		try
		{
			$xmlObj = $this->APIClient->getIssue((int)$issueID);
			if( !is_null($xmlObj) && is_object($xmlObj))
			{
				$issue = new stdClass();
		    $issue->IDHTMLString = "<b>{$issueID} : </b>";
				$issue->statusCode = (string)$xmlObj->status['id'];
				$issue->statusVerbose = (string)$xmlObj->status['name'];;
				$issue->statusHTMLString = "[$issue->statusVerbose] ";
				$issue->summary = $issue->summaryHTMLString = (string)$xmlObj->subject;
				$issue->redmineProject = array('name' => (string)$xmlObj->project['name'], 
				                               'id' => (int)$xmlObj->project['id'] );
			}
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
	 * @param string issueID
     *
     * @return bool true if issue exists on BTS
     **/
    function checkBugIDExistence($issueID)
    {
        if(($status_ok = $this->checkBugIDSyntax($issueID)))
        {
            $issue = $this->getIssue($issueID);
            $status_ok = is_object($issue) && !is_null($issue);
        }
        return $status_ok;
    }

    public function addIssue($summary,$description)
  	{
  	  // Check mandatory info
  	  if( !property_exists($this->cfg,'projectidentifier') )
  	  {
  	    throw new exception(__METHOD__ . " project identifier is MANDATORY");
  	  }
  	  
      try
      {
        // needs json or xml
        $issueXmlObj = new SimpleXMLElement('<?xml version="1.0"?><issue></issue>');
    		$issueXmlObj->addChild('subject', htmlentities($summary));
    		$issueXmlObj->addChild('description', htmlentities($description));
    		$issueXmlObj->addChild('project_id', (string)$this->cfg->projectidentifier);
    		$issueXmlObj->addChild('tracker_id', (string)$this->cfg->trackerid);
    		// $issueXmlObj->addChild('priority_id', $priority_id);
    		// $issueXmlObj->addChild('category_id', $category_id);
 
        $op = $this->APIClient->addIssueFromSimpleXML($issueXmlObj);
        $ret = array('status_ok' => true, 'id' => (string)$op->id, 
                     'msg' => sprintf(lang_get('redmine_bug_created'),$summary,
                                      $issueXmlObj->project_id));
      }
      catch (Exception $e)
      {
        $msg = "Create REDMINE Ticket FAILURE => " . $e->getMessage();
        tLog($msg, 'WARNING');
        $ret = array('status_ok' => false, 'id' => -1, 'msg' => $msg . ' - serialized issue:' . serialize($issue));
      }
      return $ret;
  	}  


    /**
     *
     * @author francisco.mancardi@gmail.com>
     **/
	  public static function getCfgTemplate()
  	{
  	
  		// http://tl.m.remine.org
		$template = "<!-- Template " . __CLASS__ . " -->\n" .
					      "<issuetracker>\n" .
					      "<apikey>REDMINE API KEY</apikey>\n" .
					      "<uribase>http://tl.m.remine.org</uribase>\n" .
					      "<!-- Project Identifier is NEEDED ONLY if you want to create issues from TL -->\n" . 
					      "<projectidentifier>REDMINE PROJECT IDENTIFIER</projectidentifier>\n" .
					      "</issuetracker>\n";
		return $template;
  	}
}
?>