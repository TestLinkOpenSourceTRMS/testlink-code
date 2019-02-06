{$args_labels.bug_create_into_bts}&nbsp;
<input type="checkbox" name="issueForStep[{$args_step_id}]"  
       id="issueForStep_{$args_step_id}" 
       onclick="javascript:toogleShowHide('issueSectionForStep_{$args_step_id}');
                javascript:toogleRequiredOnShowHide('issueSummaryForStep_{$args_step_id}');
                javascript:toogleRequiredOnShowHide('artifactVersionForStep_{$args_step_id}');
                javascript:toogleRequiredOnShowHide('artifactComponentForStep_{$args_step_id}');
               ">

