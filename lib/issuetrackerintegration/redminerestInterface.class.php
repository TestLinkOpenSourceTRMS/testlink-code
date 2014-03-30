<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * @filesource	redminerestInterface.class.php
 * @author Francisco Mancardi
 *
 *
 * @internal revisions
 * @since 1.9.8
 * 20130805 - franciscom - canCreateViaAPI()
 *
**/
require_once(TL_ABS_PATH . "/third_party/redmine-php-api/lib/redmine-rest-api.php");
class redminerestInterface extends issueTrackerInterface
{
  private $APIClient;
  private $issueDefaults;
  private $issueAttr = null;

	var $defaultResolvedStatus;

	/**
	 * Construct and connect to BTS.
	 *
	 * @param str $type (see tlIssueTracker.class.php $systems property)
	 * @param xml $cfg
	 **/
	function __construct($type,$config,$name)
	{
    $this->name = $name;
		$this->interfaceViaDB = false;
		$this->methodOpt['buildViewBugLink'] = array('addSummary' => true, 'colorByStatus' => false);

    $this->defaultResolvedStatus = array();
    $this->defaultResolvedStatus[] = array('code' => 3, 'verbose' => 'resolved');
    $this->defaultResolvedStatus[] = array('code' => 5, 'verbose' => 'closed');
		
	  if( !$this->setCfg($config) )
    {
      return false;
    }  
    
		$this->completeCfg();
		$this->setResolvedStatusCfg();
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
      // $this->cfg->uriview = $base . 'issues/show/'; // seems this is good only for redmine 1
      // $this->cfg->uriview = $base . 'issues/show/'; // seems this is good only for redmine 2
      $this->cfg->uriview = $base . 'issues/'; // seems this is good only for redmine 1
		}
	    
	  if( !property_exists($this->cfg,'uricreate') )
	  {
      $this->cfg->uricreate = $base;
		}	    

    if( property_exists($this->cfg,'attributes') )
    {
      $attr = get_object_vars($this->cfg->attributes);
      foreach ($attr as $name => $elem) 
      {
        $name = (string)$name;
        if( is_object($elem) )
        {
           $elem = get_object_vars($elem);
           $cc = current($elem);
           $kk = key($elem); 
           foreach($cc as $value)
           {
              $this->issueAttr[$name][] = array($kk => (string)$value); 
           }
        } 
        else
        {
          $this->issueAttr[$name] = (string)$elem;     
        } 
      }
    }     


		$this->issueDefaults = array('trackerid' => 1);
    
    /*
    foreach($this->issueDefaults as $prop => $default)
    {
  	  $this->cfg->$prop = (string)(property_exists($this->cfg,$prop) ? $this->cfg->$prop : $default);
    }		
    */

    foreach($this->issueDefaults as $prop => $default)
    {
      if(!isset($this->issueAttr[$prop]))
      {
        $this->issueAttr[$prop] = $default;
      }  
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
    $processCatch = false;

    try
    {
  	  // CRITIC NOTICE for developers
  	  // $this->cfg is a simpleXML Object, then seems very conservative and safe
  	  // to cast properties BEFORE using it.
  	  $this->APIClient = new redmine((string)trim($this->cfg->uribase),(string)trim($this->cfg->apikey));

      // to undestand if connection is OK, I will ask for projects.
      // I've tried to ask for users but get always ERROR from redmine (not able to understand why).
      try
      {
        $items = $this->APIClient->getProjects();
        $this->connected = !is_null($items);
        unset($items);
      }
      catch(Exception $e)
      {
        $processCatch = true;
      }
    }
  	catch(Exception $e)
  	{
  	  $processCatch = true;
  	}
  	
  	if($processCatch)
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
				                               
				$issue->isResolved = isset($this->resolvedStatus->byCode[$issue->statusCode]); 
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

  /**
   *
   */
  public function addIssue($summary,$description)
  {
    // From Redmine API documentation (@20130406)
    // Parameters:
    //
    // issue - A hash of the issue attributes:
    // project_id
    // tracker_id
    // status_id
    // subject
    // description
    // category_id
    // assigned_to_id - ID of the user to assign the issue to (currently no mechanism to assign by name)
    // parent_issue_id - ID of the parent issue
    // custom_fields - See Custom fields
    // watcher_user_ids - Array of user ids to add as watchers (since 2.3.0)

  	  // Check mandatory info
  	  if( !property_exists($this->cfg,'projectidentifier') )
  	  {
  	    throw new exception(__METHOD__ . " project identifier is MANDATORY");
  	  }
  	  
     try
     {
       // needs json or xml
      $issueXmlObj = new SimpleXMLElement('<?xml version="1.0"?><issue></issue>');

      // according with user report is better to use htmlspecialchars
      // 5703: Create issue with Portuguese characters produces mangled text
      //
   		// $issueXmlObj->addChild('subject', htmlentities($summary));
   		// $issueXmlObj->addChild('description', htmlentities($description));

      // limit size to redmine max => 255 ?
      $issueXmlObj->addChild('subject', substr(htmlspecialchars($summary),0,255) );
      $issueXmlObj->addChild('description', htmlspecialchars($description));

   		$issueXmlObj->addChild('project_id', (string)$this->cfg->projectidentifier);
   		$issueXmlObj->addChild('tracker_id', (string)$this->cfg->trackerid);

      // http://www.redmine.org/issues/6843
      // "Target version" is the new display name for this property, 
      // but it's still named fixed_version internally and thus in the API.
      // $issueXmlObj->addChild('fixed_version_id', (string)2);

      if(!is_null($this->issueAttr))
      {
        foreach($this->issueAttr as $ka => $kv)
        {
          // will treat everything as simple strings
          $issueXmlObj->addChild($ka, (string)$kv);
        }  
      }  
      $op = $this->APIClient->addIssueFromSimpleXML($issueXmlObj);
      $ret = array('status_ok' => true, 'id' => (string)$op->id, 
                   'msg' => sprintf(lang_get('redmine_bug_created'),$summary,$issueXmlObj->project_id));
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
    $template = "<!-- Template " . __CLASS__ . " -->\n" .
				        "<issuetracker>\n" .
				        "<apikey>REDMINE API KEY</apikey>\n" .
				        "<uribase>http://tl.m.remine.org</uribase>\n" .
                "<uriview>http://tl.m.remine.org/issues/</uriview> <!-- for Redmine 1.x add show/ --> \n" .
				        "<!-- Project Identifier is NEEDED ONLY if you want to create issues from TL -->\n" . 
				        "<projectidentifier>REDMINE PROJECT IDENTIFIER</projectidentifier>\n" .
                "<!-- Configure This if you need to provide other attributes -->\n" .
                "<!-- <attributes><targetversion>10100<targetversion></attributes>  -->\n" .
	              "<!-- Configure This if you want NON STANDARD BEHAIVOUR for considered issue resolved -->\n" .
                "<resolvedstatus>\n" .
                "<status><code>3</code><verbose>Resolved</verbose></status>\n" .
                "<status><code>5</code><verbose>Closed</verbose></status>\n" .
                "</resolvedstatus>\n" .
				        "</issuetracker>\n";
	  return $template;
  }

 /**
  *
  **/
  function canCreateViaAPI()
  {
    return (property_exists($this->cfg, 'projectidentifier'));
  }


}
