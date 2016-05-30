{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: inc_exec_test_spec.tpl,v 1.22.2.1 2010/11/18 15:17:45 mx-julian Exp $
Purpose: draw execution controls (input for notes and results)
Author : franciscom

*}  
    {$tableColspan="4"}
    {if $tlCfg->exec_cfg->steps_exec}
      {$tableColspan="6"}
    {/if}
    
    {$getReqAction="lib/requirements/reqView.php?showReqSpecTitle=1&requirement_id="}
    {$testcase_id=$args_tc_exec.testcase_id}
    {$tcversion_id=$args_tc_exec.id}
    
     
    <div class="exec_test_spec">
    <table class="simple">
    <tr>
      <th colspan="{$tableColspan}" class="title">{$args_labels.test_exec_summary}</th>
    </tr>
    <tr>
      <td colspan="{$tableColspan}">{if $gui->testDesignEditorType == 'none'}{$args_tc_exec.summary|nl2br}{else}{$args_tc_exec.summary}{/if}</td>
    </tr>
    <tr>
      <th colspan="{$tableColspan}" class="title">{$args_labels.preconditions}</th>
    </tr>
    <tr>
      <td colspan="{$tableColspan}">{if $gui->testDesignEditorType == 'none'}{$args_tc_exec.preconditions|nl2br}{else}{$args_tc_exec.preconditions}{/if}</td>
    </tr>
    <tr>
      <td colspan="{$tableColspan}">&nbsp;</td>
    </tr>

    {if $args_design_time_cf[$testcase_id].before_steps_results != ''}
    <tr>
      <td colspan="{$tableColspan}"> {$args_design_time_cf[$testcase_id].before_steps_results}</td>
    </tr>
    {/if}

    {if $args_tc_exec.steps != '' && !is_null($args_tc_exec.steps)}
      {include file="testcases/inc_steps.tpl"
               layout=$args_cfg->exec_cfg->steps_results_layout
               edit_enabled=false
               ghost_control=false
               add_exec_info=$tlCfg->exec_cfg->steps_exec
               steps=$args_tc_exec.steps}
    {/if}

    <tr>
      <td colspan="{$tableColspan}"><b>{$args_labels.execution_type}{$smarty.const.TITLE_SEP}</b>
                                       {$args_execution_types[$args_tc_exec.execution_type]}</td>
    </tr>
    <tr>
      <td colspan="{$tableColspan}"><b>{$args_labels.estimated_execution_duration}{$smarty.const.TITLE_SEP}</b>
        {$args_tc_exec.estimated_exec_duration}
      </td>
    </tr>

    {* relations - 20140814 *}
    {if $args_relations != '' && !is_null($args_relations)}
      <tr>
        <td colspan="{$tableColspan}">
        {include file="execute/exec_tc_relations.inc.tpl"  
                 argsColSpan=$tableColspan argsRelSet=$args_relations}  
        </td>
      </tr>
    {/if}

    <tr>
    <td colspan="{$tableColspan}">
      {if $args_design_time_cf[$testcase_id].standard_location != ''}
          <div id="cfields_design_time_tcversionid_{$tcversion_id}" class="custom_field_container" 
          style="background-color:#dddddd;">{$args_design_time_cf[$testcase_id].standard_location}
          </div>
      {/if} 
      </td>
    </tr>
 
    <tr>
    {* ------------------------------------------------------------------------------------- *}
    {if $args_enable_custom_field and $args_tc_exec.active == 1}
      {if isset($args_execution_time_cf[$testcase_id]) && $args_execution_time_cf[$testcase_id] != ''}
        <tr>
          <td colspan="{$tableColspan}">
            <div id="cfields_exec_time_tcversionid_{$tcversion_id}" class="custom_field_container" 
                 style="background-color:#dddddd;">{$args_execution_time_cf[$testcase_id]}
            </div>
          </td>
        </tr>
      {/if}
    {/if} {* if $args_enable_custom_field *}
    {* ------------------------------------------------------------------------------------- *}
    
      <td colspan="{$tableColspan}">
      {if $args_testplan_design_time_cf[$testcase_id] != ''}
          <div id="cfields_testplan_design_time_tcversionid_{$tcversion_id}" class="custom_field_container" 
          style="background-color:#dddddd;">{$args_testplan_design_time_cf[$testcase_id]}
          </div>
      {/if} 
      </td>
    </tr>
    
    <tr>
      <td colspan="{$tableColspan}">
      {if $args_tcAttachments[$testcase_id] neq null}
        {include file="inc_attachments.tpl" 
                 attach_tableName="nodes_hierarchy" 
                 attach_downloadOnly=true 
                 attach_attachmentInfos=$args_tcAttachments[$testcase_id] 
                 attach_tableClassName="bordered"
                 attach_tableStyles="background-color:#dddddd;width:100%"}
      {/if}
      </td>
    </tr>

    {if isset($args_keywords)}
      <tr>
        <td colspan="{$tableColspan}">
          <b>{$args_labels.keywords}{$smarty.const.TITLE_SEP}</b>&nbsp
          {foreach item=keyword_item from=$args_keywords name=itemKeywords}
            {$keyword_item.keyword|escape}{if !$smarty.foreach.itemKeywords.last},&nbsp;{/if} 
          {/foreach}
        </td>
      </tr>
    {/if}

    </table>
    </div>

    <br />
    {if isset($args_req_details)}
    <div class="exec_test_spec">
      <table class="test_exec"  >
      <tr>
        <th colspan="{$tableColspan}" class="title">{$args_labels.reqs}</th>
      </tr>
        
      {foreach from=$args_req_details key=id item=req_elem}
      <tr>
        <td>
        <span class="bold">
         {$tlCfg->gui_separator_open}{$req_elem.req_spec_title}{$tlCfg->gui_separator_close}&nbsp;
         <a href="javascript:openLinkedReqWindow({$req_elem.id})"  
            title="{$args_labels.click_to_open}">
          {$req_elem.req_doc_id|escape}{$tlCfg->gui_title_separator_1}{$req_elem.title|escape}
         </a>
        </span>
       </td>
      </tr>
      {/foreach}
      </table>
      </div>
      <br />
    {/if}
