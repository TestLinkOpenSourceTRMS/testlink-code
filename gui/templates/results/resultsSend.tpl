{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: resultsSend.tpl,v 1.2 2008/05/06 06:26:12 franciscom Exp $ *}
{* Purpose: smarty template - send Test Report *}
{include file="inc_head.tpl"}
{*
	20051126 - scs - added escaping of tpname
*}
<body>

<h1 class="title">{$tpName|escape} {lang_get s='send_test_report'}</h1>

{if $message != "" }
	<p class='info'>{$message}</p>
{/if}

<div class="workBack">

<form method='post'>
<table class="simple" style="width: 100%; margin-left: 0px;">

	<tr><th>{lang_get s='mail_to'}</th><td><input name='to' type='text' size='75' /></td></tr>
	<tr><th>{lang_get s='mail_subject'}</th><td><input name='subject' type='text' size='75' 
		value="Test Report - {$tpName}" /></td></tr>
	<tr><th>{lang_get s='mail_body'}</th><td><textarea name='body' cols="50" rows="10"></textarea>
	<tr><th>{lang_get s='mail_report'}</th><td>
		
		<input type='radio' name='status' value='projAll' checked='checked' />
		{lang_get s='tp_status'}<br />
		
		<input type='radio' name='status' value='comAll' />
		{lang_get s='component'}
		<select name='comSelectAll'>
			{html_options options=$suites}
		</select>{lang_get s='status'}<br />
		
		<input type='radio' name='status' value='projBuild' />
		{lang_get s='tp_status_for_build'}
		<select name='buildProj'>
			{html_options options=$builds}
		</select><br />
		
		<input type='radio' name='status' value='comBuild' />
		{lang_get s='component'}
		<select name='comSelectBuild'>
			{html_options options=$suites}
		</select>{lang_get s='status_for_build'}
		<select name='buildCom'>
			{html_options options=$builds}
		</select>

	</td></tr>
</table>

<p><input type='checkbox' name='cc' value='yes'>{lang_get s='check_send_to_me'}</p>
<p><input type='submit' name='submit' value="{lang_get s='btn_send_report'}"></p>
</form>


</div>

</body>
</html>