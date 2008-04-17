<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @filesource $RCSfile: reqSpecCommands.class.php,v $
 * @version $Revision: 1.2 $
 * @modified $Date: 2008/04/17 08:24:10 $ by $Author: franciscom $
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

	function __construct(&$db)
	{
	    $this->db=$db;
	    $this->reqSpecMgr = new requirement_spec_mgr($db);
	    $this->reqMgr = new requirement_mgr($db);
	    $this->reqStatus=init_labels(config_get('req_status'));
	}

  /*
    function: create

    args:
    
    returns: 

  */
	function create(&$argsObj)
	{
      $obj=new stdClass();
		  $obj->main_descr = lang_get('testproject') . TITLE_SEP . $argsObj->tproject_name;
		  $obj->action_descr = lang_get('create_req_spec');

		  $obj->cfields = $this->reqSpecMgr->html_table_of_custom_field_inputs(null,$argsObj->tproject_id);
      $obj->template = $this->defaultTemplate;
		  $obj->submit_button_label=lang_get('btn_save');
 	    $obj->req_spec_id=null;
		  $obj->req_spec_title=null;
		  $obj->total_req_counter=null;
      echo "<pre>debug 20080416 - \ - " . __FUNCTION__ . " --- "; print_r($obj); echo "</pre>";

      return $obj;	
	}

  /*
    function: edit

    args:
    
    returns: 

  */
	function edit(&$argsObj)
	{
      $obj=new stdClass();

		  $obj->req_spec = $this->reqSpecMgr->get_by_id($argsObj->req_spec_id);
		  $obj->main_descr = lang_get('req_spec') . TITLE_SEP . $obj->req_spec['title'];
		  $obj->action_descr = lang_get('edit_req_spec');
      $obj->template = $this->defaultTemplate;
		  $obj->submit_button_label=lang_get('btn_save');
      
		  $argsObj->scope = $obj->req_spec['scope'];
      
		  $obj->req_spec_id=$argsObj->req_spec_id;
		  $obj->req_spec_title=$obj->req_spec['title'];
		  $obj->total_req_counter=$obj->req_spec['total_req'];
		  $obj->cfields = $this->reqSpecMgr->html_table_of_custom_field_inputs($argsObj->req_spec_id,$argsObj->tproject_id);

      return $obj;	
	}

  /*
    function: doCreate

    args:
    
    returns: 

  */
	function doCreate(&$argsObj,$request)
	{
      $obj=new stdClass();

		  $obj->main_descr = lang_get('testproject') . TITLE_SEP . $argsObj->tproject_name;
		  $obj->action_descr = lang_get('create_req_spec');
		  $obj->submit_button_label=lang_get('btn_save');
		  $obj->submit_button_label=lang_get('btn_save');

		  $obj->cfields = $this->reqSpecMgr->html_table_of_custom_field_inputs(null,$argsObj->tproject_id);
		  $ret = $this->reqSpecMgr->create($argsObj->tproject_id,$argsObj->title,$argsObj->scope,
		                                   $argsObj->countReq,$argsObj->user_id);
      
		  $obj->user_feedback = $ret['msg'];
		  if($ret['status_ok'])
		  {
		  	$obj->user_feedback = sprintf(lang_get('req_spec_created'),$argsObj->title);
		  	$cf_map = $this->reqSpecMgr->get_linked_cfields(null,$argsObj->tproject_id) ;
		  	$this->reqSpecMgr->values_to_db($request,$ret['id'],$cf_map);
		  	logAuditEvent(TLS("audit_req_spec_created",$argsObj->title),"CREATE",$ret['id'],"req_specs");
		  }
		  $argsObj->scope = "";

      return $obj;	
  }


  /*
    function: doUpdate

    args:
    
    returns: 

  */
	function doUpdate(&$argsObj,$request)
	{
      $obj=new stdClass();

		  $obj->req_spec_id = $argsObj->req_spec_id;
		  $obj->submit_button_label=lang_get('btn_save');
		  $ret = $this->reqSpecMgr->update($argsObj->req_spec_id,$argsObj->title,
		                                   $argsObj->scope,$argsObj->countReq,$argsObj->user_id);
	    $obj->template = null;
		  $obj->user_feedback = $ret['msg'];
		  if($ret['status_ok'])
		  {
        $obj->template = "reqSpecView.php?req_spec_id={$obj->req_spec_id}";
		  	$cf_map = $this->reqSpecMgr->get_linked_cfields($argsObj->req_spec_id);
		  	$this->reqSpecMgr->values_to_db($request,$argsObj->req_spec_id,$cf_map);
		  	logAuditEvent(TLS("audit_req_spec_saved",$argsObj->title),"SAVE",$argsObj->req_spec_id,"req_specs");
		  }
      
		  $obj->cfields = $this->reqSpecMgr->html_table_of_custom_field_values($argsObj->req_spec_id,$argsObj->tproject_id);
		  $obj->req_spec = $this->reqSpecMgr->get_by_id($argsObj->req_spec_id);
		  $obj->req_spec_id = $argsObj->req_spec_id;

      return $obj;	
  }


  /*
    function: doDelete

    args:
    
    returns: 

  */
	function doDelete(&$argsObj)
	{
      $obj=new stdClass();

      return $obj;	
  }
  
  /*
    function: reorder

    args:
    
    returns: 

  */
	function reorder(&$argsObj)
	{
      $obj=new stdClass();

      return $obj;	
  }


  /*
    function: doReorder

    args:
    
    returns: 

  */
	function doReorder(&$argsObj)
	{
      $obj=new stdClass();

      return $obj;	
  }
}
?>
