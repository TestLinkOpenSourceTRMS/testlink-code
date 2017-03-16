<?php
/**
 * redmine PHP API
 *
 * Bare bones implementation, just to cover TestLink needs
 *
 * @author   Francisco Mancardi <francisco.mancardi@gmail.com>
 * @created  20120339
 * @link     http://www.testlink.org
 *
 * @internal revisions
 * @since 1.9.16
 */

/**
 *
 */
class redmine
{
  /**
   * Url to site, http:[yoursite].xxxx.com
   * @var string 
   */
  public $url = '';
  
  /**
   * @var string 
   */
  public $apiKey = '';
  
  /**
   * Curl interface with specific settings
   * @var string 
   */
  public $curl = '';

  public $proxy = null;
  
  /**
   * Constructor
   * 
   *
   * @return void
   */
  public function __construct($url,$apiKey,$cfg=null) 
  {
    // if the values are not empty, we'll assign them to our matching properties
    $args = array('apiKey','url');
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


  /**
   * 
   *
   */
  function getIssue($issueID)
  {
    $item = $this->_get("/issues/$issueID.xml");    
    $ret = is_object($item) ? $item : null;
    return $ret;
  } 

  /**
   * 
   *
   */
  function getIssues($filters=null)
  {
    $items = $this->_get("/issues.xml");
    return $items;
  } 


  // with the help of http://tspycher.com/2011/03/using-the-redmine-api-with-php/
  // public function addIssue($summary, $description)
  public function addIssueFromSimpleXML($issueXmlObj,$reporter=null)
  {
    $op = $this->_request_xml('POST',"/issues.xml",$issueXmlObj->asXML(),0,$reporter);
    return $op;
  }

  /**
   *
   */
  public function addIssueFromXMLString($XMLString,$reporter=null)
  {
    $op = $this->_request_xml('POST',"/issues.xml",$XMLString,0,$reporter);
    return $op;
  }


  /**
   *
   */
  public function addIssueNoteFromSimpleXML($issueID,$issueXmlObj,$reporter=null)
  {
    $op = $this->_request_xml('PUT',"/issues/{$issueID}.xml",$issueXmlObj->asXML(),0,$reporter);
    return $op;
  }


  /**
   *
   */
  public function getProjects() 
  {                        
    $items = $this->_get("/projects.xml");
    return $items;
  }                                                   

  /**
   * @param mixed $id: identifier => string
   *                   id => int
   */
  public function getProjectByIdentity($id) 
  {                        
    $item = $this->_get("/projects/{$id}.xml");
    return $item;
  }                                                   

  /**
   *
   */
  public function getIssueStatuses() 
  {                        
    $items = $this->_get("/issue_statuses.xml");
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
    return $this->_request_xml('GET', $url);
  }



   /** 
  *
  * @internal notice
  * copied and adpated from work on YouTrack API interface by Jens Jahnke <jan0sch@gmx.net>
  **/
  protected function _request_xml($method, $url, $body = NULL, $ignore_status = 0,
                                  $reporter=null) 
  {
    $r = $this->_request($method, $url, $body, $ignore_status,$reporter);
    $response = $r['response'];
    $content = trim($r['content']);
    $ret = ($content != '' ? $content : null);
  
    if(!is_null($ret) && !empty($response['content_type'])) 
    {
     if(  preg_match('/application\/xml/', $response['content_type']) || 
      preg_match('/text\/xml/', $response['content_type'])) 
     {
       $ret = simplexml_load_string($ret);
     }
    }
    return $ret;
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

    curl_setopt($this->curl, CURLOPT_URL, $this->url . $cmd);

    // Following Info From http://www.redmine.org/projects/redmine/wiki/Rest_api
    // Authentication
    // Most of the time, the API requires authentication. 
    // To enable the API-style authentication, you have to check Enable REST API in 
    // Administration -> Settings -> Authentication. 
    //
    // Then, authentication can be done in 2 different ways:
    // 1. using your regular login/password via HTTP Basic authentication.
    // 2. using your API key which is a handy way to avoid putting a password in a script. 
    //    The API key may be attached to each request in one of the following way:
    //    2.1 passed in as a "key" parameter
    //    2.2 passed in as a username with a random password via HTTP Basic authentication
    //    2.3 passed in as a "X-Redmine-API-Key" HTTP header (added in Redmine 1.1.0)
    // You can find your API key on your account page ( /my/account ) when logged in, 
    // on the right-hand pane of the default layout.
    // Code From http://tspycher.com/2011/03/using-the-redmine-api-with-php/
    //
    if(!isset($this->apiKey) || trim($this->apiKey) == '') 
    {
      throw new exception(__METHOD__ . " Can not work without redmine apiKey");
    } 

    // added after some connection issues
    // CRITIC:
    // Sometimes seems that curl is unable to resolve hostname if
    // apache has been started BEFORE HAVING NETWORK connection UP.
    // Obviosly this is realy difficult is you have a production server
    // but when doing test this can happens
    //
    curl_setopt($this->curl, CURLOPT_DNS_USE_GLOBAL_CACHE, false );
    curl_setopt($this->curl, CURLOPT_DNS_CACHE_TIMEOUT, 2 );
  
    // 
    // 20150501 - I'm having problems with this way to authenticate    
    //curl_setopt($this->curl, CURLOPT_USERPWD, $this->apiKey . ":" . $this->apiKey);
    //curl_setopt($this->curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    $header = array();
    $header[] = "X-Redmine-API-Key: {$this->apiKey}";

    if(!is_null($reporter))
    {
      $header[] = "X-Redmine-Switch-User: {$reporter}";
    } 

    if ($method == 'PUT' || $method == 'POST') 
    {
      // Got this info from http://tspycher.com/2011/03/using-the-redmine-api-with-php/
      // For TL I'have added charset=UTF-8, following code I've found on other REST API example
      $header[] = "Content-Type: text/xml; charset=UTF-8"; 
      $header[] = "Content-length: " . mb_strlen($body);

    }
    curl_setopt($this->curl, CURLOPT_HEADER, 0); 
    curl_setopt($this->curl, CURLOPT_HTTPHEADER, $header); 

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