{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: cfieldsTprojectAssign.tpl,v 1.4 2008/05/06 06:25:57 franciscom Exp $
Purpose: management Custom fields assignment to a test project

rev :
     20070527 - franciscom - added check/uncheck all logic
     20070515 - franciscom - BUGID 0000852 

*}
{include file="inc_head.tpl" openHead="yes"}
{include file="inc_jsCheckboxes.tpl"}
</head>

<body>
{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

<h1 class="title">
{lang_get s='cfields_tproject_assign'}{$smarty.const.TITLE_SEP_TYPE2}{lang_get s="testproject"}{$smarty.const.TITLE_SEP}{$tproject_name|escape}
</h1>

{include file="inc_update.tpl" result=$sqlResult action=$action item="custom_field"}


{if $my_cf ne ""}
  <div class="workBack">
    <h2>{lang_get s='title_assigned_cfields'}</h2>
    <form method="post">
      <div id="assigned_cf"> 
 	    {* used as memory for the check/uncheck all checkbox javascript logic *}
       <input type="hidden" name="memory_assigned_cf"  
                            id="memory_assigned_cf"  value="0" />
      <table class="simple">
      	<tr>
      		<th align="center"  style="width: 5px;background-color:#005498;"> 
      		    <img src="{$smarty.const.TL_THEME_IMG_DIR}/toggle_all.gif"
      		             onclick='cs_all_checkbox_in_div("assigned_cf","assigned_cfield","memory_assigned_cf");'
      		             title="{lang_get s='check_uncheck_all_checkboxes'}" />
      		</th>
      		<th width="40%">{lang_get s="name"}</th>
      		<th width="40%">{lang_get s="label"}</th>
      		<th width="15%">{lang_get s="display_order"}</th>
      		<th width="5%">{lang_get s="cfields_active"}</th>
      	</tr>
      	{foreach key=cf_id item=cf from=$my_cf}
      	<tr>
      		<td class="clickable_icon"><input type="checkbox" id="assigned_cfield{$cf.id}" name="cfield[{$cf.id}]" /></td>
   		   	<td class="bold"><a href="lib/cfields/cfieldsEdit.php?do_action=edit&amp;cfield_id={$cf.id}"
   		   	                    title="{lang_get s='manage_cfield'}">{$cf.name|escape}</a></td>
      		<td class="bold">{$cf.label|escape}</td>
      		<td><input type="text" name="display_order[{$cf.id}]" 
      		           value="{$cf.display_order}" 
      		           size="{#DISPLAY_ORDER_SIZE#}" maxlength="{#DISPLAY_ORDER_MAXLEN#}" /></td>
      		<td><input type="checkbox" name="active_cfield[{$cf.id}]" 
      		                           {if $cf.active eq 1} checked="checked" {/if} /> 
      		    <input type="hidden"   name="hidden_active_cfield[{$cf.id}]" 
      		                           value="{$cf.active}" /> 
      		</td>
      	</tr>
      	{/foreach}
      </table>
    	</div>
    	<div class="groupBtn">
        
        <input type="hidden" name="doAction" value="" />
    	  
    		<input type="submit" name="doUnassign" value="{lang_get s='btn_unassign'}" 
    		                     onclick="doAction.value=this.name"/>
    		                     
    		<input type="submit" name="doActiveMgmt" value="{lang_get s='btn_cfields_active_mgmt'}"
    		                     onclick="doAction.value=this.name"/>

    		<input type="submit" name="doReorder" value="{lang_get s='btn_cfields_display_order'}" 
    		                     onclick="doAction.value=this.name"/>
    		
    	</div>
    </form>
    </div>
{/if}


{if $other_cf ne ""}
  <div class="workBack">
    <h2>{lang_get s='title_available_cfields'}</h2>
    <form method="post">
      <div id="free_cf"> 
 	    {* used as memory for the check/uncheck all checkbox javascript logic *}
       <input type="hidden" name="memory_free_cf"  
                            id="memory_free_cf"  value="0" />

      <table class="simple" style="width: 50%;">
      	<tr>
      		<th align="center"  style="width: 5px;background-color:#005498;"> 
      		    <img src="{$smarty.const.TL_THEME_IMG_DIR}/toggle_all.gif"
      		             onclick='cs_all_checkbox_in_div("free_cf","free_cfield","memory_free_cf");'
      		             title="{lang_get s='check_uncheck_all_checkboxes'}" />
      		</th>
      		<th>{lang_get s="name"}</th>
      		<th>{lang_get s="label"}</th>
      	</tr>
      	{foreach key=cf_id item=cf from=$other_cf}
      	<tr>
      		<td class="clickable_icon"> <input type="checkbox" id="free_cfield{$cf.id}" name="cfield[{$cf.id}]" /></td>
      		<td class="bold">{$cf.name|escape}</td>
      		<td class="bold">{$cf.label|escape}</td>
      	</tr>
      	{/foreach}
      </table>
    	</div>
    	<div class="groupBtn">
        <input type="hidden" name="doAction" value="" />
    		<input type="submit" name="doAssign" id=this.name value="{lang_get s='btn_assign'}" 
    		                     onclick="doAction.value=this.name"/>
    	</div>
    </form>
    </div>
{/if}

</body>
</html>