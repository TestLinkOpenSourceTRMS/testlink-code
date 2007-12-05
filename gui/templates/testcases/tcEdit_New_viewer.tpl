{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: tcEdit_New_viewer.tpl,v 1.3 2007/12/05 07:47:00 franciscom Exp $
Purpose: smarty template - create new testcase

20061231 - franciscom - viewer for tcEdit.tpl and tcNew.tpl
*}

{* ---------------------------------------------------------------- *}
{* Steps and results Layout management *}
{assign var="layout1" value="<br />"}
{assign var="layout2" value="<br />"}
{assign var="layout3" value="<br />"}

{if $gsmarty_spec_cfg->steps_results_layout == 'horizontal'}
  {assign var="layout1" value='<br /><table width="100%"><tr><td width="50%">'}
  {assign var="layout2" value='</td><td width="50%">'}
	{assign var="layout3" value="</td></tr></table><br />"}
{/if}
{* ---------------------------------------------------------------- *}



 <br/>
	<div class="labelHolder"><label for="testcase_name">{lang_get s='tc_title'}</label></div>
	<div>	
		<input type="text" name="testcase_name" id="testcase_name"
   	       size="{#TESTCASE_NAME_SIZE#}" 
           maxlength="{#TESTCASE_NAME_MAXLEN#}" 
		       value="{$tc.name|escape}"
			     title="{lang_get s='alt_add_tc_name'}"/>
  				{include file="error_icon.tpl" field="testcase_name"}
		  <br/><br/>

			<div class="labelHolder">{lang_get s='summary'}</div>
			<div>{$summary}</div>
      {$layout1}
			<div class="labelHolder">{lang_get s='steps'}</div>
			<div>{$steps}</div>
			{$layout2}
			<div class="labelHolder">{lang_get s='expected_results'}</div>
			<div>{$expected_results}</div>
			{$layout3}
			
		<div class="labelHolder">{lang_get s='execution_type'}
		  <select name="exec_type">
    	  {html_options options=$execution_types selected=$tc.execution_type}
    	</select>
    </div>
    <br />

	     
	  {* Custom fields *}
	   {if $cf neq ""}
		     <br/>
		     <div class="custom_field_container">
	     {$cf}
	     </div>
	   {/if}
	</div>
  <div>
  <a href={$gsmarty_href_keywordsView}>{lang_get s='tc_keywords'}</a>
	{include file="opt_transfer.inc.tpl" option_transfer=$opt_cfg}
	</div>
