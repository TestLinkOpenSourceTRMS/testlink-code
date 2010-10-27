{*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: project_req_spec_mgmt.tpl,v 1.17 2010/10/27 09:40:20 mx-julian Exp $

rev: 
  20101027 - Julian - reverted accidentally commited experimental code
  20080415 - franciscom - refactoring
*}
{* ------------------------------------------------------------------------- *}

{lang_get var="labels" s="btn_reorder_req_spec,btn_new_req_spec,btn_import,btn_export_all_reqspec"}
{assign var="req_module" value='lib/requirements/'}
{assign var="url_args" value="reqSpecEdit.php?doAction=create&amp;tproject_id="}
{assign var="req_spec_new_url" value="$basehref$req_module$url_args"}

{assign var="url_args" value="reqSpecEdit.php?doAction=reorder&amp;tproject_id="}
{assign var="req_spec_reorder_url" value="$basehref$req_module$url_args"}

{assign var="url_args" value="reqExport.php?scope=tree&tproject_id="}
{assign var="req_export_url"  value="$basehref$req_module$url_args"}

{assign var="url_args" value="reqImport.php?scope=tree&tproject_id="}
{assign var="req_import_url"  value="$basehref$req_module$url_args"}


{include file="inc_head.tpl"}

{* ------------------------------------------------------------------------- *}
<body>
<h1 class="title">{$gui->main_descr|escape}</h1>
<div class="workBack">
	<div class="groupBtn">
		<form method="post">
			<input type="button" id="new_req_spec" name="new_req_spec"
			       value="{$labels.btn_new_req_spec}"
			       onclick="location='{$req_spec_new_url}{$gui->tproject_id}'" />

			<input type="button" id="export_all" name="export_all"
			       value="{$labels.btn_export_all_reqspec}"
			       onclick="location='{$req_export_url}{$gui->tproject_id}'" />

			<input type="button" id="import_all" name="import_all"
			       value="{$labels.btn_import}"
			       onclick="location='{$req_import_url}{$gui->tproject_id}'" />
		</form>
	</div>
</div>

{if $gui->refresh_tree == "yes"}
   {include file="inc_refreshTree.tpl"}
{/if}

</body>
</html>
