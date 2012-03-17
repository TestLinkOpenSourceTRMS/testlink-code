<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * @filesource	mantissoapInterface.class.php
 * @author Francisco Mancardi
 *
 * @internal IMPORTANT NOTICE
 *			 we use issueID on methods signature, to make clear that this ID 
 *			 is HOW issue in identified on Issue Tracker System, 
 *			 not how is identified internally at DB	level on TestLink
 *
 * @internal revisions
 * @since 1.9.4
 * 20120220 - franciscom - TICKET 4904: integrate with ITS on test project basis 
**/
class jirasoapInterface extends issueTrackerInterface
{

    protected $APIClient;
	protected $authToken;
    protected $statusDomain = array();

	private $soapOpt = array("connection_timeout" => 1, 'exceptions' => 1);
	
	
	/**
	 * Returns URL to the bugtracking page for viewing ticket
	 *
	 * @param string issueID
	 * 
	 * @return string 
	 **/
	function buildViewBugURL($issueID)
	{
		return $this->cfg->uriview . urlencode($issueID);
	}

	/**
	 *
	 * @param string issueID
	 * @param boolean addSummary
	 * 
	 * @return string 
	 **/
    function buildViewBugLink($issueID,$addSummary=false)
    {
        $dummy = $this->getBugStatusString($issueID);
        $link = "<a href='" .$this->buildViewBugURL($issueID) . "' target='_blank'>";
        $link .= is_null($dummy) ? $issueID : $dummy;
        
        if($addSummary)
        {
            $dummy = $this->getBugSummaryString($issueID);
            if(!is_null($dummy))
            {
                $link .= " - " . $dummy;
            }
        }
        $link .= "</a>";
        return $link;
    }

	
	/**
	 * Returns the status for issueID
	 * this function is not directly called by TestLink. 
	 *
	 * @param string issueID
	 *
	 * @return 
	 **/
	function getBugStatus($issueID)
	{
		$issue = $this->getIssue($issueID);
		return !is_null($issue) ? $issue->status : false;
	}

		
	/**
	 * Returns status in a readable form (HTML context) for the bug with the given id
	 *
	 * @param string issueID
	 * 
	 * @return string 
	 *
	 **/
	function getBugStatusString($issueID)
	{
        $str = "Ticket ID - " . $issueID . " - does not exist in BTS";
        $issue = $this->getIssue($issueID);
        if (!is_null($issue))
        {
            $str = array_search($issue->status, $this->statusDomain);
			if (strcasecmp($str, 'closed') == 0 || strcasecmp($str, 'resolved') == 0 )
            {
                $str = "<del>" . $str . "</del>";
            }
            $str = "<b>" . $issueID . ": </b>[" . $str  . "] " ;
        }
        return $str;
	}
	
	
	/**
	 *
	 * @param string issueID
	 * 
	 * @return string returns the bug summary if bug is found, else null
	 **/
    function getBugSummaryString($issueID)
    {
        $summary = null;
        if(!is_null(($issue = $this->getIssue($issueID))))
        {
            $summary = $issue->summary . '<b> [' . $this->helperParseDate($issue->duedate) . ']</b> ';
        }
        return $summary;
    }
	
    /**
     * @internal precondition: TestLink has to be connected to Jira 
     *
	 * @param string issueID
     *
     **/
    function getIssue($issueID)
    {
        try
        {
            $issue = $this->APIClient->getIssue($this->authToken, $issueID);
        }
        catch (Exception $e)
        {
         	tLog("JIRA Ticket ID $issueID - " . $e->getMessage(), 'WARNING');
			$issue = null;
        }
        return $issue;
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
        $status_ok = !(trim($issueID) == "");
        if($status_ok)
        {
            $forbidden_chars = '/[!|�%&()\/=?]/';
            if (preg_match($forbidden_chars, $issueID))
            {
                $status_ok = false;
            }
        }
        return $status_ok;
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
            $status_ok = !is_null($issue);
        }
        return $status_ok;
    }

    /**
     * establishes connection to the bugtracking system
     *
     * @return bool returns true if the soap connection was established and the
     * wsdl could be downloaded, false else
     *
     **/
    function connect()
    {
		$op = $this->getClient(array('log' => true));
		if( ($this->connected = $op['connected']) )
		{ 
			// OK, we have got WSDL => server is up and we can do SOAP calls, but now we need 
			// to do a simple call with user/password only to understand if we are really connected
			try
			{
				$this->APIClient = $op['client'];
            	$this->authToken = $this->APIClient->login($this->cfg->username, $this->cfg->password);
            	$statusSet = $op['client']->getStatuses($this->authToken);
	            foreach ($statusSet as $key => $pair)
    	        {
        	    	$this->statusDomain[$pair->name]=$pair->id;
            	}
			}
			catch (SoapFault $f)
			{
				$this->connected = false;
				tLog(__CLASS_ . " - SOAP Fault: (code: {$f->faultcode}, string: {$f->faultstring})","ERROR");
			}
		}
        return $this->connected;
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
	function getClient($opt=null)
	{
		// IMPORTANT NOTICE - 2012-01-06 - If you are using XDEBUG, Soap Fault will not work
		$res = array('client' => null, 'connected' => false, 'msg' => 'generic ko');
		$my['opt'] = array('log' => false);
		$my['opt'] = array_merge($my['opt'],(array)$opt);
		
		try
		{
			// IMPORTANT NOTICE
			// $this->cfg is a simpleXML object, then is ABSOLUTELY CRITICAL 
			// DO CAST any member before using it.
			// If we do following call WITHOUT (string) CAST, SoapClient() fails
			// complaining '... wsdl has to be an STRING or null ...'
			$res['client'] = new SoapClient((string)$this->cfg->uriwsdl,$this->soapOpt);
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
     *
     * @author francisco.mancardi@gmail.com>
     **/
    private function helperParseDate($date2parse)
    {
    	$ret = "No Date";
        if (!is_null($date2parse))
        {
            $ret = date_parse($date2parse);
            $ret = ((gmmktime(0, 0, 0, $ret['month'], $ret['day'], $ret['year'])));
            $ret = gmstrftime("%d %b %Y",($ret));
        }
        return $ret ;
    }



    /**
     *
     * @author francisco.mancardi@gmail.com>
     **/
	public static function getCfgTemplate()
  	{
		$template = "<!-- Template " . __CLASS__ . " -->\n" .
					"<issuetracker>\n" .
					"<username>JIRA LOGIN NAME</username>\n" .
					"<password>JIRA PASSWORD</password>\n" .
					"<uribase>http://testlink.atlassian.net/</uribase>\n" .
					"<uriwsdl>http://testlink.atlassian.net/rpc/soap/jirasoapservice-v2?wsdl</uriwsdl>\n" .
					"<uriview>testlink.atlassian.net/browse/</uriview>\n" .
					"<uricreate>testlink.atlassian.net/secure/CreateIssue!default.jspa</uricreate>\n" .
					"</issuetracker>\n";
		return $template;
  	}

 	
    /**
     *
     * @author francisco.mancardi@gmail.com>
     **/
	public function getStatusDomain()
  	{
		return $this->statusDomain;
  	}
}
?>