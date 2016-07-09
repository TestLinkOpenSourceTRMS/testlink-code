<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * This class is encapsulates most functionality necessary to query the database
 * for results to publish in reports.  It returns data structures to the gui layer in a 
 * manner that are easy to display in smarty templates.
 *   
 * @package   TestLink
 * @author    Martin Havlat
 * @copyright 2005-2014, TestLink community 
 * @version   reports.class.php
 * @link      http://testlink.sourceforge.net/
 * @uses      config.inc.php
 * @uses      common.php
 *
 * @internal revisions
 * @since 1.9.10
 *
 **/

/** report specific configuration; navigator list definition */ 
require_once('../../cfg/reports.cfg.php');


/**
 * create reports and metrics data (except query included in class results)
 * 
 * @package TestLink
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
  private $tprojectID = -1;
  
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
   * @param object $context
   * @param boolean $bug_interface_enabled
   * @param boolean $req_mgmt_enabled
   * @param integer $format format identifier
   * 
   * @return array of array - described for array $g_reports_list in const.inc.php
   **/
   public function get_list_reports($context,$bug_interface_enabled, $req_mgmt_enabled, $format)
   {
    $reportList = config_get('reports_list');
    $items = array();

    $toggleMsg = lang_get('show_hide_direct_link');
    $canNotCreateDirectLink = lang_get('can_not_create_direct_link');

    $apiKeyLen = strlen(trim($context->apikey));
    $apiKeyIsValid = ($apiKeyLen == 32 || $apiKeyLen == 64); // I'm sorry for MAGIC
    
    $xdx = 0;
    
    foreach ($reportList as &$reportItem) 
    {
      // check validity of report   
      if (($reportItem['enabled'] == 'all') || 
          (($reportItem['enabled'] == 'req') && $req_mgmt_enabled) ||
          (($reportItem['enabled'] == 'bts') && $bug_interface_enabled)) 
      {
        if (strpos(",".$reportItem['format'],$format) > 0)
        {
          $reportUrl = $reportItem['url'] . ( stristr($reportItem['url'], "?") ? '&' : '?');
          $items[$xdx] = array('name' => lang_get($reportItem['title']), 'href' => $reportUrl,
                               'directLink' => '');

          if(isset($reportItem['directLink']) && trim($reportItem['directLink']) != '')
          {                     
            if($apiKeyIsValid)
            {
              $items[$xdx]['directLink'] = sprintf($reportItem['directLink'],$_SESSION['basehref'],
                                                   $context->apikey,$context->tproject_id,$context->tplan_id);
            }                                     
            else
            {
              $items[$xdx]['directLink'] = $canNotCreateDirectLink;
            }
          }
          
          $dl = $items[$xdx]['directLink']; 
          $mask = '<img class="clickable" title="%s" alt="%s" ' .
                  ' onclick="showHideByClass(' . "'div','%s');event.stopPropagation();" . '" ' .
                  ' src="' . $context->imgSet['link_to_report'] . '" align="center" />';

          $divClass = 'direct_link_' . $xdx;        
          $items[$xdx]['toggle'] = sprintf($mask,$toggleMsg,$toggleMsg,$divClass);
          $items[$xdx]['directLinkDiv'] = '<div class="' . $divClass . '" ' .
                                          "style='display:none;border:1px solid;background-color:white;'>" . 
                                          '<a href="' . $dl .'" target="_blank">' . $dl . '</a><br></div>';
          $xdx++;
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