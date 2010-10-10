/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @package TestLink
 * @author Francisco Mancardi
 * @copyright 2009, TestLink community
 * @version CVS: $Id: tcase_utils.js,v 1.1 2010/10/10 10:33:04 franciscom Exp $
 * @filesource http://testlink.cvs.sourceforge.net/viewvc/testlink/testlink/gui/javascript/ext_extensions.js
 * @link http://www.teamst.org
 *
 *
 * Utilities for certain test case actions / operations
 *
 * @internal revisions:
 * 20101010 - franciscom - creation
 **/

/**
 * Check through AJAX call check is name is duplicated
 * If duplicated found -> give visual feedback to user.
 *
 * initaly developed by Eloff
 * refactored to avoid (evil) global coupling
 *
 * int id: can be 0 when we are creating a new test case
 *           if > 0 we are editing then we can expect to find
 *           a test case with same name (myself).
 *           
 * string name: name to check
 *       
 * string warningOID: HTML Object ID used to give visual feedback to user    
 *
 * returns: -
 */
function checkTCaseDuplicateName(tcase_id,tcase_name,warningOID) {
	Ext.Ajax.request({
		url: 'lib/ajax/checkTCaseDuplicateName.php',
		method: 'GET',
		params: {
			testcase_id: tcase_id,
			name: tcase_name
		},
		success: function(result, request) {
			var obj = Ext.util.JSON.decode(result.responseText);
			$(warningOID).innerHTML = obj['message'];
		},
		failure: function (result, request) {
		}
	});
}