<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: cfields_edit.php,v $
 *
 * @version $Revision: 1.6 $
 * @modified $Date: 2007/06/04 17:26:46 $ by $Author: franciscom $
 *
 *
 * rev :
 *      to avoid potential problems with HTML dom:  action -> do_action
 *           
**/
require_once(dirname(__FILE__) . "/../../config.inc.php");
require_once("common.php");
testlinkInitPage($db);

$do_action = isset($_REQUEST['do_action']) ? $_REQUEST['do_action']:null;
$cfield_id = isset($_REQUEST['cfield_id']) ? $_REQUEST['cfield_id']:0;
$cf_is_used = 0;
$result_msg = null;
$cf = '';  
$disabled_cf_enable_on_execution="";

$cfield_mgr = new cfield_mgr($db);

$enable_on_exec_cfg=$cfield_mgr->get_enable_on_exec_cfg();
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
		break; 
}

$smarty = new TLSmarty();

if(!$enable_on_exec_cfg[$cf['node_type_id']])
{
  $disabled_cf_enable_on_execution=' disabled="disabled" ';
}
$show_possible_values=$possible_values_cfg[$cf['type']];


$smarty->assign('result',$result_msg);
$smarty->assign('user_action',$do_action);
$smarty->assign('cf_types',$cfield_mgr->get_available_types());
$smarty->assign('cf_allowed_nodes',$cf_allowed_nodes);
$smarty->assign('is_used',$cf_is_used);
$smarty->assign('cf',$cf);
$smarty->assign('disabled_cf_enable_on_execution', $disabled_cf_enable_on_execution);
$smarty->assign('show_possible_values', $show_possible_values);


$smarty->assign('enable_on_exec_cfg', $enable_on_exec_cfg);
$smarty->assign('possible_values_cfg', $possible_values_cfg);

$smarty->display('cfields_edit.tpl');
?>


<?php
/*
  function: 

  args :
  
  returns: 

*/
function request2cf($hash)
{
  $missing_keys=array('enable_on_execution' => 0,
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
