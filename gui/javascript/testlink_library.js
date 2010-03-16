// TestLink Open Source Project - http://testlink.sourceforge.net/
// This script is distributed under the GNU General Public License 2 or later.
//
// $Id: testlink_library.js,v 1.97 2010/03/16 13:17:17 asimon83 Exp $
//
// Javascript functions commonly used through the GUI
// Rule: DO NOT ADD FUNCTIONS FOR ONE USING
//
// @used This library is automatically loaded with inc_header.tpl
//
//                               
// ----- Development Notes --------------------------------------------------------------
//
// @global variables:
// 	fRoot
// 	menuUrl
// 	args
//
// value to this variables is assigned using different smarty templates,
// like inc_head.tpl
//
// Attention:
// window.open() - on Firefox is window name contains blank nothing happens (no good)
//                 on I.E. => generates a bug - BE CAREFUL
//
// ------ Revisions ---------------------------------------------------------------------
//
// 20100301 - asimon - added openLinkedReqWindow() and openLinkedReqSpecWindow()
// 20100223 - asimon - added PL() for BUGID 3049
// 20100216 - asimon - added triggerBuildChooser() and triggerAssignedBox() for BUGID 2455, BUGID 3026
// 20100212 - eloff - BUGID 3103 - remove js-timeout alert in favor of BUGID 3088
// 20100131 - franciscom - BUGID 3118: Help files are not getting opened when selected in the dropdown 
// 20090906 - franciscom - added openTestSuiteWindow()
// 20090821 - havlatm - added support for session timeout
// 20090530 - franciscom - openExecEditWindow()
// 20090419 - franciscom - BUGID 2364 - added std_dialog()
//                         added some comments to explain how a bug has been solved
//
// 20090329 - franciscom - openTCaseWindow(), added second argument
// 20081220 - franciscom - toggleInput()
// 20080724 - havlatm - bug 1638, 1639
// 20080322 - franciscom - openExecNotesWindow()
// 20080118 - franciscom - showHideByClass()
// 20070930 - franciscom - REQ - BUGID 1078 - openTCaseWindow()
// 20070509 - franciscom - changes in tree_getPrintPreferences()
//                         to support new options (Contribution)


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
function: show help <div> tag with absolute position or right site ow window
arg: localized text of help
returns: N/A 
*/
function show_help(text)
{
	// set workframe window for navigator pane
	if(window.name == "treeframe"){
		var mywindows = window.parent.frames["workframe"].document;		
	} else {
		var mywindows = window.document;
	}

	myElement = mywindows.getElementById("tlhelp");
	if(myElement == null)
	{
		
  		var mybody = mywindows.getElementsByTagName("body").item(0);
		var newdiv = mywindows.createElement('div');
		newdiv.setAttribute('id', 'tlhelp');
		newdiv.setAttribute('onclick', 'javascript: close_help()');
		mybody.appendChild(newdiv);

		myElement = mywindows.getElementById("tlhelp");
	}

	myElement.innerHTML = text;
}

function close_help() 
{
	// set workframe window for navigator pane
	if(window.name == "treeframe"){
		var mywindows = window.parent.frames["workframe"].document;		
	} else {
		var mywindows = window.document;
	}

	var d = mywindows.getElementsByTagName("body").item(0);
	var olddiv = mywindows.getElementById('tlhelp');
	d.removeChild(olddiv);
}



/*
  function: open_popup

  args :

  returns:

*/
function open_popup(page)
{
  var windowCfg="left=350,top=50,screenX=350,screenY=50,fullscreen=no,resizable=yes," + 
                "toolbar=no,status=no,menubar=no,scrollbars=yes,directories=no,location=no," +
                "width=400,height=650";
	window.open(page, "_blank",windowCfg);
	return true;
}

// middle window (information, TC)
function open_top(page)
{
  var windowCfg="left=350,top=50,screenX=350,screenY=50,fullscreen=no,resizable=yes," + 
                "toolbar=no,status=no,menubar=no,scrollbars=yes,directories=no,location=no," +
                "width=600,height=400";
  
	window.open(page, "_blank", windowCfg);
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
  var _FUNCTION_NAME_='ST';
  var action_url=fRoot+menuUrl+"?version_id="+version+"&level=testcase&id="+id+args;
	// alert(_FUNCTION_NAME_ + " " +action_url);
	parent.workframe.location = action_url;
}


