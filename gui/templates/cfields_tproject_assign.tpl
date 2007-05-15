{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: cfields_tproject_assign.tpl,v 1.4 2007/05/15 17:02:20 franciscom Exp $
Purpose: management Custom fields assignment to a test project

rev :
     20070515 - franciscom - BUGID 0000852 

*}
{include file="inc_head.tpl"}

<body>
{assign var="cfg_section" value=$smarty.template|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

<h1>
{lang_get s='cfields_tproject_assign'}{$smarty.const.TITLE_SEP_TYPE2}{lang_get s="testproject"}{$smarty.const.TITLE_SEP}{$tproject_name|escape}
</h1>

{include file="inc_update.tpl" result=$sqlResult action=$action item="custom_field"}

{if $my_cf ne ""}
  <div class="workBack">
    <h2>{lang_get s='title_assigned_cfields'}</h2>
    <form id="cf_assignment" method="post">
      <table class="simple">
      	<tr>
      		<th style="width: 10px;"></th>
      		<th>{lang_get s="name"}</th>
      		<th>{lang_get s="label"}</th>
      		<th style="width: 10px;">{lang_get s="display_order"}</th>
      		<th style="width: 10px;">{lang_get s="cfields_active"}</th>
      	</tr>
      	{foreach key=cf_id item=cf from=$my_cf}
      	<tr>
      		<td><input type="checkbox" name="cfield[{$cf.id}]" /></td>
   		   	<td class="bold"><a href="lib/cfields/cfields_edit.php?do_action=edit&cfield_id={$cf.id}"
   		   	                    title="{lang_get s='manage_cfield'}">{$cf.name|escape}</a></td>
      		<td class="bold">{$cf.label|escape}</span></td>
      		<td><input type="text" name="display_order[{$cf.id}]" 
      		           value="{$cf.display_order}" 
      		           size="{#DISPLAY_ORDER_SIZE#}" maxlength="{#DISPLAY_ORDER_MAXLEN#}"></td>
      		<td><input type="checkbox" name="active_cfield[{$cf.id}]" 
      		                           {if $cf.active eq 1} checked="checked" {/if}/> 
      		    <input type="hidden"   name="hidden_active_cfield[{$cf.id}]" 
      		                           value="{$cf.active}"/> 
      		</td>
      	</tr>
      	{/foreach}
      </table>
    	<div class="groupBtn">
    		<input type="submit" name="unassign" value="{lang_get s='btn_unassign'}" />
    		<input type="submit" name="active_mgmt" value="{lang_get s='btn_cfields_active_mgmt'}" />
    		<input type="submit" name="reorder" value="{lang_get s='btn_cfields_display_order'}" />
    	</div>
    </form>
    </div>
{/if}


{if $other_cf ne ""}
  <div class="workBack">
    <h2>{lang_get s='title_available_cfields'}</h2>
    <form id="cf_assignment" method="post">
      <table class="simple" style="width: 50%;">
      	<tr>
      		<th style="width: 10px;"></th>
      		<th>{lang_get s="name"}</th>
      		<th>{lang_get s="label"}</th>
      	</tr>
      	{foreach key=cf_id item=cf from=$other_cf}
      	<tr>
      		<td><input type="checkbox" name="cfield[{$cf.id}]" /></td>
      		<td class="bold">{$cf.name|escape}</td>
      		<td class="bold">{$cf.label|escape}</span></td>
      	</tr>
      	{/foreach}
      </table>
    	<div class="groupBtn">
    		<input type="submit" name="assign" value="{lang_get s='btn_assign'}" />
    	</div>
    </form>
    </div>
{/if}

</body>
</html>