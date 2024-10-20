<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * @filesource	mantisrestInterface.class.php
 * @author 
 *
 *
**/
require_once(TL_ABS_PATH . 
  "third_party/mantis-rest-api/lib/mantis-rest-api.php");

class mantisrestInterface extends issueTrackerInterface {
  private $APIClient;
  private $options = [];

  // Copied from mantis configuration
  private $status_color = array('new'          => '#ffa0a0', # red,
                                'feedback'     => '#ff50a8', # purple
                                'acknowledged' => '#ffd850', # orange
                                'confirmed'    => '#ffffb0', # yellow
                                'assigned'     => '#c8c8ff', # blue
                                'resolved'     => '#cceedd', # buish-green
                                'closed'       => '#e8e8e8'); # light gray

  public $defaultResolvedStatus;


	/**
	 * Construct and connect to BTS.
	 *
	 * @param str $type (see tlIssueTracker.class.php $systems property)
	 * @param xml $cfg
	 **/
	function __construct($type,$config,$name) {
    $this->name = $name;
	  $this->interfaceViaDB = false;
	  $this->methodOpt['buildViewBugLink'] = [
      'addSummary' => true,
      'addReporter' => true, 
      'addHandler' => true,
      'colorByStatus' => false
    ];


    $this->defaultResolvedStatus = [
      [
        'code' => 80, 
       'verbose' => 'resolved'
      ],
      [
        'code' => 90, 
        'verbose' => 'closed'
      ]
    ];
   
    $this->canSetReporter = true;
    if( !$this->setCfg($config) ) {
      return false;
    }  

    $this->completeCfg();
	  $this->setResolvedStatusCfg();
	  $this->connect();
	}

	/**
	 *
	 **/
	function completeCfg() {
		$this->cfg->uribase = trim($this->cfg->uribase,"/"); 
    if(!property_exists($this->cfg, 'uricreate') ) {
      $this->cfg->uricreate = $this->cfg->uribase; 
    }

    if (!property_exists($this->cfg,'uriview')) {
      $this->cfg->uriview = $this->cfg->uribase . '/view.php?id=';
    }

    if( property_exists($this->cfg,'options') ) {
      $option = get_object_vars($this->cfg->options);
      foreach ($option as $name => $elem) {
        $name = (string)$name;
        $this->options[$name] = (string)$elem;     
      }
    } 

    if( !property_exists($this->cfg,'userinteraction') ) {
      $this->cfg->userinteraction = 0;
    }

    if( !property_exists($this->cfg,'createissueviaapi') ) {
      $this->cfg->createissueviaapi = 0;
    }
  }

	/**
   * useful for testing 
   *
   *
   **/
	function getAPIClient() {
		return $this->APIClient;
	}

  /**
   * checks id for validity
   *
   * @param string issueID
   *
   * @return bool returns true if the bugid has the right format, false else
   **/
  function checkBugIDSyntax($issueID) {
    return $this->checkBugIDSyntaxNumeric($issueID);
  }

