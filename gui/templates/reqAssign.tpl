{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: reqAssign.tpl,v 1.5 2005/10/21 22:17:58 asielb Exp $ *}
{* Purpose: smarty template - assign REQ to one test case *}
{*
20051008 - am - removed escaping of scope
*}
{include file="inc_head.tpl"}

<body>

<h1>
	<img alt="{lang_get s='help'}: {lang_get s='req_spec'}" class="help" 
	src="icons/sym_question.gif" style="float: right;"
	onclick="javascript:open_popup('{$helphref}requirementsCoverage.html');" />
	{lang_get s='req_title_assign'} {$tcTitle}
</h1>

{include file="inc_update.tpl" result=$sqlResult action=$action item="Requirement"}

<div class="workBack">
<form method="post">{lang_get s='req_spec'}:
	<select name="idSRS" onchange="form.submit()">
	{html_options options=$arrReqSpec selected=$selectedReqSpec}</select>
</form>
</div>


<div class="workBack">
<h2>{lang_get s='req_title_assigned'}</h2>
<form id="reqList" method="post">
<input type="hidden" name="idSRS" value="{$selectedReqSpec}" />
<table class="simple">
	<tr>
		<th style="width: 15px;"></th>
		<th>{lang_get s="req_doc_id"}</th>
		<th>{lang_get s="req"}</th>
		<th>{lang_get s="scope"}</th>
	</tr>
	{section name=row loop=$arrAssignedReq}
	<tr>
		<td><input type="checkbox" name="{$arrAssignedReq[row].id}" /></td>
		<td><span class="bold">{$arrAssignedReq[row].req_doc_id|escape}</span></td>
		<td><span class="bold"><a href="lib/req/reqSpecView.php?editReq={$arrAssignedReq[row].id}&idSRS={$selectedReqSpec}">
			{$arrAssignedReq[row].title|escape}</a></span></td>
		<td>{$arrAssignedReq[row].scope|truncate:30}</td>
	</tr>
	{sectionelse}
	<tr><td></td><td><span class="bold">{lang_get s='req_msg_norequirement'}</span></td></tr>
	{/section}
</table>
{if $smarty.section.row.total > 0}
	<div class="groupBtn">
		<input type="submit" name="unassign" value="{lang_get s='btn_unassign'}" />
	</div>
{/if}
</form>
</div>

<div class="workBack">
<h2>{lang_get s='req_title_unassigned'}</h2>
<form id="reqList" method="post">
<input type="hidden" name="idSRS" value="{$selectedReqSpec}" />
<table class="simple">
	<tr>
		<th style="width: 15px;"></th>
		<th>{lang_get s="req_doc_id"}</th>
		<th>{lang_get s="req"}</th>
		<th>{lang_get s="scope"}</th>
	</tr>
	{section name=row2 loop=$arrUnassignedReq}
	<tr>
		<td><input type="checkbox" name="{$arrUnassignedReq[row2].id}" /></td>
		<td><span class="bold">{$arrUnassignedReq[row2].req_doc_id|escape}</span></td>
		<td><span class="bold"><a href="lib/req/reqSpecView.php?editReq={$arrUnassignedReq[row2].id}&idSRS={$selectedReqSpec}">
			{$arrUnassignedReq[row2].title|escape}</a></span></td>
		<td>{$arrUnassignedReq[row2].scope|truncate:30}</td>
	</tr>
	{sectionelse}
	<tr><td></td><td><span class="bold">{lang_get s='req_msg_norequirement'}</span></td></tr>
	{/section}
</table>
<div class="groupBtn">
	<input type="submit" name="assign" value="{lang_get s='btn_assign'}" />
</div>
</form>
</div>


</body>
</html>