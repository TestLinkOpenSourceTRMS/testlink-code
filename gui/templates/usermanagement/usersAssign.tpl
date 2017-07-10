{* 
Testlink: smarty template - 
@filesource usersAssign.tpl

@internal revisions
@since 1.9.15
*}
{lang_get var="labels" 
          s='TestProject,TestPlan,btn_change,title_user_mgmt,set_roles_to,show_only_authorized_users,
             warn_demo,User,btn_upd_user_data,btn_do,title_assign_roles'}

{include file="inc_head.tpl" jsValidate="yes" openHead="yes" enableTableSorting="yes"}
{include file="inc_ext_js.tpl" css_only=1}

{include file="bootstrap.inc.tpl"}

<script language="JavaScript" type="text/javascript">
/*
Set value for a group of combo (have same prefix).
MUST TO BE PLACED IN COMMON LIBRARY
*/
function set_combo_group(container_id,combo_id_prefix,value_to_assign)
{
  var container=document.getElementById(container_id);
	var all_comboboxes = container.getElementsByTagName('select');
	var input_element;
	var idx=0;

	for(idx = 0; idx < all_comboboxes.length; idx++)
	{
	  input_element=all_comboboxes[idx];
		if( input_element.type == "select-one" && 
		    input_element.id.indexOf(combo_id_prefix)==0 &&
		   !input_element.disabled)
		{
       input_element.value=value_to_assign;
		}	
	}
}
</script>

<script type="text/javascript">
function toggleRowByClass(oid,className,displayCheckOn,displayCheckOff,displayValue)
{
  var trTags = document.getElementsByTagName("tr");
  var cbox = document.getElementById(oid);
  
  for( idx=0; idx < trTags.length; idx++ ) 
  {
    if( trTags[idx].className == className ) 
    {
      if( displayValue == undefined )
      {
        if( cbox.checked )
        {
          trTags[idx].style.display = displayCheckOn;
        }
        else
        {
          trTags[idx].style.display = displayCheckOff;
        }
      } 
      else
      {
        trTags[idx].style.display = displayValue;
      }
    }
  }

}
</script>

{if $tlCfg->gui->usersAssign->pagination->enabled}
  {$ll = $tlCfg->gui->usersAssign->pagination->length}
  {include file="DataTables.inc.tpl" DataTablesOID="item_view" DataTableslengthMenu=$ll}
{/if}

</head>
<body>

<h1 class="title">{$gui->main_title}</h1>
{$umgmt="lib/usermanagement"}
{$my_feature_name=''}

{include file="usermanagement/menu.inc.tpl"}
<div class="workBack">

{include file="inc_update.tpl" result=$result item="$gui->featureType" action="$action" user_feedback=$gui->user_feedback}

{* 
Because this page can be reloaded due to a test project change done by
user on navBar.tpl, if method of form below is post we don't get
during refresh feature, and then we have a bad refresh on page getting a bug.
*}

{if $gui->features neq ''}
<form method="post" action="{$umgmt}/usersAssign.php"
	{if $tlCfg->demoMode}
		onsubmit="alert('{$labels.warn_demo}'); return false;"
	{/if}>
	<input type="hidden" name="featureID" value="{$gui->featureID}" />
	<input type="hidden" name="featureType" value="{$gui->featureType}" />

  {$styleLH="padding: 0px 30px 10px 5px;"}
  <div class="panel panel-default" style="background-color: #EAEAED;">
    <div class="panel-body">
		<table style="border:0;">
    	{if $gui->featureType == 'testproject'}
    		<tr>
          <td class="labelHolder" style="{$styleLH}">{$labels.TestProject}{$gui->accessTypeImg}</td>
          <td>&nbsp;</td>
    	{else}
    		<tr>
          <td class="labelHolder" style="{$styleLH}">{$labels.TestProject}{$gui->tprojectAccessTypeImg}</td>
          <td>{$gui->tproject_name|escape}</td>
        </tr>
    		<tr>
				  <td class="labelHolder" style="{$styleLH}">{$labels.TestPlan}{$gui->accessTypeImg}
          </td>
    	{/if}

		    	<td>
            <select id="featureSel" onchange="changeFeature('{$gui->featureType}')">
		    	   {foreach from=$gui->features item=f}
		    	     <option value="{$f.id}" {if $gui->featureID == $f.id} selected="selected" {/if}>
		    	     {$f.name|escape}</option>
		    	     {if $gui->featureID == $f.id}
		    	        {$my_feature_name=$f.name}
		    	     {/if}
		    	   {/foreach}
		    	   </select>
		    	</td>
			<td>
          {* 
					<input type="button" value="{$labels.btn_change}" onclick="changeFeature('{$gui->featureType}');"/>
          *}
		  </td>
			</tr>
   		<tr>
   		<td class="labelHolder" style="{$styleLH}"">{$labels.set_roles_to}</td>{if $gui->featureType == 'testproject'} <td>&nbsp;</td> {/if}
      <td>
        <select name="allUsersRole" id="allUsersRole">
		      {foreach key=role_id item=role from=$gui->optRights}
		        <option value="{$role_id}">
            {$role->getDisplayName()|escape}
		        </option>
		      {/foreach}
			  </select>
      </td>
      <td>
          &nbsp;
					<input type="button" value="{$labels.btn_do}" 
					       onclick="javascript:set_combo_group('usersRoleTable','userRole_',
					                                           document.getElementById('allUsersRole').value);"/>
		  </td>
			</tr>

		</table>
    </div>
    </div>

    <div id="usersRoleTable">
	    <table class="common table table-bordered sortable" width="100%" id="item_view">
    	<tr>
    		<th>{$tlImages.sort_hint}{$labels.User}</th>
    		{assign var="featureVerbose" value=$gui->featureType}
    		<th>{$tlImages.sort_hint}{lang_get s="th_roles_$featureVerbose"} ({$my_feature_name|escape})</th>
    	</tr>
    	{foreach from=$gui->users item=user}
    	    {$globalRoleName=$user->globalRole->name}
    			{$uID=$user->dbID}


          {* get role name to add to inherited in order to give better information to user *}
          {$effective_role_id=$gui->userFeatureRoles[$uID].effective_role_id}
          {if $gui->userFeatureRoles[$uID].is_inherited == 1}
            {$ikx=$effective_role_id}
          {else}
            {$ikx=$gui->userFeatureRoles[$uID].uplayer_role_id}
          {/if}
          {$inherited_role_name=$gui->optRights[$ikx]->name}

          {$user_row_class=''}
          {if $effective_role_id == $smarty.const.TL_ROLES_NO_RIGHTS}
            {$user_row_class='class="not_authorized_user"'}
          {/if}

    	<tr {$user_row_class} bgcolor="{cycle values="#eeeeee,#d0d0d0"}">
    		<td {if $gui->role_colour != '' && $gui->role_colour[$globalRoleName] != ''}  		
    		      style="background-color: {$gui->role_colour[$globalRoleName]};" {/if}>
    		    {$user->login|escape} ({$user->firstName|escape} {$user->lastName|escape}) </td>
    		<td>
          <select name="userRole[{$uID}]" id="userRole_{$uID}"
            {if $user->globalRole->dbID == $smarty.const.TL_ROLES_ADMIN}
             disabled="disabled"
            {/if}
          >
		      {foreach key=role_id item=role from=$gui->optRights}
            
            {$applySelected = ''}
            {if ($gui->userFeatureRoles[$uID].effective_role_id == $role_id && 
                   $gui->userFeatureRoles[$uID].is_inherited==0) || 
                   ($role_id == $smarty.const.TL_ROLES_INHERITED && 
                    $gui->userFeatureRoles[$uID].is_inherited==1)}
                {$applySelected = ' selected="selected" '} 
            {/if}

            /* For system consistency we need to remove admin role from selection */
            {$removeRole = 0}
            {if $role_id == $smarty.const.TL_ROLES_ADMIN && $applySelected == '' }
                {$removeRole = 1}
            {/if}             
  
            {if !$removeRole }
              <option value="{$role_id}" {$applySelected}>
                  {$role->getDisplayName()|escape}
                  {if $role_id == $smarty.const.TL_ROLES_INHERITED}
                    {$inherited_role_name|escape} 
                  {/if}
  		        </option>
            {/if}

		      {/foreach}
			</select>
          {if $user->globalRole->dbID == $smarty.const.TL_ROLES_ADMIN}
            {$gui->hintImg} 
          {/if}
			</td>
    	</tr>
    	{/foreach}
    	</table>
   </div> 	
   	
   	
   	<div class="groupBtn">	
    	{if $tlCfg->demoMode}
			{$labels.warn_demo}
		{else}	
    		<input type="submit" name="do_update" value="{$labels.btn_upd_user_data}" />
		{/if}
	</div>
  </form>
{/if} {* if $gui->features *}
</div>
</body>
</html>