/*
  function: STS
            Show Test Suite

  args :

  returns:

*/
function STS(id)
{
	var action_url = fRoot+'/'+menuUrl+"?level=testsuite&id="+id+args;
	parent.workframe.location = action_url;
}


/*
  function: SP

  args :

  returns:

*/
function SP()
{
    var action_url = fRoot+menuUrl;
  	parent.workframe.location = action_url;
}

/*
  function: EP
            printing of Test Specification

  args :

  returns:

*/
function EP(id)
{
	var _FUNCTION_NAME_="EP";

	// get checkboxes status
	var pParams = tree_getPrintPreferences();
	var action_url = fRoot+menuUrl+"?print_scope=test_specification" + "&edit=testproject" +
	                 "&level=testproject&id="+id+args+"&"+pParams;

	//alert(_FUNCTION_NAME_ + " " +action_url);
	parent.workframe.location = action_url;
}

/*
function: PL
          printing of Test Plan for BUGID 3049
args: id
*/
function PL(id)
{
	var _FUNCTION_NAME_="PL";
	var action_url = fRoot + 'lib/testcases/archiveData.php?edit=testplan&level=testplan&id=' + id;
	parent.workframe.location = action_url;
}

/*
  function: Edit Test Suite or launch print

  args :

  returns:

  rev :
        20070218 - franciscom
*/
function ETS(id)
{
  // get checkboxes status
	var _FUNCTION_NAME_="ETS";
	var pParams = tree_getPrintPreferences();
	var action_url=fRoot+menuUrl+"?print_scope=test_specification" +
	               "&edit=testsuite&level=testsuite&id="+id+args+"&"+pParams;

	// alert(_FUNCTION_NAME_ + " " +action_url);
	parent.workframe.location = action_url;

}

/*
  function: Edit Test case

  args :

  returns:

*/
function ET(id,v)
{
  // get checkboxes status
 	var _FUNCTION_NAME_="ET";
  var pParams = tree_getPrintPreferences();
	var my_location = fRoot+menuUrl+"?version_id="+v+"&edit=testcase&id="+id+args;
	// alert(_FUNCTION_NAME_ + " " +my_location);
  
	parent.workframe.location = my_location;
}


/* Generate doc: a selected Test Suite from Test Specification */
function TPROJECT_PTS(id)
{
	var pParams = tree_getPrintPreferences();
	parent.workframe.location = fRoot+menuUrl+"?type=testspec&level=testsuite&id="+id+args+"&"+pParams;
}

/* Generate doc: all Test Specification */
function TPROJECT_PTP(id)
{
	var pParams = tree_getPrintPreferences();
	parent.workframe.location = fRoot+menuUrl+"?type=testspec&level=testproject&id="+id+args+"&"+pParams;
}

/* Generate doc: a selected Test Tase from Test Specification */
function TPROJECT_PTC(id)
{
	parent.workframe.location = fRoot+menuUrl+"?type=testspec&level=tc&id="+id+args;
}

/*
  function: TPLAN_PTS
            Test PLAN Print Test Suite

  args :

  returns:

*/
function TPLAN_PTS(id)
{
	var pParams = tree_getPrintPreferences();
	parent.workframe.location = fRoot+menuUrl+"?level=testsuite&id="+id+args+"&"+pParams;
}

/*
  function: TPLAN_PTP
            Test PLAN Print Test Plan
*/
function TPLAN_PTP(id)
{
	var pParams = tree_getPrintPreferences();
	var my_location = fRoot+menuUrl+"?level=testproject&id="+id+args+"&"+pParams;
	parent.workframe.location =my_location;
}


/*
  function: TPLAN_PTC
            Test PLAN Print Test Case
*/
function TPLAN_PTC(id)
{
	var my_location = fRoot+menuUrl+"?level=tc&id="+id+args;
	parent.workframe.location = my_location;
}

function showOrHideElement(oid,hide)
{
	  var obj = document.getElementById(oid);
	  var displayValue = "";
    
    if (!obj)
    {
    	return;
    }
  	if(hide)
  	{
  		displayValue = "none";
    }
  	obj.style.display = displayValue;
}

/**
 * Display a confirmation dlg before modifying roles
 *
 * @return bool return true if the user confirmed, false else
 *
 **/
