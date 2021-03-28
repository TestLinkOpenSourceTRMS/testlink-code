<?php
/**
 * github PHP API
 *
 * Bare bones implementation, just to cover TestLink needs
 *
 * @author delcroip <delcroip@gmail:com> 
 * file derived from GITlab integration done by jlguardi <jlguardi@gmail.com> 
 * @created  202011
 * @link     https://docs.github.com/en/rest/
 *
 * @internal revisions
 * @since 1.9.20-fixed
 */



/**
 *
 */
class github
{
  /**
   * Url to site, https://[yoursite].xxxx.com
   * @var string 
   */
  public $url = '';
  
  /**
   * @var string 
   */
  public $apiKey = '';

  /**
   * @var string 
   */
  public $user = '';
  
  /**
   * Owner identifier
   * @var string
   */
  public $owner = null;
  
    /**
   * Repository identifier
   * @var string
   */
  public $repo = null;
  
  /**
   * Curl interface with specific settings
   * @var string 
   */
  public $curl = '';

  public $proxy = null;
  
  /**
   * Just supports api version 4 by now
   */
  //public $api = '/api/v4/';

  /**
   * Constructor
   * 
   *
   * @return void
   */
  public function __construct( $url, $user, $apiKey, $owner, $repo,  $cfg=null) 
  {
    // if the values are not empty, we'll assign them to our matching properties
    $args = array('user,', 'apiKey','url', 'owner', 'repo');
    foreach ($args as $arg) 
    {
      if (!empty($$arg)) 
      {
        $this->$arg = $$arg;
      }
    }

    if(is_null($this->url)){
      $this->url = "https://api.github.com";
    }

    if(!is_null($cfg))
    {
      if(!is_null($cfg->proxy))
      {
        $this->proxy = new stdClass();
        $this->proxy->port = null;
        $this->proxy->host = null;
        $this->proxy->login = null;
        $this->proxy->password = null;

        foreach($cfg->proxy as $prop => $value)
        {
          if(isset($cfg->proxy->$prop))
          {
            $this->proxy->$prop = $value; 
          }  
        }  
      }  
    }  
    
    //if( is_null($this->$projectId))
    //{
    //  throw new Exception("Missing projectId", 1);
    //}
    //if( is_null($this->$url) || is_null($this->apiKey))
    //{
    //  throw new Exception("Missing url or key", 1);
    //}
    
    $this->initCurl();
  }


