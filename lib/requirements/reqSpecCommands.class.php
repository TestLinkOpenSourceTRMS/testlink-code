<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @filesource $RCSfile: reqSpecCommands.class.php,v $
 * @version $Revision: 1.6 $
 * @modified $Date: 2009/03/16 08:47:29 $ by $Author: franciscom $
 * @author Francisco Mancardi
 * 
 * web command experiment
 */

class reqSpecCommands
{
  private $db;
  private $reqSpecMgr;
  private $reqMgr;
  private $reqStatus;
  private $defaultTemplate='reqSpecEdit.tpl';
  private $submit_button_label;
  private $auditContext;

	function __construct(&$db)
	{
	    $this->db=$db;
	    $this->reqSpecMgr = new requirement_spec_mgr($db);
	    $this->reqMgr = new requirement_mgr($db);
	    $this->reqStatus=init_labels(config_get('req_status'));
		  $this->submit_button_label=lang_get('btn_save');
	}

	function setAuditContext($auditContext)
	{
	    $this->auditContext=$auditContext;
	}


  /*
    function: create

    args:
    
    returns: 

  */
	function create(&$argsObj)
	{
      $guiObj=new stdClass();
		  $guiObj->main_descr = lang_get('testproject') . TITLE_SEP . $argsObj->tproject_name;
		  $guiObj->action_descr = lang_get('create_req_spec');

		  $guiObj->cfields = $this->reqSpecMgr->html_table_of_custom_field_inputs(null,$argsObj->tproject_id);
      $guiObj->template = $this->defaultTemplate;
		  $guiObj->submit_button_label=$this->submit_button_label;
 	    $guiObj->req_spec_id=null;
		  $guiObj->req_spec_title=null;
		  $guiObj->total_req_counter=null;

      return $guiObj;	
	}

  /*
    function: edit

    args:
    
    returns: 

  */
	function edit(&$argsObj)
	{
      $guiObj=new stdClass();

		  $guiObj->req_spec = $this->reqSpecMgr->get_by_id($argsObj->req_spec_id);
		  $guiObj->main_descr = lang_get('req_spec') . TITLE_SEP . $guiObj->req_spec['title'];
		  $guiObj->action_descr = lang_get('edit_req_spec');
      $guiObj->template = $this->defaultTemplate;
 		  $guiObj->submit_button_label=$this->submit_button_label;
      
		  $guiObj->req_spec_id=$argsObj->req_spec_id;
		  $guiObj->req_spec_title=$guiObj->req_spec['title'];
		  $guiObj->total_req_counter=$guiObj->req_spec['total_req'];
		  $guiObj->cfields = $this->reqSpecMgr->html_table_of_custom_field_inputs($argsObj->req_spec_id,$argsObj->tproject_id);

		  $argsObj->scope = $guiObj->req_spec['scope'];
      return $guiObj;	
	}

  /*
    function: doCreate

    args:
    
    returns: 

  */
	function doCreate(&$argsObj,$request)
	{
      $guiObj=new stdClass();

		  $guiObj->main_descr = lang_get('testproject') . TITLE_SEP . $argsObj->tproject_name;
		  $guiObj->action_descr = lang_get('create_req_spec');
 		  $guiObj->submit_button_label=$this->submit_button_label;
      $guiObj->template = $this->defaultTemplate;
      $guiObj->req_spec_id=null;
		  $guiObj->req_spec_title=null;
		  $guiObj->total_req_counter=null;

		  $guiObj->cfields = $this->reqSpecMgr->html_table_of_custom_field_inputs(null,$argsObj->tproject_id);
		  $ret = $this->reqSpecMgr->create($argsObj->tproject_id,$argsObj->reqParentID,
		                                   $argsObj->title,$argsObj->scope,
		                                   $argsObj->countReq,$argsObj->user_id);
      
		  $guiObj->user_feedback = $ret['msg'];
		  if($ret['status_ok'])
		  {
		  	$guiObj->user_feedback = sprintf(lang_get('req_spec_created'),$argsObj->title);
		  	$cf_map = $this->reqSpecMgr->get_linked_cfields(null,$argsObj->tproject_id) ;
		  	$this->reqSpecMgr->values_to_db($request,$ret['id'],$cf_map);
		  	logAuditEvent(TLS("audit_req_spec_created",$this->auditContext->tproject,$argsObj->title),
		  	              "CREATE",$ret['id'],"req_specs");
		  }
      else
      {
		      $guiObj->req_spec_title=$argsObj->req_spec['title'];
		      $guiObj->total_req_counter=$argsObj->req_spec['total_req'];
      }
		  
		  $argsObj->scope = "";
      return $guiObj;	
  }


