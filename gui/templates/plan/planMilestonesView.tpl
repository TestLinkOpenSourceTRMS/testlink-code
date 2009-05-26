{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: planMilestonesView.tpl,v 1.6 2009/05/26 19:06:04 schlundus Exp $

Rev:
*}
{lang_get var='labels' s='no_milestones,title_milestones,title_existing_milestones,th_name,
                         th_date_format,th_perc_a_prio,th_perc_b_prio,th_perc_c_prio,
                         btn_new_milestone,
                         th_perc_testcases,th_delete,alt_delete_milestone,no_milestones'}

{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

{* Configure Actions *}
{assign var="managerURL" value="lib/plan/planMilestonesEdit.php"}
{assign var="editAction" value="$managerURL?doAction=edit"}
{assign var="deleteAction" value="$managerURL?doAction=doDelete&id="}
{assign var="createAction" value="$managerURL?doAction=create&tplan_id="}

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
			{if $session['testprojectOptPriority']}
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
			{if $session['testprojectOptPriority']}
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
  				            src="{$delete_img}"/>
  				</td>
		</tr>
		{/foreach}
		</table>

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
