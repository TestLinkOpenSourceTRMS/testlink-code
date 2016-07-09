<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * @filesource	fogbugzdbInterface.class.php
 *
 * @internal revision
 * @since 1.9.4
 * 20120220 - franciscom - TICKET 4904: integrate with ITS on test project basis 
**/
class fogbugzdbInterface extends issueTrackerInterface
{

	/**
	 * Construct and connect to BTS.
	 *
	 * @param str $type (see tlIssueTracker.class.php $systems property)
	 * @param xml $cfg
	 **/
	function __construct($type,$config,$name)
	{
	    parent::__construct($type,$config,$name);

		$this->interfaceViaDB = true;
		$this->methodOpt['buildViewBugLink'] = array('addSummary' => true, 'colorByStatus' => true);
	    $this->guiCfg = array('use_decoration' => true);
	}


	
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
		return $this->cfg->uriview.urlencode($id);
	}

	/**
	 *
	 **/
	function getIssue($id)
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
		if (!$this->isConnected())
		{
			return false;
		}
		$sql = "/* $debugMsg */ " . 
			   " SELECT Bug.ixBug AS id, Bug.ixStatus AS status, Status.sStatus AS statusVerbose," .
			   " Bug.sTitle AS summary, Bug.fOpen AS openStatus " .
			   " FROM Bug JOIN Status ON Status.ixStatus = Bug.ixStatus " .
		       " WHERE Bug.ixBug=" . intval($id);
		
		$rs = $this->dbConnection->fetchRowsIntoMap($sql,'id');
		
		$issue = null;
		if( !is_null($rs) )	
		{
	        $issue = new stdClass();
	        $issue->id = $id;
			$issue->openStatus = $rs[$id]['openStatus'];
			$issue->IDHTMLString = "<b>{$id} : </b>";
			$issue->statusCode = $rs[$id]['status']; 
			$issue->statusVerbose = $rs[$id]['statusVerbose'];

			$issue->statusHTMLString = $this->buildStatusHTMLString($issue);
			$issue->statusColor = isset($this->status_color[$issue->statusVerbose]) ? 
								  $this->status_color[$issue->statusVerbose] : 'white';
	
			$issue->summaryHTMLString = $rs[$id]['summary'];
		}
		return $issue;	
	}


	/**
	 * Returns the status of the bug with the given id
	 * this function is not directly called by TestLink. 
	 *
	 * @return string returns the status of the given bug (if found in the db), or false else
	 **/
	function getBugStatus($id)
	{
		if (!$this->isConnected())
		{
			return false;
		}
		$issue = $this->getIssue($id);
		return is_null($issue) ? $issue : $issue->statusVerbose;
	}

 
  	/**
	 * checks is bug id is present on BTS
	 * 
	 * @return integer returns 1 if the bug with the given id exists 
	 **/
	function checkBugIDExistence($id)
	{
		$status_ok = 0;	
		$issue = $this->getIssue($id);
		return !is_null($issue) ? 1 : 0; 
	}	
	

	function buildViewBugLink($bugID,$addSummary = false)
  	{
      $linkVerbose = parent::buildViewBugLink($bugID, $addSummary);
      $status = $this->getBugStatus($bugID);
      $color = isset($this->status_color[$status]) ? $this->status_color[$status] : 'white';
      $title = lang_get('access_to_bts');  
      return "<div  title=\"{$title}\" style=\"display: inline; background: $color;\">$linkVerbose</div>";
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
     *
     *
     **/
	function buildStatusHTMLString($issue)
	{
		// if the bug wasn't found the status is null and we simply display the bugID
		$str = htmlspecialchars($issue->id);
		if (!is_null($issue) )
		{
			//strike through all bugs that have a closed status.. 
			if( $issue->statusCode > 1 )
			{	
				if( $issue->openStatus )
				{
					// strike through and bold all bugs that have a resolved status
					$str = "<b>[resolv.]</b> [$issue->statusVerbose] <del>" . $id . "</del>";
				}
				else
				{
					$str = "[closed] [$issue->statusVerbose] <del>" . $id . "</del>";
				}	
			}
			else
			{
				$str = "[$issue->statusVerbose] ";
			}
		}
	    return (!is_null($issue) && $issue) ? $str  : null;
	}


	function getMyInterface()
  	{
		return $this->cfg->interfacePHP;
  	}



	public static function getCfgTemplate()
  	{
  	
		$template = "<!-- Template " . __CLASS__ . " -->\n" .
					"<issuetracker>\n" .
					"<dbhost>DATABASE SERVER NAME</dbhost>\n" .
					"<dbname>DATABASE NAME</dbname>\n" .
					"<dbtype>mysql</dbtype>\n" .
					"<dbuser>USER</dbuser>\n" .
					"<dbpassword>PASSWORD</dbpassword>\n" .
					"<!-- IMPORTANT NOTICE - Ampersand HAS TO BE ESCAPED -->\n" .
					"<uriview>http://[FOGBUGZ_HOST]/fogbugz/default.asp?pg=pgEditBug&amp;command=view&amp;ixbug=</uriview>\n" .
					"<uricreate>http://[FOGBUGZ_HOST]/fogbugz/default.asp?command=new&amp;pg=pgEditBug</uricreate>\n" .
					"</issuetracker>\n";
		return $template;
  	}

}
?>