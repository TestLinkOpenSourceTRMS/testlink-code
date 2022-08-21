<?php
/**
 * github PHP API
 *
 * Bare bones implementation, just to cover TestLink needs
 *
 * @author   sebiboga <sebastian.boga@outlook.com>
 * @created  20220820
 * @link     https://docs.github.com/en/rest/issues
 *
 * @internal revisions
 * @since 1.9.20
 */

class github
{
  /**
   * Url to site, https://[yoursite].xxxx.com
   * @var string 
   */
  public $url = 'https://github.com/';
  
  /**
   * @var string 
   */
  public $apiKey = '';
  
  /**
   * Project identifier
   * @var string
   */
 // public $projectOwner = null;
   public $projectOwner = null;
   
 // public $projectRepo = null;
   public $projectRepo = null;
  /**
   * Curl interface with specific settings
   * @var string 
   */
  public $curl = '';

  public $proxy = null;
  
  /**
   * Repos
   */
  public $api = '';

  /**
   * Constructor
   * 
   *
   * @return void
   */
  public function __construct($url, $apiKey, $projectOwner, $projectRepo , $cfg=null) 
  {
	  
    // if the values are not empty, we'll assign them to our matching properties
    $args = array('apiKey','url', 'projectOwner', 'projectRepo');
    foreach ($args as $arg) 
    {
      if (!empty($$arg)) 
      {
        $this->$arg = $$arg;
      }
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
    
    
    $this->initCurl();
  }


  /**
   * 
   *
   */
  public function initCurl($cfg=null) 
  {
    $agent = "TestLink 1.9.20";
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
                     CURLOPT_SSL_VERIFYPEER => FALSE);

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
  //  return is_object($issue) ? $issue->web_url : null;
  
     return is_object($issue) ? $issue->html_url : null;
  }

 
 
  function getIssue($issueID)
  {
    try
    { 
      $item = $this->_get("/repos/".$this->projectOwner."/".$this->projectRepo."/issues/$issueID");    
      $ret = is_object($item) ? $item : null;
      return $ret;
    }
    catch(Exception $e)
    {
      return null;
    }
  } 

  
  function getIssues($filters=null)
  {  
  
    $items = $this->_get($this->projectOwner."/".$this->projectRepo."/issues");
	
	
    return $items;
  } 


  public function addIssue($title, $text)
  {
    $url ="/repos/". $this->projectOwner."/".$this->projectRepo."/issues";
	$temp = new StdClass();
	$temp-> title = $title;
	$temp-> body = $text;
	$temp-> labels = ["TestLink","bug"];
	$payload = json_encode($temp);
	
    $op = $this->_request_json('POST',$url,$payload);
    return $op;
  }

  public function addNote($issueID, $noteText)
  {
    $url ="/repos/". $this->projectOwner."/".$this->projectRepo."/issues/".$issueID."/comments";
	$temp = new StdClass();
	$temp-> body = $noteText;
	$payload = json_encode($temp);
	
	
    $op = $this->_request_json('POST',$url,$payload);
    return $op;
  }
  /**
   *
   */
  public function getProjects() 
  {                        
    $items = $this->_get($this->projectOwner."/repos");
    return $items;
  }                                                   



  public function getUser() 
  {                        
    $items = $this->_get("/user/repos");
    return $items;
  }  

  public function getIssueStatuses() 
  {                        
    throw new exception(__METHOD__ . " Not implemented");
    return $items;
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
      throw new exception(__METHOD__ . " Can not work without gitlab apiKey");
    } 
 
    curl_setopt($this->curl, CURLOPT_DNS_USE_GLOBAL_CACHE, false );
    curl_setopt($this->curl, CURLOPT_DNS_CACHE_TIMEOUT, 2 );
  
    $header = array();
	$header[] = "Accept: application/vnd.github+json";
	$header[] = "Content-type: application/json";
    $header[] = "Authorization: token {$this->apiKey}";

    curl_setopt($this->curl, CURLOPT_HEADER, 0); 
    curl_setopt($this->curl, CURLOPT_HTTPHEADER, $header); 

    switch ($method) 
    {
      case 'GET':
	    $header = array();
		
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
