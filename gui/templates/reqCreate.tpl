{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: reqCreate.tpl,v 1.7 2006/10/16 10:36:10 franciscom Exp $ *}
{* Purpose: smarty template - create / edit a req  *}
{include file="inc_head.tpl"}

<body onload="document.forms[0].elements[0].focus()">

<h1>
  
	<img alt="{lang_get s='help'}: {lang_get s='reqs'}" class="help" 
	src="icons/sym_question.gif" 
	onclick="javascript:open_popup('{$helphref}requirementsCoverage.html#req');" />
	{lang_get s='req_create'} - {$srs_title|escape} 
</h1>

{* show SQL result *}
{include file="inc_update.tpl" result=$sqlResult item="Requirement" name=$name action=$action}

<div class="workBack">

{* Update Form *}

<form name="formReqCreate" method="post" 
	action="lib/req/reqSpecView.php?idSRS={$arrSpec[0].id}">
<table class="common" style="width: 90%">
	<tr>
		<th>{lang_get s='req_doc_id'}</th>
		<td><input type="text" name="reqDocId" size="20" maxlength="16" /></td>
	</tr>
	<tr>
		<th>{lang_get s='title'}</th>
		<td><input type="text" name="title" size="50" maxlength="100" /></td>
	</tr>
	<tr>
		<th>{lang_get s='scope'}</th>
		<td>{$scope}</td>
	</tr>
	<tr>
		<th>{lang_get s='status'}</th>
		<td><select name="reqStatus">
			{html_options options=$selectReqStatus selected=$arrReq.status}
		</select></td>
	</tr>
</table>
<div class="groupBtn">
<input type="hidden" name="create" value="1" />
<input type="submit" name="createReq" value="{lang_get s='btn_create'}" />
<input type="button" name="cancel" value="{lang_get s='btn_cancel'}" 
	onclick="javascript: location.href=fRoot+'lib/req/reqSpecView.php?idSRS={$arrSpec[0].id}';" />
</div>
</form>

</div>

</div>

</body>
</html>