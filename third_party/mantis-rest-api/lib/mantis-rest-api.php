<?php
/**
 * Mantis PHP API
 *
 * Bare bones implementation, just to cover TestLink needs
 *
 *
 */

/**
 *
 */
class mantis {
  /**
   * Url to site, 
   * @var string 
   */
  public $url = '';
  
  /**
   * @var string 
   */
  public $apikey = '';
  
  /**
   * Curl interface with specific settings
   * @var string 
   */
  public $curl = '';

  public $proxy = null;
  
  /** 
   */
  public $summaryLengthLimit = 1024;
  public $cfg;  

  /**
   * Constructor
   * 
   *
   * @return void
   */
  public function __construct($context,$cfg=null)  {

    // if the values are not empty, 
    // we'll assign them to our matching properties
    foreach ($context as $arg => $val) {
      if (!empty($val)) {
        $this->$arg = $val;
      }
    }
    
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

      if (isset($cfg['cfg']) && !is_null($cfg['cfg'])) {
        $this->cfg = $cfg['cfg'];
      }  
    }  
    $this->initCurl();
  }

  /**
   * 
   *
   */
  public function initCurl($cfg=null) {
    $agent = "TestLink ". TL_VERSION_NUMBER;
    try {
      $this->curl = curl_init();
    }
    catch (Exception $e) {
      var_dump($e);
    }
    
    // set the agent, forwarding, and turn off ssl checking
    // Timeout in Seconds
    $curlCfg = [CURLOPT_USERAGENT => $agent,
                CURLOPT_VERBOSE => 0,
                CURLOPT_FOLLOWLOCATION => TRUE,
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_AUTOREFERER => TRUE,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_SSL_VERIFYPEER => FALSE];

    if(!is_null($this->proxy)) {
      $doProxyAuth = false;
      $curlCfg[CURLOPT_PROXYTYPE] = 'HTTP';

      foreach($this->proxy as $prop => $value) {
        switch($prop) {
          case 'host':
            $curlCfg[CURLOPT_PROXY] = $value;
          break;

          case 'port':
            $curlCfg[CURLOPT_PROXYPORT] = $value;
          break;

          case 'login':
          case 'password':
            $doProxyAuth = true;
          break;
        }
      }

      if($doProxyAuth && !is_null($this->proxy->login) && 
         !is_null($this->proxy->password) ) {
        $curlCfg[CURLOPT_PROXYUSERPWD] = 
          $this->proxy->login . ':' . $this->proxy->password;
      }  
    } 

    curl_setopt_array($this->curl,$curlCfg);
  }

