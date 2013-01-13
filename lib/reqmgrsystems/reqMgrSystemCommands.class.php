<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * req. management system commands
 *
 * @filesource  reqMgrSystemCommands.class.php
 * @package     TestLink
 * @author      Francisco Mancardi - francisco.mancardi@gmail.com
 * @copyright   2013, TestLink community 
 * @link        http://www.teamst.org/index.php
 *
 *
 * @internal revisions
 * @since 1.9.6
 **/

class reqMgrSystemCommands
{
  var $mgr;
  private $db;
  private $templateCfg;
  private $grants;
  private $guiOpWhiteList;  // used to sanitize inputs on different pages
  private $entitySpec;


  function __construct(&$dbHandler)
  {
    $this->db = $dbHandler;
    $this->mgr = new tlReqMgrSystem($dbHandler);
    $this->entitySpec = $this->mgr->getEntitySpec();

    $this->grants=new stdClass();
    $this->grants->canManage = false; 

    $this->guiOpWhiteList = array_flip(array('checkConnection','create','edit','delete','doCreate',
                                             'doUpdate','doDelete'));
  }

  function setTemplateCfg($cfg)
  {
      $this->templateCfg = $cfg;
  }

  function getGuiOpWhiteList()
  {
      return $this->guiOpWhiteList;
  }

  /**
   * 
   *
   */
  function initGuiBean(&$argsObj, $caller)
  {
    $obj = new stdClass();
    $obj->action = $caller;
    $obj->typeDomain = $this->mgr->getTypes();
    $obj->canManage = $argsObj->currentUser->hasRight($this->db,'reqmgrsystem_management'); 
    $obj->user_feedback = array('type' => '', 'message' => '');

    $obj->l18n = init_labels(array('reqmgrsystem_management' => null, 'btn_save' => null,
                                   'create' => null, 'edit' => null, 'reqmgrsystem_deleted' => null));

    // we experiment on way to get Action Description for GUI using __FUNCTION__
    $obj->l18n['doUpdate'] = $obj->l18n['edit'];
    $obj->l18n['doCreate'] = $obj->l18n['create'];
    $obj->l18n['doDelete'] = '';
    $obj->main_descr = $obj->l18n['reqmgrsystem_management']; 
    $obj->action_descr = ucfirst($obj->l18n[$caller]);

    switch($caller)
    {
      case 'delete':
      case 'doDelete':
        $obj->submit_button_label = '';
      break;
      
      default:
        $obj->submit_button_label = $obj->l18n['btn_save'];
      break;
    }

    return $obj;
  }
   
  /**
   * 
   *
   */
  function create(&$argsObj,$request,$caller=null)
  {
    $guiObj = $this->initGuiBean($argsObj,(is_null($caller) ? __FUNCTION__ : $caller));
    $templateCfg = templateConfiguration('reqMgrSystemEdit');
    $guiObj->template = $templateCfg->default_template;
    $guiObj->canManage = $argsObj->currentUser->hasRight($this->db,'reqmgrsystem_management'); 

    $guiObj->item = array('id' => 0);
    $dummy = '';
    foreach($this->entitySpec as $property => $type)
    {
      $guiObj->item[$property] = ($type == 'int') ? 0 :'';
    }
    return $guiObj;
  }

  /**
   * 
   *
   */
  function doCreate(&$argsObj,$request)
  {
    $guiObj = $this->create($argsObj,$request,__FUNCTION__);
  
    // Checks are centralized on create()
    $it = new stdClass();
    foreach($this->entitySpec as $property => $type)
    {
      $it->$property = $argsObj->$property;
      
    }
    
    // Save user input. 
    // This will be useful if create() will fail, to present values again on GUI
    $guiObj->item = (array)$it;
        
    $op = $this->mgr->create($it);
    if($op['status_ok'])
    {
      $guiObj->main_descr = '';
      $guiObj->action_descr = '';
      $guiObj->template = "reqMgrSystemView.php";
    }    
    else
    {
      $templateCfg = templateConfiguration('reqMgrSystemEdit');
      $guiObj->template=$templateCfg->default_template;  
      $guiObj->user_feedback['message'] = $op['msg'];
    }
    
    return $guiObj;    
    }




  /*
    function: edit
  
    args:
    
    returns: 
  
  */
  function edit(&$argsObj,$request)
  {
    $guiObj = $this->initGuiBean($argsObj,__FUNCTION__);
     
    $templateCfg = templateConfiguration('reqMgrSystemEdit');
    $guiObj->template = $templateCfg->default_template;

    $guiObj->item = $this->mgr->getByID($argsObj->id);
    $guiObj->canManage = $argsObj->currentUser->hasRight($this->db,'reqmgrsystem_management'); 
    return $guiObj;
  }


  /*
    function: doUpdate

    args:
    
    returns: 

  */
    function doUpdate(&$argsObj,$request)
  {
    $guiObj = $this->initGuiBean($argsObj,__FUNCTION__);

    $it = new stdClass();
    $it->id = $argsObj->id;
    foreach($this->entitySpec as $property => $type)
    {
      $it->$property = $argsObj->$property;
    }

    // Save user input. 
    // This will be useful if create() will fail, to present values again on GUI
    $guiObj->item = (array)$it;
        
    $op = $this->mgr->update($it);
    if( $op['status_ok'] )
    {
      $guiObj->main_descr = '';
      $guiObj->action_descr = '';
      $guiObj->template = "reqMgrSystemView.php";
    }
    else
    {
      $guiObj->user_feedback['message'] = $op['msg'];
      $guiObj->template = null;
    }
    
    return $guiObj;
  }  

  /**
   * 
   *
   */
  function doDelete(&$argsObj,$request)
  {
    $guiObj = $this->initGuiBean($argsObj,__FUNCTION__);
    $op = $this->mgr->delete($argsObj->id);
    $guiObj->action = 'doDelete';
    $guiObj->template = "reqMgrSystemView.php?";
    return $guiObj;
  }


  function checkConnection(&$argsObj,$request)
  {
    $guiObj = $this->initGuiBean($argsObj,__FUNCTION__);
    
    $xx = $this->mgr->getByID($argsObj->id);
    $class2create = $xx['implementation'];
    $its = new $class2create($xx['type'],$xx['cfg']);

    
    $guiObj->template = "reqMgrSystemView.php?";
    $guiObj->connectionStatus = $its->isConnected() ? 'ok' : 'ko';
    return $guiObj;
  }

} // end class  
?>