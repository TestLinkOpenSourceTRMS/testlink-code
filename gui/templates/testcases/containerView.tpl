{* 
TestLink Open Source Project - http://testlink.sourceforge.net/

@filesource	containerView.tpl

Purpose: smarty template - view test specification containers

@internal revisions
*}
{lang_get var='labels' 
          s='th_product_name,edit_testproject_basic_data,th_notes,test_suite,details,none,
             keywords,alt_del_testsuite, alt_edit_testsuite, alt_move_cp_testcases, alt_move_cp_testsuite, 
             btn_new_testsuite, btn_reorder,btn_execute_automatic_testcases,
             btn_edit_testsuite,btn_del_testsuite,btn_move_cp_testsuite,
	           btn_del_testsuites_bulk,btn_delete_testcases,btn_reorder_testcases_alpha,
	           btn_reorder_testcases_externalid,btn_reorder_testsuites_alpha,
	           btn_export_testsuite, btn_export_all_testsuites, btn_import_testsuite, 
	           btn_new_tc,btn_move_cp_testcases, btn_import_tc, btn_export_tc, th_testplan_name,
	           testsuite_operations, testcase_operations'}

{$container_id=$gui->container_data.id}
{$tproject_id=$gui->tproject_id}

{$tcImportAction="lib/testcases/tcImport.php?tproject_id=$tproject_id&containerID=$container_id"}
{$importToTProjectAction="$basehref$tcImportAction&amp;bIntoProject=1&amp;useRecursion=1&amp;"}
{$importToTSuiteAction="$basehref$tcImportAction&amp;useRecursion=1"}
{$importTestCasesAction="$basehref$tcImportAction"}
{$tcExportAction="lib/testcases/tcExport.php?tproject_id=$tproject_id&containerID=$container_id"}
{$exportTestCasesAction="$basehref$tcExportAction"}
{$tsuiteExportAction="$basehref$tcExportAction&amp;useRecursion=1"}

{include file="inc_head.tpl" openHead="yes"}
{$ext_location=$smarty.const.TL_EXTJS_RELATIVE_PATH}
<link rel="stylesheet" type="text/css" href="{$basehref}{$ext_location}/css/ext-all.css" />

</head>

<body>
<h1 class="title">{$gui->page_title}{$tlCfg->gui_title_separator_1}{$gui->container_data.name|escape}</h1>

<div class="workBack">
{include file="inc_update.tpl" result=$gui->sqlResult item=$gui->level
         name=$gui->moddedItem.name refresh=$gui->refreshTree}

{$bDownloadOnly=true}
{$drawReorderButton=true}
{$drawReorderButton=false}

{if $gui->level == 'testproject'}

	{if $gui->modify_tc_rights == 'yes'}
		{$bDownloadOnly=false}

	<fieldset class="groupBtn">
	<h2>{$labels.testsuite_operations}</h2>
	<form method="post" action="lib/testcases/containerEdit.php">
		<input type="hidden" name="doAction" id="doAction" value="" />
		<input type="hidden" name="containerID" id="containerID" value="{$gui->container_data.id}" />
		<input type="hidden" name="tproject_id" id="tproject_id" value="{$gui->tproject_id}" />
		
		<input type="submit" name="new_testsuite" value="{$labels.btn_new_testsuite}" />

		<input type="submit" name="reorder_testproject_testsuites_alpha" value="{$labels.btn_reorder_testsuites_alpha}"
				     title="{$labels.btn_reorder_testsuites_alpha}" />

		<input type="button" onclick="location='{$importToTProjectAction}'"
			                       value="{$labels.btn_import_testsuite}" />

    {* BUGID 3937 *}
    {if $gui->canDoExport}
		<input type="button" onclick="location='{$tsuiteExportAction}'" value="{$labels.btn_export_all_testsuites}" />
    {/if}


			{*
			 <input type="button" name="execButton" value="{$labels.btn_execute_automatic_testcases}"
			        onclick="javascript: startExecution({$container_data.id},'testproject');" />
			 *}
	</form>
	</fieldset>
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
		<input type="hidden" name="tproject_id" id="tproject_id" value="{$gui->tproject_id}" />

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
		<fieldset class="groupBtn">

		<h2>{$labels.testsuite_operations}</h2>
		{* Add a new testsuite children for this parent *}
		<span style="float: left; margin-right: 5px;">
		<form method="post" action="lib/testcases/containerEdit.php">
			<input type="hidden" name="containerID" value="{$gui->container_data.id}" />
			<input type="hidden" name="tproject_id" id="tproject_id" value="{$gui->tproject_id}" />
			<input type="submit" name="new_testsuite" value="{$labels.btn_new_testsuite}" />
		</form>
		</span>

		<form method="post" action="lib/testcases/containerEdit.php">
			<input type="hidden" name="testsuiteID" value="{$gui->container_data.id}" />
			<input type="hidden" name="testsuiteName" value="{$gui->container_data.name|escape}" />
			<input type="hidden" name="tproject_id" id="tproject_id" value="{$gui->tproject_id}" />
			<input type="submit" name="edit_testsuite" value="{$labels.btn_edit_testsuite}"
				     title="{$labels.alt_edit_testsuite}" />
			<input type="submit" name="move_testsuite_viewer" value="{$labels.btn_move_cp_testsuite}"
				     title="{$labels.alt_move_cp_testsuite}" />
			<input type="submit" name="delete_testsuite" value="{$labels.btn_del_testsuite}"
				     title="{$labels.alt_del_testsuite}" />
		  <input type="submit" name="reorder_testsuites_alpha" value="{$labels.btn_reorder_testsuites_alpha}"
				     title="{$labels.btn_reorder_testsuites_alpha}" />

			<input type="button" onclick="location='{$importToTSuiteAction}'" value="{$labels.btn_import_testsuite}" />
			<input type="button" onclick="location='{$tsuiteExportAction}'" value="{$labels.btn_export_testsuite}" />

		</form>
	    </fieldset>

		{* ----- Work with test cases ----------------------------------------------- *}
		<fieldset class="groupBtn">
		<h2>{$labels.testcase_operations}</h2>
		<form method="post" action="lib/testcases/tcEdit.php">
			<input type="hidden" name="tproject_id" id="tproject_id" value="{$gui->tproject_id}" />
		  	<input type="hidden" name="containerID" value="{$gui->container_data.id}" />
			<input type="submit" accesskey="t" id="create_tc" name="create_tc" value="{$labels.btn_new_tc}" />
		</form>

		<form method="post" action="lib/testcases/containerEdit.php">
			<input type="hidden" name="tproject_id" id="tproject_id" value="{$gui->tproject_id}" />
			<input type="hidden" name="testsuiteID" value="{$gui->container_data.id}" />
			<input type="hidden" name="testsuiteName" value="{$gui->container_data.name|escape}" />
	    <input type="submit" name="move_testcases_viewer" value="{$labels.btn_move_cp_testcases}"
         		 title="{$labels.alt_move_cp_testcases}" />
			<input type="submit" name="delete_testcases" value="{$labels.btn_delete_testcases}"
				     title="{$labels.btn_delete_testcases}" />
			<input type="submit" name="reorder_testcases" value="{$gui->btn_reorder_testcases}"
				     title="{$gui->btn_reorder_testcases}" />
		</form>

		<form method="post" action="lib/testcases/tcEdit.php">
			<input type="button" onclick="location='{$importTestCasesAction}'" value="{$labels.btn_import_tc}" />
			<input type="button" onclick="location='{$exportTestCasesAction}'" value="{$labels.btn_export_tc}" />
		</form>

		</fieldset>
	{/if}
	
	{* ----- show Test Suite data --------------------------------------------- *}
	{include file="testcases/inc_testsuite_viewer_ro.tpl"}

	{if $gui->modify_tc_rights eq 'yes'}
		{$bDownloadOnly=false}
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
