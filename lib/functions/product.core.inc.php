<?php
/** TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * 
 * @filesource $RCSfile: product.core.inc.php,v $
 * @version $Revision: 1.12 $
 * @modified $Date: 2007/08/20 06:41:29 $  $Author: franciscom $
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
 
/** get option list of products; all for admin and active for others 

args:
      db: database handler
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
rev :
     20070725 - franciscom - added output_type
     20060312 - franciscom - add nodes_hierarchy on join
     
*/
function getAccessibleProducts(&$db,$output_type='map')
{
	$items = array();
	
	$userID = $_SESSION['userID'];
	$sql =  " SELECT nodes_hierarchy.id,nodes_hierarchy.name,active
 	          FROM nodes_hierarchy 
 	          JOIN testprojects ON nodes_hierarchy.id=testprojects.id  
	          LEFT OUTER JOIN user_testproject_roles 
		        ON testprojects.id = user_testproject_roles.testproject_id AND  
		 	      user_testproject_roles.user_ID = {$userID} WHERE ";
		 	      
	if ($_SESSION['roleId'] != TL_ROLES_NONE)
		$sql .=  "(role_id IS NULL OR role_id != ".TL_ROLES_NONE.")";
	else
		$sql .=  "(role_id IS NOT NULL AND role_id != ".TL_ROLES_NONE.")";
	
	
	if (has_rights($db,'mgt_modify_product') != 'yes')
		$sql .= " AND active=1 ";

	$sql .= " ORDER BY name";
	
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
?>