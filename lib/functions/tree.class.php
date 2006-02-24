<?php

// 20060218 - franciscom
class tree 
{

  // configurable values - pseudoconstants
  var $node_types = array( 1 => 'testproject','testsuite',
                                'testplan','testcase','tcversion');

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
	function get_node_hirerachy_info($node_id) 
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


/*
function get_subtree_list($node_id)
{
  $sql = " SELECT * from nodes_hierarchy
          WHERE parent_id = {$node_id} ";
 
  $node_list='';  
  $result = $this->dbh->exec_query($sql);
  
  if( $this->dbh->num_rows($result) == 0 )
  {
    return(null); 	
  }
  
  while ( $row = $this->dbh->fetch_array($result) )
  {
  	
    $node_list .= $row['node_id'] . ",";
    
    $xx_list = $this->get_subtree_list($row['node_id']);	
  	
  	if( !is_null($xx_list) )
  	{
  		$node_list .= $xx_list;
  	}
  }
  return ($node_list);
}
*/

function get_subtree($node_id)
{
  $sql = " SELECT * from nodes_hierarchy
          WHERE parent_id = {$node_id} ";
 
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

    $sql = "SELECT name FROM {$node_table} 
            WHERE id = {$row['id']}";
    $result_node = $this->db->exec_query($sql);        
    
    $item_name='';        
    if( $this->db->num_rows($result_node) == 0 )
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
    
    $xx_list = $this->get_subtree($row['id']);	
  	
  	if( !is_null($xx_list) )
  	{
  		$ma = array_merge($node_list,$xx_list);
  		$node_list = $ma;
  	}
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

  
}// end class

?>



