<?php
/* TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * $Id: planNew.php,v 1.13 2006/01/05 07:30:34 franciscom Exp $ 
 *
 * Purpose:  Add new Test Plan 
 *
 * @author: francisco mancardi - 20051120 - adding test plan filter by product behaivour
 * @author: francisco mancardi - 20050915 - refactoring function name
 * @author: francisco mancardi - 20050810 - deprecated $_SESSION['product'] removed
 * 20051125 - scs - added checking for duplicate tp names
*/
require('../../config.inc.php');
require("../functions/common.php");
require("plan.inc.php");
require_once("../../lib/functions/lang_api.php");
require_once("../../third_party/fckeditor/fckeditor.php");
testlinkInitPage($db);

// ----------------------------------------------------------------------
// 20060101 - fm
$of = new fckeditor('notes') ;
$of->BasePath = $_SESSION['basehref'] . 'third_party/fckeditor/';
$of->ToolbarSet = 'TL_Medium';
$of->Value = null;
// ----------------------------------------------------------------------

$sqlResult = null;
$args = init_args($_REQUEST,$_SESSION);
if(isset($_REQUEST['newTestPlan'])) 
{
  $of->Value = $args->notes;
  if (!strlen($args->name))
	{
		$sqlResult = lang_get('warning_empty_tp_name');
	}	
	else
	{
		$tp_id = 0;
		$sqlResult = 'ok';
		
		//20051125 - scs - added checking for duplicate tp names
		$plans = getAllTestPlans($db,$args->productID,null,1);
		$bDuplicate = false;
		$num_plans = sizeof($plans);
		for($idx = 0; $idx < $num_plans; $idx++)
		{
			if ($plans[$idx]['name'] == $args->name)
			{
				$bDuplicate = true;
				break;
			}
		}
		if (!$bDuplicate)
		{
			$tp_id=insertTestPlan($db,$args->name,$args->notes,$args->productID);
			if ($tp_id == 0)
			{
				$sqlResult = $db->error_msg();
			}	
			$result = insertTestPlanPriorities($db, $tp_id);
			
			if($args->rights == 'on')
			{
				$result = insertTestPlanUserRight($db, $tp_id,$args->userID);
		  }
	    
			//user has decided to copy an existing Test Plan. 
			//What this code does is loops through each of the components, inserts the component info, 
			//loops through the categories from the component and then adds the category, 
			//and the same thing as before with test cases.
			if($args->copy) 
			{
				copy_deep_testplan($db, $args->source_tpid, $tp_id);
			}//end the copy if statement
      $of->Value = null;
		}
		else
		{
			$sqlResult = lang_get('duplicate_tp_name');
		}	
	}
}

$smarty = new TLSmarty;

// 20051120 - fm
$smarty->assign('prod_name', $args->productName);
$smarty->assign('arrPlan', getAllActiveTestPlans($db,$args->productID,FILTER_BY_PRODUCT));
$smarty->assign('sqlResult', $sqlResult);
$smarty->assign('notes', $of->CreateHTML());

$smarty->display('planNew.tpl');
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
 * 20060103 - fm 
*/
function init_args($request_hash, $session_hash)
{

  $request_hash = strings_stripSlashes($request_hash);

  $nullable_keys=array('name','notes','rights');
  foreach ($nullable_keys as $value)
  {
    $args->$value=isset($request_hash[$value]) ? $request_hash[$value] : null;
  }
  
  $intval_keys=array('copy' => 0);
  foreach ($intval_keys as $key => $value)
  {
    $args->$key=isset($request_hash[$key]) ? intval($request_hash[$key]) : $value;
  }
  $args->source_tpid = $args->copy;
  $args->copy = ($args->copy > 0) ? TRUE : FALSE;

  $args->productID   = $_SESSION['productID'];
  $args->productName = $_SESSION['productName'];
  $args->userID      = $_SESSION['userID'];

  return($args);
}


?>
