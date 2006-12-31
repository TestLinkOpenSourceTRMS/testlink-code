{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: tcEdit_New_viewer.tpl,v 1.1 2006/12/31 18:20:49 franciscom Exp $
Purpose: smarty template - create new testcase

20061231 - franciscom - viewer for tcEdit.tpl and tcNew.tpl
*}
 <p>
	<div class="labelHolder"><label for="name">{lang_get s='tc_title'}</label></div>
	<div>	
		<input type="text" name="name"
   	       size="{#TESTCASE_NAME_SIZE#}" 
           maxlength="{#TESTCASE_NAME_MAXLEN#}" 
		       value="{$tc.name|escape}"
			     title="{lang_get s='alt_add_tc_name'}"/>
	<p>

	<div class="labelHolder">{lang_get s='summary'}</div>
	<div>{$summary}</div><p>
	
	<div class="labelHolder">{lang_get s='steps'}</div>
	<div>{$steps}</div><p>

	<div class="labelHolder">{lang_get s='expected_results'}</div>
	<div>{$expected_results}</div><p>

  <div>
  <a href={$gsmarty_href_keywordsView}>{lang_get s='tc_keywords'}</a>
	{include file="opt_transfer.inc.tpl" option_transfer=$opt_cfg}
	</div><p>
