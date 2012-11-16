<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * @filesource	bugzillaxmlrpcInterface.class.php
 * @author Francisco Mancardi
 *
 *
 * @internal revisions
 * @since 1.9.5
 * 
**/
require_once('Zend/Loader/Autoloader.php');
Zend_Loader_Autoloader::getInstance();

class bugzillaxmlrpcInterface extends issueTrackerInterface
{
    private $APIClient;

	/**
	 * Construct and connect to BTS.
	 *
	 * @param str $type (see tlIssueTracker.class.php $systems property)
	 * @param xml $cfg
	 **/
	function __construct($type,$config)
	{
		$this->interfaceViaDB = false;
		$this->methodOpt['buildViewBugLink'] = array('addSummary' => true, 'colorByStatus' => false);
		
	    $this->setCfg($config);
		$this->completeCfg();
	    $this->connect();
	    $this->guiCfg = array('use_decoration' => true); // add [] on summary
	}


	/**
	 *
	 * check for configuration attributes than can be provided on
	 * user configuration, but that can be considered standard.
	 * If they are MISSING we will use 'these carved on the stone values' 
	 * in order	to simplify configuration.
	 * 
	 *
	 **/
	function completeCfg()
	{
		$base = trim($this->cfg->uribase,"/") . '/'; // be sure no double // at end
	    if( !property_exists($this->cfg,'urixmlrpc') )
	    {
	    	$this->cfg->urixmlrpc = $base . 'xmlrpc.cgi';
		}

	    if( !property_exists($this->cfg,'uriview') )
	    {
	    	$this->cfg->uriview = $base . 'show_bug.cgi?id=';
		}
	    
	    if( !property_exists($this->cfg,'uricreate') )
	    {
	    	$this->cfg->uricreate = $base;
		}	    
	}

	/**
     * useful for testing 
     *
     *
     **/
	function getAPIClient()
	{
		return $this->APIClient;
	}

    /**
     * checks id for validity
     *
	 * @param string issueID
     *
     * @return bool returns true if the bugid has the right format, false else
     **/
    function checkBugIDSyntax($issueID)
    {
        return $this->checkBugIDSyntaxNumeric($issueID);
    }

    /**
     * establishes connection to the bugtracking system
     *
     * @return bool 
     *
     **/
    function connect()
    {
    	// echo __METHOD__ . '<br><br>';
		try
		{
			// CRITIC NOTICE for developers
			// $this->cfg is a simpleXML Object, then seems very conservative and safe
			// to cast properties BEFORE using it.
			$this->createAPIClient();
	    $this->connected = true;
			// var_dump($this->APIClient);
			//echo '<br><br><b>END</b> ' . __METHOD__ . '<br><br>';
			
        }
		catch(Exception $e)
		{
			$logDetails = '';
			foreach(array('uribase','apikey') as $v)
			{
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
	function isConnected()
	{
		return $this->connected;
	}


    /**
     * 
     *
     **/
	public function getIssue($issueID)
	{
		// $client = $this->APIClient;

		$issue = null;
		$args = array(array('login' => (string)$this->cfg->username, 
							          'password' => (string)$this->cfg->password,'remember' => 1));

		$resp = array();
		$method = 'User.login';
		$resp[$method] = $this->APIClient->call($method, $args);
		
		$method = 'Bug.get';
		$args = array(array('ids' => array(intval($issueID)), 'permissive' => true));
		$resp[$method] = $this->APIClient->call($method, $args);
		
		$method = 'User.logout';
		$resp[$method] = $this->APIClient->call($method);

		if(count($resp['Bug.get']['faults']) == 0)
		{
			$issue = new stdClass();
		  $issue->IDHTMLString = "<b>{$issueID} : </b>";
			
			$issue->statusCode = 0;
			$issue->statusVerbose = $resp['Bug.get']['bugs'][0]['status'];
			$issue->statusHTMLString = "[$issue->statusVerbose] ";

			$issue->summary = $issue->summaryHTMLString = $resp['Bug.get']['bugs'][0]['summary'];
		}
    else
	  {
	    tLog(__METHOD__ . ' :: ' . $resp['Bug.get']['faults'][0]['faultString'], 'ERROR');
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
	function getIssueStatusCode($issueID)
	{
		$issue = $this->getIssue($issueID);
		return !is_null($issue) ? $issue->statusCode : false;
	}

	/**
	 * Returns status in a readable form (HTML context) for the bug with the given id
	 *
	 * @param string issueID
	 * 
	 * @return string 
	 *
	 **/
	function getIssueStatusVerbose($issueID)
	{
        return $this->getIssueStatusCode($issueID);
	}

	/**
	 *
	 * @param string issueID
	 * 
	 * @return string 
	 *
	 **/
	function getIssueSummaryHTMLString($issueID)
	{
        $issue = $this->getIssue($issueID);
        $str = $issue->summaryHTMLString;
		if($this->guiCfg['use_decoration'])
		{
			$str = "[" . $str . "] ";	
		}
        return $str;
	}

    /**
	 * @param string issueID
     *
     * @return bool true if issue exists on BTS
     **/
    function checkBugIDExistence($issueID)
    {
        if(($status_ok = $this->checkBugIDSyntax($issueID)))
        {
            $issue = $this->getIssue($issueID);
            $status_ok = is_object($issue) && !is_null($issue);
        }
        return $status_ok;
    }


    /**
     * 
     *
     **/
	function createAPIClient()
	{
		// echo __METHOD__ .'<br>';
		try
		{
			$this->APIClient = new Zend_XmlRpc_Client((string)$this->cfg->urixmlrpc);
			$httpClient = new Zend_Http_Client();
			$httpClient->setCookieJar();
			$this->APIClient->setHttpClient($httpClient);
		}
		catch(Exception $e)
		{
			$this->connected = false;
            tLog(__METHOD__ .  $e->getMessage(), 'ERROR');
		}
	}	



    /**
     *
     * @author francisco.mancardi@gmail.com>
     **/
	public static function getCfgTemplate()
  	{
		$template = "<!-- Template " . __CLASS__ . " -->\n" .
					"<issuetracker>\n" .
					"<username>USERNAME</username>\n" .
					"<password>PASSWORD</password>\n" .
					"<uribase>http://bugzilla.mozilla.org/</uribase>\n" .
					"</issuetracker>\n";					
					
		return $template;
  	}
  	
  	
  	function getAccessibleProducts()
  	{
  		$issue = null;
  		$args = array(array('login' => (string)$this->cfg->username, 
  							          'password' => (string)$this->cfg->password,'remember' => 1));
  
  		$resp = array();
  		$method = 'User.login';
  		$resp[$method] = $this->APIClient->call($method, $args);
  		
  		$method = 'Product.get_accessible_products';
  		$itemSet = $this->APIClient->call($method);
  		
  		$method = 'User.logout';
  		$resp[$method] = $this->APIClient->call($method);
      
      return $itemSet; 	  
  	}

  	function getProduct($id)
  	{
  		$issue = null;
  		$args = array(array('login' => (string)$this->cfg->username, 
  							          'password' => (string)$this->cfg->password,'remember' => 1));
  
  		$resp = array();
  		$method = 'User.login';
  		$resp[$method] = $this->APIClient->call($method, $args);
  		
  		$method = 'Product.get';
		  $args = array(array('ids' => array(intval($id))));
  		$itemSet = $this->APIClient->call($method,$args);
  		
  		$method = 'User.logout';
  		$resp[$method] = $this->APIClient->call($method);
      
      return $itemSet; 	  
  	}

}
?>