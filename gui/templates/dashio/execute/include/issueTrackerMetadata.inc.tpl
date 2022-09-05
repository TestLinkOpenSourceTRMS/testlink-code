{*
@filesource: issueTrackerMetadata.inc.tpl
@used-by: bugAdd.tpl
          exec_img_controls.inc.tpl
          issue_inputs_on_step.inc.tpl

IMPORTANT:
Required attribute for artifactVersion and artifactComponent

bugAdd.tpl:
the attribute is setted while drawing, because
inputs are rendered or not, but without any user interaction.

exec_img_controls.inc.tpl:
these inputs are created with display:none.
We need to manage the 'required' attribute via Javascript only 
when thesse input will be done visible because user 
has clicked on 'Create Issue' checkbox

*}
     {* CRITIC NOTICE: chosenClass is CRITIC 
        for the javascript logic 
        present in exec_img_controls.inc.tpl *}
     {lang_get var='lbl_required_warning' s="required"}   
     {$itMetaData = $gui->issueTrackerMetaData}
     {$disabled=''}
     {if $gui->issueTrackerCfg->editIssueAttr == 0}
       {$disabled = ' disabled '}
     {/if} 
     {$required = ''}
     {$chosenClass = 'chosen-select-artifact'}
     {if $gui->allIssueAttrOnScreen == 1}
       {$required = 'required'}
       {$chosenClass = 'chosen-select'}
     {/if} 
     {$issueTypeName = "issueType"}     
     {$issueTypeID = "issueType"}     
     {$issuePriorityName = "issuePriority"}     
     {$issuePriorityID = "issuePriority"} 
     {$artifactVersionName = "artifactVersion"}   
     {$artifactVersionID = "artifactVersion"}   
     {$artifactComponentName = "artifactComponent"}   
     {$artifactComponentID = "artifactComponent"}   

     {if $useOnSteps == 1}
       {$issueTypeName = "issueTypeForStep[{$args_step_id}]"}
       {$issueTypeID = "issueTypeForStep_{$args_step_id}"}

       {$issuePriorityName = 
         "issuePriorityForStep[{$args_step_id}]"}
       {$issuePriorityID = "issuePriorityForStep_{$args_step_id}"}

       {$artifactVersionName = 
         "artifactVersionForStep[{$args_step_id}]"}   
       {$artifactVersionID = 
         "artifactVersionForStep_{$args_step_id}"}   

       {$artifactComponentName = 
         "artifactComponentForStep[{$args_step_id}]"}   
       {$artifactComponentID = 
         "artifactComponentForStep_{$args_step_id}"}   
     {/if}


     {if '' != $itMetaData && null != $itMetaData}
      <p>
      {if $itMetaData.issueTypes != ''          
          && $itMetaData.issueTypes.items != ''
          && is_array($itMetaData.issueTypes.items)}
        <label class="label" for="{$issueTypeName}">{$labels.issueType}</label>
        {if $gui->issueTrackerCfg->editIssueAttr == 0}
          <input type="hidden" name="{$issueTypeName}" id="hidden{$issueTypeID}"
                 value="{$gui->issueType}">
        {/if}
        <select {$disabled} name="{$issueTypeName}"
                id="{$issueTypeID}">
          {html_options options=$itMetaData.issueTypes.items
                        selected=$gui->issueType}
        </select>
      {/if}

      {if $itMetaData.priorities != ''
          && $itMetaData.priorities.items != ''
          && is_array($itMetaData.priorities.items)}
        <label class="label" for="{$issuePriorityName}">{$labels.issuePriority}
        </label> 
        {if $gui->issueTrackerCfg->editIssueAttr == 0}
          <input type="hidden" name="{$issuePriorityName}" id="hidden{$issuePriorityID}"
                 value="{$gui->issuePriority}">
        {/if}
        <select {$disabled} name="{$issuePriorityName}"
                id="{$issuePriorityID}">
          {html_options options=$itMetaData.priorities.items
                        selected=$gui->issuePriority}
        </select>
      {/if}
      </p>
      <p> 
      {if $itMetaData.versions != '' 
          && $itMetaData.versions.items != '' 
          && is_array($itMetaData.versions.items)}
        <label class="label" for="{$artifactVersionName}">{$labels.artifactVersion}</label> 
        {if $gui->issueTrackerCfg->editIssueAttr == 0}
          <select style="display:none" 
                  {if $itMetaData.versions.isMultiSelect}
                   name="{$artifactVersionName}[]" size="2" multiple
                  {else}
                   name="{$artifactVersionName}"
                  {/if} 
                  id="hidden{$artifactVersionID}" >
          {html_options options=$itMetaData.versions.items
                        selected = $gui->artifactVersion}
          </select>
        {/if}
        <select class="{$chosenClass}" data-placeholder="{$lbl_required_warning}" 
                required="{$required}"
                {if $itMetaData.versions.isMultiSelect}
                 name="{$artifactVersionName}[]" size="2" multiple
                {else}
                 name="{$artifactVersionName}"
                {/if} 
                id="{$artifactVersionID}" {$disabled} >
        {html_options options=$itMetaData.versions.items
                      selected = $gui->artifactVersion}
        </select>
      {/if}
      {if $itMetaData.components != '' 
          && $itMetaData.components.items != '' 
          && is_array($itMetaData.components.items)}
        <label class="label" for="{$artifactComponentName}">{$labels.artifactComponent}</label>        
        {if $gui->issueTrackerCfg->editIssueAttr == 0}
          <select style="display:none" 
                  {if $itMetaData.components.isMultiSelect}
                   name="{$artifactComponentName}[]" size="2" multiple
                  {else}
                   name="{$artifactComponentName}"
                  {/if} 
                  id="hidden{$artifactComponentID}" >
          {html_options options=$itMetaData.components.items
                        selected = $gui->artifactComponent}
          </select>
        {/if}
         <select class="{$chosenClass}"  
                 data-placeholder="{$lbl_required_warning}" 
                 required="{$required}"
                 {if $gui->issueTrackerMetaData.components.isMultiSelect}
                   name="{$artifactComponentName}[]" size="2" multiple
                 {else}
                   name="{$artifactComponentName}"
                 {/if} 
                 id="{$artifactComponentID}" {$disabled}>
         {html_options 
               options=$gui->issueTrackerMetaData.components.items
               selected=$gui->artifactComponent}
         </select>
      {/if}
     </p>
     {/if}  {* $gui->issueTrackerMetaData *}