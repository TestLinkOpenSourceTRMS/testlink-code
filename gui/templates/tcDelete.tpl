{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: tcDelete.tpl,v 1.5 2006/03/20 18:02:12 franciscom Exp $
Purpose: smarty template - delete test case in test specification

*}

{include file="inc_head.tpl"}

<body>
<div class="workBack">
{include file="inc_title.tpl" title=$title } 
{include file="inc_update.tpl" result=$sqlResult action=$action item="test case" refresh='yes'}

{if $sqlResult == ''}
	<p>{$delete_message}</p>
	<p>{lang_get s='question_del_tc'}</p>
	<form method="post" action="lib/testcases/tcEdit.php?testcaseID={$testcaseID}"> 
		<input type="submit" id="do_delete" name="do_delete" value="{lang_get s='btn_yes_iw2del'}" />
	</form>
{/if}

</div>
</body>
</html>