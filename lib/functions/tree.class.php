<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @package 	TestLink
 * @author Francisco Mancardi
 * @copyright 	2005-2009, TestLink community 
 * @version    	CVS: $Id: tree.class.php,v 1.93.2.1 2010/11/20 17:00:18 franciscom Exp $
 * @link 		http://www.teamst.org/index.php
 *
 * @internal Revisions:
 * 20101120 - franciscom - get_full_path_verbose() refactoring return when error
 * 20101009 - franciscom - order_cfg config options, added filtering by platform_id when 
 *						   order done by exec_order on _get_subtree_rec()
 *
 * 20101009 - franciscom - _get_subtree_rec(), _get_subtree() added tcversion_id in output set
 * 20101003 - franciscom - and_not_in_clause -> additionalWhereClause
 *						   interface changes -> _get_subtree_rec(), _get_subtree()
 *						   Added new option on remove_empty_nodes_of_type on _get_subtree_rec()  	
 * 20100920 - Julian - get_full_path_verbose - added new output format
 * 20100918 - franciscom - BUGID 3790 - delete_subtree_objects()
 * 20100317 - franciscom - get_node_hierarchy_info() interface changes.
 * 20100306 - franciscom - get_subtree_list() new argument to change output type
 *						   new method() - getAllItemsID - BUGID 0003003: EXTJS does not count # req's
 * 20100209 - franciscom - BUGID 3147 - Delete test project with requirements defined crashed with memory exhausted
 * 20091220 - franciscom - new method createHierarchyMap()
 * 20090926 - franciscom - get_subtree() - interface changes
 * 20090923 - franciscom - get_full_path_verbose() - fixed bug
 * 20090905 - franciscom - get_full_path_verbose() new options
 * 20090801 - franciscom - new method nodeNameExists()
 * 20090726 - franciscom - BUGID 2728 
 * 20090607 - franciscom - refactoring to manage table prefix
 * 20090413 - franciscom - BUGID - get_full_path_verbose() interface changes
 * 20090313 - franciscom - added getTreeRoot()
 * 20090207 - franciscom - new method check_name_is_unique()
 * 20081227 - franciscom - new method - get_full_path_verbose()
 */

/**
 * @package TestLink
 */
class tree extends tlObject
{
	// configurable values - pseudoconstants
	var $node_types = array( 1 => 'testproject','testsuite',
	                              'testcase','tcversion','testplan',
	                              'requirement_spec','requirement','req_version');

  // key: node type id, value: class name
	var $class_name = array( 1 => 'testproject','testsuite',
	                              'testcase',null,'testplan',
	                              'requirement_spec_mgr','requirement_mgr',null);
	                              
	var $node_descr_id = array();
	
	var $node_tables = array('testproject' => 'testprojects',
                             'testsuite' => 'testsuites',
                             'testplan' => 'testplans',
                             'testcase' => 'testcases',
                             'tcversion' => 'tcversions',
                             'requirement_spec' =>'req_specs',
                             'requirement' => 'requirements',  
                             'req_version' => 'req_versions');
  
	var $ROOT_NODE_TYPE_ID = 1;
	var $ROOT_NODE_PARENT_ID = NULL;

	/** @var resource database handler */
	var $db;

	/**
	 * Class costructor
	 * @param resource &$db reference to database handler
	 */
	function __construct(&$db) 
	{
   		parent::__construct();
		$this->db = &$db;
		$this->node_descr_id = array_flip($this->node_types);
        $this->object_table = $this->tables['nodes_hierarchy'];
	}

  	/**
  	 *  get info from node_types table, regarding node types
  	 *        that can be used in a tree. 
  	 * 
  	 * @return array map
     *        key: description: single human friendly string describing node type
     *        value: numeric code used to identify a node type
	 */
	function get_available_node_types() 
	{
		static $s_nodeTypes;
		if (!$s_nodeTypes)
		{
			$sql = " SELECT * FROM {$this->tables['node_types']} "; 
			$s_nodeTypes = $this->db->fetchColumnsIntoMap($sql,"description","id");
		}
		return $s_nodeTypes;
	}

	/**
	 * creates a new root node in the hierarchy table.
	 *        root node is tree starting point.
	 * 
	 * @param string $name node name; default=''
	 * @return integer node ID
	 */
	function new_root_node($name = '') 
	{
		$this->new_node(null,$this->ROOT_NODE_TYPE_ID,$name,1);
		return $this->db->insert_id($this->object_table);
	}

	/*
    function: new_node
              creates a new node in the hierarchy table.
              root node is tree starting point.

    args : parent_id: node id of new node parent
           node_type_id: node type
           [name]: node name. default=''
           [node_order]= order on tree structure. default=0
           [node_id]= id to assign to new node, if you don't want
                      id bein created automatically.
                      default=0 -> id must be created automatically.
    
    returns: node_id of the new node created

  */
	function new_node($parent_id,$node_type_id,$name='',$node_order=0,$node_id=0) 
	{
		$sql = "INSERT INTO {$this->object_table} " .
		       "(name,node_type_id,node_order";

		$values=" VALUES('" . $this->db->prepare_string($name). "'," .
		        " {$node_type_id}," . intval($node_order);
		if ($node_id)
		{
			$sql .= ",id";
			$values .= ",{$node_id}";
		}
		
		if(is_null($parent_id))
		{
			$sql .= ") {$values} )";
		}
		else
		{
			$sql .= ",parent_id) {$values},{$parent_id})";
        }

		$this->db->exec_query($sql);
		return ($this->db->insert_id($this->object_table));
 	}

