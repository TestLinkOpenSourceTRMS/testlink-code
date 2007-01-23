{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: reqSpecList.tpl,v 1.8 2007/01/23 18:26:41 franciscom Exp $ *}
{* Purpose: smarty template - create view and create a new req document *}
{include file="inc_head.tpl"}
{*
20051125 - scs - added escaping of productnames
20051202 - scs - fixed 211
20061007 - franciscom - layout changes
*}

<body>

<h1> 
	<img src="{$smarty.const.TL_THEME_IMG_DIR}/sym_question.gif"
	     title="{lang_get s='help'}: {lang_get s='req_spec'}" 
	     alt="{lang_get s='help'}: {lang_get s='req_spec'}" 
	     class="help" 
       onclick="javascript: open_popup('{$helphref}requirementsCoverage.html');" />
	{lang_get s='req_spec'}{$smarty.const.TITLE_SEP_TYPE3}
	{lang_get s='testproject'}{$smarty.const.TITLE_SEP}{$productName|escape} 
</h1>


	
<div class="workBack">

{include file="inc_update.tpl" result=$sqlResult item="SRS" name=$name action=$action}



  <div id="SRS_list">
  {* existing docs *}	
  {if $arrSpec ne ""}
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
    		<td>{$arrSpec[rowSpec].scope|strip_tags|strip|truncate:190}</td>
    		<td>{$arrSpec[rowSpec].total_req|escape}</td>
    	</tr>
    	{sectionelse}
    	<tr><td><span class="bold">{lang_get s='no_docs'}</span></td></tr>
    	{/section}
    </table>
  {/if}  
  </div>

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
  
</div>

</body>
</html>