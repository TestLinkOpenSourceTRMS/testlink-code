{*
TestLink Open Source Project - http://testlink.sourceforge.net/ 
 
Compare requirement specifications revisions

@internal revision
*}

{include file="inc_head.tpl" openHead='yes' jsValidate="yes"}
{include file="inc_del_onclick.tpl"}

{lang_get var="labels"
          s="title_compare_revisions_rspec,compare,modified,modified_by,
          btn_compare_selected_revisions, context, show_all,author,timestamp,timestamp_lastchange,
          warning_context, warning_context_range, warning_empty_context,warning,custom_field, 
          warning_selected_revisions, warning_same_selected_revisions,revision,attribute,
          custom_fields,attributes,log_message,use_html_code_comp,use_html_comp,diff_method,
          btn_cancel"}

<link rel="stylesheet" type="text/css" href="{$basehref}third_party/diff/diff.css">
<link rel="stylesheet" type="text/css" href="{$basehref}third_party/daisydiff/css/diff.css">

<script type="text/javascript">
var alert_box_title = "{$labels.warning|escape:'javascript'}";
var warning_empty_context = "{$labels.warning_empty_context|escape:'javascript'}";
var warning_context_range = "{$labels.warning_context_range|escape:'javascript'}";
var warning_selected_items = "{$labels.warning_selected_revisions|escape:'javascript'}";
var warning_same_selected_items = "{$labels.warning_same_selected_revisions|escape:'javascript'}";
var warning_context = "{$labels.warning_context|escape:'javascript'}";

{literal}
function tip4log(itemID)
{
	var fUrl = fRoot+'lib/ajax/getreqspeclog.php?item_id=';
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

function switchDisplay(oid,on_off) 
{
	var item = document.getElementById(oid);
	item.style.display = "table-row";
	if (on_off == 'off') {
		item.style.display = "none";
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

function valButton(btn) 
{
    var cnt = -1;
    for (var idx=btn.length-1; idx > -1; idx--) 
    {
        if (btn[idx].checked) {
        	cnt = idx;
        	idx = -1;
        }
    }
    return (cnt > -1);
}

function validateForm(f) 
{

	// alert('DEBUG:f.context.value:' + f.context.value);
	if (isWhitespace(f.context.value)) 
	{
	    alert_message(alert_box_title,warning_empty_context);
		return false;
	} 
	else 
	{
		value = parseInt(f.context.value);
		if (isNaN(value))
		{
		   	alert_message(alert_box_title,warning_context);
		   	return false;
		} 
		else if (value < 0) 
		{
			alert_message(alert_box_title,warning_context_range);
		   	return false;
		}
	}
	
	//alert(valButton(f.left_item_id));
	//alert(valButton(f.right_item_id));
	// Check two items has been selected
	if (!valButton(f.left_item_id) || !valButton(f.right_item_id)) 
	{
		alert_message(alert_box_title,warning_selected_items);
		return false;
	}
	
	for (var idx=f.left_item_id.length-1; idx > -1; idx--) 
	{
		//alert('LEN:' + f.left_item_id.length-1);
		//alert('IDX:' + idx);
		//alert('LI:' + f.left_item_id[idx].checked);
		//alert('RI:' +f.right_item_id[idx].checked);
		
        if (f.left_item_id[idx].checked && f.right_item_id[idx].checked) 
        {
        	alert_message(alert_box_title,warning_same_selected_items);
        	return false;
        }
    }
}

</script>
{/literal}

</head>
<body>

{if $gui->doCompare}

	<h1 class="title">{$labels.title_compare_revisions_rspec}</h1> 
			
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

	<h1 class="title">{$labels.title_compare_revisions_rspec}</h1> 
	
	<div class="workBack" style="width:97%;">
	
	<form target="diffwindow" method="post" action="{$basehref}lib/requirements/reqSpecCompareRevisions.php" 
		  name="cmp" id="cmp"  
		  onsubmit="return validateForm(this);" />			
	
	<p>
		<input type="submit" name="doCompare" value="{$labels.btn_compare_selected_revisions}" />
		<input type="button" name="cancel" value="{$labels.btn_cancel}" onclick="javascript:history.back();" />
	</p>
	<br/>
	
	<table border="0" cellspacing="0" cellpadding="2" style="font-size:small;" width="100%">
	
	    <tr style="background-color:blue;font-weight:bold;color:white">
	        <th width="12px" style="font-weight: bold; text-align: center;">{$labels.revision}</td>
	        <th width="12px" style="font-weight: bold; text-align: center;">&nbsp;{$labels.compare}</td>
	        <th style="font-weight: bold; text-align: center;">{$labels.log_message}</td>
	        <th style="font-weight: bold; text-align: center;">{$labels.timestamp_lastchange}</td>
	    </tr>
	
	{counter assign="mycount"}
	{foreach item=rspec from=$gui->items}
	   <tr>
	        <td style="text-align: center;">{$rspec.revision}</td>
	        <td style="text-align: center;"><input type="radio" name="left_item_id" value="{$rspec.item_id}" 
	            {if $mycount == 2} 	 checked="checked"  {/if} />
	            <input type="radio" name="right_item_id" value="{$rspec.item_id}" {if $mycount == 1} checked="checked"	{/if}/>
	        </td>
        	{* using EXT-JS logic to open div to show info when mouse over *}
	        <td id="tooltip-{$rspec.item_id}">
        	{$rspec.log_message}
        	</td>
        	<td style="text-align: left; cursor: pointer; color: rgb(0, 85, 153);" onclick="javascript:openReqSpecRevisionWindow({$rspec.item_id});">
	            <nobr>{localize_timestamp ts = $rspec.timestamp}, {$rspec.last_editor}</nobr>
	        </td>
	    </tr>
	{counter}
	{/foreach}
	
	</table><br/>
	
	<h2>{$labels.diff_method}</h2>
	<table border="0" cellspacing="0" cellpadding="2" style="font-size:small;" width="100%">
	<tr><td style="width:8px;">
	<input type="radio" name="diff_method" value="htmlCompare" 
	       checked="checked" onclick="switchDisplay('context_input','off');"/> </td><td> {$labels.use_html_comp} </td></tr>
	<tr><td><input type="radio" name="diff_method" value="htmlCodeCompare"
	       onclick="switchDisplay('context_input','on');"/> </td><td> {$labels.use_html_code_comp} </td></tr>
	<tr id="context_input" style="display: none;"> <td>&nbsp;</td><td>
		{$labels.context} <input type="text" name="context" id="context" maxlength="4" size="4" value="{$gui->context}" /> 
		<input type="checkbox" id="context_show_all" name="context_show_all" 
		       onclick="triggerField(this.form.context);"/> {$labels.show_all} 	</td></tr></table>
	
	<p>
		<input type="hidden" name="req_spec_id" value="{$gui->req_spec_id}" />
		<input type="submit" name="doCompare" value="{$labels.btn_compare_selected_revisions}" />
		<input type="button" name="cancel" value="{$labels.btn_cancel}" onclick="javascript:history.back();" />
	</p>
	
	</form>

	</div>

{/if}

</body>

</html>