function isValidJSON($string) {
    json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE);
}

  /**
   * 
   *
   */
  function getIssue($issueID) {
    try {
      $item = $this->_get("/api/rest/issues/{$issueID}");  
      $ret = is_object($item) ? $item : null;
      return $ret;
    }
    catch(Exception $e) {
      tLog(__METHOD__ . '/' . $e->getMessage(),'ERROR');

      $exu = new stdClass();
      $exu->exception = true;
      $exu->reason = $e->getMessage();
      return $exu;
    }
  } 

  /**
   * 
   *
   */
  public function addIssue($title, $descr, $opt=null) {
  }

  /**
   * 
   *
   */
  public function addNote($issueID, $noteText) {
  }
  
  /**
   * 
   *
   */
  function addExternalLinks($issueID, $links) {
  }

  /**
   * 
   *
   */
  function addTags($issueID, $tags) {
  }

  /**
   * 
   *
   */
  public function addLink($issueID, $link, $opt=null) {
    $url = "/api/rest/index.php/plugins/TestSpec/add/{$issueID}";
    $op = $this->_request_json('POST',$url, $link);
    return $op;
  }

  /**
   * 
   *
   */
  public function removeLink($issueID, $link, $opt=null) {
    $url = "/api/rest/index.php/plugins/TestSpec/remove/{$issueID}";
    $op = $this->_request_json('POST',$url, $link);
    return $op;
  }

  /**
   *
   */
  public function getMyUserInfo() {   
    $items = $this->_get("/api/rest/users/me");
    return $items;
  }                                                   


  /* ------------------------------------------------------ */
  /* General Methods used to build up communication process */
  /* ------------------------------------------------------ */

  /** 
   *
   * @internal notice
   * copied and adapted from work on YouTrack API interface 
   * by Jens Jahnke <jan0sch@gmx.net>
   **/
  protected function _get($url) {
    return $this->_request_json('GET', $url);
  }

  /** 
   *
   * @internal notice
   * copied and adapted from work on YouTrack API interface 
   * by Jens Jahnke <jan0sch@gmx.net>
   */
  protected function _request_json($method, $url, $body = NULL, $ignore_status = 0,$reporter=null) {
    $r = $this->_request($method, $url, $body, $ignore_status,$reporter);
    $response = $r['response'];
    $r['content'] = trim($r['content']);
    $content = json_decode($r['content']);
    if (json_last_error() == JSON_ERROR_NONE) {
      return $content;
    }
    
    tLog(__METHOD__ . '/Content:' 
         . $r['content'],'ERROR');

    $msg = 'Bad Response!!';
    if (null != $response && isset($response['http_code'])) {
      $msg = "http_code:" . $response['http_code'];
    }
    $msg = "Error Parsing JSON -> " . $msg . 
           " -> Give a look to TestLink Event Viewer";

    throw new Exception($msg, 1);
  }
  
 /** 
  *
  * @internal notice
  * copied and adapted from work on YouTrack API interface 
  * by Jens Jahnke <jan0sch@gmx.net>
  **/
  protected function _request($method, $cmd, $body = NULL, $ignoreStatusCode = 0,$reporter = null) 
  {
    // this can happens because if I save object on _SESSION PHP is not able to
    // save resources.
    if( !is_resource($this->curl) ) {
      $this->initCurl();
    }  
    $url = $this->url . $cmd;
    curl_setopt($this->curl, CURLOPT_URL, $url);
    if( empty($this->apikey) ){
      throw new exception(__METHOD__ . 
        " Can not work without apikey");
    } 

    curl_setopt($this->curl, CURLOPT_DNS_USE_GLOBAL_CACHE, false );
    curl_setopt($this->curl, CURLOPT_DNS_CACHE_TIMEOUT, 2 );
  
    $header = [ "Authorization: {$this->apikey}",       
                "Content-Type: application/json",
                "Agent-Name: testlink",
                "Agent-Version: " . TL_VERSION_NUMBER ];

    curl_setopt($this->curl, CURLOPT_HEADER, 0); 
    curl_setopt($this->curl, CURLOPT_HTTPHEADER, $header); 

    switch ($method) {
      case 'GET':
        curl_setopt($this->curl, CURLOPT_HTTPGET, TRUE);
      break;
    
      case 'POST':
      case 'PATCH':
        curl_setopt($this->curl, CURLOPT_POST, TRUE);
        if (!empty($body)) {
          curl_setopt($this->curl, CURLOPT_POSTFIELDS, json_encode($body));
        }
      break;
    
      default:
        throw new exception("Unknown method $method!");
      break;
    }
    
    $content = curl_exec($this->curl);
    $response = curl_getinfo($this->curl);
    $curlError =  curl_error($this->curl);
    $httpCode = (int)$response['http_code'];
    if ($httpCode != 200 && $httpCode != 201 && $httpCode != $ignoreStatusCode) 
    {
      throw new exception(__METHOD__ . "url:$this->url - response:" .
                          json_encode($response) . ' - content: ' . json_encode($content) );
    }
    
    $rr = ['content' => $content,
           'response' => $response,
           'curlError' => $curlError];
    
    return $rr;
  }
  
  /**
   * 
   */
  public function __destruct() 
  {
  }

}