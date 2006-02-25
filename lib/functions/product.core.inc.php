<?php
/** TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * 
 * @filesource $RCSfile: product.core.inc.php,v $
 * @version $Revision: 1.7 $
 * @modified $Date: 2006/02/25 07:02:25 $
 * @author Martin Havlat
 *
 * Core Functions for Product management (get data)
 * To edit product see ./product.inc.php
 * 
 */

/*
function getProducts(&$db,$id = null)
{
	$sql = "SELECT * FROM mgtproduct";
	
	if (!is_null($id)) {
		$sql .= " WHERE id = " . $id;
	}
	
	return selectData($db,$sql);
}
*/


/** collect all information about Product */
/*
function getProduct(&$db,$id)
{
	$products = getProducts($db,$id);

	return $products ? $products[0] : null;
}
*/



function getAllProductsBut(&$db,$id,&$products)
{
	$sql = "SELECT id, name FROM testprojects WHERE id !=" . $id;
	$products = selectData($db,$sql);

	return (!empty($products)) ? 1 : 0;
}	

/** get option list of products; all for admin and active for others 

rev :
     20050810 - fm
     refactoring
     
*/
function getOptionProducts(&$db)
{
	$arrProducts = array();
	
	$userID = $_SESSION['userID'];
	$sql =  "SELECT id,name,active FROM testprojects LEFT OUTER JOIN user_testproject_roles " .
		    "ON testprojects.id = user_testproject_roles.testproject_id AND " . 
		 	"user_testproject_roles.user_ID = {$userID} WHERE ";
	if ($_SESSION['roleId'] != TL_ROLES_NONE)
		$sql .=  "(role_id IS NULL OR role_id != ".TL_ROLES_NONE.")";
	else
		$sql .=  "(role_id IS NOT NULL AND role_id != ".TL_ROLES_NONE.")";
	
	$order_by = " ORDER BY name";
	
	if (has_rights($db,'mgt_modify_product') == 'yes') {
		$sql .= $order_by;
		$arrTemp = selectData($db,$sql);
		if (sizeof($arrTemp))
		{
			foreach($arrTemp as $one_item)
			{
				$noteActive = '';
				if (!$one_item['active'])
					$noteActive = '* ';
				$arrProducts[$one_item['id']] = $noteActive . $one_item['name'];
			}
		}
		
	} else {
		$sql .= " AND active=1 " . $order_by;
		$arrProducts = selectOptionData($db,$sql);
	}
	
	return $arrProducts;
}
?>