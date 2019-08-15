     {if $gui->issueTrackerMetaData != ''}
      <p>
      {if $gui->issueTrackerMetaData.issueTypes != ''}
       <label for="issueType">{$labels.issueType}</label>
       {html_options name="issueType" options=$gui->issueTrackerMetaData.issueTypes.items 
        selected = $gui->issueType
       }
      {/if}

      {if $gui->issueTrackerMetaData.priorities != ''}
       <label for="issuePriority">{$labels.issuePriority}</label> 
       {html_options name="issuePriority" options=$gui->issueTrackerMetaData.priorities.items
        selected = $gui->issuePriority
       }
      {/if}
      </p>

      <p> 
      {* 
         IMPORTANT:
         Via Javascript the required attribute will be added when this input will be 
         done visible because user has clicked on 'Create Issue' checkbox
      *}
      {if $gui->issueTrackerMetaData.versions != '' && 
          $gui->issueTrackerMetaData.versions.items != ''}
        <label for="artifactVersion">{$labels.artifactVersion}</label> 
        <select class="chosen-select-artifact" data-placeholder=" " id="artifactVersion" 
                {if $gui->issueTrackerMetaData.versions.isMultiSelect}
                 name="artifactVersion[]" size="2" multiple
                {else}
                 name="artifactVersion"
                {/if} 
                >
        {html_options options=$gui->issueTrackerMetaData.versions.items
        selected = $gui->artifactVersion
        }
        </select>
      {/if}
      
      {* 
         IMPORTANT:
         Via Javascript the required attribute will be added when this input will be 
         done visible because user has clicked on 'Create Issue' checkbox
      *}
      {if $gui->issueTrackerMetaData.components.items != ''}
        <label for="artifactComponent">{$labels.artifactComponent}</label>         
         <select class="chosen-select-artifact" data-placeholder=" " id="artifactComponent" 
                 {if $gui->issueTrackerMetaData.components.isMultiSelect}
                   name="artifactComponent[]" size="2" multiple
                 {else}
                   name="artifactComponent"
                 {/if} 
                 >
         {html_options options=$gui->issueTrackerMetaData.components.items
         selected = $gui->artifactComponent
         }
         </select>
      {/if}
     </p>
     {/if}  {* $gui->issueTrackerMetaData *}    