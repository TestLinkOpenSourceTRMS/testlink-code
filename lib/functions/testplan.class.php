<?php
/** TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * 
 * @filesource $RCSfile: testplan.class.php,v $
 * @version $Revision: 1.1 $
 * @modified $Date: 2006/03/20 18:02:22 $ $Author: franciscom $
 * @author franciscom
 *
 */

require_once( dirname(__FILE__). '/tree.class.php' );
class testplan
{

var $db;
var $tree_manager;

function testplan(&$db)
{
  $this->db = &$db;	
  $this->tree_manager = New tree($this->db);
}


/** 
 * create 
 *
 * 20060312 - franciscom - name is setted on nodes_hierarchy table
 * 20060101 - franciscom - added notes
 */
function create($name,$notes,$testproject_id)
{
	$node_types=$this->tree_manager->get_available_node_types();
  $tplan_id = $this->tree_manager->new_node($testproject_id,$node_types['testplan'],$name);
	
	$sql = "INSERT INTO testplans (id,notes,testproject_id) 
	        VALUES ( {$tplan_id} " . ", '" . 
	                 $this->db->prepare_string($notes) . "'," . 
	                 $testproject_id .")";
	$result = $this->db->exec_query($sql);
	$id = 0;
	if ($result)
	{
		$id =  $this->db->insert_id();
	}
	return($id);
}



/*
update info on tables and on session

		20060312 - franciscom - name is setted on nodes_hierarchy table

*/
function update($id,$name,$notes)
{
}

function get_by_name($name)
{
	$sql = " SELECT testplans.*, nodes_hierarchy.name 
	         FROM testplans, nodes_hierarchy 
	         WHERE nodes_hierarchy.name = '" . 
	         $this->db->prepare_string($name) . "'";

  $recordset = $this->db->get_recordset($sql);
  return($recordset);
}

/*
get info for one test project
*/
function get_by_id($id)
{
	$sql = " SELECT testplans.*,nodes_hierarchy.name 
	         FROM testplans, nodes_hierarchy
	         WHERE testplans.id = nodes_hierarchy.id
	         AND   testplans.id = {$id}";
  $recordset = $this->db->get_recordset($sql);
  return($recordset ? $recordset[0] : null);
}


/*
get array of info for every test project
without any kind of filter.
Every array element contains an assoc array

*/
function get_all()
{
	$sql = " SELECT testplans.*, nodes_hierarchy.name 
	         FROM testplans, nodes_hierarchy
	         WHERE testplans.id=nodes_hierarchy.id";
  $recordset = $this->db->get_recordset($sql);
  return($recordset);
}



/* 20060305 - franciscom */
function count_testcases($id)
{
	$sql = "SELECT COUNT(testplan_id) AS qty
	        FROM testplan_tcversions
	        WHERE testplan_id={$id}";
	//$result = $this->db->exec_query($sql);
	
	$recordset = $this->db->get_recordset($sql);
  $qty=0;        
  if( !is_null($recordset) )
  {
	  $qty=$recordset[0]['qty'];
	}  
	return($qty);
}


// 20060319 - franciscom
// $items_to_link: assoc array key=tc_id value=tcversion_id
//                 passed by reference for speed
//
function link_tcversions($id,&$items_to_link)
{
    $sql="INSERT INTO testplan_tcversions 
          (testplan_id,tcversion_id)
          VALUES ({$id},";	
		// 
		foreach($items_to_link as $tc => $tcversion)
		{
	     $result = $this->db->exec_query($sql . "{$tcversion})");
		}
}


/* 20060319 - franciscom */
function get_linked_tcversions($id)
{
/*    $sql="SELECT nodes_hierarchy.parent_id as tc_id,tcversion_id 
          FROM testplan_tcversions,nodes_hierarchy 
          WHERE nodes_hierarchy.id = tcversion_id
          AND   testplan_id={$id}";	
*/
    $sql="SELECT	NHB.parent_id AS testsuite_id,NHA.parent_id AS tc_id, tcversion_id 
          FROM nodes_hierarchy NHA
          JOIN nodes_hierarchy NHB ON NHA.parent_id = NHB.id 
          JOIN testplan_tcversions ON NHA.id = tcversion_id 
          WHERE testplan_id={$id} ORDER BY testsuite_id";	


	  $recordset = $this->db->get_recordset($sql);
    return($recordset);
}


} // end class
?>
