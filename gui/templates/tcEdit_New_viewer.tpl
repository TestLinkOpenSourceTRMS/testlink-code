{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: tcEdit_New_viewer.tpl,v 1.3 2007/01/05 13:57:30 franciscom Exp $
Purpose: smarty template - create new testcase

20061231 - franciscom - viewer for tcEdit.tpl and tcNew.tpl
*}
 <p>
	<div class="labelHolder"><label for="testcase_name">{lang_get s='tc_title'}</label></div>
	<div>	
		<input type="text" name="testcase_name"
   	       size="{#TESTCASE_NAME_SIZE#}" 
           maxlength="{#TESTCASE_NAME_MAXLEN#}" 
		       value="{$tc.name|escape}"
			     title="{lang_get s='alt_add_tc_name'}"/>
  				{include file="error_icon.tpl" field="testcase_name"}
	<p>

	<div class="labelHolder">{lang_get s='summary'}</div>
	<div>{$summary}</div><p>
	
	<div class="labelHolder">{lang_get s='steps'}</div>
	<div>{$steps}</div><p>

	<div class="labelHolder">{lang_get s='expected_results'}</div>
	<div>{$expected_results}</div><p>

  {* Custom fields *}
   {if $cf neq ""}
     <p>
     <div class="custom_field_container">
     {$cf}
     </div>
     <p>
   {/if}

  <div>
  <a href={$gsmarty_href_keywordsView}>{lang_get s='tc_keywords'}</a>
	{include file="opt_transfer.inc.tpl" option_transfer=$opt_cfg}
	</div><p>
