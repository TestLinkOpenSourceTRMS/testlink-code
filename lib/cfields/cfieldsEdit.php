<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: cfieldsEdit.php,v $
 *
 * @version $Revision: 1.2 $
 * @modified $Date: 2007/12/18 19:30:25 $ by $Author: franciscom $
 *
 *
 * rev :
 *      to avoid potential problems with HTML dom:  action -> do_action
 *           
**/
require_once(dirname(__FILE__) . "/../../config.inc.php");
require_once("common.php");
testlinkInitPage($db);

$template_dir='cfields/';
$default_template = str_replace('.php','.tpl',basename($_SERVER['SCRIPT_NAME']));


$do_action = isset($_REQUEST['do_action']) ? $_REQUEST['do_action']:null;
$cfield_id = isset($_REQUEST['cfield_id']) ? $_REQUEST['cfield_id']:0;
$cf_is_used = 0;
$result_msg = null;
$cf = '';  
$do_control_combo_display=1;

$disabled_cf_enable_on=array('execution' => '', 'design' => '');
$disabled_cf_show_on=array('execution' => '', 'design' => '');


$cfield_mgr = new cfield_mgr($db);
$keys2loop=array('execution','design');
foreach( $keys2loop as $ui_mode)
{
  $enable_on_cfg[$ui_mode]=$cfield_mgr->get_enable_on_cfg($ui_mode);
  $show_on_cfg[$ui_mode]=$cfield_mgr->get_show_on_cfg($ui_mode);
}

$possible_values_cfg=$cfield_mgr->get_possible_values_cfg();

$allowed_nodes = $cfield_mgr->get_allowed_nodes();
$cf_allowed_nodes = array();
foreach($allowed_nodes as $verbose_type => $type_id)
{
	$cf_allowed_nodes[$type_id] = lang_get($verbose_type);
}

switch ($do_action)
{
	case 'create':
		$cf = array('id' => $cfield_id,
		            'name' => ' ', 'label' => ' ', 'type' => 0,
		            'possible_values' => '',
		            'show_on_design' => 1,
		            'enable_on_design' => 1,
		            'show_on_execution' => 1,
		            'enable_on_execution' => 1,
		            'node_type_id' => $allowed_nodes['testcase']
		);    
		break;
	
	case 'edit':
		$cf=$cfield_mgr->get_by_id($cfield_id);
		$cf=$cf[$cfield_id];
		$cf_is_used=$cfield_mgr->is_used($cfield_id);
		break;
	
	case 'do_add':  
		$cf = request2cf($_REQUEST);
		$cf['name'] = trim($cf['name']);
		$cf['label'] = trim($cf['label']);
		$cf['possible_values'] = trim($cf['possible_values']);
		
		// Check if name exists
		$dupcf = $cfield_mgr->get_by_name($cf['name']);
		if(is_null($dupcf)) 
		{
			$result_msg = "ok";
			$ret = $cfield_mgr->create($cf); 
			if(!$ret['status_ok'])
				$result_msg=lang_get("error_creating_cf"); 
		}
		else
			$result_msg = lang_get("cf_name_exists"); 
		break;
	
	case 'do_update':  
		$cf = request2cf($_REQUEST);
		
		$cf['id'] = $cfield_id;
		$cf['name'] = trim($cf['name']);
		$cf['label'] = trim($cf['label']);
		$cf['possible_values'] = trim($cf['possible_values']);
		
		// Check if name exists
		$is_unique=$cfield_mgr->name_is_unique($cf['id'],$cf['name']);
		if($is_unique) 
		{
			$result_msg = "ok";
			$ret = $cfield_mgr->update($cf); 
		}
		else
			$result_msg = lang_get("cf_name_exists"); 
		break;
	
	case 'do_delete':
		$cf = '';  
		$result_msg = "ok";
		$cfield_mgr->delete($cfield_id); 
	  $do_control_combo_display=0;
		break; 
}

$smarty = new TLSmarty();

// --------------------------------------------------------------
// To control combo display
if( $do_control_combo_display )
{
  foreach( $keys2loop as $ui_mode)
  {
    if(!$enable_on_cfg[$ui_mode][$cf['node_type_id']])
    {
     $disabled_cf_enable_on[$ui_mode]=' disabled="disabled" ';
    }   
     
    if(!$show_on_cfg[$ui_mode][$cf['node_type_id']])
    {
     $disabled_cf_show_on[$ui_mode]=' disabled="disabled" ';
    }   
  }
}

$show_possible_values=0;
if( isset($cf['type']) )
{
  $show_possible_values=$possible_values_cfg[$cf['type']];
}
// --------------------------------------------------------------

$smarty->assign('result',$result_msg);
$smarty->assign('user_action',$do_action);
$smarty->assign('cf_types',$cfield_mgr->get_available_types());
$smarty->assign('cf_allowed_nodes',$cf_allowed_nodes);
$smarty->assign('is_used',$cf_is_used);
$smarty->assign('cf',$cf);

$smarty->assign('disabled_cf_enable_on', $disabled_cf_enable_on);
$smarty->assign('disabled_cf_show_on', $disabled_cf_show_on);
$smarty->assign('show_possible_values', $show_possible_values);


$smarty->assign('enable_on_cfg', $enable_on_cfg);
$smarty->assign('show_on_cfg', $show_on_cfg);

$smarty->assign('possible_values_cfg', $possible_values_cfg);

$smarty->display($template_dir . $default_template);
?>


<?php
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
  $missing_keys=array('show_on_design' => 1,
                      'enable_on_design' => 1,
                      'show_on_execution' => 0,
                      'enable_on_execution' => 0,
                      'possible_values' => ' ' );

	$cf_prefix = 'cf_';
	$len_cfp = strlen($cf_prefix);
	$start_pos = $len_cfp;
	$cf = array();
	foreach($hash as $key => $value)
	{
		if(strncmp($key,$cf_prefix,$len_cfp) == 0)
		{
			$dummy = substr($key,$start_pos);
			$cf[$dummy]=$value;
		}
	} 
	
	foreach($missing_keys as $key => $value)
	{
	  if( !isset($cf[$key]) )
	  {
	    $cf[$key]=$value;
	  }
	}

	return $cf;
}
?>
