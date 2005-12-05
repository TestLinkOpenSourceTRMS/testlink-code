{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: reqEdit.tpl,v 1.9 2005/12/05 00:11:06 kevinlevy Exp $ *}
{* Purpose: smarty template - create / edit a req *}
{* Author: Martin Havlat *}
{* Revisions:
20050828 - fm - fckeditor, localize_date
20050830 - MHT - result presentation updated
20051008 - scs - added escaping of tile/author
20051125 - scs - added escaping of titles for coverage
*}
{include file="inc_head.tpl"}

<body onload="document.forms[0].elements[0].focus()">

<h1>
	<img alt="{lang_get s='help'}: {lang_get s='req_spec'}" class="help" 
	src="icons/sym_question.gif" 
	onclick="javascript:open_popup('{$helphref}requirementsCoverage.html');" />
	{lang_get s='req_edit'}: {$arrReq.title|escape}
</h1>

<div class="workBack">

<div class="groupBtn" style="margin-bottom: 20px;">
	{if $modify_req_rights == "yes"}
	<input type="button" name="callUpdateReq" value="{lang_get s='btn_update'}" 
		onclick="javascript: formSRSUpdate.submit();" />
	<input type="button" name="callDeleteReq" value="{lang_get s='btn_delete'}" 
		onclick="javascript:; 
	if (confirm('{lang_get s='popup_delete_req'}'))
		{ldelim}formSRSDelete.submit();{rdelim};" />
	{/if}
	<input type="button" name="cancel" value="{lang_get s='btn_cancel'}" 
		onclick="javascript: location.href=fRoot+'lib/req/reqSpecView.php?idSRS={$arrSpec[0].id}';" />
</div>

<form name="formSRSUpdate" method="post" 
	action="lib/req/reqSpecView.php?idSRS={$arrSpec[0].id}">
<table class="common" style="width: 90%">
	<tr>
		<th>{lang_get s='req_doc_id'}</th>
		<td>{if $modify_req_rights == "yes"}
			<input type="text" name="reqDocId" size="20" maxlength="16"
			value="{$arrReq.req_doc_id|escape}"/>
			{else}
				{$arrReq.req_doc_id|escape}
			{/if}
		</td>
	</tr>
	<tr>
		<th>{lang_get s='title'}</th>
		<td>{if $modify_req_rights == "yes"}
			<input type="text" name="title" size="50" maxlength="100" 
			value="{$arrReq.title|escape}"/>
			{else}
				{$arrReq.title|escape}
			{/if}
		</td>
	</tr>
	<tr>
		<th>{lang_get s='scope'}</th>
		<td>{if $modify_req_rights == "yes"}
		    {* 20050826 - fm*}
				{$scope}
			{else}
				{$arrReq.scope}
			{/if}
		</td>
	</tr>
	<tr>
		<th>{lang_get s='status'}</th>
		<td>{if $modify_req_rights == "yes"}
				<select name="reqStatus">
				{html_options options=$selectReqStatus selected=$arrReq.status}
				</select>
			{else}
				{$selectReqStatus[$arrReq.status]}
			{/if}
		</td>
	</tr>
	<tr>
		<th>{lang_get s='coverage'}</th>
		<td>
			{section name=row loop=$arrReq.coverage}
				<a href="lib/testcases/archiveData.php?edit=testcase&data={$arrReq.coverage[row].id}">{$arrReq.coverage[row].id|escape}: {$arrReq.coverage[row].title|escape}</a><br />
			{sectionelse}
			<span>{lang_get s='req_msg_notestcase'}</span>
			{/section}
		</td>
	</tr>
</table>
	<input type="hidden" name="idReq" value="{$arrReq.id}" />
	<input type="hidden" name="reqStatus" value="{$arrReq.type}" />
	<input type="hidden" name="updateReq" />
</form>


<form name="formSRSDelete" method="post" 
	action="lib/req/reqSpecView.php?idSRS={$arrSpec[0].id}">
<input type="hidden" name="idReq" value="{$arrReq.id}" />
<input type="hidden" name="title" value="{$arrReq.title}" />
<input type="hidden" name="deleteReq" value="{lang_get s='btn_delete'}" />
</form>


<p>{lang_get s="Author"}: {$arrReq.author|escape} [{localize_date d=$arrReq.create_date}]</p>
{if $arrReq.id_modifier <> ''}
<p>{lang_get s="last_edit"}: {$arrReq.modifier|escape} [{localize_date d=$arrReq.modified_date}]</p>
{/if}

</div>


</body>
</html>