function modifyRoles_warning()
{
  var ret=false;
	if (confirm(warning_modify_role))
	{
		ret=true;
  } 
	return ret;
}

/**
 * 
 * @param string feature the feature, could be testplan or product
 **/
function changeFeature(feature)
{
	var tmp = document.getElementById('featureSel');
	var fID = '';
	if (!tmp)
	{
		return;
	}
	fID = tmp.value;
	if(fID)
	{
		location = fRoot+"lib/usermanagement/usersAssign.php?featureType="+feature+"&featureID="+fID;
	}	
}

function openFileUploadWindow(id,tableName)
{
  var windowCfg="width=510,height=300,resizable=yes,dependent=yes";
	window.open(fRoot+"lib/attachments/attachmentupload.php?id="+id+"&tableName="+tableName,
	            "FileUpload",windowCfg);
}


/*
  function:

  args :  object id

  returns:

*/
function deleteAttachment_onClick(btn,txt,id)
{
	if (btn == 'yes')
	{
	  var windowCfg="width=510,height=150,resizable=yes,dependent=yes";
		window.open(fRoot+"lib/attachments/attachmentdelete.php?id="+id,
		            "Delete",windowCfg);
	}	
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
		{
			top.opener.location = attachmentDlg_refLocation;
		}	
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
		if (attachmentDlg_refWindow.attachment_reloadOnCancelURL)
		{
			attachmentDlg_refLocation = attachmentDlg_refWindow.attachment_reloadOnCancelURL;
		}	
	}
	catch(e)
	{}
}

function attachmentDlg_onSubmit(allowEmptyTitle)
{
	var bSuccess = true;
	attachmentDlg_bNoRefresh = true;

	if (!allowEmptyTitle)
	{
		var titleField = document.getElementById('title');
		if (isWhitespace(titleField.value))
		{
			var aForm = document.getElementById('aForm');
			alert_message(alert_box_title,warning_empty_title);
		    selectField(aForm, 'title');
		    bSuccess = false;
		}
	}
	return bSuccess;
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
  function: tree_getPrintPreferences

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
	var fields = ['header','summary','toc','body','passfail', 'cfields','testplan', 'metrics', 
	              'tcspec_refresh_on_action','author','requirement','keyword'];

  for (var idx= 0;idx < fields.length;idx++)
	{
		var v = tree_getCheckBox(fields[idx]);
		if (v)
		{
			params.push(v);
		}	
	}
	var f = document.getElementById('format');
	if(f)
	{
		params.push("format="+f.value);
  }
	params = params.join('&');

	return params;
}


// TODO understand where is used - 20090715 - franciscom
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
	window.open(fRoot+"lib/execute/bugAdd.php?exec_id="+exec_id,"bug_add",
	            "width=510,height=270,resizable=yes,dependent=yes");
}

function bug_dialog()
{
	this.refWindow = null;
	this.refLocation = null;
	this.NoRefresh = false;
}

function std_dialog(additional)
{
  // alert('std_dialog() - called');
	this.refWindow = null;
	this.refLocation = null;
	this.refAdditional=additional;
	this.NoRefresh = false;
}


function dialog_onSubmit(odialog)
{
  // In this way we do not do refresh.
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
		if(odialog.refAdditional != undefined)
		{
		   odialog.refLocation += odialog.refAdditional;
		} 
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
		{
			top.opener.location = odialog.refLocation;
		}	
	}
	catch(e)
	{}
	odialog.refWindow = null;
	odialog.refLocation = null;
}

/**
 * Calls the bug delete page when the 'yes' button in the delete confirmation dialog
 * was clicked
 * 
 * @param btn string id of the button clicked
 * @param text string not used
 * @param combinedBugID string like <executionID-bugID>
 */
function deleteBug(btn,text,combinedBugID)
{
	if (btn != 'yes')
		return;
	var idx = combinedBugID.indexOf('-');
	if (idx < 0)
		return;

	var executionID = combinedBugID.substr(0,idx)
	var bugID = combinedBugID.substr(idx+1);
	window.open(fRoot+"lib/execute/bugDelete.php?exec_id="+executionID+"&bug_id="+bugID,
		            "Delete","width=510,height=150,resizable=yes,dependent=yes");
}

// seems is not used => do more checks and remove
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
  function: open_help_window

  args :

  returns:

