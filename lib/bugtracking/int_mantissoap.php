<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * @filesource	int_mantissoap.php
 * @author Francisco Mancardi
 *
 * Constants used throughout TestLink are defined within this file
 * they should be changed for your environment
 *
 * @internal revisions
 *
**/
/** Interface name */
define('BUG_INTERFACE_CLASSNAME',"mantisSOAPInterface");

class mantisSOAPInterface extends bugtrackingInterface
{
	// members to store the bugtracking information
	var $username = BUG_TRACK_USERNAME;
	var $password = BUG_TRACK_PASSWORD;
	var $showBugURL = BUG_TRACK_SHOW_ISSUE_HREF;
	var $enterBugURL = BUG_TRACK_ENTER_ISSUE_HREF;
	var $wsdlURI = BUG_TRACK_SOAP_HREF;
	
	// Copied from mantis configuration
	private $status_color = array('new'          => '#ffa0a0', # red,
                                  'feedback'     => '#ff50a8', # purple
                                  'acknowledged' => '#ffd850', # orange
                                  'confirmed'    => '#ffffb0', # yellow
                                  'assigned'     => '#c8c8ff', # blue
                                  'resolved'     => '#cceedd', # buish-green
                                  'closed'       => '#e8e8e8'); # light gray
	
	
	private $soapOpt = array("connection_timeout" => 1, 'exceptions' => 1);
	
	
	/**
	 * Return the URL to the bugtracking page for viewing 
	 * the bug with the given id. 
	 *
	 * @param int id the bug id
	 * 
	 * @return string returns a complete URL to view the bug
	 **/
	function buildViewBugURL($id)
	{
		return $this->showBugURL.urlencode($id);
	}
	
	
    /**
     * establishes the soap connection to the bugtracking system
     *
     * @return bool returns true if the soap connection was established and the
     * wsdl could be downloaded, false else
     *
     **/
    function connect()
    {
    	// echo __CLASS__ . "::" . __FUNCTION__ . '<br>';
		$op = $this->getClient(array('log' => true));
		if( ($this->Connected = $op['connected']) )
		{ 
			// OK, we have got WSDL => server is up and we can do SOAP calls, but now we need 
			// to do a simple call with user/password only to understand if we are really connected
			try
			{
				$x = $op['client']->mc_enum_status($this->username,$this->password);
			}
			catch (SoapFault $f)
			{
				$this->Connected = false;
				tLog("SOAP Fault: (code: {$f->faultcode}, string: {$f->faultstring})","ERROR");
			}
		}
        return $this->Connected;
    }


    /**
     * 
     *
     **/
	function getClient($opt=null)
	{
		// IMPORTANT NOTICE - 2012-01-06 - If you are using XDEBUG, Soap Fault will not work
		$res = array('client' => null, 'connected' => false, 'msg' => 'generic ko');
		$my['opt'] = array('log' => false);
		$my['opt'] = array_merge($my['opt'],(array)$opt);
		
		try
		{
			$res['client'] = new SoapClient($this->wsdlURI,$this->soapOpt);
			$res['connected'] = true;
			$res['msg'] = 'iupi!!!';
		}
		catch (SoapFault $f)
		{
			$res['connected'] = false;
			$res['msg'] = "SOAP Fault: (code: {$f->faultcode}, string: {$f->faultstring})";
			if($my['opt']['log'])
			{
				tLog("SOAP Fault: (code: {$f->faultcode}, string: {$f->faultstring})","ERROR");
			}	
		}
		return $res;
	}	
	
