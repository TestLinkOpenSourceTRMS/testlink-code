{* 
Testlink: smarty template - 
$Id: cfields_edit.tpl,v 1.7 2007/06/04 17:27:40 franciscom Exp $ 

rev :

     20070526 - franciscom - added javascript logic to improve
                             cf enable attr management
                             
     20070128 - franciscom - variable name changes
*}
{include file="inc_head.tpl" jsValidate="yes"}

<body>
{config_load file="input_dimensions.conf" section="cfields_edit"} {* Constant definitions *}


{literal}
<script type="text/javascript">
{/literal}
var warning_empty_cfield_name = "{lang_get s='warning_empty_cfield_name'}";
var warning_empty_cfield_label = "{lang_get s='warning_empty_cfield_label'}";

var js_enable_on_exec_cfg = new Array();
{foreach key=node_type item=cfg_def from=$enable_on_exec_cfg}
  js_enable_on_exec_cfg[{$node_type}]={$cfg_def};
{/foreach}


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
  
  args : id_nodetype: id of html input used to choose node type
                      to which apply custom field
                      
         id_exec : id of html input used to configure custom field 
                   attribute "enable on execution"             
                   
         id_exec_container : id of html container 
                             where input for "enable on execution"
                             lives. Used to manage visibility.
  
  returns: 

*/
function configure_cf_attr(cfg,id_nodetype,id_exec,id_exec_container)
{
  o_nodetype=document.getElementById(id_nodetype);
  o_exec=document.getElementById(id_exec);
  o_exec_container=document.getElementById(id_exec_container);
  
  if( cfg[o_nodetype.value] == 0 )
  {
    o_exec.value=0;
    o_exec.disabled='disabled';
    o_exec_container.style.display='none';
  }
  else
  {
    o_exec.disabled='';
    o_exec_container.style.display='';
  }
}

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


<h1>
 {lang_get s='help' var='common_prefix'}
 {assign var="text_hint" value="$common_prefix"}
 {include file="inc_help.tpl" help="custom_fields" locale=$locale 
          alt="$text_hint" title="$text_hint"  style="float: right;"}
 {lang_get s='title_cfields_mgmt'} </h1>

{include file="inc_update.tpl" result=$result item="custom_field" action="$user_action" feedback_type="soft"}

<div class="workBack">


{if $user_action eq "do_delete"}
  <form method="post" name="cfields_edit" action="lib/cfields/cfields_view.php">
   <div class="groupBtn">	
		<input type="submit" name="ok" value="{lang_get s='btn_ok'}"> 
	 </div>  
  </form> 

{else}
<form method="post" name="cfields_edit" action="lib/cfields/cfields_edit.php" 
      onSubmit="javascript:return validateForm(this);">
  <input type="hidden" id="hidden_id" name="cfield_id" value="{$cf.id}">   
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
			<th>{lang_get s='name'}	
      </th>
			<td><input type="text" name="cf_name" 
			                       size="{#CFIELD_NAME_SIZE#}" 
			                       maxlength="{#CFIELD_NAME_MAXLEN#}" 
    			 value="{$cf.name|escape}" />
           {include file="error_icon.tpl" field="cf_name"}
    	</td>
		</tr>
		<tr>
			<th>{lang_get s='label'}</th>
			<td><input type="text" name="cf_label" 
			                       size="{#CFIELD_LABEL_SIZE#}" 
			                       maxlength="{#CFIELD_LABEL_MAXLEN#}" 
			           value="{$cf.label|escape}"/>
		           {include file="error_icon.tpl" field="cf_label"}
    	</td>
	  </tr>
		
		<tr>
			<th>{lang_get s='type'}</th>
			<td>
			  {if $is_used}
			    {assign var="idx" value=$cf.type}
			    {$cf_types.$idx}
			    <input type="hidden" id="hidden_cf_type" 
			           value={$cf.type} name="cf_type"> 
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
			<th>{lang_get s='possible_values'}</th>
			<td>
				<input type="text" id="cf_possible_values"
				                   name="cf_possible_values"
		                       size="{#CFIELD_POSSIBLE_VALUES_SIZE#}" 
		                       maxlength="{#CFIELD_POSSIBLE_VALUES_MAXLEN#}" 
				                   value="{$cf.possible_values}"> 
			</td>
		</tr>

		<tr>
			<th>{lang_get s='show_on_design'}</th>
			<td>
				<select name="cf_show_on_design"> 
				{html_options options=$gsmarty_option_yes_no selected=$cf.show_on_design}
				</select>
			</td>
		</tr>
		<tr>
			<th>{lang_get s='enable_on_design'}</th>
			<td>
				<select name="cf_enable_on_design"> 
				{html_options options=$gsmarty_option_yes_no selected=$cf.enable_on_design}
				</select>
			</td>
		</tr>

    {if $disabled_cf_enable_on_execution}
      {assign var="display_style" value="none"}
    {else}
      {assign var="display_style" value=""}
    {/if}
    
		<tr>
			<th>{lang_get s='show_on_exec'}</th>
			<td>
				<select name="cf_show_on_execution"> 
				{html_options options=$gsmarty_option_yes_no selected=$cf.show_on_execution}
				</select>
			</td>
		</tr>
		<tr id="cf_enable_on_execution_container" style="display:{$display_style};">
			<th>{lang_get s='enable_on_exec'}</th>
			<td>
				<select id="cf_enable_on_execution" 
				        name="cf_enable_on_execution"
				        {$disabled_cf_enable_on_execution}> 
				{html_options options=$gsmarty_option_yes_no selected=$cf.enable_on_execution}
				</select>
			</td>
		</tr>

		<tr>
			<th>{lang_get s='available_on'}</th>
			<td>
			  {if $is_used} {* Type CAN NOT BE CHANGED *}
			    {assign var="idx" value=$cf.node_type_id}
			    {$cf_allowed_nodes.$idx}
			    <input type="hidden" id="hidden_cf_node_type_id" 
			           value={$cf.node_type_id} name="cf_node_type_id"> 
			  {else}
  				<select onchange="configure_cf_attr(js_enable_on_exec_cfg,
  				                                    'combo_cf_node_type_id',
  				                                    'cf_enable_on_execution',
  				                                    'cf_enable_on_execution_container');"
  				        id="combo_cf_node_type_id" 
  				        name="cf_node_type_id"> 
  				{html_options options=$cf_allowed_nodes selected=$cf.node_type_id}
  				</select>
				{/if}
			</td>
		</tr>
	</table>
	
	<div class="groupBtn">	
	<input type="hidden" name="do_action" value="">
	{if $user_action eq 'edit'}
		<input type="submit" name="do_update" value="{lang_get s='btn_upd'}"
		       onclick="do_action.value='do_update'"/>
		       
		{if is_used eq 0}       
  		<input type="button" name="do_delete" value="{lang_get s='btn_delete'}"
  		       onclick="do_action.value='do_delete';
  		                if (confirm('{lang_get s='popup_delete_custom_field'}'))
  		                {ldelim}cfields_edit.submit();{rdelim};" />
    {/if}
		       
		       
	{else}
		<input type="submit" name="do_update" value="{lang_get s='btn_add'}" 
		       onclick="do_action.value='do_add'"/>
	{/if}
		<input type="button" name="cancel" value="{lang_get s='btn_cancel'}" 
			onclick="javascript: location.href=fRoot+'lib/cfields/cfields_view.php';" />

	</div>
</form>
<hr />
{/if}

</div>

</body>
</html>