<?php
/**
 * FogBugz PHP API
 *
 * Built against FB API 8
 *
 * @author   Craig Davis <craig.davis@learningstation.com>
 * @created  1/15/2011
 * @link     https://github.com/LearningStation/fogbugz-php-api
 * @see      http://fogbugz.stackexchange.com/fogbugz-xml-api
 * @license  MIT http://www.opensource.org/licenses/mit-license.php
 */


/**
 * FogBugz API Wrapper
 *
 * Interface with FobgBugz API
 *
 * Sample (w/o exception handling)
 *
 *   $fogbugz = new FogBugz(
 *       'username@example.com',
 *       'password',
 *       'http://example.fogbugz.com'
 *   );
 *   $fogbugz->logon();
 *   $fogbugz->startWork(array(
 *     'ixBug' => 23442
 *   ));
 *   $fogbugz->logoff();
 *
 * @author Craig Davis <craig.davis@learningstation.com>
 */
class FogBugz {
  
  /**
   * Url to the FogBugz site, http:[yoursite].fogbugz.com
   * @var string 
   */
  public $url = '';
  
  /**
   * path to the FogBugz api script
   * @var string 
   */
  public $path = 'api.asp';
  
  /**
   * Username for the site
   * @var string 
   */
  public $user = '';
  
  /**
   * User password for the site
   * @var string 
   */
  public $pass = '';
  
  /**
   * path to the FogBugz api script
   * @var string 
   */
  public $token = '';

  /**
   * Curl interface with FB specific settings
   * @var string 
   */
  public $curl = '';

  /**
   * Constructor
   * 
   * @param string $user username for fogbugz connection (default: '')
   * @param string $pass password for fogbugz connection (default: '')
   * @param string $url  base url for fogbugz (default: '')
   * @param string $path path to api script (default: '')
   *
   * @return void
   */
  public function __construct($user = '', $pass = '', $url = '', $path = '') {
    
    // if the values are not empty, we'll assign them to our matching properties
    $args = array('user', 'pass', 'url', 'path');
    foreach ($args as $arg) {
      if (!empty($$arg)) {
        $this->$arg = $$arg;
      }
    }
    
    // make sure their is a / between the url and the path
    if ('/' != substr($this->url, -1)
        && '/' != substr($this->path, 0, 1)
    ) {
      $this->url .= "/";
    }
    
    // init our curl object here
    $this->curl = new FogBugzCurl();
  }
  
  /**
   * Destructor
   * 
   * Logout if we haven't already done so
   * 
   * @return void
   */
  public function __destruct() {
    if (!empty($this->token)) {
      $this->logoff();
    }
  }

  /**
   * Respond to FogBugz API Calls
   * 
   * @param string $name      FogBugz API command name, see docs ?cmd=
   * @param array  $arguments first argument contains
   *                          an array of params for FogBugz, ie:
   *                          ixBug, sEmail, ixProject, ixPerson
   *
   * @return SimpleXMLElement containing the result from FB
   */
  public function __call($name, $arguments) {
    // if the anon method is called without arguments, we won't send any
    // along, it $fb->stopWork();
    $parameters = isset($arguments[0]) ? $arguments[0] : array();
    return $this->_request($name, $parameters);
  }
  
  /**
   * Logon to FogBugz API and store the authentication token
   * 
   * You don't have to explicitely call this, unless you want a new
   * token, the constructor runs it automatically
   * 
   * @return void
   */
  public function logon() {
    try {
      // make the initial logon request to get a token
      // that we use in subsequent requests
      $xml = $this->_request('logon', array('email'    => $this->user,
                                            'password' => $this->pass));
      // store this token for use later
      $this->token = (string)$xml->token;
    }
    catch (FogBugzAPIError $e) {
      $message = "Login Error. " .
          		   "Please check the url, username and password. Error: " .
          		   $e->getMessage();
      throw new FogBugzLogonError($message, 0);
    }
    return TRUE;
  }
  
