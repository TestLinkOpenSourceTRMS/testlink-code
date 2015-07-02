<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * @filesource  youtrackrestInterface.class.php
 * @author Francisco Mancardi
 *
 * @internal IMPORTANT NOTICE
 * we use issueID on methods signature, to make clear that this ID 
 * is HOW issue in identified on Issue Tracker System, 
 * not how is identified internally at DB level on TestLink
 *
 * @internal IMPORTANT NOTICE
 * 1. based on contribution by jetbrains.com, but refactored to use
 *    https://github.com/jan0sch/YouTrack-Client-PHP-Library
 *    to improve/fix things that were not clear on jetbrains contribution.
 *
 * 2. http://curl.haxx.se/libcurl/php/examples/callbacks.html 
 *    provided very useful simple curl php usage examples
 *
 * @internal revisions
 * @since 1.9.10
 * 
**/
require_once(TL_ABS_PATH . "/third_party/youtrackclient/src/youtrackclient.php");

class youtrackrestInterface extends issueTrackerInterface
{
  private $APIClient;
  private $issueDefaults;
  private $issueTemplate;
  

  /**
   * Construct and connect to BTS.
   * Can be overloaded in specialized class
   *
   * @param str $type (see tlIssueTracker.class.php $systems property)
   **/
  function __construct($type,$config,$name)
  {
    $this->name = $name;
    $this->interfaceViaDB = false;
    $this->methodOpt = array('buildViewBugLink' => array('addSummary' => true, 'colorByStatus' => true));
    $this->connected = false;
    if( $this->setCfg($config) )
    {  
      $this->completeCfg();
      $this->connect();
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
        $this->APIClient = new \YouTrack\Connection($this->cfg->uribase, 
                                                    $this->cfg->username, $this->cfg->password);
        $this->connected = true;
      }
      catch(Exception $e)
      {
        $this->connected = false;
        tLog(__METHOD__ . $e->getMessage(), 'ERROR');
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
  function getIssue($issueID)
  {
    if (!$this->isConnected())
    {
      return false;
    }
    
    try
    {
      $issue = $this->APIClient->get_issue($issueID);   
      if( !is_null($issue) && is_object($issue) )
      {
        $issue->IDHTMLString = "<b>{$issueID} : </b>";
        $issue->statusCode = $issue->State;
        $issue->statusVerbose = $issue->statusCode;
        $issue->statusHTMLString = "[$issue->statusCode] ";
        $issue->summaryHTMLString = $issue->summary;
      }
      
    }
    catch(\YouTrack\YouTrackException $yte)
    {
      tLog($yte->getMessage(),'ERROR');
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
    return (!is_null($issue) && is_object($issue))? $issue->statusCode : false;
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
        $str = "Ticket ID - " . $issueID . " - does not exist in BTS";
        $issue = $this->getBugStatus($issueID);
        if (!is_null($issue) && is_object($issue))
        {
            $str = array_search($issue->status, $this->statusDomain);
      if (strcasecmp($str, 'closed') == 0 || strcasecmp($str, 'resolved') == 0 )
            {
                $str = "<del>" . $str . "</del>";
            }
            $str = "<b>" . $issueID . ": </b>[" . $str  . "] " ;
        }
        return $str;
  }



    /**
     *
     * @author francisco.mancardi@gmail.com>
     **/
  public static function getCfgTemplate()
    {
    $template = "<!-- Template " . __CLASS__ . " -->\n" .
          "<issuetracker>\n" .
          "<username>YOUTRACK LOGIN NAME</username>\n" .
          "<password>YOUTRACK PASSWORD</password>\n" .
          "<!-- IMPORTANT NOTICE -->\n" .
          "<!-- uribase CAN NOT END with / -->\n" .
          "<uribase>http://testlink.myjetbrains.com/youtrack</uribase>\n".
          "<!-- Configure project if you want to Add issues from TestLink -->\n" .
          "<project>YOUTRACK PROJECT ID</project>\n".
          "</issuetracker>\n";
    return $template;
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
    // '/' at uribase name creates issue with API
    $this->cfg->uribase = trim((string)$this->cfg->uribase,"/");

    $base =  $this->cfg->uribase . '/';
    if( !property_exists($this->cfg,'uriview') )
    {
        $this->cfg->uriview = $base . 'issue/';
    }
      
    if( !property_exists($this->cfg,'uricreate') )
    {
        $this->cfg->uricreate = $base . 'dashboard#newissue=yes';
    }
    
    $this->issueTemplate = array();
    $this->issueDefaults = array('assignee' => '', 'priority' => '', 'type' => '', 
                                 'subsystem' => '', 'state' => '', 'affectsversion' => '', 
                                 'fixedversion' => '', 'fixedinbuild' => '');
    foreach($this->issueDefaults as $prop => $default)
    {
      $this->cfg->$prop = (string)(property_exists($this->cfg,$prop) ? $this->cfg->$prop : $default);
      $this->issueTemplate[$prop] = $this->cfg->$prop;
    }   

    
          
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
            $status_ok = (!is_null($issue) && is_object($issue));
        }
        return $status_ok;
    }

  /**
   *
   */
  public function addIssue($summary,$description)
  {
    try
    {
      $issue = $this->issueTemplate;
      $op = $this->APIClient->create_issue((string)$this->cfg->project, $issue['assignee'], 
                                           $summary, $description, $issue['priority'], 
                                           $issue['type'], $issue['subsystem'], $issue['state'], 
                                           $issue['affectsversion'], 
                                           $issue['fixedversion'], $issue['fixedinbuild']);
      
      $ret = array('status_ok' => true, 'id' => (string)$op->id, 
                   'msg' => sprintf(lang_get('youtrack_bug_created'),$summary,(string)$this->cfg->project));
    }
    catch (Exception $e)
    {
      $msg = "Create YOUTRACK Ticket FAILURE => " . $e->getMessage();
      tLog($msg, 'WARNING');
      $ret = array('status_ok' => false, 'id' => -1, 'msg' => $msg . ' - serialized issue:' . serialize($issue));
    }
    return $ret;
  }


 /**
  *
  **/
  function canCreateViaAPI()
  {
    return (property_exists($this->cfg, 'project'));
  }


}