	/*
	get all node hierarchy info from hierarchy table
	returns: node_id of the new node created
	
	
	*/
	/*
    function: get_node_hierarchy_info
              returns the row from nodes_hierarchy table that has
              node_id as id.
              
              get all node hierarchy info from hierarchy table

    args : node_id: node id
                    can be an array
           [parent_id]         
    
    returns: 

  */
	function get_node_hierarchy_info($node_id,$parent_id = null) 
	{
	  	$sql = "SELECT * FROM {$this->object_table} WHERE id";
	  	$getidx=-1;
	  	$result=null;
	  	
	  	if( is_array($node_id) )
	  	{
	  	    $sql .= " IN (" . implode(",",$node_id) . ") ";
      	  	$result=$this->db->fetchRowsIntoMap($sql,'id');    
	  	}
	  	else
	  	{
	  	    $sql .= "= {$node_id}";
	  	    if( !is_null($parent_id) )
	  	    {
	  	    	$sql .= " AND parent_id={$parent_id} ";	
	  	    }
			$rs=$this->db->get_recordset($sql);
			$result=!is_null($rs) ? $rs[0] : null;
	  	} 
		return $result;
	}

	/*
    function: get_subtree_list()
              get a string representing a list, where elements are separated
              by comma, with all nodes in tree starting on node_id.
              node is can be considered as root of subtree.
              
    args : node_id: root of subtree
           node_type_id: null => no filter
           				if present ONLY NODES OF this type will be ANALIZED and traversed
           				Example:
           				TREE
           					|__ TSUITE_1
           							|
           							|__TSUITE_2
           							|     |__TC_XZ
           							|
           							|__TC1
           							|__TC2
           							
           				node_type_id = TC and ROOT=Tree => output=NULL			
           				node_type_id = TC and ROOT=TSUITE_1 => output=TC1,TC2

           				
           output: null => list, not null => array

    
    returns: output=null => list (string with nodes_id, using ',' as list separator).
             output != null => array

	*/
	function get_subtree_list($node_id,$node_type_id=null,$output=null)
	{
	    $nodes = array();
	  	$this->_get_subtree_list($node_id,$nodes,$node_type_id);
	  	$node_list = is_null($output) ? implode(',',$nodes) : $nodes;
	    return($node_list);
	}
  
  
  /*
    function: _get_subtree_list()
              private function (name start with _), that using recursion
              get an array with all nodes in tree starting on node_id.
              node is can be considered as root of subtree.


    args : node_id: root of subtree
    
    returns: array with nodes_id

  */
	function _get_subtree_list($node_id,&$node_list,$node_type_id=null)
	{
		$sql = "SELECT * from {$this->object_table} WHERE parent_id = {$node_id}";
		if( !is_null($node_type_id) )
		{
		    $sql .=  " AND node_type_id = {$node_type_id} "; 
		}
		$result = $this->db->exec_query($sql);
		
		if (!$result || !$this->db->num_rows($result))
		{
			return;
		}
		
		while($row = $this->db->fetch_array($result))
		{
			$node_list[] = $row['id'];
			$this->_get_subtree_list($row['id'],$node_list,$node_type_id);	
		}
	}

  /*
    function: delete_subtree
              delete all element on tree structure that forms a subtree
              that has as root or starting point node_id.

    args : node_id: root of subtree
    
    returns: array with nodes_id

  */
	function delete_subtree($node_id)
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
		
		$children = $this->get_subtree_list($node_id);
		$id2del = $node_id;
		if($children != "")
		{
			$id2del .= ",{$children}";	
		}
		$sql = "/* $debugMsg */ DELETE FROM {$this->object_table} WHERE id IN ({$id2del})";
	
