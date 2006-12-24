{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: reqEdit.tpl,v 1.14 2006/12/24 11:48:18 franciscom Exp $ *}
{* Purpose: smarty template - create / edit a req *}
{* Author: Martin Havlat *}
{* Revisions:
20050828 - fm - fckeditor, localize_date
20050830 - MHT - result presentation updated
20051008 - scs - added escaping of tile/author
20051125 - scs - added escaping of titles for coverage
20060105 - fm  - BUGID 329: Unnable to Change requirement type to "untestable"
20061223 - franciscom - title -> name
20061224 - franciscom - layout

*}
{include file="inc_head.tpl"}

<body onload="document.forms[0].elements[0].focus()">
{config_load file="input_dimensions.conf" section="reqEdit"} {* Constant definitions *}

<h1>
	<img alt="{lang_get s='help'}: {lang_get s='req_spec'}" class="help" 
	src="icons/sym_question.gif" 
	onclick="javascript:open_popup('{$helphref}requirementsCoverage.html');" />
	{lang_get s='req_edit'}: {$arrReq.title|escape}
</h1>

<div class="workBack">


<form name="formSRSUpdate" method="post" 
	action="lib/req/reqSpecView.php?idSRS={$arrSpec[0].id}">
<table class="common" style="width: 90%">
	<tr>
		<th colspan="2">{lang_get s='requirement_spec'} {$srs_title|escape}</th>
	</tr>

	<tr>
		<th>{lang_get s='req_doc_id'}</th>
		<td>{if $modify_req_rights == "yes"}
			<input type="text" name="reqDocId" 
  	         size="{#REQ_DOCID_SIZE#}" 
             maxlength="{#REQ_DOCID_MAXLEN#}" 
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
				<a href="lib/testcases/archiveData.php?edit=testcase&id={$arrReq.coverage[row].id}">
				{lang_get s='test_case_id'} {$arrReq.coverage[row].id|escape}: {$arrReq.coverage[row].name|escape}</a><br />
			{sectionelse}
			<span>{lang_get s='req_msg_notestcase'}</span>
			{/section}
		</td>
	</tr>
  <tr class="time_stamp_creation">
    <td colspan="2">&nbsp; </td>
	</tr>
  <tr class="time_stamp_creation">
    <td colspan="2">
    {lang_get s='title_created'}&nbsp;{localize_timestamp ts=$arrReq.creation_ts}&nbsp;
    {lang_get s='by'}&nbsp;{$arrReq.author|escape}     
    </td>
	</tr>
  {if $arrReq.modifier ne ""}
    <tr class="time_stamp_creation">
      <td colspan="2">
      {lang_get s='title_last_mod'}&nbsp;{localize_timestamp ts=$arrReq.modification_ts}&nbsp;
      {lang_get s='by'}&nbsp;{$arrReq.modifier|escape}     
      </td>
	  </tr>
  {/if}

</table>

{include file="inc_attachments.tpl"}

	<input type="hidden" name="idReq" value="{$arrReq.id}" />
	<input type="hidden" name="updateReq" />
		
	{* 20060105 - fm  - BUGID 329: Unnable to Change requirement type to "untestable"
	<input type="hidden" name="reqStatus" value="{$arrReq.type}" />	*}

</form>
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

<form name="formSRSDelete" method="post" 
	action="lib/req/reqSpecView.php?idSRS={$arrSpec[0].id}">
<input type="hidden" name="idReq" value="{$arrReq.id}" />
<input type="hidden" name="title" value="{$arrReq.title}" />
<input type="hidden" name="deleteReq" value="{lang_get s='btn_delete'}" />
</form>
</div>


</body>
</html>
