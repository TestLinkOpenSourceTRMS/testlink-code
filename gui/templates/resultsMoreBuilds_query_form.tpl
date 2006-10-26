{*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: resultsMoreBuilds_query_form.tpl,v 1.35 2006/10/26 21:25:10 kevinlevy Exp $
@author Francisco Mancardi - fm - start solving BUGID 97/98
20051022 - scs - removed ' in component id values
20051121 - scs - added escaping of tpname
20051203 - scs - added missing apo in lang_get
20060805 - kl - converting to 1.7 - listing the builds is slightly different
*}
{include file="inc_head.tpl"}
<body>
<h1>{lang_get s='test_plan_header'} {$testPlanName|escape}</h1>
<div class="workBack">
<form action="lib/results/resultsMoreBuilds_buildReport.php" method="POST">
	<table class="simple" style="width: 100%; text-align: center; margin-left: 0px;">
		<tr>
			<th>{lang_get s='select_builds_header'}</th>
			<th>{lang_get s='select_components_header'}</th>
		</tr>
		<tr>
			<td>
				<select name="build[]" size="10" multiple="multiple">
					{foreach key=row item=buildid from=$arrBuilds}
						{* by default select all builds*}
						<option value="{$arrBuilds[$row].id}" selected="selected">{$arrBuilds[$row].name|escape}</option>
					{/foreach}
				</select>
			</td>
			<td>
        	       	<select name="component[]" size="10" multiple="multiple">
					{* by default select all components *}
					{foreach key=row item=component_name from=$arrComponents}
						<option value="{$arrComponents[$row].id}" selected="selected">{$arrComponents[$row].name|escape}</option>
					{/foreach}
				</select>
			</td>
		</tr>
		<tr>
			<th>{lang_get s='select_keyword_header'}</th>
			<th>{lang_get s='select_owner_header'}</th>
		</tr>
		<tr>
			<td>
				   	<select name="keyword" size="10" >

						{foreach key=keyword_id item=keyword_name from=$arrKeywords}
							<option value="{$keyword_id}" >{$arrKeywords[$keyword_id]|escape}</option>
						{/foreach}
					</select>
			</td>
			<td>
				<select name="owner" size="5">

					{foreach key=owner item=ownerid from=$arrOwners}
						{* by default the owner should be the current user *}
						<option value="{$owner}">{$ownerid|escape}</option>
					{/foreach}
				</select>
			</td>
		</tr>
	    <tr>
		<!-- 10212006 - KL - commenting out until we have some other format besides HTML 
		
			<th>{lang_get s='select_report_format_header'}</th>
		-->
			<th>{lang_get s='select_last_result_header'} </th>
		</tr>
		<tr>
		<!-- 10212006 - KL - commenting out until we have some other format besides HTML 
			<td>
				<select name="format" size="2">
					<option selected="selected" value="HTML">{lang_get s='html_format'}</option>
					<option value="EXCEL">{lang_get s='excel_format'}</option>
				</select>
			</td>
			-->
			<td>
				<select name="lastStatus" size="5">
					<option selected="selected">{lang_get s='last_status_any'}</option>
					<option>{lang_get s='last_status_passed'}</option>
					<option>{lang_get s='last_status_failed'}</option>
					<option>{lang_get s='last_status_blocked'}</option>
					<option>{lang_get s='last_status_not_run'}</option>
				</select>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<input type="submit" value="{lang_get s='submit_query'}"/>
			</td>
		</tr>
	</tr>
</table>
</form>
</div>
</body>
</html>
