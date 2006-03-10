<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: tree.class.php,v $
 *
 * @version $Revision: 1.4 $
 * @modified $Date: 2006/03/10 07:42:44 $ by $Author: franciscom $
 * @author Francisco Mancardi
*/

// 20060218 - franciscom
class tree 
{

  // configurable values - pseudoconstants
  var $node_types = array( 1 => 'testproject','testsuite','testcase','tcversion','testplan');

  var $node_tables = array('testproject' => 'testprojects',
                           'testsuite'   => 'testsuites',
                           'testplan'    => 'testplans',
                           'testcase'    => 'testcases',
                           'tcversion'   => 'tcversions');
 
  
  
  var $ROOT_NODE_TYPE_ID=1;
  var $ROOT_NODE_PARENT_ID=NULL;
  
  var $db;  // Database Handler
  
    
	function tree(&$db) 
	{
    $this->db = $db;
  }

  /*
  20060219 - franciscom
  
  */
	function get_available_node_types() 
	{
    $sql = " SELECT * FROM node_types "; 

    $ntypes=$this->db->get_recordset($sql);
    foreach($ntypes as $elem)
    {
     $hash_ntypes[$elem['description']] = $elem['id'];
    }
    return ($hash_ntypes);
  }



  /*
    create a new root node in the hierarchy table
    
  
    returns: node_id of the new node created
    
    rev    : 20060218 - franciscom
    
  */
	function new_root_node() 
	{

    $sql = " INSERT INTO nodes_hierarchy 
             (node_type_id, node_order) 
             VALUES({$this->ROOT_NODE_TYPE_ID},1)";
    $this->db->exec_query($sql);
    
    return ($this->db->insert_id());
  }


  /*
    create a new  node in the hierarchy table
    returns: node_id of the new node created

    rev    : 20060218 - franciscom
  */
	function new_node($parent_id,$node_type_id,$node_order=0) 
	{
    $sql = " INSERT INTO nodes_hierarchy 
             (parent_id,node_type_id, node_order) 
             VALUES({$parent_id},{$node_type_id},{$node_order})";
    $this->db->exec_query($sql);
    
    return ($this->db->insert_id());
  }

  /*
    get all node hierarchy info from hierarchy table
    
    
  
    returns: node_id of the new node created
    rev    : 20060218 - franciscom
    
  */
	function get_node_hierachy_info($node_id) 
	{
    $sql = " SELECT * FROM nodes_hierarchy 
             WHERE id ={$node_id} ";
             
    $result=$this->db->exec_query($sql);
    return ($this->db->fetch_array($result));
  }


