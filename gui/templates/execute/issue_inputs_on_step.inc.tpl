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
     {if $gui->issueTrackerMetaData != ''}
      <p>
      {if $gui->issueTrackerMetaData.issueTypes != ''}
       <label for="issueTypeForStep[{$args_step_id}]">{$labels.issueType}</label>
       {html_options name="issueTypeForStep[{$args_step_id}]" 
         options=$gui->issueTrackerMetaData.issueTypes.items 
       }
      {/if}

      {if $gui->issueTrackerMetaData.priorities != ''}
       <label for="issuePriorityForStep[{$args_step_id}]">{$labels.issuePriority}</label> 
       {html_options name="issuePriorityForStep[{$args_step_id}]" 
        options=$gui->issueTrackerMetaData.priorities.items
       }
      {/if}
      </p>

      <p> 
      {* 
         IMPORTANT:
         Via Javascript the required attribute will be added when this input will be 
         done visible because user has clicked on 'Create Issue' checkbox
      *}
      {if $gui->issueTrackerMetaData.versions != ''}
        <label for="artifactVersionForStep[{$args_step_id}]">{$labels.artifactVersion}</label> 
        <select class="chosen-select-artifact" data-placeholder=" " id="artifactVersionForStep_{$args_step_id}" 
                {if $gui->issueTrackerMetaData.versions.isMultiSelect}
                 name="artifactVersionForStep[{$args_step_id}][]" size="2" multiple
                {else}
                 name="artifactVersionForStep[{$args_step_id}]"
                {/if} 
                >
        {html_options options=$gui->issueTrackerMetaData.versions.items
        }
        </select>
      {/if}
      
      {* 
         IMPORTANT:
         Via Javascript the required attribute will be added when this input will be 
         done visible because user has clicked on 'Create Issue' checkbox
      *}
      {if $gui->issueTrackerMetaData.components.items != ''}
        <label for="artifactComponentForStep[{$args_step_id}]">{$labels.artifactComponent}</label>         
         <select class="chosen-select-artifact" data-placeholder=" " id="artifactComponentForStep_{$args_step_id}" 
                 {if $gui->issueTrackerMetaData.components.isMultiSelect}
                   name="artifactComponentForStep[{$args_step_id}][]" size="2" multiple
                 {else}
                   name="artifactComponentForStep[{$args_step_id}]"
                 {/if} 
                 >
         {html_options options=$gui->issueTrackerMetaData.components.items
         }
         </select>
      {/if}
     </p>
     {/if}  {* $gui->issueTrackerMetaData *}      
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

      </table>
