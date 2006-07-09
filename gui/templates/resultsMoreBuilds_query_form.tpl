{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: resultsMoreBuilds_query_form.tpl,v 1.24 2006/07/09 05:56:39 kevinlevy Exp $
@author Francisco Mancardi - fm - start solving BUGID 97/98
20051022 - scs - removed ' in component id values
20051121 - scs - added escaping of tpname
20051203 - scs - added missing apo in lang_get
*}
{include file="inc_head.tpl"}

<body>

<!-- ============================== -->
<!-- 20060604 - KL - 1.7 development - temporarily commenting this out
<!-- ============================= -->
<!--
<table class="simple" style="width: 100%; text-align: center; margin-left: 0px;">
	{foreach key=build item=buildid from=$arrBuilds}
	{* by default select all builds*}
		{$build} {$buildid}
	{/foreach}				
</table>
-->

	{foreach key=id item=array from=$suiteList}
		<h3>suite id = {$id} </h3>
		<table class="simple" style="width: 100%; text-align: center; margin-left: 0px;">
		<tr><th>test case id</th><th>test case version id</th><th>build id</th><th>tester_id</th><th>execution_ts</th><th>status</th><th>notes</th></tr>

		{foreach key=id2 item=array2 from=$suiteList[$id]}

		{* by default select all builds*}
		<tr>
			<td>{$array2[0]}</td>
	 	     	<td>{$array2[1]}</td>
	   	     	<td>{$array2[2]}</td>
	   	     	<td>{$array2[3]}</td>
	   	     	<td>{$array2[4]}</td>
	   	     	<td>{$array2[5]}</td>
	   	     	<td>{$array2[6]}</td>
	   	     	<td>{$array2[7]}</td>
		</tr>
		
		{/foreach}
	</table>
	{/foreach}				


<!--
<h1>{lang_get s='test_plan_header'} {$testPlanName|escape}</h1>
<div class="workBack">	
<form action="lib/results/resultsMoreBuilds_buildReport.php" method='get'>
	<INPUT TYPE="HIDDEN" NAME="testplanid" VALUE="{$testplanid}"/>
	<INPUT TYPE="HIDDEN" NAME="testPlanName" VALUE="{$testPlanName|escape}" />
	<table class="simple" style="width: 100%; text-align: center; margin-left: 0px;">
		<tr><th>{lang_get s='select_builds_header'}</th><th>{lang_get s='select_components_header'}</th></tr>
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
						{* by default select all components *}
					{foreach key=componentid item=component_name from=$arrComponents}
						<option value="{$componentid}" selected>{$component_name|escape}</option>
					{/foreach}			
				</select>	
			</td>
		</tr>
    <tr><th>{lang_get s='select_keyword_header'}</th><th>{lang_get s='select_owner_header'}</th></tr>
		<tr><td>
        	        <select name="keyword" size=5>
			<option value="" selected>{lang_get s='do_not_query_by_keyword'}</option>
                        {section name=Row loop=$arrKeywords}
                        <option value="{$arrKeywords[Row].keyword|escape}">{$arrKeywords[Row].keyword|escape}</option>
                        {/section}
		</td>
			<td>
				<select name='owner' size=5 >
					<option value="" selected>{lang_get s='do_not_query_by_owner'}</option>
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
    <tr><th>{lang_get s='select_report_format_header'}</th><th>{lang_get s='select_last_result_header'} </th></tr>	
		<tr><td> 
			<select name='format' size=2>
				<option selected>{lang_get s='html_format'}</option>
				<option>{lang_get s='excel_format'}</option>
			</select>
		</td>
		<td> 
			<select name='lastStatus' size=5>
				<option selected>{lang_get s='last_status_any'}</option>
				<option>{lang_get s='last_status_passed'}</option>
				<option>{lang_get s='last_status_failed'}</option>
				<option>{lang_get s='last_status_blocked'}</option>
				<option>{lang_get s='last_status_not_run'}</option>
			</select>
		</td></tr>
	<tr>
		<td>
			<INPUT TYPE=submit VALUE='{lang_get s='submit_query'}'/>
		</td>
	</tr>
</table>
</form>
</div>
-->
</body>
</html>
