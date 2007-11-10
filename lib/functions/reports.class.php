<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: reports.class.php,v $
 * @author Martin Havlát
 * @version $Revision: 1.8 
 * @modified $Date: 2007/11/10 02:52:38 $ by $Author: havlat $
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
require_once("../../config.inc.php");
require_once('common.php');


	/**
	* Functions to create reports and metrics (except query included in class results)
	*/ 
class reports
{
	// class references passed in by constructor
	private $db = null;
	private $tp = null;
	private $testPlanID = -1;
	private	$tprojectID = -1;
	
	private $map_tc_status;
  

	/** class constructor */    
	public function reports(&$db, &$tplanId = null)
	{
		$this->db = $db;	
//	  $this->tp = $tplan_mgr;  

	// @TODO: wrong must correspond with choosen TP    
//    $this->tprojectID = $tproject_info['id'];
    $this->testPlanID = $tplanId;
//		$this->tplanName  = $tplan_info['name'];
    
	} // end report constructor


	/** 
	 * Function returns array with input for reports navigator
	 * @return array of array - described for array $g_reports_list in const.inc.php
	 **/
	public function get_list_reports($bug_interface_on,$req_mgmt_enabled)
	{
		global $g_reports_list;
		$arrItems = array();

		foreach ($g_reports_list as &$reportItem) {
		
			if (($reportItem['enabled'] == 'all') || (($reportItem['enabled'] == 'req') && $req_mgmt_enabled) ||
			(($reportItem['enabled'] == 'bts') && $bug_interface_on)) 
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

		
} // end class result
?>