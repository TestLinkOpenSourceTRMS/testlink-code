<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * code tracker commands
 *
 * @filesource  codeTrackerCommands.class.php
 * @package     TestLink
 * @author      Uwe Kirst - uwe_kirst@mentor.com
 * @link        http://testlink.sourceforge.net/
 *
 *
 * @internal revisions
 **/

class codeTrackerCommands
{
  var $codeTrackerMgr;
  private $db;
  private $templateCfg;
  private $grants;
  private $guiOpWhiteList;  // used to sanitize inputs on different pages
  private $entitySpec;


  function __construct(&$dbHandler)
  {
    $this->db=$dbHandler;
    $this->codeTrackerMgr = new tlCodeTracker($dbHandler);
    $this->entitySpec = $this->codeTrackerMgr->getEntitySpec();

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
    $obj->typeDomain = $this->codeTrackerMgr->getTypes();
    $obj->canManage = $argsObj->currentUser->hasRight($this->db,'codetracker_management'); 
    $obj->user_feedback = array('type' => '', 'message' => '');

    $obj->l18n = init_labels(array('codetracker_management' => null,
                                   'btn_save' => null, 'create' => null,
                                   'edit' => null,
                                   'checkConnection' => 'btn_check_connection',
                                   'codetracker_deleted' => null));

    // we experiment on way to get Action Description for GUI using __FUNCTION__
    $obj->l18n['doUpdate'] = $obj->l18n['edit'];
    $obj->l18n['doCreate'] = $obj->l18n['create'];
    $obj->l18n['doDelete'] = '';
    $obj->main_descr = $obj->l18n['codetracker_management']; 
    $obj->action_descr = ucfirst($obj->l18n[$caller]);
    $obj->connectionStatus = '';

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
    $templateCfg = templateConfiguration('codeTrackerEdit');
    $guiObj->template = $templateCfg->default_template;
    $guiObj->canManage = $argsObj->currentUser->hasRight($this->db,'codetracker_management'); 

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
    $ct = new stdClass();
    foreach($this->entitySpec as $property => $type)
    {
      $ct->$property = $argsObj->$property;
    }
    
    // Save user input. 
    // This will be useful if create() will fail, to present values again on GUI
    $guiObj->item = (array)$ct;
        
    $op = $this->codeTrackerMgr->create($ct);
    if($op['status_ok'])
    {
      $guiObj->main_descr = '';
      $guiObj->action_descr = '';
      $guiObj->template = "codeTrackerView.php";
    }   
    else
    {
      $templateCfg = templateConfiguration('codeTrackerEdit');
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
    
    $templateCfg = templateConfiguration('codeTrackerEdit');
    $guiObj->template = $templateCfg->default_template;

    $guiObj->item = $this->codeTrackerMgr->getByID($argsObj->id);
    $guiObj->canManage = $argsObj->currentUser->hasRight($this->db,'codetracker_management'); 
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

    $ct = new stdClass();
    $ct->id = $argsObj->id;
    foreach($this->entitySpec as $property => $type)
    {
      $ct->$property = $argsObj->$property;
    }

    // Save user input. 
    // This will be useful if create() will fail, to present values again on GUI
    $guiObj->item = (array)$ct;
        
    $op = $this->codeTrackerMgr->update($ct);
    if( $op['status_ok'] )
    {
      $guiObj->main_descr = '';
      $guiObj->action_descr = '';
      $guiObj->template = "codeTrackerView.php";
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

    // get minimal info for user feedback before deleting
    // $ct = $this->codeTrackerMgr->getByID($argsObj->id);
    $op = $this->codeTrackerMgr->delete($argsObj->id);
    
    // http://www.plus2net.com/php_tutorial/variables.php
    //if($op['status_ok'])
    //{
    //  $msg = sprintf($this->guiObj->l18n['codetracker_deleted'],$ct['name']);    
    //}
    //else
    //{
    //  $msg = $op['msg'];    
    //}
    //$_SESSION['codeTrackerView.user_feedback'] = $msg;

    $guiObj->action = 'doDelete';
    $guiObj->template = "codeTrackerView.php?";

    return $guiObj;
  }

  /**
   *
   */
  function checkConnection(&$argsObj,$request)
  {
    $guiObj = $this->initGuiBean($argsObj,__FUNCTION__);
    $guiObj->canManage = $argsObj->currentUser->hasRight($this->db,'codetracker_management');

    $tplCfg = templateConfiguration('codeTrackerEdit');
    $guiObj->template = $tplCfg->default_template;
  
    if( $argsObj->id > 0 )
    {
      $cxx = $this->codeTrackerMgr->getByID($argsObj->id);
      $guiObj->item['id'] = $cxx['id']; 
    }  
    else
    {
      $guiObj->operation = 'doCreate';
      $guiObj->item['id'] = 0;
    }
    
    $guiObj->item['name'] = $argsObj->name;
    $guiObj->item['type'] = $argsObj->type;
    $guiObj->item['cfg'] = $argsObj->cfg;
    $guiObj->item['implementation'] = 
             $this->codeTrackerMgr->getImplementationForType($argsObj->type);

    $class2create = $guiObj->item['implementation'];
    $cts = new $class2create($argsObj->type,$argsObj->cfg,$argsObj->name);
    $guiObj->connectionStatus = $cts->isConnected() ? 'ok' : 'ko';

    return $guiObj;
  }
  
} // end class  