*/
function open_help_window(help_page,locale)
{
    var windowCfg='';
    windowCfg="left=350,top=50,screenX=350,screenY=50,fullscreen=no,resizable=yes," + 
               "toolbar=no,status=no,menubar=no,scrollbars=yes,directories=no," + 
               "location=no,width=400,height=650";
    window.open(fRoot+"lib/general/show_help.php?help="+help_page+"&locale="+locale,"_blank",windowCfg);
}


/*
  function: openTCaseWindow

  args: tcase_id: test case id
        tcversion_id: test case version id
        show_mode: string used on testcase.show() to manage refresh 
                   logic of frames when Edit Test case page is closed.
                   This argument was added to allow automatic referesh
                   of frames when user uses the feature that allows 
                   edit a test case while execution it.

  returns:

  rev :
       20090715 - franciscom - added documentation
       20070930 - franciscom - REQ - BUGID 1078

*/
function openTCaseWindow(tcase_id,tcversion_id,show_mode)
{
	  //@TODO schlundus, what is show_mode? not used in archiveData.php
	  //You are right: problem fixed see documentation added on header (franciscom)
	  // 
    var windowCfg='';
	  var feature_url = "lib/testcases/archiveData.php";
	  feature_url +="?allow_edit=0&show_mode="+show_mode+"&edit=testcase&id="+
                    tcase_id+"&tcversion_id="+tcversion_id;
    
    // second parameter(window name) with spaces caused bug on IE
	  windowCfg="width=510,height=300,resizable=yes,scrollbars=yes,dependent=yes";
	  window.open(fRoot+feature_url,"TestCaseSpec",windowCfg);
}


/**
 * open a requirement in a popup window
 * 
 * @param req_id Requirement ID
 * @param anchor string with anchor name
 */
function openLinkedReqWindow(req_id, anchor)
{
	if (anchor == null) {
		anchor = '';
	} else {
		anchor = '#' + anchor;
	}
	
	var windowCfg='';
	var feature_url = "lib/requirements/reqView.php";
	feature_url += "?showReqSpecTitle=1&requirement_id=" + req_id + anchor;

	windowCfg="width=800,height=400,resizable=yes,scrollbars=yes,dependent=yes";
	window.open(fRoot+feature_url,"Requirement",windowCfg);
}


/**
 * open a req spec in a popup window
 * 
 * @param reqspec_id Requirement Specification ID
 * @param anchor string with anchor name
 */
function openLinkedReqSpecWindow(reqspec_id, anchor)
{
	if (anchor == null) {
		anchor = '';
	} else {
		anchor = '#' + anchor;
	}
	
	var windowCfg='';
	var feature_url = "lib/requirements/reqSpecView.php";
	feature_url += "?req_spec_id=" + reqspec_id + anchor;

	windowCfg="width=800,height=400,resizable=yes,scrollbars=yes,dependent=yes";
	window.open(fRoot+feature_url,"Requirement Specification",windowCfg);
}


/*
  function: TPROJECT_REQ_SPEC_MGMT
            launcher for Testproject REQuirement SPECifications ManaGeMenT

  args:

  returns:

*/
function TPROJECT_REQ_SPEC_MGMT(id)
{
	var _FUNCTION_NAME_="TPROJECT_REQ_SPEC_MGMT";
	var pParams = tree_getPrintPreferences();
	var action_url = fRoot+"lib/project/project_req_spec_mgmt.php"+"?id="+id+args+"&"+pParams;

  //alert(_FUNCTION_NAME_ + " " +action_url);
	parent.workframe.location = action_url;

}


/*
  function: REQ_SPEC_MGMT
            launcher for REQuirement SPECification ManaGeMenT

  args:

  returns:

*/
function REQ_SPEC_MGMT(id)
{
	var _FUNCTION_NAME_="REQ_SPEC_MGMT";
	var pParams = tree_getPrintPreferences();
  var action_url = fRoot+req_spec_manager_url+"?item=req_spec&req_spec_id="+id+args+"&"+pParams;
  
  // alert(_FUNCTION_NAME_ + " " +action_url);
	parent.workframe.location = action_url;
}

/*
  function: REQ_MGMT
            launcher for REQuirement ManaGeMenT

  args:

  returns:

*/
function REQ_MGMT(id)
{
	var _FUNCTION_NAME_="REQ_MGMT";
	var pParams = tree_getPrintPreferences();
	var action_url = fRoot+req_manager_url+"?item=requirement&requirement_id="+id+args+"&"+pParams;

  //alert(_FUNCTION_NAME_ + " " +action_url);
	parent.workframe.location = action_url;

}


