{* 
Testlink: smarty template - 
@filesource usersAssign.tpl

*}
{lang_get var="labels" 
          s='TestProject,TestPlan,btn_change,title_user_mgmt,set_roles_to,show_only_authorized_users,
             warn_demo,User,btn_upd_user_data,btn_do,title_assign_roles,
             filter_all,search_user_hint,btn_filter,prev_page,next_page,
             users_shown_of'}

{include file="inc_head.tpl" jsValidate="yes" 
  openHead="yes" enableTableSorting="yes"}
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

{* ------------------------------------------------------------------------------------------------ *}
{*
   IMPORTANT DEVELOPMENT NOTICE
   User listing/filtering/pagination are now done SERVER SIDE
   (MantisBT-style, see getFilteredUsersPage() on usersAssign.php),
   so the client-side DataTables includes are no longer used here.
*}
{* ------------------------------------------------------------------------------------------------ *}

{include file="bootstrap.inc.tpl"}

</head>
<body>

<h1 class="title">{$gui->main_title}</h1>
{$umgmt="lib/usermanagement"}
{$my_feature_name=''}

{include file="usermanagement/menu.inc.tpl"}
<div class="workBack">

{include file="inc_update.tpl" 
         result=$result 
         item=$gui->featureType 
         action=$action
         user_feedback=$gui->user_feedback}

{* 
Because this page can be reloaded due to a test project change done by
user on navBar.tpl, if method of form below is post we don't get
during refresh feature, and then we have a bad refresh on page getting a bug.
*}

{if $gui->features neq ''}
{* MantisBT-style filter toolbar: initial-letter chips + user name search.
   NOTE: the GET search form must NOT be nested inside the main POST form
   below: an inner <form> start tag makes the HTML parser terminate the
   outer form early, which orphans the user table and the do_update button. *}
    {if $gui->pager->enabled}
    <div class="panel panel-default" style="background-color: #EAEAED;">
      <div class="panel-body">
        <div class="btn-toolbar" role="toolbar">
          <div class="btn-group">
            <a class="btn btn-xs btn-default {if $gui->pager->filter == 'ALL'}active{/if}"
               href="{$basehref}lib/usermanagement/usersAssign.php?featureType={$gui->featureType}&amp;featureID={$gui->featureID}&amp;search={$gui->pager->search|escape:urlencode}">
               {$labels.filter_all} <span class="badge">{$gui->pager->all_count}</span></a>
            {foreach item=ch from=array_merge(array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','0','1','2','3','4','5','6','7','8','9'))}
              {if isset($gui->pager->letter_counts.$ch)}
                <a class="btn btn-xs btn-default {if $gui->pager->filter == $ch}active{/if}"
                   href="{$basehref}lib/usermanagement/usersAssign.php?featureType={$gui->featureType}&amp;featureID={$gui->featureID}&amp;filter={$ch}&amp;search={$gui->pager->search|escape:urlencode}">
                   {$ch} <span class="badge">{$gui->pager->letter_counts.$ch}</span></a>
              {/if}
            {/foreach}
          </div>
        </div>
        <div class="pull-right">
          <form method="get" action="{$basehref}lib/usermanagement/usersAssign.php" class="form-inline" role="search">
            <input type="hidden" name="featureType" value="{$gui->featureType}" />
            <input type="hidden" name="featureID" value="{$gui->featureID}" />
            <input type="hidden" name="filter" value="{$gui->pager->filter}" />
            <label for="userSearchInput">{$labels.User}:</label>
            <input id="userSearchInput" type="text" name="search" value="{$gui->pager->search|escape}"
                   placeholder="{$labels.search_user_hint}" />
            <input type="submit" class="btn btn-default" value="{$labels.btn_filter}" />
          </form>
        </div>
      </div>
    </div>
    {/if}
{/if}

{if $gui->features neq ''}
<form method="post" action="{$umgmt}/usersAssign.php"
	{if $tlCfg->demoMode}
		onsubmit="alert('{$labels.warn_demo}'); return false;"
	{/if}>
	<input type="hidden" name="featureID" value="{$gui->featureID}" />
	<input type="hidden" name="featureType" value="{$gui->featureType}" />
	{* keep paging/filter context so an update re-renders the same subset *}
	<input type="hidden" name="page_number" value="{$gui->pager->page_number}" />
	<input type="hidden" name="filter" value="{$gui->pager->filter}" />
	<input type="hidden" name="search" value="{$gui->pager->search}" />
	<input type="hidden" name="per_page" value="{$gui->pager->per_page}" />

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
      {* total matching users badge (MantisBT style) *}
      {if $gui->pager->enabled}
        <div class="pull-right" style="padding: 4px 8px;">
          <strong>{$labels.title_assign_roles}:</strong> <span class="badge">{$gui->pager->total_users}</span>
          {if $gui->pager->total_users > 0}
            <small>({$labels.users_shown_of}: {$gui->pager->first_row}-{$gui->pager->last_row} / {$gui->pager->total_users})</small>
          {/if}
        </div>
      {/if}
	    <table class="table table-bordered no-sortable" width="100%" id="item_view">
        <thead class="thead-dark">
          <tr>
            <th>{$labels.User}</th>
            {$featureVerbose=$gui->featureType}
            <th>{lang_get s="th_roles_$featureVerbose"} ({$my_feature_name|escape})</th>
          </tr>
        </thead>
      <tbody>  
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
        </tbody>
	    </table>

    {* MantisBT-style pagination footer (only when more than one page) *}
    {if $gui->pager->enabled && $gui->pager->page_count > 1}
    <div class="groupBtn" style="text-align:right;">
      <ul class="pagination pagination-sm" style="margin: 0;">
        <li {if !$gui->pager->has_prev}class="disabled"{/if}>
          <a {if $gui->pager->has_prev}href="{$basehref}lib/usermanagement/usersAssign.php?featureType={$gui->featureType}&amp;featureID={$gui->featureID}&amp;filter={$gui->pager->filter}&amp;search={$gui->pager->search|escape:urlencode}&amp;page_number={$gui->pager->page_number - 1}""{/if}>
            &laquo; {$labels.prev_page}</a>
        </li>
        {foreach item=pl from=$gui->pager->page_links}
          {if $pl.ellipsis}
            <li class="disabled"><a>&hellip;</a></li>
          {else}
            <li {if $pl.active}class="active"{/if}>
              <a {if !$pl.active}href="{$basehref}lib/usermanagement/usersAssign.php?featureType={$gui->featureType}&amp;featureID={$gui->featureID}&amp;filter={$gui->pager->filter}&amp;search={$gui->pager->search|escape:urlencode}&amp;page_number={$pl.page}""{/if}>
                {$pl.label|escape}</a>
            </li>
          {/if}
        {/foreach}
        <li {if !$gui->pager->has_next}class="disabled"{/if}>
          <a {if $gui->pager->has_next}href="{$basehref}lib/usermanagement/usersAssign.php?featureType={$gui->featureType}&amp;featureID={$gui->featureID}&amp;filter={$gui->pager->filter}&amp;search={$gui->pager->search|escape:urlencode}&amp;page_number={$gui->pager->page_number + 1}""{/if}>
            {$labels.next_page} &raquo;</a>
        </li>
      </ul>
    </div>
    {/if}
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