	/**
	 * Returns the status of the bug with the given id
	 * this function is not directly called by TestLink. 
	 *
	 * @return string returns the status of the given bug if exists on Mantis, or false else
	 **/
	function getBugStatus($id)
	{
		$status = false;
		$issue = $this->getIssue($id);
		if(!is_null($issue))
		{
			$status = $issue->status->name;						
		}
		return $status;
	}

		
	/**
	 * Returns the status in a readable form (HTML context) for the bug with the given id
	 *
	 * @param int id the bug id
	 * 
	 * @return string returns the status (in a readable form) of the given bug if the bug
	 * 		was found , else false
	 **/
	function getBugStatusString($id)
	{
		$status = $this->getBugStatus($id);
		$str = htmlspecialchars($id);
		// if the bug wasn't found the status is null and we simply display the bugID
		if ($status !== false)
		{
			//the status values depends on your mantis configuration at config_inc.php in $g_status_enum_string, 
			//below is the default:
			//'10:new,20:feedback,30:acknowledged,40:confirmed,50:assigned,80:resolved,90:closed'
			// With this replace if user configure status on mantis with blank we do not have problems
			$status = str_replace(" ", "_", $status);
			$status_i18n = lang_get('issue_status_' . $status);
			$str = "[" . $status_i18n . "] " . $id . "";	
		}
		return $str;
	}
	
	/**
	 * Fetches the bug summary from the matnis db
	 *
	 * @param int id the bug id
	 * 
	 * @return string returns the bug summary if bug is found, else false
	 **/
	function getBugSummaryString($id)
	{
		$summary = null;
		$issue = $this->getIssue(intval($id));
		if(!is_null($issue))
		{
			$summary = $issue->summary;						
		}
		return $summary;
	}

 
  	/**
	 * checks is bug id is present on BTS
	 * 
	 * @return integer returns 1 if the bug with the given id exists 
	 **/
	function checkBugID_existence($id)
	{
		static $client;
		
		if (!$this->isConnected())
		{
			return 0;  // >>>---> bye!
		}
		
		if(is_null($client))
		{
			$dummy = $this->getClient();
			$client = $dummy['client'];
		}
		
		$status_ok = 0;
		$id = intval($id);
		try
		{
			$status_ok = $client->mc_issue_exists($this->username,$this->password,$id) ? 1 : 0;
		}
		catch (SoapFault $f) 
		{
			// from http://www.w3schools.com/soap/soap_fault.asp
			// VersionMismatch 	- 	Found an invalid namespace for the SOAP Envelope element
			// MustUnderstand 	- 	An immediate child element of the Header element, 
			//						with the mustUnderstand attribute set to "1", was not understood
			// Client 			-	The message was incorrectly formed or contained incorrect information
			// Server 			-	There was a problem with the server so the message ...
			
			// @ŢODO - 20120106 - need to think how to manage this situation in a better way
		}
		return $status_ok;
	}



	
  	/**
	 *
	 * 
	 *
	 **/
	function buildViewBugLink($bugID,$bWithSummary = false)
  	{
      $s = parent::buildViewBugLink($bugID, $bWithSummary);
      $status = $this->getBugStatus($bugID);
      $color = isset($this->status_color[$status]) ? $this->status_color[$status] : 'white';
      $title = lang_get('access_to_bts');  
      return "<div  title=\"{$title}\" style=\"display: inline; background: $color;\">$s</div>";
  	}


	/**
	 * 
	 * 
	 *
	 * 
	 **/
	function getIssue($id)
	{
		static $client;
		
		if (!$this->isConnected())
		{
			return false;
		}
		
		if(is_null($client))
		{
			$dummy = $this->getClient();
			$client = $dummy['client'];
		}

		$status = false;
		$id = intval($id);
		$issue = null;

		try
		{
			if($client->mc_issue_exists($this->username,$this->password,$id))
			{
				$issue = $client->mc_issue_get($this->username,$this->password,$id);
			}
		}
		catch (SoapFault $f) 
		{
			// from http://www.w3schools.com/soap/soap_fault.asp
			// VersionMismatch 	- 	Found an invalid namespace for the SOAP Envelope element
			// MustUnderstand 	- 	An immediate child element of the Header element, 
			//						with the mustUnderstand attribute set to "1", was not understood
			// Client 			-	The message was incorrectly formed or contained incorrect information
			// Server 			-	There was a problem with the server so the message ...
			
			// @ŢODO - 20120106 - need to think how to manage this situation in a better way
		}
		return $issue;
	}


	function isConnected()
	{
    	// echo __CLASS__ . "::" . __FUNCTION__ .'<br>';
    	// echo '$this->Connected' . "::" . $this->Connected .'<br>';
		return $this->Connected;
	}


}
?>
