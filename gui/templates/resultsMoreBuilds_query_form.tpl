{*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: resultsMoreBuilds_query_form.tpl,v 1.43 2007/06/22 04:31:10 kevinlevy Exp $
@author Francisco Mancardi - fm - start solving BUGID 97/98
20051022 - scs - removed ' in component id values
20051121 - scs - added escaping of tpname
20051203 - scs - added missing apo in lang_get
20060805 - kl - converting to 1.7 - listing the builds is slightly different
*}
{include file="inc_head.tpl"}
<body>
<h1> {$testPlanName|escape} {lang_get s='query_metrics_report'}</h1>
<div class="workBack">
<form action="lib/results/resultsMoreBuilds_buildReport.php?report_type={$report_type}" method="POST">
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
						<option value="{$arrComponents[$row].id},{$arrComponents[$row].name}" selected="selected">{$arrComponents[$row].name|escape}</option>
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
		
		<!-- 
		KL - 06132006 - Functionality to allow query by start and end time		

		<tr>
			<th>{lang_get s='enter_start_time'}</th>
			<th>{lang_get s='enter_end_time'}</th>
		</tr>
		<tr>
			<td>
			     <table>
			     <tr><td>year</td><td> <input type="text" size="4" maxlength="4" name="start_year" value="2000"/></td></tr>
			     <tr><td>month</td><td><input type="text" size="2" maxlength="2"name="start_month" value="01"/></td></tr>
			     <tr><td>day</td><td><input type="text" size="2" maxlength="2"name="start_day" value="01"/></td></tr>
			     <tr><td>hour</td><td><input type="text" size="2" maxlength="2"name="start_hour" value="00"/></td></tr>
				</table>
			</td>
			<td>
			     <table>
			     <tr><td>year</td><td> <input type="text" size="4" maxlength="4" name="end_year" value="2010"/></td></tr>
			     <tr><td>month</td><td><input type="text" size="2" maxlength="2" name="end_month" value="01"/></td></tr>
			     <tr><td>day</td><td><input type="text" size="2" maxlength="2" name="end_day" value="01"/></td></tr>
			     <tr><td>hour</td><td><input type="text"  size="2" maxlength="2"name="end_hour" value="00"/></td></tr>
				</table>
			</td>
		</tr>
		-->
		<!-- 
		KL - 06132006 - Functionality to allow query by executor or grep the notes field

		<tr>
			<th>{lang_get s='search_in_notes'}</th>
			<th>{lang_get s='executor'}</th>
		</tr>
		
		<tr>
			<td>
				<input type="text" name="search_notes_string"/>
			</td>
			<td>
				<select name="executor" size="5">
					{foreach key=executor item=executorid from=$arrOwners}
						{* by default the owner should be the current user *}
						<option value="{$executor}">{$executorid|escape}</option>
					{/foreach}
				</select>
			</td>
		</tr>
		-->
<!-- KL - 20070220 - commented out until fixed
	    <tr>
			<th>{lang_get s='select_last_result_header'} </th>
		</tr>
		<tr>

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
-->
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
