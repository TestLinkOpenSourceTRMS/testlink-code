<?php
/**
 * redmine PHP API
 *
 * Bare bones implementation, just to cover TestLink needs
 *
 * @author   Francisco Mancardi <francisco.mancardi@gmail.com>
 * @created  20120339
 * @link     http://gitorious.org/testlink-ga/testlink-code
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
	 * Curl interface with FB specific settings
	 * @var string 
	 */
	public $curl = '';
	
	private $headers = array();
	
	
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
		// Let's be nice and let them know we are out here
		$agent = "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0; " .
		         "TestLink simple redmine API )";
		
		try
		{
			$this->curl = curl_init();
		}
		catch (Exception $e)
		{
			var_dump($e);
		}
		
		// set the agent, forwarding, and turn off ssl checking
		curl_setopt_array($this->curl, 
						  array(CURLOPT_USERAGENT => $agent,CURLOPT_VERBOSE => 0,CURLOPT_FOLLOWLOCATION => TRUE,
		    					CURLOPT_RETURNTRANSFER => TRUE,CURLOPT_AUTOREFERER => TRUE,
		    					CURLOPT_SSL_VERIFYPEER => FALSE));
	}


	/**
	 * 
	 *
	 */
	function getIssue($issueID)
	{
		/* 
		 Strange thing, if I comment following block using JUST //,
		 when running script I've got unexpected $end error!!!
		*/  
		/*
		//  Example of what we get as content
		//  <?xml version="1.0" encoding="UTF-8"?>
		// 	<issue>
		// 		<id>3</id>
		// 		<project name="tl-project-002" id="2"/>
		// 		<tracker name="Bug" id="1"/>
		// 		<status name="New" id="1"/>
		// 		<priority name="Immediate" id="7"/>
		// 		<author name="Redmine Admin" id="2"/>
		// 		<subject>BUG ON tl-project-002</subject>
		// 		<description>Is a BUG IMMEDIATE</description>
		// 		<start_date>2012-03-31</start_date>
		// 		<due_date/>
		// 		<done_ratio>0</done_ratio>
		// 		<estimated_hours/>
		// 		<spent_hours>0.0</spent_hours>
		// 		<created_on>2012-03-31T10:49:44+02:00</created_on>
		// 		<updated_on>2012-03-31T10:49:44+02:00</updated_on>
		// 	</issue>
		// 	
		// 	This is returned as a simpleXMLObject
		// 	(int)$xx->id
		// 	(string)$xx->project['name']
		// 	(int)$xx->project['id']
		*/
	    $item = $this->_get("/issues/$issueID.xml?key=$this->apiKey");
		$ret = is_object($item) ? $item : null;
		return $ret;
	}	




	/**
	 * 
	 *
	 */
	function getIssues($filters=null)
	{
	    $items = $this->_get("/issues.xml?key=$this->apiKey");
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
		
		// added after some connection issues
		// CRITIC:
		// Sometimes seems that curl is unable to resolve hostname if
		// apache has been started BEFORE HAVING NETWORK connection UP.
		// Obviosly this is realy difficult is you have a production server
		// but when doing test this can happens
		//
		curl_setopt($this->curl, CURLOPT_DNS_USE_GLOBAL_CACHE, false );
		curl_setopt($this->curl, CURLOPT_DNS_CACHE_TIMEOUT, 2 );
		
		
		$headers = $this->headers;
		if ($method == 'PUT' || $method == 'POST') 
		{
		  $headers[CURLOPT_HTTPHEADER][] = 'Content-Type: application/xml; charset=UTF-8';
		  $headers[CURLOPT_HTTPHEADER][] = 'Content-Length: '. mb_strlen($body);
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
		  throw new exception(__METHOD__ . "url:$url - response:$response - content: $content");
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