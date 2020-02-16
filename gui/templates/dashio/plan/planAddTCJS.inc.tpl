{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
@filesource planAddTCJS.inc.tpl
*}
<script type="text/javascript">
<!--
js_warning_remove_executed = '{$labels.warning_remove_executed}';
js_cs_all_checkbox_selected = '{$labels.selected_cb}';
js_cs_all_checkbox_deselected = '{$labels.deselected_cb}';
js_remove_executed_counter = 0;

function updateRemoveExecCounter(oid)
{
  var obj = document.getElementById(oid)
  if( obj.checked )
  {
    js_remove_executed_counter++;
  }
  else
  {
    js_remove_executed_counter--;
  }
}

function checkDelete(removeExecCounter)
{
  if (js_remove_executed_counter > 0) {
    return confirm(js_warning_remove_executed);
  } else {
    return true;
  }
}


function tTip(tcID,vID)
{
  var fUrl = fRoot+'lib/ajax/gettestcasesummary.php?tcase_id=';
  new Ext.ToolTip({
        target: 'tooltip-'+tcID,
        width: 500,
        autoLoad: { url: fUrl+tcID+'&tcversion_id='+vID },
        dismissDelay: 0,
        trackMouse: true
    });
}

function showTT(e)
{
  alert(e);
}

js_tcase_importance = new Array();
js_tcase_wkfstatus = new Array();

attrDomain = new Object();
attrDomain.importance = new Array();
attrDomain.wkfstatus = new Array();

{foreach key=key item=item from=$gsmarty_option_importance}
  attrDomain.importance[{$key}] = "{$item}";
{/foreach}

{foreach key=key item=item from=$gsmarty_option_wkfstatus}
  attrDomain.wkfstatus[{$key}] = "{$item}";
{/foreach}


// Update test case attributes when selecting a different test case version
// - workflow status
// - importance
//
function updTCAttr(tcID,tcvID) 
{
  var impOID = "importance_"+tcID;
  var wkfOID = "wkfstatus_"+tcID;
  var val;
  var poid;

  val = js_tcase_importance[tcID][tcvID];
  poid = document.getElementById(impOID);
  poid.firstChild.nodeValue = attrDomain.importance[val];

  val = js_tcase_wkfstatus[tcID][tcvID];
  poid = document.getElementById(wkfOID);
  poid.firstChild.nodeValue = attrDomain.wkfstatus[val];
}

Ext.onReady(function(){ 
{foreach from=$gui->items key=idx item=info}
  {foreach from=$info.testcases key=tcidx item=tcversionInfo}
   {$tcversionLinked=$tcversionInfo.linked_version_id}
     tTip({$tcidx},{$tcversionLinked});
  {/foreach}  
{/foreach}
});
//-->
</script>