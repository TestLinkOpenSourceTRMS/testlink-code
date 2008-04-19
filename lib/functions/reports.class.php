<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: reports.class.php,v $
 * @author Martin Havlát
 * @version $Revision: 1.8 
 * @modified $Date: 2008/04/19 21:52:21 $ by $Author: havlat $
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
//		global $tlCfg;

		$this->db = $db;	
//	  $this->tp = $tplan_mgr;  

	// @TODO: wrong must correspond with choosen TP    
//    $this->tprojectID = $tproject_info['id'];
    $this->testPlanID = $tplanId;
//		$this->tplanName  = $tplan_info['name'];
    
	} // end constructor


	/** 
	 * Function returns array with input for reports navigator
	 * @return array of array - described for array $g_reports_list in const.inc.php
	 **/
	public function get_list_reports($bug_interface_on,$req_mgmt_enabled, $format)
	{
		global $tlCfg;
		$arrItems = array();

		foreach ($tlCfg->reports_list as &$reportItem) {

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
    			   	$arrItems[] = array('name' => lang_get($reportItem['title']), 'href' => $reportUrl);
				}
			}
		}

		/** @TODO: these reports are not available in 1.7 */
		// 20070826 - has problems
		// array('name' => lang_get('link_results_import'), 'href' => 'resultsImport.php?report_type='));
	
		// not ready yet
		// array('name' => lang_get('time_charts'), 'href' => 'timeCharts.php?report_type=')

	  	// this results are related to selected build
		//  $arrDataB = array(
		//		array('name' => lang_get('link_report_metrics_active_build'), 'href' => 'resultsBuild.php'),
  		//	);

		return $arrItems;
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