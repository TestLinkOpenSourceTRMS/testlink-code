// TestLink Open Source Project - http://testlink.sourceforge.net/ 
// This script is distributed under the GNU General Public License 2 or later. 
//
// $Id: testlink_library.js,v 1.37 2007/08/20 06:41:11 franciscom Exp $ 
//
// Javascript functions commonly used through the GUI
// This library is automatically loaded with inc_header.tpl
//
// DO NOT ADD FUNCTIONS FOR ONE USING
//
// 20070509 - franciscom - changes in tree_getPrintPreferences()
//                         to support new options (Contribution)
// 
// 20070220 - franciscom - changes in ET(), and tree_getPrintPreferences()
// 20070129 - franciscom - changes in SP() 
// 20070107 - franciscom - subtle bug deleteUser_onClick()
// 20061223 - franciscom - added open_show_notes_window()
// 20060603 - franciscom - added confirm_and_submit()
//
/*
  function: focusInputField

  args :
  
  returns: 

*/
function focusInputField(id,bSelect)
{
	var f = document.getElementById(id);
	if (f)
	{
		f.focus();
		if (bSelect)
			f.select();
	}
}


/*
  function: open_popup

  args :
  
  returns: 

*/
function open_popup(page) 
{
	window.open(page, "_blank", "left=350,top=50,screenX=350,screenY=50,fullscreen=no,resizable=yes,toolbar=no,status=no,menubar=no,scrollbars=yes,directories=no,location=no,width=400,height=650")
	return true;
}

// middle window (information, TC)
function open_top(page) 
{
	window.open(page, "_blank", "left=350,top=50,screenX=350,screenY=50,fullscreen=no,resizable=yes,toolbar=no,status=no,menubar=no,scrollbars=yes,directories=no,location=no,width=600,height=400")
	return true;
}


// test specification related functions
/*
  function: ST 
            Show Test case

  args :
  
  returns: 

*/
function ST(id,version)
{
	parent.workframe.location = fRoot+'/'+menuUrl+"?version_id="+version+"&level=testcase&id="+id+args;
}


/*
  function: STS 
            Show Test Suite

  args :
  
  returns: 

*/
function STS(id)
{
	parent.workframe.location = fRoot+'/'+menuUrl+"?level=testsuite&id="+id+args;
}


function SP()
{
  	parent.workframe.location = fRoot+menuUrl;
}


function EP(id)
{
  // get checkboxes status
	var pParams = tree_getPrintPreferences();
	parent.workframe.location = fRoot+menuUrl+"?edit=testproject&id="+id+args+"&"+pParams;
}

/*
  function: Edit Test Suite

  args :
  
  returns: 

  rev :
        20070218 - franciscom
*/
function ETS(id)
{
  // get checkboxes status
	var pParams = tree_getPrintPreferences();
	parent.workframe.location = fRoot+menuUrl+"?edit=testsuite&id="+id+args+"&"+pParams;
}


/*
  function: Edit Test case

  args :
  
  returns: 

*/
function ET(id,v)
{
  // get checkboxes status
	var pParams = tree_getPrintPreferences();
	parent.workframe.location = fRoot+menuUrl+"?version_id="+v+"&edit=testcase&id="+id+args;
}


/*
  function: Print Test Suite

  args :
  
  returns: 

*/
function PTS(id)
{
	var pParams = tree_getPrintPreferences();
	parent.workframe.location = fRoot+menuUrl+"?level=testsuite&id="+id+args+"&"+pParams;
}

/*
  function: Print Test Plan (PTP)
*/
function PTP(id)
{
	var pParams = tree_getPrintPreferences();
	parent.workframe.location = fRoot+menuUrl+"?level=testproject&id="+id+args+"&"+pParams;
}

function PT(id)
{
	parent.workframe.location = fRoot+menuUrl+"?level=tc&id="+id+args;
}


//==========================================
// Set DIV ID to hide
//==========================================
function my_hide_div(itm)
{
	if (!itm)
		return;

	itm.style.display = "none";
}

//==========================================
// Set DIV ID to show
//==========================================
function my_show_div(itm)
{
	if (!itm)
		return;

	itm.style.display = "";
}

// MHT: TODO move it to validate.js
// 20051007 - am - removed build name
function deleteBuild_onClick(build_id,msg)
{
  confirm_and_submit(msg,'deleteBuildForm',
                     'build_id',build_id,'do_action','do_delete');
}

/*
  function: deleteUser_onClick()

  args :
  
  returns: 

  rev :
        20070107 - franciscom - added fRoot
*/
function deleteUser_onClick(userID,userName)
{
	if (confirm(warning_delete_user+"\n(" + userName +")"))
		location = fRoot+"lib/usermanagement/usersview.php?operation=delete&user="+userID;
}

/**
 * Display a confirmation dlg before modifying roles
 *
 * @return bool return true if the user confirmed, false else
 *
 **/
function modifyRoles_warning()
{
	if (confirm(warning_modify_role))
		return true;

	return false;
}

/**
 * Function-Documentation
 *
 * @param string feature the feature, could be testplan or product
 **/
function changeFeature(feature)
{
	var tmp = document.getElementById('featureSel');
	if (!tmp)
		return;
	var fID = tmp.value;	
	if(fID)
		location = fRoot+"lib/usermanagement/usersassign.php?feature="+feature+"&featureID="+fID;
}

// 20070222 - changed height to solve BUGID 627
function openFileUploadWindow(id,tableName)
{
	window.open(fRoot+"lib/attachments/attachmentupload.php?id="+id+"&tableName="+tableName,
	            "FileUpload","width=510,height=300,resizable=yes,dependent=yes");
}


