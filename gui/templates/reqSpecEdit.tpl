{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: reqSpecEdit.tpl,v 1.4 2005/10/09 18:13:48 schlundus Exp $ *}
{* Purpose: smarty template - edit a req specification *}
{include file="inc_head.tpl"}

<body onload="document.forms[0].elements[0].focus()">
<h1>{lang_get s='edit'} {lang_get s='req_spec'}: {$arrSpec[0].title|escape}
	<img alt="{lang_get s='help'}: {lang_get s='req_spec'}" class="help" 
	src="icons/sym_question.gif" 
	onclick="javascript:open_popup('{$helphref}requirementsCoverage.html');" />
</h1>

<div class="workBack">

{* Update Form *}
<h2>{lang_get s='update_data'}</h2>

<div style="margin: 0px 20px;">
<form name="formSRSCreate" method="post" 
	action="lib/req/reqSpecView.php?idSRS={$arrSpec[0].id}">
<table class="common" style="width: 90%">
	<tr>
		<th>{lang_get s='title'}</th>
		<td><input type="text" name="title" size="50" maxlength="100" 
			value="{$arrSpec[0].title|escape}"/>
		</td>
	</tr>
	<tr>
		<th>{lang_get s='scope'}</th>
		<td>{$scope}</td>
	</tr>
	<tr>
		<th>{lang_get s='req_total'}</th>
		<td><input type="text" name="countReq" size="5" maxlength="5" 
			value="{$arrSpec[0].total_req}" /></td>
	</tr>
</table>

<div class="groupBtn">
	<input type="submit" name="updateSRS" value="{lang_get s='btn_update'}" />
	<input type="button" name="cancel" value="{lang_get s='btn_cancel'}" 
		onclick="javascript: location.href='lib/req/reqSpecView.php?idSRS={$arrSpec[0].id}';" />
</div>
</form>

</div>

</div>


</body>
</html>