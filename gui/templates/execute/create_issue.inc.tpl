  {if $gui->tlCanCreateIssue}
      {$args_labels.bug_create_into_bts}&nbsp;
        <input type="checkbox" name="issueForStep[]"  
               id="issueForStep_{}" 
               onclick="javascript:toogleShowHide('issue_summary');
                        javascript:toogleRequiredOnShowHide('bug_summary');
                        javascript:toogleRequiredOnShowHide('artifactVersion');
                        javascript:toogleRequiredOnShowHide('artifactComponent');
                        ">
  {/if}
  