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

  /*
	if (!isStepsRestore()) {
		if (!confirm(msg)) {
			return;
		}
	}
	*/
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

