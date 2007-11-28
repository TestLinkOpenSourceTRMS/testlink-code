{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: project_req_spec_mgmt.tpl,v 1.3 2007/11/28 08:15:24 franciscom Exp $
*}

{assign var="req_module" value=$smarty.const.REQ_MODULE}
{assign var="url_args" value="reqSpecEdit.php?do_action=create&tproject_id="}
{assign var="req_spec_new_url" value="$basehref$req_module$url_args$$tproject_id"}

{assign var="url_args" value="reqSpecEdit.php?do_action=reorder&tproject_id="}
{assign var="req_spec_reorder_url" value="$basehref$req_module$url_args$$tproject_id"}

{include file="inc_head.tpl"}

<body>
<div class="workBack">
<h1>{lang_get s='testproject'}{$smarty.const.TITLE_SEP}{$name|escape}</h1>

{*{if $modify_tc_rights == 'yes'} *}

	<div>
		<form method="post">
			<input type="button" id="new_req_spec" name="new_req_spec" 
			       value="{lang_get s='btn_new_req_spec'}" 
			       onclick="location='{$req_spec_new_url}'" />  

		  <input type="button" id="reorder_req_spec" name="reorder_req_spec" 
		         value="{lang_get s='btn_reorder_req_spec'}" 
		         onclick="location='{$req_spec_reorder_url}'" />  
		</form>
	</div>
{* {/if} *}
</div>
{if $refresh_tree}
   {include file="inc_refreshTree.tpl"}
{/if}
</body>
</html>
