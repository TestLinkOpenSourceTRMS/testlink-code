{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: reqSpecView.tpl,v 1.19 2006/10/16 10:36:11 franciscom Exp $ *}
{* 
   Purpose: smarty template - view a requirement specification
   Author: Martin Havlat 

20050828 - fm - localize_date
20050810 - scs - added escaping of title/author
20051125 - scs - removed title for the deling of SRS
20051202 - scs - fixed 211
20061007 - franciscom - layout changes
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

{literal}
<script type="text/javascript">
{/literal}
var warning_delete_requirements = "{lang_get s='warning_delete_requirements'}";
{literal}
</script>
{/literal}


{* show SQL result *}
{include file="inc_update.tpl" result=$sqlResult item=$sqlItem name=$name action=$action}


	
<div class="workBack">

<div class="groupBtn">
<form method="post">
	<input type="hidden" name="idSRS" value="{$arrSpec[0].id}" />
	{if $modify_req_rights == "yes"}
	<input type="submit" name="editSRS" value="{lang_get s='btn_edit_spec'}" />
	<input type="button" name="deleteSRS" value="{lang_get s='btn_delete_spec'}"
		onclick="javascript:; 
		if (confirm('{lang_get s="popup_sure_delete"}')){ldelim} 
		location.href=fRoot+'lib/req/reqSpecList.php?deleteSRS=1&amp;idSRS={$arrSpec[0].id}';{rdelim};"/>
	{/if}
	<input type="button" name="printSRS" value="{lang_get s='btn_print'}"
		onclick="javascript: window.open('{$basehref}lib/req/reqSpecPrint.php?idSRS={$arrSpec[0].id}', 
		'_blank', 'left=100,top=50,fullscreen=no,resizable=yes,toolbar=no,status=no,menubar=no,scrollbars=yes,directories=no,location=no,width=600,height=650');" />
	<input type="button" name="analyse" value="{lang_get s='btn_analyse'}"
		onclick="javascript: location.href=fRoot+'lib/req/reqSpecAnalyse.php?idSRS={$arrSpec[0].id}';" />
	<input type="button" name="backToSRSList" value="{lang_get s='btn_spec_list'}" 
		onclick="javascript: location.href=fRoot+'lib/req/reqSpecList.php';" />
</form>
</div>

<p class="bold">{lang_get s="title"} {$arrSpec[0].title|escape}</p>
<div class="tree" style="padding-left: 15px;">{$arrSpec[0].scope}</div>
{if $arrSpec[0].total_req != 0}
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

<form id="frmReqList" enctype="multipart/form-data" method="post">

  {if $modify_req_rights == "yes"}
	  <input type="submit" name="createReq" value="{lang_get s='btn_req_create'}" />
	  <input type="button" name="importReq" value="{lang_get s='btn_import'}"
		       onclick="javascript: location.href=fRoot+'lib/req/reqImport.php?idSRS={$arrSpec[0].id}';" />
    &nbsp;
  {/if}	

{* ------------------------------------------------------------------------------------------ *}
{if $arrReq ne ''}  {* There are requirements in the SRS *}
  	<input type="submit" name="exportAll" value="{lang_get s='btn_export_reqs'}"> 
  	<select name="exportType">
  		{html_options options=$exportTypes}
  	</select>
  
  <table class="simple">
  	<tr>
  		{if $modify_req_rights == "yes"}<th style="width: 15px;"></th>{/if}
  		<th>{lang_get s="req_doc_id"}</th>
  		<th>{lang_get s="title"}</th>
  		<th>{lang_get s="scope"}</th>
  	</tr>
  	{section name=row loop=$arrReq}
  	<tr>
  	  {* 20060110 - fm - managing checkboxes as array and added value *}
  		{if $modify_req_rights == "yes"}<td><input type="checkbox" name="req_id_cbox[{$arrReq[row].id}]" 
  		                                           value="{$arrReq[row].id}"/></td>{/if}
  		<td><span class="bold">{$arrReq[row].req_doc_id|escape}</span></td>
  		<td><span class="bold"><a href="lib/req/reqSpecView.php?editReq={$arrReq[row].id}&amp;idSRS={$arrSpec[0].id}">
  		{$arrReq[row].title|escape}</a></span></td>
  		<td>{$arrReq[row].scope|strip_tags|strip|truncate:100}</td>
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
  
   <input type="submit" name="create_tc_from_req" value="{lang_get s='req_select_create_tc'}" />
   <input type="submit" onclick="return confirm(warning_delete_requirements)" name="req_select_delete" value="{lang_get s='req_select_delete'}" />
  </div>
  {/if}

{/if}  
{* ------------------------------------------------------------------------------------------ *}
</form>

</div>

{if $js_msg neq ""}
<script type="text/javascript">
alert("{$js_msg}");
</script>
{/if}


</body>
</html>