{*
Testlink Open Source Project - http://testlink.sourceforge.net/

users overview
@filesource usersView.tpl
@internal revisions
*}

{include file="inc_head.tpl" openHead="yes"}
{include file="inc_del_onclick.tpl"}

{assign var="userActionMgr" value="lib/usermanagement/usersEdit.php"}
{assign var="createUserAction" value="$userActionMgr?doAction=create"}

<script type="text/javascript">
var del_action=fRoot+"lib/usermanagement/usersView.php?operation=disable&user=";
</script>

{foreach from=$gui->tableSet key=idx item=matrix name="initializer"}
  {assign var=tableID value=$matrix->tableID}
  {if $smarty.foreach.initializer.first}
    {$matrix->renderCommonGlobals()}
    {if $matrix instanceof tlExtTable}
        {include file="inc_ext_js.tpl" bResetEXTCss=1}
        {include file="inc_ext_table.tpl"}
    {/if}
  {/if}
  {$matrix->renderHeadSection()}
{/foreach}

<style type=text/css>
.x-action-col-cell img.normal_user {ldelim}
    height: 16px;
    width: 16px;
    background-image: url({$tlImages.delete});
{rdelim}

.x-action-col-cell img.special_user {ldelim}
    height: 16px;
    width: 16px;        
    background-image: url({$tlImages.demo_mode});
{rdelim}
</style>
</head>


{lang_get var="labels"
          s="title_user_mgmt,th_login,title_user_mgmt,th_login,th_first_name,th_last_name,th_email,
             th_role,order_by_role_descr,order_by_role_dir,th_locale,th_active,th_api,th_delete,
             disable,alt_edit_user,Yes,No,alt_delete_user,no_permissions_for_action,btn_create,
             show_inactive_users,hide_inactive_users,alt_disable_user,order_by_login,
             order_by_login_dir,alt_active_user,demo_special_user"}

<body>
{if $gui->grants->user_mgmt == "yes"}

	<h1 class="title">{$labels.title_user_mgmt}</h1>
	{assign var=grants value=$gui->grants}  {* transitional code *}
  {include file="usermanagement/tabsmenu.tpl"}
	<div class="workBack">

	  {include file="inc_update.tpl" result=$gui->result item="user" 
	           action=$gui->action user_feedback=$gui->user_feedback}
	           
	  {foreach from=$gui->tableSet key=idx item=matrix}
      {$matrix->renderBodySection()}
    {/foreach}

		<div class="groupBtn">
		<form method="post" action="{$createUserAction}" name="launch_create">
		  <input type="hidden" id="operation" name="operation" value="" />
		  <input type="submit" name="doCreate"  value="{$labels.btn_create}" />
  	</form>
		</div>
	</div>
	
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
	<a href="{$gui->basehref}" alt="Home">Home</a>
{/if}
</body>
</html>