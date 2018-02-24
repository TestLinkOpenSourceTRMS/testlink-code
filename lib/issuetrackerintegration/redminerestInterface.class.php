<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * @filesource	redminerestInterface.class.php
 * @author Francisco Mancardi
 *
 *
**/
require_once(TL_ABS_PATH . "/third_party/redmine-php-api/lib/redmine-rest-api.php");
class redminerestInterface extends issueTrackerInterface
{
  private $APIClient;
  private $issueDefaults;
  private $issueOtherAttr = null; // see 
  private $translate = null;

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

    // http://www.redmine.org/issues/6843
    // "Target version" is the new display name for this property, 
    // but it's still named fixed_version internally and thus in the API.
    // $issueXmlObj->addChild('fixed_version_id', (string)2);
    $this->translate['targetversion'] = 'fixed_version_id';

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
      // seems this is good only for redmine 1 and 2 ??
      // $this->cfg->uriview = $base . 'issues/show/'; 
      $this->cfg->uriview = $base . 'issues/'; 
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
              $this->issueOtherAttr[$name][] = array($kk => (string)$value); 
           }
        } 
        else
        {
          $this->issueOtherAttr[$name] = (string)$elem;     
        } 
      }
    }     

    // All attributes that I do not consider mandatory 
    // are managed through the issueAdditionalAttributes
    //
    // On Redmine 1 seems to be standard for Issues/Bugs
		$this->issueDefaults = array('trackerid' => 1); 
    foreach($this->issueDefaults as $prop => $default)
    {
      if(!isset($this->issueAttr[$prop]))
      {
        $this->issueAttr[$prop] = $default;
      } 
    }   
    
    if( property_exists($this->cfg,'custom_fields') )
    {
      libxml_use_internal_errors(true);
      $xcfg = simplexml_load_string($this->xmlCfg);
      $this->cfg->custom_fields = (string)$xcfg->custom_fields->asXML();
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
      $redUrl = (string)trim($this->cfg->uribase);
      $redAK = (string)trim($this->cfg->apikey);
      $pxy = new stdClass();
      $pxy->proxy = config_get('proxy');
  	  $this->APIClient = new redmine($redUrl,$redAK,$pxy);

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
   * From Redmine API documentation (@20130406)
   * Parameters:
   *
   * issue - A hash of the issue attributes:
   * - subject
   * - description
   * - project_id
   * - tracker_id
   * - status_id
   * - category_id
   * - fixed_version_id - see http://www.redmine.org/issues/6843
   * - assigned_to_id   - ID of the user to assign the issue to (currently no mechanism to assign by name)
   * - parent_issue_id  - ID of the parent issue  <= aslo know as Parent Task
   * - custom_fields    - See Custom fields
   * - watcher_user_ids - Array of user ids to add as watchers (since 2.3.0)
   */
  public function addIssue($summary,$description,$opt=null)
  {
    $reporter = null;
    if(!is_null($opt) && property_exists($opt, 'reporter')) {
      $reporter = $opt->reporter;
    }  


  	// Check mandatory info
  	if( !property_exists($this->cfg,'projectidentifier') ) {
  	  throw new exception(__METHOD__ . " project identifier is MANDATORY");
  	}
  	  
    try {
       // needs json or xml
      $issueXmlObj = new SimpleXMLElement('<?xml version="1.0"?><issue></issue>');

      // according with user report is better to use htmlspecialchars
      // TICKET 5703: Create issue with Portuguese characters produces mangled text
      //
   		// $issueXmlObj->addChild('subject', htmlentities($summary));
   		// $issueXmlObj->addChild('description', htmlentities($description));

      // limit size to redmine max => 255 ?
      $issueXmlObj->addChild('subject', substr(htmlspecialchars($summary),0,255) );
      $issueXmlObj->addChild('description', htmlspecialchars($description));

      // Got from XML Configuration
      // improvement
      $pid = (string)$this->cfg->projectidentifier;
      if(is_string($pid)) {
        $pinfo = $this->APIClient->getProjectByIdentity($pid);
        if(!is_null($pinfo)) {
          $pid = (int)$pinfo->id;
        }  
      }  
   		$issueXmlObj->addChild('project_id', (string)$pid);

      if( property_exists($this->cfg,'trackerid') ) {
        $issueXmlObj->addChild('tracker_id', (string)$this->cfg->trackerid);
      } 

      // try to be generic
      if( property_exists($this->cfg,'parent_issue_id') ) {
        $issueXmlObj->addChild('parent_issue_id', (string)$this->cfg->parent_issue_id);
      } 


      // Why issuesAttr is issue ?
      // Idea was 
      // on XML config on TestLink provide direct access to a minimun set of MANDATORY
      // attributes => without it issue can not be created.
      // After first development/release of this feature people that knows better
      // Redmine start asking for other attributes.
      // Then to manage this other set of unknown attributes in a generic way idea was
      // loop over an object property and blidly add it to request.
      //
      // Drawback/limitations
      // I can not manage type (because I do not request this info) => will treat always as STRING 
      //
      // * Special case Target Version
      // http://www.redmine.org/issues/6843
      // "Target version" is the new display name for this property, 
      // but it's still named fixed_version internally and thus in the API.
      // $issueXmlObj->addChild('fixed_version_id', (string)2);
      // 
      if(!is_null($this->issueOtherAttr)) {
        foreach($this->issueOtherAttr as $ka => $kv) {
          // will treat everything as simple strings or can I check type
          // see completeCfg()
          $issueXmlObj->addChild((isset($this->translate[$ka]) ? $this->translate[$ka] : $ka), (string)$kv);
        }  
      }  

      // In order to manage custom fields in simple way, 
      // it seems that is better create here plain XML String
      //
      $xml = $issueXmlObj->asXML();
      if( property_exists($this->cfg,'custom_fields') ) {
        $cf = (string)$this->cfg->custom_fields;

        // -- 
        // Management of Dynamic Values From XML Configuration 
        $safeVal = array();
        foreach($opt->tagValue->value as $val) {
          array_push($safeVal, htmlentities($val, ENT_XML1));
        }
        $cf = str_replace($opt->tagValue->tag,$safeVal,$cf);
        // --

        $xml = str_replace('</issue>', $cf . '</issue>', $xml);
      }

      // $op = $this->APIClient->addIssueFromSimpleXML($issueXmlObj);
      file_put_contents('/var/testlink/' . __CLASS__ . '.log', $xml);
      $op = $this->APIClient->addIssueFromXMLString($xml,$reporter);

      
      if(is_null($op)) {
        $msg = "Error Calling " . __CLASS__ . 
               "->APIClient->addIssueFromXMLString() " .
               " check Communication TimeOut ";
        throw new Exception($msg, 1);
      }  

      $ret = array('status_ok' => true, 'id' => (string)$op->id, 
                   'msg' => sprintf(lang_get('redmine_bug_created'),
                    $summary,$pid));
     }
     catch (Exception $e) {
       $msg = "Create REDMINE Ticket FAILURE => " . $e->getMessage();
       tLog($msg, 'WARNING');
       $ret = array('status_ok' => false, 'id' => -1, 'msg' => $msg . ' - serialized issue:' . serialize($xml));
     }
     return $ret;
  }  


  /**
   *
   */
  public function addNote($issueID,$noteText,$opt=null)
  {
    try
    {
       // needs json or xml
      $issueXmlObj = new SimpleXMLElement('<?xml version="1.0"?><issue></issue>');
      $issueXmlObj->addChild('notes', htmlspecialchars($noteText));

      $reporter = null;
      if(!is_null($opt) && property_exists($opt, 'reporter'))
      {
        $reporter = $opt->reporter;
      }  
      $op = $this->APIClient->addIssueNoteFromSimpleXML($issueID,$issueXmlObj,$reporter);
      $ret = array('status_ok' => true, 'id' => (string)$op->id, 
                   'msg' => sprintf(lang_get('redmine_bug_created'),$summary,$issueXmlObj->project_id));
     }
     catch (Exception $e)
     {
       $msg = "REDMINE Add Note to Ticket FAILURE => " . $e->getMessage();
       tLog($msg, 'WARNING');
       $ret = array('status_ok' => false, 'id' => -1, 'msg' => $msg . ' - serialized issue:' . serialize($issueXmlObj));
     }
     return $ret;
  }  




  /**
   *
   * @author francisco.mancardi@gmail.com>
   **/
	public static function getCfgTemplate()
  {
    $tpl = "<!-- Template " . __CLASS__ . " -->\n" .
				   "<issuetracker>\n" .
				   "<apikey>REDMINE API KEY</apikey>\n" .
				   "<uribase>http://tl.m.remine.org</uribase>\n" .
           "<uriview>http://tl.m.remine.org/issues/</uriview> <!-- for Redmine 1.x add show/ --> \n" .
				   "<!-- Project Identifier is NEEDED ONLY if you want to create issues from TL -->\n" . 
				   "<projectidentifier>REDMINE PROJECT IDENTIFIER\n" .
           " You can use numeric id or identifier string \n" .
           "</projectidentifier>\n" .
           "\n" .
           "<!--                                       -->\n" .
           "<!-- Configure This if you need to provide other attributes, ATTENTION to REDMINE API Docum. -->\n" .
           "<!-- <attributes> -->\n" .
           "<!--   <targetversion>10100</targetversion>\n" .
           "<!--   <parent_issue_id>10100</parent_issue_id>\n" .
           "<!-- </attributes>  -->\n" .
           "<!--                                       -->\n" .
           "<!-- Custom Fields-->\n" .
           "<!-- Check Redmine API Docs for format -->\n" .
           '<!-- <custom_fields type="array"> -->' . "\n" .
           '<!-- <custom_field id="1" name="CF-STRING-OPT"> -->' . "\n" .
           '<!--   <value>SALT</value> -->' . "\n" .
           '<!-- </custom_field> -->' . "\n" .
           '<!-- <custom_field id="3" name="CF-LIST-OPT" multiple="true"> -->' . "\n" .
           '<!--   <value type="array"> -->' . "\n" .
           '<!--     <value>ALFA</value> -->' . "\n" .
           '<!--   </value> -->' . "\n" .
           '<!-- </custom_field> -->' . "\n" .
           '<!-- </custom_fields> -->' . "\n" .
	         "<!-- Configure This if you want NON STANDARD BEHAIVOUR for considered issue resolved -->\n" .
           "<!--  <resolvedstatus>-->\n" .
           "<!--    <status><code>3</code><verbose>Resolved</verbose></status> -->\n" .
           "<!--    <status><code>5</code><verbose>Closed</verbose></status> -->\n" .
           "<!--  </resolvedstatus> -->\n" .
				   "</issuetracker>\n";
	  return $tpl;
  }

 /**
  *
  **/
  function canCreateViaAPI()
  {
    return (property_exists($this->cfg, 'projectidentifier'));
  }


}