  /**
   * establishes connection to the bugtracking system
   *
   * @return bool 
   *
   **/
  function connect() {
    $processCatch = false;

    try {
  	  // CRITIC NOTICE for developers
  	  // $this->cfg is a simpleXML Object, then seems very conservative and safe
  	  // to cast properties BEFORE using it.
      $context = [
        'url' => (string)trim($this->cfg->uribase),
        'apikey' => (string)trim($this->cfg->apikey) ];

      $tlContext = [ 'proxy' => config_get('proxy') ];

      $this->APIClient = new mantis($context,$tlContext);

      // to undestand if connection is OK, I will ask for users.
      try {
        $ValarMorghulis = $this->APIClient->getMyUserInfo();
        $this->connected = !is_null($ValarMorghulis);
      }
      catch(Exception $e) {
        $processCatch = true;
      }
    }
  	catch(Exception $e) {
  	  $processCatch = true;
  	}
  	
  	if($processCatch) {
  		$logDetails = '';
  		foreach(['uribase'] as $v) {
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
	function isConnected() {
		return $this->connected;
	}

 /**
   * Return the URL to the bugtracking page for viewing 
   * the bug with the given id. 
   *
   * @param int id the bug id
   * 
   * @return string returns a complete URL to view the bug
   **/
  function buildViewBugURL($id)
  {
    return (string)($this->cfg->uriview . urlencode($id));
  }
 
  /**
   * 
   *
   **/
	public function getIssue($issueID) {
    if (!$this->isConnected()) {
      tLog(__METHOD__ . '/Not Connected ', 'ERROR');
      return false;
    }
    
    $issue = null;
    try {
      $jsonObj = $this->APIClient->getIssue($issueID);

      if( !is_null($jsonObj) && is_object($jsonObj)) {

        $issue = new stdClass();
        $issue->IDHTMLString = "<b>{$issueID} : </b>";
  
        if (property_exists($jsonObj,'exception')) {
          $issue->summary = (string)$jsonObj->reason;
          $issue->summaryHTMLString = $issue->summary;          
          return $issue;
        }

        // Normal processing
        $item = $jsonObj->issues;
        $item = $item[0];

        $issue->statusCode = intval($item->status->id);
        $issue->statusVerbose = (string)$item->status->label;
        $issue->statusHTMLString = "[{$issue->statusVerbose}]";
        $issue->summary = $issue->summaryHTMLString = (string)$item->summary;

        // Actors - Begin
        $issue->reportedBy = (string)$item->reporter->real_name;
        
        // Attention: when issue has not handler yet, property does not exist
        $issue->handledBy = '';
        if (property_exists($item,'handler')) {
          $issue->handledBy = (string)$item->handler->real_name;
        }
        // Actors - End


        $cond = [
          'version' => 'name',
          'fixed_in_version' => 'name',
          'target_version' => 'name'
        ];
        $trans = [
          'version' => 'version',
          'fixed_in_version' => 'fixedInVersion',
          'target_version' => 'targetVersion'
        ];  

        foreach ($cond as $prop => $wtg) {
          $ip = $trans[$prop];
          $issue->$ip = null;
          if ( property_exists($item, $prop)) {
            $issue->$ip = (string)$item->$prop->$wtg;
          }
        }

        $issue->isResolved = false;
      }
    }
    catch(Exception $e) {
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
	function getIssueStatusCode($issueID) {
		$issue = $this->getIssue($issueID);
		return !is_null($issue) ? $issue->state : false;
	}

	/**
	 * Returns status in a readable form (HTML context) for the bug with the given id
	 *
	 * @param string issueID
	 * 
	 * @return string 
	 *
	 **/
	function getIssueStatusVerbose($issueID) {
    $state = $this->getIssueStatusCode($issueID);
    if ($state) {
      return $this->resolvedStatus->byCode[$state];
    }
    return false;
	}

	/**
	 *
	 * @param string issueID
	 * 
	 * @return string 
	 *
	 **/
	function getIssueSummaryHTMLString($issueID) {
    $issue = $this->getIssue($issueID);
    return $issue->summaryHTMLString;
	}

  /**
	 * @param string issueID
   *
   * @return bool true if issue exists on BTS
   **/
  function checkBugIDExistence($issueID) {
    if(($status_ok = $this->checkBugIDSyntax($issueID))) {
      $issue = $this->getIssue($issueID);
      $status_ok = is_object($issue) && !is_null($issue);
    }
    return $status_ok;
  }

  /**
   *
   */
  /* NOT IMPLEMENTED YET 20211130
  public function addIssue($summary,$moreInfo,$opt=null) {
    $more = $moreInfo;
    try {
      $op = $this->APIClient->addIssue($summary, $more['descr'],$opt);
      if(is_null($op)){
        throw new Exception("Error creating issue", 1);
      }

      if (count($more['links']) > 0) {
        $this->APIClient->addExternalLinks($op->id,$more['links']);
      }
  
      $ret = ['status_ok' => true, 'id' => (string)$op->id, 
              'msg' => sprintf(lang_get('mantis_bug_created'),
              $summary, (string)$op->board_id)];
    }
    catch (Exception $e) {
       $msg = "Create Mantis Issue Via REST FAILURE => " . $e->getMessage();
       tLog($msg, 'WARNING');
       $ret = ['status_ok' => false, 'id' => -1, 'msg' => $msg];
    }
    return $ret;
  }
  */
    
  /**
   *
   */
  
  /* NOT IMPLEMENTED YET 20211130
  public function addNote($issueID,$noteText,$opt=null) {
    $op = $this->APIClient->addNote($issueID, $noteText);
    if(is_null($op)){
      throw new Exception("Error setting note", 1);
    }
    $ret = ['status_ok' => true, 'id' => (string)$op->iid, 
            'msg' => sprintf(lang_get('mantis_bug_comment'),
                       $op->body, $this->APIClient->projectId)];
    return $ret;
  }
  */


  /**
   *
   * link->testCaseID
   * link->testCaseName
   * link->relation (verbose)
   *
   */
  public function addLink($issueID,$link) {
    try {
      $op = $this->APIClient->addLink($issueID,$link);
      if(is_null($op)){
        throw new Exception("Error creating link", 1);
      }
      $ret = ['status_ok' => true, 'id' => (string)$op->id, 
              'msg' => 'ok'];
      $msg = "Create Mantis Link Via REST OK => TICKET:" . $issueID . ' >> link: ' . json_encode($link);
      tLog($msg, 'WARNING');
    }
    catch (Exception $e) {
       $msg = "Create Mantis Link Via REST FAILURE => " . $e->getMessage();
       tLog($msg, 'WARNING');

       $msg = "Create Mantis Link Via REST FAILURE => TICKET -> " . $issueID . ' >> link: ' . json_encode($link);
       tLog($msg, 'WARNING');

       $ret = ['status_ok' => false, 'id' => -1, 'msg' => $msg];
    }
    return $ret;
  }

  /**
   *
   * link->testCaseID
   *
   */
  public function removeLink($issueID,$link) {
    try {
      $op = $this->APIClient->removeLink($issueID,$link);
      if(is_null($op)){
        throw new Exception("Error removing link", 1);
      }
      $ret = ['status_ok' => true, 'id' => (string)$op->id, 
              'msg' => 'ok'];
    }
    catch (Exception $e) {
       $msg = "Remove Mantis Link Via REST FAILURE => " . $e->getMessage();
       tLog($msg, 'WARNING');
       $ret = ['status_ok' => false, 'id' => -1, 'msg' => $msg];
    }
    return $ret;
  }

  /**
   *
   * link->testCaseID
   * link->testCaseName
   * link->relation (verbose)
   * link->testPlanName": "TPLAN_A",
   * link->buildName": "BUILD 1",
   * link->platformName": "",
   * link->tester": "Mauro",
   * link->execStatus": "Passed",
   * link->timeStamp": "20200101-23:00"
   *
   */
  public function addExecLink($issueID,$link) {
    try {
      $op = $this->APIClient->addExecLink($issueID,$link);
      /* if(is_null($op)){
        throw new Exception("Error creating exec link", 1);
      }*/
      $ret = ['status_ok' => true, 'msg' => 'ok'];
    }
    catch (Exception $e) {
       $msg = "Create Mantis Exec Link Via REST FAILURE => " . $e->getMessage();
       tLog($msg, 'WARNING');
       $ret = ['status_ok' => false, 'id' => -1, 'msg' => $msg];
    }
    return $ret;
  }


  /**
   *
   **/
	public static function getCfgTemplate() {
    $tpl = "<!-- Template " . __CLASS__ . " -->\n" .
           "<issuetracker>\n" .
           "<!-- Mandatory parameters: -->\n" .
           "<apikey>API KEY</apikey>\n" .
           "<uribase>https://www.mantisbt.org/</uribase>\n" .
           "<!-- IMPORTANT NOTICE --->\n" .
           "<!-- You Do not need to configure uriview,uricreate  -->\n" .
           "<!-- if you have done Mantis standard installation -->\n" .
           "<!-- In this situation DO NOT COPY these config lines -->\n" .
           "<uriview>https://www.mantisbt.org/view.php?id=</uriview>\n" .
           "<uricreate>https://www.mantisbt.org/</uricreate>\n" .
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
