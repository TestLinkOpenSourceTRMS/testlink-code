<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * @filesource  githubrestInterface.class.php
 * @author delcroip <delcroip@gmail:com> 
 * file derived from GITlab integration done by jlguardi <jlguardi@gmail.com> 
 *
 * @internal revisions
 * @since 1.9.20-fixed
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
    $this->defaultResolvedStatus[] = array('code' => 'open', 'verbose' => 'open');
    $this->defaultResolvedStatus[] = array('code' => 'closed', 'verbose' => 'closed');
    
    if( !$this->setCfg($config) )
    {
      return false;
    }  

    // http://www.github.org/issues/6843
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
   * in order to simplify configuration.
   * 
   *
   **/
  function completeCfg()
  {
    $base = trim($this->cfg->url,"/") . '/'; // be sure no double // at end
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
      $url = (string)trim($this->cfg->url);
      $user = (string)trim($this->cfg->user);
      $apiKey = (string)trim($this->cfg->apikey);
      $repo = (string)trim($this->cfg->repo); //TODO: check integer value
      $owner = (string)trim($this->cfg->owner); //TODO: check integer value
      $pxy = new stdClass();
      $pxy->proxy = config_get('proxy');
      $this->APIClient = new github($url, $user, $apiKey, $owner, $repo, $pxy);

      // to undestand if connection is OK, I will ask for projects.
      // I've tried to ask for users but get always ERROR from github (not able to understand why).
      try
      {
        $items = $this->APIClient->getRepo();
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
      foreach(array('url', 'user', 'apikey', 'owner', 'repo') as $v)
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
        $issue->id = $jsonObj->number;
        $issue->url = $jsonObj->html_url;
        $issue->statusCode = (string)$jsonObj->state;
        $issue->statusVerbose = (string)$jsonObj->state;
        $issue->statusHTMLString = "[$issue->statusVerbose] ";
        $issue->summaryHTMLString = (string)$jsonObj->title.":</br>".(string)$jsonObj->body; 
        $issue->summary =  (string)$jsonObj->title.":\n".(string)$jsonObj->body;
        $Notes = $this->APIClient->getNotes((int)$issueID);
        if(is_array($Notes) && count($Notes)>0){
          foreach($Notes as $key => $note){
            $issue->summaryHTMLString .= "</br>[Note $key]:$note->body";
            $issue->summary .= "\n[Note $key]: $note->body";
          }
        }
        $issue->isResolved = $this->state == 'closed'; 
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

  public function addIssue($summary,$description,$opt=null)
  {
    try
    {
      $op = $this->APIClient->addIssue($summary, $description);
      if(is_null($op)){
        throw new Exception("Error creating issue", 1);
      }
      $ret = array('status_ok' => true, 'id' => (string)$op->number, 
                   'msg' => sprintf(lang_get('github_bug_created'),
                    $summary, $this->APIClient->repo));
     }
     catch (Exception $e)
     {
       $msg = "Create github Ticket FAILURE => " . $e->getMessage();
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
                   'msg' => sprintf(lang_get('github_bug_comment'),$op->body, $this->APIClient->repo));
    return $ret;
  }




  /**
   *
   * 
   *    
   **/
  public static function getCfgTemplate()
  {
    $tpl = "<!-- Template " . __CLASS__ . " -->\n" .
           "<issuetracker>\n" .
           "<user>github user</user>\n" .
           "<apikey>github TOKEN</apikey>\n" .
           "<url>https://api.github.com</url>\n" .
           "<owner>GitHub Org or User</owner>\n" .
           "<repo>github REPOSITORY</repo>\n" .
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