/*
  function: show_hide_column

  args:

  returns:

*/
function show_hide_column(table_id,col_no)
{
	var tbl  = document.getElementById(table_id);
	var rows = tbl.getElementsByTagName('tr');
	
	for (var row=0; row<rows.length;row++)
	{
		if(row == 0)
			cellTag = 'th';
		else
			cellTag = 'td';
  	
	  	var cels = rows[row].getElementsByTagName(cellTag)
	    if(cels[col_no].style.display == 'none')
	        cels[col_no].style.display='block';
	    else
	       cels[col_no].style.display='none';
    }
}


function showHideByClass(tagName,className)
{
    var objects = document.getElementsByTagName(tagName);
    for (var idx=0; idx<objects.length; idx++)
    {
    	var myClassName = objects[idx].className;
    	if( myClassName == className)
    	{
            if(objects[idx].style.display == 'none')
            {
                objects[idx].style.display='';
            }
            else
            {
               objects[idx].style.display='none';
            }
    	}
    }
}

function showCal(id,dateField)
{
	var x = document.getElementById(id);
	x.innerHTML = '';
	var dp = new Ext.DatePicker({ renderTo:id, format:"m/d/y", idField:dateField });
	//get the element
	var el = document.getElementById(dateField);
	if(el.value != "")
	{
		selectedDate = new Date(el.value);
		if (isNaN(selectedDate.getTime()))
		{
			 selectedDate = '';
			 el.value = '';
		}
		else
			dp.setValue(selectedDate);
	}
	dp.addListener("select", onSelect);
}

function onSelect(datePicker,date)
{
	var dt = new Date(date);
	document.getElementById(datePicker.idField).value = dt.format("m/d/Y");
	datePicker.destroy();
}

function showEventHistoryFor(objectID,objectType)
{
	var f = document.getElementById('eventhistory');
	if (!f)
	{
		f = document.createElement("form");
		if (!f)
		{
			return;
		}
		var b = document.getElementsByTagName('body')[0];
		if (!b)
		{
			return;
		}
		b.appendChild(f);
		f.style.display = "none";
		f.id = "eventhistory";
		f.target = "_blank";
		f.method = "POST";
		var i = document.createElement("input");
		i.type = "hidden";
		i.name = "object_id";
		i.id = "object_id";
		f.appendChild(i);
		i = document.createElement("input");
		i.type = "hidden";
		i.name = "object_type";
		i.id = "object_type";
		f.appendChild(i);
		f.action = fRoot+"lib/events/eventviewer.php";
	}
	if (f)
	{
		f.object_id.value = objectID;
		f.object_type.value = objectType;
		f.submit();
	}
}

/*
  function: 

  args :
  
  returns: 

*/
function openReqWindow(tcase_id)
{ 
  var windowCfg='';                       
	var feature_url = "lib/requirements/reqTcAssign.php";
	
	feature_url +="?edit=testcase&showCloseButton=1&id="+tcase_id;

	// second parameter(window name) with spaces generate bug on IE
	windowCfg="width=510,height=300,resizable=yes,scrollbars=yes,dependent=yes";
	window.open(fRoot+feature_url,"TestCase_Requirement_link",windowCfg);
}

/*
  function: toggleInput

  args: oid - object id
  
  returns: 

  rev: 20090716 - franciscom - fixed refactored that does not work
*/
function toggleInput(oid)
{
	var elem = document.getElementById(oid);
	if (elem)
	{
    elem.value = (elem.value == 1) ? 0 : 1;
  }  
}


/**
 * Show User feedback
 * @param boolean success 	[0] = error
 * @param string message 	localized text
 */
function showFeedback(success, msg_text)
{
	var base = document.getElementById('user_feedback');
	if (base)
	{
    	if (success)
    	{
    		base.className = 'user_feedback';
    	} else {
	    	base.className = 'error';
	    }
		base.innerHTML = msg_text;
	}
/*	else
	{
		// add div element 'user_feedback' if don't exists
		// havlatm: I don't know why doesn't work :-(
		var oNewDiv = document.createElement("div");
		var dim = document.body.insertBefore(oNewDiv, document.getElementsByTagName('div')[0]);
		if (dim)
		{
    		dim.setAttribute('id','user_feedback');
    		showFeedback(success, msg_text);
		} 
	}
*/	 
}


