/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @package     TestLink
 * @author      syji
 * @copyright   2010,2014 TestLink community
 * @filesource  execResult.js
 * @link        http://www.testlink.org
 * @since       1.9.20
 *
 * Functions JS for exeSetResult page
 *
 * Usage: 
 * Import this javascript for page execSetResult.
 *
 * @TODO. Extract javascript function from execSetResult.tpl, to this file
 *
 * @internal revisions
 * @since 1.9.19
 * user contribution to make this work with CKeditor and also with Chrome
 */

/**
 * Restore step's result to the form
 * 
 * @param steps
 * @returns
 */
function restoreSteps(steps) {
	
    var parser = new DOMParser;
    var noteDom;
	for (var stepsId in steps) {
		//for display special html character
		noteDom = parser.parseFromString(steps[stepsId]['notes'],"text/html");
		
		document.getElementById('step_notes_'+stepsId).value=noteDom.body.textContent;
		document.getElementById('step_status_'+stepsId).value=steps[stepsId]['status'];
		document.getElementById('backupSteps').style.display= "none";
	}

};


/**
 * Submit Executions status
 * before, check if partial execution exist
 * 
 * @param tcversionId   test case id
 * @param status status
 * @returns
 */
function doExecStatus(tcversionId,status,messageConfirm) {
	initHiddenAction();
	
	if (!isStepsRestore()) {  		
  		if (!confirm(messageConfirm)) {
  			return;
  		}  		
  	}
	
  
    document.getElementById('save_button_clicked').value=tcversionId;
    document.getElementById('statusSingle_'+tcversionId).value=status;
    document.getElementById('save_results').value=1;
    doSubmitForHTML5();
}


/**
 * Submit Executions status and next
 * before, check if partial execution exist
 * 
 * @param tcversionId   test case id
 * @param status status
 * @returns
 */
function doExecStatusAndNext(tcversionId,status,messageConfirm) {
	initHiddenAction();
	
	if (!isStepsRestore()) {  		
  		if (!confirm(messageConfirm)) {
  			return;
  		}  		
  	}
	
  	document.getElementById('save_button_clicked').value=tcversionId;
    document.getElementById('statusSingle_'+tcversionId).value=status;
    document.getElementById('save_and_next').value=1;
    doSubmitForHTML5();
}

/**
 * Before saving backup steps, check if partial execution exist 
 * 
 * @param messageConfirm
 * @returns
 */
function doBackupSteps(messageConfirm) {
	initHiddenAction();
  	if (!isStepsRestore()) {  		
  		if (!confirm(messageConfirm)) {
  			return;
  		}  		
  	}
  	
  	document.getElementById('save_backup').value=1;
    doSubmitForHTML5();
}


/**
 * initialisation form's action
 * 
 */
function initHiddenAction()  {
	document.getElementById('save_and_next').value=0;
    document.getElementById('save_results').value=0;
    document.getElementById('save_backup').value=0;
}

/**
 * Check if user restore a partial execution
 * 
 * @returns 
 * 	true : if partial execution exist and was restore. Or partial execution not exist
 *  else false.
 */

function isStepsRestore() {
	
	var element = document.getElementById("backupSteps");
	
	if (element) {
		return (element.style.display === "none");
	} else {
		return true;
	}
}
