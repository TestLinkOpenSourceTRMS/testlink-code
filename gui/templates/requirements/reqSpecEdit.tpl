{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: reqSpecEdit.tpl,v 1.1 2007/11/19 21:01:05 franciscom Exp $ *}
{* Purpose: smarty template - create a new req document *}
{include file="inc_head.tpl"}

{assign var="cfg_section" value=$smarty.template|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

<body>
<h1> 
 {lang_get s='help' var='common_prefix'}
 {lang_get s='req_spec' var="xx_alt"}
 {assign var="text_hint" value="$common_prefix: $xx_alt"}
 {include file="inc_help.tpl" help="requirementsCoverage" locale=$locale 
          alt="$text_hint" title="$text_hint"  style="float: right;"}
	{lang_get s='req_spec'}{$smarty.const.TITLE_SEP_TYPE3}
	{lang_get s='testproject'}{$smarty.const.TITLE_SEP}{$productName|escape} 
</h1>

{* Create Form *}
{if $modify_req_rights == "yes"}

<div class="workBack">
{if $page_descr != ''}
<h1>{$page_descr}</h1>
{/if}
	
<form name="reqSpecEdit" id="reqSpecEdit" method="post">
<input type="hidden" name="req_spec_id" value="{$req_spec_id}">

<table class="common" style="width: 90%">
	<tr>
		<th>{lang_get s='title'}</th>
		<td><input type="text" name="title" 
		           size="{#REQ_SPEC_TITLE_SIZE#}"  
		           maxlength="{#REQ_SPEC_TITLE_MAXLEN#}" 
		           value="{$req_spec_title}"/></td>
	</tr>
	<tr>
		<th>{lang_get s='scope'}</th>
		<td>{$scope}</td>
	</tr>
	<tr>
		<th>{include file="inc_help.tpl" help="requirementsCoverage" locale=$locale 
          alt="$text_hint" title="$text_hint"  style="float: right;"}
			{lang_get s='req_total'}
		 </th>
		<td><input type="text" name="countReq" 
		           size="{#REQ_COUNTER_SIZE#}" maxlength="{#REQ_COUNTER_MAXLEN#}" 
			         value="{$total_req_counter}" /></td>
	</tr>

  {if $cf != ''}
  {$cf}
  {/if}
</table>
<div class="groupBtn">
	<input type="hidden" name="do_action" value="">
	<input type="submit" name="createSRS" value="{$submit_button_label}" 
	       onclick="do_action.value='{$submit_button_action}'"/>
</div>
</form>
</div>
{/if}


<script type="text/javascript" defer="1">
   	document.forms[0].title.focus()
</script>

</body>
</html>
