      <table style="display:none;" 
             id="issueSectionForStep_{$args_step_id}">
      <tr>
        <td colspan="2">
          {* 
             IMPORTANT:
             Via Javascript the required attribute 
             will be added when this input will be 
             done visible because user has clicked on 
             'Create Issue' checkbox
          *}
          <div class="label">{$args_labels.bug_summary}</div>
           <input type="text" 
                  id="issueSummaryForStep_{$args_step_id}" 
                  name="issueSummaryForStep[{$args_step_id}]" 
                  value="{$gui->issueSummaryForStep[{$args_step_id}]}"
                  size="{#BUGSUMMARY_SIZE#}" 
                  maxlength="{$gui->issueTrackerCfg->bugSummaryMaxLength}" 
                  style="display:none;">
        </td>
      </tr>

      <tr>
      <td colspan="2">
        {$itMetaData = $gui->issueTrackerMetaData}
        {if '' != $itMetaData && null != $itMetaData}
          {include file="./issueTrackerMetadata.inc.tpl"
                   useOnSteps=1
          }  
        {/if}  {* $itMetaData *}
      </td>
      </tr>

      <tr>
        <td colspan="2">
          <div class="label">{$args_labels.bug_description}</div>
          <textarea id="issueBodyForStep_{$args_step_id}" 
                    name="issueBodyForStep[{$args_step_id}]" 
                    rows="{#BUGNOTES_ROWS#}" 
                    cols="{$gui->issueTrackerCfg->bugSummaryMaxLength}" ></textarea>          
        </td>
      </tr>
      <tr>
        <td colspan="2">
          <input type="checkbox" name="addLinkToTLForStep[{$args_step_id}]"  
                 id="addLinkToTLForStep_{$args_step_id}"
                 {if $gui->addLinkToTLChecked} checked {/if} >
          <span class="label">{$args_labels.add_link_to_tlexec}</span>
        </td>
      </tr>
      </table>
