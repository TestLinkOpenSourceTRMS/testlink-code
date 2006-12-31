{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
Id: reqAssign.tpl,v 1.6 2006/07/15 19:55:30 schlundus Exp $
Purpose: management Custom fields assignment to a test project

*}
{include file="inc_head.tpl"}

<body>

<h1>
	{lang_get s='cfields_tproject_assign'}{$gsmarty_title_sep_type2}{lang_get s="testproject"}{$gsmarty_title_sep}{$tproject_name|escape}
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
      		<th style="width: 10px;">{lang_get s="cfields_active"}</th>
      	</tr>
      	{foreach key=cf_id item=cf from=$my_cf}
      	<tr>
      		<td><input type="checkbox" name="cfield[{$cf.id}]" /></td>
      		<td class="bold">{$cf.name|escape}</td>
      		<td class="bold">{$cf.label|escape}</span></td>
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
    	</div>
    </form>
    </div>
{/if}


{if $other_cf ne ""}
  <div class="workBack">
    <h2>{lang_get s='title_available_cfields'}</h2>
    <form id="cf_assignment" method="post">
      <table class="simple">
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