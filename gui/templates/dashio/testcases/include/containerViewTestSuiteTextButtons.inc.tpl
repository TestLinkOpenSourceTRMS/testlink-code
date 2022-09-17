  {$calledByOK = property_exists($gui,'calledByMethod')} 
  {$tprojIDOK = property_exists($gui,'tproject_id')} 
  {$tlIMGTags.toggle_direct_link} &nbsp;
  <i class="fa fa-cog" aria-hidden="true"
     onclick="javascript:toogleShowHide('tsuite_control_panel');"
     title="{$labels.actions}">
  </i>
 
  <div class="direct_link" style='display:none'>
    <a href="{$gui->direct_link}" target="_blank">{$gui->direct_link}</a>
  </div>

  <div id="tsuite_control_panel" 
      style="display:{$tlCfg->gui->op_area_display->test_spec_container};">
      <fieldset class="groupBtn">
        <b>{$labels.testsuite_operations}</b>
        <form method="post" action="{$basehref}lib/testcases/containerEdit.php">
          <input type="hidden" name="form_token" id="form_token" value="{$gui->form_token}" />
          <input type="hidden" name="doAction" id="doAction" value="" />
          <input type="hidden" name="containerID" value="{$gui->container_data.id}" />
          <input type="hidden" name="testsuiteID" value="{$gui->container_data.id}" />
          <input type="hidden" name="testsuiteName" value="{$gui->container_data.name|escape}" />
          <input type="hidden" name="containerType" value="{$gui->containerType}" />

          <input type="hidden" name="containerType" value="{$gui->containerType}" />

          {if $tprojIDOK}
            <input type="hidden" name="tproject_id" id="tproject_id" value="{$gui->tproject_id}" />
          {/if}

          {if $calledByOK}
            <input type="hidden" name="calledByMethod" id="calledByMethod" value="{$gui->calledByMethod}" />
          {/if}

          <button style="border:0;" name="new_testsuite" id="new_testsuite" onclick="doAction.value='new_testsuite'">
            <i class="fas fa-plus-circle" title="{$labels.btn_new_testsuite}"></i>
          </button>
               
          <button style="border:0;" name="edit_testsuite" id="edit_testsuite"
            onclick="doAction.value='edit_testsuite'">
            <i class="fas fa-pencil-alt" 
               title="{$labels.btn_edit_testsuite}"></i>
          </button>
        
          <button style="border:0;" name="move_testsuite_viewer" 
            id="move_testsuite_viewer"
            onclick="doAction.value='move_testsuite_viewer'">
            <i class="fas fa-copy" title="{$labels.alt_move_cp_testsuite}"></i>
          </button>

          <button style="border:0;" name="delete_testsuite" 
            id="delete_testsuite"
            onclick="doAction.value='delete_testsuite'">
            <i class="fas fa-times-circle" 
               title="{$labels.alt_del_testsuite}"></i>
          </button>

          <button style="border:0;" name="reorder_testsuites_alpha" 
            id="reorder_testsuites_alpha"
            onclick="doAction.value='reorder_testsuites_alpha'">
            <i class="fas fa-sort-alpha-down" 
              title="{$labels.btn_reorder_testsuites_alpha}"></i>
          </button>

          <button style="border:0;" name="testcases_table_view" 
            id="testcases_table_view"
            onclick="doAction.value='testcases_table_view'">
            <i class="fas fa-table" 
              title="{$labels.btn_testcases_table_view}"></i>
          </button>
                 

          <i class="fas fa-book" style="padding:1px 6px;"
            onclick="window.open('{$testSuiteDocAction}')" 
            title="{$labels.btn_gen_test_suite_spec_new_window}"></i>   

          <i class="far fa-file-word" style="padding:1px 6px;"
            onclick="window.open('{$testSuiteWordDocAction}')" 
            title="{$labels.btn_gen_test_suite_spec_word}"></i>

          <i class="fas fa-file-import" style="padding:1px 6px;"
            id="importItem"
            onclick="location='{$importToTSuiteAction}'"
            title="{$labels.btn_import_testsuite}"></i>

          <i class="fas fa-file-export" style="padding:1px 6px;"
            id="exportItem"
            onclick="location='{$tsuiteExportAction}'"
            title="{$labels.btn_export_testsuite}"></i>
        </form>
      </fieldset>

      {* ----- Work with test cases -------------------------- *}
      <fieldset class="groupBtn">
        <b>{$labels.testcase_operations}</b>
        <form method="post" action="{$basehref}lib/testcases/tcEdit.php">
          <input type="hidden" name="tproject_id" value="{$gui->tproject_id}" />
          <input type="hidden" name="form_token" id="form_token" value="{$gui->form_token}" />
          <input type="hidden" name="doAction" id="doAction" value="" />
          <input type="hidden" name="containerID" value="{$gui->container_data.id}" />

          <button style="border:0;" name="create_tc" id="create_tc" onclick="doAction.value='create'">
            <i class="fas fa-plus-circle" title="{$labels.btn_new_tc}"></i>
          </button>
        </form>

        <form method="post" action="{$basehref}lib/testcases/containerEdit.php">
          <input type="hidden" name="form_token" id="form_token" value="{$gui->form_token}" />
          <input type="hidden" name="doAction" id="doAction" value="" />
          <input type="hidden" name="testsuiteID" value="{$gui->container_data.id}" />
          <input type="hidden" name="testsuiteName" value="{$gui->container_data.name|escape}" />

          {if $tprojIDOK}
            <input type="hidden" name="tproject_id" id="tproject_id" 
                  value="{$gui->tproject_id}" />
          {/if}

          {if $calledByOK}
            <input type="hidden" name="calledByMethod" id="calledByMethod" 
                  value="{$gui->calledByMethod}" />
          {/if}

          <button style="border:0;" name="move_testcases_viewer" 
            id="move_testcases_viewer"
            onclick="doAction.value='move_testcases_viewer'">
            <i class="fas fa-copy" title="{$labels.alt_move_cp_testcases}"></i>
          </button>

          <button style="border:0;" name="delete_testcases" 
            id="delete_testcases"
            onclick="doAction.value='delete_testcases'">
            <i class="fas fa-times-circle" 
               title="{$labels.btn_delete_testcases}"></i>
          </button>

          <button style="border:0;" name="reorder_testcases" 
            id="reorder_testcases"
            onclick="doAction.value='reorder_testcases'">
            <i class="fas fa-random" 
               title="{$gui->btn_reorder_testcases}"></i>
          </button>
        </form>

        <form method="post" action="{$basehref}lib/testcases/tcEdit.php">
          <input type="hidden" name="tproject_id" value="{$gui->tproject_id}" />
          <input type="hidden" name="form_token" id="form_token" value="{$gui->form_token}" />
          <input type="hidden" name="doAction" id="doAction" value="" />

          <i class="fas fa-file-import" style="padding:1px 6px;"
             id="importItem" onclick="location='{$importTestCasesAction}'" title="{$labels.btn_import_tc}"></i>

          <i class="fas fa-file-export" style="padding:1px 6px;" id="exportItem"
             onclick="location='{$exportTestCasesAction}'" title="{$labels.btn_export_tc}"></i>
          
          <i class="fas fa-magic" onclick="location='{$createTCFromIssueMantisXMLAction}'" 
             title="{$labels.btn_create_from_issue_xml}"></i>
        </form>
      </fieldset>
  </div>  