<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @filesource $RCSfile: reqSpecCommands.class.php,v $
 * @version $Revision: 1.1 $
 * @modified $Date: 2008/04/15 06:46:23 $ by $Author: franciscom $
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
	    echo "<pre>debug 20080415 - \ - " . __FUNCTION__ . " --- "; print_r($obj); echo "</pre>";

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
