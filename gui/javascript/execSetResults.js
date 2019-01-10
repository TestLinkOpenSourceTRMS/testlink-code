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
 * initialisation form's action
 * 
 */
function initHiddenAction() {
	document.getElementById("save_and_next").value = 0;
	document.getElementById("save_results").value = 0;
	document.getElementById("save_backup").value = 0;
}

/**
 * Check if user restore a partial execution
 * 
 * @returns true : if partial execution exist and was restore. Or partial
 *          execution not exist else false.
 */
function isStepsRestore() {

	var element = document.getElementById("backupSteps");

	if (element) {
		return (element.style.display === "none");
	} else {
		return true;
	}
}

/**
 * Submit Executions status before, check if partial execution exist
 * 
 * @param tcversionId
 *            test case id
 * @param status
 *            status
 * @returns
 */
function doExecStatus(tcversionId, status, messageConfirm) {
	initHiddenAction();

	if (!isStepsRestore()) {
		if (!confirm(messageConfirm)) {
			return;
		}
	}

	document.getElementById("save_button_clicked").value = tcversionId;
	document.getElementById("statusSingle_" + tcversionId).value = status;
	document.getElementById("save_results").value = 1;
	doSubmitForHTML5();
}

/**
 * Submit Executions status and next before, check if partial execution exist
 * 
 * @param tcversionId
 *            test case id
 * @param status
 *            status
 * @returns
 */
function doExecStatusAndNext(tcversionId, status, messageConfirm) {
	initHiddenAction();

	if (!isStepsRestore()) {
		if (!confirm(messageConfirm)) {
			return;
		}
	}

	document.getElementById("save_button_clicked").value = tcversionId;
	document.getElementById("statusSingle_" + tcversionId).value = status;
	document.getElementById("save_and_next").value = 1;
	doSubmitForHTML5();
}

/**
 * Check before save partial execution if notes and Status are not empty
 * 
 * @returns true is there are one notes or one status fill
 */
function checkStepsNotEmpty() {
	var notes = document.getElementsByClassName("step_note_textarea");

	for (var i = 0; i < notes.length; i++) {
		if (notes[i].value) {
			return true;
		}
	}

	var status = document.getElementsByClassName("step_status");
	for (var j = 0; j < status.length; j++) {
		if (status[j].value && status[j].value !== "n") {
			return true;
		}
	}

	return false;
}

/**
 * Check if attachement is present
 * 
 * @returns
 */
function checkHasAttachement() {

	var uploads = document.getElementsByClassName("uploadedFile");
	for (var i = 0; i < uploads.length; i++) {
		if (uploads[i].value) {
			return true;
		}
	}
	return false;
}


/**
 * Restore step's result to the form
 * 
 * @param steps
 * @returns
 */
function restoreSteps(steps,msgWarning) {

	var isStepsNotEmpty = checkStepsNotEmpty();
	var doRestore = true;
	if (isStepsNotEmpty) {
		if (!confirm(msgWarning)) {
			doRestore = false;
		}
	}
	
	if (doRestore === true) {
		var parser = new DOMParser;
		var noteDom;
		for ( var stepsId in steps) {
			// for display special html character
			noteDom = parser.parseFromString(steps[stepsId]["notes"], "text/html");

			document.getElementById("step_notes_" + stepsId).value = noteDom.body.textContent;
			document.getElementById("step_status_" + stepsId).value = steps[stepsId]["status"];
			document.getElementById("backupSteps").style.display = "none";
		}
	}
}

/**
 * Before saving backup steps, check if partial execution exist
 * 
 * @param messageConfirmRestore
 *            message to confirm save
 * @param messageStepsEmpty
 *            alert notes and status are empty
 * 
 * @returns
 */
function doBackupSteps(backupPresent, messageConfirmRestore, messageStepsEmpty,
		messageAttachement) {
	var isStepsNotEmpty = checkStepsNotEmpty();
	var isRestore = isStepsRestore();
	var hasAttachement = checkHasAttachement();
	initHiddenAction();

	// for display all message Once
	var msg = "";
	var doSave = true;
	if (!isRestore && isStepsNotEmpty) {
		var msgConfirm = messageConfirmRestore;
		if (hasAttachement) {
			msgConfirm += "\n" + messageAttachement;
		}
		if (!confirm(msgConfirm)) {
			return;
		}
	} else if (hasAttachement && isStepsNotEmpty) {
		msg = messageAttachement;
	} else {
		if (!isStepsNotEmpty) {
			msg = messageStepsEmpty;
			doSave = false;
		}
		if (hasAttachement) {
			msg += "\n" + messageAttachement;
		}
		if (!isRestore) {
			msg += "\n" + backupPresent;
		}
	}

	// finaly save
	if (doSave) {
		document.getElementById("save_backup").value = 1;
		doSubmitForHTML5();
	} else {
		alert(msg);
	}
}