  /**
   * 
   *
   */
  public function initCurl($cfg=null) 
  {
    $agent = "TestLink 1.9.16";
    try
    {
      $this->curl = curl_init();
    }
    catch (Exception $e)
    {
      var_dump($e);
    }
    
    // set the agent, forwarding, and turn off ssl checking
    // Timeout in Seconds
    $curlCfg = array(CURLOPT_USERAGENT => $agent,
                     CURLOPT_VERBOSE => 0,
                     CURLOPT_FOLLOWLOCATION => TRUE,
                     CURLOPT_RETURNTRANSFER => TRUE,
                     CURLOPT_AUTOREFERER => TRUE,
                     CURLOPT_TIMEOUT => 60,
                     CURLOPT_SSL_VERIFYPEER => FALSE  );

    if(!is_null($this->proxy))
    {
      $doProxyAuth = false;
      $curlCfg[CURLOPT_PROXYTYPE] = 'HTTP';

      foreach($this->proxy as $prop => $value)
      {
        switch($prop)
        {
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
         !is_null($this->proxy->password) )
      {
        $curlCfg[CURLOPT_PROXYUSERPWD] = 
          $this->proxy->login . ':' . $this->proxy->password;
      }  
    } 

    curl_setopt_array($this->curl,$curlCfg);
  }

  function getIssueURL($issueID)
  {
    $issue = $this->getIssue($issueID);
    return is_object($issue) ? $issue->html_url : null;
  }
  /**
   * Function to get the list of comment on the issue
   *
   */
  function getNotes($issueID)
  {
    try
    {
      //return $this->get('/repos/'.rawurlencode($username).'/'.rawurlencode($repository).'/issues/'.$id);
      $item = $this->_get("/repos/".rawurlencode($this->owner)."/".rawurlencode($this->repo)."/issues/$issueID/comments");    
      $ret = is_array($item) ? $item : null;
      return $ret;
    }
    catch(Exception $e)
    {
      return null;
    }
  } 
  function getIssue($issueID)
  {
    try
    {
      //return $this->get('/repos/'.rawurlencode($username).'/'.rawurlencode($repository).'/issues/'.$id);
      $item = $this->_get("/repos/".rawurlencode($this->owner)."/".rawurlencode($this->repo)."/issues/$issueID");
      $ret = is_object($item) ? $item : null;
      return $ret;
    }
    catch(Exception $e)
    {
      return null;
    }
  }

  /**
   *
   *
   */
  function getIssues($filters=null)
  {
    $items = $this->_get("/repos/".rawurlencode($this->owner)."/".rawurlencode($this->repo)."/issues");
    return $items;
  }

  // with the help of http://tspycher.com/2011/03/using-the-redmine-api-with-php/
  // public function addIssue($summary, $description)
  public function addIssue($title, $text)
  {
    $url =  "/repos/".rawurlencode($this->owner)."/".rawurlencode($this->repo)."/issues";
    $data = array("title"=>$title,"body" => $text, "label" => array("testlink"));
    $op = $this->_request_json('POST',$url, json_encode($data));
    return $op;
  }

  public function addNote($issueID, $noteText)
  {
    $url = "/repos/".rawurlencode($this->owner)."/".rawurlencode($this->repo)."/issues/$issueID/comments";
    $data = array("body" => $noteText);
    $op = $this->_request_json('POST',$url,  json_encode($data));
    return $op;
  }
  /**
   *
   */
  public function getRepo()
  {
    $items = $this->_get("/repos/".rawurlencode($this->owner)."/".rawurlencode($this->repo));
    return $items;
  }                                                   

                                           

  /**
   *
   */
  public function getIssueStatuses() 
  {                 
    return array('open','closed');
  }                                                   




  /* -------------------------------------------------------------------------------------- */
  /* General Methods used to build up communication process                                 */
  /* -------------------------------------------------------------------------------------- */

  /** 
   *
   * @internal notice
   * copied and adpated from work on YouTrack API interface by Jens Jahnke <jan0sch@gmx.net>
   **/
  protected function _get($url) 
  {
    return $this->_request_json('GET', $url);
  }



   /** 
  *
  * @internal notice
  * copied and adpated from work on YouTrack API interface by Jens Jahnke <jan0sch@gmx.net>
  **/
  protected function _request_json($method, $url, $body = NULL, $ignore_status = 0,
                                  $reporter=null) 
  {
    $r = $this->_request($method, $url, $body, $ignore_status,$reporter);
    $response = $r['response'];
    $content = $r['content'];
    return ($content != '' ? json_decode($content) : null);
  }

  
 /** 
  *
  * @internal notice
  * copied and adpated from work on YouTrack API interface by Jens Jahnke <jan0sch@gmx.net>
  **/
  protected function _request($method, $cmd, $body = NULL, $ignoreStatusCode = 0,$reporter = null) 
  {
    // this can happens because if I save object on _SESSION PHP is not able to
    // save resources.
    if( !is_resource($this->curl) )
    {
      $this->initCurl();
    }  
    $url = $this->url . $this->api . $cmd;

    curl_setopt($this->curl, CURLOPT_URL, $url);

    if(!isset($this->apiKey) || trim($this->apiKey) == '') 
    {
      throw new exception(__METHOD__ . " Can not work without github apiKey");
    } 

    curl_setopt($this->curl, CURLOPT_DNS_USE_GLOBAL_CACHE, false );
    curl_setopt($this->curl, CURLOPT_DNS_CACHE_TIMEOUT, 2 );
  
    $header = array();
    //$header[] = "PRIVATE-TOKEN: {$this->apiKey}";
    $header[] = 'Accept: application/vnd.github.v3+json';

    curl_setopt($this->curl, CURLOPT_HEADER, 0); 
    curl_setopt($this->curl, CURLOPT_HTTPHEADER, $header); 
    $userpwd = $this->user.':'.$this->apiKey;
    curl_setopt($this->curl, CURLOPT_USERPWD, $userpwd);
    curl_setopt($this->curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    switch ($method) 
    {
      case 'GET':
        curl_setopt($this->curl, CURLOPT_HTTPGET, TRUE);
      break;
    
      case 'PUT':
        $handle = NULL;
        $size = 0;
        // Check if we got a file or just a string of data.
        if (file_exists($body)) 
        {
          $size = filesize($body);
          if (!$size) 
          {
            throw new exception("Can't open file $body!");
          }
          $handle = fopen($body, 'r');
        }
        else 
        {
          $size = mb_strlen($body);
          $handle = fopen('data://text/plain,' . $body,'r');
        }
        curl_setopt($this->curl, CURLOPT_PUT, TRUE);
        curl_setopt($this->curl, CURLOPT_INFILE, $handle);
        curl_setopt($this->curl, CURLOPT_INFILESIZE, $size);
      break;
    
      case 'POST':
        curl_setopt($this->curl, CURLOPT_POST, TRUE);
        if (!empty($body)) 
        {
          curl_setopt($this->curl, CURLOPT_POSTFIELDS, $body);
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
    
    $rr = array('content' => $content,'response' => $response,'curlError' => $curlError);
    return $rr;
  
  }


        
  
  /**
   * Destructor
   * 
   * Logout if we haven't already done so
   * 
   * @return void
   */
  public function __destruct() 
  {
  }

} // Class end