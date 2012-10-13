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
  {include file="testcases/containerViewTestProject.inc.tpl"}  
{elseif $gui->level == 'testsuite'}
  {include file="testcases/containerViewTestSuite.inc.tpl"}  
{/if}
</div>
{if $gui->refreshTree}
  {include file="inc_refreshTreeWithFilters.tpl"}
{/if}
</body>
</html>