/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @package     TestLink
 * @author      Erik Eloff
 * @copyright   2010,2014 TestLink community
 * @filesource  checkmodified.js
 * @link        http://www.testlink.org
 * @since       1.9
 *
 * Functions for warning the user of unsaved content in an FCKeditor or CKeditor.
 *
 * Usage: 
 * Import this javascript and make sure the variables unload_msg and tc_editor are defined.
 *
 * @internal revisions
 * @since 1.9.8
 * user contribution to make this work with CKeditor and also with Chrome
 */

/** Any input can change content_modified to true if it is modified */
var content_modified = false;

/** Set show_modified_warning to false when clear to submit */
var show_modified_warning = true;

// Notify on exit with unsaved data
function doBeforeUnload()
{
  if (!show_modified_warning) 
  {
    return;
  }

  if (editorChanged()) 
  {
    content_modified = true;
  }

  if (!content_modified) 
  {
    return; // Let the page unload
  }

  if(window.event) 
  {
    window.event.returnValue = unload_msg; // IE
    return unload_msg; // Chrome
  } 
  else 
  {
    return unload_msg; // Firefox
  }
}

// verify if content of any editor changed
function editorChanged()
{
  if (tc_editor == "fckeditor") 
  {
    for (var id in FCKeditorAPI.Instances) 
    {
      if (FCKeditorAPI.GetInstance(id).IsDirty()) 
      {
        return true;
      }
    }
  }

  if (tc_editor == "ckeditor") 
  {
    for (var id in CKEDITOR.instances) 
    {
      if (CKEDITOR.instances[id].checkDirty()) 
      {
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