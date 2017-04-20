<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * issue tracker commands
 *
 * @filesource  issueTrackerCommands.class.php
 * @package     TestLink
 * @author      Francisco Mancardi - francisco.mancardi@gmail.com
 * @copyright   2007-2017, TestLink community 
 * @link        http://testlink.sourceforge.net/
 *
 *
 * @internal revisions
 **/

class issueTrackerCommands
{
  var $issueTrackerMgr;
  private $db;
  private $templateCfg;
  private $grants;
  private $guiOpWhiteList;  // used to sanitize inputs on different pages
  private $entitySpec;


  function __construct(&$dbHandler)
  {
    $this->db=$dbHandler;
    $this->issueTrackerMgr = new tlIssueTracker($dbHandler);
    $this->entitySpec = $this->issueTrackerMgr->getEntitySpec();

    $this->grants=new stdClass();
    $this->grants->canManage = false; 

    $this->guiOpWhiteList = array_flip(array('checkConnection','create','edit',
                                             'delete','doCreate',
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
    $obj->typeDomain = $this->issueTrackerMgr->getTypes();
    $obj->canManage = $argsObj->currentUser->hasRight($this->db,'issuetracker_management'); 
    $obj->user_feedback = array('type' => '', 'message' => '');

    $obj->l18n = init_labels(array('issuetracker_management' => null, 
                                   'btn_save' => null,'create' => null, 
                                   'edit' => null, 
                                   'checkConnection' => 'btn_check_connection', 
                                   'issuetracker_deleted' => null));

    // we experiment on way to get Action Description for GUI using __FUNCTION__
    $obj->l18n['doUpdate'] = $obj->l18n['edit'];
    $obj->l18n['doCreate'] = $obj->l18n['create'];
    $obj->l18n['doDelete'] = '';
    $obj->main_descr = $obj->l18n['issuetracker_management']; 
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
    $templateCfg = templateConfiguration('issueTrackerEdit');
    $guiObj->template = $templateCfg->default_template;
    $guiObj->canManage = $argsObj->currentUser->hasRight($this->db,'issuetracker_management'); 

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
        
    $op = $this->issueTrackerMgr->create($it);
    if($op['status_ok'])
    {
      $guiObj->main_descr = '';
      $guiObj->action_descr = '';
      $guiObj->template = "issueTrackerView.php";
    }   
    else
    {
      $templateCfg = templateConfiguration('issueTrackerEdit');
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
    
    $templateCfg = templateConfiguration('issueTrackerEdit');
    $guiObj->template = $templateCfg->default_template;

    $guiObj->item = $this->issueTrackerMgr->getByID($argsObj->id);
    $guiObj->canManage = $argsObj->currentUser->hasRight($this->db,'issuetracker_management'); 
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
        
    $op = $this->issueTrackerMgr->update($it);
    if( $op['status_ok'] )
    {
      $guiObj->main_descr = '';
      $guiObj->action_descr = '';
      $guiObj->template = "issueTrackerView.php";
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
    // $it = $this->issueTrackerMgr->getByID($argsObj->id);
    $op = $this->issueTrackerMgr->delete($argsObj->id);
    
    // http://www.plus2net.com/php_tutorial/variables.php
    //if($op['status_ok'])
    //{
    //  $msg = sprintf($this->guiObj->l18n['issuetracker_deleted'],$it['name']);    
    //}
    //else
    //{
    //  $msg = $op['msg'];    
    //}
    //$_SESSION['issueTrackerView.user_feedback'] = $msg;

    $guiObj->action = 'doDelete';
    $guiObj->template = "issueTrackerView.php?";

    return $guiObj;
  }


  /**
   *
   */
  function checkConnection(&$argsObj,$request)
  {
    $guiObj = $this->initGuiBean($argsObj,__FUNCTION__);
    $guiObj->canManage = $argsObj->currentUser->hasRight($this->db,'issuetracker_management'); 
    
    $tplCfg = templateConfiguration('issueTrackerEdit');
    $guiObj->template = $tplCfg->default_template;
  
    if( $argsObj->id > 0 )
    {
      $ixx = $this->issueTrackerMgr->getByID($argsObj->id);
      $guiObj->item['id'] = $ixx['id']; 
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
             $this->issueTrackerMgr->getImplementationForType($argsObj->type);

    $class2create = $guiObj->item['implementation'];

    $its = new $class2create($argsObj->type,$argsObj->cfg,$argsObj->name);
    $guiObj->connectionStatus = $its->isConnected() ? 'ok' : 'ko';

    return $guiObj;
  }
  
} // end class  
