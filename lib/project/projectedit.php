<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: projectedit.php,v $
 *
 * @version $Revision: 1.2 $
 * @modified $Date: 2006/03/12 18:47:15 $
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
 * 20060107 - scs - added new product functionality
**/
include('../../config.inc.php');
require_once('common.php');
require_once('product.inc.php');
require_once('testproject.class.php');
require_once("../../third_party/fckeditor/fckeditor.php");
testlinkInitPage($db,true);

$session_tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;

$updateResult = null;
$action = 'no';
$show_prod_attributes = 'yes';

$tlog_msg = "Product [ID: Name]=";
$tlog_level = 'INFO';

$tproject = new testproject($db);
$args = init_args($tproject, $_REQUEST, $session_tproject_id);

$of = new fckeditor('notes') ;
$of->BasePath = $_SESSION['basehref'] . 'third_party/fckeditor/';
$of->ToolbarSet = 'TL_Medium';

if ($session_tproject_id)
	$tlog_msg .= $session_tproject_id . ': ' . $_SESSION['testprojectName'];
else
	$tlog_msg .= $args->id . ': ' . $args->name;

switch($args->do)
{
	case 'do_delete':
		$show_prod_attributes = 'no';
		$error = null;
		if (deleteProduct($db,$args->id,$error))
		{
			$updateResult = lang_get('info_product_was_deleted');
			$tlog_msg .= " was deleted.";
		} 
		else 
		{
			$updateResult = lang_get('info_product_not_deleted_check_log') . ' ' . $error;
			$tlog_msg .=  " wasn't deleted.\t";
			$tlog_level = 'ERROR';
		}
		$action = 'delete';
		break;
		
	case 'show_create_screen':
		$args->id = -1;
		break;	 
		
	case 'do_create':
		$updateResult = 'ok';
		if ($tproject->checkTestProjectName($args->name,$updateResult))
		{
			$args->id = $tproject->create($args->name, $args->color, $args->optReq, $args->notes);
			if (!$args->id)
				$updateResult = lang_get('refer_to_log');
			else
				$args->id = -1;
			$action = 'updated';	
		}
		break;
		
	case 'do_edit':
		$updateResult = 'ok';
		if ($tproject->checkTestProjectName($args->name,$updateResult))
		{
			$updateResult = $tproject->update($args->id, $args->name, $args->color,$args->optReq, $args->notes);
			$action = 'updated';
		}
		break;
	
	case 'inactivateProduct':
		if ($tproject->activateTestProject($args->id,0))
		{
			$updateResult = lang_get('info_product_inactivated');
			$tlog_msg .= 'was inactivated.';
		}
		$action = 'inactivate';
		break;

	case 'activateProduct':
		if ($tproject->activateTestProject($args->id,1))
		{
			$updateResult = lang_get('info_product_activated');
			$tlog_msg .= 'was activated.';
		}
		$action = 'activate';
		break;
}

$smarty = new TLSmarty();
if ($args->do != 'deleteProduct')
{
	if ($args->id != -1)
	{
		if ($session_tproject_id)
		{
			$the_data = $tproject->get_by_id($session_tproject_id);
			if ($the_data)
			{
				$args->name = $the_data['name'];
				$smarty->assign('found', 'yes');
				$smarty->assign('id', $the_data['id']);
				$smarty->assign('color', $the_data['color']);
				$smarty->assign('active', $the_data['active']);
				$smarty->assign('reqs_default', $the_data['option_reqs']);
			}
			else
				$updateResult = lang_get('info_failed_loc_prod');
		}
		else
			$updateResult = lang_get('info_no_more_prods');
	}
	else
	{
		$smarty->assign('found', 'yes');
		$smarty->assign('id', -1);
		$args->name = '';
		$args->notes = '';
		$args->color = '';
	}
}

if($action != 'no')
	tLog($tlog_msg, $tlog_level);

$of->Value = $args->notes;
$smarty->assign('action', $action);
$smarty->assign('sqlResult', $updateResult);
$smarty->assign('name', $args->name);
$smarty->assign('show_prod_attributes', $show_prod_attributes);
$smarty->assign('notes', $of->CreateHTML());
$smarty->display('projectedit.tpl');

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
 * 20060219 - franciscom
 * 20060102 - fm 
*/
function init_args($tproject,$request_hash, $session_tproject_id)
{
	$request_hash = strings_stripSlashes($request_hash);
	
	$do_keys = array('show_create_screen','do_delete','do_edit',
	                 'inactivateProduct','activateProduct','do_create');
	$args->do = '';
	foreach ($do_keys as $value)
	{
		$args->do = isset($request_hash[$value]) ? $value : $args->do;
	}
	
	$nullable_keys = array('name','color','notes');
	foreach ($nullable_keys as $value)
	{
		$args->$value = isset($request_hash[$value]) ? $request_hash[$value] : null;
	}
	
	$intval_keys = array('optReq' => 0, 'id' => null);
	foreach ($intval_keys as $key => $value)
	{
		$args->$key = isset($request_hash[$key]) ? intval($request_hash[$key]) : $value;
	}
	
	// Special algorithm for notes
	$the_tproject_id = 0;
	if ($args->do == 'show_create_screen' || $args->do == 'do_create')
		$args->id = -1;
	else if ($session_tproject_id)
	{
		$the_tproject_id = $session_tproject_id;
		$args->id = $the_tproject_id;
	}	
	else if(!is_null($args->id))
		$the_tproject_id = $args->id;

	if( $args->do != 'do_edit')
	{
		if ($the_tproject_id > 0)
		{
			$the_data = $tproject->get_by_id($the_tproject_id);
			$args->notes = 	$the_data['notes'];
		}
		else
			$args->notes = '';
	}
	
	return $args;
}
?>
