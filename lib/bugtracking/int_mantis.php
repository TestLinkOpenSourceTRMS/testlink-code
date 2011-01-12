<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: int_mantis.php,v $
 *
 * @version $Revision: 1.18.6.1 $
 * @modified $Date: 2011/01/12 18:33:49 $ $Author: franciscom $
 *
 * @author Andreas Morsing
 *
 * Constants used throughout TestLink are defined within this file
 * they should be changed for your environment
 *
 * @internal revision
 *
 * 20110110 - Contribution by KarlReichert - BUGID 3784
 * 20100313 - franciscom - BUGID 3195
 * 
 * 20080523 - franciscom - 
 * Contribution Peter Rooms - BUGID 1534 -
 * Bug coloring and labeling according status using same colors than Mantis.
 *
 * 20070304 - franciscom - 
 * 1. added an specialized version of checkBugID
 * 2. added new method checkBugID_existence()
 *
 *
 * 20070302 - BUGID 
 * Problems on getBugSummaryString($id), when DB is MS SQL
 * On MS-SQL fetch_array() does not returns numeric indexes, then
 * only choice is accessing my field name (IMHO better)
 *
 * Removed also DBNAME on Queries because causes problems with MS-SQL
 *
**/
/** Interface name */
define('BUG_INTERFACE_CLASSNAME',"mantisInterface");

class mantisInterface extends bugtrackingInterface
{
	//members to store the bugtracking information
	var $dbHost = BUG_TRACK_DB_HOST;
	var $dbName = BUG_TRACK_DB_NAME;
	var $dbUser = BUG_TRACK_DB_USER;
	var $dbPass = BUG_TRACK_DB_PASS;
	var $dbType = BUG_TRACK_DB_TYPE;
	var $showBugURL = BUG_TRACK_HREF;
	var $enterBugURL = BUG_TRACK_ENTER_BUG_HREF;

	// Contribution 
	// Copied from mantis configuration
	//
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
		return $this->showBugURL.urlencode($id);
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
				// BUGID 3195
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
			// 20110110 - KarlReichert - BUGID 3784
			$status = str_replace(" ", "_", $status);

			// 20080523 - franciscom - BUGID 1534
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
			return false;

		$status = null;
		// Problems with MS-SQL
		$query = "SELECT summary FROM mantis_bug_table WHERE id='" . $id."'";
		
		$result = $this->dbConnection->exec_query($query);
		if ($result)
		{
			$summary = $this->dbConnection->fetch_array($result);

			// 20070302 - BUGID - on MS-SQL fetch_array() does not returns numeric indexes, then
			//                    only choice is accessing my field name (IMHO better)
			if ($summary)
				$summary = $summary['summary'];
			else
				$summary = null;
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
		$status_ok = 0;	
		$query = "SELECT status FROM mantis_bug_table WHERE id='" . $id ."'";
		$result = $this->dbConnection->exec_query($query);
		if ($result && ($this->dbConnection->num_rows($result) == 1))
		{
      		$status_ok = 1;    
    	}
		return $status_ok;
	}	
	
	// Contribution
	function buildViewBugLink($bugID,$bWithSummary = false)
  	{
      $s = parent::buildViewBugLink($bugID, $bWithSummary);
      $status = $this->getBugStatus($bugID);

      // BUGID 3195
      $color = isset($this->status_color[$status]) ? $this->status_color[$status] : 'white';
      $title = lang_get('access_to_bts');  
      return "<div  title=\"{$title}\" style=\"display: inline; background: $color;\">$s</div>";
  	}
}
?>
