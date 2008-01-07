<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: projectEdit.php,v $
 *
 * @version $Revision: 1.2 $
 * @modified $Date: 2008/01/07 07:58:41 $ $Author: franciscom $
 *
 * @author Martin Havlat
 *
 * Allows users to edit/delete test projetcs.
 * 
 * @todo Verify dependency before delete testplan 
 *
 * 20070725 - franciscom - refactoring to control display of edit/delete tab
 *                         when there are 0 test projects on system.
 * 
 * 20070620 - franciscom - BUGID 914 
 * 20070221 - franciscom - BUGID 652
 * 20070206 - franciscom - BUGID 617
 * 20051211 - fm - poor workaround for the delete loop - BUGID 180 Unable to delete Product
 * 20050908 - fm - BUGID 0000086
 *
**/
include('../../config.inc.php');
require_once('common.php');
require_once('testproject.class.php');
require_once("web_editor.php");
testlinkInitPage($db,true);

$template_dir='project/';
$default_template = str_replace('.php','.tpl',basename($_SERVER['SCRIPT_NAME']));

// current testproject displayed on testproject combo.
$session_tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;

// Important: 
// if != 'no' refresh of navbar frame is done
//
$action = 'no';
$template=null;
$ui=array('doActionValue' => '', 'buttonValue' => '', 'caption' => '');
$user_feedback ='';
$reloadType='none';

$tlog_msg = "Product [ID: Name]=";
$tlog_level = 'INFO';

$tproject_mgr = new testproject($db);
$args = init_args($tproject_mgr, $_REQUEST, $session_tproject_id);

$of=web_editor('notes',$_SESSION['basehref']) ;
$of->Value = null;

if ($session_tproject_id)
	$tlog_msg .= $session_tproject_id . ': ' . $_SESSION['testprojectName'];
else
	$tlog_msg .= $args->tprojectID . ': ' . $args->tprojectName;

switch($args->doAction)
{
	case 'create':
		$ui=array();
    $ui['doActionValue']='doCreate';
		$ui['buttonValue']=lang_get('btn_create');
		$ui['caption']=lang_get('caption_new_tproject');
    $found='yes';
    $template=$default_template;
		break;	 

	case 'edit':
	  $ui=edit($args,$tproject_mgr);
    $template=$default_template;
    $found='yes';
		break;
		
	case 'doCreate':
	  $template=$default_template;
		$action="do_create";
	  $op=doCreate($args,$tproject_mgr);
    if($op->status_ok)
    {
        $template=null;
    }
    else
    {
        $user_feedback=$op->msg; 
    } 
		break;
		
	case 'doUpdate':
	  $template=$default_template;
		$action="do_update";
	  $op=doUpdate($args,$tproject_mgr);
    if($op->status_ok)
    {
        $template=null;
		    if( $session_tproject_id == $args->tprojectID)
		    {
          $reloadType='reloadNavBar';
	      }
    }
    else
    {
        $user_feedback=$op->msg; 
    } 
  	break;
	
	
	case 'doDelete':
		$op=$tproject_mgr->delete($args->tprojectID);
		
		if ($op['status_ok'])
		{
		   if( $session_tproject_id == $args->tprojectID)
		   {
         $reloadType='reloadNavBar';
	     }

		  $user_feedback = sprintf(lang_get('test_project_deleted'),$args->tprojectName);
			$tlog_msg .= " was deleted.";
		} 
		else 
		{
			$user_feedback = lang_get('info_product_not_deleted_check_log') . ' ' . $op['msg'];
			$tlog_msg .=  " wasn't deleted.\t";
			$tlog_level = 'ERROR';
		}
		$action = 'delete';
		break;

}


// ----------------------------------------------------------------------
// render GUI
// ----------------------------------------------------------------------

if($action != 'no')
	tLog($tlog_msg, $tlog_level);

$smarty = new TLSmarty();
$smarty->assign('canManage', has_rights($db,"mgt_modify_product"));

switch($args->doAction)
{
    case "doCreate":
    case "doDelete":
    case "doUpdate":
        $tprojects=$tproject_mgr->get_all();
        $template= is_null($template) ? 'projectView.tpl' : $template;
        $smarty->assign('tprojects',$tprojects);
        $smarty->assign('doAction',$reloadType);
        $smarty->display($template_dir . $template);
    break; 
    
    default:
        $of->Value = $args->notes;
        
        $smarty->assign('doActionValue',$ui['doActionValue']);
        $smarty->assign('buttonValue',$ui['buttonValue']);
        $smarty->assign('caption',$ui['caption']);
        $smarty->assign('user_feedback', $user_feedback);
        $smarty->assign('id', $args->tprojectID);
        $smarty->assign('name', $args->tprojectName);
        $smarty->assign('active', $args->active);
        $smarty->assign('action', $action);
        $smarty->assign('notes', $of->CreateHTML());
        $smarty->assign('found', $found);
        $smarty->display($template_dir . $template);
    break; 

} 


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
 * 20070206 - franciscom - BUGID 617
