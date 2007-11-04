<?php
/** TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * 
 * @filesource $RCSfile: product.core.inc.php,v $
 * @version $Revision: 1.13 $
 * @modified $Date: 2007/11/04 11:14:41 $  $Author: franciscom $
 * @author Martin Havlat
 *
 * Core Functions for Product management (get data)
 * To edit product see ./product.inc.php
 * 
 * rev :
 *       20070819 - franciscom
 *       getAccessibleProducts(), added doc header and new arg
 *
 *       20070120 - franciscom 
 *       removed "dead code"
 *       added TL_INACTIVE_MARKUP
 */
 
/** 
get option list of products; all for admin and active for others 

args:
      db: database handler
      user_id
      role_id
      [output_type]: choose the output data structure.
                     possible values: map, map_of_map
                     map: key -> test project id
                          value -> test project name
                            
                     map_of_map: key -> test project id
                                 value -> array ('name' => test project name,
                                                 'active' => active status)
                                                 
                     array_of_map: value -> array ('id' => test project id
                                                   'name' => test project name,
                                                   'active' => active status)
                                                 
                     
                     default: map
     [order_by]: default: ORDER BY name
                     
rev :
     20071104 - franciscom - added user_id,role_id to remove global coupling
                             added order_by (BUGID 498)
     20070725 - franciscom - added output_type
     20060312 - franciscom - add nodes_hierarchy on join
     
*/
/*
function getAccessibleProducts(&$db,$user_id,$role_id,$output_type='map',$order_by=" ORDER BY name ")
{
	$items = array();
	$sql =  " SELECT nodes_hierarchy.id,nodes_hierarchy.name,active
 	          FROM nodes_hierarchy 
 	          JOIN testprojects ON nodes_hierarchy.id=testprojects.id  
	          LEFT OUTER JOIN user_testproject_roles 
		        ON testprojects.id = user_testproject_roles.testproject_id AND  
		 	      user_testproject_roles.user_ID = {$user_id} WHERE ";
		 	      
	if ($role_id != TL_ROLES_NONE)
		$sql .=  "(role_id IS NULL OR role_id != ".TL_ROLES_NONE.")";
	else
		$sql .=  "(role_id IS NOT NULL AND role_id != ".TL_ROLES_NONE.")";
	
	
	if (has_rights($db,'mgt_modify_product') != 'yes')
		$sql .= " AND active=1 ";

	$sql .= $order_by;
	
	$arrTemp = $db->fetchRowsIntoMap($sql,'id');
	
	if (sizeof($arrTemp))
	{
    switch ($output_type)
	  {
	     case 'map':
		   foreach($arrTemp as $id => $row)
		   {
			   $noteActive = '';
			   if (!$row['active'])
				   $noteActive = TL_INACTIVE_MARKUP;
			   $items[$id] = $noteActive . $row['name'];
		   }
		   break;
		   
	     case 'map_of_map':
		   foreach($arrTemp as $id => $row)
		   {
			   $items[$id] = array( 'name' => $row['name'],
			                        'active' => $row['active']);
		   }
		   
		   case 'array_of_map':
		   foreach($arrTemp as $id => $row)
		   {
			   $items[] = array( 'id' => $id,
			                     'name' => $row['name'],
			                     'active' => $row['active']);
		   }
		   break;
	  }
	}

	return $items;
}
*/
?>