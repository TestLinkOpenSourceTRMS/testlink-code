// TestLink Open Source Project - http://testlink.sourceforge.net/ 
// This script is distributed under the GNU General Public License 2 or later. 
//
// $Id: testlink_library.js,v 1.12 2006/05/16 19:35:40 schlundus Exp $ 
//
// Javascript functions commonly used through the GUI
// This library is automatically loaded with inc_header.tpl
//
// DO NOT ADD FUNCTIONS FOR ONE USING
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
	parent.workframe.location = fRoot+'/'+menuUrl+"?version="+version+"&level=testcase&id="+id+args;
}

function STS(id)
{
	parent.workframe.location = fRoot+'/'+menuUrl+"?level=component&id="+id+args;
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
	parent.workframe.location = fRoot+menuUrl+"?edit=testproject&data="+id+args;
}

function ETS(id)
{
	parent.workframe.location = fRoot+menuUrl+"?edit=testsuite&data="+id+args;
}

function ET(id)
{
	parent.workframe.location = fRoot+menuUrl+"?edit=testcase&data="+id+args;
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
	parent.workframe.location = fRoot+menuUrl+"?level=root&data="+id+args;
}

function PCO(id)
{
	parent.workframe.location = fRoot+menuUrl+"?level=component&data="+id+args;
}

function PC(id)
{
	parent.workframe.location = fRoot+menuUrl+"?level=category&data="+id+args;
}


function PT(id)
{
	parent.workframe.location = fRoot+menuUrl+"?level=tc&data="+id+args;
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
function deleteBuild_onClick(buildID)
{
	if (confirm(warning_delete_build))
	{
		var f = document.getElementById('deleteBuildForm');
		if (f)
		{
			var field = document.getElementById('buildID');
			if (field)
				field.value = buildID;
			f.submit();
		}
	}
	
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