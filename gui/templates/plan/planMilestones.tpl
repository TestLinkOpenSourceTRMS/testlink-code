{*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: planMilestones.tpl,v 1.5 2008/02/15 20:26:43 schlundus Exp $
Purpose: smarty template - edit milestones

rev :
     20070624 - franciscom - changed access to defined for gsmarty_ vars
     20070519 - franciscom - added delete confirmation
                             added js checkings
*}

{include file="inc_head.tpl" jsValidate="yes" openHead="yes"}

<script type="text/javascript">
var warning_empty_milestone_name  = "{lang_get s='warning_empty_milestone_name'}";

/*
  function: delete_confirmation

  args : delUrl: url to call if user press OK
         elem_name: name of element to be deleted, used
                    to build warning message

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
</script>
{/literal}

</head>

<body>
{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

<h1>{lang_get s='title_milestones'} {$tpName|escape}</h1>

{* product was added *}
{if $sqlResult ne ""}
	{include file="inc_update.tpl" result=$sqlResult item="Milestone" action="$action"}
{/if}

<div class="workBack">

	<div>
	<h2>{lang_get s='title_new_milestone'}</h2>
	<p class="italic">{lang_get s='info_milestones_date'}</p>
	<form method="post" action="lib/plan/planMilestones.php"
	      name="milestone_mgr" onSubmit="javascript:return validateForm(this);">
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
      {if $mileStone neq null}
        {assign var="selected_date" value=$mileStone.target_date}
      {/if}
      {html_select_date time=$selected_date
       month_format='%m' start_year="-1" end_year="+1"
       field_order=$gsmarty_html_select_date_field_order}
			</td>
		</tr>

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
	</table>
	<p>
	{$bEdit}
	{if !$mileStone.id}
		<input type="submit" name="newMilestone" value="{lang_get s='btn_new_milestone'}" />
	{else}
		<input type="submit" name="update" value="{lang_get s='btn_edit_milestone'}" />
	{/if}
	</p>
	</form>
	</div>


</div>

<div class="workBack">

	<div>
		<h2>{lang_get s='title_existing_milestones'}</h2>
		{if $arrMilestone ne ""}
				<table class="common" width="100%">
					<tr>
						<th>{lang_get s='th_name'}</th>
						<th>{lang_get s='th_date_format'}</th>
						<th>{lang_get s='th_perc_a_prio'}</th>
						<th>{lang_get s='th_perc_b_prio'}</th>
						<th>{lang_get s='th_perc_c_prio'}</th>
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
							<td>
								{$arrMilestone[Row].A|escape}
							</td>
							<td>
								{$arrMilestone[Row].B|escape}
							</td>
							<td>
								{$arrMilestone[Row].C|escape}
							</td>
							<td>
								<a href="javascript:delete_confirmation(fRoot+'lib/plan/planMilestones.php?delete=1&amp;id={$arrMilestone[Row].id}','{$arrMilestone[Row].name}');">
								<img style="border:none"
								     title="{lang_get s='alt_delete_milestone'}"
								     alt="{lang_get s='alt_delete_milestone'}"
								     src="{$smarty.const.TL_THEME_IMG_DIR}/trash.png"/>
								</a>
							</td>
						</tr>
					{/section}
				</table>
		{else}
			{lang_get s='no_milestones'}
		{/if}
	</div>

</div>

</body>
</html>