  /*
    function: doUpdate

    args:
    
    returns: 

  */
	function doUpdate(&$argsObj,$request)
	{
	    $descr_prefix = lang_get('req_spec') . TITLE_SEP;

      $guiObj=new stdClass();
 		  $guiObj->submit_button_label=$this->submit_button_label;
	    $guiObj->template = null;
		  $guiObj->req_spec_id = $argsObj->req_spec_id;

		  $guiObj->req_spec = $this->reqSpecMgr->get_by_id($argsObj->req_spec_id);

		  $ret = $this->reqSpecMgr->update($argsObj->req_spec_id,$argsObj->title,
		                                   $argsObj->scope,$argsObj->countReq,$argsObj->user_id);
		  $guiObj->user_feedback = $ret['msg'];


		  if($ret['status_ok'])
		  {
        $guiObj->main_descr = '';
	      $guiObj->action_descr='';
        $guiObj->template = "reqSpecView.php?req_spec_id={$guiObj->req_spec_id}";
		  	$cf_map = $this->reqSpecMgr->get_linked_cfields($argsObj->req_spec_id);
		  	$this->reqSpecMgr->values_to_db($request,$argsObj->req_spec_id,$cf_map);

		  	if( $argsObj->title == $guiObj->req_spec['title'] )
		  	{
		  	    $audit_msg= TLS("audit_req_spec_saved",$this->auditContext->tproject,$argsObj->title);
		  	}    
		  	else
		  	{
		  	    $audit_msg= TLS("audit_req_spec_renamed",$this->auditContext->tproject,
		  	                                             $guiObj->req_spec['title'],$argsObj->title);
		  	}
  	    logAuditEvent($audit_msg,"SAVE",$argsObj->req_spec_id,"req_specs");
		  }
      else
      {
  	     // Action has failed => no change done on DB.
         $guiObj->main_descr = $descr_prefix . $guiObj->req_spec['title'];
   		   $guiObj->action_descr = lang_get('edit_req_spec');
         $guiObj->cfields = $this->reqSpecMgr->html_table_of_custom_field_values($argsObj->req_spec_id,$argsObj->tproject_id);
      }


      return $guiObj;	
  }


  /*
    function: doDelete

    args:
    
    returns: 

  */
	function doDelete(&$argsObj)
	{
		$guiObj = new stdClass();

		$req_spec = $this->reqSpecMgr->get_by_id($argsObj->req_spec_id);
		// $this->reqSpecMgr->delete($argsObj->req_spec_id);
		$this->reqSpecMgr->delete_deep($argsObj->req_spec_id);
		logAuditEvent(TLS("audit_req_spec_deleted",$this->auditContext->tproject,$req_spec['title']),
		               "DELETE",$argsObj->req_spec_id,"req_specs");
		  
		$guiObj->template = 'show_message.tpl';
		$guiObj->template_dir = '';
      	$guiObj->main_descr = lang_get('testproject') . TITLE_SEP . $argsObj->tproject_name;
		$guiObj->title=lang_get('delete_req_spec');

		$guiObj->user_feedback = sprintf(lang_get('req_spec_deleted'),$req_spec['title']);
		$guiObj->refresh_tree = 'yes'; // needed to enable refresh_tree logic
		$guiObj->result = 'ok';  
      	
		return $guiObj;	
  }
  
  
  /*
    function: reorder

    args:
    
    returns: 

  */
	function reorder(&$argsObj)
	{
      $guiObj=new stdClass();
  		$guiObj->template = 'reqSpecReorder.tpl';
		  $guiObj->main_descr = lang_get('testproject') . TITLE_SEP . $argsObj->tproject_name;
		  $guiObj->action_descr = lang_get('title_change_req_spec_order');

		  $order_by = ' ORDER BY NH.node_order,REQ_SPEC.id ';
		  $guiObj->all_req_spec = $this->reqSpecMgr->get_all_in_testproject($argsObj->tproject_id,$order_by);
      $guiObj->tproject_name=$argsObj->tproject_name;
      $guiObj->tproject_id=$argsObj->tproject_id;
	    return $guiObj;
  }



  /*
    function: doReorder

    args:
    
    returns: 

  */
	function doReorder(&$argsObj)
	{
      $guiObj=new stdClass();
      $guiObj->tproject_name=$argsObj->tproject_name;
      $guiObj->tproject_id=$argsObj->tproject_id;
  		$guiObj->template = 'project_req_spec_mgmt.tpl';
  		$guiObj->main_descr = lang_get('testproject') . TITLE_SEP . $argsObj->tproject_name;
  		
		  $nodes_in_order = transform_nodes_order($argsObj->nodes_order);

		  // need to remove first element, is testproject
		  array_shift($nodes_in_order);
		  $this->reqSpecMgr->set_order($nodes_in_order);
      $guiObj->refresh_tree='yes';
	    return $guiObj;
  }


  /*
    function: create

    args:
    
    returns: 

  */
	function createChild(&$argsObj)
	{
      $reqParent=$this->reqSpecMgr->get_by_id($argsObj->reqParentID);
      $guiObj=new stdClass();
		  $guiObj->main_descr = lang_get('req_spec_short') . TITLE_SEP . $reqParent['title'];
		  $guiObj->action_descr = lang_get('create_child_req_spec');

		  $guiObj->cfields = $this->reqSpecMgr->html_table_of_custom_field_inputs(null,$argsObj->tproject_id);
      $guiObj->template = $this->defaultTemplate;
		  $guiObj->submit_button_label=$this->submit_button_label;
 	    $guiObj->req_spec_id=null;
		  $guiObj->req_spec_title=null;
		  $guiObj->total_req_counter=null;

      return $guiObj;	
	}

}
?>
