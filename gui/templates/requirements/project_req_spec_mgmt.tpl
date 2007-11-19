{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: project_req_spec_mgmt.tpl,v 1.1 2007/11/19 21:01:05 franciscom Exp $
*}
{include file="inc_head.tpl"}

<body>
<div class="workBack">
<h1>{lang_get s='testproject'}{$smarty.const.TITLE_SEP}{$name|escape}</h1>

{*{if $modify_tc_rights == 'yes'} *}

	<div>
		<form method="post">
			<input type="button" id="new_req_spec" name="new_req_spec" 
			       value="{lang_get s='btn_new_req_spec'}" 
			       onclick="location='{$basehref}{$smarty.const.REQ_MODULE}reqSpecEdit.php?tproject_id={$tproject_id}&do_action=create'" />  
		  <input type="submit" name="reorder_req_spec" value="{lang_get s='btn_reorder_req_spec'}" />
		</form>
	</div>
{* {/if} *}
</div>
{if $refreshTree}
   {include file="inc_refreshTree.tpl"}
{/if}
</body>
</html>
