{*
  execSetResultsJS.inc.tpl
*}

<script language="JavaScript" src="gui/javascript/radio_utils.js" type="text/javascript"></script>
<script language="JavaScript" src="gui/javascript/expandAndCollapseFunctions.js" type="text/javascript"></script>

{if #ROUND_EXEC_HISTORY# || #ROUND_TC_TITLE# || #ROUND_TC_SPEC#}
  {$round_enabled=1}
  <script language="JavaScript" src="{$basehref}gui/niftycube/niftycube.js" type="text/javascript"></script>
{/if}

<script language="JavaScript" type="text/javascript">
var msg="{$labels.warning_delete_execution}";
var import_xml_results="{$labels.import_xml_results}";
</script>

{include file="inc_del_onclick.tpl"}

<script language="JavaScript" type="text/javascript">
function load_notes(panel,exec_id) {
  // solved ONLY for  $webeditorType == 'none'
  var url2load=fRoot+'lib/execute/getExecNotes.php?readonly=1&exec_id=' + exec_id;
  panel.load({ url:url2load });
}

/*
Set value for a group of combo (have same prefix).
*/
function set_combo_group(formid,combo_id_prefix,value_to_assign) {
  var f=document.getElementById(formid);
  var all_comboboxes = f.getElementsByTagName('select');
  var input_element;
  var idx=0;
    
  for(idx = 0; idx < all_comboboxes.length; idx++) {
    input_element=all_comboboxes[idx];
    if( input_element.type == "select-one" && 
        input_element.id.indexOf(combo_id_prefix)==0 &&
       !input_element.disabled) {
      input_element.value=value_to_assign;
    } 
  }
}

// Escape all messages (string)
var alert_box_title="{$labels.warning|escape:'javascript'}";
var warning_nothing_will_be_saved="{$labels.warning_nothing_will_be_saved|escape:'javascript'}";

/**
 *
 */
function validateForm(f) {
  var status_ok = true;


  if( status_ok ) {
    status_ok = checkCustomFields(f);
  }

  if( status_ok && saveStepsPartialExecClicked ) {
    var msg="{$labels.partialExecNothingToSave}";
    status_ok = checkStepsHaveContent(msg);
  }

  return status_ok;
}






function OLDvalidateForm(f)
{
  var status_ok=true;
  var cfields_inputs='';
  var cfValidityChecks;
  var cfield_container;
  var access_key;
  cfield_container=document.getElementById('save_button_clicked').value;
  access_key='cfields_exec_time_tcversionid_'+cfield_container; 
    
  if( document.getElementById(access_key) != null )
  {    
      cfields_inputs = document.getElementById(access_key).getElementsByTagName('input');
      cfValidityChecks=validateCustomFields(cfields_inputs);
      if( !cfValidityChecks.status_ok )
      {
          var warning_msg=cfMessages[cfValidityChecks.msg_id];
          alert_message(alert_box_title,warning_msg.replace(/%s/, cfValidityChecks.cfield_label));
          return false;
      }
  }
  return true;
}

/*
  function: checkSubmitForStatusCombo
            $statusCode has been checked, then false is returned to block form submit().
            
            Dev. Note - remember this:
            
            KO:
               onclick="foo();checkSubmitForStatus('n')"
            OK
               onclick="foo();return checkSubmitForStatus('n')"
                              ^^^^^^ 
            

  args :
  
  returns: 

*/
function checkSubmitForStatusCombo(oid,statusCode2block)
{
  var access_key;
  var isChecked;
  
  if(document.getElementById(oid).value == statusCode2block)
  {
    alert_message(alert_box_title,warning_nothing_will_be_saved);
    return false;
  }  
  return true;
}


/**
 * 
 * IMPORTANT DEVELOPMENT NOTICE
 * ATTENTION args is a GLOBAL Javascript variable, then be CAREFULL
 */
function openExportTestCases(windows_title,tsuite_id,tproject_id,tplan_id,build_id,platform_id,tcversion_set) 
{
  wargs = "tsuiteID=" + tsuite_id + "&tprojectID=" + tproject_id + "&tplanID=" + tplan_id; 
  wargs += "&buildID=" + build_id + "&platformID=" + platform_id;
  wargs += "&tcversionSet=" + tcversion_set;
  wref = window.open(fRoot+"lib/execute/execExport.php?"+wargs,
                     windows_title,"menubar=no,width=650,height=500,toolbar=no,scrollbars=yes");
  wref.focus();
}


{* 
  Initialize note panels. The array panel_init_functions is filled with init
  functions from inc_exec_show_tc_exec.tpl and executed from onReady below 
*}
panel_init_functions = new Array();
Ext.onReady(function() {
  for(var i=0;i<panel_init_functions.length;i++) {
    panel_init_functions[i]();
  }
});

/**
 * Be Carefull this TRUST on existence of $gui->delAttachmentURL
 */
function jsCallDeleteFile(btn, text, o_id) { 
  if( btn == 'yes' ) {
    var windowCfg="width=510,height=150,resizable=yes,dependent=yes";
    window.open(fRoot+"lib/attachments/attachmentdelete.php?id="+o_id,
                "Delete",windowCfg);
  }
}        
</script>

<script src="third_party/clipboard/clipboard.min.js"></script>

