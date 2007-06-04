<?php
/** TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * 
 * @filesource $RCSfile: product.core.inc.php,v $
 * @version $Revision: 1.11 $
 * @modified $Date: 2007/06/04 17:30:09 $  $Author: franciscom $
 * @author Martin Havlat
 *
 * Core Functions for Product management (get data)
 * To edit product see ./product.inc.php
 * 
 * 20070120 - franciscom 
 * removed "dead code"
 * added TL_INACTIVE_MARKUP
 */
/** get option list of products; all for admin and active for others 

rev :
     20060312 - franciscom - add nodes_hierarchy on join
     
*/
function getAccessibleProducts(&$db)
{
	$arrProducts = array();
	
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
		foreach($arrTemp as $id => $row)
		{
			$noteActive = '';
			if (!$row['active'])
				$noteActive = TL_INACTIVE_MARKUP;
			$arrProducts[$id] = $noteActive . $row['name'];
		}
	}
	
	return $arrProducts;
}
?>