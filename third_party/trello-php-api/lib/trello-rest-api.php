<?php
/**
 * trello PHP API
 *
 * Bare bones implementation, just to cover TestLink needs
 *
 * @author  Francisco Mancardi <vinoron@yandex.ru>
 * @link    https://developer.atlassian.com/cloud/trello/rest/api-group-actions/
 *
 */

/**
 *
 */
class trello extends bareBonesRestAPI {

  /**
   * Url to site, https://api.trello.com/1/
   * @var string 
   */
  public $url = '';

  
  /**
   * @var string 
   *
   * Both are needed for Trello
   */
  public $apikey = '';
  public $apitoken = '';
  
  private $authQueryString;

  /**
   * Curl interface with specific settings
   * @var string 
   */
  public $curl = '';

  public $proxy = null;
  
  public $summaryLengthLimit = 1024;
  public $cfg;  

  /**
   * Constructor
   * 
   *
   * @return void
   */
  public function __construct($context,$cfg=null)  
  {

    // if the values are not empty, 
    // we'll assign them to our matching properties
    $mandatory = ['url' => '', 'apikey' => '','apitoken' => '','boardid' => ''];
    foreach ($mandatory as $arg => $dummy) {
      if (!isset($context[$arg]) || empty($context[$arg])) {
        throw new Exception("Missing: $arg", 1);
      }
      $this->$arg = $context[$arg];
    }
    $this->authQueryString = "key={$this->apikey}&token={$this->apitoken}";
    

    if(!is_null($cfg)) {
      if(!is_null($cfg['proxy'])) {
        $this->proxy = (object)['port' => null, 'host' => null,
                                'login' => null, 'password' => null];
        foreach($cfg['proxy'] as $prop => $value) {
          if(isset($cfg['proxy']->$prop)) {
            $this->proxy->$prop = $value; 
          }  
        }  
      }  

      if(isset($cfg['cfg']) && !is_null($cfg['cfg'])) {
        $this->cfg = $cfg['cfg'];
      }  
    }  
    $this->initCurl();
  }



  /**
   *
   */
  function getIssueURL($issueID) {
    $issue = $this->getIssue($issueID);
    if ($issue != null) {
      return $issue->url;
    }
  }

  /**
   * 
object(stdClass)#587 (31) {
  ["id"]=>
  string(24) "5fce598044b6f92b2181641b"
  ["checkItemStates"]=>
  array(0) {
  }
  ["closed"]=>
  bool(false)
  ["dateLastActivity"]=>
  string(24) "2020-12-07T16:34:08.912Z"
  ["desc"]=>
  string(0) ""
  ["descData"]=>
  NULL
  ["dueReminder"]=>
  NULL
  ["idBoard"]=>
  string(24) "5fce57daaf929e8c76ecfeeb"
  ["idList"]=>
  string(24) "5fce57daaf929e8c76ecfeec"
  ["idMembersVoted"]=>
  array(0) {
  }
  ["idShort"]=>
  int(15)
  ["idAttachmentCover"]=>
  NULL
  ["idLabels"]=>
  array(0) {
  }
  ["manualCoverAttachment"]=>
  bool(false)
  ["name"]=>
  string(4) "GAGA"
  ["pos"]=>
  int(212992)
  ["shortLink"]=>
  string(8) "vaHE0rHv"
  ["isTemplate"]=>
  bool(false)
  ["cardRole"]=>
  NULL
  ["dueComplete"]=>
  bool(false)
  ["due"]=>
  NULL
  ["labels"]=>
  array(0) {
  }
  ["shortUrl"]=>
  string(29) "https://trello.com/c/vaHE0rHv"
  ["start"]=>
  NULL
  ["url"]=>
  string(37) "https://trello.com/c/vaHE0rHv/15-gaga"
  ["cover"]=>
  object(stdClass)#584 (5) {
    ["idAttachment"]=>
    NULL
    ["color"]=>
    NULL
    ["idUploadedBackground"]=>
    NULL
    ["size"]=>
    string(6) "normal"
    ["brightness"]=>
    string(5) "light"
  }
  ["idMembers"]=>
  array(0) {
  }
  ["email"]=>
  NULL
  ["badges"]=>
  object(stdClass)#591 (15) {
    ["attachmentsByType"]=>
    object(stdClass)#590 (1) {
      ["trello"]=>
      object(stdClass)#583 (2) {
        ["board"]=>
        int(0)
        ["card"]=>
        int(0)
      }
    }
    ["location"]=>
    bool(false)
    ["votes"]=>
    int(0)
    ["viewingMemberVoted"]=>
    bool(false)
    ["subscribed"]=>
    bool(false)
    ["fogbugz"]=>
    string(0) ""
    ["checkItems"]=>
    int(0)
    ["checkItemsChecked"]=>
    int(0)
    ["checkItemsEarliestDue"]=>
    NULL
    ["comments"]=>
    int(0)
    ["attachments"]=>
    int(0)
    ["description"]=>
    bool(false)
    ["due"]=>
    NULL
    ["dueComplete"]=>
    bool(false)
    ["start"]=>
    NULL
  }
  ["subscribed"]=>
  bool(false)
  ["idChecklists"]=>
  array(0) {
  }
}


   *
   */
  function getIssue($issueID) {
    try {
      $item = $this->_get("/cards/{$issueID}?{$this->authQueryString}");    
      $ret = is_object($item) ? $item : null;
      return $ret;
    } catch(Exception $e) {
      return null;
    }
  } 

  /**
   * 
   *
   */
  public function addIssue($title, $descr, $opt=null) {

    // Limit title length
    $ellipsis = '...';
    $safeTitle = $title;
    $titleLen = strlen($title);
    if( $titleLen > $this->summaryLengthLimit ) {
      $safeTitle = $ellipsis . 
        substr($title, -1*($this->summaryLengthLimit + strlen($ellipsis)));
    }

    $url = '/cards';
    $body = [
      'title' => $safeTitle,
      'description' => $descr,
      'board_id' => (int)$this->boardid,
    ];

    $op = $this->_request_json('POST',$url, $body);

    return $op;
  }

  /**
   * 
   *
   */
  public function addNote($cardID, $noteText) {
    $url = "/cards/{$cardID}/comments";
    $body = [ 'text' => $noteText ];
    $op = $this->_request_json('POST',$url,$body);
    return $op;
  }
  

 /**
  *
  */
  public function getBoard() {
    $item = $this->_get("/boards/{$this->boardid}?{$this->authQueryString}");
    return $item;
  }  

 /**
  *
  * fields: (from trello documentation)
  *         all or a comma separated list of List field names.
  *         Default: name,closed,idBoard,pos
  */
  public function getList($itemID,$fields=null) {
    $item = $this->_get("/lists/{$itemID}?{$this->authQueryString}");
    return $item;
  }  

  
  /**
   * 
   */
  public function __destruct() 
  {
  }

}