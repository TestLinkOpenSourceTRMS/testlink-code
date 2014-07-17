<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * @filesource	gforgesoapInterface.class.php
 * @author Francisco Mancardi
 *
 *
 * @internal IMPORTANT NOTICE
 *			 we use issueID on methods signature, to make clear that this ID 
 *			 is HOW issue is identified on Issue Tracker System, 
 *			 not how is identified internally at DB	level on TestLink
 *
 * @internal revisions
 * @since 1.9.4
 * 20120220 - franciscom - TICKET 4904: integrate with ITS on test project basis
 * 20140120 - aurelien.tisne@c-s.fr - finish the soap interface for gforge
 **/
define('STATUSTYPEID', '7'); // ID of type status

class gforgesoapInterface extends issueTrackerInterface
{

	protected $APIClient;
	protected $authToken;
	protected $statusDomain = array();
	protected $statusVerbose = "";
	protected $l18n;
	protected $labels = array('duedate' => 'its_duedate_with_separator');
	
	private $soapOpt = array("connection_timeout" => 1, 'exceptions' => 1);
	
	/**
	 * Construct and connect to BTS.
	 *
	 * @param str $type (see tlIssueTracker.class.php $systems property)
	 * @param xml $cfg
	 **/
	function __construct($type,$config)
	{
		$this->interfaceViaDB = false;
		$this->defaultResolvedStatus = array();
		$this->defaultResolvedStatus[] = 'Resolved';
		$this->defaultResolvedStatus[] = 'Closed';
		$this->defaultResolvedStatus[] = 'Fixed';
		$this->methodOpt['buildViewBugLink'] = array('addSummary' => true, 'colorByStatus' => false);

		$this->setCfg($config);
		$this->completeCfg();
		$this->setResolvedStatusCfg();
		$this->connect();
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
		$base = trim($this->cfg->uribase,"/") . '/' ;
		if( !property_exists($this->cfg,'uriwsdl') )
			{
				$this->cfg->uriwsdl = $base . 'gf/xmlcompatibility/soap5/?wsdl';
			}
		if( !property_exists($this->cfg,'uriview') )
			{
				$this->cfg->uriview = $base . 'gf/project/'.$_SESSION['testprojectName'].'/tracker/?action=TrackerItemEdit&amp;tracker_item_id=';
			}
		// $base/gf/project/valati/tracker/?action=TrackerItemAdd&tracker_id=1841
		if( !property_exists($this->cfg,'uricreate') )
			{
				$this->cfg->uricreate = $base . 'gf/project/'.$_SESSION['testprojectName'].'/tracker/';
			}
	}


	function getAuthToken()
	{
		return $this->authToken;
	}


	/**
	 * status code (integer) for issueID 
	 *
	 * 
	 * @param string issueID
	 *
	 * @return 
	 **/
	public function getIssueStatusCode($issueID)
	{
		$issue = $this->getIssue($issueID);
		return (!is_null($issue) && is_object($issue)) ? $issue->statusCode : false;
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
		$issue = $this->getIssue($issueID);
		return (!is_null($issue) && is_object($issue)) ? $issue->statusVerbose : null;
	}
	
	
	/**
	 *
	 * @param string issueID
	 * 
	 * @return string returns the bug summary if bug is found, else null
	 **/
	function getIssueSummary($issueID)
	{
		$issue = $this->getIssue($issueID);
		return (!is_null($issue) && is_object($issue)) ? iconv("UTF-8", "ISO-8859-1", $issue->summary) : null;
	}
	
