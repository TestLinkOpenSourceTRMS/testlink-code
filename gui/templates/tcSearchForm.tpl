{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: tcSearchForm.tpl,v 1.2 2005/08/16 17:59:13 franciscom Exp $ *}
{* Purpose: smarty template - show form for search through test cases 
 * in test specification of the actual product
 *}
{include file="inc_head.tpl"}
<body>

<h1>{lang_get s='title_search_tcs'}</h1>

<div style="margin: 1px;">
<form method="post" action="lib/testcases/searchData.php" target="workframe">
	<table class="common">
		<caption>{lang_get s='caption_search_form'}</caption>
		<tr>
			<td>{lang_get s='th_tcid'}</td>
			<td><input type="text" size="15" name="TCID" /></td>
		</tr>
		<tr>
			<td>{lang_get s='th_title'}</td>
			<td><input type="text" size="35" name="title" /></td>
		</tr>
		<tr>
			<td>{lang_get s='summary'}</td>
			<td><input type="text" size="35" name="summary" /></td>
		</tr>
		<tr>
			<td>{lang_get s='steps'}</td>
			<td><input type="text" size="35" name="steps" /></td>
		</tr>
		<tr>
			<td>{lang_get s='expected_results'}</td>
			<td><input type="text" size="35" name="exresult" /></td>
		</tr>
		<tr>
			<td>{lang_get s='keyword'}</td>
			<td><select style="width:235px" name="key">
					<option value="none">{lang_get s='not_applied'}</option>
					{section name=Row loop=$arrKeys}
					<option value="{$arrKeys[Row].keyword|escape}">{$arrKeys[Row].keyword|escape}</option>
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