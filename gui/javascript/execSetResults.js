/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Functions JS for exeSetResult page
 *
 * @package     TestLink
 * @author      syji
 * @copyright   2010,2019 TestLink community
 * @filesource  execResult.js
 * @link        http://www.testlink.org
 * @used-by     execSetResult.tpl
 *
 */

/**
 *
 */
function doSubmitForHTML5() {
  jQuery("#hidden-submit-button").click();
}

/**
 * Submit Executions status.
 * Used 
 *
 */
function saveExecStatus(tcvID, status, msg, goNext) {
	
  /* Init */
  jQuery('#save_and_next').val(0);
	jQuery('#save_results').val(0);
	jQuery('#save_partial_steps_exec').val(0);

  jQuery('#save_button_clicked').val(tcvID);
  jQuery('#statusSingle_' + tcvID).val(status);
  if( goNext == undefined || goNext == 0 ) {
  	jQuery('#save_results').val(1);
  } else {
  	if( goNext == 1 ) {
  	  jQuery('#save_and_next').val(1);  		
  	}
  }

  doSubmitForHTML5();
}


/**
 * move to next test case without writting executtion
 *
 */
function moveToNextTC(tcvID) {
  jQuery('#save_button_clicked').val(tcvID);
}

/**
 * Check before save partial execution if notes or Status are not empty
 * 
 * @returns true / false
 */
function checkStepsHaveContent(msg) {
  var notes = jQuery(".step_note_textarea");
  
  // https://www.tutorialrepublic.com/faq/
  //         how-to-check-if-an-element-exists-in-jquery.php
  if( notes.length == 0 ) {
    // there are no steps
    return true;
  }

  
  for (var idx = 0; idx < notes.length; idx++) {
    if (notes[idx].value) {
      return true;
    }
  }

  var status = jQuery(".step_status");
  for (var idx = 0; idx < status.length; idx++) {
    if (status[idx].value && status[idx].value !== "n") {
      return true;
    }
  }

  if( msg !== undefined ) {
    alert(msg);
  }
  return false;
}

/**
 * Check if attachement is present
 * 
 * @returns
 */
function checkStepsHaveAttachments() {
  var uploads = jQuery(".uploadedFile");
  for (var idx = 0; idx < uploads.length; idx++) {
    if (uploads[idx].value) {
      return true;
    }
  }
  return false;
}

/**
 * uses globals alert_box_title,warning_msg
 *
 *
 */
function checkCustomFields(theForm) {
  var cfields_inputs='';
  var cfValidityChecks; 
  var f = theForm;

  var cfield_container = jQuery('#save_button_clicked').val();
  var access_key='cfields_exec_time_tcversionid_'+cfield_container; 
    
  if( document.getElementById(access_key) != null ) {    
      cfields_inputs = document.getElementById(access_key).getElementsByTagName('input');
      cfValidityChecks=validateCustomFields(cfields_inputs);
      if( !cfValidityChecks.status_ok ) {
          var warning_msg=cfMessages[cfValidityChecks.msg_id];
          alert_message(alert_box_title,warning_msg.replace(/%s/, cfValidityChecks.cfield_label));
          return false;
      }
  }
  return true;
}

/**
 * checkSubmitForStatusCombo
 * $statusCode has been checked, then false is returned to block form submit().
 *           
 * Dev. Note - remember this:
 *  KO: onclick="foo();checkSubmitForStatus('n')"
 *  OK: onclick="foo();return checkSubmitForStatus('n')"
 *                            ^^^^^^ 
 */
function checkSubmitForStatusCombo(oid,statusCode2block) {
  if(jQuery('#'+oid).val() == statusCode2block) {
    alert_message(alert_box_title,warning_nothing_will_be_saved);
    return false;
  }  
  return true;
}

/**
 *
 */
saveStepsPartialExecClicked = false;
$("#saveStepsPartialExec").click(function() {
   saveStepsPartialExecClicked = true;
});