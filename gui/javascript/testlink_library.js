// TestLink Open Source Project - http://testlink.sourceforge.net/
// This script is distributed under the GNU General Public License 2 or later.
//
// @filesource testlink_library.js
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
//  fRoot
//  menuUrl
//  args
//
// value to this variables is assigned using different smarty templates,
// like inc_head.tpl
//
// Attention:
// window.open() - on Firefox is window name contains blank nothing happens (no good)
//                 on I.E. => generates a bug - BE CAREFUL
//
// @internal revisions
// @since 1.9.13


/*
  function: focusInputField

  args :

  returns:

*/
function focusInputField(id,selectIt)
{
  var f = document.getElementById(id);
  if (f)
  {
    f.focus();
    if(selectIt)
    {
      f.select();
    } 
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

  var myElement = mywindows.getElementById("tlhelp");
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

// test specification related functions
/*
  function: ST
            Show Test case

  args :

  returns:

  IMPORTANT NOTICE: see Development Notes on TOP of this document for info regarding args

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
  var _FUNCTION_NAME_='STS';
  var action_url = fRoot+'/'+menuUrl+"?level=testsuite&id="+id+args;
  // alert(args);
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

/**
 *  EXecution DaShboard (EXDS)
 */
function EXDS()
{
  var action_url = fRoot+'lib/execute/execDashboard.php';
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
  // menuUrl 99% => archiveData.php
  var _FUNCTION_NAME_="EP";

  // get checkboxes status
  var pParams = tree_getPrintPreferences();
  var action_url = fRoot+menuUrl+"?print_scope=test_specification" + "&edit=testproject" +
                   "&level=testproject&containerType=testproject&id="+id+args+"&"+pParams;

  // alert(_FUNCTION_NAME_ + " " +action_url);
                 
  parent.workframe.location = action_url;
}

/*
  function: Edit Test Suite or launch print

  args :

  returns:

  rev :
*/
function ETS(id)
{
  // menuUrl 99% => archiveData.php

  // get checkboxes status
  var _FUNCTION_NAME_="ETS";
  var pParams = tree_getPrintPreferences();
  var action_url=fRoot+menuUrl+"?print_scope=test_specification" +
                 "&edit=testsuite&level=testsuite&containerType=testsuite&id="+id+args+"&"+pParams;

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


/* Generate doc: all Req Specs, complete project */
function TPROJECT_PTP_RS(id)
{
  var pParams = tree_getPrintPreferences();
  parent.workframe.location = fRoot+menuUrl+"?type=reqspec&level=testproject&id="+id+args+"&"+pParams;
}


/* Generate doc: one Req Spec (with children) */
function TPROJECT_PRS(id)
{
  var pParams = tree_getPrintPreferences();
  parent.workframe.location = fRoot+menuUrl+"?type=reqspec&level=reqspec&id="+id+args+"&"+pParams;
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

  ATTENTION:
  this work in symbiosis with init_checkboxes() - printDocOptions.php 
  Symbiosis (from Ancient Greek σύν "together" and βίωσις "living")

*/
function tree_getPrintPreferences()
{
  var params = [];
  var fields = ['header','summary','toc','body','passfail', 'cfields','testplan', 'metrics', 
                'author','requirement','keyword','notes','assigned_to_me',
                'req_spec_scope','req_spec_author','req_spec_overwritten_count_reqs',
                'req_spec_type','req_spec_cf','req_scope','req_author','req_status',
                'req_type','req_cf','req_relations','req_linked_tcs','req_coverage', 
                'headerNumbering','displayVersion','build_cfields','step_exec_notes','step_exec_status'];

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

  var bx = document.getElementById('build_id');
  if(bx)
  {
    params.push("build_id="+bx.value);
  }

  var bx = document.getElementById('with_user_assignment');
  if(bx)
  {
    var vv = 0;
    if(bx.checked)
    {
      vv = 1;
    }  
    params.push("with_user_assignment=" + vv);
  }

  params = params.join('&');  // from array to string

  return params;
}


/**
 * @used-by tree_getPrintPreferences
 */
function tree_getCheckBox(id)
{
  var cb = document.getElementById('cb'+id);
  if (cb && cb.checked)
  {
    return id+'=y';
  }
  return null;
}

/**
 *
 */
function open_bug_add_window(tproject_id,tplan_id,tcversion_id,exec_id,tcstep_id,user_action)
{
  l2l = "lib/execute/bugAdd.php?user_action=" + user_action + 
        "&tcversion_id="+tcversion_id +"&tproject_id=" + tproject_id + 
        "&tplan_id=" + tplan_id + "&exec_id="+exec_id + "&tcstep_id="+tcstep_id;

  switch(user_action)  
  {
    case 'create':
      wh = "width=700,height=400";
    break;

    default:
      wh = "width=510,height=400";
    break;
  }            
  
  window.open(fRoot+l2l,"bug_add",wh+",resizable=yes,dependent=yes");
}

/**
 *
 */
function open_bug_note_add_window(bug_id,tproject_id,tcversion_id,exec_id,user_action)
{
  link2launch = "lib/execute/bugAdd.php?user_action=" + user_action + "&tcversion_id="+tcversion_id +
                "&tproject_id=" + tproject_id + "&exec_id="+exec_id + "&bug_id=" + bug_id;
                
  window.open(fRoot+link2launch,"bug_add_note","width=510,height=270,resizable=yes,dependent=yes");
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
 * @param combinedBugID string like <executionID-tcStepID-bugID>
 */
function deleteBug(btn,text,combinedBugID)
{
  var idx;
  var executionID;
  var tcStepID;
  var bugID;
  var target;
  
  if (btn != 'yes')
  {
    return;
  }
  
  idx = combinedBugID.indexOf('-');
  if (idx < 0)
  {
    return;
  }
  
  executionID = combinedBugID.substr(0,idx);
  
  target = combinedBugID.substr(idx+1);
  idx = target.indexOf('-');
  tcStepID = target.substr(0,idx)

  // TICKET 4814: bug deletion may fails if bugID string contains special characters ('#', '&' , ...)
  // bugID string may contain special characters : 
  // must escape it to get correct bugID value in bugDelete.php
  bugID = escape(target.substr(idx+1));
  
  window.open(fRoot+"lib/execute/bugDelete.php?exec_id="+executionID+"&tcstep_id="+tcStepID+"&bug_id="+bugID,
                "DeleteBug","width=510,height=150,resizable=yes,dependent=yes");
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

/**
 * Open the overview over all Test Cases assigned to a user in a popup window.
 * @author Andreas Simon
 * @param user_id
 * @param build_id
 * @param tplan_id
 */
function openAssignmentOverviewWindow(user_id, build_id, tplan_id) {
  var url = "lib/testcases/tcAssignedToUser.php";
  url += "?user_id=" + user_id + "&build_id=" + build_id + "&tplan_id=" + tplan_id;

  // 20101008 - asimon - BUGID 3311
  var width = getCookie("AssignmentOverviewWidth");
  var height = getCookie("AssignmentOverviewHeight");

  if (width == null)
  {
    var width = "800";
  }

  if (height == null)
  {
    var height = "600";
  }

  var windowCfg = "width="+width+",height="+height+",resizable=yes,scrollbars=yes,dependent=yes";
  window.open(fRoot+url, '_blank', windowCfg);
}


/**
 * Open testcase description in a popup window.
 * @author Andreas Simon
 * @param tc_id
 */
function openTCEditWindow(tcase_id,tcversion_id) 
{
  var url = "lib/testcases/archiveData.php?edit=testcase&id=" + tcase_id + "&tcversion_id=" + tcversion_id;
  var width = getCookie("TCEditPopupWidth");
  var height = getCookie("TCEditPopupHeight");
  
  if (width == null)
  {
    var width = "800";
  }
  
  if (height == null)
  {
    var height = "600";
  }
  
  var windowCfg = "width="+width+",height="+height+",resizable=yes,scrollbars=yes,dependent=yes";
  window.open(fRoot+url, '_blank', windowCfg);
}


/**
 * Open testcase for execution in a popup window.
 * @author Andreas Simon
 * @param tc_id
 * @param tcversion_id
 * @param build_id
 * @param tplan_id
 * @param platform_id
 */
function openExecutionWindow(tc_id, tcversion_id, build_id, tplan_id, platform_id, whoiam) 
{
  var url = "lib/execute/execSetResults.php?" + "version_id=" + tcversion_id +
            "&level=testcase&id=" + tc_id + "&tplan_id=" + tplan_id +
            "&setting_build=" + build_id + "&setting_platform=" + platform_id +
            "&caller=" + whoiam;
  
  var width = getCookie("TCExecPopupWidth");
  var height = getCookie("TCExecPopupHeight");
  
  if (width == null)
  {
    var width = "800";
  }
  
  if (height == null)
  {
    var height = "600";
  }
  
  var windowCfg = "width="+width+",height="+height+",resizable=yes,scrollbars=yes,dependent=yes";
  window.open(fRoot+url, '_blank', windowCfg);
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

*/
function openTCaseWindow(tcase_id,tcversion_id,show_mode)
{
  var feature_url = "lib/testcases/archiveData.php";
  feature_url +="?allow_edit=0&show_mode="+show_mode+"&edit=testcase&id="+
          tcase_id+"&tcversion_id="+tcversion_id;

  // 20101111 - asimon - now also remembers popup size
  var width = getCookie("TCEditPopupWidth");
  var height = getCookie("TCEditPopupHeight");

  if (width == null)
  {
    var width = "800";
  }

  if (height == null)
  {
    var height = "600";
  }

  var windowCfg = "width="+width+",height="+height+",resizable=yes,scrollbars=yes,dependent=yes";

  // second parameter(window name) with spaces caused bug on IE
  window.open(fRoot+feature_url,"TestCaseSpec",windowCfg);
}


/**
 * open a specific version of a requirement in a popup window
 * 
 * @param req_id Requirement ID
 * @param req_version_id Requirement Version ID
 * @param anchor string with anchor name
 */
function openLinkedReqVersionWindow(req_id, req_version_id, anchor)
{
  if (anchor == null) {
    anchor = '';
  } else {
    anchor = '#' + anchor;
  }
  
  var windowCfg='';
  var feature_url = "lib/requirements/reqView.php";
  feature_url += "?showReqSpecTitle=1&requirement_id=" + req_id + "&req_version_id=" + req_version_id + anchor;

  var width = getCookie("ReqPopupWidth");
  var height = getCookie("ReqPopupHeight");

  if (width == null)
  {
    var width = "800";
  }

  if (height == null)
  {
    var height = "600";
  }

  var windowCfg = "width="+width+",height="+height+",resizable=yes,scrollbars=yes,dependent=yes";
  window.open(fRoot+feature_url,"Requirement",windowCfg);
}


/**
 * open a requirement in a popup window
 * 
 * @param req_id Requirement ID
 * @param anchor string with anchor name
 */
function openLinkedReqWindow(req_id, anchor)
{
  // 20101008 - asimon - BUGID 3311
  var width = getCookie("ReqPopupWidth");
  var height = getCookie("ReqPopupHeight");
  var windowCfg='';
  var feature_url = "lib/requirements/reqView.php";


  if (anchor == null) {
    anchor = '';
  } else {
    anchor = '#' + anchor;
  }
  
  if (width == null)
  {
    width = "800";
  }

  if (height == null)
  {
    height = "600";
  }

  feature_url += "?showReqSpecTitle=1&requirement_id=" + req_id + anchor;
  windowCfg = "width="+width+",height="+height+",resizable=yes,scrollbars=yes,dependent=yes";
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

  // 20101008 - asimon - BUGID 3311
  var width = getCookie("ReqSpecPopupWidth");
  var height = getCookie("ReqSpecPopupHeight");

  if (width == null)
  {
    var width = "800";
  }

  if (height == null)
  {
    var height = "600";
  }

  var windowCfg = "width="+width+",height="+height+",resizable=yes,scrollbars=yes,dependent=yes";
  window.open(fRoot+feature_url,"RequirementSpecification",windowCfg);
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

/*
function: showCal
          used to display extjs datepicker

args:

returns:

*/

function showCal(id,dateField,dateFormat)
{
  // set default dateFormat if no dateFormat is passed
  if(dateFormat == null) {
    dateFormat = "m/d/Y";
  }
  var x = document.getElementById(id);
  x.innerHTML = '';
  var dp = new Ext.DatePicker({ renderTo:id, format:dateFormat, idField:dateField });
  // read value of date input field
  var el = document.getElementById(dateField);
  
  // if value on input field exists use it to preselect datepicker
  if(el.value != "")
  {
    /* now we have to convert localized timestamp to a format that "Date" understands
     * because datepicker needs Date Object to be able to preselect date according to
     * value on input field
     */
    
    // get char that splits date pieces on localized timestamp ( ".", "/" or "-" )
    splitChar = ".";
    if (el.value.indexOf("-") != -1) {
      splitChar = "-";
    } 
    if (el.value.indexOf("/") != -1) {
      splitChar = "/";
    }
    
    // split the date from input field with splitChar
    var splitDate = el.value.split(splitChar);
    
    // prepare variables for date "pieces"
    var year = null;
    var month = null;
    var day = null;
    
    // remove all splitChars (max 2)
    // TODO do not call replace twice use reg exp
    dateFormat = dateFormat.replace(splitChar, "");
    dateFormat = dateFormat.replace(splitChar, "");
    
    // get date "pieces" according to dateFormat
    for(i=0; i < dateFormat.length; i++) {
      switch (dateFormat.charAt(i)) {
        case "Y":
          year = splitDate[i];
          break;
        // not necessary right now as all localized timestamps use "Y" -> four digit year
        //case "y":
        //  year = splitDate[i];
        //  break;
        case "m": 
          month = splitDate[i];
          break;
        case "d": 
          day = splitDate[i];
          break;
      }
    }
    
    // finally create Date object to preselect date on datepicker
    // subtract 1 from month as january has value 0
    selectedDate = new Date(year,(month-1),day);
    
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

/*
function: onSelect
          used by showCal (only) to handle date select event

args:

returns:

*/

function onSelect(datePicker,date)
{
  var dt = new Date(date);
  // use the same output dateformat as datepicker uses
  document.getElementById(datePicker.idField).value = dt.format(datePicker.format);
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
function openReqWindow(tcase_id,callback)
{ 
  var windowCfg='';                       
  var feature_url = "lib/requirements/reqTcAssign.php";
  
  feature_url +="?edit=testcase&showCloseButton=1&callback="+callback+"&id="+tcase_id;

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
 * @param boolean success   [0] = error
 * @param string message  localized text
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
/*  else
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

  // 20101008 - asimon - BUGID 3311
  var width = getCookie("ExecEditPopupWidth");
  var height = getCookie("ExecEditPopupHeight");

  if (width == null)
  {
    var width = "800";
  }

  if (height == null)
  {
    var height = "600";
  }

  var windowCfg = "width="+width+",height="+height+",resizable=yes,scrollbars=yes,dependent=yes";
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
 * Helper function for BUGID 3311. Loads the value of a given cookie.
 * @param name Name of the cookie to load.
 */
function getCookie(name) {
  var cookie = document.cookie;

  var posName = cookie.indexOf("; " + name + "=");
  if (posName == -1) {
    if (cookie.indexOf(name + "=") == 0) {
      posName = 0;
    }
    else {
      return null;
    }
  }

  var valueStart = cookie.indexOf("=", posName)+1;
  var valueEnd = cookie.indexOf(";", posName+1);
  if (valueEnd == -1) valueEnd = cookie.length;

  var value = cookie.substring(valueStart, valueEnd);
  return unescape(value);
}

/**
 * Helper function for BUGID 3311. Stores the size of the current window to a cookie with the given name.
 * @param windowname The name for which the cookie shall be stored.
 */
function storeWindowSize(windowname) {
  var width = window.innerWidth || (window.document.documentElement.clientWidth || window.document.body.clientWidth);
  var height = window.innerHeight || (window.document.documentElement.clientHeight || window.document.body.clientHeight);

  var expires = new Date();
  // Expires in 10 days
  expires = new Date(expires.getTime() + 1000*60*60*24*10);

  document.cookie = windowname+'Width='+width+'; expires='+expires.toGMTString()+'; path=/';
  document.cookie = windowname+'Height='+height+'; expires='+expires.toGMTString()+'; path=/';
}

/**
 * Wrapper to native function to put an element ' on the face of user'
 * @param oid: HTML element ID
 *
 * @internal Revisions
 * 20110112 - added check if obj is not null to avoid warnings
 * 20101016 - franciscom - BUGID 3901: Edit Test Case STEP - scroll window to show selected step 
 */
function scrollToShowMe(oid) {
  obj = document.getElementById(oid);
  if (obj != null) {
    obj.scrollIntoView(true);
    obj.focus();
  }
}


/**
 * open a requirement in a popup window
 * 
 * @param item_id Requirement REVISION ID
 * @param anchor string with anchor name
 */
function openReqRevisionWindow(item_id, anchor)
{
  var width = getCookie("ReqPopupWidth");
  var height = getCookie("ReqPopupHeight");
  var windowCfg='';
  var feature_url = "lib/requirements/reqViewRevision.php";


  if (anchor == null) {
    anchor = '';
  } else {
    anchor = '#' + anchor;
  }

  if (width == null)
  {
    width = "800";
  }

  if (height == null)
  {
    height = "600";
  }

  feature_url += "?showReqSpecTitle=1&item_id=" + item_id + anchor;
  windowCfg = "width="+width+",height="+height+",resizable=yes,scrollbars=yes,dependent=yes";

  // Warning YOU CAN NOT HAVE spaces on windows title IE does not like it
  windowTitle = " ";
  window.open(fRoot+feature_url,"Requirement Revision",windowCfg);
}

/**
 * Open print preview in popup window to enable simple printing for the user.
 * 
 * @author asimon
 * @param type can be "req","reqSpec", "tc"
 * @param id
 * @param version_id the version which shall be printed, if set
 * @param revision_id only used for requirements, null in case of testcases
 * @param print_action target url to open in popup
 */
function openPrintPreview(type, id, child_id, revision, print_action) 
{
  // configure window size using cookies or default values if there are no cookies
  var width = getCookie("ReqPopupWidth");
  var height = getCookie("ReqPopupHeight");
  var windowCfg='';
  var feature_url = print_action;

  if (width == null) {
    width = "800";
  }
  if (height == null) {
    height = "600";
  }
  
  switch(type)
  {

    case 'req':
      feature_url += "?req_id=" + id + "&req_version_id=" + child_id + "&req_revision=" + revision;
    break;

    case 'reqSpec':
      feature_url += "?reqspec_id=" + id + "&reqspec_revision_id=" + child_id;
    break;
    
    case 'tc':
      feature_url += "&testcase_id=" + id + "&tcversion_id=" + child_id;
    break;

    case 'exec':
     feature_url += "?id=" + id;
    break;


    
  }
  windowCfg = "width="+width+",height="+height+",resizable=yes,scrollbars=yes,toolbar=yes,dependent=yes,menubar=yes";
  window.open(fRoot+feature_url,"_blank",windowCfg); // TODO localize "Print Preview"!
}



function openExecHistoryWindow(tc_id,tplan_check) 
{
  var url = "lib/execute/execHistory.php?tcase_id=" + tc_id;

  var width = getCookie("execHistoryPopupWidth");
  var height = getCookie("execHistoryPopupHeight");

  if(tplan_check != undefined)
  {
    url = url + '&onlyActiveTestPlans=' + tplan_check;    
  }  

  if (width == null)
  {
    width = "800";
  }

  if (height == null)
  {
    height = "600";
  }
  
  var windowCfg = "width="+width+",height="+height+",resizable=yes,scrollbars=yes,dependent=yes";
  window.open(fRoot+url, '_blank', windowCfg);
}

/**
 * open a requirement in a popup window
 * 
 * @param item_id Req Spec Revision ID
 * @param anchor string with anchor name
 */
function openReqSpecRevisionWindow(item_id, anchor)
{
  var cfg = new Object();

  cfg.width = getCookie("ReqPopupWidth");
  cfg.height = getCookie("ReqPopupHeight");
  cfg.feature_url = "lib/requirements/reqSpecViewRevision.php";

  if (anchor == null) {
    anchor = '';
  } 
  else 
  {
    anchor = '#' + anchor;
  }

  cfg.feature_url += "?showReqSpecTitle=1&item_id=" + item_id + anchor;
  cfg.windowTitle = "ReqSpecRevisionView";
  openItemWindow(cfg);
}



function openItemWindow(cfg)
{

  var width = cfg.width;
  var height = cfg.height;

  if (cfg.anchor == null) {
    anchor = '';
  } else {
    anchor = '#' + cfg.anchor;
  }

  if (width == null)
  {
    width = "800";
  }

  if (height == null)
  {
    height = "600";
  }

  windowCfg = "width=" + cfg.width + ",height=" + cfg.height + ",resizable=yes,scrollbars=yes,dependent=yes";
  window.open(fRoot+cfg.feature_url,cfg.windowTitle,windowCfg);
}

/*
  function: openTCW
        another open Test Case Window function
        Intended to be used for adding reference to a Test Case 
        inside other Test Case for some sort of reuse

  args: tcase_external_id: example TER-23
      version_number: example 2

  returns: - 

*/
function openTCW(tcase_external_id,version_number)
{
  var __FUNCTION__ = 'openTCW';
  var windowCfg = '';
  var width = getCookie("TCEditPopupWidth");
  var height = getCookie("TCEditPopupHeight");
  var feature_url = "lib/testcases/archiveData.php";

  feature_url +=  "?caller=openTCW&allow_edit=0&show_mode=editDisabled"+"&edit=testcase&tcaseExternalID="+tcase_external_id+
          "&tcaseVersionNumber="+version_number;

  if (width == null)
  {
    var width = "800";
  }

  if (height == null)
  {
    var height = "600";
  }
  windowCfg = "width="+width+",height="+height+",resizable=yes,scrollbars=yes,dependent=yes";

  // second parameter(window name) with spaces caused bug on IE
  window.open(fRoot+feature_url,__FUNCTION__,windowCfg);
}

/**
 *
 */
function toogleShowHide(oid,display_type)
{
  var obj = document.getElementById(oid);
  
  if (!obj)
  {
    return;
  }                  
  
  if(obj.style.display == 'none')
  {
    if(display_type != undefined)
    {
      obj.style.display = display_type;
    } 
    else
    {
      obj.style.display = '';
    } 
  }
  else
  {
    obj.style.display = 'none';
  }
}

/**
 *
 */
function getImageURL(file_id)
{

  return '<img src="lib/attachments/attachmentdownload.php?id=' + file_id + '"/>';
}


/**
 *
 */
function toogleImageURL(img_container_oid,file_id)
{
  var ox = document.getElementById(img_container_oid);
  if(ox.innerHTML != '')
  {
    return '';
  } 
  else
  {
    return '<img src="lib/attachments/attachmentdownload.php?id=' + file_id + '"/>';
  } 
}


/**
 *
 */
function clearTextAreaByClassName(cssClassName)
{
  var ol = document.getElementsByClassName(cssClassName);
  for (var idx= 0;idx < ol.length;idx++)
  {
    ol[idx].value = '';
  }   
}

/**
 *
 */
function clearSelectByClassName(cssClassName)
{
  var ol = document.getElementsByClassName(cssClassName);
  for (var idx= 0;idx < ol.length;idx++)
  {
    ol[idx].value = '';
  }   
}



/**
 *
 */
function validateStepsReorder(cssClassName)
{
  var ol = document.getElementsByClassName(cssClassName);
  var status = true;
  var dummy;
  
  for (var idx= 0;idx < ol.length;idx++)
  {
    dummy = ol[idx].value;
    dummy = dummy.trim();
    if(dummy == '')
    {
      alert('Empty step number - KO');
      return false;
    }  

    if(isNaN(dummy))
    {
      alert('Only numbers allowed - KO');
      return false;
    }  

    if( dummy <= 0)
    {
      alert('Only numbers > 0, please - KO');
      return false;
    }  
  }   

  // check that all steps numbers are different
  dummy = [];
  for (var idx= 0;idx < ol.length;idx++)
  {
    if( dummy.indexOf(ol[idx].value) < 0 )
    {
      dummy.push(ol[idx].value);
    }  
    else
    {
      alert('Step numbers must be unique');
      return false;
    }  
  }
  return status;
}


/**
 * toogle Required attribute on field that can be Show/Hide
 *
 */
function toogleRequiredOnShowHide(oid,display_type)
{
  var obj = document.getElementById(oid);
  if (!obj)
  {
    return;
  }                  

  if(obj.style.display == 'none')
  {
    if(display_type != undefined)
    {
      obj.style.display = display_type;
    } 
    else
    {
      // SHOW, then field has to be Required
      obj.style.display = '';
      obj.setAttribute('required','required');
    } 
  }
  else
  {
    obj.style.display = 'none';
    obj.removeAttribute('required'); 
  }
}

/**
 * Open testcase description in a popup window.
 * @author Andreas Simon
 * @param tc_id
 */
function openTSEditWindow(tsuite_id) 
{

  var url = "lib/testcases/archiveData.php?edit=testsuite&id=" + tsuite_id 
  var width = getCookie("TSEditPopupWidth");
  var height = getCookie("TSEditPopupHeight");
  
  if (width == null)
  {
    var width = "800";
  }
  
  if (height == null)
  {
    var height = "600";
  }
  
  var windowCfg = "width="+width+",height="+height+",resizable=yes,scrollbars=yes,dependent=yes";
  window.open(fRoot+url, '_blank', windowCfg);
}
