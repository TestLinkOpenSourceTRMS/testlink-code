    {$tlImages.toggle_direct_link} &nbsp;

    {$calledByOK = property_exists($gui,'calledByMethod')} 
    {$tprojIDOK = property_exists($gui,'tproject_id')} 
    
    <img class="clickable" src="{$tlImages.cog}" 
         onclick="javascript:toogleShowHide('tsuite_control_panel');"  title="{$labels.actions}" />
    
   <div class="direct_link" style='display:none'><a href="{$gui->direct_link}" target="_blank">{$gui->direct_link}</a></div>

    <div id="tsuite_control_panel" style="display:{$tlCfg->gui->op_area_display->test_spec_container};">
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
            <input type="hidden" name="tproject_id" id="tproject_id" 
                  value="{$gui->tproject_id}" />
          {/if}

          {if $calledByOK}
            <input type="hidden" name="calledByMethod" id="calledByMethod" 
                  value="{$gui->calledByMethod}" />
          {/if}


          <input type="image" src="{$tlImages.add}" name="new_testsuite" id="new_testsuite" 
                 onclick="doAction.value='new_testsuite'" title="{$labels.btn_new_testsuite}">

          <input type="image" src="{$tlImages.edit}" name="edit_testsuite" id="edit_testsuite" 
                 onclick="doAction.value='edit_testsuite'" title="{$labels.btn_edit_testsuite}">

          <input type="image" src="{$tlImages.move_copy}" name="move_testsuite_viewer" id="move_testsuite_viewer" 
                 onclick="doAction.value='move_testsuite_viewer'" title="{$labels.alt_move_cp_testsuite}">
          
          <input type="image" src="{$tlImages.delete}" name="delete_testsuite" id="delete_testsuite" 
                 onclick="doAction.value='delete_testsuite'" title="{$labels.alt_del_testsuite}">

          <input type="image" src="{$tlImages.order_alpha}" name="reorder_testsuites_alpha" id="reorder_testsuites_alpha" 
                 onclick="doAction.value='reorder_testsuites_alpha'" title="{$labels.btn_reorder_testsuites_alpha}">
          
          <input type="image" src="{$tlImages.testcases_table_view}" name="testcases_table_view" id="testcases_table_view" 
                 onclick="doAction.value='testcases_table_view'" title="{$labels.btn_testcases_table_view}">

          <img src="{$tlImages.report}" onclick="window.open('{$testSuiteDocAction}')" 
               title="{$labels.btn_gen_test_suite_spec_new_window}" />

          <img src="{$tlImages.report_word}" onclick="window.open('{$testSuiteWordDocAction}')" 
               title="{$labels.btn_gen_test_suite_spec_word}" />

          <img src="{$tlImages.import}" onclick="location='{$importToTSuiteAction}'" title="{$labels.btn_import_testsuite}" />
          <img src="{$tlImages.export}" onclick="location='{$tsuiteExportAction}'" title="{$labels.btn_export_testsuite}" />
        </form>
      </fieldset>

      {* ----- Work with test cases ----------------------------------------------- *}
      <fieldset class="groupBtn">
        <b>{$labels.testcase_operations}</b>
        <form method="post" action="{$basehref}lib/testcases/tcEdit.php">
          <input type="hidden" name="form_token" id="form_token" value="{$gui->form_token}" />
          <input type="hidden" name="doAction" id="doAction" value="" />
          <input type="hidden" name="containerID" value="{$gui->container_data.id}" />
          <input type="image" src="{$tlImages.add}" name="create_tc" id="create_tc" 
                 onclick="doAction.value='create'" title="{$labels.btn_new_tc}">
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



          <input type="image" src="{$tlImages.move_copy}" name="move_testcases_viewer" id="move_testcases_viewer" 
                 onclick="doAction.value='move_testcases_viewer'" title="{$labels.alt_move_cp_testcases}">

          <input type="image" src="{$tlImages.delete}" name="delete_testcases" id="delete_testcases" 
                 onclick="doAction.value='delete_testcases'" title="{$labels.btn_delete_testcases}">

          <input type="image" src="{$tlImages.reorder}" name="reorder_testcases" id="reorder_testcases" 
                 onclick="doAction.value='reorder_testcases'" title="{$gui->btn_reorder_testcases}">
        </form>

        <form method="post" action="{$basehref}lib/testcases/tcEdit.php">
          <input type="hidden" name="form_token" id="form_token" value="{$gui->form_token}" />
          <input type="hidden" name="doAction" id="doAction" value="" />
          <img src="{$tlImages.import}" onclick="location='{$importTestCasesAction}'" title="{$labels.btn_import_tc}" />
          <img src="{$tlImages.export}" onclick="location='{$exportTestCasesAction}'" title="{$labels.btn_export_tc}" />
          <img src="{$tlImages.create_from_xml}" onclick="location='{$createTCFromIssueMantisXMLAction}'" 
               title="{$labels.btn_create_from_issue_xml}" />
        </form>
      </fieldset>

    </div>  