*/
function init_args($tprojectMgr,$request_hash, $session_tproject_id)
{
	$request_hash = strings_stripSlashes($request_hash);
	$nullable_keys = array('tprojectName','color','notes','doAction');
	foreach ($nullable_keys as $value)
	{
		$args->$value = isset($request_hash[$value]) ? $request_hash[$value] : null;
	}
	
	$intval_keys = array('optReq' => 0, 'tprojectID' => 0);
	foreach ($intval_keys as $key => $value)
	{
		$args->$key = isset($request_hash[$key]) ? intval($request_hash[$key]) : $value;
	}
	
	$checkbox_keys = array('active' => 0);
	foreach ($checkbox_keys as $key => $value)
	{
		$args->$key = isset($request_hash[$key]) ? 1 : $value;
	}
	

  // Special algorithm for notes
  // 20070206 - BUGID 617
	if( $args->doAction != 'doUpdate' && $args->doAction != 'doCreate')
	{
		if ($args->tprojectID > 0)
		{
			$the_data = $tprojectMgr->get_by_id($args->tprojectID);
			$args->notes = 	$the_data['notes'];
		}
		else
		{
			$args->notes = '';
		}	
	}

	return $args;
}


/*
  function: 

  args:
  
  returns: 

*/
function doCreate($argsObj,&$tprojectMgr)
{
    $op->status_ok=0;
    $op->template='';
    $op->msg='';  
    
    $check_op=$tprojectMgr->checkName($argsObj->tprojectName);
    $op->msg=$check_op['msg'];
    
		if($check_op['status_ok'])
		{
			if (!$tprojectMgr->get_by_name($argsObj->tprojectName))
			{
				$new_id=$tprojectMgr->create($argsObj->tprojectName, $argsObj->color, 
				                             $argsObj->optReq, $argsObj->notes, $argsObj->active);
				if (!$new_id)
				{
					$op->msg = lang_get('refer_to_log');
				}
				else
				{
				  $op->status_ok=1;
				  $op->template='projectView.tpl';	
				}	
			}
			else
			{
				$op->msg = sprintf(lang_get('error_product_name_duplicate'),$argsObj->tprojectName);
			}
		}
    return $op;
}

/*
  function: 

  args:
  
  returns: 

*/
function doUpdate($argsObj,&$tprojectMgr)
{
    $op->status_ok=0;
    $op->msg='';  
    
    $check_op=$tprojectMgr->checkName($argsObj->tprojectName);
    $op->msg=$check_op['msg'];

		if ($check_op['status_ok'])
		{
			if (!$tprojectMgr->get_by_name($argsObj->tprojectName,"testprojects.id <> {$argsObj->tprojectID}"))
			{
				$op->msg = sprintf(lang_get('test_project_update_failed'),$argsObj->tprojectName);
				if( $tprojectMgr->update($argsObj->tprojectID,$argsObj->tprojectName,$argsObj->color,
				                         $argsObj->optReq,$argsObj->notes) )
				{
				  $op->msg = sprintf(lang_get('test_project_updated'),$argsObj->tprojectName);
				  $op->status_ok=1;
				  $tprojectMgr->activateTestProject($argsObj->tprojectID,$argsObj->active);
				}
				
			}
			else
				$op->msg = lang_get('error_product_name_duplicate');
		}
		return $op;
}

/*
  function: edit
            initialize variables to launch user interface (smarty template)
            to get information to accomplish edit task.

  args:
  
  returns: - 

*/
function edit(&$argsObj,&$tprojectMgr)
{
	  $tprojectInfo = $tprojectMgr->get_by_id($argsObj->tprojectID);
       
    $argsObj->tprojectName=$tprojectInfo['name'];
	  $argsObj->color=$tprojectInfo['color'];
	  $argsObj->notes=$tprojectInfo['notes'];
	  $argsObj->optReq=$tprojectInfo['option_reqs'];
	  $argsObj->active=$tprojectInfo['active'];

    $ui=array(); 

    $ui['doActionValue']='doUpdate';
		$ui['buttonValue']=lang_get('btn_save');
		$ui['caption']=lang_get('caption_edit_tproject');
		return $ui;
}


?>
