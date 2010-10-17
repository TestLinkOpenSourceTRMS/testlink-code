{* 
Testlink: smarty template - 
$Id: usersAssign.tpl,v 1.21 2010/10/17 09:46:37 franciscom Exp $ 

rev:
    20100930 - franciscom - BUGID 2344: Private test project
    20100314 - eloff - BUGID 3272 - send assign form via POST to allow more data to be sent
    20091129 - franciscom - ISSUE 2554 - coloruing
    20090426 - franciscom - BUGID 2442- added bulk setting management
    20070818 - franciscom
    added logic to display effective role for test project and test plan
    given user info about inheritenance.

    20070829 - jbarchibald
      -  bug 1000  - Testplan User Role Assignments
    
*}
{lang_get var="labels" 
          s='TestProject,TestPlan,btn_change,title_user_mgmt,set_roles_to,show_only_authorized_users,
             warn_demo,User,btn_upd_user_data,btn_do,title_assign_roles'}

{include file="inc_head.tpl" jsValidate="yes" openHead="yes" enableTableSorting="yes"}
{include file="inc_ext_js.tpl" css_only=1}

<script language="JavaScript" type="text/javascript">
{literal}
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
{/literal}
</script>

{literal}
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
{/literal}



</head>
<body>

<h1 class="title">{$labels.title_user_mgmt} - {$labels.title_assign_roles}</h1>
{assign var="umgmt" value="lib/usermanagement"}
{assign var="my_feature_name" value=''}

{***** TABS *****}
{assign var="highlight" value=$gui->highlight}
{assign var="grants" value=$gui->grants}

{include file="usermanagement/tabsmenu.tpl"}


<div class="workBack">

  {include file="inc_update.tpl" result=$result item="$gui->featureType" action="$action" user_feedback=$gui->user_feedback}


{* 20070227 - franciscom
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
	<div>
		<table border='0'>
    	{if $gui->featureType == 'testproject'}
    		<tr><td class="labelHolder">{$labels.TestProject}</td><td>&nbsp;</td>
    	{else}
    		<tr><td class="labelHolder">{$labels.TestProject}{$smarty.const.TITLE_SEP}</td><td>{$gui->tproject_name|escape}</td></tr>
    		<tr>
				<td class="labelHolder">{$labels.TestPlan}</td>
    	{/if}
		    	<td>
		        <select id="featureSel" onchange="changeFeature('{$gui->featureType}')">
		    	   {foreach from=$gui->features item=f}
		    	     <option value="{$f.id}" {if $gui->featureID == $f.id} selected="selected" {/if}>
		    	     {$f.name|escape}</option>
		    	     {if $gui->featureID == $f.id}
		    	        {assign var="my_feature_name" value=$f.name}
		    	     {/if}
		    	   {/foreach}
		    	   </select>
		    	</td>
			<td>
					<input type="button" value="{$labels.btn_change}" onclick="changeFeature('{$gui->featureType}');"/>
		  </td>
			</tr>
   		<tr>
   		<td class="labelHolder">{$labels.set_roles_to}</td>{if $gui->featureType == 'testproject'} <td>&nbsp;</td> {/if}
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
					<input type="button" value="{$labels.btn_do}" 
					       onclick="javascript:set_combo_group('usersRoleTable','userRole_',
					                                           document.getElementById('allUsersRole').value);"/>
		  </td>
			</tr>

    	{if $gui->featureType == 'testproject'}
   		<tr>
   		<td class="labelHolder">{$labels.show_only_authorized_users}</td><td>&nbsp;</td>
      <td> 
          <input name="show_only_authorized_users" id="show_only_authorized_users" 
                  type="checkbox" {$checked_hide_inactive_users} 
           value="on" onclick="toggleRowByClass('show_only_authorized_users','not_authorized_user','none','table-row')">
      </td>
			</tr>

    	{/if}


		</table>
    </div>
    
    <div id="usersRoleTable">
	    <table class="common sortable" width="75%">
    	<tr>
    		<th>{$tlImages.sort_hint}{$labels.User}</th>
    		{assign var="featureVerbose" value=$gui->featureType}
    		<th>{$tlImages.sort_hint}{lang_get s="th_roles_$featureVerbose"} ({$my_feature_name|escape})</th>
    	</tr>
    	{foreach from=$gui->users item=user}
    	    {assign var="globalRoleName" value=$user->globalRole->name}
    			{assign var=uID value=$user->dbID}

          {* get role name to add to inherited in order to give better information to user *}
          {assign var="effective_role_id" value=$gui->userFeatureRoles[$uID].effective_role_id}
          {if $gui->userFeatureRoles[$uID].is_inherited == 1}
            {assign var="ikx" value=$effective_role_id}
          {else}
            {assign var="ikx" value=$gui->userFeatureRoles[$uID].uplayer_role_id}
          {/if}
          {assign var="inherited_role_name" value=$gui->optRights[$ikx]->name}

          {assign var="user_row_class" value=''}
          {if $effective_role_id == $smarty.const.TL_ROLES_NO_RIGHTS}
            {assign var="user_row_class" value='class="not_authorized_user"'}
          {/if}

    	<tr {$user_row_class} bgcolor="{cycle values="#eeeeee,#d0d0d0"}">
    		<td {if $gui->role_colour != '' && $gui->role_colour[$globalRoleName] != ''}  		
    		      style="background-color: {$gui->role_colour[$globalRoleName]};" {/if}>
    		    {$user->getDisplayName()|escape}</td>
    		<td>
          <select name="userRole[{$uID}]" id="userRole_{$uID}">
		      {foreach key=role_id item=role from=$gui->optRights}
		        <option value="{$role_id}"
		          {if ($gui->userFeatureRoles[$uID].effective_role_id == $role_id && 
		               $gui->userFeatureRoles[$uID].is_inherited==0) || 
		               ($role_id == $smarty.const.TL_ROLES_INHERITED && 
		                $gui->userFeatureRoles[$uID].is_inherited==1)}
		            selected="selected" {/if} >
                {$role->getDisplayName()|escape}
                {if $role_id == $smarty.const.TL_ROLES_INHERITED}
                  {$inherited_role_name|escape} 
                {/if}
		        </option>
		      {/foreach}
			</select>
			</td>
    	</tr>
    	{/foreach}
    	</table>
   </div> 	
    	<div class="groupBtn">	
    		<input type="submit" name="do_update" value="{$labels.btn_upd_user_data}" />
    	</div>
  </form>
{/if} {* if $gui->features *}
</div>
</body>
</html>
