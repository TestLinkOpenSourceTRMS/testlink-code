<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * @filesource	int_bugzillaxmlrpc.php
 * @author		Francisco Mancardi - francisco.mancardi@gmail.com
 * 
 * @internal thanks to 	http://petehowe.co.uk/2010/example-of-calling-the-bugzilla-api-using-php-zend-framework/
 * 
 * @internal revisions
 * @since 1.9.4
 *
 *
**/
require_once('Zend/Loader/Autoloader.php');
Zend_Loader_Autoloader::getInstance();

/** Interface name */
define('BUG_INTERFACE_CLASSNAME',"bugzillaXMLRPCInterface");

class bugzillaXMLRPCInterface extends bugtrackingInterface
{
	// members to store the bugtracking information
	var $username = BUG_TRACK_USERNAME;
	var $password = BUG_TRACK_PASSWORD;
	var $showBugURL = BUG_TRACK_SHOW_ISSUE_HREF;
	var $enterBugURL = BUG_TRACK_ENTER_ISSUE_HREF;
	var $XMLRPCServer = BUG_TRACK_XMLRPC_HREF;


	/**
	 *
	 **/
	function getIssue($id)
	{
		$client = $this->getClient();

		$args = array(array('login' => $this->username, 'password' => $this->password,'remember' => 1));
		$resp = array();
		$method = 'User.login';
		$resp[$method] = $client->call($method, $args);
		
		$method = 'Bug.get';
		$args = array(array('ids' => array(intval($id)), 'permissive' => true));
		$resp[$method] = $client->call($method, $args);
		
		$method = 'User.logout';
		$resp[$method] = $client->call($method);

		return $resp['Bug.get'];
	}

	
	/**
	 * Return the URL to the bugtracking page for viewing 
	 * the bug with the given id. 
	 *
	 * @param int id the bug id
	 * 
	 * @return string returns a complete URL to view the bug
	 *
	 **/
	function buildViewBugURL($id)
	{
		return $this->showBugURL . $id;		
	}
	
	/**
	 * Returns the status of the bug with the given id
	 * this function is not directly called by TestLink. 
	 *
	 * @return string returns the status of the given bug.
	 **/
	function getBugStatus($id)
	{
		$issue = $this->getIssue($id);
		return (count($issue['faults']) == 0) ? $issue['bugs'][0]['status'] : null;
	}
	
	/**
	 * Returns the bug summary in a human readable format
	 *
	 * @return string returns the summary (in readable form) of the given bug
	 *
	 **/
	function getBugSummaryString($id)
	{
		$issue = $this->getIssue($id);
		return (count($issue['faults']) == 0) ? $issue['bugs'][0]['summary'] : null;
	}	

	
	/**
	 * Returns the status in a readable form (HTML context) for the bug with the given id
	 *
	 * @param int id the bug id
	 * 
	 * @return string returns the status (in a readable form) of the given bug 
	 *
	 **/
	function getBugStatusString($id)
	{
		$status = $this->getBugStatus($id);
		
		// if the bug wasn't found the status is null and we simply display the bugID
		$str = htmlspecialchars($id);
		if (!is_null($status))
		{
			// strike through all bugs that have a resolved, verified, or closed status.. 
			if('RESOLVED' == $status || 'VERIFIED' == $status || 'CLOSED' == $status)
			{
			   $str = "<del>" . htmlspecialchars($id). "</del>";
			}   
		}
		return $str;
	}
	
	/**
	 * checks is bug id is present on BTS
	 * 
	 * @return bool 
	 **/
	function checkBugID_existence($id)
	{
		$issue = $this->getIssue($id);
		$exists = (count($issue['faults']) == 0);
		return $exists;
	}



    /**
     * 
     *
     **/
	function getClient()
	{
		$client = new Zend_XmlRpc_Client($this->XMLRPCServer);
		$httpClient = new Zend_Http_Client();
		$httpClient->setCookieJar();
		$client->setHttpClient($httpClient);
		
		return $client;
	}	


    /**
     * Mock method overloaded from the bugtrackingInterface.
     * Fakes establishing the database connection to the bugtracking system,
     * while we are NOT USING Database DIRECT Access
     *
     **/
    function connect() 
    {
        $this->Connected = true;
        return $this->Connected;
    }

    /**
     * Mock method overloaded from the bugtrackingInterface.
     * Returns the fake state of the db connection, 
     * while we are NOT USING Database DIRECT Access
     *
     **/
    function isConnected() 
    {
        return $this->Connected;
    }


}
?>
