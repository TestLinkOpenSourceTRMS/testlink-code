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


/**
 *
 */
function initGuiForCreate(&$guiObj) 
{
/*
  $guiObj->canManage = 1;
  $guiObj->doAction = "create";  
  $guiObj->itemID = 0;
  $guiObj->found = "yes";
  $guiObj->tprojectName = '';
  $guiObj->tcasePrefix = '';
  $guiObj->issue_tracker_enabled = 0;
  $guiObj->active = 1;
  $guiObj->is_public = 1;
  
  /*
  $gui->tproject_id = 0;
  $gui->tplan_id = 0;
  */

  $a2c = ['requirementsEnabled' => 0, 
          'testPriorityEnabled' => 0, 
          'automationEnabled' => 0];
  $guiObj->projectOptions = (object) $a2c;
}

/**
 *
 */
function initIntegrations(&$tprojSet,$tprojQty,&$tplEngine) {
  $labels = init_labels(array('active_integration' => null, 
                              'inactive_integration' => null));

  $imgSet = $tplEngine->getImages();

  $intk = array('it' => 'issue', 'ct' => 'code');
  for($idx=0; $idx < $tprojQty; $idx++) {  
    foreach( $intk as $short => $item ) {
      $tprojSet[$idx][$short . 'statusImg'] = '';
      if($tprojSet[$idx][$short . 'name'] != '') {
        $ak = ($tprojSet[$idx][$item . '_tracker_enabled']) ? 
              'active' : 'inactive';
        $tprojSet[$idx][$short . 'statusImg'] = 
          ' <img title="' . $labels[$ak . '_integration'] . '" ' .
          ' alt="' . $labels[$ak . '_integration'] . '" ' .
          ' src="' . $imgSet[$ak] . '"/>';
      } 
    }
  }
}  