/*
  function: openExecEditWindow

  args :

  returns:

*/
function openExecEditWindow(exec_id,tcversion_id,tplan_id,tproject_id)
{
	var target_url = "lib/execute/editExecution.php";
	var windowCfg = "width=510,height=270,resizable=yes,dependent=yes,scrollbars=yes";
	window.open(fRoot+target_url+"?exec_id="+exec_id+"&tcversion_id="+tcversion_id+"&tplan_id="+tplan_id+"&tproject_id="+tproject_id,
	            "execution_notes",windowCfg);
}

/* 
 * use to display test suite content (read only mode) on execution feature
 * on user request
 */
function openTestSuiteWindow(tsuite_id)
{ 
	var windowCfg = '';                       
	var feature_url = "lib/testcases/archiveData.php";

	feature_url +="?show_mode=readonly&print_scope=test_specification&edit=testsuite&level=testsuite&id="+tsuite_id;

	// second parameter(window name) with spaces generate bug on IE
	windowCfg = "width=510,height=300,resizable=yes,scrollbars=yes,dependent=yes";
	window.open(fRoot+feature_url,"TestSuite",windowCfg);
}

/* 
 * use to display documentation included on test link distribution
 * 20100131 - franciscom - moved here to solve BUGID 3118: Help files are not getting 
 *                         opened when selected in the dropdown
 */
function get_docs(name, server_name)
{
  if (name != 'leer') {
      var w = window.open();
      w.location = server_name + '/docs/' + name;
  }
}

/**
 * used to disable the build chooser field (and make it invisible) if it should not be used
 * (in case of some filter settings)
 * (testcase execution & testcase execution assignment, BUGID 2455, BUGID 3026)
 * 
 * @author asimon
 * @param build_id_combo box in which the build is chosen
 * @param filter_method_combo box in which the filter method is chosen
 * @param specific_build_value value for which the box shall be disabled
 */
function triggerBuildChooser(deactivatable_id, filter_method_combo_id, specific_build_value) 
{
	var deactivatable = document.getElementById(deactivatable_id);
	var filter_method_combo = document.getElementById(filter_method_combo_id);
	var index = filter_method_combo.options.selectedIndex;  
	deactivatable.style.visibility = "hidden";
	
	if(filter_method_combo[index].value == specific_build_value) 
	{
		deactivatable.style.visibility = "visible";
	} 
}

/**
 * used to disable the "include unassigned testcases" checkbox when it should not be used
 * (testcase execution & testcase execution assignment, BUGID 2455, BUGID 3026)
 * 
 * @author asimon
 * @param filter_assigned_to combobox in which assignment is chosen
 * @param include_unassigned checkbox for including unassigned testcases
 * @param str_option_any string value anybody
 * @param str_option_none string value nobody
 * @param str_option_somebody string value somebody
 */
function triggerAssignedBox(filter_assigned_to_id, include_unassigned_id,
							str_option_any, str_option_none, str_option_somebody) 
{
	var filter_assigned_to = document.getElementById(filter_assigned_to_id);
	var include_unassigned = document.getElementById(include_unassigned_id);
	var index = filter_assigned_to.options.selectedIndex;
	var choice = filter_assigned_to.options[index].label;
	include_unassigned.disabled = false;

	if (choice == str_option_any || choice == str_option_none || choice == str_option_somebody) 
	{
		include_unassigned.disabled = true;
		include_unassigned.checked = false;
	} 
}

/**
 * disable unneeded filters in the filter method combo box
 * (testcase execution & testcase execution assignment, BUGID 2455, BUGID 3026)
 * 
 * @author asimon
 * @param filter_method_combo the box which shall be disabled
 * @param value2select the string which shall be selected in the box before disabling it
 */
function disableUnneededFilters(filter_method_combo_id, value2select) {
	filter_method_combo = document.getElementById(filter_method_combo_id);
	var length = filter_method_combo.options.length;
	
	for (var index = 0; index < length; index ++) {
		if (filter_method_combo.options[index].value == value2select) {
			filter_method_combo.options.selectedIndex = index;
		}
	}
	
	filter_method_combo.disabled = true;
}
