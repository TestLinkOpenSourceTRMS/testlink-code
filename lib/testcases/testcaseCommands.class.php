<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 *
 * Filename $RCSfile: testcaseCommands.class.php,v $
 *
 * @version $Revision: 1.3 $
 * @modified $Date: 2008/08/14 15:08:26 $  by $Author: franciscom $
 * testcases commands
 *
 *
 *
**/
class testcaseCommands
{
  private $db;
  private $tcaseMgr;
  private $templateCfg;

	function __construct(&$db)
	{
	    $this->db=$db;
	    $this->tcaseMgr = new testcase($db);
	}

	function setTemplateCfg($cfg)
	{
	    $this->templateCfg=$cfg;
	}


  /*
    function: edit

    args:
    
    returns: 

  */
	function edit(&$argsObj,&$otCfg,$oWebEditorKeys)
	{
	  $tpl_cfg=config_get('tpl');
	  
    $guiObj = new stdClass();
    $otCfg->to->map = $this->tcaseMgr->get_keywords_map($argsObj->tcase_id," ORDER BY keyword ASC ");

    keywords_opt_transf_cfg($otCfg, $argsObj->assigned_keywords_list);

  	$tc_data = $this->tcaseMgr->get_by_id($argsObj->tcase_id,$argsObj->tcversion_id);

    
  	foreach ($oWebEditorKeys as $key => $value)
   	{
  	  	$guiObj->$key = $tc_data[0][$key];
  	}

		$guiObj->cfields = $this->tcaseMgr->html_table_of_custom_field_inputs($argsObj->tcase_id);
    $guiObj->tc=$tc_data[0];
    $guiObj->opt_cfg=$otCfg;
    $guiObj->template=$tpl_cfg['tcEdit'];
    return $guiObj;
  }


  /*
    function: doUpdate

    args:
    
    returns: 

  */
	function doUpdate(&$argsObj,$request)
	{
      $smartyObj = new TLSmarty();
      $guiObj=new stdClass();
      $viewer_args=array();
      
   	  $guiObj->refresh_tree=$argsObj->do_refresh?"yes":"no";

		  // to get the name before the user operation
		  $tc_old = $this->tcaseMgr->get_by_id($argsObj->tcase_id,$argsObj->tcversion_id);

      $ret=$this->tcaseMgr->update($argsObj->tcase_id, $argsObj->tcversion_id, $argsObj->name, 
		                               $argsObj->summary, $argsObj->steps, $argsObj->expected_results,
		                               $argsObj->user_id, $argsObj->assigned_keywords_list,
		                               TC_DEFAULT_ORDER, $argsObj->exec_type, $argsObj->importance);

      $smartyObj->assign('attachments',null);
		  if($ret['status_ok'])
		  {
		    $refresh_tree='yes';
		    $msg = '';
  			$ENABLED = 1;
	  		$NO_FILTERS = null;
		  	$cf_map=$this->tcaseMgr->cfield_mgr->get_linked_cfields_at_design($argsObj->testproject_id,
			                                                                     $ENABLED,$NO_FILTERS,'testcase') ;
			  $this->tcaseMgr->cfield_mgr->design_values_to_db($request,$argsObj->tcase_id);
         
        $attachments[$argsObj->tcase_id] = getAttachmentInfosFrom($this->tcaseMgr,$argsObj->tcase_id);
        $smartyObj->assign('attachments',$attachments);
		  }
		  else
		  {
		      $refresh_tree='no';
		     	$msg = $ret['msg'];
		  }
	
	    $viewer_args['refresh_tree'] = $refresh_tree;
 	    $viewer_args['user_feedback'] = $msg;

      $smartyObj->assign('has_been_executed',$argsObj->has_been_executed);
      $smartyObj->assign('execution_types',$this->tcaseMgr->get_execution_types());
      
	    $this->tcaseMgr->show($smartyObj,$this->templateCfg->template_dir,
	                          $argsObj->tcase_id,$argsObj->tcversion_id,$viewer_args);
 
      return $guiObj;
  }  

} // end class  
?>