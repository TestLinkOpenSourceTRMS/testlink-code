/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @package TestLink
 * @author Erik Eloff
 * @copyright 2010, TestLink community
 * @version CVS: $Id: checkmodified.js,v 1.3 2010/02/21 15:24:04 franciscom Exp $
 * @filesource http://testlink.cvs.sourceforge.net/viewvc/testlink/testlink/gui/javascript/checkmodified.js
 * @link http://www.teamst.org
 * @since 1.9
 *
 * Functions for warning the user of unsaved content in an FCKeditor.
 *
 * Usage: Import this javascript and make sure the variables unload_msg and
 *        tc_editor are defined.
 */

/** Any input can change content_modified to true if it is modified */
var content_modified = false;

/** Set show_modified_warning to false when clear to submit */
var show_modified_warning = true;

// Notify on exit with unsaved data
function doBeforeUnload()
{
	if (!show_modified_warning) {
		return;
	}

	if (FCKEditorChanged()) {
		content_modified = true;
	}

	if (!content_modified) {
		return; // Let the page unload
	}

	if(window.event) 
	{
		window.event.returnValue = unload_msg; // IE
	} 
	else 
	{
		return unload_msg; // Firefox
	}
}

// verify if content of any editor changed
function FCKEditorChanged()
{
	if (tc_editor == "fckeditor") {
		for (var editorname in FCKeditorAPI.Instances) {
			if (FCKeditorAPI.GetInstance(editorname).IsDirty()) {
				return true;
			}
		}
	}
	return false;
}

// Tell FCKeditor that it is clean from start
function FCKeditor_OnComplete(editorInstance)
{
    editorInstance.ResetIsDirty();
}

// set unload checking if configured to use
if(window.body) 
{
	window.body.onbeforeunload = doBeforeUnload; // IE
} 
else 
{
	window.onbeforeunload = doBeforeUnload; // Firefox
}
