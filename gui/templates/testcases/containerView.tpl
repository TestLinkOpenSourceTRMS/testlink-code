{* 
TestLink Open Source Project - http://testlink.sourceforge.net/

View test specification containers

@filesource containerView.tpl
@internal revisions
@since 1.9.12
*}

{lang_get var='labels' 
          s='th_product_name,edit_testproject_basic_data,th_notes,test_suite,details,none,
             keywords,alt_del_testsuite, alt_edit_testsuite, alt_move_cp_testcases, alt_move_cp_testsuite, 
             btn_new_testsuite, btn_reorder,btn_execute_automatic_testcases,
             btn_edit_testsuite,btn_del_testsuite,btn_move_cp_testsuite,btn_testcases_table_view,
             btn_del_testsuites_bulk,btn_delete_testcases,btn_reorder_testcases_alpha,
             btn_reorder_testcases_externalid,btn_reorder_testsuites_alpha,actions,
             btn_gen_test_spec,btn_gen_test_spec_new_window,
             btn_gen_test_spec_word,btn_gen_test_suite_spec_word,
             btn_gen_test_suite_spec,btn_gen_test_suite_spec_new_window,
             btn_export_testsuite, btn_export_all_testsuites, btn_import_testsuite, 
             btn_new_tc,btn_move_cp_testcases, btn_import_tc, btn_export_tc, th_testplan_name,
             testsuite_operations, testcase_operations,btn_create_from_issue_xml'}

{$ft=''}
{if isset($gui->form_token)}
  {$ft=$gui->form_token}
{/if}

{$container_id = $gui->container_data.id}
{$tproject_id = $gui->tproject_id}

{$tcImportAction="lib/testcases/tcImport.php?containerID=$container_id"}
{$importToTProjectAction="$basehref$tcImportAction&amp;bIntoProject=1&amp;useRecursion=1&amp;"}
{$importToTSuiteAction="$basehref$tcImportAction&amp;useRecursion=1"}
{$importTestCasesAction="$basehref$tcImportAction"}
{$tcExportAction="lib/testcases/tcExport.php?tproject_id=$tproject_id&containerID=$container_id&amp;form_token=$ft"}
{$exportTestCasesAction="$basehref$tcExportAction"}

{$testSpecFullDocAction="lib/results/printDocument.php?type=testspec&level=testproject&allOptionsOn=1&format=0&id=$container_id&form_token=$ft"}
{$testSpecFullDocAction="$basehref$testSpecFullDocAction"}

{$testSpecFullWordDocAction="lib/results/printDocument.php?type=testspec&level=testproject&allOptionsOn=1&format=4&id=$container_id"}
{$testSpecFullWordDocAction="$basehref$testSpecFullWordDocAction"}

{$testSuiteDocAction="lib/results/printDocument.php?type=testspec&level=testsuite&allOptionsOn=1&format=0&id=$container_id&form_token=$ft"}
{$testSuiteDocAction="$basehref$testSuiteDocAction"}

{$testSuiteWordDocAction="lib/results/printDocument.php?type=testspec&level=testsuite&allOptionsOn=1&format=4&id=$container_id"}
{$testSuiteWordDocAction="$basehref$testSuiteWordDocAction"}


{$tsuiteExportAction="$basehref$tcExportAction&amp;useRecursion=1&amp;form_token=$ft"}

{$tcMantisXMLAction="lib/testcases/tcCreateFromIssueMantisXML.php?containerID=$container_id"}
{$createTCFromIssueMantisXMLAction="$basehref$tcMantisXMLAction"}


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
</head>

<body>
<h1 class="title">{$gui->page_title}{$tlCfg->gui_title_separator_1}{$gui->container_data.name|escape}</h1>
<div class="workBack">
{include file="inc_update.tpl" result=$gui->sqlResult item=$gui->level
         name=$gui->moddedItem.name refresh=$gui->refreshTree user_feedback=$gui->user_feedback}

{$bDownloadOnly=true}
{$drawReorderButton=true}
{$drawReorderButton=false}

{if $gui->level == 'testproject'}

  {if $gui->modify_tc_rights == 'yes'}
    {$bDownloadOnly=false}

  <img class="clickable" src="{$tlImages.cog}" onclick="javascript:toogleShowHide('tproject_control_panel');"
       title="{$labels.actions}" />
  <div id="tproject_control_panel" style="display:{$tlCfg->gui->op_area_display->test_spec_container};">
    <fieldset class="groupBtn">
    <b>{$labels.testsuite_operations}</b>
    <form method="post" action="{$basehref}lib/testcases/containerEdit.php">
      <input type="hidden" name="treeFormToken" id="treeFormToken" value="{$ft}" />
      <input type="hidden" name="doAction" id="doAction" value="" />
      <input type="hidden" name="containerID" value="{$gui->container_data.id}" />
      <input type="hidden" name="containerType" id="containerType" value="{$gui->level}" />

      <input type="image" src="{$tlImages.add}" name="new_testsuite" id="new_testsuite" 
             onclick="doAction.value='new_testsuite'" title="{$labels.btn_new_testsuite}">
      
      <input type="image" src="{$tlImages.order_alpha}" name="reorder_testproject_testsuites_alpha" 
             id="reorder_testproject_testsuites_alpha" 
             onclick="doAction.value='reorder_testproject_testsuites_alpha'" title="{$labels.btn_reorder_testsuites_alpha}">

      <img src="{$tlImages.import}" onclick="location='{$importToTProjectAction}'" title="{$labels.btn_import_testsuite}" />

      {if $gui->canDoExport}
        <img src="{$tlImages.export}" onclick="location='{$tsuiteExportAction}'" title="{$labels.btn_export_all_testsuites}" />
      {/if}

      <img src="{$tlImages.report}" onclick="window.open('{$testSpecFullDocAction}')" 
           title="{$labels.btn_gen_test_spec_new_window}" />

      <img src="{$tlImages.report_word}" onclick="window.open('{$testSpecFullWordDocAction}')" 
           title="{$labels.btn_gen_test_spec_word}" />

    </form>
    </fieldset>
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
      <td>{if $gui->testProjectEditorType == 'none'}{$gui->container_data.notes|nl2br}{else}{$gui->container_data.notes}{/if}</td>
    </tr>
  </table>

{elseif $gui->level == 'testsuite'}

  {if $gui->modify_tc_rights == 'yes' || $gui->sqlResult neq ''}
     {include file="testcases/containerViewTestSuiteTextButtons.inc.tpl"} 
  {/if}
  
  {* ----- show Test Suite data --------------------------------------------- *}
  {include file="testcases/inc_testsuite_viewer_ro.tpl"}

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