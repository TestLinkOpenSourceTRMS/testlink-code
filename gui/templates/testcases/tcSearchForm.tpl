{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: tcSearchForm.tpl,v 1.2 2007/12/08 19:10:18 schlundus Exp $
Purpose: show form for search through test cases in test specification

rev :
     BUGID 
*}
{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
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
					{section name=Row loop=$keywords}
					<option value="{$keywords[Row]->m_dbID}">{$keywords[Row]->m_name|escape}</option>
				{/section}
				</select>
			</td>
		</tr>
		<tr>
      <td>{lang_get s='custom_field'}</td>
			<td><select name="custom_field_id">
					<option value="0"></option>
					{foreach from=$design_cf key=cf_id item=cf}
						<option value="{$cf_id}">{$cf.name}</option>
					{/foreach}
				</select>
	  </tr>
	
	  <tr>
	   <td>{lang_get s='custom_field_value'}</td>
     <td>
			  <input type="text" name="custom_field_value" 
			         size="{#CFVALUE_SIZE#}" maxlength="{#CFVALUE_MAXLEN#}"/>
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