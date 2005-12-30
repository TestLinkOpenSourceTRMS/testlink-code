{* 
Testlink: smarty template - 
$Id: adminUserEdit.tpl,v 1.1 2005/12/30 16:03:53 franciscom Exp $ 
*}
{* 
20050913 - fm - BUGID 0000103: Localization is changed but not strings

20050815 - changed action to updated 

*}
{include file="inc_head.tpl" jsValidate="yes"}

<body>

<h1> {lang_get s='title_account_settings'} </h1>

{include file="inc_update.tpl" result=$updateResult action="updated" item="user" name=$userData[1]}


<div class="workBack">

{literal}
<script type="text/javascript">
{/literal}
var warning_empty_pwd = "{lang_get s='warning_empty_pwd'}";
var warning_different_pwd = "{lang_get s='warning_different_pwd'}";
var warning_enter_less1 = "{lang_get s='warning_enter_less1'}";
var warning_enter_at_least1 = "{lang_get s='warning_enter_at_least1'}";
var warning_enter_at_least2 = "{lang_get s='warning_enter_at_least2'}";
var warning_enter_less2 = "{lang_get s='warning_enter_less2'}";
{literal}
function valAllText(form)
{
	if (valTextLength(form.first,30,1) && valTextLength(form.last,30,1))
	{
		return true;
	}
	return false;
}
</script>
{/literal}

<form method="post" action="lib/admin/adminUserEdit.php" onsubmit="return valAllText(this)">
	<input type="hidden" name="user_id" value="{$userData.id}" />
	<input type="hidden" name="user_login" value="{$userData.login}" />
	<table class="common">
		<tr>
			<th>{lang_get s='th_login'}</th>
			<td>{$userData.login}</td>
		</tr>
		<tr>
			<th>{lang_get s='th_first_name'}</th>
			<td><input type="text" name="first" value="{$userData.first|escape}" maxlength="30" /></td></tr>
		<tr>
			<th>{lang_get s='th_last_name'}</th>
			<td><input type="text" name="last" value="{$userData.last|escape}" maxlength="30" /></td>
		</tr>
		<tr>
			<th>{lang_get s='th_email'}</th>
			<td><input type="text" name="email" value="{$userData.email|escape}" size="50" maxlength="100" /></td>
		</tr>

		<tr>
			<th>{lang_get s='th_rights'}:</th>
			<td>
				<select name="rights_id"> 
				{html_options options=$optRights selected=$userData.rightsid}
				</select>
			</td>
		</tr>

		<tr>
			<th>{lang_get s='th_locale'}</th>
			<td>		   
				<select name="locale">
				{html_options options=$optLocale selected=$userData.locale}
				</select>	
			</td>
		</tr>

		<tr>
			<th>{lang_get s='th_active'}</th>
			<td> 
			  <input type="checkbox"  name="user_is_active" {if $userData.active eq 1} checked {/if} />
			</td>
		</tr>

	</table>


	<div class="groupBtn">	
		<input type="submit" name="do_update" value="{lang_get s='btn_upd_user_data'}" />
	</div>
</form>
<hr />


</div>

{*  BUGID 0000103: Localization is changed but not strings *}
{if $update_title_bar == 1}
{literal}
<script type="text/javascript">
	//parent.mainframe.location = parent.mainframe.location;
	parent.titlebar.location.reload();
</script>
{/literal}
{/if}
</body>
</html>