  //     rev    : 20060218 - franciscom
  function get_descendants($node_id, $level) 
  {
    $sql = " SELECT * FROM nodes_hierarchy 
             WHERE parent_id = {$node_id} ";
    
    $result = $this->db->exec_query($sql);
    
    // display each child
    while ($row = $this->db->fetch_array($result)) 
    {
     echo str_repeat(' ',$level) . $row['id'] ."\n";

     $this->get_descendants($row['id'], $level+1);
    }
  }


function get_subtree_list($node_id)
{
  $sql = " SELECT * from nodes_hierarchy
          WHERE parent_id = {$node_id} ";
 
  $node_list='';  
  $result = $this->db->exec_query($sql);
  
  if( $this->db->num_rows($result) == 0 )
  {
    return(null); 	
  }
  
  while ( $row = $this->db->fetch_array($result) )
  {
    $node_list .= $row['id'] . ",";
    
    $xx_list = $this->get_subtree_list($row['id']);	
  	
  	if( !is_null($xx_list) )
  	{
  		$node_list .= $xx_list;
  	}
  }
  return (rtrim($node_list,","));
}


// 20060305 - franciscom
// added $exclude_node_types,$exclude_children_of
//
function get_subtree($node_id,$exclude_node_types=null,
                              $exclude_children_of=null,$exclude_branches=null)
{

  $sql = " SELECT * from nodes_hierarchy
          WHERE parent_id = {$node_id} ";
 
 // echo "<pre>debug" . __FUNCTION__ ; print_r($sql); echo "</pre>";
 
  $node_list=array();  
  $result = $this->db->exec_query($sql);
  
  if( $this->db->num_rows($result) == 0 )
  {
  	//echo "NO CHILDREN<br>";
    return(null); 	
  }
  
  //echo $node_id . " HAS  CHILDREN:: ". $this->db->num_rows($result) ."<br>";

  
  
  while ( $row = $this->db->fetch_array($result) )
  {
    // ----------------------------------------------------------------------------
    // Getting data from the node specific table
    $node_table = $this->node_tables[$this->node_types[$row['node_type_id']]];

    /*echo "\$node_id=" . $row['id'] ."<br>";
    echo "\$parent_id=" . $row['parent_id'] ."<br>";
    echo "child ::" . $this->node_types[$row['node_type_id']] . "<br>";
    echo "<pre>\$exclude_node_types"; print_r($exclude_node_types); echo "</pre>";  
    echo "<pre>\$exclude_branches"; print_r($exclude_branches); echo "</pre>";  
    */  
    if( !isset($exclude_node_types[$this->node_types[$row['node_type_id']]]) &&
        !isset($exclude_branches[$row['id']])
      )
    {
      //echo "node :: accepted <br><br>";
      $sql = "SELECT name FROM {$node_table} 
      	      WHERE id = {$row['id']}";
      $result_node = $this->db->exec_query($sql);        
      
      $item_name='';        
      if( $this->db->num_rows($result_node) == 1 )
      {
      	$item_row  = $this->db->fetch_array($result_node);
      	$item_name = $item_row['name'];	
      }
      // ----------------------------------------------------------------------------        
  
      $node_list[] = array('id'        => $row['id'],
                           'parent_id' => $row['parent_id'],
                           'node_type_id' => $row['node_type_id'],
                           'node_order' => $row['node_order'],
                           'node_table' => $node_table,
                           'name' => $item_name);
      
      // Basically we use this because:
      // We don't want the children if the parent is a testcase,
      // due to the version management
      if( !isset($exclude_children_of[$this->node_types[$row['node_type_id']]]) && 
          !isset($exclude_branches[$row['id']])
        )
      {
      	//echo "Exploring children of {$row['id']} <br>";
    	  $xx_list = $this->get_subtree($row['id'],
    	                                $exclude_node_types,$exclude_children_of,$exclude_branches);	
     	  
     	  if( !is_null($xx_list) )
    	  {
    		  $ma = array_merge($node_list,$xx_list);
    		  $node_list = $ma;
    	  }
    	}
      // ------------------------------------------------------------------------------------- 	
  	}
  	/*
  	else
  	{
  	  //echo "node :: REJECTED <br><br>";
  	}
  	*/
  }
  return ($node_list);
}


function get_xx($aa)
{
  // useful to group ids for table name trying
  // to reduce amount of querie using IN clause
   
	$xx=array();
  foreach($aa as $key => $value)
  {
  	print_r($value);
  	echo "<br>";
  	$xx[$value['node_table']]['id'][]=$value['id'];
  	$xx[$value['node_table']]['key_id'][$value['id']]=$key;
  }
  
  foreach($xx as $key => $value)
  {
    $zz = implode(",", $value['id']);
    echo $key . "<br>";
    echo $zz . "<br>";
  }
  return($xx);
}



/*

*/
function delete_subtree($node_id)
{
 // echo "\$node_id ={$node_id} <br>";	
 $children=$this->get_subtree_list($node_id);
 $id2del=$node_id;
 if( strlen(trim($children)) > 0)
 {
   $id2del .= ",{$children}";	
 }
 $sql = "DELETE FROM nodes_hierarchy WHERE nodes_hierarchy.id IN ({$id2del})";
 $result = $this->db->exec_query($sql);
 
}



// $node is the name of the node we want the path of
function get_path($node_id,$to_node_id=null) 
{
	
// look up the parent of this node
 $sql = " SELECT * from nodes_hierarchy
          WHERE id = {$node_id} ";
 
 $node_list=array();  
 $result = $this->db->exec_query($sql);
 
 if( $this->db->num_rows($result) == 0 )
 {
    return(null); 	
 }
  
 while ( $row = $this->db->fetch_array($result) )
 {
   
   // only continue if this $node isn't the root node
   // (that's the node with no parent)
   
   if ($row['parent_id'] != '' && $row['id'] != $to_node_id) 
   {
   	  // 20060309 - franciscom
      // Getting data from the node specific table
      $node_table = $this->node_tables[$this->node_types[$row['node_type_id']]];
      
      $sql = "SELECT name FROM {$node_table} 
      	      WHERE id = {$row['id']}";
      $result_node = $this->db->exec_query($sql);        
      
      $item_name='';        
      if( $this->db->num_rows($result_node) == 1 )
      {
      	$item_row  = $this->db->fetch_array($result_node);
      	$item_name = $item_row['name'];	
      }
      
      
   		// the last part of the path to $node, is the name
   		// of the parent of $node
      $node_list[] = array('id'        => $row['id'],
                           'parent_id' => $row['parent_id'],
                           'node_type_id' => $row['node_type_id'],
                           'node_order' => $row['node_order'],
                           'node_table' => $node_table,
                           'name' => $item_name );

			
      // we should add the path to the parent of this node
      // to the path
      $node_list = array_merge($this->get_path($row['parent_id'],$to_node_id), $node_list);
   }
 }
 return $node_list;
}


/* 20060306 - franciscom */
function change_parent($node_id, $parent_id) 
{
  $sql = "UPDATE nodes_hierarchy
          SET parent_id = {$parent_id}
          WHERE id = {$node_id}";
  $result = $this->db->exec_query($sql);
 
  return ($result);

}
 
 
/* 20060306 - franciscom */
function get_children($id,$exclude_node_types=null) 
{
  $sql = " SELECT * from nodes_hierarchy
          WHERE parent_id = {$node_id} ORDER BY node_order";

  $node_list=array();  
  $result = $this->db->exec_query($sql);
 
  if( $this->db->num_rows($result) == 0 )
  {
    return(null); 	
  }

  while ( $row = $this->db->fetch_array($result) )
  {
    // ----------------------------------------------------------------------------
    // Getting data from the node specific table
    $node_table = $this->node_tables[$this->node_types[$row['node_type_id']]];

    if( !isset($exclude_node_types[$this->node_types[$row['node_type_id']]]))
    {
      $sql = "SELECT name FROM {$node_table} 
      	      WHERE id = {$row['id']}";
      $result_node = $this->db->exec_query($sql);        
      
      $item_name='';        
      if( $this->db->num_rows($result_node) == 1 )
      {
      	$item_row  = $this->db->fetch_array($result_node);
      	$item_name = $item_row['name'];	
      }
      $node_list[] = array('id'        => $row['id'],
                           'parent_id' => $row['parent_id'],
                           'node_type_id' => $row['node_type_id'],
                           'node_order' => $row['node_order'],
                           'node_table' => $node_table,
                           'name' => $item_name);
  	}
  }
  return ($node_list);
}
 
 
 
 
}// end class
?>



