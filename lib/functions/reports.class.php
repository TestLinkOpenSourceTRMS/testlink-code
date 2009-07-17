<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * This class is encapsulates most functionality necessary to query the database
 * for results to publish in reports.  It returns data structures to the gui layer in a 
 * manner that are easy to display in smarty templates.
 *   
 * @package 	TestLink
 * @author 		Martin Havlat
 * @copyright 	2005-2009, TestLink community 
 * @version    	CVS: $Id: reports.class.php,v 1.11 2009/07/17 08:36:45 franciscom Exp $
 * @link 		http://www.teamst.org/index.php
 * @uses 		config.inc.php
 * @uses		common.php
 *
 * @internal Revisions:
 *	20090618 - franciscom - BUGID 0002621 
 **/

/** report specific configuration; navigator list definition */ 
require_once('../../cfg/reports.cfg.php');


/**
 * create reports and metrics data (except query included in class results)
 * 
 * @package	TestLink
 * @author Martin Havlat
 * @since 1.7 
 * @link results.class.php advance reporting data query
 */ 
class tlReports extends tlObjectWithDB
{
	/** resource of database handler; reference is passed in by constructor */
	var $db = null;

	/** Test Plan Identifier; reference is passed in by constructor */
	private $testPlanID = -1;
	private	$tprojectID = -1;
	
	private $map_tc_status;
  

	/** 
	 * class constructor 
	 * 
	 * @param resource &$db reference to database handler
	 * @param integer $tplanId
	 **/    
	public function __construct(&$db, &$tplanId = null)
	{
		$this->db = $db;	
		$this->testPlanID = $tplanId;
		// tlObjectWithDB::__construct($db);
		parent::__construct($this->db);
	}


	/** 
	 * Function returns array with input for reports navigator
	 * 
	 * @param boolean $bug_interface_enabled
	 * @param boolean $req_mgmt_enabled
	 * @param integer $format format identifier
	 * 
	 * @return array of array - described for array $g_reports_list in const.inc.php
	 **/
	public function get_list_reports($bug_interface_enabled, $req_mgmt_enabled, $format)
	{
		$reportList = config_get('reports_list');
		$items = array();

		foreach ($reportList as &$reportItem) {

			// check validity of report		
			if (($reportItem['enabled'] == 'all') || 
				(($reportItem['enabled'] == 'req') && $req_mgmt_enabled) ||
			    (($reportItem['enabled'] == 'bts') && $bug_interface_enabled)) 
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
	 * 
	 * @param boolean $active (optional) query open builds [0,1] 
	 * @param boolean $open (optional) query active builds [0,1]
	 * 
	 * @return integer count of builds
	 */ 
	public function get_count_builds($active=1, $open=0)
	{
		$sql = " SELECT COUNT(0) FROM {$this->tables['builds']} builds " . 
		       " WHERE builds.testplan_id = {$this->testPlanID} ";
		       
	 	if( $active )
	 	{
	 	   $sql .= " AND active=" . intval($active) . " ";   
	 	}
	 	
	 	if( $open )
	 	{
	 	   $sql .= " AND is_open=" . intval($open) . " ";   
	 	}
	      
		return $this->db->fetchOneValue($sql);
	}
	
	
	/** 
	 * get count of testcase linked to a testplan
	 * @return integer count
	 */ 
	public function get_count_testcase4testplan()
	{
		$sql = " SELECT COUNT(0) FROM {$this->tables['testplan_tcversions']} testplan_tcversions " .
		       " WHERE testplan_id = {$this->testPlanID} ";
		return $this->db->fetchOneValue($sql);
	}
		
} // end class result

?>