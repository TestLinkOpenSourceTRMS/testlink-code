<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * @filesource	githubrestInterface.class.php
 * @author sebiboga <sebastian.boga@outlook.com>
 *
 * @internal revisions
 * @since 1.9.20
 *
**/
require_once(TL_ABS_PATH . "/third_party/github-php-api/lib/github-rest-api.php");
class githubrestInterface extends issueTrackerInterface
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
      $cf = $this->cfg->custom_fields;
      $this->cfg->custom_fields = (string)$cf->asXML();
    }   
  }

	function getAPIClient()
	{
		return $this->APIClient;
	}


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
      $projectOwner = (string)trim($this->cfg->projectowner); //TODO: check integer value
	  $projectRepo = (string)trim($this->cfg->projectrepo); //TODO: check integer value
      $pxy = new stdClass();
      $pxy->proxy = config_get('proxy');
	 
      $this->APIClient = new github($redUrl,$redAK,$projectOwner,$projectRepo, $pxy);

      // here we test if we can connect to GitHUB using configuration data provided
	  // curl -i -H "Authorization: token  <PAT>"    https://api.github.com/user/repos

	  
   
      try
      { 
	    //you need to have at least one repo for validation of TestLink <-> gitHUB integration
        $items = $this->APIClient->getUser();
        $this->connected = count($items) > 0 ? true : false;
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

  function buildViewBugURL($issueID)
  {
    if (!$this->isConnected())
    {
      tLog(__METHOD__ . '/Not Connected ', 'ERROR');
      return false;
    }
    return $this->APIClient->getIssueURL($issueID);
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
      $jsonObj = $this->APIClient->getIssue((int)$issueID);
      
      if( !is_null($jsonObj) && is_object($jsonObj))
      {
        $issue = new stdClass();
        $issue->IDHTMLString = "<b>{$issueID} : </b>";
        $issue->statusCode = (string)$jsonObj->state;
        $issue->statusVerbose = (string)$jsonObj->state;
        $issue->statusHTMLString = "[$issue->statusVerbose] ";
        $issue->summary = $issue->summaryHTMLString = (string)$jsonObj->title;
        $issue->githubProject = array('name' => (string)$jsonObj->repository_url);
                                       
        $issue->isResolved = isset($this->state); 
      }
    }
    catch(Exception $e)
    {
      tLog(__METHOD__ . '/' . $e->getMessage(),'ERROR');
      $issue = null;
    }	
    return $issue;		
	}



	function getIssueStatusCode($issueID)
	{
		$issue = $this->getIssue($issueID);
		return !is_null($issue) ? $issue->statusCode : false;
	}


	function getIssueStatusVerbose($issueID)
	{
    return $this->getIssueStatusCode($issueID);
	}


	function getIssueSummaryHTMLString($issueID)
	{
    $issue = $this->getIssue($issueID);
    return $issue->summaryHTMLString;
	}


  function checkBugIDExistence($issueID)
  {
    if(($status_ok = $this->checkBugIDSyntax($issueID)))
    {
      $issue = $this->getIssue($issueID);
      $status_ok = is_object($issue) && !is_null($issue);
    }
    return $status_ok;
  }

  public function addIssue($summary,$description,$opt=null)
  {
    try
    {
      $op = $this->APIClient->addIssue($summary, $description);
      if(is_null($op)){
        throw new Exception("Error creating issue", 1);
      }
      $ret = array('status_ok' => true, 'id' => (string)$op->number, 
                   'msg' => sprintf(lang_get('gitlab_bug_created'),
                    $summary, $this->APIClient->projectrepo));
     }
     catch (Exception $e)
     {
       $msg = "Create GITHUB Ticket FAILURE => " . $e->getMessage();
       tLog($msg, 'WARNING');
       $ret = array('status_ok' => false, 'id' => -1, 'msg' => $msg . ' - serialized issue:' . serialize($issue));
     }
     return $ret;
  }  


  /**
   *
   */
  public function addNote($issueID,$noteText,$opt=null)
  {
    $op = $this->APIClient->addNote($issueID, $noteText);
    if(is_null($op)){
      throw new Exception("Error setting note", 1);
    }
    $ret = array('status_ok' => true, 'id' => (string)$op->id, 
                   'msg' => sprintf(lang_get('gitlab_bug_comment'),$op->body, $this->APIClient->projectrepo));
    return $ret;
  }




  /**
   *
   * @author sebastian.boga@outlook.com>
   **/
  public static function getCfgTemplate()
  {
    $tpl = "<!-- Template " . __CLASS__ . " -->\n" .
           "<issuetracker>\n" .
           "<apikey>GITHUB PERSONAL TOKEN</apikey>\n" .
           "<uribase>https://api.github.com</uribase>\n" .
           "<projectowner>GITHUB USER</projectowner>\n" .
		   "<projectrepo>REPO</projectrepo>\n" .
           "</issuetracker>\n";
    return $tpl;
  }

 /**
  *
  **/
  function canCreateViaAPI()
  {
    return true;
  }


}
