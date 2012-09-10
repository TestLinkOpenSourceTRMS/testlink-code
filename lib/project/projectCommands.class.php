<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * test project commands
 *
 * @filesource  projectCommands.class.php
 * @package   TestLink
 * @author    Francisco Mancardi - francisco.mancardi@gmail.com
 * @copyright   2007-2012, TestLink community 
 * @link    http://www.teamst.org/index.php
 * @since 2.0
 *
 * @internal revisions
 * @since 2.0
 **/

class projectCommands
{
  private $db;
  private $tprojectMgr;
  private $templateCfg;
  private $grants;
  private $l18n;
  private $id;

  function __construct(&$db,&$userObj,$id = null)
  {
      $this->db = $db;
      $this->tprojectMgr = new testproject($db);
      $this->grants = new stdClass();
      $this->l18n = init_labels(array('btn_create' => null,'caption_new_tproject' => null));
      if(is_null($id))
      {
        $this->id = intval($id);
      }  
  }


  function setTemplateCfg($cfg)
  {
      $this->templateCfg=$cfg;
  }

  /**
   * 
   *
   */
  function initGuiBean(&$argsObj)
  {
    $obj = new stdClass();
    return $obj;
  }
   
  /**
   * initialize common object information
   *
   */

  function initTestProjectBasicInfo(&$argsObj,&$guiObj)
  {

  }
 
   
   
   
  /**
   * 
   *
   */
  function create(&$argsObj,&$guiObj,$oWebEditorKeys)
  {
    $uiObj = new stdClass();
    $uiObj->doActionValue = 'doCreate';
    $uiObj->buttonValue = $this->l18n['btn_create'];
    $uiObj->caption = $this->l18n['caption_new_tproject'];
    $uiObj->testprojects = $this->tprojectMgr->get_all(null,array('access_key' => 'id'));

    // update by refence
    $argsObj->active = 1;
    $argsObj->is_public = 1;
  
    $guiObj->initWebEditorFromTemplate = true;
    $guiObj->testprojects = $uiObj->testprojects;
    $guiObj->reloadType = 'none';

    return $uiObj;
  }


  /**
   * 
   *
   */
  function doCreate(&$argsObj,&$otCfg,$oWebEditorKeys,$request)
  {
      $guiObj = $this->create($argsObj,$otCfg,$oWebEditorKeys);
      $tplKey = 'tcNew';
      
  }

  /*
    function: edit
  
    args:
    
    returns: 
  
  */
  function edit(&$argsObj,&$otCfg,$oWebEditorKeys)
  {
      $guiObj = $this->initGuiBean($argsObj);
      return $guiObj;
  }


  /*
    function: doUpdate
    
        IMPORTANT NOTICE  
        this method will not return to caller  but act directly on GUI        
    args:
    
    returns: 

  */
  function doUpdate(&$argsObj,$request)
  {
  
        return $guiObj;
  }  



 /**
   * 
   *
   */
  function delete(&$argsObj,$request)
  {
      $guiObj = $this->initGuiBean($argsObj);
    return $guiObj;
  }

  /**
   * 
   *
   */
  function doDelete(&$argsObj,$request)
  {
    return $guiObj;
  }




  /**
   * 
   *
   */
  function show(&$argsObj,$request,$userFeedback)
  {
    exit(); 
  }
  

  function renderGui(&$argsObj,$guiObj,$opObj,$templateCfg,$cfgObj,$edit_steps)
  {
    $smartyObj = new TLSmarty();
    
    // needed by webeditor loading logic present on inc_head.tpl
    $smartyObj->assign('editorType',$guiObj->editorType);  

    $renderType = 'none';


  }



} // end class  
?>