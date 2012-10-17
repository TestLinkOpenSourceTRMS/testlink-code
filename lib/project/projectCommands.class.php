<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * test project commands
 *
 * @filesource  projectCommands.class.php
 * @package     TestLink
 * @author      Francisco Mancardi - francisco.mancardi@gmail.com
 * @copyright   2007-2012, TestLink community 
 * @link        http://www.teamst.org/index.php
 * @since       2.0
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
    $guiObj->form_security_field = form_security_field();

    

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
  

  function renderGui(&$argsObj,$guiObj,$opObj,$templateCfg,$cfgObj)
  {
    $smartyObj = new TLSmarty();
    $renderType = 'none';

    //
    // key: operation requested (normally received from GUI on doAction)
    // value: operation value to set on doAction HTML INPUT
    // This is useful when you use same template (example xxEdit.tpl), for create and edit.
    // When template is used for create -> operation: doCreate.
    // When template is used for edit -> operation: doUpdate.
    //              
    // used to set value of: $guiObj->operation
    //
    $actionOperation = array('create' => 'doCreate', 'doCreate' => 'doCreate',
                             'edit' => 'doUpdate','delete' => 'doDelete', 'doDelete' => '');

    // Need to document
    $key2work = 'initWebEditorFromTemplate';
    $initWebEditorFromTemplate = property_exists($opObj,$key2work) ? $opObj->$key2work : false;                             
    $key2work = 'cleanUpWebEditor';
    $cleanUpWebEditor = property_exists($opObj,$key2work) ? $opObj->$key2work : false;                             

    $oWebEditor = $this->createWebEditors($argsObj->basehref,$cfgObj->webEditorCfg,null); 

	  foreach ($oWebEditor->cfg as $key => $value)
  	{
  		$of = &$oWebEditor->editor[$key];
  		$rows = $oWebEditor->cfg[$key]['rows'];
  		$cols = $oWebEditor->cfg[$key]['cols'];
  		
		  switch($argsObj->doAction)
    	{
    	    case "edit":
    	    case "delete":
  				  $initWebEditorFromTemplate = false;
  				  $of->Value = $argsObj->$key;
          break;

    	    case "doCreate":
    	    case "doDelete":
  				  $initWebEditorFromTemplate = false;
  				  $of->Value = $argsObj->$key;
  			  break;
  			
    	    case "create":
  			  default:	
  				  $initWebEditorFromTemplate = true;
  			  break;
  		}
      $guiObj->operation = $actionOperation[$argsObj->doAction];
	
  		if(	$initWebEditorFromTemplate )
  		{
			  $of->Value = getItemTemplateContents('project_template', $of->InstanceName, '');	
		  }
		  else if( $cleanUpWebEditor )
		  {
			  $of->Value = '';
		  }
		  
		  $guiObj->$key = $of->CreateHTML($rows,$cols);
    } // foreach
    
 
// -.....................
    switch($argsObj->doAction)
    {
        case "edit":
   	    case "create":
        case "delete":
   	    case "doCreate":
        case "doDelete":
            $renderType = 'template';
            
            // Document !!!!
            $key2loop = get_object_vars($opObj);
            foreach($key2loop as $key => $value)
            {
            	$guiObj->$key = $value;
            }
            $guiObj->operation = $actionOperation[$argsObj->doAction];
            
            $tplDir = (!isset($opObj->template_dir)  || is_null($opObj->template_dir)) ? $templateCfg->template_dir : $opObj->template_dir;
            $tpl = is_null($opObj->template) ? $templateCfg->default_template : $opObj->template;
            
            $pos = strpos($tpl, '.php');
           	if($pos === false)
           	{
                $tpl = $tplDir . $tpl;      
            }
            else
            {
                $renderType = 'redirect';  
            } 
        break;
    }  // switch $argsObj->doAction

    switch($renderType)
    {
        case 'template':
          $smartyObj->assign('gui',$guiObj);
          $smartyObj->display($tpl);
        break;  
 
        case 'redirect':
		      header("Location: {$tpl}");
	  		  exit();
        break;

        default:
       	break;
    }

  } // function end

  /*
    function: createWebEditors
  
        When using tinymce or none as web editor, 
        we need to set rows and cols to appropriate values, to avoid an ugly ui.
        null => use default values defined on editor class file
        Rows and Cols values are useless for FCKeditor
  
    args :
    
    returns: object
    
  */
  function createWebEditors($basehref,$editorCfg,$editorSet=null)
  {
      // Rows and Cols configuration
      $cols = array('notes' => array('horizontal' => 38, 'vertical' => 44));

      $owe = new stdClass();
		  $owe->cfg = array('notes' => array('rows'=> null,'cols' => null));
      $owe->editor = array();

      if(is_null($basehref) || trim($basehref) == '')
      {
        throw new Exception(__METHOD__ . ' basehref can NOT BE EMPTY.');
      }

      $force_create = is_null($editorSet);
      foreach ($owe->cfg as $key => $value)
      {
      	if( $force_create || isset($editorSet[$key]) )
      	{
      		$owe->editor[$key] = web_editor($key,$basehref,$editorCfg);
      	}
      	else
      	{
      		unset($owe->cfg[$key]);
      	}
      }
      return $owe;
  }

} // end class  
?>