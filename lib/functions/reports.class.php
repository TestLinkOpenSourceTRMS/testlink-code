<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: reports.class.php,v $
 * @author Martin Havlát
 * @version $Revision: 1.8 
 * @modified $Date: 2009/05/21 19:24:05 $ by $Author: schlundus $
 *
 * Scope:
 * This class is encapsulates most functionality necessary to query the database
 * for results to publish in reports.  It returns data structures to the gui layer in a 
 * manner that are easy to display in smarty templates.
 *   
 *-------------------------------------------------------------------------
 * Revisions:
 *
 **/
require_once('../../config.inc.php');
require_once('../../cfg/reports.cfg.php');
require_once('common.php');


	/**
	* Functions to create reports and metrics (except query included in class results)
	*/ 
class tlReports
{
	// class references passed in by constructor
	private $db = null;
	private $tp = null;
	private $testPlanID = -1;
	private	$tprojectID = -1;
	
	private $map_tc_status;
  

	/** class constructor */    
	public function tlReports(&$db, &$tplanId = null)
	{
		$this->db = $db;	
		$this->testPlanID = $tplanId;
	}


	/** 
	 * Function returns array with input for reports navigator
	 * @return array of array - described for array $g_reports_list in const.inc.php
	 **/
	public function get_list_reports($bug_interface_on,$req_mgmt_enabled, $format)
	{
		$reportList = config_get('reports_list');
		$items = array();

		foreach ($reportList as &$reportItem) {

			// check validity of report		
			if (($reportItem['enabled'] == 'all') || (($reportItem['enabled'] == 'req') && $req_mgmt_enabled) ||
			    (($reportItem['enabled'] == 'bts') && $bug_interface_on)) 
			{
				// check format availability
				if (strpos(",".$reportItem['format'],$format) > 0)
				{
					// prepare for $GET params
					if (stristr($reportItem['url'], "?")) {
						$reportUrl = $reportItem['url'].'&';
					} else {
						$reportUrl = $reportItem['url'].'?';
					}
    			$items[] = array('name' => lang_get($reportItem['title']), 'href' => $reportUrl);
				}
			}
		}
		return $items;
	}


/** 
 * get count of builds
 * @param $active - boolean - query open builds [0,1] optional
 * @param $open - boolean - query active builds [0,1] optional
 * @return count || null
 */ 
public function get_count_builds($active=1, $open=null)
{
	$sql = " SELECT COUNT(*) FROM builds WHERE builds.testplan_id = {$this->testPlanID} ";
	       
 	if( !is_null($active) )
 	{
 	   $sql .= " AND active=" . intval($active) . " ";   
 	}
 	if( !is_null($open) )
 	{
 	   $sql .= " AND is_open=" . intval($open) . " ";   
 	}
      
	return $this->db->fetchOneValue($sql);
}


/** 
 * get count of builds
 * @return count || null
 */ 
public function get_count_testcase4testplan()
{
	$sql = " SELECT COUNT(*) FROM testplan_tcversions WHERE testplan_id = {$this->testPlanID} ";
	return $this->db->fetchOneValue($sql);
}
		
} // end class result
?>