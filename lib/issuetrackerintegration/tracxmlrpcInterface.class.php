<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * @filesource	tracxmlrpcInterface.class.php
 * @author Francisco Mancardi
 *
 * @internal info
 * http://www.hossainkhan.info/content/trac-xml-rpc-api-reference
 *
 *
 * [Trac Settings]
 * The XmlRpcPlugin plugin should be installed in your Trac.
 * 
 * IMPORTANT NOTICE
 *
 * Anonymous Access
 * You should add the permission of 'TICKET_VIEW' and 'XML_RPC'
 * to the user 'anonymous' in Trac.
 * And use URL without login part
 *
 * Example for TurnKeyLinux trac appliance (@2012-08-26)
 * login into appliance and then:
 * #trac-admin /var/local/lib/trac/hg-helloworld
 * Trac [/var/local/lib/trac/hg-helloworld] permission add anonymous XML_RPC
 * Trac [/var/local/lib/trac/hg-helloworld] permission list anonymous
 *
 * Authenticated Access
 * You should add the permission of 'TICKET_VIEW' and 'XML_RPC'
 * to the user YOU HAVE CONFIGURED on TestLink, in Trac.
 * And use URL WITH login part
 *
 * Example: username fman
 *
 * Example for TurnKeyLinux trac appliance (@2012-08-26)
 * login into appliance and then:
 * #trac-admin /var/local/lib/trac/hg-helloworld
 * Trac [/var/local/lib/trac/hg-helloworld] permission add fman XML_RPC
 * Trac [/var/local/lib/trac/hg-helloworld] permission list fman
 *
 * @internal revisions
**/

// use phpxmlrpc because support HTTPS, while incutio NO.
require_once(TL_ABS_PATH . 'third_party/phpxmlrpc/lib/xmlrpc.inc');

class tracxmlrpcInterface extends issueTrackerInterface
{
    private $APIClient;

	// this info has been get from ticket.get documentation
	const TICKET_GET_ID_IDX = 0;
	const TICKET_GET_ATTRIBUTES_IDX = 3;
	 
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
		$prop2check = array('urixmlrpc' => 'xmlrpc', 'uriview' => 'ticket/', 'uricreate' => 'newticket/');
		foreach($prop2check as $prop => $add2base)
		{
	    if( !property_exists($this->cfg,$prop) )
	    {
	      	$this->cfg->$prop = $base . $add2base;
		  }
	  }
	}

	/**
    * useful for testing 
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
	  try
  	{
	  	// CRITIC NOTICE for developers
		  // $this->cfg is a simpleXML Object, then seems very conservative and safe
		  // to cast properties BEFORE using it.
		  $this->createAPIClient();
	    $this->connected = true;
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
		// array ticket.get(int id) Fetch a ticket. 
		// Returns [id, time_created, time_changed, attributes]. 
		// attributes is following map (@20120826)
		//		
		// ------------------------------------------
		// key          | value
		// ------------------------------------------
		// status 		  | new
		// description 	| MERCURIAL FIRST TICKET
		// reporter 	  | admin
		// cc 			    | [empty string]
		// component 	  | component1
		// summary 		  | MERCURIAL FIRST TICKET
		// priority 	  | major
		// keywords 	  | [empty string]
		// version 		  | [empty string]
		// milestone 	  | [empty string]
		// owner 		    | somebody
		// type 		    | defect
		//	
		
    $resp = $this->sendCmd('ticket.get', $issueID);
		if( $resp == false )
		{
			$issue = null;
		}
		else
		{
			$attrib = $resp[self::TICKET_GET_ATTRIBUTES_IDX];
			$issue = new stdClass();
		  $issue->IDHTMLString = "<b>{$issueID} : </b>";
			$issue->statusCode = 0;
			$issue->statusVerbose = $attrib['status'];
			$issue->statusHTMLString = "[$issue->statusVerbose] ";
			$issue->summary = $issue->summaryHTMLString = $attrib['summary'];
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
    $dBugLabel = array('label' => __METHOD__);
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
		try
		{
      // Create a new connection with the TRAC-server.
      $this->APIClient = new xmlrpc_client($this->cfg->urixmlrpc);
            
      // Set the credentials to use to log in.
			$this->APIClient->setCredentials($this->cfg->username, $this->cfg->password);

			// Disable certificate checking. Don't need to check it. 
			$this->APIClient->verifyhost = false;
			$this->APIClient->verifypeer = false;
		}
		catch(Exception $e)
		{
			$this->connected = false;
      tLog(__METHOD__ .  $e->getMessage(), 'ERROR');
		}
	}	



  function sendCmd($cmd, $id)
  {
    	$param = new xmlrpcval(intval($id));
    	$msg = new xmlrpcmsg($cmd);
    	$msg->addParam($param);
    	
    	// Send request with timeout disabled
    	$response = $this->APIClient->send($msg, 0);
    	if (!$response->errno) 
    	{
    		$response = php_xmlrpc_decode($response->val);
    	} 
    	else 
    	{
    		$response = false;
    		tLog(__METHOD__ .  'Error Number:' . $response->errno, 'ERROR');
    	}
    	return $response;
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
					      "<!-- READ CAREFULLY --->\n" .
                "<!-- The XmlRpcPlugin plugin should be installed in your Trac. \n -->" .
                "<!-- Anonymous Access: \n". 
                "     You should add the permission of 'TICKET_VIEW' and 'XML_RPC' \n" .
                "     to the user 'anonymous' in Trac. \n" .
                "     Then you HAVE TO USE THIS URI. --> \n" .
					      "<uribase>'http://<YourTracServer>/<YourTracProjectName</uribase>\n" .
                "<!-- Authenticated Access: \n". 
                "     You should add the permission of 'TICKET_VIEW' and 'XML_RPC' \n" .
                "     to the user you HAVE DECLARED HERE,  in Trac. \n" .
                "     Then you HAVE TO USE THIS URI. --> \n" .
					      "<uribase>'http://<YourTracServer>/<YourTracProjectName/login</uribase>\n" .
					      "</issuetracker>\n";					
		return $template;
  }
}
?>