  /**
   * Logoff and unset our authentication token
   * 
   * @return void
   */
  public function logoff() {
    $this->_request('logoff');
    $this->token = '';
  }
  
  /**
   * Send request to FogBugz
   * 
   * Internal handler to communicate to FB
   * 
   * @see __call()
   *
   * @param string $command FogBugz command, ?cmd=
   * @param array  $params  fogbugz parameters (default: array())
   *
   * @return SimpleXMLElement containing the result from FB
   */
  private function _request($command, $params = array()) {
    // the logon command generates the token
    if ('logon' != $command) {
      $params['token'] = $this->token;
    }
    
    // add the command to the get request
    $params['cmd'] = $command;
    $url = $this->url . $this->path . '?' . http_build_query($params);
    
    // make the request and throw an api exception if we detect an error
    try {
      $result = $this->curl->fetch($url);
      $xml    = new SimpleXMLElement($result);
      if (isset($xml->error)) {
        $code    = (string) $xml->error['code'];
        $message = (string) $xml->error;
        throw new FogBugzAPIError($message, $code);
      }    
    }
    catch (FogBugzCurlError $e) {
      throw new FogBugzAPIError($e->getMessage, 0);
    }
    
    // return the SimpleXMLElement object
    return $xml;
  }

}

/** 
 * Simple Curl wrapper to encapsulate any special settings
 *
 * @author Craig Davis <craig.davis@learningstation.com>
 */
class FogBugzCurl {

  /**
   * Our curl connection reference
   * @var resource
   */
  private $_ch;
  
  /** 
   * last response
   * @var string
   */
  public $response;

  /**
   * Constructor inits our curl
   * 
   * @return void
   */
  public function __construct() {
  
    // Let's be nice and let them know we are out here
    $agent = 
        "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0; " .
        "LearningStation FogBugz API " .
        "https://github.com/LearningStation/fogbugz-php-api)";
  
    $this->_ch = curl_init();

    // set the agent, forwarding, and turn off ssl checking
    curl_setopt_array($this->_ch, array(
        CURLOPT_USERAGENT      => $agent,
        CURLOPT_VERBOSE        => 0,
        CURLOPT_FOLLOWLOCATION => TRUE,
        CURLOPT_RETURNTRANSFER => TRUE,
        CURLOPT_AUTOREFERER    => TRUE,
        CURLOPT_SSL_VERIFYPEER => FALSE
    ));
  }
  
  /**
   * Fetch a url
   * 
   * @param string $url path to fetch
   *
   * @return void
   */
  public function fetch($url) {
    // set the url
    curl_setopt($this->_ch, CURLOPT_URL, $url);
    // execute the curl call
    $this->response = curl_exec($this->_ch);
    
    // check for errors and throw an exception if something happened
    if (curl_errno($this->_ch)) {
      throw new FogBugzCurlError(
          curl_error($this->_ch),
          curl_errno($this->_ch)
      );
    }
    return $this->response;
  }
  
  /**
   * Destructor closes the curl instance
   * 
   * @return void
   */
  public function __destruct() {
    curl_close($this->_ch);
  }
}

/** 
 * Fogbugz Curl Error
 *
 * Used by FogBugzCurl for connection errors
 *
 * @author Craig Davis <craig.davis@learningstation.com>
 */
class FogBugzCurlError extends Exception {
}

/** 
 * Fogbugz API Error
 *
 * @author Craig Davis <craig.davis@learningstation.com>
 */
class FogBugzAPIError extends Exception {
}

/** 
 * Fogbugz Logon Error
 *
 * @author Craig Davis <craig.davis@learningstation.com>
 */
class FogBugzLogonError extends FogBugzAPIError {
}

/** 
 * Fogbugz Connection Error
 *
 * @author Craig Davis <craig.davis@learningstation.com>
 */
class FogBugzConnectionError extends FogBugzAPIError {
}