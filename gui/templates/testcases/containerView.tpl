{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: containerView.tpl,v 1.32 2010/07/22 14:14:43 asimon83 Exp $ *}
{*
Purpose: smarty template - view test specification containers

rev :
  20010708 - asimon - BUGID 3406: removed buttons and labels for 3049 
  20100501 - franciscom - BUGID 3410: Smarty 3.0 compatibility
  20100212 - asimon - BUGID 3049 - added removing of testplan assignment feature
  20100102 - franciscom - refactoring to use $gui
  20080805 - franciscom - fixed undefined variable log warning.
                          BUGID 1661 - removed reorder button if tree component support drag & drop
                          
  20080706 - franciscom - fixed refactorization bug that broke attachments feature
  20080606 - havlatm - refactorization; layout update
  20080403 - franciscom - BUGID  - problems with IE 7 and incomplete URL
  20080329 - franciscom - added contribution by Eugenia Drosdezki
                            choose testcases to move/copy inside a testsuite
  20071102 - franciscom - added contribution

  20070216 - franciscom  moved parameters from GET to hidden
*}
{lang_get var='labels' 
          s='th_product_name,edit_testproject_basic_data,th_notes,test_suite,details,none,
             keywords,alt_del_testsuite, alt_edit_testsuite, alt_move_cp_testcases, alt_move_cp_testsuite, 
             btn_new_testsuite, btn_reorder,btn_execute_automatic_testcases,
	           btn_edit_testsuite,btn_del_testsuite,btn_move_cp_testsuite,
	           btn_del_testsuites_bulk,
	           btn_export_testsuite, btn_export_all_testsuites, btn_import_testsuite, 
	           btn_new_tc,btn_move_cp_testcases, btn_import_tc, btn_export_tc, th_testplan_name'}

{assign var="container_id" value=$gui->container_data.id}
{assign var="tcImportAction"
        value="lib/testcases/tcImport.php?containerID=$container_id"}
{assign var="importToTProjectAction"  value="$basehref$tcImportAction&amp;bIntoProject=1&amp;useRecursion=1&amp;"}
{assign var="importToTSuiteAction"  value="$basehref$tcImportAction&amp;useRecursion=1"}
{assign var="importTestCasesAction"  value="$basehref$tcImportAction"}
{assign var="tcExportAction"
        value="lib/testcases/tcExport.php?containerID=$container_id"}
{assign var="exportTestCasesAction"  value="$basehref$tcExportAction"}
{assign var="tsuiteExportAction" value="$basehref$tcExportAction&amp;useRecursion=1"}

{include file="inc_head.tpl" openHead="yes"}
{assign var="ext_location" value=$smarty.const.TL_EXTJS_RELATIVE_PATH}
<link rel="stylesheet" type="text/css" href="{$basehref}{$ext_location}/css/ext-all.css" />

{include file="inc_del_onclick.tpl" openHead="yes"}

{* removed for BUGID 3406
<script type="text/javascript">
{literal}

function unassign_tcs(btn) {
	if (btn == "yes") {
		document.getElementById("tc_unassign_from_tp").submit();
	}
}

function warn_unassign_tcs(tp_id, tp_name, msgbox_title, msgbox_content) {
	Ext.Msg.confirm(msgbox_title, msgbox_content, function(btn) {
		unassign_tcs(btn);
	});
}					

{/literal}
</script>
*}

</head>

<body>
<h1 class="title">{$gui->page_title}{$tlCfg->gui_title_separator_1}{$gui->container_data.name|escape}</h1>

<div class="workBack">

{include file="inc_update.tpl" result=$gui->sqlResult item=$gui->level
         name=$gui->moddedItem.name refresh=$smarty.session.setting_refresh_tree_on_action}

{assign var="bDownloadOnly" value=true}
{assign var="drawReorderButton" value=true}
{assign var="drawReorderButton" value=false}

{if $gui->level == 'testproject'}

	{if $gui->modify_tc_rights == 'yes'}
		{assign var="bDownloadOnly" value=false}

	<div>
	<form method="post" action="lib/testcases/containerEdit.php">
		<input type="hidden" name="doAction" id="doAction" value="" />
		<input type="hidden" name="containerID" value="{$gui->container_data.id}" />
		
		<input type="submit" name="new_testsuite" value="{$labels.btn_new_testsuite}" />

		<input type="button" onclick="location='{$importToTProjectAction}'"
			                       value="{$labels.btn_import_testsuite}" />
		<input type="button" onclick="location='{$tsuiteExportAction}'" value="{$labels.btn_export_all_testsuites}" />

    {* TO BE DEVELOPED  
		<input type="submit" name="del_testsuites_bulk" id="del_testsuites_bulk"
		       onclick="doAction.value='deleteBulk'" value="{$labels.btn_del_testsuites_bulk}" />
    *}

			{*
			 <input type="button" name="execButton" value="{$labels.btn_execute_automatic_testcases}"
			        onclick="javascript: startExecution({$container_data.id},'testproject');" />
			 *}
	</form>
	</div>
	{/if}

	<table class="simple" >
		<tr>
			<th>{$labels.th_product_name}</th>
		</tr>
		<tr>
			<td>
	    {if $gui->mgt_modify_product == 'yes'}
			  <a href="lib/project/projectView.php"  target="mainframe"
			          title="{$labels.edit_testproject_basic_data}">{$gui->container_data.name|escape}</a>
			{else}
			   {$gui->container_data.name|escape}
			{/if}
			</td>
		</tr>
		<tr>
			<th>{$labels.th_notes}</th>
		</tr>
		<tr>
			<td>{$gui->container_data.notes}</td>
		</tr>

	</table>
	{*
	<div id="inProgress"></div>
	<div id="executionResults"></div>
  	*}

  {* internal bug - 20080706 - franciscom*}
	{include file="inc_attachments.tpl" 
	         attach_id=$gui->id attach_tableName="nodes_hierarchy"
	         attach_attachmentInfos=$gui->attachmentInfos
	         attach_downloadOnly=$bDownloadOnly}


{* removed for BUGID 3406
{* ----- TEST PLAN (for BUGID 3049) -----------------------------------------------
{elseif $gui->level == 'testplan'}

	{if $gui->draw_tc_unassign_button}
		<form id="tc_unassign_from_tp" name="tc_unassign_from_tp" action="lib/testcases/containerEdit.php?tplan_id={$gui->tplan_id}" method="post">
		<input type="hidden" name="doAction" value="doUnassignFromPlan" />
		<input type="hidden" name="doUnassignFromPlan" value="doUnassignFromPlan" />
		<input type="hidden" name="tplan_id" value="{$gui->tplan_id}" />
		<input type="button" name="unassign_all_tcs" value="{$labels.btn_unassign_all_tcs}"
			  	onclick="javascript: warn_unassign_tcs({$gui->tplan_id}, '{$gui->tplan_name}',
			  	'{$labels.unassign_all_tcs_msgbox_title}', '{$gui->unassign_all_tcs_warning_msg}');"/>
		</form>
	{/if}

	{if $gui->result}
	<div class="user_feedback">
		<p>{$gui->result}</p>
	</div>
	{/if}
	
	<table class="simple" >
		<tr>
			<th>{$labels.th_product_name}</th>
		</tr>
		<tr>
			<td>{$gui->tproject_name|escape}</td>
		</tr>
		<tr>
			<th>{$labels.th_notes}</th>
		</tr>
		<tr>
			<td>{$gui->tproject_description}</td>
		</tr>

	</table>

	<table class="simple" >
		<tr>
			<th>{$labels.th_testplan_name}</th>
		</tr>
		<tr>
			<td>{$gui->tplan_name|escape}</td>
		</tr>
		<tr>
			<th>{$labels.th_notes}</th>
		</tr>
		<tr>
			<td>{$gui->tplan_description}</td>
		</tr>

	</table>

*}


{* ----- TEST SUITE ----------------------------------------------------- *}
{elseif $gui->level == 'testsuite'}

	{if $gui->modify_tc_rights == 'yes' || $gui->sqlResult neq ''}
		<div class="groupBtn">

		{* Add a new testsuite children for this parent *}
		<span style="float: left; margin-right: 5px;">
		<form method="post" action="lib/testcases/containerEdit.php">
			<input type="hidden" name="containerID" value="{$gui->container_data.id}" />
			<input type="submit" name="new_testsuite" value="{$labels.btn_new_testsuite}" />
		</form>
		</span>

		<form method="post" action="lib/testcases/containerEdit.php">
			<input type="hidden" name="testsuiteID" value="{$gui->container_data.id}" />
			<input type="hidden" name="testsuiteName" value="{$gui->container_data.name|escape}" />
			<input type="submit" name="edit_testsuite" value="{$labels.btn_edit_testsuite}"
				     title="{$labels.alt_edit_testsuite}" />
			<input type="submit" name="delete_testsuite" value="{$labels.btn_del_testsuite}"
				     title="{$labels.alt_del_testsuite}" />
			<input type="submit" name="move_testsuite_viewer" value="{$labels.btn_move_cp_testsuite}"
				     title="{$labels.alt_move_cp_testsuite}" />

			<input type="button" onclick="location='{$importToTSuiteAction}'" value="{$labels.btn_import_testsuite}" />
			<input type="button" onclick="location='{$tsuiteExportAction}'" value="{$labels.btn_export_testsuite}" />
		</form>
	    </div>

		{* ----- Work with test cases ----------------------------------------------- *}
		<div class="groupBtn">
		<span style="float: left; margin-right: 5px;">
		<form method="post" action="lib/testcases/tcEdit.php">
		  <input type="hidden" name="containerID" value="{$gui->container_data.id}" />
			<input type="submit" accesskey="t" id="create_tc" name="create_tc" value="{$labels.btn_new_tc}" />
			<input type="button" onclick="location='{$importTestCasesAction}'" value="{$labels.btn_import_tc}" />
			<input type="button" onclick="location='{$exportTestCasesAction}'" value="{$labels.btn_export_tc}" />

{* 20071102 - franciscom @TODO unfinished feature
			<input type="button" name="execButton" value="{$labels.btn_execute_automatic_testcases}"
			       onclick="javascript: startExecution({$container_data.id},'testsuite');" />
*}
		</form>
		</span>
		<form method="post" action="lib/testcases/containerEdit.php">
			<input type="hidden" name="testsuiteID" value="{$gui->container_data.id}" />
			<input type="hidden" name="testsuiteName" value="{$gui->container_data.name|escape}" />
	    <input type="submit" name="move_testcases_viewer" value="{$labels.btn_move_cp_testcases}"
         		 title="{$labels.alt_move_cp_testcases}" />
		</form>

		</div>
{*
		<div id="inProgress"></div><br />
		<div id="executionResults"></div>
*}
	{/if}
	
	{* ----- show Test Suite data --------------------------------------------- *}
	{include file="testcases/inc_testsuite_viewer_ro.tpl"}

	{if $gui->modify_tc_rights eq 'yes'}
		{assign var="bDownloadOnly" value=false}
	{/if}
	{include file="inc_attachments.tpl" 
	         attach_attachmentInfos=$gui->attachmentInfos
	         attach_id=$gui->id attach_tableName="nodes_hierarchy" 
	         attach_downloadOnly=$bDownloadOnly}

{/if} {* test suite *}

</div>
{if $gui->refreshTree}
   	{include file="inc_refreshTreeWithFilters.tpl"}
	{*include file="inc_refreshTree.tpl"*}
{/if}
</body>
</html>