<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: adminProductEdit.php,v $
 *
 * @version $Revision: 1.10 $
 * @modified $Date: 2006/01/05 07:30:33 $
 *
 * @author Martin Havlat
 *
 * This page allows users to edit/delete products.
 * 
 * @todo Verify dependency before delete testplan 
 *
 * 20051211 - fm - poor workaround for the delete loop - BUGID 180 Unable to delete Product
 * 20050908 - fm - BUGID 0000086
 * 20050831 - scs - moved POST to top, some small changes
**/
include('../../config.inc.php');
require_once('common.php');
require_once('product.inc.php');
require_once("../../third_party/fckeditor/fckeditor.php");

global $db;
testlinkInitPage($db,true);

$updateResult = null;
$error = null;
$action = 'no';
$show_prod_attributes = 'yes';

$tlog_msg=null;
$tlog_level='INFO';
$tlog_msg_prefix="Product [ID: Name]=";

$smarty = new TLSmarty();

$args = init_args($db,$_REQUEST,$_SESSION);

// ----------------------------------------------------------------------
// 20060101 - fm
$of = new fckeditor('notes') ;
$of->BasePath = $_SESSION['basehref'] . 'third_party/fckeditor/';
$of->ToolbarSet = 'TL_Medium';
$of->Value = $args->notes;
// ----------------------------------------------------------------------

if (isset($_SESSION['productID']))
{
  $tlog_msg = $tlog_msg_prefix . $_SESSION['productID'] . ': ' . $_SESSION['productName'];
}
else
{
  $tlog_msg = $tlog_msg_prefix . $args->id . ': ' . $args->name;
}


switch($args->do)
{
  case 'deleteProduct':
  $show_prod_attributes = 'no';
	$sql = "SELECT id FROM mgtproduct WHERE id=" . $args->id;
	$result = do_sql_query($sql);

	if( $GLOBALS['db']->num_rows($result) == 1 )
  {
  	if (deleteProduct($args->id,$error))
  	{
  		$updateResult = lang_get('info_product_was_deleted');
  		$tlog_msg .= " was deleted.";
  		
  	} 
  	else 
  	{
  		$updateResult = lang_get('info_product_not_deleted_check_log') . ' ' . $error;
  		$tlog_msg .=  " wasn't deleted.\t";
  		$tlog_level='ERROR';
  	}
   	$action = 'delete';
  }
  break;


  case 'editProduct':
	$name_ok = 1;
	if ($name_ok && !strlen($args->name))
	{
		$updateResult = lang_get('info_product_name_empty');
		$name_ok = 0;
	}
	
	// BUGID 0000086
	if ($name_ok && !check_string($args->name,$g_ereg_forbidden))
	{
		$updateResult = lang_get('string_contains_bad_chars');
		$name_ok = 0;
	}
	if ($name_ok && $args->id)
	{
		$updateResult = updateProduct($args->id, $args->name, $args->color, 
		                              $args->optReq, $args->notes);
	}
	$action = 'updated';
	$show_prod_attributes = 'yes';
  break;


  case 'inactivateProduct':
	if (activateProduct($args->id, 0))
	{
		$updateResult = lang_get('info_product_inactivated');
		$tlog_msg .= 'was inactivated.';
	}
	$action = 'inactivate';
	$show_prod_attributes = 'yes';
  break;

  case 'activateProduct':
	if (activateProduct($args->id, 1))
	{
		$updateResult = lang_get('info_product_activated');
		$tlog_msg .= 'was activated.';
	}
	$action = 'activate';
	$show_prod_attributes = 'yes';
  break;
  
}

// Common Processing
if (strcasecmp($args->do,'deleteProduct') != 0 )
{
	if (isset($_SESSION['productID']))
	{
		$productData = getProduct($db,$_SESSION['productID']);
		if ($productData)
		{
			$args->name = $productData['name'];
			$smarty->assign('found', 'yes');
			$smarty->assign('id', $productData['id']);
			$smarty->assign('color', $productData['color']);
			$smarty->assign('active', $productData['active']);
			$smarty->assign('reqs_default', $productData['option_reqs']);
		}
		else
		{
			$updateResult = lang_get('info_failed_loc_prod');
		}	
	}
	else
	{
		$updateResult = lang_get('info_no_more_prods');
	}
}

if( !is_null(tlog_msg) )
{
	tLog($tlog_msg, $tlog_level);
}


$smarty->assign('action', $action);
$smarty->assign('sqlResult', $updateResult);
$smarty->assign('name', $args->name);
$smarty->assign('productName', isset($_SESSION['productName']) ? $_SESSION['productName'] : '');

// 20051211 - fm - poor workaround
$smarty->assign('show_prod_attributes', $show_prod_attributes);
$smarty->assign('notes', $of->CreateHTML());

$smarty->display('adminProductEdit.tpl');
?>


<?php
/*
 * INITialize page ARGuments, using the $_REQUEST and $_SESSION
 * super-global hashes.
 * Important: changes in HTML input elements on the Smarty template
 *            must be reflected here.
 *
 *  
 * @parameter hash request_hash the $_REQUEST
 * @parameter hash session_hash the $_SESSION
 * @return    object with html values tranformed and other
 *                   generated variables.
 *
 * 20060102 - fm 
*/
function init_args(&$db,$request_hash, $session_hash)
{

  $request_hash = strings_stripSlashes($request_hash);
  
  $do_keys=array('deleteProduct','editProduct','inactivateProduct','activateProduct');
  $args->do='';
  foreach ($do_keys as $value)
  {
    $args->do=isset($request_hash[$value]) ? $value : $args->do;
  }
  
  $nullable_keys=array('name','color','notes');
  foreach ($nullable_keys as $value)
  {
    $args->$value=isset($request_hash[$value]) ? $request_hash[$value] : null;
  }
  
  $intval_keys=array('optReq' => 0, 'id' => null);
  foreach ($intval_keys as $key => $value)
  {
    $args->$key=isset($request_hash[$key]) ? intval($request_hash[$key]) : $value;
  }

  // Special algorithm for notes
  $the_prodid = 0;
  if (isset($session_hash['productID']))
  {
  	$the_prodid = $session_hash['productID'];
  }
  else if(!is_null($args->id))
  {
    $the_prodid = $args->id;
  }
  
  $get_notes_from_db = (!is_null($the_prodid) && strcasecmp($args->do,"editProduct") != 0);
  if ($get_notes_from_db)
  {
    $productData = getProduct($db,$the_prodid);
  	$args->notes = 	$productData['notes'];
  }
  return($args);
}


?>
