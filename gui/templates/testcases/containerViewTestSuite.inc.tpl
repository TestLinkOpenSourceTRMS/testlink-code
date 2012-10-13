{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource	containerViewTestSuite.inc.tpl
@internal revisions
*}

{if $gui->mgt_modify_tc == 'yes' || $gui->sqlResult neq ''}
	<fieldset class="groupBtn">
	<h2>{$labels.testsuite_operations}</h2>
	<form method="post" action="lib/testcases/containerEdit.php">
	  <input type="hidden" name="containerType" id="containerType" value="{$gui->level}" />
		<input type="hidden" name="containerID" value="{$gui->container_data.id}" />
		<input type="hidden" name="testsuiteID" value="{$gui->container_data.id}" />
		<input type="hidden" name="testsuiteName" value="{$gui->container_data.name|escape}" />
		<input type="hidden" name="tproject_id" id="tproject_id" value="{$gui->tproject_id}" />

		<input type="submit" name="new_testsuite" value="{$labels.btn_new_testsuite}" />
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
	  <input type="hidden" name="containerType" id="containerType" value="{$gui->level}" />
	  <input type="hidden" name="containerID" value="{$gui->container_data.id}" />
		<input type="submit" accesskey="t" id="create_tc" name="create_tc" value="{$labels.btn_new_tc}" />
	</form>

	<form method="post" action="lib/testcases/containerEdit.php">
		<input type="hidden" name="tproject_id" id="tproject_id" value="{$gui->tproject_id}" />

	  <input type="hidden" name="containerType" id="containerType" value="{$gui->level}" />
	  <input type="hidden" name="containerID" value="{$gui->container_data.id}" />
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
{include file="inc_attachments.tpl" attach=$gui->attach}