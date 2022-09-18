{* 
TestLink Open Source Project - http://testlink.sourceforge.net/

View test specification containers

@filesource containerView.tpl
*}
{$cfg=$smarty.template|basename|replace:".tpl":""}
{config_load file="input_dimensions.conf" section=$cfg}

{lang_get var='labels' 
  s='th_product_name,edit_testproject_basic_data,
     th_notes,test_suite,
     details,none,keywords,alt_del_testsuite,
     alt_edit_testsuite, alt_move_cp_testcases, 
     alt_move_cp_testsuite, 
     btn_new_testsuite, btn_reorder,btn_execute_automatic_testcases,
     btn_edit_testsuite,btn_del_testsuite,btn_move_cp_testsuite,
     btn_testcases_table_view,
     btn_del_testsuites_bulk,btn_delete_testcases,
     btn_reorder_testcases_alpha,
     btn_reorder_testcases_externalid,btn_reorder_testsuites_alpha,
     actions,btn_gen_test_spec,btn_gen_test_spec_new_window,
     btn_gen_test_spec_word,btn_gen_test_suite_spec_word,
     btn_gen_test_suite_spec,btn_gen_test_suite_spec_new_window,
     btn_export_testsuite, btn_export_all_testsuites, 
     btn_import_testsuite,btn_new_tc,btn_move_cp_testcases, btn_import_tc, btn_export_tc, th_testplan_name,
     testsuite_operations,testcase_operations,
     btn_create_from_issue_xml,file_upload_ko'}

{$ft=''}
{if isset($gui->form_token)}
  {$ft=$gui->form_token}
{/if}

{$container_id = $gui->container_data.id}
{$tproject_id = $gui->tproject_id}
{$tplan_id = $gui->tplan_id}


{$tcImportAction="lib/testcases/tcImport.php?containerID=$container_id"}
{$importToTProjectAction="$basehref$tcImportAction&bIntoProject=1&useRecursion=1&tproject_id=$tproject_id"}
{$importToTSuiteAction="$basehref$tcImportAction&useRecursion=1"}
{$importTestCasesAction="$basehref$tcImportAction"}
{$tcExportAction="lib/testcases/tcExport.php?tproject_id=$tproject_id&containerID=$container_id&amp;form_token=$ft"}
{$exportTestCasesAction="$basehref$tcExportAction"}

{$testSpecFullDocAction="lib/results/printDocument.php?type=testspec&level=testproject&allOptionsOn=1&format=0&id=$container_id&form_token=$ft"}
{$testSpecFullDocAction="$basehref$testSpecFullDocAction&tproject_id=$tproject_id&tplan_id=$tplan_id"}

{$testSpecFullWordDocAction="lib/results/printDocument.php?type=testspec&level=testproject&allOptionsOn=1&format=4&id=$container_id"}
{$testSpecFullWordDocAction="$basehref$testSpecFullWordDocAction&tproject_id=$tproject_id&tplan_id=$tplan_id"}

{$testSuiteDocAction="lib/results/printDocument.php?type=testspec&level=testsuite&allOptionsOn=1&format=0&id=$container_id&form_token=$ft"}
{$testSuiteDocAction="$basehref$testSuiteDocAction&tproject_id=$tproject_id&tplan_id=$tplan_id"}

{$testSuiteWordDocAction="lib/results/printDocument.php?type=testspec&level=testsuite&allOptionsOn=1&format=4&id=$container_id"}
{$testSuiteWordDocAction="$basehref$testSuiteWordDocAction&tproject_id=$tproject_id&tplan_id=$tplan_id"}


{$tsuiteExportAction="$basehref$tcExportAction&useRecursion=1&form_token=$ft"}

{$tcMantisXMLAction="lib/testcases/tcCreateFromIssueMantisXML.php?containerID=$container_id"}
{$createTCFromIssueMantisXMLAction="$basehref$tcMantisXMLAction&tproject_id=$tproject_id&tplan_id=$tplan_id"}


{include file="inc_head.tpl" openHead="yes"}
{$ext_location=$smarty.const.TL_EXTJS_RELATIVE_PATH}
<link rel="stylesheet" type="text/css" href="{$basehref}{$ext_location}/css/ext-all.css" />

{include file="inc_del_onclick.tpl" openHead="yes"}

<script type="text/javascript">
/**
 * Be Carefull this TRUST on existence of $gui->delAttachmentURL
 */
function jsCallDeleteFile(btn, text, o_id)
{ 
  var my_action='';
  if( btn == 'yes' )
  {
    my_action='{$gui->delAttachmentURL}'+o_id;
    window.location=my_action;
  }
}        
</script> 

{include file="bootstrap.inc.tpl"}
<script src="{$basehref}third_party/bootbox/bootbox.all.min.js"></script>
</head>

<body>
<h1 class="{#TITLE_CLASS#}">{$gui->page_title}{$tlCfg->gui_title_separator_1}{$gui->container_data.name|escape}</h1>
<div class="workBack">
{include file="inc_update.tpl" result=$gui->sqlResult item=$gui->level
         name=$gui->moddedItem.name refresh=$gui->refreshTree user_feedback=$gui->user_feedback}

{if $gui->uploadOp != null }
  <script>
  var uplMsg = "{$labels.file_upload_ko}<br>";
  var doAlert = false;
  {if $gui->uploadOp->statusOK == false}
    uplMsg += "{$gui->uploadOp->msg}<br>";
    doAlert = true;
  {/if}
  if (doAlert) {
    bootbox.alert(uplMsg);
  }
  </script>
{/if}

{$bDownloadOnly=true}
{$drawReorderButton=true}
{$drawReorderButton=false}

{if $gui->level == 'testproject'}
  {include file="testprojectView.inc.tpl"}
{elseif $gui->level == 'testsuite'}

  {if $gui->modify_tc_rights == 'yes' || $gui->sqlResult neq ''}
     {include file="containerViewTestSuiteTextButtons.inc.tpl" labels=$labels} 
  {/if}
  
  {* ----- show Test Suite data ----------------------------- *}
  {include file="tsuiteViewerRO.inc.tpl"}

  {if $gui->modify_tc_rights eq 'yes'}
    {$bDownloadOnly=false}
  {/if}
{/if} {* test suite *}

{include file="attachments.inc.tpl" 
         attach_attachmentInfos=$gui->attachmentInfos
         attach_id=$gui->id attach_tableName="nodes_hierarchy" 
         attach_downloadOnly=$bDownloadOnly}

</div>
{if $gui->refreshTree}
  {include file="inc_refreshTreeWithFilters.tpl"}
{/if}
</body>
</html>