// TestLink Open Source Project - http://testlink.sourceforge.net/ 
// This script is distributed under the GNU General Public License 2 or later. 
//
// $Id: testlink_library.js,v 1.17 2006/08/17 19:29:59 schlundus Exp $ 
//
// Javascript functions commonly used through the GUI
// This library is automatically loaded with inc_header.tpl
//
// DO NOT ADD FUNCTIONS FOR ONE USING
//
// 20060603 - franciscom - added confirm_and_submit()
//

// help popup
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
function ST(id,version)
{
	parent.workframe.location = fRoot+'/'+menuUrl+"?version_id="+version+"&level=testcase&id="+id+args;
}

function STS(id)
{
	parent.workframe.location = fRoot+'/'+menuUrl+"?level=testsuite&id="+id+args;
}

function SP()
{
	parent.workframe.location = fRoot+'/'+SP_html_help_file;
}

/*
function SC(id)
{
	parent.workframe.location = fRoot+'/'+menuUrl+"&level=category&id="+id;
}
*/

function EP(id)
{
	var pParams = tree_getPrintPreferences();
	parent.workframe.location = fRoot+menuUrl+"?edit=testproject&id="+id+args+"&"+pParams;
}

function ETS(id)
{
	var pParams = tree_getPrintPreferences();
	parent.workframe.location = fRoot+menuUrl+"?edit=testsuite&id="+id+args+"&"+pParams;
}

function ET(id)
{
	parent.workframe.location = fRoot+menuUrl+"?edit=testcase&id="+id+args;
}

function PTS(id)
{
	var pParams = tree_getPrintPreferences();
	parent.workframe.location = fRoot+menuUrl+"?level=testsuite&id="+id+args+"&"+pParams;
}
/*
function ECO(id)
{
	parent.workframe.location = fRoot+menuUrl+"?edit=component&data="+id+args;
}

function EC(id)
{
	parent.workframe.location = fRoot+menuUrl+"?edit=category&data="+id+args;
}
*/

function PTP(id)
{
	var pParams = tree_getPrintPreferences();
	parent.workframe.location = fRoot+menuUrl+"?level=testproject&id="+id+args+"&"+pParams;
}
/*
function PCO(id)
{
	parent.workframe.location = fRoot+menuUrl+"?level=component&data="+id+args;
}

function PC(id)
{
	parent.workframe.location = fRoot+menuUrl+"?level=category&data="+id+args;
}
*/

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
function deleteBuild_onClick(buildID,msg)
{
  confirm_and_submit(msg,
                     'deleteBuildForm','buildID',
                     buildID);
}

function deleteUser_onClick(userID)
{
	if (confirm(warning_delete_user))
		location = "lib/usermanagement/usersview.php?delete=1&user="+userID;
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

function openFileUploadWindow(id,tableName)
{
	window.open(fRoot+"lib/attachments/attachmentupload.php?id="+id+"&tableName="+tableName,"FileUpload","width=510,height=270,resizable=yes,dependent=yes");
}

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


function confirm_and_submit(msg,form_id,field_id,field_value)
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
			f.submit();
		}
	}
	
}

function tree_getPrintPreferences()
{
	var params = [];
	var fields = ['header','summary','toc','body'];
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
		return id+'=y';
	return null;
}