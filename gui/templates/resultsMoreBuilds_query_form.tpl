{*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: resultsMoreBuilds_query_form.tpl,v 1.54 2007/09/17 06:28:46 franciscom Exp $
@author Francisco Mancardi

rev :
     20070916 - franciscom - added hidden input to manage test plan id
     20070901 - franciscom - use config file and smarty date and time controls

*}
{assign var="cfg_section" value=$smarty.template|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

{include file="inc_head.tpl"}
<body>
<h1> {lang_get s='query_metrics_report'}</h1>
<div class="workBack">
{include file="inc_result_tproject_tplan.tpl" 
         arg_tproject_name=$tproject_name arg_tplan_name=$tplan_name}	


{* ------------------------------------------------------------------------------- *}
{* Calculate combo size *}
{if $build_qty > #BUILDS_COMBO_NUM_ITEMS# }
  {assign var="build_qty" value=#ASSIGNED_TESTERS_COMBO_NUM_ITEMS# }
{/if}

{if $testsuite_qty > #TESTSUITES_COMBO_NUM_ITEMS# }
  {assign var="testsuite_qty" value=#TESTSUITES_COMBO_NUM_ITEMS# }
{/if}

{if $keyword_qty > #KEYWORDS_COMBO_NUM_ITEMS# }
  {assign var="keyword_qty" value=#KEYWORDS_COMBO_NUM_ITEMS# }
{/if}

{* ------------------------------------------------------------------------------- *}



<form action="lib/results/resultsMoreBuilds_buildReport.php?build={$build}&amp;report_type={$report_type}" 
      method="POST">

  <input type="hidden" id="tplan_id" name="tplan_id" value={$tplan_id}>
  <div>
	<table class="simple" style="width: 100%; text-align: center; margin-left: 0px;">
		<tr>
			<th>{lang_get s='select_builds_header'}</th>
			<th>{lang_get s='select_components_header'}</th>
		</tr>
		<tr>
			<td>
				<select name="build[]" size="{$build_qty}" multiple="multiple">
					{foreach key=row item=buildid from=$arrBuilds}
						<option value="{$arrBuilds[$row].id}" selected="selected">{$arrBuilds[$row].name|escape}</option>
					{/foreach}
				</select>
			</td>
			<td>
       <select name="testsuite[]" size="{$testsuite_qty}" multiple="multiple">
					{foreach key=row item=tsuite_name from=$arrTestsuites}
						<option value="{$arrTestsuites[$row].id},{$arrTestsuites[$row].name}" 
						        selected="selected">{$arrTestsuites[$row].name|escape}</option>
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
				<select name="keyword" size="{$keyword_qty}" >
					{foreach key=keyword_id item=keyword_name from=$arrKeywords}
						<option value="{$keyword_id}" >{$arrKeywords[$keyword_id]|escape}</option>
					{/foreach}
				</select>
			</td>
			<td>
				<select name="owner">
					{foreach key=owner item=ownerid from=$arrOwners}
						{* by default the owner should be the current user *}
						<option value="{$owner}">{$ownerid|escape}</option>
					{/foreach}
				</select>
			</td>
		</tr>
		
		<tr>
			<th>{lang_get s='enter_start_time'}</th>
			<th>{lang_get s='enter_end_time'}</th>
		</tr>
		<tr>
			<td align="center">
       <table border='0'>
       <tr>
       <td>{lang_get s="date"}</td><td>{html_select_date prefix="start_" time=$selected_start_date
                         month_format='%m' start_year="-1" end_year="+1"
                         field_order=$gsmarty_html_select_date_field_order}</td>
       </tr>
       <tr>
       <td>{lang_get s="hour"}</td>
       <td align='left'>{html_select_time prefix="start_" display_minutes=false 
                                          time=$selected_start_time
                                          display_seconds=false use_24_hours=true}</td>
       </tr>
			 </table>
			</td>

			<td align="center">
       <table border='0'>
       <tr>
       <td>{lang_get s="date"}</td><td>{html_select_date prefix="end_" time=$selected_end_date
                         month_format='%m' start_year="-1" end_year="+1"
                         field_order=$gsmarty_html_select_date_field_order}</td>
       </tr>
       <tr>
       <td>{lang_get s="hour"}</td>
       <td align='left'>{html_select_time prefix="end_" display_minutes=false 
                                          time=$selected_end_time
                                          display_seconds=false use_24_hours=true}</td>
       </tr>
			 </table>
			</td>


		</tr>
		<!-- 
		KL - 20070627 -Functionality to allow query by executor or grep the notes field
		     Allows user to change what data / results are displayed in report 
		-->
			<tr>
				<th>{lang_get s='search_in_notes'}</th>
				<th>{lang_get s='executor'}</th>
			</tr>
			<tr>
				<td>
					<input type="text" name="search_notes_string"/>
				</td>
				<td>
					<select name="executor">
						{foreach key=executor item=executorid from=$arrOwners}
							{* by default the owner should be the current user *}
							<option value="{$executor}">{$executorid|escape}</option>
						{/foreach}
					</select>
				</td>
			</tr>
			<tr>
				<th>{lang_get s='select_last_result_header'}</th>
				<th>&nbsp;</th>
			</tr>
			<tr>
				<td>
					<select name="lastStatus[]" size="{#TCSTATUS_COMBO_NUM_ITEMS#}" multiple="multiple">
					{foreach key=status_code item=status_label from=$status_code_label}
						<option selected="selected" value="{$status_code}">{$status_label|escape}</option>
					{/foreach}
				<td>&nbsp;</td>
			</tr>

    </table>
    </div>
    
    <div>
    <table>
     <tr><th>{lang_get s='display_suite_summaries'}</th>
     <td>
					<select name="display_suite_summaries">
						<option value="1">{lang_get s='Yes'}</option>
						<option value="0" selected="selected">{lang_get s='No'}</option>
					</select>
		 </td>
		 </tr>
		 <tr>
     	<th>{lang_get s='display_query_params'} </th>
			
				<td>
					<select name="display_query_params">
						<option value="1">{lang_get s='Yes'}</option>
						<option value="0" selected="selected">{lang_get s='No'}</option>
					</select>
				</td>
		</tr>
		<tr>		
				<th>{lang_get s='display_totals'}</th>
	   		<td>
					<select name="display_totals">
						<option value="1">{lang_get s='Yes'}</option>
						<option value="0" selected="selected">{lang_get s='No'}</option>
					</select>
				</td>
			</tr>
		<tr>
			<td colspan="3">
				<input type="submit" value="{lang_get s='submit_query'}"/>
			</td>
		</tr>
</table>
</div>
</form>
</div>
</body>
</html>