	/**
	 * @internal precondition: TestLink has to be connected to BTS 
	 *
	 * @param string issueID
	 *
	 **/
	function getIssue($issueID)
	{
		try
			{
				$issue = $this->APIClient->getTrackerItemFull($this->authToken, $issueID);
				//new dBug($issue);
						
				$target = array();
				$dataID = array();
				foreach($issue->extra_field_data as $efd)
					{
						$target[] = $efd->tracker_extra_field_id;
						$dataID[] = $efd->tracker_extra_field_data_id;
					}
						
				// retrieve Status extra field (type 7)
				$extraFields = $this->APIClient->getTrackerExtraFields($this->authToken,
																										$issue->tracker_id,
																										STATUSTYPEID,
																										null, null, null, null, null, null);
				//new dBug($extraFields);
				if (!is_null($extraFields))
					$statusObj = $extraFields[0];
				else
					echo "Cannot find the status extra field for the tracker ".$issue->tracker_id;

				// retrieve all status values for the tracker
				$this->statusDomain = $this->APIClient->getTrackerExtraFieldElements($this->authToken, 
																																						 $statusObj->tracker_extra_field_id,
																																						 '', -1, -1);
				//new dBug($this->statusDomain);
						
				// We are going to have a set of standard properties
				$issue->IDHTMLString = "<b>{$issueID} : </b>";
				$issue->statusCode = $issue->status_id;
				$issue->statusVerbose = $this->getStatusLabel($issue, $statusObj);
				$this->statusVerbose = $issue->statusVerbose;
				$issue->statusHTMLString = $this->buildStatusHTMLString($issue->statusCode);
				$issue->summaryHTMLString = $this->buildSummaryHTMLString($issue);
				$issue->isResolved = (array_search($issue->statusVerbose, $this->resolvedStatus->byName) > 0);
				//new dBug($issue);
			}
		catch (Exception $e)
			{
				tLog("GFORGE Ticket ID $issueID - " . $e->getMessage(), 'WARNING');
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
		return $this->checkBugIDSyntaxString($issueID);
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
		$this->interfaceViaDB = false;
		$op = $this->getClient(array('log' => true));
		if( ($this->connected = $op['connected']) )
			{ 
				// OK, we have got WSDL => server is up and we can do SOAP calls, but now we need 
				// to do a simple call with user/password only to understand if we are really connected
				try
					{
						$this->APIClient = $op['client'];
						$this->authToken = $this->APIClient->login((string)$this->cfg->username, (string)$this->cfg->password);
						$this->l18n = init_labels($this->labels);
					}
				catch (SoapFault $f)
					{
						$this->connected = false;
						tLog(__CLASS__.".".__FUNCTION__ . " - SOAP Fault: (code: {$f->getCode()}, string: {$f->getMessage()}, detail: {$f->detail})","ERROR");
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
				$res['msg'] = "SOAP Fault: (code: {$f->getCode()}, string: {$f->getMessage()})";
				if($my['opt']['log'])
					{
						tLog(__CLASS__.".".__FUNCTION__ . " - SOAP Fault: (code: {$f->getCode()}, string: {$f->getMessage()}, detail: {$f->detail})","ERROR");
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
		$ret = null;
		if (!is_null($date2parse))
			{
				$ret = date_parse($date2parse);
				$ret = ((gmmktime(0, 0, 0, $ret['month'], $ret['day'], $ret['year'])));
				$ret = $this->l18n['duedate'] . gmstrftime("%d %b %Y",($ret));
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
			"<username>GFORGE LOGIN NAME</username>\n" .
			"<password>GFORGE PASSWORD</password>\n" .
			"<uribase>http://gforge.org/</uribase>\n" .
			"<!-- Configure This if you want NON STANDARD BEHAVIOR for considered issue resolved -->\n" .
			"<resolvedstatus>\n" .
			"<status>Resolved</status>\n" .
			"<status>Closed</status>\n" .
			"</resolvedstatus>\n" .
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


 /**
	 * Set resolved status from config
	 *
	 * default implementation must be overloaded: resolved status code is moving
	 * 
	 * @author Aurelien TISNE <aurelien.tisne@c-s.fr>
	 **/
	public function setResolvedStatusCfg()
	{
		if( property_exists($this->cfg,'resolvedstatus') )
		{
			$statusCfg = (array)$this->cfg->resolvedstatus;
		}
		else
		{
			$statusCfg['status'] = $this->defaultResolvedStatus;
		}
		$this->resolvedStatus = new stdClass();
		foreach($statusCfg['status'] as $cfx)
		{
			$this->resolvedStatus->byName[] = $cfx;
		}
	}

	/**
	 * Retrieve the status label of an issue
	 *
	 * @param $issue Current ITS issue
	 * @param $statusObj Extra field 'Status' of the issue $issue
	 *
	 * @return string The label of the status
	 *
	 * @author Aurelien TISNE <aurelien.tisne@c-s.fr>
	 **/
	function getStatusLabel($issue, $statusObj)
	{
		$status="Not found";

		foreach($issue->extra_field_data as $efd) {
			if ($efd->tracker_extra_field_id == $statusObj->tracker_extra_field_id) {
				$statusValueID = $efd->field_data;
				break;
			}
		}
		foreach($this->statusDomain as $statusElement) {
			if ($statusElement->element_id == $statusValueID) {
				$status = $statusElement->element_name;
						break;
			}
		}
		return iconv("UTF-8", "ISO-8859-1",$status);
	}

	/**
	 *
	 **/
	function buildStatusHTMLString($statusCode)
	{
		$str = $this->statusVerbose;
		if ($statusCode == 0) // Closed type status
			{
				$str = "<del>" . $str . "</del>";
			}
		return "[{$str}] ";
	}

	/**
	 *
	 **/
	function buildSummaryHTMLString($issue)
	{
		$summary = iconv("UTF-8", "ISO-8859-1", $issue->summary);
/* no duedate in standard fields
		$strDueDate = $this->helperParseDate($issue->duedate);
		if( !is_null($strDueDate) )
			{ 
				$summary .= "<b> [$strDueDate] </b> ";
			}
*/
		return $summary;
	}

	public static function checkEnv()
	{
		$ret = array();
		$ret['status'] = extension_loaded('soap');
		$ret['msg'] = $ret['status'] ? 'OK' : 'You need to enable SOAP extension';
		return $ret;
	}

	/**
	 * Creates an association in GForge between a tracker item and a Testlink execution test
	 *
	 * @return 
	 *
	 * @version 1.0
	 * @author Daniel Nistor <daniel.nistor@c-s.ro>
	 **/
	function createBugAssociation($bugId, $testId)
	{
		if (!$this->isConnected())
			return null;
		
		$status = null;
		try{
			$status =	 $this->APIClient->addAssociation($this->authToken, 'Testlink', $testId, 'trackeritem', $bugId, 'Created using Testlink');
		} catch(SoapFault $f) {
			tLog(__CLASS__.".".__FUNCTION__ . " - SOAP Fault: (code: {$f->getCode()}, string: {$f->getMessage()}, detail: {$f->detail})","ERROR");
		}
		
		return $status; 
	}
	
	/**
	 * Deletes an association in GForge between a tracker item and a Testlink execution test
	 *
	 * @return 
	 *
	 * @version 1.0
	 * @author Daniel Nistor <daniel.nistor@c-s.ro>
	 **/
	function deleteBugAssociation($bugId, $testId)
	{
		if (!$this->isConnected())
			return null;
		
		$status = null;
		try{
			$status =	 $this->APIClient->deleteAssociation($this->authToken, 'Testlink', $testId, 'trackeritem', $bugId);
		} catch(SoapFault $f) {
			tLog(__CLASS__.".".__FUNCTION__ . " - SOAP Fault: (code: {$f->getCode()}, string: {$f->getMessage()}, detail: {$f->detail})","ERROR");
		}

		return $status;
	}

	/*		
				getTrackerItem() 

				$issue (object)
				tracker_id			371
				tracker_item_id		7091
				status_id				0
				priority			3
				submitted_by		14030
				open_date				2011-03-31 03:00:00
				close_date			2011-04-07 03:00:00
				summary				Add support for Gforge6
				details				Add support for Gforge As 6.0 (hierarchical tracker items)
				last_modified_date	2011-05-26 20:31:40
				last_modified_by	14030
				sort_order			0
				parent_id				0
				has_subitems		[empty string]
				subitems_count		0		 
	*/
		
}
?>
