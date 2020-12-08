<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * @filesource	trellorestInterface.class.php
 * @author 
 *
 *
**/
require_once(TL_ABS_PATH . "/third_party/trello-php-api/lib/trello-rest-api.php");

class trellorestInterface extends issueTrackerInterface {
  private $APIClient;
  private $options = [];
  public $defaultResolvedStatus;

  // for trello we allow /
  var $forbidden_chars = '/[!|�%&()=?]/';


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
	  $this->methodOpt['buildViewBugLink'] = [
      'addSummary' => true,'colorByStatus' => false,
      'addReporter' => false, 'addHandler' => false ];

    $this->defaultResolvedStatus = [];

    /* @20201207
    $this->defaultResolvedStatus[] = ['code' => 1, 'verbose' => 'queue'];
    $this->defaultResolvedStatus[] = ['code' => 2, 'verbose' => 'in progress'];
    $this->defaultResolvedStatus[] = ['code' => 3, 'verbose' => 'done'];
    */

    // @20201207 $this->canSetReporter = true;
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
	function completeCfg() 
  {
    $this->cfg->implements = __CLASS__; 

		$this->cfg->uribase = trim($this->cfg->uribase,"/"); 
    if(!property_exists($this->cfg, 'uricreate') ) {
      $this->cfg->uricreate = $this->cfg->uribase; 
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
	function getAPIClient() 
  {
		return $this->APIClient;
	}


  /**
   * 
   * Two formats allowed
   *   https://trello.com/c/XZZftZ8A/12-backlog01-yy
   *   XZZftZ8A
   *
   **/
  function normalizeBugID($issueID)
  {
    $norm = $issueID;
    $pieces = explode('/',$issueID);
    $piecesQty = count($pieces); 
    if ( $piecesQty > 1) {
      // MAGIC
      // 0 -> https:
      // 1 -> /trello.com
      // 2 -> c
      // 3 -> XZZftZ8A
      // 4 -> 12-backlog01-yy
      $norm = $pieces[$piecesQty-2];
    }
    return $norm;
  }

  /**
   * checks id for validity
   *
   * @param string issueID
   *               Two formats allowed
   *               https://trello.com/c/XZZftZ8A/12-backlog01-yy
   *               XZZftZ8A
   *
   * @return bool returns true if the bugid has the right format, false else
   **/
  function checkBugIDSyntax($issueID) 
  {
    // Two formats allowed
    // https://trello.com/c/XZZftZ8A/12-backlog01-yy
    // XZZftZ8A
    return $this->checkBugIDSyntaxString($issueID);
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
      $myContext = [
        'url' => (string)trim($this->cfg->uribase),
        'apikey' => (string)trim($this->cfg->apikey),
        'apitoken' => (string)trim($this->cfg->apitoken),
        'boardid' => (string)trim($this->cfg->boardid)
      ];

      $cfg = ['proxy' => config_get('proxy')];

      $this->APIClient = new trello($myContext,$cfg);
      // to undestand if connection is OK, I will ask for users.
      try {
        $item = $this->APIClient->getBoard();
        $this->connected = ($item != null);
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
	function isConnected() 
  {
		return $this->connected;
	}

  /**
   * 
   *
   **/
  function buildViewBugURL($issueID) 
  {
    return $this->APIClient->getIssueURL($issueID);
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
    $issue = $this->getIssue($issueID);
    return !is_null($issue) ? $issue->statusVerbose : false;
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
    if(($status_ok = $this->checkBugIDSyntax($issueID))) {
      $issue = $this->getIssue($issueID);
      $status_ok = is_object($issue) && !is_null($issue);
    }
    return $status_ok;
  }



  /**
   * 
   *
   **/
	public function getIssue($issueID) 
  {
    if (!$this->isConnected()) {
      tLog(__METHOD__ . '/Not Connected ', 'ERROR');
      return false;
    }
    
    $issue = null;
    try {
      $jsonObj = $this->APIClient->getIssue($issueID);
      if( !is_null($jsonObj) && is_object($jsonObj)) {
        $issue = new stdClass();
        $issue->IDHTMLString = "<b>{$jsonObj->idShort} : </b>";
        
        // dateLastActivity ?
        // idList -> get the name
        $silo = $this->APIClient->getList($jsonObj->idList);
    
        // we will use the list name as the status verbose
        $issue->statusCode = (string)$jsonObj->idList;
        $issue->statusVerbose = (string)$silo->name;
        $issue->statusHTMLString = "[{$issue->statusVerbose}]";

        $verbose = (string)$jsonObj->name; // . " {{$jsonObj->dateLastActivity}}";
        $issue->summary = $issue->summaryHTMLString = $verbose; 
    
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
   *
   **/
	public static function getCfgTemplate() 
  {
    $tpl = "<!-- Template " . __CLASS__ . " -->\n" .
           "<issuetracker>\n" .
           "<!-- Mandatory parameters: -->\n" .
           "<apikey>TRELLO API KEY</apikey>\n" .
           "<apitoken>TRELLO API TOKEN</apitoken>\n" .
           "<uribase>https://api.trello.com/1/</uribase>\n" .
           "<boardid>BOARD IDENTIFICATOR</boardid>\n" .
           "</issuetracker>\n";
	  return $tpl;
  }

 /**
  *
  **/
  function canCreateViaAPI()
  {
    return false;
  }

 /**
  *
  **/
  function canAddNoteViaAPI()
  {
    return false;
  }

}
