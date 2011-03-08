{*
Testlink Open Source Project - http://testlink.sourceforge.net/
$Id: usersView.tpl,v 1.26 2010/10/17 09:46:37 franciscom Exp $

Purpose: smarty template - users overview

rev:
  20101017 - franciscom - image access refactored (tlImages)
  20100923 - Julian - BUGID 3802
  20100426 - asimon - removed forgotten comment end sign (template syntax error)
  20100419 - franciscom - BUGID 3355: A user can not be deleted from the list
  20100326 - franciscom - BUGID 3324
*}
{include file="inc_head.tpl" openHead="yes"}
{include file="inc_del_onclick.tpl"}

{assign var="userActionMgr" value="lib/usermanagement/usersEdit.php"}
{assign var="createUserAction" value="$userActionMgr?doAction=create"}
{assign var="editUserAction" value="$userActionMgr?doAction=edit&amp;user_id="}

{lang_get s='warning_disable_user' var="warning_msg"}
{lang_get s='disable' var="del_msgbox_title"}

<script type="text/javascript">
	var del_action=fRoot+"lib/usermanagement/usersView.php?operation=disable&user=";
</script>

{literal}
<script type="text/javascript">
function toggleRowByClass(oid,className,displayValue)
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
          trTags[idx].style.display = 'none';
        }
        else
        {
          trTags[idx].style.display = 'table-row';
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


{lang_get var="labels"
          s="title_user_mgmt,th_login,title_user_mgmt,th_login,th_first_name,th_last_name,th_email,
             th_role,order_by_role_descr,order_by_role_dir,th_locale,th_active,th_api,th_delete,
             disable,alt_edit_user,Yes,No,alt_delete_user,no_permissions_for_action,btn_create,
             show_inactive_users,hide_inactive_users,alt_disable_user,order_by_login,order_by_login_dir,alt_active_user"}

<body {$body_onload}>

{if $grants->user_mgmt == "yes"}

	<h1 class="title">{$labels.title_user_mgmt}</h1>
	{***** TABS *****}
  {include file="usermanagement/tabsmenu.tpl"}

	{***** existing users form *****}
	<div class="workBack">
		<form method="post" action="lib/usermanagement/usersView.php" name="usersview" id="usersview">
		<input type="hidden" id="operation" name="operation" value="" />
		<input type="hidden" id="order_by_role_dir" name="order_by_role_dir" value="{$order_by_role_dir}" />
		<input type="hidden" id="order_by_login_dir" name="order_by_login_dir" value="{$order_by_login_dir}" />
		<input type="hidden" id="user_order_by" name="user_order_by" value="{$user_order_by}" />

	  {include file="inc_update.tpl" result=$result item="user" action="$action" user_feedback=$user_feedback}
    {$labels.hide_inactive_users}
    <input name="hide_inactive_users" id="hide_inactive_users" type="checkbox" {$checked_hide_inactive_users} 
           value="on" onclick="toggleRowByClass('hide_inactive_users','inactive_user')">
		<table class="simple">
			<tr>
				<th {if $user_order_by == 'order_by_login'}style="background-color: #c8dce8;color: black;"{/if}>
				    {$labels.th_login}
				    <img src="{$smarty.const.TL_THEME_IMG_DIR}/order_{$order_by_login_dir}.gif"
				         title="{$labels.order_by_login} {lang_get s=$order_by_login_dir}"
						     alt="{$labels.order_by_role_descr} {lang_get s=$order_by_role_dir}"
				         onclick="usersview.operation.value='order_by_login';
				                  usersview.user_order_by.value='order_by_login';
				                  usersview.submit();" />
				</th>

				<th>{$labels.th_first_name}</th>
				<th>{$labels.th_last_name}</th>
				<th>{$labels.th_email}</th>

				<th {if $user_order_by == 'order_by_role'}style="background-color: #c8dce8;color: black;"{/if}>
				    {$labels.th_role}
	    			<img src="{$smarty.const.TL_THEME_IMG_DIR}/order_{$order_by_role_dir}.gif"
	    			     title="{$labels.order_by_role_descr} {lang_get s=$order_by_role_dir}"
						 alt="{$labels.order_by_role_descr} {lang_get s=$order_by_role_dir}"
	    			     onclick="usersview.operation.value='order_by_role';
	    			              usersview.user_order_by.value='order_by_role';
	      			            usersview.submit();" />
				</th>

				<th>{$labels.th_locale}</th>
				<th style="width:50px;">{$labels.th_active}</th>
				<th style="width:50px;">{$labels.disable}</th>
			</tr>

      {foreach from=$users item=userObj}
 			  {assign var="r_n" value=$userObj->globalRole->name}
				{assign var="r_d" value=$userObj->globalRole->getDisplayName()}
        {if $userObj->isActive eq 1}
          {assign var="user_row_class" value=''}
        {else}
          {assign var="user_row_class" value='class="inactive_user"'}
        {/if}
				<tr {$user_row_class} {if $role_colour[$r_n] neq ''} style="background-color: {$role_colour[$r_n]};" {/if}>
				<td><a href="{$editUserAction}{$userObj->dbID}">
				    {$userObj->login|escape}
			      {if $gsmarty_gui->show_icon_edit}
				      <img title="{$labels.alt_edit_user}"
				           alt="{$labels.alt_edit_user}" src="{$smarty.const.TL_THEME_IMG_DIR}/icon_edit.png"/>
				    {/if}
				    </a>
				</td>
				<td>{$userObj->firstName|escape}</td>
				<td>{$userObj->lastName|escape}</td>
				<td>{$userObj->emailAddress|escape}</td>
				<td>{$r_d|escape}</td>
				<td>
				 {* BUGID 3802 *}
				 {assign var="user_locale" value=$userObj->locale}
				 {$optLocale.$user_locale|escape}
				</td>
				<td align="center">
					{if $userObj->isActive eq 1}
				  		<img style="border:none" title="{$labels.alt_active_user}" alt="{$labels.alt_active_user}"  
				  		     src="{$tlImages.checked}"/>
  			  {else}
  				    &nbsp;
        	{/if}
				</td>
				<td align="center">
				  <img style="border:none;cursor: pointer;" alt="{$labels.alt_disable_user}"
					     title="{$labels.alt_disable_user}" src="{$tlImages.delete}"
					     onclick="delete_confirmation({$userObj->dbID},'{$userObj->login|escape:'javascript'|escape}',
					                                  '{$del_msgbox_title}','{$warning_msg}');" />
				</td>
			</tr>
			{/foreach}
		</table>
		</form>

		<div class="groupBtn">
		<form method="post" action="{$createUserAction}" name="launch_create">
		<input type="submit" name="doCreate"  value="{$labels.btn_create}" />
  		</form>
		</div>
	</div>
	
	{*  BUGID 0000103: Localization is changed but not strings *}
	{if $update_title_bar == 1}
	{literal}
	<script type="text/javascript">
		parent.titlebar.location.reload();
	</script>
	{/literal}
	{/if}
	{if $reload == 1}
	{literal}
	<script type="text/javascript">
		top.location.reload();
	</script>
	{/literal}
	{/if}
{else}
	{$labels.no_permissions_for_action}<br />
	<a href="{$base_href}" alt="Home">Home</a>
{/if}
</body>
</html>