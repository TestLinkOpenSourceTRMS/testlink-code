{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource planMilestonesView.tpl
*}

{lang_get var='labels' s='no_milestones,title_milestones,title_existing_milestones,th_name,
                         th_date_format,th_perc_a_prio,th_perc_b_prio,th_perc_c_prio,
                         btn_new_milestone,start_date,title_report_milestones,until,
						 th_milestone,th_tc_priority_high,th_expected,th_tc_priority_medium,
						 th_expected,th_tc_priority_low,th_expected,th_overall,from,
                         th_perc_testcases,th_delete,alt_delete_milestone,no_milestones'}

{$cfg_section=$smarty.template|basename|replace:".tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}

{* Configure Actions *}
{$managerURL="lib/plan/planMilestonesEdit.php"}
{$editAction="$managerURL?doAction=edit"}
{$deleteAction="$managerURL?doAction=doDelete&id="}
{$createAction="$managerURL?doAction=create&tplan_id="}

{lang_get s='warning_delete_milestone' var="warning_msg" }
{lang_get s='delete' var="del_msgbox_title" }

{include file="inc_head.tpl" openHead="yes" jsValidate="yes" enableTableSorting="yes"}
{include file="inc_del_onclick.tpl"}

<script type="text/javascript">
/* All this stuff is needed for logic contained in inc_del_onclick.tpl */
var del_action=fRoot+'{$deleteAction}';
</script>
</head>


{* ----------------------------------------------------------------------------------- *}

<body>
<h1 class="title">{$gui->main_descr|escape}</h1>

<div class="workBack">
	{if $gui->items != ""}
		<table class="common" width="100%">
		<tr>
			<th>{$labels.th_name}</th>
			<th>{$labels.th_date_format}</th>
			<th>{$labels.start_date}</th>
			{if $session['testprojectOptions']->testPriorityEnabled}
				<th>{$labels.th_perc_a_prio}</th>
				<th>{$labels.th_perc_b_prio}</th>
				<th>{$labels.th_perc_c_prio}</th>
			{else}
				<th>{$labels.th_perc_testcases}</th>
			{/if}
			<th>{$labels.th_delete}</th>
		</tr>

		{foreach item=milestone from=$gui->items}
		<tr>
			<td>
				<a href="{$editAction}&id={$milestone.id}">{$milestone.name|escape}</a>
			</td>
			<td>
				{$milestone.target_date|date_format:$gsmarty_date_format}
			</td>
			<td>
			  {if $milestone.start_date != '' && $milestone.start_date != '0000-00-00' }
				  {$milestone.start_date|date_format:$gsmarty_date_format}
				{/if}
			</td>
			{if $session['testprojectOptions']->testPriorityEnabled}
				<td style="text-align: right">{$milestone.high_percentage|escape}</td>
				<td style="text-align: right">{$milestone.medium_percentage|escape}</td>
				<td style="text-align: right">{$milestone.low_percentage|escape}</td>
			{else}
				<td style="text-align: right">{$milestone.medium_percentage|escape}</td>
			{/if}
			<td class="clickable_icon">
				       <img style="border:none;cursor: pointer;" 
  				            title="{$labels.alt_delete_milestone}" 
  				            alt="{$labels.alt_delete_milestone}" 
 					            onclick="delete_confirmation({$milestone.id},'{$milestone.name|escape:'javascript'|escape}',
 					                                         '{$del_msgbox_title}','{$warning_msg}');"
  				            src="{$tlImages.delete}"/>
  				</td>
		</tr>
		{/foreach}
		</table>


		{if $gui->itemsLive != ""}
			<h2>{$labels.title_report_milestones}</h2>

			<table class="simple_tableruler sortable" style="text-align: center; margin-left: 0px;">
			<tr>
				<th>{$labels.th_milestone}</th>
				<th>{$labels.th_tc_priority_high}</th>
				<th>{$labels.th_expected}</th>
				<th>{$labels.th_tc_priority_medium}</th>
				<th>{$labels.th_expected}</th>
				<th>{$labels.th_tc_priority_low}</th>
				<th>{$labels.th_expected}</th>
				<th>{$labels.th_overall}</th>
			</tr>
 			{foreach item=res from=$gui->itemsLive}
  			<tr>
  				<td>{$res.name|escape} {$tlCfg->gui_separator_open}
  						{if $res.start_date|escape != "0000-00-00"}
						{$labels.from} {$res.start_date|escape}
						{/if}
  						{$labels.until} {$res.target_date|escape} {$tlCfg->gui_separator_close}</td>
	  			<td class="{if $res.high_incomplete}failed{else}passed{/if}">
	  					{$res.result_high_percentage} % {$tlCfg->gui_separator_open} 
	  					{$res.results.3}/{$res.tcs_priority.3} {$tlCfg->gui_separator_close}</td>
	  			<td>{$res.high_percentage} %</td>
	  			<td class="{if $res.medium_incomplete}failed{else}passed{/if}">
	  					{$res.result_medium_percentage} % {$tlCfg->gui_separator_open} 
	  					{$res.results.2}/{$res.tcs_priority.2} {$tlCfg->gui_separator_close}</td>
	  			<td>{$res.medium_percentage} %</td>
	  			<td class="{if $res.low_incomplete}failed{else}passed{/if}">
	  					{$res.result_low_percentage} % {$tlCfg->gui_separator_open} 
	  					{$res.results.1}/{$res.tcs_priority.1} {$tlCfg->gui_separator_close}</td>
	  			<td>{$res.low_percentage} %</td>
				<td>{$res.percentage_completed} %</td>
  			</tr>
  			{/foreach}
		</table>
		{/if}



  {else}
		<p>{$labels.no_milestones}</p>
  {/if}

   <div class="groupBtn">
    <form method="post" action="{$createAction}{$gui->tplan_id}">
      <input type="submit" name="create_milestone" value="{$labels.btn_new_milestone}" />
    </form>
  </div>
</div>
</body>
</html>
