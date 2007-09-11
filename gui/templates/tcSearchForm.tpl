{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: tcSearchForm.tpl,v 1.8 2007/09/11 06:31:49 franciscom Exp $
Purpose: show form for search through test cases in test specification

20060428 - franciscom - added version input
*}
{assign var="cfg_section" value=$smarty.template|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

{include file="inc_head.tpl"}
<body>

<h1>{lang_get s='title_search_tcs'}</h1>

<div style="margin: 1px;">
<form method="post" action="lib/testcases/searchData.php" target="workframe">
	<table class="smallGrey">
		<caption>{lang_get s='caption_search_form'}</caption>
		<tr>
			<td>{lang_get s='th_tcid'}</td>
			<td><input type="text" name="TCID" size="{#TCID_SIZE#}" maxlength="{#TCID_MAXLEN#}" /></td>
		</tr>
		<tr>
			<td>{lang_get s='th_tcversion'}</td>
			<td><input type="text" name="version"
			           size="{#VERSION_SIZE#}" maxlength="{#VERSION_MAXLEN#}" /></td>
		</tr>
		<tr>
			<td>{lang_get s='th_title'}</td>
			<td><input type="text" name="name" size="{#TCNAME_SIZE#}" maxlength="{#TCNAME_MAXLEN#}" /></td>
		</tr>
		<tr>
			<td>{lang_get s='summary'}</td>
			<td><input type="text" name="summary" 
			           size="{#SUMMARY_SIZE#}" maxlength="{#SUMMARY_MAXLEN#}" /></td>
		</tr>
		<tr>
			<td>{lang_get s='steps'}</td>
			<td><input type="text" name="steps" 
			           size="{#STEPS_SIZE#}" maxlength="{#STEPS_MAXLEN#}" /></td>
		</tr>
		<tr>
			<td>{lang_get s='expected_results'}</td>
			<td><input type="text" name="expected_results" 
			           size="{#RESULTS_SIZE#}" maxlength="{#RESULTS_MAXLEN#}" /></td>
		</tr>
		<tr>
			<td>{lang_get s='keyword'}</td>
			<td><select  name="key">
					<option value="0"></option>
					{section name=Row loop=$arrKeys}
					<option value="{$arrKeys[Row].id}">{$arrKeys[Row].keyword|escape}</option>
				{/section}
				</select>
			</td>
		</tr>
	</table>
	<p style="padding-left: 20px;">
		<input type="submit" name="submit" value="{lang_get s='btn_find'}" />
	</p>
</form>

</div>
</body>
</html>