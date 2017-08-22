<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource	cfieldsEdit.php
 *
 */
require_once(dirname(__FILE__) . "/../../config.inc.php");
require_once("common.php");
testlinkInitPage($db,false,false,"checkRights");

$cfield_mgr = new cfield_mgr($db);
$templateCfg = templateConfiguration();
$args = init_args();
$gui = initializeGui($cfield_mgr);


$result_msg = null;
$do_control_combo_display = 1;
$cfMix = getCFCfg($args,$cfield_mgr);
$cfieldCfg = $cfMix->cfieldCfg;
$gui->cfield = $cfMix->emptyCF;

switch ($args->do_action)
{
	case 'create':
  	$templateCfg->template=$templateCfg->default_template;
		$user_feedback ='';
  	$operation_descr = '';
	break;

	case 'edit':
	 	$op = edit($args,$cfield_mgr);
		$gui->cfield = $op->cf;
		$gui->cfield_is_used = $op->cf_is_used;
		$gui->cfield_is_linked = $op->cf_is_linked;
		$gui->linked_tprojects = $op->linked_tprojects;
   	$user_feedback = $op->user_feedback;
   	$operation_descr=$op->operation_descr;
	break;

	case 'do_add':
  case 'do_add_and_assign':
	 	$op = doCreate($_REQUEST,$cfield_mgr,$args);
		$gui->cfield = $op->cf;
   	$user_feedback = $op->user_feedback;
   	$templateCfg->template = $op->template;
   	$operation_descr = '';
	break;

	case 'do_update':
	 	$op = doUpdate($_REQUEST,$args,$cfield_mgr);
		$gui->cfield = $op->cf;
   	$user_feedback = $op->user_feedback;
   	$operation_descr=$op->operation_descr;
   	$templateCfg->template = $op->template;
	break;

	case 'do_delete':
		$op = doDelete($args,$cfield_mgr);
	  $user_feedback = $op->user_feedback;
   	$operation_descr=$op->operation_descr;
		$templateCfg->template = $op->template;
		$do_control_combo_display = 0;
	break;
}

if( $do_control_combo_display )
{
  $keys2loop = $cfield_mgr->get_application_areas();
	foreach( $keys2loop as $ui_mode)
	{
    $cfieldCfg->cf_enable_on[$ui_mode]['value']=0;
    $cfieldCfg->cf_show_on[$ui_mode]['disabled']='';
    $cfieldCfg->cf_show_on[$ui_mode]['style']='';

    if($cfieldCfg->enable_on_cfg[$ui_mode][$gui->cfield['node_type_id']])
		{
	    $cfieldCfg->cf_enable_on[$ui_mode]['value']=1;
    }
        
		if(!$cfieldCfg->show_on_cfg[$ui_mode][$gui->cfield['node_type_id']])
		{
			$cfieldCfg->cf_show_on[$ui_mode]['disabled']=' disabled="disabled" ';
			$cfieldCfg->cf_show_on[$ui_mode]['style']=' style="display:none;" ';
		}
	}
}

$gui->show_possible_values = 0;
if(isset($gui->cfield['type']))
{
	$gui->show_possible_values = $cfieldCfg->possible_values_cfg[$gui->cfield['type']];
}

// enable on 'execution' implies show on 'execution' then has nosense to display show_on combo
if($args->do_action == 'edit' && $gui->cfield['enable_on_execution'] )
{
  $cfieldCfg->cf_show_on['execution']['style']=' style="display:none;" ';
} 

$gui->cfieldCfg = $cfieldCfg;

$smarty = new TLSmarty();
$smarty->assign('operation_descr',$operation_descr);
$smarty->assign('user_feedback',$user_feedback);
$smarty->assign('user_action',$args->do_action);
renderGui($smarty,$args,$gui,$cfield_mgr,$templateCfg);

/**
 *
 */
function getCFCfg(&$args,&$cfield_mgr)
{
  $cfg = new stdClass();

  $cfg->cfieldCfg = cfieldCfgInit($cfield_mgr);
  
  $cfg->emptyCF = array('id' => $args->cfield_id,
                        'name' => '','label' => '',
                        'type' => 0,'possible_values' => '',
                        'show_on_design' => 1,'enable_on_design' => 1,
                        'show_on_execution' => 0,'enable_on_execution' => 0,
                        'show_on_testplan_design' => 0,
                        'enable_on_testplan_design' => 0);

  $cfg->emptyCF['node_type_id'] = $cfg->cfieldCfg->allowed_nodes['testcase'];

  return $cfg;
}


/**
 *
 */
function initializeGui(&$cfield_mgr)
{
  $gui = new stdClass();

  $gui->cfield=null;
  $gui->cfield_is_used=0;
  $gui->cfield_is_linked=0;
  $gui->linked_tprojects=null;
  $gui->cfield_types=$cfield_mgr->get_available_types();

  return $gui;
}



