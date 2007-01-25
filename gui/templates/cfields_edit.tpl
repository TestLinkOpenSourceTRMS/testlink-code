{* 
Testlink: smarty template - 
$Id: cfields_edit.tpl,v 1.5 2007/01/25 14:04:30 franciscom Exp $ 
*}
{include file="inc_head.tpl" jsValidate="yes"}

<body>
{config_load file="input_dimensions.conf" section="cfields_edit"} {* Constant definitions *}

{literal}
<script type="text/javascript">
{/literal}
var warning_empty_cfield_name = "{lang_get s='warning_empty_cfield_name'}";
var warning_empty_cfield_label = "{lang_get s='warning_empty_cfield_label'}";
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
</script>
{/literal}


<h1>
 {lang_get s='help' var='common_prefix'}
 {assign var="text_hint" value="$common_prefix"}
 {include file="inc_help.tpl" help="custom_fields" locale=$locale 
          alt="$text_hint" title="$text_hint"  style="float: right;"}
 {lang_get s='title_cfields_mgmt'} </h1>

{include file="inc_update.tpl" result=$result item="custom_field" action="$action" feedback_type="soft"}

<div class="workBack">


{if $action eq "do_delete"}
  <form method="post" name="cfields_edit" action="lib/cfields/cfields_view.php">
   <div class="groupBtn">	
		<input type="submit" name="ok" value="{lang_get s='btn_ok'}"> 
	 </div>  
  </form> 

{else}
<form method="post" name="cfields_edit" action="lib/cfields/cfields_edit.php" 
      onSubmit="javascript:return validateForm(this);">
  <input type="hidden" id="hidden_id" name="id" value="{$cf.id}">   
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
  				<select id="combo_cf_type" name="cf_type"> 
	  			{html_options options=$cf_types selected=$cf.type}
		  		</select>
		  	{/if}	
			</td>
		</tr>

		<tr>
			<th>{lang_get s='possible_values'}</th>
			<td>
				<input type="text" name="cf_possible_values"
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

		<tr>
			<th>{lang_get s='show_on_exec'}</th>
			<td>
				<select name="cf_show_on_execution"> 
				{html_options options=$gsmarty_option_yes_no selected=$cf.show_on_execution}
				</select>
			</td>
		</tr>
		<tr>
			<th>{lang_get s='enable_on_exec'}</th>
			<td>
				<select name="cf_enable_on_execution"> 
				{html_options options=$gsmarty_option_yes_no selected=$cf.enable_on_execution}
				</select>
			</td>
		</tr>

		<tr>
			<th>{lang_get s='available_on'}</th>
			<td>
			  {if $is_used}
			    {assign var="idx" value=$cf.node_type_id}
			    {$cf_allowed_nodes.$idx}
			    <input type="hidden" id="hidden_cf_node_type_id" 
			           value={$cf.node_type_id} name="cf_node_type_id"> 
			  {else}
  				<select id="combo_cf_node_type_id" name="cf_node_type_id"> 
  				{html_options options=$cf_allowed_nodes selected=$cf.node_type_id}
  				</select>
				{/if}
			</td>
		</tr>
	</table>
	
	<div class="groupBtn">	
	<input type="hidden" name="action" value="">
	{if $action eq 'edit'}
		<input type="submit" name="do_update" value="{lang_get s='btn_upd'}"
		       onclick="action.value='do_update'"/>
		       
		{if is_used eq 0}       
  		<input type="button" name="do_delete" value="{lang_get s='btn_delete'}"
  		       onclick="action.value='do_delete';
  		                if (confirm('{lang_get s='popup_delete_custom_field'}'))
  		                {ldelim}cfields_edit.submit();{rdelim};" />
    {/if}
		       
		       
	{else}
		<input type="submit" name="do_update" value="{lang_get s='btn_add'}" 
		       onclick="action.value='do_add'"/>
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