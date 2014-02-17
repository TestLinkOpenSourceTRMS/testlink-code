<?php
/**
 * redmine PHP API
 *
 * Bare bones implementation, just to cover TestLink needs
 *
 * @author   Francisco Mancardi <francisco.mancardi@gmail.com>
 * @created  20120339
 * @link     http://gitorious.org/testlink-ga/testlink-code
 *
 * @internal revisions
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
	 * Curl interface with FugB specific settings
	 * @var string 
	 */
	public $curl = '';
	
	/**
	 * Constructor
	 * 
	 *
	 * @return void
	 */
	public function __construct($url,$apiKey) 
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

		$this->initCurl();
	}


	/**
	 * 
	 *
	 */
	public function initCurl() 
	{
		$agent = "TestLink 1.9.10";
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
		curl_setopt_array($this->curl,array(CURLOPT_USERAGENT => $agent,
						                            CURLOPT_VERBOSE => 0,
						                            CURLOPT_FOLLOWLOCATION => TRUE,
		    					                      CURLOPT_RETURNTRANSFER => TRUE,
		    					                      CURLOPT_AUTOREFERER => TRUE,
                                        CURLOPT_TIMEOUT => 30,
		    					                      CURLOPT_SSL_VERIFYPEER => FALSE));
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
  public function addIssueFromSimpleXML($issueXmlObj)
  {
    $op = $this->_request_xml('POST',"/issues.xml",$issueXmlObj->asXML());
    return $op;
  }

	public function getProjects() 
	{                        
	  $items = $this->_get("/projects.xml");
	  return $items;
	}                                                   

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
	protected function _request_xml($method, $url, $body = NULL, $ignore_status = 0) 
	{
		$r = $this->_request($method, $url, $body, $ignore_status);
		$response = $r['response'];
		$content = trim($r['content']);
		$ret = ($content != '' ? $content : null);
  
		if(!is_null($ret) && !empty($response['content_type'])) 
		{
		 if(	preg_match('/application\/xml/', $response['content_type']) || 
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
	protected function _request($method, $cmd, $body = NULL, $ignoreStatusCode = 0) 
	{
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
	    if(!isset($this->apiKey)) 
	    {
		  throw new exception(__METHOD__ . " Can not work without redmine apiKey");
		}		  
		curl_setopt($this->curl, CURLOPT_USERPWD, $this->apiKey . ":" . $this->apiKey);
		curl_setopt($this->curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

		
		// added after some connection issues
		// CRITIC:
		// Sometimes seems that curl is unable to resolve hostname if
		// apache has been started BEFORE HAVING NETWORK connection UP.
		// Obviosly this is realy difficult is you have a production server
		// but when doing test this can happens
		//
		curl_setopt($this->curl, CURLOPT_DNS_USE_GLOBAL_CACHE, false );
		curl_setopt($this->curl, CURLOPT_DNS_CACHE_TIMEOUT, 2 );
	
		
		if ($method == 'PUT' || $method == 'POST') 
		{
		  // Got this info from http://tspycher.com/2011/03/using-the-redmine-api-with-php/
		  // For TL I'have added charset=UTF-8, following code I've found on other REST API example
			curl_setopt($this->curl, CURLOPT_HEADER, 0); 
			curl_setopt($this->curl, CURLOPT_HTTPHEADER, 
			                         array("Content-Type: text/xml; charset=UTF-8", 
			                               "Content-length: " . mb_strlen($body))); 
		}

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
?>