/*
  function: request2cf
            scan a hash looking for a keys with 'cf_' prefix,
            because this keys represents fields of Custom Fields
            tables.
            Is used to get values filled by user on a HTML form.
            This requirement dictated how html inputs must be named.
            If notation is not followed logic will fail.

  args: hash

  returns: hash only with related to custom fields, where
           (keys,values) are the original with 'cf_' prefix, but
           in this new hash prefix on key is removed.
           
  rev: 
      20090524 - franciscom - changes due to User Interface changes
      20080811 - franciscom - added new values on missing_keys         

*/
function request2cf($hash)
{
    // design and execution has sense for node types regarding testing
    // testplan,testsuite,testcase, but no sense for requirements.
    //
    // Missing keys are combos that will be disabled and not show at UI.
    // For req spec and req, no combo is showed.
    // To avoid problems (need to be checked), my choice is set to 1
    // *_on_design keys, that right now will not present only for
    // req spec and requirements.
    //
	$missing_keys = array('show_on_design' => 0,
                          'enable_on_design' => 0,
                          'show_on_execution' => 0,
                          'enable_on_execution' => 0,
                          'show_on_testplan_design' => 0,
                          'enable_on_testplan_design' => 0,
                          'possible_values' => ' ' );

	$cf_prefix = 'cf_';
	$len_cfp = tlStringLen($cf_prefix);
	$start_pos = $len_cfp;
	$cf = array();
	foreach($hash as $key => $value)
	{
		if(strncmp($key,$cf_prefix,$len_cfp) == 0)
		{
			$dummy = substr($key,$start_pos);
			$cf[$dummy] = $value;
		}
	}

	foreach($missing_keys as $key => $value)
	{
		if(!isset($cf[$key]))
		{
			$cf[$key] = $value;
		}	
	}

    // After logic refactoring
    // if ENABLE_ON_[area] == 1
    //    DISPLAY_ON_[area] = 1
    //
    // 
    // IMPORTANT/CRITIC: 
    // this KEY MUST BE ALIGNED WITH name on User Inteface
    // then if is changed on UI must be changed HERE
    $setter=array('design' => 0, 'execution' => 0, 'testplan_design' => 0);    
    switch($cf['enable_on'])
    {
        case 'design':
        case 'execution':
        case 'testplan_design':
        $setter[$cf['enable_on']]=1;
        break;

        default:
        $setter['design']=1;
        break;    
    }
    
    foreach($setter as $key => $value)
    {
        $cf['enable_on_' . $key] = $value;
        if( $cf['enable_on_' . $key] )
        {
            $cf['show_on_' . $key] = 1;    
        }          
    }
	return $cf;
}

/*
  function:

  args:

  returns:

*/
function init_args()
{
    $_REQUEST=strings_stripSlashes($_REQUEST);
    $args = new stdClass();
    $args->do_action = isset($_REQUEST['do_action']) ? $_REQUEST['do_action'] : null;
    $args->cfield_id = isset($_REQUEST['cfield_id']) ? intval($_REQUEST['cfield_id']) : 0;
    $args->cf_name = isset($_REQUEST['cf_name']) ? $_REQUEST['cf_name'] : null;

    $args->tproject_id = isset($_REQUEST['tproject_id']) ? intval($_REQUEST['tproject_id']) : 0;
    if( $args->tproject_id == 0 )
    {
      $args->tproject_id = isset($_SESSION['testprojectID']) ? intval($_SESSION['testprojectID']) : 0;
    }  
    return $args;
}

/*
  function: edit

  args:

  returns:

*/
function edit(&$argsObj,&$cfieldMgr)
{
  $op = new stdClass();
  $op->cf = null;
  $op->cf_is_used = 0;
  $op->cf_is_linked = 0;
    
  $op->user_feedback = '';
  $op->template = null;
  $op->operation_descr = '';
  $op->linked_tprojects = null;

	$cfinfo = $cfieldMgr->get_by_id($argsObj->cfield_id);

	if ($cfinfo)
	{
		$op->cf = $cfinfo[$argsObj->cfield_id];
		$op->cf_is_used = $cfieldMgr->is_used($argsObj->cfield_id);
		
		$op->operation_descr = lang_get('title_cfield_edit') . TITLE_SEP_TYPE3 . $op->cf['name'];
		$op->linked_tprojects = $cfieldMgr->get_linked_testprojects($argsObj->cfield_id); 
 		$op->cf_is_linked = !is_null($op->linked_tprojects) && count($op->linked_tprojects) > 0;
	}
  return $op;
}


/*
  function: doCreate

  args:

  returns:

*/
function doCreate(&$hash_request,&$cfieldMgr,&$argsObj)
{
  $op = new stdClass();
  $op->template = "cfieldsEdit.tpl";
  $op->user_feedback='';
	$op->cf = request2cf($hash_request);

	$keys2trim=array('name','label','possible_values');
	foreach($keys2trim as $key)
	{
	  $op->cf[$key]=trim($op->cf[$key]);
	}
  
  // Check if name exists
  $dupcf = $cfieldMgr->get_by_name($op->cf['name']);
  if(is_null($dupcf))
  {
  	$ret = $cfieldMgr->create($op->cf);
   	if(!$ret['status_ok'])
   	{
   		$op->user_feedback = lang_get("error_creating_cf");
   	}
   	else
   	{
    	$op->template = null;
   		logAuditEvent(TLS("audit_cfield_created",$op->cf['name']),"CREATE",$ret['id'],"custom_fields");
   	
      if($hash_request['do_action'] == 'do_add_and_assign')
      {
        $cfieldMgr->link_to_testproject($argsObj->tproject_id,array($ret['id']));
      }  
    }
  }
  else
	{
  	$op->user_feedback = lang_get("cf_name_exists");
  }
    
  return $op;
}



