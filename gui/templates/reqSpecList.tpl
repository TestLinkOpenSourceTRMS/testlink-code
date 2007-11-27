{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: reqSpecList.tpl,v 1.13 2007/11/27 07:46:53 franciscom Exp $ *}
{* Purpose: smarty template - create view and create a new req document *}
{include file="inc_head.tpl" jsTree="yes"}

<body>

<h1> 
 {lang_get s='help' var='common_prefix'}
 {lang_get s='req_spec' var="xx_alt"}
 {assign var="text_hint" value="$common_prefix: $xx_alt"}
 
 {include file="inc_help.tpl" help="requirementsCoverage" locale=$locale 
          alt="$text_hint" title="$text_hint"  style="float: right;"}
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
    		<td>
		      {if $arrSpec[rowSpec].total_req eq "0"}
      			{lang_get s='not_aplicable'}
      		  {else}
    			{$arrSpec[rowSpec].total_req|escape}
		      {/if}
    		</td>
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