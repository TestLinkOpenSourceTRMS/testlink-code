{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: tc_exec_unassign_all.tpl,v 1.3 2010/07/26 19:01:13 asimon83 Exp $
Purpose: show a confirmation before unassigning all testers from the test cases in a build.

rev :

*}

{lang_get var="labels" s='btn_remove_all_tester_assignments'}

{include file="inc_head.tpl" openHead='yes'}

{assign var="ext_location" value=$smarty.const.TL_EXTJS_RELATIVE_PATH}
<link rel="stylesheet" type="text/css" href="{$basehref}{$ext_location}/css/ext-all.css" />
{include file="inc_del_onclick.tpl" openHead="yes"}

<script type="text/javascript">
{literal}

/**
 * submit the form to confirm deletion of all tester assignments
 *
 *
 */
function remove_testers(btn) {
	if (btn == "yes") {
		document.getElementById("delete_tc_exec_assignments").submit();
	}
}

/**
 * open popup message to ask for user's confirmation before deleting assignments
 *
 * 
 */
function warn_remove_testers(msgbox_title, msgbox_content) {
	Ext.Msg.confirm(msgbox_title, msgbox_content, function(btn) {
		remove_testers(btn);
	});
}					

{/literal}
</script>

</head>

<body>

<h1 class="title">{$gui->title|escape}</h1>

<div class="workBack">

{$gui->message|escape}

{if $gui->draw_tc_unassign_button}
	<div class="groupBtn">
		<form id='delete_tc_exec_assignments' name='delete_tc_exec_assignments' method='post'>
			<input type="hidden" name="build_id" value="{$gui->build_id}" />
			<input type="hidden" name="confirmed" value="yes" />
			<input type="button" 
			       name="remove_all_tester_assignments"
			       value="{lang_get s='btn_remove_all_tester_assignments'}"
			       onclick="javascript: warn_remove_testers('{$gui->popup_title}', 
			                                                '{$gui->popup_message}');" />
		</form>
	</div> <!-- groupBtn -->
{/if}

{if $gui->refreshTree}
	{include file="inc_refreshTreeWithFilters.tpl"}
{/if}

</div> <!-- workback -->
  
</body>
</html>