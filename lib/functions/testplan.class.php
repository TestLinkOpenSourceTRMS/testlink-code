<?php
/** TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * 
 * @filesource $RCSfile: testplan.class.php,v $
 * @version $Revision: 1.6 $
 * @modified $Date: 2006/06/07 12:34:55 $ $Author: franciscom $
 * @author franciscom
 *
 * 20060603 - franciscom - changes in get_linked_tcversions()
 * 20060430 - franciscom - added get_keywords_map()
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
 * 20060511 - franciscom - wrong use of insert_id() [not needed]
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
		$id =  $tplan_id;
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

//  20060603 - franciscom - new argument executed
//                          not_null     -> get only executed tcversions
//                          null         -> get executed and NOT executed
// 
//
//  20060430 - franciscom - new join to get the execution status 
// executed field: will take the following values
//                 NULL if the tc version has not been executed in THIS test plan
//                 tcversion_id if has executions 
//
function get_linked_tcversions($id,$tcase_id=null,$keyword_id=0,$executed=null)

{
$keywords_join = " ";
$keywords_filter = " ";
$tc_id_filter = " ";
$executions_join = " ";

if( $keyword_id > 0 )
{
    $keywords_join = " JOIN testcase_keywords TK ON NHA.parent_id = TK.testcase_id ";   
    $keywords_filter = " AND   TK.keyword_id = {$keyword_id} ";
}
if (!is_null($tcase_id) )
{
   if( is_array($tcase_id) )
   {

   }
   else if ($tcase_id > 0 )
   {
      $tc_id_filter = " AND   NHA.parent_id = {$tcase_id} ";
   }
}

// 20060603 - franciscom
if( is_null($executed) )
{
     $executions_join = " LEFT OUTER ";
}          
$executions_join .= " JOIN executions E ON NHA.id = E.tcversion_id ";


$sql="SELECT DISTINCT NHB.parent_id AS testsuite_id,
              NHA.parent_id AS tc_id, 
              T.tcversion_id AS tcversion_id,
              E.tcversion_id AS executed
      FROM nodes_hierarchy NHA
      JOIN nodes_hierarchy NHB ON NHA.parent_id = NHB.id 
      JOIN testplan_tcversions T ON NHA.id = T.tcversion_id 
      {$executions_join}
      {$keywords_join}
      WHERE T.testplan_id={$id} {$keywords_filter} {$tc_id_filter}      
      ORDER BY testsuite_id";
          
$recordset = $this->db->fetchRowsIntoMap($sql,'tc_id');
return($recordset);
}


function get_builds_for_html_options($id)
{
	$sql = " SELECT builds.id, builds.name 
	         FROM builds WHERE builds.testplan_id = {$id}
	         ORDER BY builds.name";
	return $this->db->fetchColumnsIntoMap($sql,'id','name');
}//end function

// 20060322 - franciscom
function get_max_build_id($id)
{
	$sql = " SELECT MAX(builds.id)
	         FROM builds WHERE builds.testplan_id = {$id}";
	
	$recordset = $this->db->get_recordset($sql);
	return(intval($recordset[0][0]));
	
}//end function


function get_builds($id)
{
	$sql = " SELECT builds.id, builds.name, builds.notes 
	         FROM builds WHERE builds.testplan_id = {$id}
	         ORDER BY builds.name";
  $recordset = $this->db->get_recordset($sql);
  return($recordset);
}//end function




// 20060430 - franciscom
// $items: assoc array key=tc_id value=tcversion_id
//
function unlink_tcversions($id,&$items)
{
  if( !is_null($items) )
  {
	    $in_clause = " AND tcversion_id IN (" . implode(",",$items) . ")";    

      // Need to remove all related info:
      // execution_bugs - to be done
      // executions
      
      // First get the executions id if any exist
      $sql=" SELECT id AS execution_id 
             FROM executions
             WHERE testplan_id = {$id} ${in_clause}";
      $exec_ids = $this->db->fetchRowsIntoMap($sql,'execution_id');       
      
      if( !is_null($exec_ids) and count($exec_ids) > 0 )
      {
          // has executions
          $exec_ids = array_keys($exec_ids);
          $exec_id_where= " WHERE execution_id IN (" . implode(",",$exec_ids) . ")";    
          
          // Remove bugs if any exist             
          $sql=" DELETE FROM execution_bugs {$exec_id_where} ";
          $result = $this->db->exec_query($sql);    
          
          // now remove executions
          $sql=" DELETE FROM executions
                 WHERE testplan_id = {$id} ${in_clause}";
          $result = $this->db->exec_query($sql);    
      }
      
      // build sql
      $sql=" DELETE FROM testplan_tcversions 
             WHERE testplan_id={$id} {$in_clause} ";
	    $result = $this->db->exec_query($sql);
	}
} // end function


// 20060430 - franciscom
function get_keywords_map($id,$order_by_clause='')
{
  $map_keywords=null;

  // keywords are associated to testcase id, then first  
  // we need to get the list of testcases linked to the testplan
  $linked_items = $this->get_linked_tcversions($id);
  if( !is_null($linked_items) )
  {
     $tc_id_list = implode(",",array_keys($linked_items));  
  
  	 $sql = "SELECT DISTINCT keyword_id,keywords.keyword 
	           FROM testcase_keywords,keywords 
	           WHERE keyword_id = keywords.id 
	           AND testcase_id IN ( {$tc_id_list} ) 
	           {$order_by_clause}";
	   $map_keywords = $this->db->fetchColumnsIntoMap($sql,'keyword_id','keyword');
  }
  return ($map_keywords);
} // end function


// 20060501 - franciscom
function get_keywords_tcases($id,$keyword_id=0)
{
  $map_keywords=null;

  // keywords are associated to testcase id, then first  
  // we need to get the list of testcases linked to the testplan
  $linked_items = $this->get_linked_tcversions($id);
  if( !is_null($linked_items) )
  {
     $keyword_filter= '' ;
     if( $keyword_id > 0 )
     {
       $keyword_filter = " AND keyword_id = {$keyword_id} ";
     }
     $tc_id_list = implode(",",array_keys($linked_items));  
  
  	 $sql = "SELECT DISTINCT testcase_id,keyword_id,keyword 
	           FROM testcase_keywords,keywords 
	           WHERE keyword_id = keywords.id 
	           AND testcase_id IN ( {$tc_id_list} )
 		         {$keyword_filter}
			       ORDER BY keyword ASC ";
		$map_keywords = $this->db->fetchRowsIntoMap($sql,'testcase_id');
  }
  return ($map_keywords);
} // end function

} // end class
?>