/*
  function: 

  args :  object id
  
  returns: 

*/
function deleteAttachment_onClick(id)
{
	if (confirm(warning_delete_attachment))
		window.open(fRoot+"lib/attachments/attachmentdelete.php?id="+id,"Delete","width=510,height=150,resizable=yes,dependent=yes");
}

function attachmentDlg_onUnload()
{
	if (attachmentDlg_bNoRefresh)
	{
		attachmentDlg_bNoRefresh = false;
		return;
	}
	try
	{
		if (attachmentDlg_refWindow == top.opener)
			top.opener.location = attachmentDlg_refLocation;		
	}
	catch(e)
	{}
	attachmentDlg_refWindow = null;
	attachmentDlg_refLocation = null;
}

function attachmentDlg_onLoad()
{
	attachmentDlg_refWindow = null;
	attachmentDlg_refLocation = null;
	try
	{
		attachmentDlg_refWindow = top.opener;
		attachmentDlg_refLocation = top.opener.location;
	}
	catch(e)
	{}
}

function attachmentDlg_onSubmit()
{
	attachmentDlg_bNoRefresh = true;
	
	return true;
}


/*
  function: confirm_and_submit

  args :
  
  returns: 

*/
function confirm_and_submit(msg,form_id,field_id,field_value,action_field_id,action_field_value)
{
	if (confirm(msg))
	{
		var f = document.getElementById(form_id);
		if (f)
		{
			var field = document.getElementById(field_id);
			if (field)
			{
				field.value = field_value;
			}	
			
			var field_a = document.getElementById(action_field_id);
			if (field_a)
			{
				field_a.value = action_field_value;
			}	
			
			f.submit();
		}
	}
	
}

/*
  function: 

  args :
  
  returns: 

  rev  :
         20070509 - franciscom - added 'author'
         20070218 - franciscom - added tcspec_refresh_on_action
                                 useful on test case specification edit NOT Printing
*/
function tree_getPrintPreferences()
{
	var params = [];
	var fields = ['header','summary','toc','body','passfail',
	              'tcspec_refresh_on_action','author'];

  for (var i= 0;i < fields.length;i++)
	{
		var v = tree_getCheckBox(fields[i]);
		if (v)
			params.push(v);
	}
	var f = document.getElementById('format');
	if(f)
		params.push("format="+f.value);
		
	params = params.join('&');
	
	return params;
}

function tree_getCheckBox(id)
{
	var	cb = document.getElementById('cb'+id);
	if (cb && cb.checked)
	{
		return id+'=y';
	}	
	return null;
}


function open_bug_add_window(exec_id)
{
	window.open(fRoot+"lib/execute/bug_add.php?exec_id="+exec_id,"bug_add","width=510,height=270,resizable=yes,dependent=yes");
}
function bug_dialog() 
{
	this.refWindow = null;
	this.refLocation = null;
	this.NoRefresh = false;
}

function dialog_onSubmit(odialog)
{
	odialog.NoRefresh = true;
	return true;
}

function dialog_onLoad(odialog)
{
	odialog.refWindow = null;
	odialog.refLocation = null;
	try
	{
		odialog.refWindow = top.opener;
		odialog.refLocation = top.opener.location;
	}
	catch(e)
	{}
}

function dialog_onUnload(odialog)
{
	if (odialog.NoRefresh)
	{
		odialog.NoRefresh = false;
		return;
	}
	try
	{
		if (odialog.refWindow == top.opener)
			top.opener.location = odialog.refLocation;		
	}
	catch(e)
	{}
	odialog.refWindow = null;
	odialog.refLocation = null;
}

function deleteBug_onClick(execution_id,bug_id,warning_msg)
{
	if (confirm(warning_msg))
	{
		window.open(fRoot+"lib/execute/bug_delete.php?exec_id="+execution_id+"&bug_id="+bug_id,
		            "Delete","width=510,height=150,resizable=yes,dependent=yes");
	}	
}

function planRemoveTC(warning_msg)
{
	var cbs = document.getElementsByTagName('input');
	var bRemoveTC = false;
	var len = cbs.length;
	for (var i = 0;i < len;i++)
	{
		var item = cbs[i];
		if (item.type == 'checkbox' && item.checked && item.name.substring(0,17) == "remove_checked_tc")
		{	
			bRemoveTC = true;
			break;
		}
	}
	if (bRemoveTC)
	{
		if (!confirm(warning_msg))
			return false;
	}
	
	return true;
}

/*
  function: open_show_notes_window

  args :
  
  returns: 

*/
function open_show_notes_window(exec_id)
{
	window.open(fRoot+"lib/execute/show_exec_notes.php?exec_id="+exec_id,
	            "execution_notes","width=510,height=270,resizable=yes,dependent=yes");
}

/*
  function: open_help_window

  args :
  
  returns: 

*/
function open_help_window(help_page,locale)
{
	window.open(fRoot+"lib/general/show_help.php?help="+help_page+"&locale="+locale,"_blank", "left=350,top=50,screenX=350,screenY=50,fullscreen=no,resizable=yes,toolbar=no,status=no,menubar=no,scrollbars=yes,directories=no,location=no,width=400,height=650")
}


/*
  function: open_help_window

  args :
  
  returns: 

*/
function deleteRole_onClick(role_id,msg)
{
	if (confirm(msg))
	{
		location = fRoot+"lib/usermanagement/rolesview.php?deleterole=1&id="+role_id;
  }
}

