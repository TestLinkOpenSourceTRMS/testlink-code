{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: reqSpecCreate.tpl,v 1.5 2006/03/23 20:46:26 schlundus Exp $ *}
{* Purpose: smarty template - create a new req document *}
{include file="inc_head.tpl"}

<body>
<h1> 
	<img alt="{lang_get s='help'}: {lang_get s='req_spec'}" class="help" 
	src="icons/sym_question.gif" 
	onclick="javascript: open_popup('{$helphref}requirementsCoverage.html');" />
	{lang_get s='create'} {$productName|escape} {lang_get s='req_spec'}
</h1>

{* Create Form *}
{if $modify_req_rights == "yes"}

<div class="workBack">
	
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
		<th><img alt="{lang_get s='help'}: {lang_get s='req_total_count'}"
			class="help" src="icons/sym_question.gif" 
			onclick="javascript:open_popup('{$helphref}requirementsCoverage.html#total_count');" />
			{lang_get s='req_total'}
		 </th>
		<td><input type="text" name="countReq" size="5" maxlength="5" 
			value="n/a" /></td>
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