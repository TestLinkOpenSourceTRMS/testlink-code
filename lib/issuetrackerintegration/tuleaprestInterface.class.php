<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 *
 * @author Alex & Amit
 *
**/

require_once(TL_ABS_PATH . "/third_party/tuleap-php-api/lib/tuleap-rest-api.php");

class tuleaprestInterface extends issueTrackerInterface
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
    $this->methodOpt = array('buildViewBugLink' =>
                           array('addSummary' => true,
                                 'colorByStatus' => true,
                                 'addReporter' => true,
                                 'addHandler' => true));

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
      $processCatch = false;

      try
      {
    	  // CRITIC NOTICE for developers
    	  // $this->cfg is a simpleXML Object, then seems very conservative and safe
    	  // to cast properties BEFORE using it.
        $redUrl = (string)trim($this->cfg->uribase.'/api');
        $redAK = (string)trim($this->cfg->apikey);
        $pxy = new stdClass();
        $pxy->proxy = config_get('proxy');
        $this->APIClient = new tuleap($redUrl,$redAK, $pxy);

        try
        {
          $items = $this->APIClient->getProjects();
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
		  $issue->statusCode = (string)$jsonObj->id;


		  $issue->statusVerbose = (string)$jsonObj->status;
          $issue->statusHTMLString = "[$issue->statusVerbose] ";
          $issue->summary = $issue->summaryHTMLString = (string)$jsonObj->title;
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



  public static function getCfgTemplate()
    {
    $template = "<!-- Template " . __CLASS__ . " -->\n" .
          "<issuetracker>\n" .
          "<tracker>TULEAP TRACK NUMBER</tracker>\n" .
          "<fields>\n" .
		  "<summary>FIELD ID OF ISSUE SUMMERY</summary>\n" .
		  "<description>FIELD ID OF ISSUE DESCRIPTION</description>\n" .
		  "<email>FIELD ID OF CC</email>\n" .
      "<assign>FIELD ID OF ASSIGN TO</assign>\n" .
      "</fields>\n" .
      "<!-- create custom field (type list and devlopers names like tulep) and set here the id (from DB) -->\n" .
      "<cf>2</cf>\n" .
      "<!-- write here your devlopers user_group id from tuleap -->\n" .
      "<ugroupid>103</ugroupid>\n" .
          "<uribase>YOUR TULEAP URL</uribase>\n".
	      "<apikey>YOUR TULEAP API KEY</apikey>\n".
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

	$this->cfg->apiuribase = $this->cfg->uribase."/api";
    $base =  $this->cfg->uribase . '/';
	$this->cfg->pluginuribase = $this->cfg->uribase."/plugins";
	$this->cfg->plugintrackeruri = $this->cfg->pluginuribase."/tracker";

    if( !property_exists($this->cfg,'uriview') )
    {
        $this->cfg->uriview = $this->cfg->plugintrackeruri.'/?aid=';
    }

    if( !property_exists($this->cfg,'uricreate') )
    {
         $this->cfg->uricreate = $this->cfg->plugintrackeruri.'/?tracker='.$this->cfg->tracker;
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

	$this->cfg->project = '1';
	$this->cfg->issuetype = '1';
	$this->cfg->issuepriority = '1';
	$this->cfg->component = '1';
	$this->cfg->version = '1';
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


  public function addIssue($summary,$description,$opt=null)
  {
    try
    {
      $op = $this->APIClient->addIssue($summary, $description, $this->cfg);
      if(is_null($op)){
        throw new Exception("Error creating issue", 1);
      }
      $ret = array('status_ok' => true, 'id' => (string)$op->id,
                   'msg' => sprintf(lang_get('tuleap_bug_created'),
                    $summary, $this->cfg->tracker));

     }
     catch (Exception $e)
     {
       $msg = "Create TULEAP Ticket FAILURE => " . $e->getMessage();
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

  /**
  * this method config that create link to TL in issue descriotion always true
  **/
  function completeOpt($opt)
  {
	  $opt['addLinkToTL'] = true;
	  return $opt;
  }


}
