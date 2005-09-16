{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: resultsMoreBuilds_query_form.tpl,v 1.14 2005/09/16 20:38:49 kevinlevy Exp $
@author Francisco Mancardi - fm - start solving BUGID 97/98
*}
{include file="inc_head.tpl"}

<body>
<h1>{lang_get s='resultsMoreBuilds_query_form_test_plan_header'} {$testPlanName}</h1>
<div class="workBack">	
<form action="lib/results/resultsMoreBuilds_buildReport.php" method='get'>
	<INPUT TYPE=HIDDEN NAME=projectid VALUE={$projectid}>
	<INPUT TYPE=HIDDEN NAME=testPlanName VALUE="{$testPlanName}">
	<table class="simple" style="width: 100%; text-align: center; margin-left: 0px;">
		<tr><th>{lang_get s='resultsMoreBuilds_query_form_select_builds_header'}</th><th>{lang_get s='resultsMoreBuilds_query_form_select_components_header}</th></tr>
		<tr>
			<td>
				<select name='build[]' size=10 multiple>
					{foreach key=build item=buildid from=$arrBuilds}
						{* by default select all builds*}
						<option value="{$build}" selected>{$buildid|escape}</option>
					{/foreach}				
				</select>
			</td>
			<td>
        	        	<select name='component[]' size=10 multiple>
					<option value="*" selected>{lang_get s='resultsMoreBuilds_query_form_components_selection_all'}</option>
					{foreach key=component item=componentid from=$arrComponents}
						<option value="'{$componentid}'">{$componentid|escape}</option>
					{/foreach}			
				</select>	
			</td>
		</tr>
    <tr><th>{lang_get s='resultsMoreBuilds_query_form_select_keyword_header'}</th><th>{lang_get s='resultsMoreBuilds_query_form_select_owner_header'}</th></tr>
		<tr><td>
        	        <select name="keyword" size=5>
			<option value="" selected>{lang_get s='resultsMoreBuilds_query_form_do_not_query_by_keyword'}</option>
                        {section name=Row loop=$arrKeywords}
                        <option value="{$arrKeywords[Row].keyword|escape}">{$arrKeywords[Row].keyword|escape}</option>
                        {/section}
		</td>
			<td>
				<select name='owner' size=5 >
					<option value="" selected>{lang_get s='resultsMoreBuilds_query_form_do_not_query_by_owner'}</option>
					{foreach key=owner item=ownerid from=$arrOwners}
						{* by default the owner should be the current user *}
						<option value="{$ownerid|escape}">{$ownerid|escape}</option>
					{/foreach}				
				</select>
			</td>
		</tr>
    <tr></tr>
		<tr>

		</tr>
    <tr><th>{lang_get s='resultsMoreBuilds_query_form_select_report_format_header'}</th><th>{lang_get s='resultsMoreBuilds_query_form_select_last_result_header'} </th></tr>	
		<tr><td> 
			<select name='format' size=2>
				<option selected>{lang_get s='resultsMoreBuilds_query_form_html_format'}</option>
				<option>{lang_get s='resultsMoreBuilds_query_form_excel_format'}</option>
			</select>
		</td>
		<td> 
			<select name='lastStatus' size=5>
				<option selected>{lang_get s='resultsMoreBuilds_query_form_last_status_any'}</option>
				<option>{lang_get s='resultsMoreBuilds_query_form_last_status_passed'}</option>
				<option>{lang_get s='resultsMoreBuilds_query_form_last_status_failed'}</option>
				<option>{lang_get s='resultsMoreBuilds_query_form_last_status_blocked'}</option>
				<option>{lang_get s='resultsMoreBuilds_query_form_last_status_not_run'}</option>
			</select>
		</td></tr>
	<tr>
		<td>
			<INPUT TYPE=submit VALUE='{lang_get s='resultsMoreBuilds_query_form_submit_query'}'/>
		</td>
	</tr>
</table>
</form>
</div>

</body>
</html>