/*
  function: doUpdate

  args:

  returns:

*/
function doUpdate(&$hash_request,&$argsObj,&$cfieldMgr)
{
  $op = new stdClass();
  $op->template = "cfieldsEdit.tpl";
  $op->user_feedback='';
	$op->cf = request2cf($hash_request);
	$op->cf['id'] = $argsObj->cfield_id;

  $oldObjData=$cfieldMgr->get_by_id($argsObj->cfield_id);
  $oldname=$oldObjData[$argsObj->cfield_id]['name'];
  $op->operation_descr=lang_get('title_cfield_edit') . TITLE_SEP_TYPE3 . $oldname;

	$keys2trim=array('name','label','possible_values');
	foreach($keys2trim as $key)
	{
		$op->cf[$key]=trim($op->cf[$key]);
	}

	// Check if name exists
	$is_unique = $cfieldMgr->name_is_unique($op->cf['id'],$op->cf['name']);
	if($is_unique)
	{
		$ret = $cfieldMgr->update($op->cf);
		if ($ret)
		{
			$op->template = null;
			logAuditEvent(TLS("audit_cfield_saved",$op->cf['name']),"SAVE",$op->cf['id'],"custom_fields");
		}
	}
	else
  {  
		$op->user_feedback = lang_get("cf_name_exists");
	}
	return $op;
}



/*
  function: doDelete

  args:

  returns:

*/
function doDelete(&$argsObj,&$cfieldMgr)
{
    $op = new stdClass();
	  $op->user_feedback='';
	  $op->cf = null;
	  $op->template = null;
	  $op->operation_descr = '';
    
	  $cf = $cfieldMgr->get_by_id($argsObj->cfield_id);
	  if ($cf)
	  {
	  	$cf = $cf[$argsObj->cfield_id];
	  	if ($cfieldMgr->delete($argsObj->cfield_id))
	  	{
	  		logAuditEvent(TLS("audit_cfield_deleted",$cf['name']),"DELETE",$argsObj->cfield_id,"custom_fields");
	  	}	
	  }
	  return $op;
}


/*
  function: cfieldCfgInit

  args :

  returns: object with configuration options
*/
function cfieldCfgInit($cfieldMgr)
{
    $cfg = new stdClass();
    $cfAppAreas=$cfieldMgr->get_application_areas();
    foreach($cfAppAreas as $area)
    {
      $cfg->disabled_cf_enable_on[$area] = array();
      $cfg->cf_show_on[$area]['disabled'] = '';
      $cfg->cf_show_on[$area]['style'] = '';
        
      $cfg->cf_enable_on[$area] = array();
      $cfg->cf_enable_on[$area]['label'] = lang_get($area);
      $cfg->cf_enable_on[$area]['value'] = 0;
             
    	$cfg->enable_on_cfg[$area] = $cfieldMgr->get_enable_on_cfg($area);
    	$cfg->show_on_cfg[$area] = $cfieldMgr->get_show_on_cfg($area);
    }

    $cfg->possible_values_cfg = $cfieldMgr->get_possible_values_cfg();
    $cfg->allowed_nodes = $cfieldMgr->get_allowed_nodes();
    $cfg->cf_allowed_nodes = array();
    foreach($cfg->allowed_nodes as $verbose_type => $type_id)
    {
    	$cfg->cf_allowed_nodes[$type_id] = lang_get($verbose_type);
    }

    return $cfg;
}


/*
  function: renderGui
            set environment and render (if needed) smarty template

  args: 

  returns: - 
  

*/
function renderGui(&$smartyObj,&$argsObj,&$guiObj,&$cfieldMgr,$templateCfg)
{
  $doRender=false;
  switch($argsObj->do_action)
  {
  	case "do_add":
  	case "do_delete":
  	case "do_update":
    case "do_add_and_assign":
      $doRender=true;
  		$tpl = is_null($templateCfg->template) ? 'cfieldsView.tpl' : $templateCfg->template;
  	break;

  	case "edit":
  	case "create":
      $doRender=true;
  		$tpl = is_null($templateCfg->template) ? $templateCfg->default_template : $templateCfg->template;
  	break;
  }

  if($doRender)
  {
   $guiObj->cf_map = $cfieldMgr->get_all(null,'transform');
   $guiObj->cf_types=$cfieldMgr->get_available_types();
   $smartyObj->assign('gui',$guiObj);
   $smartyObj->display($templateCfg->template_dir . $tpl);
  }
}

function checkRights(&$db,&$user)
{
	return $user->hasRight($db,"cfield_management");
}