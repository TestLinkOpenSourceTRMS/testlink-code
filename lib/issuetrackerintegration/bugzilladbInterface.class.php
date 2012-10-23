<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * @filesource	bugzilladbInterface.class.php
 * @author Francisco Mancardi
 *
 *
 * @internal revisions
 * @since 1.9.4
 * 20120324 - franciscom - TICKET 4904: integrate with ITS on test project basis 
**/

class bugzilladbInterface extends issueTrackerInterface
{

	/**
	 * Construct and connect to BTS.
	 *
	 * @param str $type (see tlIssueTracker.class.php $systems property)
	 * @param xml $cfg
	 **/
	function __construct($type,$config)
	{
	    parent::__construct($type,$config);
		$this->interfaceViaDB = true;
	    $this->guiCfg = array('use_decoration' => true); // add [] on summary
   		$this->methodOpt['buildViewBugLink'] = array('addSummary' => true, 'colorByStatus' => false);

	}



	function getissue($id)
	{
		if (!$this->isConnected())
		{
			return null;
		}
		
		$sql = " SELECT bug_id AS id,short_desc AS summary,bug_status AS status" .
			     " FROM " . ( !is_null($this->cfg->dbschema) ? " {$this->cfg->dbschema}.bugs " : 'bugs') .
			     " WHERE bug_id = '{$id}' ";
		$rs = $this->dbConnection->fetchRowsIntoMap($sql,'id');
		$issue = null;

		if( !is_null($rs) )	
		{
	    $issue = new stdClass();
			$issue->IDHTMLString = "<b>{$id} : </b>";
			$issue->statusCode = $issue->statusVerbose = $rs[$id]['status']; 
			$issue->statusHTMLString = $this->buildStatusHTMLString($issue->statusVerbose);
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
	 * @return string returns the status of the given bug (if found in the db), or null else
	 **/
	function getBugStatus($id)
	{
		if (!$this->isConnected())
		{
			return null;
		}
		$issue = $this->getIssue($id);
		
		return is_null($issue) ? $issue : $issue->statusVerbose;
	}
	
	
	/**
	 * Returns the status in a readable form (HTML context) for the bug with the given id
	 *
	 * @param int id the bug id
	 * 
	 * @return string returns the status (in a readable form) of the given bug 
	 *
	 **/
	function buildStatusHTMLString($id)
	{
		$status = $this->getBugStatus($id);
		
		//if the bug wasn't found the status is null and we simply display the bugID
		$str = htmlspecialchars($id);
		if (!is_null($status))
		{
			//strike through all bugs that have a resolved, verified, or closed status.. 
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
	 * @return integer returns 1 if the bug with the given id exists 
	 **/
	function checkBugIDExistence($id)
	{
		$status_ok = 0;	
		$issue = $this->getIssue($id);
		
		return !is_null($issue) ? 1 : 0; 
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
     * @author francisco.mancardi@gmail.com>
     **/
	function getMyInterface()
  	{
		return $this->cfg->interfacePHP;
  	}


    /**
     *
     * @author francisco.mancardi@gmail.com>
     **/
	public static function getCfgTemplate()
  	{
  	
		$template = "<!-- Template " . __CLASS__ . " -->\n" .
					"<issuetracker>\n" .
					"<dbhost>DATABASE SERVER NAME</dbhost>\n" .
					"<dbname>DATABASE NAME</dbname>\n" .
					"<dbschema>DATABASE NAME</dbschema>\n" .
					"<dbtype>mysql</dbtype>\n" .
					"<dbuser>USER</dbuser>\n" .
					"<dbpassword>PASSWORD</dbpassword>\n" .
					"<uricreate>http://[bugzillaserver]/bugzilla/</uricreate>\n" .
					"<uriview>http://[bugzillaserver]/bugzilla/show_bug.cgi?id=</uriview>\n" .
					"</issuetracker>\n";
		return $template;
  	}


}
?>