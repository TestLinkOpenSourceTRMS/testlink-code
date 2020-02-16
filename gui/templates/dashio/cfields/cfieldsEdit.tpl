{*
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource cfieldsEdit.tpl

Important Development note:
Input names:
            cf_show_on_design
            cf_show_on_execution
            cf_enable_on_design
            cf_enable_on_execution
            cf_show_on_testplan_design
            cf_enable_on_testplan_design


can not be changed, because there is logic on cfields_edit.php
that dependens on these names.
As you can see these names are build adding 'cf_' prefix to name
of columns present on custom fields tables.
This is done to simplify logic.

*}

{$cfg_section=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

{$managerURL="lib/cfields/cfieldsEdit.php"}
{$viewAction="lib/cfields/cfieldsView.php"}

{lang_get s='warning_delete_cf' var="warning_msg" }
{lang_get s='delete' var="del_msgbox_title" }

{lang_get var="labels"
          s="btn_ok,title_cfields_mgmt,warning_is_in_use,warning,name,label,type,possible_values,
             warning_empty_cfield_name,warning_empty_cfield_label,testproject,assigned_to_testprojects,
             enable_on_design,show_on_exec,enable_on_exec,enable_on_testplan_design,
             available_on,btn_upd,btn_delete,warning_no_type_change,enable_on,
             btn_add,btn_cancel,show_on_design,show_on_testplan_design,btn_add_and_assign_to_current,"}

{include file="inc_head.tpl" jsValidate="yes" openHead="yes"}
{include file="inc_del_onclick.tpl"}

{include file="cfields/cfieldsEditJS.tpl"}

{include file="bootstrap.inc.tpl"}
</head>

<body onload="configure_cf_attr('combo_cf_node_type_id',js_enable_on_cfg,js_show_on_cfg);">

{include file="aside.tpl"}  
<div id="main-content">
<h1 class="title big-font">{$labels.title_cfields_mgmt}</h1>

<h2>{$operation_descr|escape}</h2>
{include file="inc_update.tpl" user_feedback=$user_feedback}

{if $gui->cfield_is_used}
  <div class="user_feedback">{$labels.warning_no_type_change}</div>
{/if}

<div class="workBack">

{if $user_action eq "do_delete"}
  <form method="post" name="cfields_edit" action="{$viewAction}">
   <div class="groupBtn">
		<input class="{#BUTTON_CLASS#}" type="submit" 
           id="ok" name="ok" 
           value="{$labels.btn_ok}" />
	 </div>
  </form>

{else}
<form method="post" name="cfields_edit" action="lib/cfields/cfieldsEdit.php"
      onSubmit="javascript:return validateForm(this);">
<input type="hidden" id="hidden_id" name="cfield_id" value="{$gui->cfield.id}" />
<table class="common">

	 <tr>
			<th style="background:none;">{$labels.name}</th>
			<td><input type="text" name="cf_name"
			                       size="{#CFIELD_NAME_SIZE#}"
			                       maxlength="{#CFIELD_NAME_MAXLEN#}"
    			 value="{$gui->cfield.name|escape}" required />
           {include file="error_icon.tpl" field="cf_name"}
    	</td>
		</tr>
		<tr>
			<th style="background:none;">{$labels.label}</th>
			<td><input type="text" name="cf_label"
			                       size="{#CFIELD_LABEL_SIZE#}"
			                       maxlength="{#CFIELD_LABEL_MAXLEN#}"
			           value="{$gui->cfield.label|escape}" required />
		           {include file="error_icon.tpl" field="cf_label"}
    	</td>
	  </tr>
		<tr>
			<th style="background:none;">{$labels.available_on}</th>
			<td>
			  {if $gui->cfield_is_used} {* Type CAN NOT BE CHANGED *}
			    {assign var="idx" value=$gui->cfield.node_type_id}
			    {$gui->cfieldCfg->cf_allowed_nodes.$idx}
			    <input type="hidden" id="combo_cf_node_type_id"
			           value={$gui->cfield.node_type_id} name="cf_node_type_id" />
			  {else}
  				<select onchange="configure_cf_attr('combo_cf_node_type_id',
  				                                    js_enable_on_cfg,
  				                                    js_show_on_cfg);"
  				        id="combo_cf_node_type_id"
  				        name="cf_node_type_id">
  				{html_options options=$gui->cfieldCfg->cf_allowed_nodes selected=$gui->cfield.node_type_id}
  				</select>
				{/if}
			</td>
		</tr>

		<tr>
			<th style="background:none;">{$labels.type}</th>
			<td>
			  {if $gui->cfield_is_used}
			    {$idx=$gui->cfield.type}
			    {$gui->cfield_types.$idx}
			    <input type="hidden" id="hidden_cf_type"
			           value={$gui->cfield.type} name="cf_type" />
			  {else}
  				<select onchange="cfg_possible_values_display(js_possible_values_cfg,
  				                                              'combo_cf_type',
  				                                              'possible_values');"
  				        id="combo_cf_type"
  				        name="cf_type">
	  			{html_options options=$gui->cfield_types selected=$gui->cfield.type}
		  		</select>
		  	{/if}
			</td>
		</tr>

    {if $gui->show_possible_values }
      {$display_style=""}
    {else}
      {$display_style="none"}
		{/if}
		<tr id="possible_values" style="display:{$display_style};">
			<th style="background:none;">{$labels.possible_values}</th>
			<td>
				<input type="text" id="cf_possible_values"
				                   name="cf_possible_values"
		                       size="{#CFIELD_POSSIBLE_VALUES_SIZE#}"
		                       maxlength="{#CFIELD_POSSIBLE_VALUES_MAXLEN#}"
				                   value="{$gui->cfield.possible_values}" />
			</td>
		</tr>

   {* ----------------------------------------------------------------------- *}
		<tr	id="container_cf_enable_on">
			<th style="background:none;">{$labels.enable_on}</th>
			<td>
				<select name="cf_enable_on" id="cf_enable_on"
				        onchange="initShowOnExec('cf_enable_on',js_show_on_cfg);">
        {foreach item=area_cfg key=area_name from=$gui->cfieldCfg->cf_enable_on}
          {assign var="access_key" value="enable_on_$area_name"}
				  <option value={$area_name} id="option_{$area_name}" 
				          {if $area_cfg.value == 0} style="display:none;" {/if} 
				  {if $gui->cfield.$access_key} selected="selected"	{/if}>{$area_cfg.label}</option>
				{/foreach}
				</select>
			</td>
		</tr>


    {* --------------------------------------------------------------------- *}
    {* Execution  *}
    		<tr id="container_cf_show_on_execution" {$gui->cfieldCfg->cf_show_on.execution.style}>
			<th style="background:none;">{$labels.show_on_exec}</th>
			<td>
				<select id="cf_show_on_execution"  name="cf_show_on_execution">
				{html_options options=$gsmarty_option_yes_no selected=$gui->cfield.show_on_execution}
				</select>
			</td>
		</tr>

	</table>

  {if isset($gui->cfield_is_linked) && $gui->cfield_is_linked}
  <table class="common">
    <tr> <th>{$labels.assigned_to_testprojects} </th>
    {foreach item=tproject from=$gui->linked_tprojects}
      <tr> <td>{$tproject.name|escape}</td> </tr>
    {/foreach}
  </table>

  {/if}

	<div class="groupBtn">
	<input type="hidden" name="do_action" value="" />
	{if $user_action eq 'edit'  or $user_action eq 'do_update'}
		
    <input class="{#BUTTON_CLASS#}" type="submit" 
           name="do_update" value="{$labels.btn_upd}"
           onclick="do_action.value='do_update'"/>

		{* Allow delete , just give warning *}
  		<input class="{#BUTTON_CLASS#}" type="button" 
             name="do_delete" id="do_delete"
             value="{$labels.btn_delete}"
  		       onclick="delete_confirmation({$gui->cfield.id},
                      '{$gui->cfield.name|escape:'javascript'|escape}',
  		                '{$del_msgbox_title}','{$warning_msg}');">
	{else}
		
    <input class="{#BUTTON_CLASS#}" type="submit" 
           name="do_update" value="{$labels.btn_add}"
           onclick="do_action.value='do_add'"/>


    <input class="{#BUTTON_CLASS#}" type="submit" 
           name="do_add_and_assign" id="do_add_and_assign"
           value="{$labels.btn_add_and_assign_to_current}"
           onclick="do_action.value='do_add_and_assign'"/>
	{/if}
		<input class="{#BUTTON_CLASS#}" type="button" 
           name="cancel" id="cancel" 
           value="{$labels.btn_cancel}"
			     onclick="javascript: location.href=fRoot+'lib/cfields/cfieldsView.php';" />

	</div>
</form>
<hr />
{/if}

</div>

</div>
{include file="supportJS.inc.tpl"}
</body>
</html>