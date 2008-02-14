{*
Testlink: smarty template -
$Id: cfieldsEdit.tpl,v 1.7 2008/02/14 21:26:20 schlundus Exp $


Important Development note:
Input names:
            cf_show_on_design
            cf_show_on_execution
            cf_enable_on_design
            cf_enable_on_execution

can not be changed, because there is logic on cfields_edit.php
that dependens on these names.
As you can see these names are build adding 'cf_' prefix to name
of columns present on custom fields tables.
This is done to simplify logic.


rev :
     20071209 - franciscom - added user feedback to explain
                             why certain changes can not be done.

     20070526 - franciscom - added javascript logic to improve
                             cf enable attr management

     20070128 - franciscom - variable name changes
*}
{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

{lang_get s='warning_delete_cf' var="warning_msg" }
{lang_get s='delete' var="del_msgbox_title" }

{include file="inc_head.tpl" jsValidate="yes" openHead="yes"}
{include file="inc_del_onclick.tpl"}

<script type="text/javascript">
/* All this stuff is needed for logic contained in inc_del_onclick.tpl */
var del_action=fRoot+'lib/cfields/cfieldsEdit.php?do_action=do_delete&cfield_id=';
</script>

{literal}
<script type="text/javascript">
{/literal}
var warning_empty_cfield_name = "{lang_get s='warning_empty_cfield_name'}";
var warning_empty_cfield_label = "{lang_get s='warning_empty_cfield_label'}";

// -------------------------------------------------------------------------------
// To manage hide/show combo logic, depending of node type
var js_enable_on_cfg = new Array();
var js_show_on_cfg = new Array();

// DOM Object ID (oid)
js_enable_on_cfg['oid_prefix'] = new Array();
js_enable_on_cfg['oid_prefix']['combobox'] = 'cf_enable_on_';
js_enable_on_cfg['oid_prefix']['container'] = 'container_cf_enable_on_';

// will containg show (1 /0 ) info for every node type
js_enable_on_cfg['execution'] = new Array();
js_enable_on_cfg['design'] = new Array();

// DOM Object ID (oid)
js_show_on_cfg['oid_prefix'] = new Array();
js_show_on_cfg['oid_prefix']['combobox'] = 'cf_show_on_';
js_show_on_cfg['oid_prefix']['container'] = 'container_cf_show_on_';

// will containg show (1 /0 ) info for every node type
js_show_on_cfg['execution'] = new Array();
js_show_on_cfg['design'] = new Array();

{foreach key=node_type item=cfg_def from=$enable_on_cfg.execution}
  js_enable_on_cfg['execution'][{$node_type}]={$cfg_def};
{/foreach}

{foreach key=node_type item=cfg_def from=$enable_on_cfg.design}
  js_enable_on_cfg['design'][{$node_type}]={$cfg_def};
{/foreach}

{foreach key=node_type item=cfg_def from=$show_on_cfg.execution}
  js_show_on_cfg['execution'][{$node_type}]={$cfg_def};
{/foreach}

{foreach key=node_type item=cfg_def from=$show_on_cfg.design}
  js_show_on_cfg['design'][{$node_type}]={$cfg_def};
{/foreach}
// -------------------------------------------------------------------------------


var js_possible_values_cfg = new Array();
{foreach key=cf_type item=cfg_def from=$possible_values_cfg}
  js_possible_values_cfg[{$cf_type}]={$cfg_def};
{/foreach}



{literal}
function validateForm(f)
{
  if (isWhitespace(f.cf_name.value))
  {
      alert(warning_empty_cfield_name);
      selectField(f, 'cf_name');
      return false;
  }

  if (isWhitespace(f.cf_label.value))
  {
      alert(warning_empty_cfield_label);
      selectField(f, 'cf_label');
      return false;
  }
  return true;
}

/*
  function: configure_cf_attr
            depending of node type, custom fields attributes
            will be set to disable, is its value is nonsense
            for node type choosen by user.

  args :
         id_nodetype: id of html input used to choose node type
                      to which apply custom field


  returns: -

*/
function configure_cf_attr(id_nodetype,enable_on_cfg,show_on_cfg)
{
  var o_nodetype=document.getElementById(id_nodetype);
  var o_enable=new Array();
  var o_enable_container=new Array();
  var o_display=new Array();
  var o_display_container=new Array();


  var oid;
  var keys2loop=new Array();
  var idx;
  var key;

  keys2loop[0]='execution';
  keys2loop[1]='design';

  // ------------------------------------------------------------
  // Enable on
  // ------------------------------------------------------------
  for(idx=0;idx < keys2loop.length; idx++)
  {
    key=keys2loop[idx];
    oid=enable_on_cfg['oid_prefix']['combobox']+key;
    o_enable[key]=document.getElementById(oid);

    oid=enable_on_cfg['oid_prefix']['container']+key;
    o_enable_container[key]=document.getElementById(oid);

    if( enable_on_cfg[key][o_nodetype.value] == 0 )
    {
      // 20071124 - need to understand if can not set to 0
      o_enable[key].value=0;
      o_enable[key].disabled='disabled';
      o_enable_container[key].style.display='none';
    }
    else
    {
      o_enable[key].disabled='';
      o_enable_container[key].style.display='';
    }
  }
  // ------------------------------------------------------------

  // ------------------------------------------------------------
  // Display on
  // ------------------------------------------------------------
  for(idx=0;idx < keys2loop.length; idx++)
  {
    key=keys2loop[idx];
    oid=show_on_cfg['oid_prefix']['combobox']+key;
    o_display[key]=document.getElementById(oid);

    oid=show_on_cfg['oid_prefix']['container']+key;
    o_display_container[key]=document.getElementById(oid);

    if( show_on_cfg[key][o_nodetype.value] == 0 )
    {
      // 20071124 - need to understand if can not set to 0
      o_display[key].value=0;
      o_display[key].disabled='disabled';
      o_display_container[key].style.display='none';
    }
    else
    {
      o_display[key].disabled='';
      o_display_container[key].style.display='';
    }
  }
  // ------------------------------------------------------------



} // configure_cf_attr



/*
  function: cfg_possible_values_display
            depending of Custom Field type, Possible Values attribute
            will be displayed or not.

  args : cf_type: id of custom field type, choosen by user.

         id_possible_values_container : id of html container
                                        where input for possible values
                                        lives. Used to manage visibility.

  returns:

*/
function cfg_possible_values_display(cfg,id_cftype,id_possible_values_container)
{

  o_cftype=document.getElementById(id_cftype);
  o_container=document.getElementById(id_possible_values_container);

  if( cfg[o_cftype.value] == 0 )
  {
    o_container.style.display='none';
  }
  else
  {
    o_container.style.display='';
  }
}
</script>
{/literal}

</head>

<body {$body_onload}>

<h1>
 {lang_get s='help' var='common_prefix'}
 {assign var="text_hint" value="$common_prefix"}
 {include file="inc_help.tpl" help="custom_fields" locale=$locale
          alt="$text_hint" title="$text_hint"  style="float: right;"}
 {lang_get s='title_cfields_mgmt'} </h1>

{include file="inc_update.tpl" result=$result item="custom_field" action="$user_action"}

{if $is_used}
  <div class="user_feedback">{lang_get s="warning_is_in_use"}</div>
{/if}

<div class="workBack">


{if $user_action eq "do_delete"}
  <form method="post" name="cfields_edit" action="lib/cfields/cfieldsView.php">
   <div class="groupBtn">
		<input type="submit" name="ok" value="{lang_get s='btn_ok'}" />
	 </div>
  </form>

{else}
<form method="post" name="cfields_edit" action="lib/cfields/cfieldsEdit.php"
      onSubmit="javascript:return validateForm(this);">
  <input type="hidden" id="hidden_id" name="cfield_id" value="{$cf.id}" />
	<table class="common">
    <tr>
      <td colspan="2">
       {lang_get s='help' var='common_prefix'}
       {assign var="text_hint" value="$common_prefix"}
       {include file="inc_help.tpl" help="custom_fields" locale=$locale
                alt="$text_hint" title="$text_hint"  style="float: right;"}
      </td>
    </tr>

	 <tr>
			<th style="background:none;">{lang_get s='name'}</th>
			<td><input type="text" name="cf_name"
			                       size="{#CFIELD_NAME_SIZE#}"
			                       maxlength="{#CFIELD_NAME_MAXLEN#}"
    			 value="{$cf.name|escape}" />
           {include file="error_icon.tpl" field="cf_name"}
    	</td>
		</tr>
		<tr>
			<th style="background:none;">{lang_get s='label'}</th>
			<td><input type="text" name="cf_label"
			                       size="{#CFIELD_LABEL_SIZE#}"
			                       maxlength="{#CFIELD_LABEL_MAXLEN#}"
			           value="{$cf.label|escape}"/>
		           {include file="error_icon.tpl" field="cf_label"}
    	</td>
	  </tr>

		<tr>
			<th style="background:none;">{lang_get s='type'}</th>
			<td>
			  {if $is_used}
			    {assign var="idx" value=$cf.type}
			    {$cf_types.$idx}
			    <input type="hidden" id="hidden_cf_type"
			           value={$cf.type} name="cf_type" />
			  {else}
  				<select onchange="cfg_possible_values_display(js_possible_values_cfg,
  				                                              'combo_cf_type',
  				                                              'possible_values');"
  				        id="combo_cf_type"
  				        name="cf_type">
	  			{html_options options=$cf_types selected=$cf.type}
		  		</select>
		  	{/if}
			</td>
		</tr>

    {if $show_possible_values }
      {assign var="display_style" value=""}
    {else}
      {assign var="display_style" value="none"}
		{/if}
		<tr id="possible_values" style="display:{$display_style};">
			<th style="background:none;">{lang_get s='possible_values'}</th>
			<td>
				<input type="text" id="cf_possible_values"
				                   name="cf_possible_values"
		                       size="{#CFIELD_POSSIBLE_VALUES_SIZE#}"
		                       maxlength="{#CFIELD_POSSIBLE_VALUES_MAXLEN#}"
				                   value="{$cf.possible_values}" />
			</td>
		</tr>

    {* ------------------------------------------------------------------------------- *}
    {*   Design   *}
    {if $disabled_cf_show_on.design}
      {assign var="display_style" value="none"}
    {else}
      {assign var="display_style" value=""}
    {/if}

		<tr id="container_cf_show_on_design" style="display:{$display_style};">
			<th style="background:none;">{lang_get s='show_on_design'}</th>
			<td>
				<select id="cf_show_on_design"
				        name="cf_show_on_design"
			        	{$disabled_cf_show_on.design} >
				{html_options options=$gsmarty_option_yes_no selected=$cf.show_on_design}
				</select>
			</td>
		</tr>


		{if $disabled_cf_enable_on.design}
      {assign var="display_style" value="none"}
    {else}
      {assign var="display_style" value=""}
    {/if}
		<tr	id="container_cf_enable_on_design" style="display:{$display_style};">
			<th style="background:none;">{lang_get s='enable_on_design'}</th>
			<td>
				<select name="cf_enable_on_design"
				        id="cf_enable_on_design"
				        {$disabled_cf_enable_on.design}>
				{html_options options=$gsmarty_option_yes_no selected=$cf.enable_on_design}
				</select>
			</td>
		</tr>
    {* ------------------------------------------------------------------------------- *}


    {* ------------------------------------------------------------------------------- *}
    {*   Execution  *}
    {if $disabled_cf_show_on.execution}
      {assign var="display_style" value="none"}
    {else}
      {assign var="display_style" value=""}
    {/if}

		<tr id="container_cf_show_on_execution" style="display:{$display_style};">
			<th style="background:none;">{lang_get s='show_on_exec'}</th>
			<td>
				<select id="cf_show_on_execution"  name="cf_show_on_execution"
				        {$disabled_cf_show_on.execution}>
				{html_options options=$gsmarty_option_yes_no selected=$cf.show_on_execution}
				</select>
			</td>
		</tr>

		{if $disabled_cf_enable_on.execution}
      {assign var="display_style" value="none"}
    {else}
      {assign var="display_style" value=""}
    {/if}
		<tr id="container_cf_enable_on_execution" style="display:{$display_style};">
			<th style="background:none;">{lang_get s='enable_on_exec'}</th>
			<td>
				<select id="cf_enable_on_execution"
				        name="cf_enable_on_execution"
				        {$disabled_cf_enable_on.execution}>
				{html_options options=$gsmarty_option_yes_no selected=$cf.enable_on_execution}
				</select>
			</td>
		</tr>
    {* ------------------------------------------------------------------------------- *}


		<tr>
			<th style="background:none;">{lang_get s='available_on'}</th>
			<td>
			  {if $is_used} {* Type CAN NOT BE CHANGED *}
			    {assign var="idx" value=$cf.node_type_id}
			    {$cf_allowed_nodes.$idx}
			    <input type="hidden" id="hidden_cf_node_type_id"
			           value={$cf.node_type_id} name="cf_node_type_id" />
			  {else}
  				<select onchange="configure_cf_attr('combo_cf_node_type_id',
  				                                    js_enable_on_cfg,
  				                                    js_show_on_cfg);"
  				        id="combo_cf_node_type_id"
  				        name="cf_node_type_id">
  				{html_options options=$cf_allowed_nodes selected=$cf.node_type_id}
  				</select>
				{/if}
			</td>
		</tr>
	</table>

	<div class="groupBtn">
	<input type="hidden" name="do_action" value="" />
	{if $user_action eq 'edit'  or $user_action eq 'do_update'}
		<input type="submit" name="do_update" value="{lang_get s='btn_upd'}"
		       onclick="do_action.value='do_update'"/>

		{if $is_used eq 0}
  		<input type="button" name="do_delete" value="{lang_get s='btn_delete'}"
  		       onclick="delete_confirmation({$cf.id},'{$cf.name|escape:'javascript'}',
  		                                    '{$del_msgbox_title}','{$warning_msg}');">
    {/if}

	{else}
		<input type="submit" name="do_update" value="{lang_get s='btn_add'}"
		       onclick="do_action.value='do_add'"/>
	{/if}
		<input type="button" name="cancel" value="{lang_get s='btn_cancel'}"
			onclick="javascript: location.href=fRoot+'lib/cfields/cfieldsView.php';" />

	</div>
</form>
<hr />
{/if}

</div>

</body>
</html>