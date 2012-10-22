{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource	testSpecViewTestSuite.inc.tpl
@internal revisions
*}
{lang_get var='labels' 
          s='btn_new_testsuite,btn_edit_testsuite,btn_move_cp_testsuite,btn_del_testsuite,
             btn_reorder_testsuites_alpha,btn_import_testsuite,btn_export_testsuite,
             btn_new_tc,btn_move_cp_testcases,btn_delete_testcases,btn_import_tc,btn_export_tc'}

{include file="inc_head.tpl" openHead="yes"}
{$ext_location=$smarty.const.TL_EXTJS_RELATIVE_PATH}
<link rel="stylesheet" type="text/css" href="{$basehref}{$ext_location}/css/ext-all.css" />
</head>

<body>
<h1 class="title">{$gui->pageTitle|escape}</h1>
<div class="workBack">

{if $gui->grants->mgt_modify_tc == 'yes' || $gui->sqlResult == ''}
	<fieldset class="groupBtn">
	<h2>{$labels.testsuite_operations}</h2>
	<form method="post" action="lib/testsuites/testSuiteEdit.php">
	  <input type="hidden" name="containerType" id="containerType" value="testsuite" />
		<input type="hidden" name="testsuiteID" value="{$gui->tsuite.id}" />
		<input type="hidden" name="testsuiteName" value="{$gui->tsuite.name|escape}" />
		<input type="hidden" name="tproject_id" id="tproject_id" value="{$gui->tproject_id}" />

		<input type="submit" name="new_testsuite" value="{$labels.btn_new_testsuite}" />
		<input type="submit" name="edit_testsuite" value="{$labels.btn_edit_testsuite}"
			     title="{$labels.btn_edit_testsuite}" />
		<input type="submit" name="move_testsuite_viewer" value="{$labels.btn_move_cp_testsuite}"
			     title="{$labels.btn_move_cp_testsuite}" />
		<input type="submit" name="delete_testsuite" value="{$labels.btn_del_testsuite}"
			     title="{$labels.btn_del_testsuite}" />
	  <input type="submit" name="reorder_testsuites_alpha" value="{$labels.btn_reorder_testsuites_alpha}"
			     title="{$labels.btn_reorder_testsuites_alpha}" />

		<input type="button" onclick="location='{$gui->actions->importTestSuite}'" value="{$labels.btn_import_testsuite}" />
		<input type="button" onclick="location='{$gui->actions->exportTestSuite}'" value="{$labels.btn_export_testsuite}" />

	</form>
  </fieldset>

	{* ----- Work with test cases ----------------------------------------------- *}
	<fieldset class="groupBtn">
	<h2>{$labels.testcase_operations}</h2>
	<form method="post" action="lib/testcases/tcEdit.php">
		<input type="hidden" name="tproject_id" id="tproject_id" value="{$gui->tproject_id}" />
	  <input type="hidden" name="containerType" id="containerType" value="testsuite" />
	  <input type="hidden" name="parentID" id="parentID" value="{$gui->tsuite.id}" />
		<input type="submit" accesskey="t" id="create_tc" name="create_tc" value="{$labels.btn_new_tc}" />
	</form>

	<form method="post" action="lib/testsuites/testSuiteEdit.php">
		<input type="hidden" name="tproject_id" id="tproject_id" value="{$gui->tproject_id}" />

	  <input type="hidden" name="containerType" id="containerType" value="testsuite" />
		<input type="hidden" name="testsuiteID" value="{$gui->tsuite.id}" />
		<input type="hidden" name="testsuiteName" value="{$gui->tsuite.name|escape}" />

    <input type="submit" name="move_testcases_viewer" value="{$labels.btn_move_cp_testcases}"
       		 title="{$labels.alt_move_cp_testcases}" />
		<input type="submit" name="delete_testcases" value="{$labels.btn_delete_testcases}"
			     title="{$labels.btn_delete_testcases}" />
		<input type="submit" name="reorder_testcases" value="{$gui->btn_reorder_testcases}"
			     title="{$gui->btn_reorder_testcases}" />
	</form>

	<form method="post" action="lib/testcases/tcEdit.php">
		<input type="button" onclick="location='{$gui->actions->importTestCases}'" value="{$labels.btn_import_tc}" />
		<input type="button" onclick="location='{$gui->actions->exportTestCases}'" value="{$labels.btn_export_tc}" />
	</form>
	</fieldset>
{/if}

{include file="testsuites/testSuiteViewerRO.inc.tpl"}
{include file="inc_attachments.tpl" attach=$gui->attach}
{if $gui->refreshTree}
  {include file="inc_refreshTreeWithFilters.tpl"}
{/if}
</body>
</html>