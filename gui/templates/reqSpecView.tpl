{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: reqSpecView.tpl,v 1.7 2005/10/09 18:13:48 schlundus Exp $ *}
{* 
   Purpose: smarty template - view a requirement specification
   Author: Martin Havlat 

   20050828 - fm - localize_date

   20050810 - am - added escaping of title/author
*}
{include file="inc_head.tpl" openHead="yes"}
{include file="inc_jsCheckboxes.tpl"}
</head>
<body>

<h1>
	<img alt="{lang_get s='help'}: {lang_get s='req_spec'}" class="help" 
	src="icons/sym_question.gif" 
	onclick="javascript:open_popup('{$helphref}requirementsCoverage.html');" />
	{lang_get s='req_edit_spec'}
</h1>

{* show SQL result *}
{include file="inc_update.tpl" result=$sqlResult item=$sqlItem name=$name action=$action}
	
<div class="workBack">

<div class="groupBtn">
<form method="post">
	<input type="hidden" name="idSRS" value="{$arrSpec[0].id}" />
	{if $modify_req_rights == "yes"}
	<input type="submit" name="createReq" value="{lang_get s='btn_req_create'}" />
	<input type="submit" name="editSRS" value="{lang_get s='btn_edit_spec'}" />
	<input type="button" name="deleteSRS" value="{lang_get s='btn_delete_spec'}"
		onclick="javascript:; 
		if (confirm('{lang_get s="popup_sure_delete"} {$arrSpec[0].title}?')){ldelim} 
		location.href=fRoot+'lib/req/reqSpecList.php?deleteSRS={$arrSpec[0].title}&idSRS={$arrSpec[0].id}';{rdelim};"/>
	{/if}
	<input type="button" name="printSRS" value="{lang_get s='btn_print'}"
		onclick="javascript: window.open('{$basehref}lib/req/reqSpecPrint.php?idSRS={$arrSpec[0].id}', 
		'_blank', 'left=100,top=50,fullscreen=no,resizable=yes,toolbar=no,status=no,menubar=no,scrollbars=yes,directories=no,location=no,width=600,height=650');" />
	<input type="button" name="analyse" value="{lang_get s='btn_analyse'}"
		onclick="javascript: location.href=fRoot+'lib/req/reqSpecAnalyse.php?idSRS={$arrSpec[0].id}';" />
	{if $modify_req_rights == "yes"}
	<input type="button" name="importReq" value="{lang_get s='btn_import'}"
		onclick="javascript: location.href=fRoot+'lib/req/reqImport.php?idSRS={$arrSpec[0].id}';" />
	{/if}
	<input type="button" name="backToSRSList" value="{lang_get s='btn_spec_list'}" 
		onclick="javascript: location.href=fRoot+'lib/req/reqSpecList.php';" />
</form>
</div>

<p class="bold">{lang_get s="title"}: {$arrSpec[0].title|escape}</p>
<div class="tree" style="padding-left: 15px;">{$arrSpec[0].scope}</div>
{if $arrSpec[0].total_req != 'n/a'}
<p>{lang_get s="req_total_count"}: {$arrSpec[0].total_req}</p>
{/if}
<p>{lang_get s="Author"}: {$arrSpec[0].author|escape} [{localize_date d=$arrSpec[0].create_date}]</p>
{if $arrSpec[0].id_modifier <> ''}
<p>{lang_get s="last_edit"}: {$arrSpec[0].modifier|escape} [{localize_date d=$arrSpec[0].modified_date}]</p>
{/if}
</div>


{* existing REQs *}	
<div class="workBack">
<h2>{lang_get s="req_title_list"}</h2>

<form id="frmReqList" method="post">
<table class="simple">
	<tr>
		{if $modify_req_rights == "yes"}<th style="width: 15px;"></th>{/if}
		<th>{lang_get s="title"}</th>
		<th>{lang_get s="scope"}</th>
	</tr>
	{section name=row loop=$arrReq}
	<tr>
		{if $modify_req_rights == "yes"}<td><input type="checkbox" name="{$arrReq[row].id}" /></td>{/if}
		<td><span class="bold"><a href="lib/req/reqSpecView.php?editReq={$arrReq[row].id}&idSRS={$arrSpec[0].id}">
			{$arrReq[row].title|escape}</a></span></td>
		<td>{$arrReq[row].scope|truncate:100|regex_replace:"/<.*>/":" "}</td>
	</tr>
	{sectionelse}
	<tr><td></td><td><span class="bold">{lang_get s='req_msg_norequirement'}</span></td></tr>
	{/section}
</table>

{if $modify_req_rights == "yes"}
<div class="groupBtn">
	<input type="button" name="checkAll" value="{lang_get s='btn_check_all'}" 
		onclick="javascript: box('frmReqList', true);" />
	<input type="button" name="clearAll" value="{lang_get s='btn_uncheck_all'}" 
		onclick="javascript: box('frmReqList', false);" />
	
	<select name="multiAction" onchange="this.form.submit();">
		<option>{lang_get s='checked'}:</option>
		<option name="multiCreate">{lang_get s='req_select_create_tc'}</option>
		<option name="multiDelete">{lang_get s='req_select_delete'}</option>
	</select>
</div>
{/if}
</form>

</div>

</body>
</html>