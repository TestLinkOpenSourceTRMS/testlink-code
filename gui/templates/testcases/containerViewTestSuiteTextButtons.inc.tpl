    <img class="clickable" src="{$tlImages.cog}" 
         onclick="javascript:toogleShowHide('tsuite_control_panel');"  title="{$labels.actions}" />
    
    <div id="tsuite_control_panel" style="display:{$tlCfg->gui->op_area_display->test_spec_container};">
      <fieldset class="groupBtn">
        <h2>{$labels.testsuite_operations}</h2>
        <span style="float: left; margin-right: 5px;">
        <form method="post" action="lib/testcases/containerEdit.php">
          <input type="hidden" name="containerID" value="{$gui->container_data.id}" />
          <input type="submit" name="new_testsuite" value="{$labels.btn_new_testsuite}" />
        </form>
        </span>
        <form method="post" action="lib/testcases/containerEdit.php">
          <input type="hidden" name="testsuiteID" value="{$gui->container_data.id}" />
          <input type="hidden" name="testsuiteName" value="{$gui->container_data.name|escape}" />
          <input type="hidden" name="containerType" value="{$gui->containerType}" />
          <input type="submit" name="edit_testsuite" value="{$labels.btn_edit_testsuite}"
                 title="{$labels.alt_edit_testsuite}" />
    
          <input type="submit" name="move_testsuite_viewer" value="{$labels.btn_move_cp_testsuite}"
                 title="{$labels.alt_move_cp_testsuite}" />
    
          <input type="submit" name="delete_testsuite" value="{$labels.btn_del_testsuite}"
                 title="{$labels.alt_del_testsuite}" />
    
          <input type="submit" name="reorder_testsuites_alpha" value="{$labels.btn_reorder_testsuites_alpha}"
               title="{$labels.btn_reorder_testsuites_alpha}" />
          
          <input type="submit" name="testcases_table_view" value="{$labels.btn_testcases_table_view}"
                 title="{$labels.btn_testcases_table_view}" />

          <input type="button" onclick="location='{$importToTSuiteAction}'" value="{$labels.btn_import_testsuite}" />
          <input type="button" onclick="location='{$tsuiteExportAction}'" value="{$labels.btn_export_testsuite}" />

        </form>
      </fieldset>

      {* ----- Work with test cases ----------------------------------------------- *}
      <fieldset class="groupBtn">
        <h2>{$labels.testcase_operations}</h2>
        <form method="post" action="lib/testcases/tcEdit.php">
          <input type="hidden" name="containerID" value="{$gui->container_data.id}" />
          <input type="submit" accesskey="t" id="create_tc" name="create_tc" value="{$labels.btn_new_tc}" />
        </form>

        <form method="post" action="lib/testcases/containerEdit.php">
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
          <input type="button" onclick="location='{$createTCFromIssueMantisXMLAction}'" value="{$labels.btn_create_from_issue_xml}" />
        </form>
      </fieldset>

    </div>  
