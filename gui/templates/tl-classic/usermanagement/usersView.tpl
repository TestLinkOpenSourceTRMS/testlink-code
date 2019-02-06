{*
Testlink Open Source Project - http://testlink.sourceforge.net/

users overview
@filesource usersView.tpl
@internal revisions

*}

{include file="inc_head.tpl" openHead="yes"}
{include file="inc_del_onclick.tpl"}

<script type="text/javascript">
var del_action=fRoot+"lib/usermanagement/usersView.php?operation=disable&user=";
</script>

{foreach from=$gui->tableSet key=idx item=matrix name="initializer"}
  {$tableID="$matrix->tableID"}
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

{include file="bootstrap.inc.tpl"}
</head>

{$userActionMgr="lib/usermanagement/usersEdit.php"}
{$createUserAction="$userActionMgr?doAction=create"}
{$editUserAction="$userActionMgr?doAction=edit"}
{$exportUsersAction="lib/usermanagement/usersExport.php"}

{lang_get var="labels"
          s="title_user_mgmt,th_login,title_user_mgmt,th_login,th_first_name,th_last_name,th_email,
             th_role,order_by_role_descr,order_by_role_dir,th_locale,th_active,th_api,th_delete,
             disable,alt_edit_user,Yes,No,alt_delete_user,no_permissions_for_action,btn_create,
             show_inactive_users,hide_inactive_users,alt_disable_user,order_by_login,btn_manage_user,
             order_by_login_dir,alt_active_user,demo_special_user,btn_export"}

<body>
{if $gui->grants->user_mgmt == "yes"}

  <h1 class="title">{$gui->main_title}</h1>
  {include file="usermanagement/menu.inc.tpl"}
  <div class="workBack">

    {include file="inc_update.tpl" result=$gui->result item="user" 
             action=$gui->action user_feedback=$gui->user_feedback}
             
    {foreach from=$gui->tableSet key=idx item=matrix}
      {$matrix->renderBodySection()}
    {/foreach}

    <div class="groupBtn">
      <span style="float:left;">  
        <form method="post" action="{$createUserAction}" name="launch_create">
          <input type="hidden" id="operation" name="operation" value="" />
          <input type="submit" name="doCreate"  value="{$labels.btn_create}" />
        </form>
      </span>

      <span>
        <form method="post" action="{$exportUsersAction}" name="launch_export" style="inline;">
          <input type="submit" id="export"  name="export" value="{$labels.btn_export}" style="margin-left: 3px;">
        </form>
      </span>

    </div>
    <div class="groupBtn">
        <form method="post" action="{$editUserAction}" name="manage_user" style="inline;">
          <input type="text" id="login"  name="login" size="{#LOGIN_SIZE#}" maxlength="{#LOGIN_MAXLEN#}"
                 placeholder="{$labels.th_login}" required> 
          <input type="submit" id="manage_user"  name="manage_user" value="{$labels.btn_manage_user}" 
                 style="margin-left: 3px;">
        </form>
    </div>
  </div>
  
  {if $gui->update_title_bar == 1}
  <script type="text/javascript">
    parent.titlebar.location.reload();
  </script>
  {/if}

  {if $gui->reload == 1}
  <script type="text/javascript">
    top.location.reload();
  </script>
  {/if}
{else}
  {$labels.no_permissions_for_action}<br />
  <a href="{$gui->basehref}" alt="Home">Home</a>
{/if}
</body>
</html>