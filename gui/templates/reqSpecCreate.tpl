{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: reqSpecCreate.tpl,v 1.10 2007/01/25 20:02:23 schlundus Exp $ *}
{* Purpose: smarty template - create a new req document *}
{include file="inc_head.tpl"}

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
<h1>{lang_get s='action_create_srs'}</h1>
	
<form name="formSRSCreate" method="post">
<table class="common" style="width: 90%">
	<tr>
		<th>{lang_get s='title'}</th>
		<td><input type="text" name="title" size="60" maxlength="100" /></td>
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
		<td><input type="text" name="countReq" size="5" maxlength="5" 
			value="0" /></td>
	</tr>
</table>
<div class="groupBtn">
	<input type="submit" name="createSRS" value="{lang_get s='btn_create'}" />
	<input type="button" name="backToSRSList" value="{lang_get s='btn_cancel'}" 
		onclick="javascript: location.href=fRoot+'lib/req/reqSpecList.php';" />
</div>
</form>
</div>
{/if}


<script type="text/javascript" defer="1">
   	document.forms[0].title.focus()
</script>

</body>
</html>
