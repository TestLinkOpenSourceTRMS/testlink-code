{*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: project_req_spec_mgmt.tpl,v 1.8 2008/04/19 16:12:33 franciscom Exp $

rev: 20080415 - franciscom - refactoring
*}

{lang_get var="labels" s="btn_reorder_req_spec,btn_new_req_spec"}
{assign var="req_module" value='lib/requirements/'}
{assign var="url_args" value="reqSpecEdit.php?doAction=create&amp;tproject_id="}
{assign var="req_spec_new_url" value="$basehref$req_module$url_args"}

{assign var="url_args" value="reqSpecEdit.php?doAction=reorder&amp;tproject_id="}
{assign var="req_spec_reorder_url" value="$basehref$req_module$url_args}

{include file="inc_head.tpl"}

<body>
<div class="workBack">
<h1>{$gui->main_descr|escape}</h1>
	<div>
		<form method="post">
			<input type="button" id="new_req_spec" name="new_req_spec"
			       value="{$labels.btn_new_req_spec}"
			       onclick="location='{$req_spec_new_url}{$gui->tproject_id}'" />

		  <input type="button" id="reorder_req_spec" name="reorder_req_spec"
		         value="{$labels.btn_reorder_req_spec}"
		         onclick="location='{$req_spec_reorder_url}{$gui->tproject_id}'" />
		</form>
	</div>
{* {/if} *}
</div>
{if $gui->refresh_tree == "yes"}
   {include file="inc_refreshTree.tpl"}
{/if}
</body>
</html>