		$result = $this->db->exec_query($sql);
	}


  /*
    function: get_path
              get list of nodes to traverse when you want to move 
              from node A (node at level N) to node B (node at level M),
              where MUST BE ALLWAYS M < N, and remembering that level for root node is the minimun.
              This means path on tree backwards (to the upper levels).
              An array is used to represent list.
              Last array element contains data regarding Node A, first element (element with index 0) 
              is data regarding child of node B.
              What data is returned depends on value of optional argument 'format'.
              
              Attention:
              1 - destination node (node B) will be NOT INCLUDED in result.
              2 - This is refactoring of original get_path method.

    args : node_id: start of path
           [to_node_id]: destination node. default null -> path to tree root.
           [format]: default 'full' 
                     defines type of elements of result array.
                     
                     format='full'
                     Element is a map with following keys:
                     id
                     parent_id
                     node_type_id
                     node_order
                     node_table
                     name
                     
                     Example
                     Is tree is :
                                
                              null 
                                \
                               id=1   <--- Tree Root
                                 |
                                 + ------+
                               /   \      \
                            id=9   id=2   id=8
                                    \
                                     id=3
                                      \
                                       id=4     
                    
                    
                    get_path(4), returns:
                          
                    (
                     [0] => Array([id] => 2
                                  [parent_id] => 1
                                  [node_type_id] => 2
                                  [node_order] => 1
                                  [node_table] => testsuites
                                  [name] => TS1)
        
                     [1] => Array([id] => 3
                                  [parent_id] => 2
                                  [node_type_id] => 2
                                  [node_order] => 1
                                  [node_table] => testsuites
                                  [name] => TS2)
        
                     [2] => Array([id] => 4
                                  [parent_id] => 3
                                  [node_type_id] => 3
                                  [node_order] => 0
                                  [node_table] => testcases
                                  [name] => TC1)
                    )
                  
                    
                    
                    format='simple'
                    every element is a number=PARENT ID, array index = value
                    For the above example result will be:
                    (
                     [1] => 1
                     [2] => 2
                     [3] => 3
                    )
                    
                    

    returns: array

  */
	function get_path($node_id,$to_node_id = null,$format = 'full') 
	{
		$the_path = array();
		$this->_get_path($node_id,$the_path,$to_node_id,$format); 
		
		if( !is_null($the_path) && count($the_path) > 0 )
		{
			$the_path=array_reverse($the_path);  
		}
		return $the_path;
	}


	/*
	  function: _get_path
	            This is refactoring of original get_path method.
	            Attention:
	            returns node in inverse order, that was done for original get_path
	
	  args : node_id: start of path
	         node_list: passed by reference, to build the result.
	         [to_node_id]: destination node. default null -> path to tree root.
	         [format]: default 'full' 
	  
	  returns: array
	*/
	function _get_path($node_id,&$node_list,$to_node_id=null,$format='full') 
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

		// look up the parent of this node
		$sql = "/* $debugMsg */ " . 
		       " SELECT * from {$this->object_table} " .
			   " WHERE id = {$node_id} ";
		
		$result = $this->db->exec_query($sql);
		if( $this->db->num_rows($result) == 0 )
		{
			$node_list=null;
			return; 	
		}
		
		while ( $row = $this->db->fetch_array($result) )
		{
			
			// only continue if this $node isn't the root node
			// (that's the node with no parent)
			if ($row['parent_id'] != '' && $row['id'] != $to_node_id) 
			{
				// Getting data from the node specific table
				$node_table = $this->node_tables[$this->node_types[$row['node_type_id']]];
				
				// the last part of the path to $node, is the name
				// of the parent of $node
				switch($format)
				{
					case 'full':
						$node_list[] = array('id' => $row['id'],
							'parent_id' => $row['parent_id'],
							'node_type_id' => $row['node_type_id'],
							'node_order' => $row['node_order'],
							'node_table' => $node_table,
							'name' => $row['name'] );
						break;    
						
					case 'simple':
						// Warning: starting node is NOT INCLUDED in node_list
						$node_list[$row['parent_id']] = $row['parent_id'];
						break;    
						
					case 'points':
						$node_list[] = $row['id'];
						break;    
						
				}
				
				// if( $format == "full" )
				// {
				// 	$node_list[] = array('id' => $row['id'],
				//   		                 'parent_id' => $row['parent_id'],
				//       		             'node_type_id' => $row['node_type_id'],
				//           		         'node_order' => $row['node_order'],
				//               		     'node_table' => $node_table,
				//                   		 'name' => $row['name'] );
				// }
				// else
				// {
				// 	$node_list[$row['parent_id']] = $row['parent_id'];
				// }
				
				// we should add the path to the parent of this node to the path
				$this->_get_path($row['parent_id'],$node_list,$to_node_id,$format);
			}
		}
	}
	
	
	
	
	/*
	  function: change_parent
	            change node parent, using this method you implement move operation.
	
	  args : node_id: node/nodes that need(s) to changed.
	                  mixed type: single id or array containing set of id.
	                  
	         parent_id: new parent
	  
	  returns: 1 -> operation OK
	  
	  rev : 20080330 - franciscom - changed node_id type, to allow bulk operation.
	  
	*/
	function change_parent($node_id, $parent_id) 
	{
    	$debugMsg='Class:' .__CLASS__ . ' - Method:' . __FUNCTION__ . ' :: ';
		if( is_array($node_id) )
		{
			$id_list = implode(",",$node_id);
			$where_clause = " WHERE id IN ($id_list) ";
		}
		else
		{
			$where_clause=" WHERE id = {$node_id}";
		}
		$sql = "/* $debugMsg */ UPDATE {$this->object_table} SET parent_id = {$parent_id} {$where_clause}";
		
		$result = $this->db->exec_query($sql);
		
		return $result ? 1 : 0;
	}
	 
	 
	/*
	  function: get_children
	            get nodes that have id as parent node.
	            Children can be filtering according to node type.
	            
	  args : id: node 
	         [exclude_node_types]: map 
	                               key: verbose description of node type to exclude.
	                                    see get_available_node_types.
	                               value: anything is ok
	  
	  returns: array of maps that contain children nodes.
	           map structure:
	           id 
	           name
	           parent_id
	           node_type_id
	           node_order
	           node_table
	          
	           
	*/
	function get_children($id,$exclude_node_types=null) 
	{
		$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
		
	  	$sql = "/* $debugMsg */ SELECT * from {$this->object_table} " .
	  	       " WHERE parent_id = {$id} ORDER BY node_order,id";
	  	
	  	$node_list=array();  
	  	$result = $this->db->exec_query($sql);
	  	
	  	if( $this->db->num_rows($result) == 0 )
	  	{
	  	  return(null); 	
	  	}
	  	
	  	while ( $row = $this->db->fetch_array($result) )
	  	{
	  	  	$node_table = $this->node_tables[$this->node_types[$row['node_type_id']]];
	  	  	if( !isset($exclude_node_types[$this->node_types[$row['node_type_id']]]))
	  	  	{
	  	    	$node_list[] = array('id' => $row['id'], 'parent_id' => $row['parent_id'],
	  	        	                 'node_type_id' => $row['node_type_id'],
	  	            	             'node_order' => $row['node_order'],
	  	                	         'node_table' => $node_table,'name' => $row['name']);
	  		}
	  	}
	  	return ($node_list);
	}
	
	 
	/*
	  function: change_order_bulk
	            change order for all nodes is present in nodes array.
	            Order of node in tree, is set to position node has in nodes array.
	
	  args :
	         nodes: array where value is node_id. Node order = node position on array
	   
	  returns: -
	
	*/
	function change_order_bulk($nodes) 
	{
		foreach($nodes as $order => $node_id)
		{
			$order = abs(intval($order));
			$node_id = intval($node_id);
		  	$sql = "UPDATE {$this->object_table} SET node_order = {$order} WHERE id = {$node_id}";
		  	$result = $this->db->exec_query($sql);
		}
	}
	
	
	/*
	  function: change_child_order
	            will change order of children of parent id, to position
	            choosen node on top or bottom of children.             
	
	  args:
	        parent_id: node used as root of a tree.
	        node_id: node which we want to reposition
	        $top_bottom: possible values 'top', 'bottom'
	        [exclude_node_types]: map 
	                              key: verbose description of node type to exclude.
	                                   see get_available_node_types.
	                              value: anything is ok
	
	   
	  returns: -
	
	*/
	function change_child_order($parent_id,$node_id,$top_bottom,$exclude_node_types=null)
	{
	    $node_type_filter='';
	    if( !is_null($exclude_node_types) )
	    {
	       $types=implode("','",array_keys($exclude_node_types));  
	       $node_type_filter=" AND NT.description NOT IN ('{$types}') ";
	    }
	    
	    $sql = " SELECT NH.id, NH.node_order, NH.name " .
	           " FROM {$this->object_table} NH, {$this->tables['node_types']} NT " .
	           " WHERE NH.node_type_id=NT.id " .
	           " AND NH.parent_id = {$parent_id} AND NH.id <> {$node_id} " . 
	           $node_type_filter .
	           " ORDER BY NH.node_order,NH.id";
	    $children=$this->db->get_recordset($sql);
	    
	    switch ($top_bottom)
	    {
	        case 'top':
	        $no[]=$node_id;
	        if( !is_null($children) )
	        {
	            foreach($children as $key => $value)
	            {
	              $no[]=$value['id'];     
	            }
	        }
	        break;
	          
	        case 'bottom':  
	        $new_order=$this->getBottomOrder($parent_id)+1;
	        $no[$new_order]=$node_id;
	        break;
	    }
	    $this->change_order_bulk($no);    
	} 
	
	/*
	  function: getBottomOrder
	            given a node id to be used as parent, returns  the max(node_order) from the children nodes.
	            We consider this bottom order.
	
	  args: parentID: 
	  
	  returns: order
	
	*/
	function getBottomOrder($parentID)
	{
	    $sql="SELECT MAX(node_order) AS top_order" .
	         " FROM {$this->object_table} " . 
	         " WHERE parent_id={$parentID} " .
	         " GROUP BY parent_id";
	    $rs=$this->db->get_recordset($sql);
	    
	    return $rs[0]['top_order'];     
	}
	
	
	
	
	/*
	  function: get_subtree
	            Giving a node_id, get the nodes that forma s subtree that 
	            has node_id as root or starting point.
	
	            Is possible to exclude:
	            branches that has as staring node, node of certain types.
	            children of some node types.
	            full branches.
	            
	
	  args :
			[filters] map with following keys	
	
	        [exclude_node_types]: map/hash. 
	                              default: null -> no exclusion filter will be applied.
	                              Branches starting with nodes of type detailed, will not be
	                              visited => no information will be returned.
	                              key: verbose description of node type to exclude.
	                                   (see get_available_node_types).
	                              value: can be any value, because is not used,anyway is suggested 
	                                     to use 'exclude_me' as value.
	                              
	                              Example:
	                              array('testplan' => 'exclude_me')
	                              Node of type tesplan, will be excluded. 
	                             
	                             
	        
	        [exclude_children_of]: map/hash
	                              default: null -> no exclusion filter will be applied.
	                              When traversing tree if the type of a node child, of node under analisys,
	                              is contained in this map, traversing of branch starting with this child node
	                              will not be done.
	                              key: verbose description of node type to exclude.
	                                   (see get_available_node_types).
	                              value: can be any value, because is not used,anyway is suggested 
	                                     to use 'exclude_my_children' as value.
	                              
	                              Example:        
	                              array('testcase' => 'exclude_my_children')                               
	                              Children of testcase nodes, (tcversion nodes) will be EXCLUDED.         
	        
	        [exclude_branches]: map/hash. 
	                            default: null -> no exclusion filter will be applied.
	                            key: node id.
	                            value: anything is ok.
	                            
	                            When traversing tree branches that have these node is, will
	                            not be visited => no information will be retrieved.
	        
	        
	        [additionalWhereClause]: sql filter to include in sql sentence used to retrieve nodes.
	                                 default: null -> no action taken.
	                              
	        [family]: used to include guide the tree traversal.
	                  map where key = node_id TO INCLUDE ON traversal
	                  			value = map where each key is a CHILD that HAS TO BE INCLUDED in return set.                      
	                              
	        [options]: map with following keys
	                              
	        [recursive]: changes structure of returned structure.
	                      default: false -> a flat array will be generated
	                               true  -> a map with recursive structure will be generated.
	                      
	                      false returns array, every element is a map with following keys:
	                      
	                      id
	                      parent_id
	                      node_type_id
	                      node_order
	                      node_table
	                      name
	                      
	                      
	                      true returns a map, with only one element
	                      key: childNodes.
	                      value: array, that represents a tree branch.
	                             Array elements are maps with following keys:
	                      
	                             id
	                             parent_id
	                             node_type_id
	                             node_order
	                             node_table
	                             name
	                             childNodes -> (array)
	                      
	          
	  returns: array or map
	  
	  rev: 
	       20090311 - franciscom
	       changed management of order_cfg.
	       
	       20080614 - franciscom
	       added key_type arguments, useful only fo recursive mode
	
	*/
	function get_subtree($node_id,$filters=null,$options=null)
	{
        $my['filters'] = array('exclude_node_types' => null, 'exclude_children_of' => null,
	                           'exclude_branches' => null,'additionalWhereClause' => '', 'family' => null);
                               
        $my['options'] = array('recursive' => false, 'order_cfg' => array("type" =>'spec_order'), 'key_type' => 'std');
	
		// Cast to array to handle $options = null
		$my['filters'] = array_merge($my['filters'], (array)$filters);
		$my['options'] = array_merge($my['options'], (array)$options);
	
		$the_subtree = array();
	 		
		// Generate NOT IN CLAUSE to exclude some node types
	 	// $not_in_clause = $my['filters']['additionalWhereClause'];
	 	if(!is_null($my['filters']['exclude_node_types']))
	  	{
	  		$exclude = array();
			foreach($my['filters']['exclude_node_types'] as $the_key => $elem)
	    	{
	      		$exclude[] = $this->node_descr_id[$the_key];
	    	}
	    	$my['filters']['additionalWhereClause'] .= " AND node_type_id NOT IN (" . implode(",",$exclude) . ")";
	  	}

	    if ($my['options']['recursive'])
	    {
		    $this->_get_subtree_rec($node_id,$the_subtree,$my['filters'],$my['options']);
		}
		else
		{
		    $this->_get_subtree($node_id,$the_subtree,$my['filters'],$my['options']);
	    }
	 	return $the_subtree;
	}
	
	
	// 20101009 - franciscom - added tcversion_id in output set
	// 20061008 - franciscom - added ID in order by clause
	// 
	// 20060312 - franciscom
	// Changed and improved following some Andreas Morsing advice.
	//
	// I would like this method will be have PRIVate scope, but seems not possible in PHP4
	// that's why I've prefixed with _
	//
	function _get_subtree($node_id,&$node_list,$filters = null, $options = null)
	{

        $my['filters'] = array('exclude_children_of' => null,'exclude_branches' => null,
        					   'additionalWhereClause' => '', 'family' => null);
                               
        $my['options'] = array('order_cfg' => array("type" =>'spec_order'),'key_type' => 'std');

		// Cast to array to handle $options = null
		$my['filters'] = array_merge($my['filters'], (array)$filters);
		$my['options'] = array_merge($my['options'], (array)$options);

		// init old variables, till we refactor
		$additionalWhereClause = $my['filters']['additionalWhereClause'];
		$exclude_branches = $my['filters']['exclude_branches'];
		$exclude_children_of = $my['filters']['exclude_children_of'];	
		$order_cfg = $my['options']['order_cfg'];
		$key_type = $my['options']['key_type'];		
	   
	    switch($order_cfg['type'])
	    {
	        case 'spec_order':
	  	    $sql = " SELECT * from {$this->object_table} " .
	  	           " WHERE parent_id = {$node_id} {$additionalWhereClause}" .
			       " ORDER BY node_order,id";
			    break;
			    
			case 'exec_order':
			// REMEMBER THAT DISTINCT IS NOT NEEDED when you does UNION
			//
			// First query get Nodes that ARE NOT test case => test suites
			// Second query get the TEST CASES
			//
			// 20101009 - franciscom - added tcversion_id , neeed for test plan export
	        $sql="SELECT * FROM ( SELECT NH.node_order AS spec_order," . 
	             "                NH.node_order AS node_order, NH.id, NH.parent_id," . 
	             "                NH.name, NH.node_type_id, 0 AS tcversion_id" .
	             "                FROM {$this->object_table} NH, {$this->tables['node_types']} NT" .
	             "                WHERE parent_id = {$node_id}" .
	             "                AND NH.node_type_id=NT.id" .
	             "                AND NT.description <> 'testcase' {$additionalWhereClause}" .
	             "                UNION" .
	             "                SELECT NHA.node_order AS spec_order, " .
	             "                       T.node_order AS node_order, NHA.id, NHA.parent_id, " .
	             "                       NHA.name, NHA.node_type_id, T.tcversion_id" .
	             "                FROM {$this->object_table} NHA, {$this->object_table} NHB," .
	             "                     {$this->tables['testplan_tcversions']}  T,{$this->tables['node_types']} NT" .
	             "                WHERE NHA.id=NHB.parent_id " .
	             "                AND NHA.node_type_id=NT.id" .
	             "                AND NHB.id=T.tcversion_id " .
	             "                AND NT.description = 'testcase'" .
	             "                AND NHA.parent_id = {$node_id}" .
	             "                AND T.testplan_id = {$order_cfg['tplan_id']}) AC" .
	             "                ORDER BY node_order,spec_order,id";
			    break;
	    }
	    $result = $this->db->exec_query($sql);
	  
	    if( $this->db->num_rows($result) == 0 )
	    {
	  	   return; 	
	    }
	  
	    while ( $row = $this->db->fetch_array($result) )
	    {
	      if( !isset($exclude_branches[$row['id']]) )
	      {  
	          
				$node_table = $this->node_tables[$this->node_types[$row['node_type_id']]];
				$node_list[] = array('id' => $row['id'],
				                     'parent_id' => $row['parent_id'],
				                     'tcversion_id' => (isset($row['parent_id']) ? $row['parent_id'] : -1),
				                     'node_type_id' => $row['node_type_id'],
				                     'node_order' => $row['node_order'],
				                     'node_table' => $node_table,
				                     'name' => $row['name']);
	          
				// Basically we use this because:
				// 1. Sometimes we don't want the children if the parent is a testcase,
				//    due to the version management
				//
				// 2. Sometime we want to exclude all descendants (branch) of a node.
				//
				// [franciscom]: 
				// I think ( but I have no figures to backup my thoughts) doing this check and 
				// avoiding the function call is better that passing a condition that will result
				// in a null result set.
				//
				//
				if( !isset($exclude_children_of[$this->node_types[$row['node_type_id']]]) && 
				    !isset($exclude_branches[$row['id']]) )
				{
				  $this->_get_subtree($row['id'],$node_list,$filters,$options);
				}
	    	}
	  	}
	} // function end
	 
	 
	// 20101009 - franciscom - added tcversion_id in output set 
	// 20061008 - franciscom - added ID in order by clause
	// 
	function _get_subtree_rec($node_id,&$pnode,$filters = null, $options = null)
	{
		static $s_testCaseNodeTypeID;
		if (!$s_testCaseNodeTypeID)
		{
		  	$s_testCaseNodeTypeID = $this->node_descr_id['testcase'];
		}

        $my['filters'] = array('exclude_children_of' => null,'exclude_branches' => null,
        					   'additionalWhereClause' => '', 'family' => null);
                               
        $my['options'] = array('order_cfg' => array("type" =>'spec_order'),'key_type' => 'std',
        					   'remove_empty_nodes_of_type' => null);

		// Cast to array to handle $options = null
		$my['filters'] = array_merge($my['filters'], (array)$filters);
		$my['options'] = array_merge($my['options'], (array)$options);
		
		// init old variables, till we refactor
		$additionalWhereClause = $my['filters']['additionalWhereClause'];
		$exclude_branches = $my['filters']['exclude_branches'];
		$exclude_children_of = $my['filters']['exclude_children_of'];	
		$order_cfg = $my['options']['order_cfg'];
		$key_type = $my['options']['key_type'];		
			
		if( !is_null($my['filters']['family']) )
		{
			if( isset($my['filters']['family'][$node_id]) )
			{
				$children2get = implode( ',',array_keys($my['filters']['family'][$node_id]));
				// echo 'parent:' . $node_id . 'family' . $children2get . '<br>';
			}
		} 	
	    
	    switch($order_cfg['type'])
	    {
	        case 'spec_order':
	  	    	$sql = " SELECT * FROM {$this->object_table} " .
	  	           	   " WHERE parent_id = {$node_id} {$additionalWhereClause}" .
			           " ORDER BY node_order,id";
		    break;
			    
		    case 'exec_order':
			// 20101003 - franciscom -
			// Hmmm, no action regarding platforms. is OK ??
			//
			// REMEMBER THAT DISTINCT IS NOT NEEDED when you does UNION
			//
			// Important Notice:
			// Second part of UNION, allows to get from nodes hierarchy,
			// only test cases that has a version linked to test plan.
			//
			// 20101009 - franciscom
			$platform_filter = "";
			if( isset($order_cfg['platform_id']) && $order_cfg['platform_id'] > 0 )
			{
				$platform_filter = " AND T.platform_id = {$order_cfg['platform_id']} ";
			}
			$sql="SELECT * FROM ( SELECT NH.node_order AS spec_order," . 
			     "                NH.node_order AS node_order, NH.id, NH.parent_id," . 
			     "                NH.name, NH.node_type_id, 0 AS tcversion_id " .
			     "                FROM {$this->tables['nodes_hierarchy']}  NH" .
			     "                WHERE parent_id = {$node_id}" .
			     "                AND node_type_id <> {$s_testCaseNodeTypeID} {$additionalWhereClause}" .
			     "                UNION" .
			     "                SELECT NHA.node_order AS spec_order, " .
			     "                       T.node_order AS node_order, NHA.id, NHA.parent_id, " .
			     "                       NHA.name, NHA.node_type_id, T.tcversion_id " .
			     "                FROM {$this->tables['nodes_hierarchy']} NHA, " .
			     "                     {$this->tables['nodes_hierarchy']} NHB," .
			     "                     {$this->tables['testplan_tcversions']} T" .
			     "                WHERE NHA.id=NHB.parent_id " .
			     "                AND NHA.node_type_id = {$s_testCaseNodeTypeID}" .
			     "                AND NHB.id=T.tcversion_id " .
			     "                AND NHA.parent_id = {$node_id}" .
			     "				  {$platform_filter} " .	
			     "                AND T.testplan_id = {$order_cfg['tplan_id']}) AC" .
			     "                ORDER BY node_order,spec_order,id";
			break;
			    
	    }
	    
	  	$children_key = 'childNodes';
	  	$result = $this->db->exec_query($sql);
	    while($row = $this->db->fetch_array($result))
	    {
	  		$rowID = $row['id'];
	  		$nodeTypeID = $row['node_type_id'];
	  		$nodeType = $this->node_types[$nodeTypeID];
			
	  		if(!isset($exclude_branches[$rowID]))
	  		{  
				    switch($key_type)
				    {
	  		        	case 'std':
	  		    	        $node_table = $this->node_tables[$nodeType];
	  		    	        
	  		    	        $node =  array('id' => $rowID,
	                                       'parent_id' => $row['parent_id'],
	                                       'node_type_id' => $nodeTypeID,
	                                       'node_order' => $row['node_order'],
	                                       'node_table' => $node_table,
	                                       'name' => $row['name'],
	  		    	           			   $children_key => null);
	  		    	           			   
	  		    	        if( isset($row['tcversion_id']) && $row['tcversion_id'] > 0)
	  		    	        {
	  		    	        	$node['tcversion_id'] = $row['tcversion_id'];
	  		    	        }    			   
	  		    	    	break;
	  		    	    
				    	   case 'extjs':
	  		    	        $node =  array('text' => $row['name'],
	  		    	                       'id' => $rowID,
	                                       'parent_id' => $row['parent_id'],
	                                       'node_type_id' => $nodeTypeID,
	                                       'position' => $row['node_order'],
	  		    	           			   $children_key => null,
	                                       'leaf' => false);
	          
		                    switch($nodeType)
		                    {
		                        case 'testproject':
		                        case 'testsuite':
		                            $node[$children_key] = null;
		                        	break;  
		        
		                        case 'testcase':
		                            $node['leaf'] = true;
		                        	break;
		                    } 
		  	    		    break;
	  	      }	
	            
		        // Basically we use this because:
		        // 1. Sometimes we don't want the children if the parent is a testcase,
		        //    due to the version management
		        //
		        // 2. Sometime we want to exclude all descendants (branch) of a node.
		        //
		        // [franciscom]: 
		        // I think ( but I have no figures to backup my thoughts) doing this check and 
		        // avoiding the function call is better that passing a condition that will result
		        // in a null result set.
		        //
		        //
		        if(!isset($exclude_children_of[$nodeType]) && !isset($exclude_branches[$rowID]))
		  		{
		  				$this->_get_subtree_rec($rowID,$node,$my['filters'],$my['options']);
		        }

				// 20101003 - franciscom 
				// Have added this logic, because when export test plan will be developed
				// having a test spec tree where test suites that do not contribute to test plan
				// are pruned/removed is very important, to avoid additional processing
				//		        
		        //  !is_null($my['options']['remove_empty_nodes_of_type']) && 
		        $doRemove = is_null($node[$children_key]) && 
		        	        $node['node_type_id'] == $my['options']['remove_empty_nodes_of_type'];
		        
		        if(!$doRemove)
		        {
	  				$pnode[$children_key][] = $node;
	  			}	
	  		} // if(!isset($exclude_branches[$rowID]))
	  	} //while
	}

	/*
	  function: get_full_path_verbose
	
	  args:
	  
	  returns: 
	
		@internal Revisions
		20101120 - franciscom - when path can not be found instead of null, 
								anyway a map will be returned, with key=itemID value=NULL
			
	*/
	function get_full_path_verbose(&$items,$options=null)
	{
    	$debugMsg='Class:' .__CLASS__ . ' - Method:' . __FUNCTION__ . ' :: ';
	    $goto_root=null;
	    $path_to=null;
	    $all_nodes=array();
	    $path_format = 'simple';
	    $output_format = 'simple';
	    
	    if( !is_null($options) )
	    {
	        $path_format = isset($options['include_starting_point']) ? 'points' : $path_format;
	        $output_format = isset($options['output_format']) ? $options['output_format'] : $output_format;
	    }
	    
	    foreach((array)$items as $item_id)
	    {
	        $path_to[$item_id]['name']=$this->get_path($item_id,$goto_root,$path_format);
	        $all_nodes = array_merge($all_nodes,(array)$path_to[$item_id]['name']);
	    }
	    
	    // BUGID 2728 - added check to avoid crash
	    $status_ok = (!is_null($all_nodes) && count($all_nodes) > 0);
        if( $status_ok )
        { 
	        // get only different items, to get descriptions
	    	$unique_nodes=implode(',',array_unique($all_nodes));
	    	$sql="/* $debugMsg */ " . 
	    	     " SELECT id,name FROM {$this->tables['nodes_hierarchy']}  WHERE id IN ({$unique_nodes})"; 
	    	$decode=$this->db->fetchRowsIntoMap($sql,'id');
	    	foreach($path_to as $key => $elem)
	    	{
	    	     foreach($elem['name'] as $idx => $node_id)
	    	     {
	   	     		$path_to[$key]['name'][$idx]=$decode[$node_id]['name'];
	   	     		$path_to[$key]['node_id'][$idx]=$node_id;
	    	     }
	    	}
	    }  
	    else
	    {
	    	$path_to=null;
	    } 
        
        if( !is_null($path_to) )
        {
        	switch ($output_format)
        	{
        		case 'path_as_string':
				$flat_path=null;
				foreach($path_to as $tcase_id => $pieces)
				{
					//remove test project node
					unset($pieces['name'][0]);
					$flat_path[$tcase_id]=implode('/',$pieces['name']);
				}
				$path_to = $flat_path;
        		break;
        		
        		case 'id_name':
        		break;
        		
        		case 'simple':	
        		default:
        		$keySet = array_keys($path_to);
        		foreach($keySet as $key)
        		{
        			$path_to[$key] = $path_to[$key]['name'];
        		}
        		break;
        	}	
        }
	    return $path_to; 
	}


	/**
	 * check if there is a sibbling node of same type that has same name
	 *
	 * @param string name: name to check
	 * @param int node_type_id: node types to check.
	 * @param int id: optional. exclude this node id from result set
	 *                this is useful when you want to check for name
	 *                existence during an update operation.
	 *                Using id you get node parent, to get sibblings.
	 *                If null parent_id argument must be present
	 *
	 * @param int parent_id: optional. Mandatory if id is null
	 *                       Used to get children nodes to check for
	 *                       name existence.
	 *
	 *                          
	 * @return map ret: ret['status']=1 if name exists
	 *                                0 if name does not exist
	 *                  ret['msg']= localized message
	 *                                
	 */
	function nodeNameExists($name,$node_type_id,$id=null,$parent_id=null)
    {
    	$debugMsg='Class:' .__CLASS__ . ' - Method:' . __FUNCTION__ . ' :: ';
		$ret['status'] = 0;
		$ret['msg'] = '';
        if( is_null($id) && is_null($parent_id) )
        {
        	$msg = $debugMsg . 'Error on call $id and $parent_id can not be both null';
        	throw new Exception($msg);
        }        	
        
        $additionalFilters = '';
        $parentNodeID=$parent_id;
        if( !is_null($id) )
        {
        	// Try to get parent id if not provided on method call.
        	if( is_null($parentNodeID) )
        	{
        		$sql = "/* {$debugMsg} */ " . 
        		       " SELECT parent_id FROM {$this->object_table} NHA " .
    				   " WHERE NHA.id = {$id} ";
    	        $rs = $this->db->get_recordset($sql);
        		$parentNodeID=$rs[0]['parent_id'];	   
        	}
        	$additionalFilters = " AND NHA.id <> {$id} ";
        }		
		$sql = "/* {$debugMsg} */ " . 
		       " SELECT count(0) AS qty FROM {$this->object_table} NHA " .
    		   " WHERE NHA.node_type_id	= {$node_type_id} " .
    		   " AND NHA.name = '" . $this->db->prepare_string($name) . "'" .
    		   " AND NHA.parent_id = {$parentNodeID} {$additionalFilters} "; 
 
		$rs = $this->db->get_recordset($sql);
		if( $rs[0]['qty'] > 0)
		{
			$ret['status'] = 1;
			$ret['msg'] = sprintf(lang_get('name_already_exists'),$name);
		}
    	
		return $ret;
    }

	/**
	 * getTreeRoot()
	 *
	 */
	function getTreeRoot($node_id)
	{
		$path = $this->get_path($node_id);
		$path_len = count($path);
		$root_node_id = ($path_len > 0)? $path[0]['parent_id'] : $node_id;
		return $root_node_id;
	}


	/**
	 * delete_subtree_objects()
	 * 
	 * ATTENTION: subtree root node ($node_id?? or root_id?) IS NOT DELETED.
	 *
	 * BUGID 3147 - Delete test project with requirements defined crashed with memory exhausted
	 */
	function delete_subtree_objects($root_id,$node_id,$additionalWhereClause = '',$exclude_children_of = null,
	                                $exclude_branches = null)
	{
		static $debugMsg;
		if( is_null($debugMsg) )
		{
			$debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
		}
		
		$sql = "/* $debugMsg */ SELECT NH.* FROM {$this->object_table} NH " .
			   " WHERE NH.parent_id = {$node_id} {$additionalWhereClause} ";
		
		$rs = $this->db->get_recordset($sql);
		if( !is_null($rs) )
		{
			foreach($rs as $row)
			{  
				$rowID = $row['id'];
				$nodeTypeID = $row['node_type_id'];
				$nodeType = $this->node_types[$nodeTypeID];
				$nodeClassName = $this->class_name[$nodeTypeID];
				
				if(!isset($exclude_branches[$rowID]))
				{  
					// Basically we use this because:
					// 1. Sometimes we don't want the children if the parent is a testcase,
					//    due to the version management
					//
					// 2. Sometime we want to exclude all descendants (branch) of a node.
					//
					if(!isset($exclude_children_of[$nodeType]) && !isset($exclude_branches[$rowID]))
					{
						file_put_contents('c:\delete_subtree_objects', 'rowid:' . $rowID . "nodeType: $nodeType\n", FILE_APPEND);                            
						
						// 20100918 - franciscom
						// I'm paying not having commented this well
						// Why I've set root_id to null ?
						// doing this when traversing a tree, containers under level of subtree root
						// will not be deleted => and this seems to be wrong.
						// BUGID 3790
						$this->delete_subtree_objects($root_id,$rowID,$additionalWhereClause,$exclude_children_of,$exclude_branches);
					}
					else
					{
						// For us in this method context this node is a leaf => just delete
						if( !is_null($nodeClassName) )
						{ 
							// file_put_contents('c:\delete_subtree_objects', $nodeClassName  & ' ' & $rowID, FILE_APPEND);                            
							$item_mgr = new $nodeClassName($this->db);
							$item_mgr->delete($rowID);        
						}
					}
				} // if(!isset($exclude_branches[$rowID]))
			} //while
		}
		
		// Must delete myself if I'm empty, only if I'm not subtree root.
		// Done this way to avoid infinte recursion for some type of nodes
		// that use this method as it's delete method. (example testproject).
		
		// 20100918 - franciscom
		// Hmmm, need to recheck if this condition is ok
		// 
		if( !is_null($root_id) && ($node_id != $root_id) )
		{
			$children = (array)$this->db->get_recordset($sql);
			 
			// if( is_null($children) || count($children) == 0 )
			if( count($children) == 0 )
			{
				$sql2 = "/* $debugMsg */ SELECT NH.* FROM {$this->object_table} NH " .
					    " WHERE NH.id = {$node_id}";
				$node_info = $this->db->get_recordset($sql2);
				if( isset($this->class_name[$node_info[0]['node_type_id']]) )
				{
					$className = $this->class_name[$node_info[0]['node_type_id']];
					if( !is_null($className) )
					{ 
						$item_mgr = new $className($this->db);
						$item_mgr->delete($node_id);        
					}
				}   
				else
				{
					// need to signal error - TO BE DONE
				}
			}   	   
		}  // if( $node_id != $root_id )
	}
 

  /*
  
              [$mode]: dotted -> $level number of dot characters are appended to
                               the left of item name to create an indent effect.
                               Level indicates on what tree layer item is positioned.
                               Example:

                                null
                                \
                               id=1   <--- Tree Root = Level 0
                                 |
                                 + ------+
                               /   \      \
                            id=9   id=2   id=8  <----- Level 1
                                    \
                                     id=3       <----- Level 2
                                      \
                                       id=4     <----- Level 3


                               key: item id (= node id on tree).
                               value: every array element is an string, containing item name.

                               Result example:

                                2  .TS1
                                3 	..TS2
                                9 	.20071014-16:22:07 TS1
                               10 	..TS2


                     array  -> key: item id (= node id on tree).
                               value: every array element is a map with the following keys
                               'name', 'level'

                                2  	array(name => 'TS1',level =>	1)
                                3   array(name => 'TS2',level =>	2)
                                9	  array(name => '20071014-16:22:07 TS1',level =>1)
                               10   array(name =>	'TS2', level 	=> 2)

  */
  function createHierarchyMap($array2map,$mode='dotted')
  {
		$hmap=array();
		$the_level = 1;
		$level = array();
  		$pivot = $array2map[0];

		foreach($array2map as $elem)
		{
			$current = $elem;

			if ($pivot['id'] == $current['parent_id'])
			{
				$the_level++;
				$level[$current['parent_id']]=$the_level;
			}
			else if ($pivot['parent_id'] != $current['parent_id'])
			{
				$the_level = $level[$current['parent_id']];
			}

			switch($mode)
			{
  				case 'dotted':
					$hmap[$current['id']] = str_repeat('.',$the_level) . $current['name'];
					break;

  				case 'array':
					$hmap[$current['id']] = array('name' => $current['name'], 'level' =>$the_level);
					break;
			}

			// update pivot
			$level[$current['parent_id']]= $the_level;
			$pivot=$elem;
		}
		
	    return $hmap;
  }

	/**
	 * getAllItemsID
 	 *
 	 * @internal revisions
 	 * based on code from testproject->get_all_testcases_id
 	 *
 	 */
	function getAllItemsID($parentList,&$itemSet,$coupleTypes)
	{
		static $debugMsg;
		if (!$debugMsg)
		{
		}
		$sql = "/* $debugMsg */  " .
		       " SELECT id,node_type_id from {$this->tables['nodes_hierarchy']} " .
		       " WHERE parent_id IN ({$parentList})";
		$sql .= " AND node_type_id IN ({$coupleTypes['target']},{$coupleTypes['container']}) "; 
		
		$result = $this->db->exec_query($sql);
		if ($result)
		{
			$containerSet = array();
			while($row = $this->db->fetch_array($result))
			{
				if ($row['node_type_id'] == $coupleTypes['target'])
				{
					$itemSet[] = $row['id'];
				}
				else
				{
				  	$containerSet[] = $row['id'];
				}
			}
			if (sizeof($containerSet))
			{
				$containerSet  = implode(",",$containerSet);
				$this->getAllItemsID($containerSet,$itemSet,$coupleTypes);
			}
		}	
	}



 
}// end class

?>