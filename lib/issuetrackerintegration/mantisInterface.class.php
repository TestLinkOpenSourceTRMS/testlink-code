<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * @filesource	mantisInterface.class.php
 *
 * @internal revision
 * @since 1.9.4
 * 20120220 - franciscom - TICKET 4904: integrate with ITS on test project basis 
**/
class mantisInterface extends issueTrackerInterface
{
  	private $code_status = array(10 => 'new',
                                 20 => 'feedback',
                                 30 => 'acknowledged',
                                 40 => 'confirmed',
                                 50 => 'assigned',
                                 80 => 'resolved',
                                 90 => 'closed');
                              
	private $status_color = array('new'          => '#ffa0a0', # red,
                                  'feedback'     => '#ff50a8', # purple
                                  'acknowledged' => '#ffd850', # orange
                                  'confirmed'    => '#ffffb0', # yellow
                                  'assigned'     => '#c8c8ff', # blue
                                  'resolved'     => '#cceedd', # buish-green
                                  'closed'       => '#e8e8e8'); # light gray
	
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
		
		$status = false;
		$query = "SELECT status FROM mantis_bug_table WHERE id='" . $id."'";
		
		$result = $this->dbConnection->exec_query($query);
		if ($result)
		{
			$status_rs = $this->dbConnection->fetch_array($result);
			$status = null;
			if ($status_rs)
			{
				if( isset($this->code_status[$status_rs['status']]) )
				{
			  		$status = $this->code_status[$status_rs['status']];
			  	}
			  	else
			  	{
			  		// give info to user on Event Viewer
			  		$msg = lang_get('MANTIS_status_not_configured');
			  		$msg = sprintf($msg,$status_rs['status']);
			  		logWarningEvent($msg,"MANTIS INTEGRATION");
			  		
			  		$status = 'custom_undefined_on_tl';
			  	}	
			}	
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
		//if the bug wasn't found the status is null and we simply display the bugID
		if ($status !== false)
		{
		  
			//the status values depends on your mantis configuration at config_inc.php in $g_status_enum_string, 
			//below is the default:
			//'10:new,20:feedback,30:acknowledged,40:confirmed,50:assigned,80:resolved,90:closed'
			//strike through all bugs that have a resolved or closed status.. 
			// if ($status == 80 || $status == 90)
			// {
			// 	$str = "<del>" . $id . "</del>";
			// 	
			// }
			// this way if user configure status on mantis with blank
            // we do not have problems
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
		if (!$this->isConnected())
		{
			return false;
		}
		
		$status = null;
		// Problems with MS-SQL
		$query = "SELECT summary FROM mantis_bug_table WHERE id='" . $id."'";
		
		$result = $this->dbConnection->exec_query($query);
		if ($result)
		{
			$summary = $this->dbConnection->fetch_array($result);

			// on MS-SQL fetch_array() does not returns numeric indexes, then
			// only choice is accessing my field name (IMHO better)
			if ($summary)
			{
				$summary = $summary['summary'];
			}
			else
			{
				$summary = null;
			}	
		}
		return $summary;
	}

 
  	/**
	 * checks is bug id is present on BTS
	 * 
	 * @return integer returns 1 if the bug with the given id exists 
	 **/
	function checkBugIDExistence($id)
	{
		$status_ok = 0;	
		$query = "SELECT status FROM mantis_bug_table WHERE id='" . $id ."'";
		$result = $this->dbConnection->exec_query($query);
		if ($result && ($this->dbConnection->num_rows($result) == 1))
		{
      		$status_ok = 1;    
    	}
		return $status_ok;
	}	
	

	function buildViewBugLink($bugID,$addSummary = false)
  	{
      $s = parent::buildViewBugLink($bugID, $addSummary);
      $status = $this->getBugStatus($bugID);
      $color = isset($this->status_color[$status]) ? $this->status_color[$status] : 'white';
      $title = lang_get('access_to_bts');  
      return "<div  title=\"{$title}\" style=\"display: inline; background: $color;\">$s</div>";
  	}


	function getMyInterface()
  	{
		return $this->cfg->interfacePHP;
  	}



	function getCfgTemplate()
  	{
  	
		$template = "<!-- Template " . __CLASS__ . " -->" .
					"<issuetracker>" .
					"<dbhost>DATABASE SERVER NAME</dbhost>" .
					"<dbname>DATABASE NAME</dbname>" .
					"<dbtype>mysql</dbtype>" .
					"<dbuser>USER</dbuser>" .
					"<dbpassword>PASSWORD</dbpassword>" .
					"<uriview>http://localhost:8080/development/mantisbt-1.2.5/view.php?id=</uriview>" .
					"<uricreate>http://localhost:8080/development/mantisbt-1.2.5/</uricreate>" .
					"</issuetracker>";
		return $template;
  	}

}
?>