{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: tcSearchForm.tpl,v 1.6 2008/05/09 17:14:19 schlundus Exp $
Purpose: show form for search through test cases in test specification

rev :

*}
{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

{lang_get var="labels" 
          s='title_search_tcs,caption_search_form,th_tcid,th_tcversion,
             th_title,summary,steps,expected_results,keyword,custom_field,
             custom_field_value,btn_find'}


{include file="inc_head.tpl"}
<body>

<h1 class="title">{$mainCaption}</h1>

<div style="margin: 1px;">
<form method="post" action="lib/testcases/searchData.php" target="workframe">
	<table class="smallGrey">
		<caption>{$labels.caption_search_form}</caption>
		<tr>
			<td>{$labels.th_tcid}</td>
			<td><input type="text" name="targetTestCase" id="TCID"  size="{#TCID_SIZE#}" maxlength="{#TCID_MAXLEN#}" /></td>
		</tr>
		<tr>
			<td>{$labels.th_tcversion}</td>
			<td><input type="text" name="version"
			           size="{#VERSION_SIZE#}" maxlength="{#VERSION_MAXLEN#}" /></td>
		</tr>
		<tr>
			<td>{$labels.th_title}</td>
			<td><input type="text" name="name" size="{#TCNAME_SIZE#}" maxlength="{#TCNAME_MAXLEN#}" /></td>
		</tr>
		<tr>
			<td>{$labels.summary}</td>
			<td><input type="text" name="summary" 
			           size="{#SUMMARY_SIZE#}" maxlength="{#SUMMARY_MAXLEN#}" /></td>
		</tr>
		<tr>
			<td>{$labels.steps}</td>
			<td><input type="text" name="steps" 
			           size="{#STEPS_SIZE#}" maxlength="{#STEPS_MAXLEN#}" /></td>
		</tr>
		<tr>
			<td>{$labels.expected_results}</td>
			<td><input type="text" name="expected_results" 
			           size="{#RESULTS_SIZE#}" maxlength="{#RESULTS_MAXLEN#}" /></td>
		</tr>
		<tr>
			<td>{$labels.keyword}</td>
			<td><select  name="keyword_id">
					<option value="0">&nbsp;</option>
					{section name=Row loop=$keywords}
					<option value="{$keywords[Row]->dbID}">{$keywords[Row]->name|escape}</option>
				{/section}
				</select>
			</td>
		</tr>
		<tr>
      <td>{$labels.custom_field}</td>
			<td><select name="custom_field_id">
					<option value="0">&nbsp;</option>
					{foreach from=$design_cf key=cf_id item=cf}
						<option value="{$cf_id}">{$cf.name}</option>
					{/foreach}
				</select>
	  </tr>
	
	  <tr>
	   <td>{$labels.custom_field_value}</td>
     <td>
			  <input type="text" name="custom_field_value" 
			         size="{#CFVALUE_SIZE#}" maxlength="{#CFVALUE_MAXLEN#}"/>
			</td>
	  </tr>
	
	</table>
	
	
	<p style="padding-left: 20px;">
		<input type="submit" name="doSearch" value="{$labels.btn_find}" />
	</p>
</form>

</div>
</body>
</html>
