<?php
/**
 * bare bones REST PHP API
 *
 * Bare bones implementation, to be reused  
 * Copied and adpated from work on YouTrack API interface 
 * by Jens Jahnke <jan0sch@gmx.net>
 *
 * @author  Francisco Mancardi <vinoron@yandex.ru>
 *
 */

/**
 *
 */
class bareBonesRestAPI {
  /**
   * @var string 
   *
   * Some systems i.e. trello need both
   */
  public $apikey = '';
  public $apitoken = '';
  
  /**
   * Curl interface with specific settings
   * @var string 
   */
  public $curl = '';


  /**
   * Curl Header
   * changes according the system
   *
   * @var [] 
   */
  public $curlHeader = [];

  /**
   * properties
   *  host
   *  port
   *  login
   *  password
   */
  public $proxy = null;
  
  public $cfg;  

  /**
   * Constructor
   * 
   *
   * @return void
   */
  public function __construct()  {
    $this->initCurl();
  }

  /**
   * 
   *
   */
  public function initCurl($cfg=null) 
  {
    $agent = "TestLink " . TL_VERSION_NUMBER;
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
   * @internal notice
   * copied and adpated from work on YouTrack API interface by Jens Jahnke <jan0sch@gmx.net>
   **/
  protected function _get($url) {
    return $this->_request_json('GET', $url);
  }

  /** 
  *
  * @internal notice
  * copied and adpated from work on YouTrack API interface by Jens Jahnke <jan0sch@gmx.net>
  **/
  protected function _request_json($method, $url, $body = NULL, $ignore_status = 0,$reporter=null) {
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

    // this is the minimal test
    if(empty($this->apikey) ){
      throw new exception(__METHOD__ . 
        " Can not work without apikey");
    } 



    // this can happens because if I save object on _SESSION PHP is not able to
    // save resources.
    if( !is_resource($this->curl) ) {
      $this->initCurl();
    } 

    $additional = '';
    if (property_exists($this, 'api')) {
      $additional = trim($this->api);
    }
    $url = $this->url . $additional . $cmd;
    
    curl_setopt($this->curl, CURLOPT_URL, $url);



    curl_setopt($this->curl, CURLOPT_DNS_USE_GLOBAL_CACHE, false );
    curl_setopt($this->curl, CURLOPT_DNS_CACHE_TIMEOUT, 2 );
    curl_setopt($this->curl, CURLOPT_HEADER, 0); 
    curl_setopt($this->curl, CURLOPT_HTTPHEADER, $this->curlHeader); 

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
    
    $rr = ['content' => $content,'response' => $response,'curlError' => $curlError];
    return $rr;
  }
  
  /**
   * 
   */
  public function __destruct() 
  {
  }

}