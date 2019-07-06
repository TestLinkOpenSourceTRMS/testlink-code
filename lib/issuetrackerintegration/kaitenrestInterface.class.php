<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * @filesource	kaitenrestInterface.class.php
 * @author 
 *
 *
**/
require_once(TL_ABS_PATH . "/third_party/kaiten-php-api/lib/kaiten-rest-api.php");

class kaitenrestInterface extends issueTrackerInterface {
  private $APIClient;
  private $options = [];
  private $conditionMap = [
    '2' => 'archived',
    '3' => 'deleted'
  ];

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
      'addSummary' => true,'colorByStatus' => false,
      'addReporter' => false, 'addHandler' => false ];

    $this->defaultResolvedStatus = [];
    $this->defaultResolvedStatus[] = ['code' => 1, 'verbose' => 'queue'];
    $this->defaultResolvedStatus[] = ['code' => 2, 'verbose' => 'in progress'];
    $this->defaultResolvedStatus[] = ['code' => 3, 'verbose' => 'done'];
  
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

    if( property_exists($this->cfg,'options') ) {
      $option = get_object_vars($this->cfg->options);
      foreach ($option as $name => $elem) {
        $name = (string)$name;
        $this->options[$name] = (string)$elem;     
      }
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
      $kaitenContext = [
        'url' => (string)trim($this->cfg->uribase),
        'apikey' => (string)trim($this->cfg->apikey),
        'boardId' => (string)trim($this->cfg->boardid),
        'options' => $this->options ];

      $tlContext = [ 'proxy' => config_get('proxy'), 
                     'cfg' => ['setcardowneremail' => 
                                 $this->cfg->setcardowneremail] ];
      $tlContext['cfg'] = (object)$tlContext['cfg'];

      $this->APIClient = new kaiten($kaitenContext,$tlContext);
      // to undestand if connection is OK, I will ask for users.
      try {
        $items = $this->APIClient->getUsers();
        $this->connected = count($items) > 0 ? true : false;
        unset($items);
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
   * 
   *
   **/
  function buildViewBugURL($issueID) {
    return $this->APIClient->getIssueURL($issueID);
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
        $conditionData = isset($this->conditionMap[$jsonObj->condition]) ? ' / '.$this->conditionMap[$jsonObj->condition] : '';
        $issue = new stdClass();
        $issue->IDHTMLString = "<b>{$issueID} : </b>";
        $issue->statusCode = (string)$jsonObj->state;
        $issue->statusVerbose = $this->resolvedStatus->byCode[$issue->statusCode];
        $issue->statusHTMLString = "[{$issue->statusVerbose}{$conditionData}]";
        $issue->summary = $issue->summaryHTMLString = (string)$jsonObj->title;
        $issue->isResolved = (int)$jsonObj->state == 3;
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
  function parseAddInfo($info) {
    $result = [ 'descr' => $info, 'links' => [] ];

    $pik = array('dl2tl' => lang_get('dl2tl'),
                 'dl2tlpv' => lang_get('dl2tlpv'));

    $matches = array('dl2tl' => 0, 'dl2tlpv' => 0);

    foreach($pik as $ky => $vy ) {
      preg_match('/^' . $vy . '(.+)$/imu', $info, $matches[$ky]);    
      if( count($matches[$ky]) > 1 ) {
        $result['links'][] = [
          'descr' => $vy,
          'url' => $matches[$ky][1]
        ];
      }
    }

    if (!empty($result['links'])) {
      $result['descr'] = strstr($info, $result['links'][0]['descr'], true);
    }
    return $result;
  }

  /**
   *
   */
  public function addIssue($summary,$moreInfo,$opt=null) {
    $more = $this->parseAddInfo($moreInfo);
    try {
      $op = $this->APIClient->addIssue($summary, $more['descr'],$opt);
      if(is_null($op)){
        throw new Exception("Error creating issue", 1);
      }

      if (count($more['links']) > 0) {
        $this->APIClient->addExternalLinks($op->id,$more['links']);
      }
  
      $tags = null;
      if (!empty($opt)) {
        $tags = [
          ['name' => $opt->execContext['testplan_name']],
          ['name' => $opt->execContext['build_name']] 
        ];
      }
      if (null !== $tags) {
        $this->APIClient->addTags($op->id,$tags);
      }

      $ret = ['status_ok' => true, 'id' => (string)$op->id, 
              'msg' => sprintf(lang_get('kaiten_bug_created'),
              $summary, (string)$op->board_id)];
    }
    catch (Exception $e) {
       $msg = "Create KAITEN Card FAILURE => " . $e->getMessage();
       tLog($msg, 'WARNING');
       $ret = ['status_ok' => false, 'id' => -1, 'msg' => $msg];
    }
    return $ret;
  }
    
  /**
   *
   */
  public function addNote($issueID,$noteText,$opt=null) {
    $op = $this->APIClient->addNote($issueID, $noteText);
    if(is_null($op)){
      throw new Exception("Error setting note", 1);
    }
    $ret = ['status_ok' => true, 'id' => (string)$op->iid, 
            'msg' => sprintf(lang_get('kaiten_bug_comment'),
                       $op->body, $this->APIClient->projectId)];
    return $ret;
  }

  /**
   *
   **/
	public static function getCfgTemplate() {
    $tpl = "<!-- Template " . __CLASS__ . " -->\n" .
           "<issuetracker>\n" .
           "<!-- Mandatory parameters: -->\n" .
           "<apikey>KAITEN API KEY</apikey>\n" .
           "<uribase>https://company.kaiten.io</uribase>\n" .
           "<boardid>BOARD IDENTIFICATOR</boardid>\n" .
           "<!-- TestLink Optional parameters --> \n" .
           "<setcardowneremail>0</setcardowneremail>\n" .          
           "<!-- Optional parameters (see API documentation on https://kaiten.io): -->\n" .
           "<options>\n" .
           "<columnid></columnid>\n" .
           "<laneid></laneid>\n" .
           "<ownerid></ownerid>\n" .
           "<typeid></typeid>\n" .
           "<position></position>\n" .
           "<sortorder></sortorder>\n" .
           "<asap></asap>\n" .
           "<sizetext></sizetext>\n" .
           "<businessvalue></businessvalue>\n" .
           "</options>\n" .
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
