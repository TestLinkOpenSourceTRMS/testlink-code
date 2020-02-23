{*
TestLink Open Source Project - http://testlink.sourceforge.net/

@filesource execSetResultsBulk.inc.tpl
@used-by execSetResults.inc.tpl
*}
      <div>
      <br />
      <input type="button" id="do_export_testcases" name="do_export_testcases"  value="{$labels.btn_export_testcases}"
               onclick="javascript: openExportTestCases('export_testcases',{$gui->node_id},{$gui->tproject_id},
                                                        {$gui->tplan_id},{$gui->build_id},{$gui->platform_id},
                                                        '{$gui->tcversionSet}');" />


      {if $tlCfg->exec_cfg->enable_test_automation}
        <input type="submit" id="execute_cases" name="execute_cases" value="{$labels.execute_and_save_results}"/>
      {/if}

      <table class="mainTable-x" width="100%">
      <tr>
      <th>{$labels.th_testsuite}</th>
      <th>{$labels.title_test_case}</th>
      <th>{$labels.exec_status}</th>
      <th>{$labels.test_exec_result}</th>
      </tr>

      {foreach item=tc_exec from=$gui->map_last_exec name="tcSet"}
          {if $tc_exec.active == 1}
            {$tc_id=$tc_exec.testcase_id}
            {$tcversion_id=$tc_exec.id}
            {* IMPORTANT:
               Here we use version_number, which is related to tcversion_id SPECIFICATION.
               When we need to display executed version number, we use tcversion_number
            *}
            {$version_number=$tc_exec.version}
        
        <input type="hidden" id="tc_version_{$tcversion_id}" name="tc_version[{$tcversion_id}]" value='{$tc_id}' />
        <input type="hidden" id="version_number_{$tcversion_id}" name="version_number[{$tcversion_id}]" value='{$version_number}' />
      
          {* ------------------------------------------------------------------------------------ *}
          <tr bgcolor="{cycle values="#eeeeee,#d0d0d0"}">       
          <td>{$tsuite_info[$tc_id].tsuite_name}</td>{* <td>&nbsp;</td> *}
          <td>
                  <img class="clickable" src="{$tlImages.history_small}"
                       onclick="javascript:openExecHistoryWindow({$tc_exec.testcase_id});"
                       title="{$labels.execution_history}" />
                  <img class="clickable" src="{$tlImages.exec_icon}"
                       onclick="javascript:openExecutionWindow({$tc_exec.testcase_id},{$tcversion_id},{$gui->build_id},{$gui->tplan_id},{$gui->platform_id});"
                       title="{$labels.execution}" />
                  <img class="clickable" src="{$tlImages.edit}"
                       onclick="javascript:openTCaseWindow({$tc_exec.testcase_id},{$tc_exec.id});"
                       title="{$labels.design}" />        
          <a href="javascript:openTCaseWindow({$tc_exec.testcase_id},{$tc_exec.id},'editOnExec')" title="{$labels.show_tcase_spec}">
          {$gui->tcasePrefix|escape}{$cfg->testcase_cfg->glue_character}{$tc_exec.tc_external_id|escape}::{$labels.version}: {$tc_exec.version}::{$tc_exec.name|escape}
          </a>
          </td>
          <td class="{$tlCfg->results.code_status[$tc_exec.status]}">
          {$gui->execStatusValues[$tc_exec.status]}
          </td>
          <td>
              {if $tc_exec.can_be_executed}
              <select name="status[{$tcversion_id}]" id="status_{$tcversion_id}">
              {html_options options=$gui->execStatusValues}
              </select>
              {else}
                &nbsp;
              {/if}
          </td>         </tr>
      {/if}   {* Design only if test case version we want to execute is ACTIVE *}   
      {/foreach}
      </table>
      </div>
