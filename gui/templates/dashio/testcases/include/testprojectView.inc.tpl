{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource testprojectView.inc.tpl
*}

{if $gui->modify_tc_rights == 'yes'}
    {$bDownloadOnly=false}
    <i class="fa fa-cog" aria-hidden="true"
      onclick="javascript:toogleShowHide('tproject_control_panel');"
      title="{$labels.actions}">
    </i>
    <div id="tproject_control_panel" style="display:{$tlCfg->gui->op_area_display->test_spec_container};">
      <fieldset class="groupBtn">
      <b>{$labels.testsuite_operations}</b>
      <form method="post" action="{$basehref}lib/testcases/containerEdit.php">

        {if property_exists($gui,'calledByMethod')} 
          <input type="hidden" name="calledByMethod" id="calledByMethod" 
                value="{$gui->calledByMethod}" />
        {/if}

        <input type="hidden" name="treeFormToken" id="treeFormToken" value="{$ft}" />

        <input type="hidden" name="doAction" id="doAction" value="" />
        <input type="hidden" name="containerID" value="{$gui->container_data.id}" />
        <input type="hidden" name="containerType" id="containerType" value="{$gui->level}" />

        <button style="border:0;" name="new_testsuite" id="new_testsuite" onclick="doAction.value='new_testsuite'">
          <i class="fas fa-plus-circle" title="{$labels.btn_new_testsuite}"></i>
        </button>

        <button style="border:0;" name="reorder_testproject_testsuites_alpha" id="reorder_testproject_testsuites_alpha" 
                onclick="doAction.value='reorder_testproject_testsuites_alpha'">
            <i class="fas fa-sort-alpha-down" 
              title="{$labels.btn_reorder_testsuites_alpha}"></i>
          </button>

        <i class="fas fa-file-import" style="padding:1px 6px;" id="importItem" onclick="location='{$importToTProjectAction}'"
            title="{$labels.btn_import_testsuite}"></i>

        {if $gui->canDoExport}
          <i class="fas fa-file-export" style="padding:1px 6px;" id="exportItem" onclick="location='{$tsuiteExportAction}'"
            title="{$labels.btn_export_all_testsuites}"></i>
        {/if}


        <i class="fas fa-book" style="padding:1px 6px;" onclick="window.open('{$testSpecFullDocAction}')" 
            title="{$labels.btn_gen_test_spec_new_window}"></i>   

        <i class="far fa-file-word" style="padding:1px 6px;" onclick="window.open('{$testSpecFullWordDocAction}')" 
            title="{$labels.btn_gen_test_spec_word}"></i>

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

 