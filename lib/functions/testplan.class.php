<?php
/** TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * 
 * @filesource $RCSfile: testplan.class.php,v $
 * @version $Revision: 1.12 $
 * @modified $Date: 2006/10/15 19:05:39 $ $Author: schlundus $
 * @author franciscom
 *
 * 20060919 - franciscom - copy_* functions
 *
 * 20060805 - franciscom - created update()
 * 20060603 - franciscom - changes in get_linked_tcversions()
 * 20060430 - franciscom - added get_keywords_map()
 *
 */

require_once( dirname(__FILE__). '/tree.class.php' );
require_once( dirname(__FILE__) . '/assignment_mgr.class.php' );
require_once( dirname(__FILE__) . '/attachments.inc.php' );

class testplan
{

var $db;
var $tree_manager;
var $assignment_mgr;
var $assignment_types;
var $assignment_status;


function testplan(&$db)
{
  $this->db = &$db;	
  $this->tree_manager = New tree($this->db);

  // 20060910 - franciscom
  $this->assignment_mgr=New assignment_mgr($this->db);
  $this->assignment_types=$this->assignment_mgr->get_available_types(); 
  $this->assignment_status=$this->assignment_mgr->get_available_status();

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
  20060805 - franciscom - creation
*/
function update($id,$name,$notes,$is_active)
{
  $do_update=1;
  $result=null;
	$active = to_boolean($is_active);
	$name=trim($name);
	
	// 20060805 - franciscom - two tables to update and we have no transaction yet.
  $rsa=$this->get_by_id($id);
  $duplicate_check = (strcmp($rsa['name'],$name) != 0 );
    
  if($duplicate_check)
  {
    $rs=$this->get_by_name($name,$rsa['parent_id']);
    $do_update=is_null($rs);
  }
  
  if( $do_update )
  {
	  // Update name
	  $sql = "UPDATE nodes_hierarchy " .
	         "SET name='" . $this->db->prepare_string($name) . "'" .
			     "WHERE id={$id}";
  	$result=$this->db->exec_query($sql);
	  
	  if($result)
	  {
    	$sql = "UPDATE testplans " .
    	       "SET active={$active}," .
	           "notes='" . $this->db->prepare_string($notes). "' " .
	           "WHERE id=" . $id;
	    $result=$this->db->exec_query($sql); 
	  }
	}
	return($result ? 1 : 0);
}

// 20060805 - franciscom - added possibility to filter by test project id
function get_by_name($name,$tproject_id=0)
{
	$sql = " SELECT testplans.*, NH.name " .
	       " FROM testplans, nodes_hierarchy NH" .
	       " WHERE testplans.id=NH.id " . 
	       " AND NH.name = '" . $this->db->prepare_string($name) . "'";
	         
	if($tproject_id > 0 )
	{
	  $sql .= " AND NH.parent_id={$tproject_id}"; 
	}         

  $recordset = $this->db->get_recordset($sql);
  return($recordset);
}

/*
get info for one test project

20060805 - franciscom - added nodes_hierarchy.parent_id on result
*/
function get_by_id($id)
{
	$sql = " SELECT testplans.*,NH.name,NH.parent_id 
	         FROM testplans, nodes_hierarchy NH
	         WHERE testplans.id = NH.id
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
function get_linked_tcversions($id,$tcase_id=null,$keyword_id=0,$executed=null,$owner = null)

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

// 20060921 - franciscom
// added tc_id in order clause to maintain same order that navigation tree
$sql=" SELECT DISTINCT NHB.parent_id AS testsuite_id, " .
     "        NHA.parent_id AS tc_id," .
     "        T.tcversion_id AS tcversion_id, T.id AS feature_id," .
     "        E.tcversion_id AS executed, " .
     "        UA.user_id,UA.type,UA.status,UA.assigner_id ".
     " FROM nodes_hierarchy NHA " .
     " JOIN nodes_hierarchy NHB ON NHA.parent_id = NHB.id " .
     " JOIN testplan_tcversions T ON NHA.id = T.tcversion_id " .
     " {$executions_join} " .
     " {$keywords_join} " .
     " LEFT OUTER JOIN user_assignments UA ON UA.feature_id = T.id " . 
     " WHERE T.testplan_id={$id} {$keywords_filter} {$tc_id_filter} " .
     " AND (UA.type=" . $this->assignment_types['testcase_execution']['id'] . 
     "      OR UA.type IS NULL) ";
if (!is_null($owner))
	$sql .= " AND UA.user_id = {$owner}"; 
$sql .= " ORDER BY testsuite_id,tc_id";
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

function get_max_build_id($id)
{
	$sql = " SELECT MAX(builds.id) AS maxbuildid
	         FROM builds WHERE builds.testplan_id = {$id}";
	
	$recordset = $this->db->get_recordset($sql);
	$maxBuildID = 0;
	if ($recordset)
		$maxBuildID = intval($recordset[0]['maxbuildid']);
	
	return $maxBuildID;
}


function get_builds($id)
{
	$sql = " SELECT builds.id, builds.name, builds.notes 
	         FROM builds WHERE builds.testplan_id = {$id}
	         ORDER BY builds.name";
  $recordset = $this->db->get_recordset($sql);
  
  return $recordset;
}//end function



// $id   : test plan id 
// $items: assoc array key=tc_id value=tcversion_id
//
//
// 20060910 - franciscom
// added remove of records from user_assignments table
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
      
      // ----------------------------------------------------------------
      // 20060910 - franciscom 
      // to remove the assignment to users (if any exists)
      // we need the list of id
      $sql=" SELECT id AS link_id FROM testplan_tcversions 
             WHERE testplan_id={$id} {$in_clause} ";
	    // $link_id = $this->db->get_recordset($sql);
	    $link_ids = $this->db->fetchRowsIntoMap($sql,'link_id');
	    $features = array_keys($link_ids);
	    if( count($features) == 1)
	    {
	      $features=$features[0];
	    }
	    $this->assignment_mgr->delete_by_feature_id($features);
	    // ----------------------------------------------------------------      
	          
      // Delete from link table
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
// -------------------------------------------------------------------------------

// 20060919 - francisco.mancardi@gruppotesi.com
// $id: source testplan id
// $new_tplan_id: destination
// $tplan_name  != null => set this as the new name
// $tproject_id != null => set this as the new testproject for the testplan
//                         this allow us to copy testplans to differents
//                         test projects.
//
function copy_as($id,$new_tplan_id,$tplan_name=null,$tproject_id=null)
{
  // get source testplan general info
  $rs_source=$this->get_by_id($id);
  
  if(!is_null($tplan_name))
  {
    $sql="UPDATE nodes_hierarchy " .
         "SET name='" . $this->db->prepare_string(trim($tplan_name)) . "' " .
         "WHERE id={$new_tplan_id}";
    $this->db->exec_query($sql);
  }  
  
  if(!is_null($tproject_id))
  {
    $sql="UPDATE testplans " .
         "SET testproject_id={$tproject_id} " .
         "WHERE id={$new_tplan_id}";
    $this->db->exec_query($sql);
  }  
  
  $this->copy_builds($id,$new_tplan_id);
  $this->copy_linked_tcversions($id,$new_tplan_id);
  $this->copy_milestones($id,$new_tplan_id);
  //$this->copy_attachments($id,$new_tplan_id);
  $this->copy_user_roles($id,$new_tplan_id);
  $this->copy_priorities($id,$new_tplan_id);

} // end function


// $id: source testplan id
// $new_tplan_id: destination
//
function copy_builds($id,$new_tplan_id)
{
  $rs=$this->get_builds($id);
  
  if(!is_null($rs))
  {
    foreach($rs as $build)
    {
      $sql="INSERT builds (name,notes,testplan_id) " .
           "VALUES ('" . $this->db->prepare_string($build['name']) ."'," .
           "'" . $this->db->prepare_string($build['notes']) ."',{$new_tplan_id})";
           
      $this->db->exec_query($sql);     
    }
  }
}


// $id: source testplan id
// $new_tplan_id: destination
//
function copy_linked_tcversions($id,$new_tplan_id)
{
  $sql="SELECT * FROM testplan_tcversions WHERE testplan_id={$id} ";
       
  $rs=$this->db->get_recordset($sql);
  
  if(!is_null($rs))
  {
    foreach($rs as $elem)
    {
      $sql="INSERT INTO testplan_tcversions " .
           "(testplan_id,tcversion_id) " .
           "VALUES({$new_tplan_id}," . $elem['tcversion_id'] .")";
      $this->db->exec_query($sql);     
    }
  }
}

// $id: source testplan id
// $new_tplan_id: destination
//
function copy_milestones($id,$new_tplan_id)
{
  $sql="SELECT * FROM milestones WHERE testplan_id={$id} ";
  $rs=$this->db->get_recordset($sql);
  
  if(!is_null($rs))
  {
    foreach($rs as $mstone)
    {
      $sql="INSERT milestones (name,A,B,C,date,testplan_id) " .
           "VALUES ('" . $this->db->prepare_string($mstone['name']) ."'," .
           $mstone['A'] . "," . $mstone['B'] . "," . $mstone['C'] . "," . 
           "'" . $mstone['date'] . "',{$new_tplan_id})";
           
      $this->db->exec_query($sql);     
    }
  }
}


// $id: source testplan id
// $new_tplan_id: destination
//
function copy_user_roles($id,$new_tplan_id)
{
  $sql="SELECT * FROM user_testplan_roles WHERE testplan_id={$id} ";
       
  $rs=$this->db->get_recordset($sql);
  
  if(!is_null($rs))
  {
    foreach($rs as $elem)
    {
      $sql="INSERT INTO user_testplan_roles " .
           "(testplan_id,user_id,role_id) " .
           "VALUES({$new_tplan_id}," . $elem['user_id'] ."," . $elem['role_id'] . ")";
      $this->db->exec_query($sql);     
    }
  }
}

// $id: source testplan id
// $new_tplan_id: destination
//
function copy_priorities($id,$new_tplan_id)
{
  $sql="SELECT * FROM priorities WHERE testplan_id={$id} ";
  $rs=$this->db->get_recordset($sql);
  if(!is_null($rs))
  {
    foreach($rs as $pr)
    {
      $sql="INSERT priorities (risk_importance,priority,testplan_id) " .
           "VALUES ('" . $pr['risk_importance'] ."'," .
           "'" . $pr['priority'] . "',{$new_tplan_id})";
      $this->db->exec_query($sql);     
    }
  }
}


function delete($id)
{
  
  $the_sql=array();
  $main_sql=array();
  
  $the_sql[]="DELETE FROM user_testplan_roles WHERE testplan_id={$id}";
  $the_sql[]="DELETE FROM milestones WHERE testplan_id={$id}";
  $the_sql[]="DELETE FROM testplan_tcversions WHERE testplan_id={$id}";
  $the_sql[]="DELETE FROM builds WHERE testplan_id={$id}";
  $the_sql[]="DELETE FROM priorities WHERE testplan_id={$id}";
  $the_sql[]="DELETE FROM risk_assignments WHERE testplan_id={$id}";
  $the_sql[]="DELETE FROM cfield_execution_values WHERE testplan_id={$id}";
  
  // When deleting from executions, we need to clean related tables
  $the_sql[]="DELETE FROM execution_bugs WHERE execution_id ".
             "IN (SELECT id from executions WHERE testplan_id={$id})";
  $the_sql[]="DELETE FROM executions WHERE testplan_id={$id}";
  
  
  foreach($the_sql as $sql)
  {
    $this->db->exec_query($sql);  
  }

  // ------------------------------------------------------------------------
  // attachments need special care
  $sql="SELECT * FROM attachments WHERE fk_id={$id} AND fk_table='testplans'";
  $rs=$this->db->get_recordset($sql);  
  if(!is_null($rs))
  {
    foreach($rs as $elem)
    {
       deleteAttachment($this->db,$elem['id']);
    }  
  }
  // ------------------------------------------------------------------------  
  
  // Finally delete from main table
  $main_sql[]="DELETE FROM testplans WHERE id={$id}";
  $main_sql[]="DELETE FROM nodes_hierarchy WHERE id={$id}";
  
  foreach($main_sql as $sql)
  {
    $this->db->exec_query($sql);  
  }
} // end delete()


} // end class
?>
