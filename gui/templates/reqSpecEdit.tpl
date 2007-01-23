{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: reqSpecEdit.tpl,v 1.7 2007/01/23 18:26:41 franciscom Exp $
Purpose: smarty template - edit a req specification

20070101 - franciscom - use of config_load
*}
{include file="inc_head.tpl"}

<body onload="document.forms[0].elements[0].focus()">
{assign var="cfg_section" value=$smarty.template|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

<h1><img src="{$smarty.const.TL_THEME_IMG_DIR}/sym_question.gif" 
	     title="{lang_get s='help'}: {lang_get s='req_spec'}" 
	     alt="{lang_get s='help'}: {lang_get s='req_spec'}" 
	     class="help" 
	     src="{$smarty.const.TL_THEME_IMG_DIR}/sym_question.gif" 
	     onclick="javascript:open_popup('{$helphref}requirementsCoverage.html');" />
	  {lang_get s='req_spec'}{$smarty.const.TITLE_SEP}{$arrSpec[0].title|escape}   
</h1>

<div class="workBack">
<h1>{lang_get s='edit'}</h1>

<div style="margin: 0px 20px;">
<form name="formSRSCreate" method="post" 
	action="lib/req/reqSpecView.php?idSRS={$arrSpec[0].id}">
 <table class="common" style="width: 90%">
	<tr>
		<th width="120px">{lang_get s='title'}</th>
		<td><input type="text" name="title" size="{#SRS_TITLE_SIZE#}" maxlength="{#SRS_TITLE_MAXLEN#}" 
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
		onclick="javascript: location.href=fRoot+'lib/req/reqSpecView.php?idSRS={$arrSpec[0].id}';" />
</div>
</form>

</div>

</div>


</body>
</html>