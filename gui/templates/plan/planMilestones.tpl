{*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: planMilestones.tpl,v 1.10 2008/09/23 20:27:55 schlundus Exp $
Purpose: smarty template - edit milestones

rev :
     20080616 - havlatm - disabled prioritization support; fixed wrong condition for H2
     20070624 - franciscom - changed access to defined for gsmarty_ vars
     20070519 - franciscom - added delete confirmation
                             added js checkings
 ----------------------------------------------------------------------------- *}

{include file="inc_head.tpl" jsValidate="yes" openHead="yes"}

<script type="text/javascript">
var warning_empty_milestone_name  = "{lang_get s='warning_empty_milestone_name'}";

/*
  function: delete_confirmation
  args : delUrl: url to call if user press OK
         elem_name: name of element to be deleted, used to build warning message
  returns: -
*/
function delete_confirmation(delUrl,elem_name) {ldelim}
  var msg='{lang_get s='popup_delete_milestones'|escape:"javascript"}';
  msg=msg.replace('%NAME%',elem_name);
	if (confirm(msg)){ldelim}
		window.location = delUrl;
	{rdelim}
{rdelim}

{literal}
/*
  function: validateForm
            validate form inputs, doing several checks like:
            - fields that can not be empty

            if some check fails:
            1. an alert message is displayed
            2. background color of offending field is changed.

  args : f: form object

  returns: true  -> all checks ok
           false -> when a check fails
*/

function validateForm(f)
{
  if (isWhitespace(f.milestone_name.value))
  {
      alert(warning_empty_milestone_name);
      selectField(f, 'milestone_name');
      return false;
  }
}
{/literal}
</script>

</head>
{* ----------------------------------------------------------------------------------- *}
<body>
{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

<h1 class="title">{lang_get s='title_milestones'} {$tpName|escape}</h1>

{* ----- user feedback ----- *}
{if $sqlResult ne ""}
	{include file="inc_update.tpl" result=$sqlResult item="Milestone" action="$action"}
{/if}

<div class="workBack">

	<h2>
	{if $mileStone.id > 0}
		{lang_get s='title_edit_milestone'}&nbsp;{$mileStone.name|escape}&nbsp;
		{if $mgt_view_events eq "yes"}
			<img style="margin-left:5px;" class="clickable" src="{$smarty.const.TL_THEME_IMG_DIR}/question.gif" 
					onclick="showEventHistoryFor('{$mileStone.id}','milestones')" 
					alt="{lang_get s='show_event_history'}" title="{lang_get s='show_event_history'}"/>
		{/if}
	{else}
		{lang_get s='title_new_milestone'}
	{/if}
	</h2>
	
	<form method="post" action="lib/plan/planMilestones.php"
	      name="milestone_mgr" onSubmit="javascript:return validateForm(this);">

	<div class="groupBtn">
		{if !$mileStone.id}
		<input type="submit" name="newMilestone" value="{lang_get s='btn_new_milestone'}" />
		{else}
		<input type="submit" name="update" value="{lang_get s='btn_edit_milestone'}" />
		{/if}
		<input type="button" name="go_back" value="{lang_get s='cancel'}" onclick="javascript: history.back();"/>
	</div>


	<input type="hidden" name="id" value="{$mileStone.id|escape}"/>
	<table class="simple" width="45%">

		<tr>
			<td>{lang_get s='th_name'}:</td>
			<td>
				<input type="text" name="milestone_name" size="{#MILESTONE_NAME_SIZE#}"
        	  	 maxlength="{#MILESTONE_NAME_MAXLEN#}"  value="{$mileStone.name|escape}"/>
	      {include file="error_icon.tpl" field="milestone_name"}
			</td>
		</tr>

		<tr>
			<td>{lang_get s='th_date_format'}:</td>
			<td>
	  {assign var="selected_date" value=""}
      {if $mileStone neq null}
        {assign var="selected_date" value=$mileStone.target_date}
      {/if}
      {html_select_date time=$selected_date
       month_format='%m' start_year="-1" end_year="+1"
       field_order=$gsmarty_html_select_date_field_order}
       		<span class="italic">{lang_get s='info_milestones_date'}</span>
			</td>
		</tr>

	    {if $session['testprojectOptPriority']}
		<tr>
			<td>{lang_get s='th_perc_a_prio'}:</td>
			<td>
				<input type="text" name="A" size="{#PRIORITY_SIZE#}"
				       maxlength="{#PRIORITY_MAXLEN#}" value="{$mileStone.A|escape}"/>
			</td>
		</tr>
		<tr>
			<td>{lang_get s='th_perc_b_prio'}:</td>
			<td>
				<input type="text" name="B" size="{#PRIORITY_SIZE#}"
				       maxlength="{#PRIORITY_MAXLEN#}" value="{$mileStone.B|escape}"/>
			</td>
		</tr>
		<tr>
			<td>{lang_get s='th_perc_c_prio'}:</td>
			<td>
				<input type="text" name="C" size="{#PRIORITY_SIZE#}"
				       maxlength="{#PRIORITY_MAXLEN#}" value="{$mileStone.C|escape}"/>
			</td>
		</tr>
		{else}
		<tr>
			<td>{lang_get s='th_perc_testcases'}:</td>
			<td>
				<input type="hidden" name="A" value="0"/>
				<input type="hidden" name="C" value="0"/>
				<input type="text" name="B" size="{#PRIORITY_SIZE#}"
				       maxlength="{#PRIORITY_MAXLEN#}" value="{$mileStone.B|escape}"/>
			</td>
		</tr>
		{/if}
	</table>

	</form>


</div>

<div class="workBack">

	<h2>{lang_get s='title_existing_milestones'}</h2>

	{if $arrMilestone ne ""}

		<table class="common" width="100%">
		<tr>
			<th>{lang_get s='th_name'}</th>
			<th>{lang_get s='th_date_format'}</th>
			{if $session['testprojectOptPriority']}
				<th>{lang_get s='th_perc_a_prio'}</th>
				<th>{lang_get s='th_perc_b_prio'}</th>
				<th>{lang_get s='th_perc_c_prio'}</th>
			{else}
				<th>{lang_get s='th_perc_testcases'}</th>
			{/if}
			<th>{lang_get s='th_delete'}</th>
		</tr>

		{section name=Row loop=$arrMilestone}
		<tr>
			<td>
				<a href="lib/plan/planMilestones.php?id={$arrMilestone[Row].id}">{$arrMilestone[Row].name|escape}</a>
			</td>
			<td>
				{$arrMilestone[Row].target_date|date_format:$gsmarty_date_format}
			</td>
			{if $session['testprojectOptPriority']}<td>{$arrMilestone[Row].A|escape}</td>{/if}
			<td>{$arrMilestone[Row].B|escape}</td>
			{if $session['testprojectOptPriority']}<td>{$arrMilestone[Row].C|escape}</td>{/if}
			<td>
				<a href="javascript:delete_confirmation(fRoot+'lib/plan/planMilestones.php?delete=1&amp;id={$arrMilestone[Row].id}','{$arrMilestone[Row].name|escape:'javascript'|escape}');">
				<img style="border:none" title="{lang_get s='alt_delete_milestone'}"
					alt="{lang_get s='alt_delete_milestone'}"
					src="{$smarty.const.TL_THEME_IMG_DIR}/trash.png"/>
				</a>
			</td>
		</tr>
		{/section}
		</table>

	{else}
		<p>{lang_get s='no_milestones'}</p>
	{/if}

</div>

</body>
</html>
