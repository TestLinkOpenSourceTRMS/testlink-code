{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: reqSpecList.tpl,v 1.4 2005/11/26 13:27:24 schlundus Exp $ *}
{* Purpose: smarty template - create view and create a new req document *}
{include file="inc_head.tpl"}
{*
20051125 - scs - added escaping of productnames
*}

<body>

<h1> 
	<img alt="{lang_get s='help'}: {lang_get s='req_spec'}" class="help" 
	src="icons/sym_question.gif" 
	onclick="javascript: open_popup('{$helphref}requirementsCoverage.html');" />
	{$productName|escape} {lang_get s='req_spec'}
</h1>


{* show SQL result *}
{include file="inc_update.tpl" result=$sqlResult item="Requirements Specification" name=$name action=$action}
	
<div class="workBack">

{if $modify_req_rights == 'yes'}
<div class="groupBtn">
	<input type="button" name="createSRS" value="{lang_get s='btn_create'}" 
		onclick="javascript: location.href=fRoot + 'lib/req/reqSpecList.php?createForm=';" />
	{if $arrSpecCount > 0}
	<input type="button" name="assign" value="{lang_get s='btn_assign_tc'}" 
		onclick="javascript: location.href=fRoot + 'lib/general/frmWorkArea.php?feature=assignReqs';" />
	{/if}
</div>
{/if}

{* existing docs *}	
<h2>{lang_get s='req_list_docs'}</h2>

<table class="simple" style="width: 90%">
	<tr>
		<th>{lang_get s='title'}</th>
		<th>{lang_get s='scope'}</th>
		<th style="width: 30px;">{lang_get s='req_total'}</th>
	</tr>
	{section name=rowSpec loop=$arrSpec}
	<tr>
		<td><span class="bold"><a href="lib/req/reqSpecView.php?idSRS={$arrSpec[rowSpec].id}">
			{$arrSpec[rowSpec].title|escape}</a></span></td>
		<td>{$arrSpec[rowSpec].scope|truncate:190|regex_replace:"/<.*>/":" "}</td>
		<td>{$arrSpec[rowSpec].total_req|escape}</td>
	</tr>
	{sectionelse}
	<tr><td><span class="bold">{lang_get s='no_docs'}</span></td></tr>
	{/section}
</table>


</div>

</body>
</html>