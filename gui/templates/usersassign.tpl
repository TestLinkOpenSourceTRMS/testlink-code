{* 
Testlink: smarty template - 
$Id: usersassign.tpl,v 1.11 2007/10/04 20:05:11 asielb Exp $ 

rev:
    20070818 - franciscom
    added logic to display effective role for test project and test plan
    given user info about inheritenance.

    20070829 - jbarchibald
      -  bug 1000  - Testplan User Role Assignments
    
*}
{include file="inc_head.tpl" jsValidate="yes"}

<body>

<h1>{lang_get s='title_user_mgmt'} - {lang_get s='title_assign_roles'}</h1>
{assign var="umgmt" value="lib/usermanagement"}
{* tabs *}
<div class="tabMenu">
{if $mgt_users == "yes"}
	<span class="unselected"><a href="{$umgmt}/usersedit.php">{lang_get s='menu_new_user'}</a></span> 
	<span class="unselected"><a href="{$umgmt}/usersview.php">{lang_get s='menu_view_users'}</a></span>
{/if}
{if $role_management == "yes"}
	<span class="unselected"><a href="{$umgmt}/rolesedit.php">{lang_get s='menu_define_roles'}</a></span> 
{/if}
	<span class="unselected"><a href="{$umgmt}/rolesview.php">{lang_get s='menu_view_roles'}</a></span>
	{if $feature == 'testproject'}
		{if $tproject_user_role_assignment == "yes"}
			<span class="selected">{lang_get s='menu_assign_testproject_roles'}</span> 
		{/if}
		{if $tp_user_role_assignment == "yes"}
			<span class="unselected"><a href="{$umgmt}/usersassign.php?feature=testplan">{lang_get s='menu_assign_testplan_roles'}</a></span>
		{/if}
	{else}
		{if $tproject_user_role_assignment == "yes"}
			<span class="unselected"><a href="{$umgmt}/usersassign.php?feature=testproject">{lang_get s='menu_assign_testproject_roles'}</a></span>
		{/if}
		{if $tp_user_role_assignment == "yes"}
			<span class="selected">{lang_get s='menu_assign_testplan_roles'}</span> 
		{/if}
	{/if}
</div>


<div class="workBack">

  {include file="inc_update.tpl" result=$result item="$feature" action="$action" user_feedback=$user_feedback}


{* 20070227 - franciscom
   Because this page can be reloaded due to a test project change done by
   user on navBar.tpl, if method of form below is post we don't get
   during refresh feature, and then we have a bad refresh on page getting a bug.
*}
{if $features neq ''}
  <form method="get" action="{$umgmt}/usersassign.php">
  	<input type="hidden" name="featureID" value="{$featureID}" />
  	<input type="hidden" name="feature" value="{$feature}" />
    	<div>
    	<table border='0'>
    	{if $feature == 'testproject'}
    		<tr><td class="labelHolder">{lang_get s='TestProject'}</td><td>&nbsp;<td>
    	{else}
    		<tr><td class="labelHolder">{lang_get s='TestProject'}{$smarty.const.TITLE_SEP}</td><td>{$tproject_name}</td><tr>
    		<tr><td class="labelHolder">{lang_get s='TestPlan'}</td>
    	{/if}
    	<td>
        <select id="featureSel" onchange="changeFeature('{$feature}')">
    	   {foreach from=$features item=f}
    	     <option value="{$f.id}" {if $featureID == $f.id} selected="selected" {/if}>
    	     {$f.name|escape}</option>
    	     {if $featureID == $f.id}
    	        {assign var="my_feature_name" value=$f.name}
    	     {/if}
    	   {/foreach}
    	   </select>
    	</td>
    	
      <td>
    	<input type="button" value="{lang_get s='btn_change'}" onclick="changeFeature('{$feature}');"/>
    	</td>
   		</tr>
  		</table>
    	</div>
      <p></p>
    	<table class="common" width="75%">
    	<tr>
    		<th>{lang_get s='User'}</th>
    		<th>{lang_get s=th_roles_$feature} ({$my_feature_name|escape})</th>
    	</tr>
    	{foreach from=$userData item=user}
    	<tr bgcolor="{cycle values="#eeeeee,#d0d0d0"}">
    		<td>{$user.fullname|escape}</td>
    		<td>
    			{assign var=uID value=$user.id}
          {* --------------------------------------------------------------------- *}
          {* get role name to add to inherited in order to give 
             better information to user
          *}
          {if $userFeatureRoles[$uID].is_inherited == 1 }
            {assign var="ikx" value=$userFeatureRoles[$uID].effective_role_id }
          {else}
            {assign var="ikx" value=$userFeatureRoles[$uID].uplayer_role_id }
          {/if}
          {assign var="inherited_role_name" value=$optRights[$ikx] }
          {* --------------------------------------------------------------------- *}
         
		     <select name="userRole[{$uID}]" id="userRole[{$uID}]">
		      {foreach key=role_id item=role_description from=$optRights}
		        <option value="{$role_id}"
		          {if ($userFeatureRoles[$uID].effective_role_id == $role_id && 
		               $userFeatureRoles[$uID].is_inherited==0) || 
		               ($role_id == $smarty.const.TL_ROLES_INHERITED && 
		                $userFeatureRoles[$uID].is_inherited==1) }
		            selected="selected" {/if}  >
                {$role_description|escape}
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
    	<div class="groupBtn">	
    		<input type="submit" name="do_update" value="{lang_get s='btn_upd_user_data'}" />
    	</div>
  
  </form>
  <hr />
{/if} {* if $features *}


</div>

</body>
</html>
