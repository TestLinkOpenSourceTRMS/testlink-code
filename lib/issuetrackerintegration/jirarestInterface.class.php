<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * @filesource	jirarestInterface.class.php
 * @author      Francisco Mancardi
 *
 *
 * @internal revisions
 * @since 1.9.10
 *
**/
require_once(TL_ABS_PATH . "/third_party/fayp-jira-rest/RestRequest.php");
require_once(TL_ABS_PATH . "/third_party/fayp-jira-rest/Jira.php");
class jirarestInterface extends issueTrackerInterface
{
  private $APIClient;
  private $issueDefaults;
  private $issueAttr = null;
  var $support;

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
    $this->support = new jiraCommons();
    $this->support->guiCfg = array('use_decoration' => true);

		$this->methodOpt['buildViewBugLink'] = array('addSummary' => true, 'colorByStatus' => false);
	  if( $this->setCfg($config) )
    {
      $this->completeCfg();
      $this->connect();
      $this->guiCfg = array('use_decoration' => true);
    }  

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

    if( !property_exists($this->cfg,'uriapi') )
    {
      $this->cfg->uriapi = $base . 'rest/api/latest/';
    }

    if( !property_exists($this->cfg,'uriview') )
    {
      $this->cfg->uriview = $base . 'browse/';
    }
      
    if( !property_exists($this->cfg,'uricreate') )
    {
      $this->cfg->uricreate = $base . '';
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
    return $this->checkBugIDSyntaxString($issueID);
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
      $par = array('username' => (string)trim($this->cfg->username),
                   'password' => (string)trim($this->cfg->password),
                   'host' => (string)trim($this->cfg->uriapi));
  	  $this->APIClient = new JiraApi\Jira($par);
      $this->connected = true;

    }
  	catch(Exception $e)
  	{
      $this->connected = false;
      tLog(__METHOD__ . "  " . $e->getMessage(), 'ERROR');
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

			$issue = $this->APIClient->getIssue($issueID);
      if(!is_null($issue) && is_object($issue) && !property_exists($issue,'errorMessages'))
      {
        // We are going to have a set of standard properties
        $issue->summary = $issue->fields->summary;
        $issue->statusCode = $issue->fields->status->id;
        $issue->statusVerbose = $issue->fields->status->name;

        $issue->IDHTMLString = "<b>{$issueID} : </b>";
        $issue->statusHTMLString = $this->support->buildStatusHTMLString($issue->statusVerbose);
        $issue->summaryHTMLString = $this->support->buildSummaryHTMLString($issue);
        $issue->isResolved = isset($this->resolvedStatus->byCode[$issue->statusCode]); 

        /*
        for debug porpouses
        $tlIssue = new stdClass();
        $tlIssue->IDHTMLString = $issue->IDHTMLString;
        $tlIssue->statusCode = $issue->statusCode;
        $tlIssue->statusVerbose = $issue->statusVerbose;
        $tlIssue->statusHTMLString = $issue->statusHTMLString;
        $tlIssue->summaryHTMLString = $issue->summaryHTMLString;
        $tlIssue->isResolved = $issue->isResolved;

        var_dump($tlIssue);
        */
      }
      else
      {
        $issue = null;
      }  
    	
		}
		catch(Exception $e)
		{
      tLog("JIRA Ticket ID $issueID - " . $e->getMessage(), 'WARNING');
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

/*
{
    "fields": {
       "project":
       {
          "key": "TEST"
       },
       "summary": "REST ye merry gentlemen.",
       "description": "Creating of an issue using project keys and issue type names using the REST API",
       "issuetype": {
          "name": "Bug"
       }
   }
}
*/

  /**
   *
   *
   * JSON example:
   *
   * {
   *  "fields": {
   *    "project": {
   *       "key": "TEST"
   *    },
   *    "summary": "REST ye merry gentlemen.",
   *    "description": "Creating of an issue using project keys and issue type names using the REST API",
   *    "issuetype": {
   *       "name": "Bug"
   *    }
   *  }
   * }
   *
   *
   */
  public function addIssue($summary,$description)
  {
    try
    {
      $issue = array('fields' =>
                     array('project' => array('key' => (string)$this->cfg->projectkey),
                           'summary' => $summary,
                           'description' => $description,
                           'issuetype' => array( 'id' => (int)$this->cfg->issuetype)
                           ));


      if(!is_null($this->issueAttr))
      {
        $issue = array_merge($issue,$this->issueAttr);
      }  

      $op = $this->APIClient->createIssue($issue);
      $ret = array('status_ok' => false, 'id' => null, 'msg' => 'ko');
      if(!is_null($op))
      {  
        if(isset($op->errors))
        {
          $ret['msg'] = $op->errors;
        }
        else
        {        
          $ret = array('status_ok' => true, 'id' => $op->key, 
                       'msg' => sprintf(lang_get('jira_bug_created'),$summary,$issue['fields']['project']['key']));
        }  
      }
    }
    catch (Exception $e)
    {
      $msg = "Create JIRA Ticket (REST) FAILURE => " . $e->getMessage();
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
    $tpl = "<!-- Template " . __CLASS__ . " -->\n" .
           "<issuetracker>\n" .
           "<username>JIRA LOGIN NAME</username>\n" .
           "<password>JIRA PASSWORD</password>\n" .
           "<uribase>https://testlink.atlassian.net/</uribase>\n" .
           "<!-- CRITIC - WITH HTTP getIssue() DOES NOT WORK -->\n" .
           "<uriapi>https://testlink.atlassian.net/rest/api/latest/</uriapi>\n" .
           "<uriview>https://testlink.atlassian.net/browse/</uriview>\n" .
           "<!-- Configure This if you want be able TO CREATE ISSUES -->\n" .
           "<projectkey>JIRA PROJECT KEY</projectkey>\n" .
           "<issuetype>JIRA ISSUE TYPE</issuetype>\n" .
           "</issuetracker>\n";
	  return $tpl;
  }



  /**
   *
   **/
  function canCreateViaAPI()
  {
    return (property_exists($this->cfg, 'projectkey') && 
            property_exists($this->cfg, 'issuetype'));
  }

}
