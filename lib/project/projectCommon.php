<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Project View and Edit common functions
 *
 * @package 	  TestLink
 * @author 		  TestLink community
 * @copyright   2007-2020, TestLink community 
 * @filesource  projectCommon.php
 * @used-by     projectView.php 
 * @used-by     projectEdit.php 
 * @link 		    http://www.testlink.org/
 *
 */


require_once("web_editor.php");

/**
 *
 * @used-by projectView.php
 */
function initGuiForCreate(&$dbH,&$argsObj,&$guiObj) 
{
  $guiObj->canManage = "yes";
  $guiObj->doActionValue = "doCreate";
    
  $guiObj->itemID = 0;
  $guiObj->found = "yes";
  $guiObj->tprojectName = '';
  $guiObj->tcasePrefix = '';
  $guiObj->issue_tracker_enabled = 0;
  $guiObj->active = 1;
  $guiObj->is_public = 1;
  $guiObj->api_key = '';
  $guiObj->testprojects = '';
  
  $guiObj->buttonValue = lang_get('btn_create');
  $guiObj->caption = lang_get('caption_new_tproject');


  $a2c = ['requirementsEnabled' => 0, 
          'testPriorityEnabled' => 1, 
          'automationEnabled' => 1];
  $guiObj->projectOptions = (object) $a2c;

  $guiObj->editorCfg = getWebEditorCfg('testproject');
  $guiObj->editorType = $guiObj->editorCfg['type'];    

  require_once(require_web_editor($guiObj->editorType));
  $guiObj->of = web_editor('notes',$_SESSION['basehref'],$guiObj->editorCfg) ;
  $guiObj->of->Value = getItemTemplateContents('project_template', $guiObj->of->InstanceName,'');
  $guiObj->notes = $guiObj->of->CreateHTML();


  $guiObj->issue_tracker_id = 0;
  $guiObj->code_tracker_id = 0;

  $ent2loop = array('tlIssueTracker' => 'issueTrackers', 
                    'tlCodeTracker' => 'codeTrackers');
  
  foreach($ent2loop as $cl => $pr) {
    $mgr = new $cl($dbH);
    $guiObj->$pr = $mgr->getAll();
    unset($mgr);
  }

}

/**
 *
 */
function initIntegrations(&$tprojSet,$tprojQty,&$tplEngine) {
  $labels = init_labels(array('active_integration' => null, 
                              'inactive_integration' => null));

  $imgSet = $tplEngine->getFontawesomeSet();

  $intk = array('it' => 'issue', 'ct' => 'code');
  for($idx=0; $idx < $tprojQty; $idx++) {  
    foreach( $intk as $short => $item ) {
      $tprojSet[$idx][$short . 'statusImg'] = '';
      if($tprojSet[$idx][$short . 'name'] != '') {
        $ak = ($tprojSet[$idx][$item . '_tracker_enabled']) ? 
              'active' : 'inactive';
        /*
        $tprojSet[$idx][$short . 'statusImg'] = 
          ' <img title="' . $labels[$ak . '_integration'] . '" ' .
          ' alt="' . $labels[$ak . '_integration'] . '" ' .
          ' src="' . $imgSet[$ak] . '"/>';
        */
        $tprojSet[$idx][$short . 'statusImg'] = 
          sprintf($imgSet[$ak],$labels[$ak . '_integration']);
      } 
    }
  }
}  