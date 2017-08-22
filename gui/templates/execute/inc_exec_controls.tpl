{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource inc_exec_controls.tpl
Purpose: draw execution controls (input for notes and results)
Author : franciscom

@internal revisions
@since 1.9.15 
*}	
      {$ResultsStatusCode=$tlCfg->results.status_code}
      {if $args_save_type == 'bulk'}
        {$radio_id_prefix = "bulk_status"}
      {else}
        {$radio_id_prefix = "statusSingle"}
      {/if}

  		<table class="invisible">
  		<tr>
  			<td style="text-align: center;">
  				<div class="title">{$args_labels.test_exec_notes}</div>
          {$args_webeditor} 
  			</td>
  			<td valign="top" style="width: 30%;">			
    				{* status of test *}
      			<div class="title" style="text-align: center;">
            {if $args_save_type == 'bulk'} {$args_labels.test_exec_result} {else} &nbsp; {/if}
            </div>

    				<div class="resultBox">
              {if $args_save_type == 'bulk'}
                {foreach key=verbose_status item=locale_status from=$tlCfg->results.status_label_for_exec_ui}
    						      <input type="radio" {$args_input_enable_mgmt} name="{$radio_id_prefix}[{$args_tcversion_id}]" 
    						      id="{$radio_id_prefix}_{$args_tcversion_id}_{$ResultsStatusCode.$verbose_status}" 
    							    value="{$ResultsStatusCode.$verbose_status}"
    											onclick="javascript:set_combo_group('execSetResults','status_','{$ResultsStatusCode.$verbose_status}');"
    							    {if $verbose_status eq $tlCfg->results.default_status}
    							        checked="checked" 
    							    {/if} /> &nbsp;{lang_get s=$locale_status}<br />
    					  {/foreach}
              {else}
                {$args_labels.test_exec_result}&nbsp;
                <select name="statusSingle[{$tcversion_id}]" id="statusSingle_{$tcversion_id}">
                {html_options options=$gui->execStatusValues}
                </select>
              {/if}

                {if $tlCfg->exec_cfg->features->exec_duration->enabled}	
                <br />	
                {$args_labels.execution_duration}&nbsp;
                <input type="text" name="execution_duration" id="execution_duration"
                       size="{#EXEC_DURATION_SIZE#}" 
                       onkeyup="this.value=this.value.replace(/[^0-9]/g,'');"
                       maxlength="{#EXEC_DURATION_MAXLEN#}">  
                {/if}       		 			
              {if $args_save_type == 'single'}
                <br />
                {$addBR=0}
                {if $tc_exec.assigned_user == ''}
                  {$args_labels.assign_exec_task_to_me}&nbsp;
                  <input type="checkbox" name="assignTask"  id="assignTask">
                  {$addBR=1}
                {/if}
                
                {if $gui->tlCanCreateIssue}
                  {if $addBR}<br>{/if} 
                  {$addBR=1}
                  {$args_labels.bug_create_into_bts}&nbsp;
                  <input type="checkbox" name="createIssue"  id="createIssue" 
                         onclick="javascript:toogleShowHide('issue_summary');
                         javascript:toogleRequiredOnShowHide('bug_summary');
                         javascript:toogleRequiredOnShowHide('artifactVersion');
                         javascript:toogleRequiredOnShowHide('artifactComponent');
                         ">
                {/if}

                {if $tlCfg->exec_cfg->copyLatestExecIssues->enabled}
                  {if $addBR}<br>{/if}
                  {$args_labels.bug_copy_from_latest_exec}&nbsp;
                   <input type="checkbox" name="copyIssues[{$args_tcversion_id}]" id="copyIssues" 
                    {if $tlCfg->exec_cfg->copyLatestExecIssues->default} checked {/if}>
                {/if}


                <br />
                <br />    
    		 			      <input type="submit" name="save_results[{$args_tcversion_id}]" 
    		 			        {$args_input_enable_mgmt}
                      onclick="document.getElementById('save_button_clicked').value={$args_tcversion_id};return checkSubmitForStatusCombo('statusSingle_{$tcversion_id}','{$ResultsStatusCode.not_run}')"
    		 			        value="{$args_labels.btn_save_tc_exec_results}" />
    		 			         
    		 			      <input type="submit" name="save_and_next[{$args_tcversion_id}]" 
    		 			        {$args_input_enable_mgmt}
                      onclick="document.getElementById('save_button_clicked').value={$args_tcversion_id};return checkSubmitForStatusCombo('statusSingle_{$tcversion_id}','{$ResultsStatusCode.not_run}')"
    		 			        value="{$args_labels.btn_save_exec_and_movetonext}" />

                  <input type="submit" name="move2next[{$args_tcversion_id}]" 
                      {$args_input_enable_mgmt}
                      onclick="document.getElementById('save_button_clicked').value={$args_tcversion_id};"
                      value="{$args_labels.btn_next}" />


    		 			  {else}
     	    	        <input type="submit" id="do_bulk_save" name="do_bulk_save"
      	    	             value="{$args_labels.btn_save_tc_exec_results}"/>

    		 			  {/if}       
    				</div>
    			</td>
    		</tr>
        {if $args_save_type == 'bulk' && $args_execution_time_cfields != ''}
          <tr><td colspan="2">
  					<div id="cfields_exec_time_tcversionid_{$args_tcversion_id}" class="custom_field_container" 
  						style="background-color:#dddddd;">
            {$args_labels.testcase_customfields}
            {$args_execution_time_cfields.0} {* 0 => bulk *}
            </div> 
          </td></tr>
        {/if}
  		</table>

      {if $gui->addIssueOp != '' && !is_null($gui->addIssueOp) && 
          !is_null($gui->addIssueOp.type) }  
        {$ak = $gui->addIssueOp.type} 
        <hr> 
        <table id="addIssueFeedback">
        <tr>
          <td colspan="2" class="label">{$args_labels.create_issue_feedback}</td>
        </tr>
  
        {if $ak == 'createIssue'}
          <tr>
            <td colspan="2">
              <div class="label">{$gui->addIssueOp[$ak].msg}</div>
            </td>
          </tr>
        {else}
          {foreach key=ik item=ikmsg from=$gui->addIssueOp[$ak]}
          <tr>
            <td colspan="2">
              <div class="label">{$ikmsg.msg}</div>
            </td>
          </tr>
          {/foreach}
        {/if}
        </table>
        <hr>
      {/if}

      <table style="display:none;" id="issue_summary">
      <tr>
        <td colspan="2">
          {* 
             IMPORTANT:
             Via Javascript the required attribute will be added when this input will be 
             done visible because user has clicked on 'Create Issue' checkbox
          *}
          <div class="label">{$args_labels.bug_summary}</div>
           <input type="text" id="bug_summary" name="bug_summary" value="{$gui->bug_summary}"
                  size="{#BUGSUMMARY_SIZE#}" maxlength="{$gui->issueTrackerCfg->bugSummaryMaxLength}" 
                  style="display:none;">
        </td>
      </tr>

      <tr>
      <td colspan="2">
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
      </td>
      </tr>

      <tr>
        <td colspan="2">
          <div class="label">{$args_labels.bug_description}</div>
          <textarea id="bug_notes" name="bug_notes" 
                  rows="{#BUGNOTES_ROWS#}" cols="{$gui->issueTrackerCfg->bugSummaryMaxLength}" ></textarea>          
        </td>
      </tr>

      <tr>
        <td colspan="2">
          <input type="checkbox" name="addLinkToTL"  id="addLinkToTL">
          <span class="label">{$args_labels.add_link_to_tlexec}</span>
        </td>
      </tr>

      </table>
      
      </br>
      <div class="messages" style="align:center;">
      {$args_labels.exec_not_run_result_note}
      </div>


      <script>
      jQuery( document ).ready(function() {

      // IMPORTANT
      // For some chosen select I want on page load to be DISPLAY NONE
      // That's why I've changes from original example on the line where styles were applied
      // 
      jQuery(".chosen-select-artifact").chosen({ width: "35%" });

      // From https://github.com/harvesthq/chosen/issues/515
      jQuery(".chosen-select-artifact").each(function(){
          // take each select and put it as a child of the chosen container
          // this mean it'll position any validation messages correctly
          jQuery(this).next(".chosen-container").prepend(jQuery(this).detach());

          // apply all the styles, personally, I've added this to my stylesheet
          // TESTLINK NOTE
          jQuery(this).attr("style","display:none!important; position:absolute; clip:rect(0,0,0,0)");

          // to all of these events, trigger the chosen to open and receive focus
          jQuery(this).on("click focus keyup",function(event){
              jQuery(this).closest(".chosen-container").trigger("mousedown.chosen");
          });
      });
      });
      </script>

