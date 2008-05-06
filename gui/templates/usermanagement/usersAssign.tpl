{* 
Testlink: smarty template - 
$Id: usersAssign.tpl,v 1.10 2008/05/06 06:26:13 franciscom Exp $ 

rev:
    20070818 - franciscom
    added logic to display effective role for test project and test plan
    given user info about inheritenance.

    20070829 - jbarchibald
      -  bug 1000  - Testplan User Role Assignments
    
*}
{include file="inc_head.tpl" jsValidate="yes" openHead="yes" enableTableSorting="yes"}
{include file="inc_ext_js.tpl" css_only=1}
</head>
<body>

<h1 class="title">{lang_get s='title_user_mgmt'} - {lang_get s='title_assign_roles'}</h1>
{assign var="umgmt" value="lib/usermanagement"}

{***** TABS *****}
{include file="usermanagement/tabsmenu.tpl"}


<div class="workBack">

  {include file="inc_update.tpl" result=$result item="$feature" action="$action" user_feedback=$user_feedback}


{* 20070227 - franciscom
   Because this page can be reloaded due to a test project change done by
   user on navBar.tpl, if method of form below is post we don't get
   during refresh feature, and then we have a bad refresh on page getting a bug.
*}
{if $features neq ''}
  <form method="get" action="{$umgmt}/usersAssign.php">
  	<input type="hidden" name="featureID" value="{$featureID}" />
  	<input type="hidden" name="feature" value="{$feature}" />
    <div>
    	<table border='0'>
    	{if $feature == 'testproject'}
    		<tr><td class="labelHolder">{lang_get s='TestProject'}</td><td>&nbsp;<td>
    	{else}
    		<tr><td class="labelHolder">{lang_get s='TestProject'}{$smarty.const.TITLE_SEP}</td><td>{$tproject_name}</td></tr>
    		<tr>
				<td class="labelHolder">{lang_get s='TestPlan'}</td>
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
	    <table class="common sortable" width="75%">
    	<tr>
    		<th>{$sortHintIcon}{lang_get s='User'}</th>
    		<th>{$sortHintIcon}{lang_get s=th_roles_$feature} ({$my_feature_name|escape})</th>
    	</tr>
    	{foreach from=$userData item=user}
    	<tr bgcolor="{cycle values="#eeeeee,#d0d0d0"}">
    		<td>{$user->getDisplayName()|escape}</td>
    		<td>
    			{assign var=uID value=$user->dbID}
          {* --------------------------------------------------------------------- *}
          {* get role name to add to inherited in order to give 
             better information to user
          *}
          {if $userFeatureRoles[$uID].is_inherited == 1 }
            {assign var="ikx" value=$userFeatureRoles[$uID].effective_role_id }
          {else}
            {assign var="ikx" value=$userFeatureRoles[$uID].uplayer_role_id }
          {/if}
			{assign var="inherited_role_name" value=$optRights[$ikx]->name }
             <select name="userRole[{$uID}]">
		      {foreach key=role_id item=role from=$optRights}
		        <option value="{$role_id}"
		          {if ($userFeatureRoles[$uID].effective_role_id == $role_id && 
		               $userFeatureRoles[$uID].is_inherited==0) || 
		               ($role_id == $smarty.const.TL_ROLES_INHERITED && 
		                $userFeatureRoles[$uID].is_inherited==1) }
		            selected="selected" {/if}  >
                {$role->name|escape}
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
