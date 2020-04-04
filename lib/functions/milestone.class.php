<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 *
 * @filesource  milestone.class.php
 * @package     TestLink
 * @author      franciscom
 * @copyright   2020, TestLink community 
 * @link        http://testlink.sourceforge.net/
 *
 **/

/** related functionality */
require_once( dirname(__FILE__) . '/tree.class.php' );
require_once( dirname(__FILE__) . '/assignment_mgr.class.php' );
require_once( dirname(__FILE__) . '/attachments.inc.php' );

/** 
 * Milestone Manager Class 
 * @package TestLink
 **/
class milestone extends tlObject
{
  /** @var database handler */
  var $db;

  /** 
   * class constructor 
   * 
   * @param resource &$db reference to database handler
   **/
  function __construct(&$db)
  {
    parent::__construct();
    $this->db = &$db;
  }

  /*
    function: create()

    args :  keys
            $tplan_id
            $name
            $target_date: string with format: 
            $start_date: 
            $low_priority: percentage
            $medium_priority: percentage
            $high_priority: percentage

    returns:

  */
  function create($mi)
  {
    $item_id=0;
    $dateFields=null;
    $dateValues=null;
    $dateKeys=array('target_date','start_date');
    
    // check dates
    foreach($dateKeys as $varname)
    {
      $value=  trim($mi->$varname);
      if($value != '') 
      {
        if (($time = strtotime($value)) == -1 || $time === false) 
        {
          die (__FUNCTION__ . ' Abort - Invalid date');
        }
        $dateFields[]=$varname;  
        $dateValues[]=" '{$this->db->prepare_string($value)}' ";
      }
    }
    $additionalFields='';
    if( !is_null($dateFields) )
    {
      $additionalFields= ',' . implode(',',$dateFields) ;
      $additionalValues= ',' . implode(',',$dateValues) ;
    }
    /* for future
    $sql = "INSERT INTO {$this->tables['milestones']} " .
           " (testplan_id,name,platform_id,build_id,a,b,c{$additionalFields}) " .
           " VALUES (" . intval($mi->tplan_id) . "," . 
           "'{$this->db->prepare_string($mi->name)}'," .
           intval($mi->platform_id) . "," . intval($mi->build_id) . "," .
           $mi->low_priority . "," .  $mi->medium_priority . "," . $mi->high_priority . 
           $additionalValues . ")";
    */
    $sql = "INSERT INTO {$this->tables['milestones']} " .
           " (testplan_id,name,a,b,c{$additionalFields}) " .
           " VALUES (" . intval($mi->tplan_id) . "," . 
           "'{$this->db->prepare_string($mi->name)}'," .
           $mi->low_priority . "," .  $mi->medium_priority . "," . $mi->high_priority . 
           $additionalValues . ")";

    $result = $this->db->exec_query($sql);
    
    if ($result)
    {
      $item_id = $this->db->insert_id($this->tables['milestones']);
    }
    
    return $item_id;
  }

  /*
    function: update

    args :
          $id
          $name
          $notes
          [$active]: default: 1
          [$open]: default: 1



    returns:

    rev :
  */
  function update($id,$name,$target_date,$start_date,$low_priority,$medium_priority,$high_priority)
  {
    $sql = "UPDATE {$this->tables['milestones']} " . 
           " SET name='{$this->db->prepare_string($name)}', " .
         " target_date='{$this->db->prepare_string($target_date)}', " .
         " start_date='{$this->db->prepare_string($start_date)}', " .
         " a={$low_priority}, b={$medium_priority}, c={$high_priority} WHERE id={$id}";
    $result = $this->db->exec_query($sql);
    return $result ? 1 : 0;
  }



  /*
    function: delete

    args :
          $id


    returns:

  */
  function delete($id)
  {
    $sql = "DELETE FROM {$this->tables['milestones']} WHERE id={$id}";
    $result=$this->db->exec_query($sql);
    return $result ? 1 : 0;
  }


  /*
    function: get_by_id

    args :
          $id
    returns:

  */
  function get_by_id($id)
  {
    $sql=" SELECT M.id, M.name, M.a AS high_percentage, " .
         " M.b AS medium_percentage, M.c AS low_percentage, " .
         " M.target_date, M.start_date, " .
         " M.testplan_id, NH_TPLAN.name AS testplan_name " .
    //     " M.build_id, B.name AS build_name, " . 
    //     " M.platform_id, P.name AS platform_name " .  
         " FROM {$this->tables['milestones']} M " .
         " JOIN {$this->tables['nodes_hierarchy']} NH_TPLAN " .
         " ON NH_TPLAN.id=M.testplan_id " .
    //     " LEFT OUTER JOIN {$this->tables['builds']} B " .
    //     " ON B.id=M.build_id " .
    //     " LEFT OUTER JOIN {$this->tables['platforms']} P " .
    //     " ON P.id=M.platform_id " .
         " WHERE M.id = " . $this->db->prepare_int($id);
            
    $row = $this->db->fetchRowsIntoMap($sql,'id');
    return $row;
  }

  /**
   * check existence of milestone name in Test Plan
   * 
   * @param integer $tplan_id  test plan id.
   * @param string $milestone_name milestone name
   * @param integer $milestone_id default: null
   *                when is not null we add milestone_id as filter, this is useful
   *                to understand if is really a duplicate when using this method
   *                while managing update operations via GUI
   * 
   * @return integer 1 => name exists
   */
  function check_name_existence($tplan_id,$milestone_name,$milestone_id=null,$case_sensitive=0)
  {
    $sql = " SELECT id, name FROM {$this->tables['milestones']} " .
           " WHERE testplan_id = " . $this->db->prepare_int($tplan_id);
    
    if($case_sensitive)
    {
      $sql .= " AND name=";
    }
    else
    {
      $milestone_name=strtoupper($milestone_name);
      $sql .= " AND UPPER(name)=";
    }
    $sql .= "'{$this->db->prepare_string($milestone_name)}'";
    
    if( !is_null($milestone_id) )
    {
      $sql .= " AND id <> " . $this->db->prepare_int($milestone_id);
    }
    
    $result = $this->db->exec_query($sql);
    $status= $this->db->num_rows($result) ? 1 : 0;
    
    return $status;
  }


  /*
    function: get_all_by_testplan
              get info about all milestones defined for a testlan
    args :
          tplan_id


    returns:

    rev :
  */
  function get_all_by_testplan($tplan_id)
  {
    $sql=" SELECT M.id, M.name, M.a AS high_percentage, M.b AS medium_percentage, M.c AS low_percentage, " .
       " M.target_date, M.start_date, M.testplan_id, NH.name as testplan_name " .   
       " FROM {$this->tables['milestones']} M, {$this->tables['nodes_hierarchy']} NH " .
       " WHERE testplan_id={$tplan_id} AND NH.id = testplan_id " .
       " ORDER BY M.target_date,M.name";
    $rs=$this->db->get_recordset($sql);
    return $rs;
  }
}