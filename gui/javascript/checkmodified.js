/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @package TestLink
 * @author Erik Eloff
 * @copyright 2010, TestLink community
 * @version CVS: $Id: checkmodified.js,v 1.1 2010/01/11 15:59:02 erikeloff Exp $
 * @filesource
http://testlink.cvs.sourceforge.net/viewvc/testlink/testlink/gui/javascript/checkmodified.js
 * @link http://www.teamst.org
 * @since 1.9
 *
 * This file contains functions for warning the user of unsaved content
 * in an editor.
 *
 * Usage: Import this javascript and make sure the variables UNLOAD_MSG and
 * TC_EDITOR are defined.
 */

/** Any input can change content_modified to true if it is modified */
var content_modified = false;

// Notify on exit with unsaved data
function doBeforeUnload()
{
	if (FCKEditorChanged())
		content_modified = true;

	if (!content_modified) return; // Let the page unload

	if(window.event)
		window.event.returnValue = UNLOAD_MSG; // IE
	else
		return UNLOAD_MSG; // FX
}

// verify if content of any editor changed
function FCKEditorChanged()
{
	if (TC_EDITOR == "fckeditor")
	{
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
	window.body.onbeforeunload = doBeforeUnload; // IE
else
	window.onbeforeunload = doBeforeUnload; // FX
