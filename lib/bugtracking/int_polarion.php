<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * @filesource	int_polarion.php
 *
 * @author Gregor Bonney
 *
 * IMPORTANT NOTICE
 * this code has been contributed to TestLink.
 * TestLink development team has no tested it.
 *
 * TestLink development team has just refactor minor parts of this code.
 *
**/
/** Interface name */
define('BUG_INTERFACE_CLASSNAME',"polarionInterface");

class polarionInterface extends bugtrackingInterface
{
	var $svnProto = BUG_TRACK_SVN_PROTO;
	var $svnPrjGrp = BUG_TRACK_SVN_USED_PROJECT_GRP;
	var $svnRepo = BUG_TRACK_SVN_REPO;
	var $svnWIDir = BUG_TRACK_SVN_WIDIR;
	
	var $svnUser = BUG_TRACK_SVN_USER;
	var $svnPass = BUG_TRACK_SVN_PASS;
	
	var $showBugURL = BUG_TRACK_HREF;
	var $showBugURLEnd = BUG_TRACK_HREF_END;
	
	var $enterBugURL = BUG_TRACK_ENTER_BUG_HREF;
	var $enterBugURLEnd = BUG_TRACK_ENTER_BUG_HREF_END;
	
	var $project = "";
	
	private $status_color = array('open'         => '#FF4D4D', # red,
								  'accepted'     => '#FF7E3D', # orange,
								  'inprogress'	 =>	'#FF7E3D', # orange,
								  'resolved'     => '#F7F74A', # light-yellow
								  'tested'       => '#52E3E3', # ugly
								  'delivered'    => '#42EB55', # light-green
								  'confirmed'    => '#52E3E3', # ugly
								  'closed'       => '#B8B8B8', # gray
								  'held'         => '#7899F5'); # light-blue

	function buildViewBugURL($id)
	{
		$this->idToProjectName($id);
		return $this->showBugURL . $this->project . $this->showBugURLEnd . urlencode($id);
	}
	
	function getEnterBugURL(){
		return $this->enterBugURL . $this->enterBugURLEnd;
	}

	function connect(){
		$this->Connected = 1;
		$this->dbConnection = new database("mysql");
		return $this->Connected;
	}
    
    public function isConnected(){
        return true;
    }
    
	private function convertWorkItemIdToSvnPath($id)
	{
		$id_len = strlen($id);
		$path = '';
		$workPackageId = split("-",$id);
		$tmp_id = $workPackageId[1];
		// BUGID 4455
		$tmp_id_len = strlen($tmp_id);
		
		switch ($tmp_id_len)
		{
				case 0:
					die("Wrong ID!");
					break;

				case 1:
				case 2:
					$path="00-99/" . $id;
					break;
					
				case 3:
				case 4:
				case 5:
					// examples
					// 3 -> x00-x99/CHO
					// 4 -> x000-x999/xy00-xy99/CHO
					// 5 -> x0000-x9999/xy000-xy999/xyz00-xyz99/CHO
					$loop2do = $tmp_id_len-2; // MAGIC
					$path = '';
					for( $fdx=0; $fdx < $loop2do; $fdx++)
					{
						$cutLen = $fdx+1;
						$f = mb_strcut($tmp_id,0,$cutLen);
						$nTimes = $tmp_id_len-$cutLen;
						$zeros = str_repeat('0',$nTimes);
						$nines = str_repeat('9',$nTimes);
						$path .= $f . $zeros . "-" . $f . $nines . "/";
					}
					$path .= $id;
					break;
		}
		$path = $path . "/workitem.xml";
		
		return $path;
	}


	private function idToProjectName($id)
	{
		$projectId = split("-",$id);
		$this->project = $projectId[0];
	}


	function getBugStatusString($id)
	{	
		
		$status = $this->getBugStatus($id);
		
		$str = htmlspecialchars($id);
		if ($status !== false)
		{
			$status = str_replace(" ", "_", $status);
			$status_i18n = lang_get('issue_status_' . $status);
			$str = "[" . $status_i18n . "] " . $id . "";	
		}
		return $str;
	}


	function getBugAttribute($attr,$id)
	{
		switch($attr)
		{
			case 'status':
				$this->idToProjectName($id);
				$ret = array(false);
				break;
				
			case 'title':
				$ret = array('error');
				break;
		}
		
		$workItemPath = $this->convertWorkItemIdToSvnPath($id);
		$path = $this->svnProto . $this->svnUser .":". $this->svnPass ."@". $this->svnRepo;
		if($this->svnPrjGrp <> "")
		{
			 $path .= $this->svnPrjGrp ."/";
		}
		$path .=  $this->project . $this->svnWIDir . $workItemPath;
		
		$content = file_get_contents($path);
		if(strlen($content) > 25)   // MAGIC
		{
			$xml = new SimpleXMLElement($content);
			$ret = $xml->xpath("/work-item/field[@id='{$attr}']");
		}
		
		return $ret[0];
		
	}



	function getBugStatus($id)
	{
		$ret = $this->getBugAttribute('status',$id);
		return $ret[0];
	}

	function getBugSummaryString($id)
	{
		$ret = $this->getBugAttribute('title',$id);
		return $ret[0];
	}

 	function checkBugID($id)
	{
		$valid = false;
	  	$allowed_chars = '/[A-Za-z0-9]*\-[0-9]+$/'; 
		if (preg_match($allowed_chars, $id))
    	{
			$valid = true;	
    	}

      	return $valid;
	}

	function checkBugID_existence($id)
	{
		return 1;
	}	

	function buildViewBugLink($bugID,$bWithSummary = false)
  	{
      $s = parent::buildViewBugLink($bugID, $bWithSummary);
      $status = $this->getBugStatus($bugID);
      $color = isset($this->status_color["$status"]) ? $this->status_color["$status"] : 'white';
      $title = lang_get('access_to_bts');  
      return "<div  title=\"{$title}\" style=\"display: inline; background: ". $color . ";\">$s</div>";
  	}
}
?>