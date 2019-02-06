{*

TestLink Open Source Project - http://testlink.sourceforge.net/ 
 
Purpose: smarty template - compare requirement versions

@internal revision
*}

{include file="inc_head.tpl" openHead='yes' jsValidate="yes"}
{include file="inc_del_onclick.tpl"}

{lang_get var="labels"
          s="select_versions,title_compare_versions_req,version,compare,modified,modified_by,
          btn_compare_selected_versions, context, show_all,author,timestamp,timestamp_lastchange,
          warning_context, warning_context_range, warning_empty_context,warning,custom_field, 
          warning_selected_versions, warning_same_selected_versions,revision,attribute,
          custom_fields,attributes,log_message,use_html_code_comp,use_html_comp,diff_method,
          btn_cancel"}

<link rel="stylesheet" type="text/css" href="{$basehref}third_party/diff/diff.css">
<link rel="stylesheet" type="text/css" href="{$basehref}third_party/daisydiff/css/diff.css">

<script type="text/javascript">
//BUGID 3943: Escape all messages (string)
var alert_box_title = "{$labels.warning|escape:'javascript'}";
var warning_empty_context = "{$labels.warning_empty_context|escape:'javascript'}";
var warning_context_range = "{$labels.warning_context_range|escape:'javascript'}";
var warning_selected_versions = "{$labels.warning_selected_versions|escape:'javascript'}";
var warning_same_selected_versions = "{$labels.warning_same_selected_versions|escape:'javascript'}";
var warning_context = "{$labels.warning_context|escape:'javascript'}";

{literal}
function tip4log(itemID)
{
	var fUrl = fRoot+'lib/ajax/getreqlog.php?item_id=';
	new Ext.ToolTip({
        target: 'tooltip-'+itemID,
        width: 500,
        autoLoad:{url: fUrl+itemID},
        dismissDelay: 0,
        trackMouse: true
    });
}

Ext.onReady(function(){ 
{/literal}
{foreach from=$gui->items key=idx item=info}
  tip4log({$info.item_id});
{/foreach}
{literal}
});

// 20110107 - new diff engine
function triggerContextInput(selected) {
	var context = document.getElementById("context_input");
	if (selected == 0) {
		context.style.display = "none";
	} else {
		context.style.display = "table-row";;
	}
}

function triggerField(field)
{
	if (field.disabled == true) {
    	field.disabled = false;
	} else {
    	field.disabled = true;
	}
}

function triggerRadio(radio, field) {
    	radio[0].checked = false;
    	radio[1].checked = false;
    	radio[field].checked = true;
    	triggerContextInput(field);
}

function valButton(btn) {
    var cnt = -1;
    for (var i=btn.length-1; i > -1; i--) {
        if (btn[i].checked) {
        	cnt = i;
        	i = -1;
        }
    }
    if (cnt > -1) {
    	return true;
    }
    else {
    	return false;
    }
}

function validateForm() {
	if (isWhitespace(document.req_compare_versions.context.value)) {
	    alert_message(alert_box_title,warning_empty_context);
		return false;
	} else {
		value = parseInt(document.req_compare_versions.context.value);
		if (isNaN(value))
		{
		   	alert_message(alert_box_title,warning_context);
		   	return false;
		} else if (value < 0) {
			alert_message(alert_box_title,warning_context_range);
		   	return false;
		}
	}
	
	if (!valButton(document.req_compare_versions.left_item_id)
			|| !valButton(document.req_compare_versions.right_item_id)) {
		alert_message(alert_box_title,warning_selected_versions);
		return false;
	}
	
	for (var i=document.req_compare_versions.left_item_id.length-1; i > -1; i--) {
        if (document.req_compare_versions.left_item_id[i].checked && document.req_compare_versions.right_item_id[i].checked) {
        	alert_message(alert_box_title,warning_same_selected_versions);
        	return false;
        }
    }
}

</script>
{/literal}

</head>
<body>

{if $gui->compare_selected_versions}

	<h1 class="title">{$labels.title_compare_versions_req}</h1> 
			
		<div class="workBack" style="width:99%; overflow:auto;">	
	{$gui->subtitle}
    {if $gui->attrDiff != ''}
      <h2>{$labels.attributes}</h2>
      <table border="1" cellspacing="0" cellpadding="2" style="width:60%" class="code">
        <thead>
          <tr>
            <th style="text-align:left;">{$labels.attribute}</th>
            <th style="text-align:left;">{$gui->leftID}</th>
            <th style="text-align:left;">{$gui->rightID}</th>
          </tr>
        </thead>
        <tbody>
	      {foreach item=attrDiff from=$gui->attrDiff}
          <tr>
            <td class="{if $attrDiff.changed}del{else}ins{/if}"; style="font-weight:bold">{$attrDiff.label}</td>
            <td class="{if $attrDiff.changed}del{else}ins{/if}";>{$attrDiff.lvalue}</td>
            <td class="{if $attrDiff.changed}del{else}ins{/if}";>{$attrDiff.rvalue}</td>
          </tr>
        {/foreach}
        </tbody>
      </table>
      <p />
    {/if}
		
	  {foreach item=diff from=$gui->diff}
		<h2>{$diff.heading}</h2>
		<fieldset class="x-fieldset x-form-label-left" >
		<legend class="legend_container" >{$diff.message}</legend>
	  	  {if $diff.count > 0}{$diff.diff}{/if}
	  	  </fieldset>
	  {/foreach}
    {if $gui->cfieldsDiff != ''}
      <p />
      <h2>{$labels.custom_fields}</h2>
      <table border="1" cellspacing="0" cellpadding="2" style="width:60%" class="code">
        <thead>
        <tr>
          <th style="text-align:left;">{$labels.custom_field}</th>
          <th style="text-align:left;">{$gui->leftID}</th>
          <th style="text-align:left;">{$gui->rightID}</th>
        </tr>
        </thead>
        <tbody>
	      {foreach item=cfDiff from=$gui->cfieldsDiff}
          <tr>
            <td class="{if $cfDiff.changed}del{else}ins{/if}"; style="font-weight:bold">{$cfDiff.label}</td>
            <td class="{if $cfDiff.changed}del{else}ins{/if}";>{$cfDiff.lvalue}</td>
            <td class="{if $cfDiff.changed}del{else}ins{/if}";>{$cfDiff.rvalue}</td>
          </tr>
        {/foreach}
        </tbody>
      </table>
		{/if}
		</div>
		
{else}

	<h1 class="title">{$labels.title_compare_versions_req}</h1> 
	
	<div class="workBack" style="width:97%;">
	
	<form target="diffwindow" method="post" action="{$basehref}lib/requirements/reqCompareVersions.php" name="req_compare_versions" id="req_compare_versions"  
			onsubmit="return validateForm();" />			
	
	<p>
		<input type="submit" name="compare_selected_versions" value="{$labels.btn_compare_selected_versions}" />
		<input type="button" name="cancel" value="{$labels.btn_cancel}" onclick="javascript:history.back();" />
	</p>
	<br/>
	
	<table border="0" cellspacing="0" cellpadding="2" style="font-size:small;" width="100%">
	
	    <tr style="background-color:blue;font-weight:bold;color:white">
	        <th width="12px" style="font-weight: bold; text-align: center;">{$labels.version}</td>
	        <th width="12px" style="font-weight: bold; text-align: center;">{$labels.revision}</td>
	        <th width="12px" style="font-weight: bold; text-align: center;">&nbsp;{$labels.compare}</td>
	        <th style="font-weight: bold; text-align: center;">{$labels.log_message}</td>
	        <th style="font-weight: bold; text-align: center;">{$labels.timestamp_lastchange}</td>
	    </tr>
	
	{counter assign="mycount"}
	{foreach item=req from=$gui->items}
	   <tr>
	        <td style="text-align: center;">{$req.version}</td>
	        <td style="text-align: center;">{$req.revision}</td>
	        <td style="text-align: center;"><input type="radio" name="left_item_id" value="{$req.item_id}" 
	            {if $mycount == 2} 	 checked="checked"  {/if} />
	            <input type="radio" name="right_item_id" value="{$req.item_id}" {if $mycount == 1} checked="checked"	{/if}/>
	        </td>
        	{* using EXT-JS logic to open div to show info when mouse over *}
	        <td id="tooltip-{$req.item_id}">
        	{$req.log_message}
        	</td>
        	<td style="text-align: left; cursor: pointer; color: rgb(0, 85, 153);" onclick="javascript:openReqRevisionWindow({$req.item_id});">
	            <nobr>{localize_timestamp ts = $req.timestamp}, {$req.last_editor}</nobr>
	        </td>
	    </tr>
	{counter}
	{/foreach}
	
	</table><br/>
	
	{* 20110107 - new diff engine *}
	<h2>{$labels.diff_method}</h2>
	<table border="0" cellspacing="0" cellpadding="2" style="font-size:small;" width="100%">
	<tr><td style="width:8px;">
	
	<input type="radio" id="use_html_comp" name="use_html_comp" 
	       checked="checked" onclick="triggerRadio(this.form.use_html_comp, 0);"/> </td><td> {$labels.use_html_comp} </td></tr>
	<tr><td><input type="radio" id="use_html_comp" name="use_html_code_comp"
	       onclick="triggerRadio(this.form.use_html_comp, 1);"/> </td><td> {$labels.use_html_code_comp} </td></tr>
	<tr id="context_input" style="display: none;"> <td>&nbsp;</td><td>
		{$labels.context} <input type="text" name="context" id="context" maxlength="4" size="4" value="{$gui->context}" /> 
		<input type="checkbox" id="context_show_all" name="context_show_all" 
		       onclick="triggerField(this.form.context);"/> {$labels.show_all} 	</td></tr></table>
	
	<p>
		<input type="hidden" name="requirement_id" value="{$gui->req_id}" />
		<input type="submit" name="compare_selected_versions" value="{$labels.btn_compare_selected_versions}" />
		<input type="button" name="cancel" value="{$labels.btn_cancel}" onclick="javascript:history.back();" />
	</p>
	
	</form>

	</div>

{/if}

</body>

</html>
