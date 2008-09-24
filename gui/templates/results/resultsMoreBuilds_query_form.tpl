{*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: resultsMoreBuilds_query_form.tpl,v 1.10 2008/09/24 20:17:54 schlundus Exp $
@author Francisco Mancardi

rev :
     20080524 - franciscom - layout changes
                             BUGID 1430
     20080517 - franciscom - refactoring
     20070916 - franciscom - added hidden input to manage test plan id
     20070901 - franciscom - use config file and smarty date and time controls

*}
{lang_get var="labels
			s='enter_start_time,enter_end_time,date,hour,Yes,submit_query,
			   select_builds_header,select_components_header,report_display_options,
			   display_suite_summaries,display_test_cases,display_query_params,
			   display_totals,
			   search_in_notes,executor,No,query_metrics_report'}
			   


{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

{include file="inc_head.tpl"}
<body>
<h1 class="title"> {$labels.query_metrics_report}</h1>
<div class="workBack">
{include file="inc_result_tproject_tplan.tpl" 
         arg_tproject_name=$gui->tproject_name arg_tplan_name=$gui->tplan_name}	


{* ------------------------------------------------------------------------------- *}
{* Calculate combo size *}
{if $gui->builds->qty > #BUILDS_COMBO_NUM_ITEMS# }
  {assign var="build_qty" value={#BUILDS_COMBO_NUM_ITEMS#} }
{else}
  {assign var="build_qty" value=$gui->builds->qty }
{/if}

{if $gui->testsuites->qty > #TSUITES_COMBO_NUM_ITEMS# }
  {assign var="testsuite_qty" value=#TSUITES_COMBO_NUM_ITEMS# }
{else}
  {assign var="testsuite_qty" value=$gui->testsuites->qty }
{/if}

{if $gui->keywords->qty > #KEYWORDS_COMBO_NUM_ITEMS# }
  {assign var="keyword_qty" value=#KEYWORDS_COMBO_NUM_ITEMS# }
{else}
  {assign var="keyword_qty" value=$gui->keywords->qty }
{/if}

{* ------------------------------------------------------------------------------- *}



<form action="lib/results/resultsMoreBuilds_buildReport.php?report_type={$gui->report_type}" 
      method="post">

  <input type="hidden" id="tplan_id" name="tplan_id" value="{$gui->tplan_id}" />
  <div>
	<table class="simple" style="width: 100%; text-align: center; margin-left: 0px;">
		<tr>
			<th>{$labels.select_builds_header}</th>
			<th>{$labels.select_components_header}</th>
		</tr>
		<tr>
			<td>
				<select name="build[]" size="{$build_qty}" multiple="multiple">
					{foreach key=row item=buildid from=$gui->builds->items}
						<option value="{$gui->builds->items[$row].id}" selected="selected">{$gui->builds->items[$row].name|escape}</option>
					{/foreach}
				</select>
			</td>
			<td>
       <select name="testsuite[]" size="{$testsuite_qty}" multiple="multiple">
					{foreach key=row item=tsuite_name from=$gui->testsuites->items}
						<option value="{$gui->testsuites->items[$row].id},{$gui->testsuites->items[$row].name|escape}" 
						        selected="selected">{$gui->testsuites->items[$row].name|escape}</option>
					{/foreach}
				</select>
			</td>
		</tr>
		<tr>
			<th>{lang_get s='select_keyword_header'}</th>
			<th>{lang_get s='select_last_result_header'}</th>
		</tr>
		<tr>
			<td>
				<select name="keyword" size="{$keyword_qty}" >
					{foreach key=keyword_id item=keyword_name from=$gui->keywords->items}
						<option value="{$keyword_id}" >{$gui->keywords->items[$keyword_id]|escape}</option>
					{/foreach}
				</select>
			</td>
				<td>
					<select name="lastStatus[]" size="{#TCSTATUS_COMBO_NUM_ITEMS#}" multiple="multiple">
					{foreach key=status_code item=status_label from=$gui->status_code_label}
						<option selected="selected" value="{$status_code}">{$status_label|escape}</option>
					{/foreach}
					</select>
				</td>

		</tr>
		
		<tr>
			<th>{$labels.enter_start_time}</th>
			<th>{$labels.enter_end_time}</th>
		</tr>
		<tr>
			<td align="center">
       <table border='0'>
       <tr>
       <td>{$labels.date}</td><td>{html_select_date prefix="start_" time=$gui->selected_start_date
                                   month_format='%m' start_year="-1" end_year="+1"
                                   field_order=$gsmarty_html_select_date_field_order}</td>
       </tr>
       <tr>
       <td>{$labels.hour}</td>
       <td align='left'>{html_select_time prefix="start_" display_minutes=false 
                                          time=$gui->selected_start_time
                                          display_seconds=false use_24_hours=true}</td>
       </tr>
			 </table>
			</td>

			<td align="center">
       <table border='0'>
       <tr>
       <td>{$labels.date}</td><td>{html_select_date prefix="end_" time=$gui->selected_end_date
                                   month_format='%m' start_year="-1" end_year="+1"
                                   field_order=$gsmarty_html_select_date_field_order}</td>
       </tr>
       <tr>
       <td>{$labels.hour}</td>
       <td align='left'>{html_select_time prefix="end_" display_minutes=false 
                                          time=$gui->selected_end_time
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
			  <th>{lang_get s='select_owner_header'}</th>
				<th>{$labels.executor}</th>
			</tr>
			<tr>
			<td>
				<select name="owner">
					{foreach key=owner item=ownerid from=$gui->assigned_users->items}
						{* by default the owner should be the current user *}
						<option value="{$owner}">{$ownerid|escape}</option>
					{/foreach}
				</select>
			</td>
				<td>
					<select name="executor">
						{foreach key=executor item=executorid from=$gui->assigned_users->items}
							{* by default the owner should be the current user *}
							<option value="{$executor}">{$executorid|escape}</option>
						{/foreach}
					</select>
				</td>
			</tr>
  		<tr>
				<th>{$labels.search_in_notes}</th>
				<th>&nbsp;</th>
			</tr>
			<tr>
				<td>
					<input type="text" name="search_notes_string"/>
				</td>
				<td>&nbsp;</td>
			</tr>
	    </table>
    </div>
    
    <div>
    <table border cellspacing=0 cellpadding=5 rules=groups width="100%">
     <caption align="top">{$labels.report_display_options}</caption>
      <tr>
      <td>
        <table>
         <tr>
         <td>{$labels.display_suite_summaries}</td>
         <td><select name="display_suite_summaries">
		    				<option value="1">{$labels.Yes}</option>
		    				<option value="0" selected="selected">{$labels.No}</option>
		    			</select>
		     </td>
         <td>{$labels.display_test_cases}</td>
         <td><select name="display_test_cases">
		    				<option value="1" selected="selected">{$labels.Yes}</option>
		    				<option value="0">{$labels.No}</option>
		    			</select>
		     </td>
		    </tr>
		    <tr>		
     		<td>{$labels.display_query_params}</td>
     		<td><select name="display_query_params">
		    				<option value="1">{$labels.Yes}</option>
		    				<option value="0" selected="selected">{$labels.No}</option>
		    			</select>
    		</td>
		     <td>{$labels.display_totals}</td>
		     <td><select name="display_totals">
		    				<option value="1">{$labels.Yes}</option>
		    				<option value="0" selected="selected">{$labels.No}</option>
		    			</select>
		    		</td>
	    	</tr>
		    </table>
		  </td>
		  <td>&nbsp;</td>
			<td>
				<input type="submit" value="{$labels.submit_query}"/>
			</td>
			</tr>
    </table>

</div>
</form>
</div>
</body>
</html>
