<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: cfields_edit.php,v $
 *
 * @version $Revision: 1.2 $
 * @modified $Date: 2007/01/05 13:57:30 $ by $Author: franciscom $
**/
require_once("../../config.inc.php");
require_once("../functions/common.php");
testlinkInitPage($db);

$action=isset($_REQUEST['action']) ? $_REQUEST['action']:null;
$id=isset($_REQUEST['id']) ? $_REQUEST['id']:0;
$cf_is_used=0;
$result_msg = null;
$cfield_mgr=New cfield_mgr($db);

$allowed_nodes=$cfield_mgr->get_allowed_nodes();
$cf_allowed_nodes=array();
foreach($allowed_nodes as $verbose_type => $type_id)
{
  $cf_allowed_nodes[$type_id] = lang_get($verbose_type);
}


switch ($action)
{
  
  case 'create':
       $cf=array('id' => $id,
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
       $cf=$cfield_mgr->get_by_id($id);
       $cf=$cf[$id];
       $cf_is_used=$cfield_mgr->is_used($id);
  break;
  
  
  case 'do_add':  
       $cf=request2cf($_REQUEST);
       $cf['name']=trim($cf['name']);
       $cf['label']=trim($cf['label']);
       $cf['possible_values']=trim($cf['possible_values']);
       
       // Check if name exists
       $dupcf=$cfield_mgr->get_by_name($cf['name']);
       if( is_null($dupcf) ) 
       {
         $result_msg="ok";
         $ret=$cfield_mgr->create($cf); 
         if( !$ret['status_ok'] )
         {
          $result_msg=lang_get("error_creating_cf"); 
         }
       }
       else
       {
         $result_msg=lang_get("cf_name_exists"); 
       }
  break;

  case 'do_update':  
       $cf=request2cf($_REQUEST);

       echo "<pre>debug 20061230 " . __FUNCTION__ . " --- "; print_r($cf); echo "</pre>";

       $cf['id']=$id;
       $cf['name']=trim($cf['name']);
       $cf['label']=trim($cf['label']);
       $cf['possible_values']=trim($cf['possible_values']);
       
       // Check if name exists
       $is_unique=$cfield_mgr->name_is_unique($cf['id'],$cf['name']);
       if( $is_unique) 
       {
         $result_msg="ok";
         $ret=$cfield_mgr->update($cf); 
       }
       else
       {
         $result_msg=lang_get("cf_name_exists"); 
       }
  break;

  case 'do_delete':
       $cf='';  
       $result_msg="ok";
       $cfield_mgr->delete($id); 
  break; 
  
  
}



$smarty = new TLSmarty();

$smarty->assign('result',$result_msg);
$smarty->assign('action',$action);
$smarty->assign('cf_types',$cfield_mgr->get_available_types());
$smarty->assign('cf_allowed_nodes',$cf_allowed_nodes);
$smarty->assign('is_used',$cf_is_used);
$smarty->assign('cf',$cf);

$smarty->display('cfields_edit.tpl');
?>

<?php
function request2cf($hash)
{
  $cf_prefix='cf_';
  $len_cfp=strlen($cf_prefix);
  $start_pos=$len_cfp;
  $cf=array();
  foreach($hash as $key => $value)
  {
    if( strncmp($key,$cf_prefix,$len_cfp) == 0 )
    {
      //$dummy=explode($cf_prefix,$key);
      $dummy=substr($key,$start_pos);
      $cf[$dummy]=$value;
    }
  } 
  return($cf);
}
?>
