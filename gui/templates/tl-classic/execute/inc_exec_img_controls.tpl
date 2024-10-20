{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource inc_exec_controls.tpl
Purpose: draw execution controls
         input for notes and results
         buttons
         clickable images

Author : franciscom
*}	
{* Russian Doll, make name shorter *}
{$tcvID = $args_tcversion_id}  
      {$ResultsStatusCode=$tlCfg->results.status_code}
      {if $args_save_type == 'bulk'}
        {$radio_id_prefix = "bulk_status"}
      {else}
        {$radio_id_prefix = "statusSingle"}
      {/if}

  {if $gui->grants->execute}
  	<table class="no-border" 
        style="width:100%;border: thick solid white">
    		<tr border='0'>
    			<td style="text-align: center;width:75%; border: 0px">
    				<div class="title">{$args_labels.test_exec_notes}</div>
            {$args_webeditor} 
          <br>  
          <!-- {include file="attachments_simple.inc.tpl" attach_id=0} -->
    			</td>
    			<td valign="top" style="width:25%; border: 0px">			
      				{* status of test *}
        			<div class="title" style="text-align: center;">
              {if $args_save_type == 'bulk'} {$args_labels.test_exec_result} {else} &nbsp; {/if}
              </div>

      				<div class="resultBox">
                {if $args_save_type == 'bulk'}
                  {foreach key=verbose_status item=locale_status from=$tlCfg->results.status_label_for_exec_ui}
      						      <input type="radio" {$args_input_enable_mgmt} name="{$radio_id_prefix}[{$tcvID}]" 
      						      id="{$radio_id_prefix}_{$tcvID}_{$ResultsStatusCode.$verbose_status}" 
      							    value="{$ResultsStatusCode.$verbose_status}"
      											onclick="javascript:set_combo_group('execSetResults','status_','{$ResultsStatusCode.$verbose_status}');"
      							    {if $verbose_status eq $tlCfg->results.default_status}
      							        checked="checked" 
      							    {/if} /> &nbsp;{lang_get s=$locale_status}<br />
      					  {/foreach}
                {/if}

                {if $tlCfg->exec_cfg->features->exec_duration->enabled}	
                  <br />	
                  <img src="{$tlImages.execution_duration}" 
                         title="{$args_labels.execution_duration}">
                  <input type="text" name="execution_duration" id="execution_duration"
                         size="{#EXEC_DURATION_SIZE#}" 
                         onkeyup="this.value=this.value.replace(/[^0-9]/g,'');"
                         maxlength="{#EXEC_DURATION_MAXLEN#}">  
                  {/if}       		 			
                {if $args_save_type == 'single'}
                  <br />
                  <br />
                  {$addBR=0}
                  {if $tc_exec.assigned_user == ''}
                   <img src="{$tlImages.assign_task}" 
                         title="{$args_labels.assign_exec_task_to_me}">
                    <input type="checkbox" name="assignTask"  id="assignTask"
                    {if $gui->assignTaskChecked} checked {/if}>
                    &nbsp;
                  {/if}

                  {if $tlCfg->exec_cfg->exec_mode->new_exec == 'latest'}
                    {$addBR=1}
                   <img src="{$tlImages.copy_attachments}" 
                         title="{$args_labels.copy_attachments_from_latest_exec}">
                    <input type="checkbox" name="copyAttFromLEXEC"  id="copyAttFromLEXEC">
                    &nbsp;
                  {/if}


                  
                  {if $gui->tlCanCreateIssue}
                    {$addBR=1}
                    <img src="{$tlImages.bug_create_into_bts}" 
                         title="{$args_labels.bug_create_into_bts}">
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
                     <input type="checkbox" name="copyIssues[{$tcvID}]" id="copyIssues" 
                      {if $tlCfg->exec_cfg->copyLatestExecIssues->default} checked {/if}>
                     <br />
                  {/if}

                   <input type="hidden" name="statusSingle[{$tcversion_id}]" 
                          id="statusSingle_{$tcversion_id}" value="">
                   <input type="hidden" name="save_results" id="save_results" value="0">
                   <br />
                   <br />
                   <button style="display: none;" type="submit" 
                           id="hidden-submit-button"></button>
                   {foreach key=kode item=ikval from=$gui->execStatusIcons}
                     {$in = $ikval.img}
                     <img src="{$tlImages.$in}" title="{$ikval.title}"
                          name="fastExec{$kode}[{$tcversion_id}]"
                          id="fastExec{$kode}_{$tcversion_id}"
                          onclick="javascript:saveExecStatus({$tcvID},'{$kode}',{$tlCfg->exec_cfg->steps_notes_concat});">&nbsp;
                   {/foreach}  
                   <br />
                   <br />

                   <input type="hidden" name="save_and_next" 
                                        id="save_and_next" value="0">
                   {foreach key=kode item=ikval from=$gui->execStatusIconsNext}
                     {$in = $ikval.img}
                     <img src="{$tlImages.$in}" title="{$ikval.title}"
                          name="fastExecNext{$kode}[{$tcversion_id}]"
                          id="fastExecNext{$kode}_{$tcversion_id}"
                          onclick="javascript:saveExecStatus({$tcvID},'{$kode}',{$tlCfg->exec_cfg->steps_notes_concat},1);">&nbsp;
                   {/foreach}  
                   <br />
                   <br />
                    <input type="submit" name="move2next[{$tcvID}]" 
                        {$args_input_enable_mgmt}
                        onclick="javascript:moveToNextTC({$tcvID});"
                        value="{$args_labels.btn_next_tcase}" />
      		 			  {else}
       	    	        <input type="submit" id="do_bulk_save" name="do_bulk_save"
        	    	             value="{$args_labels.btn_save_tc_exec_results}"/>
      		 			  {/if}       
      				</div>
      		</td>
      	</tr>

        <tr border='0'>
          <td colspan="2">
            {include file="attachments_simple.inc.tpl" attach_id=0}
          </td>
        </tr>

        {if $args_save_type == 'bulk' 
            && $args_execution_time_cfields != ''}
          <tr>
              <td colspan="2">
      					<div id="cfields_exec_time_tcversionid_{$tcvID}" class="custom_field_container" 
      						style="background-color:#dddddd;">
                {$args_labels.testcase_customfields}
                {$args_execution_time_cfields.0} {* 0 => bulk *}
                </div> 
              </td>
          </tr>
        {/if}
  	</table>
  {else}
    <input type="submit" name="move2next[{$tcvID}]" 
           {$args_input_enable_mgmt}
           onclick="javascript:moveToNextTC({$tcvID});"
           value="{$args_labels.btn_next_tcase}" />
  {/if}

  {if $gui->addIssueOp != '' 
      && !is_null($gui->addIssueOp) 
      && !is_null($gui->addIssueOp.type) }  
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
          Via Javascript the required attribute will be 
          added when this input will be done visible 
          because user has clicked on 'Create Issue' checkbox
        *}
        <div class="label">{$args_labels.bug_summary}</div>
        <input type="text" id="bug_summary" 
               name="bug_summary" value="{$gui->bug_summary}"
               size="{#BUGSUMMARY_SIZE#}" 
               maxlength="{$gui->issueTrackerCfg->bugSummaryMaxLength}" 
               style="display:none;">
      </td>
    </tr>

    {$itMetaData = $gui->issueTrackerMetaData}
    {if '' != $itMetaData && null != $itMetaData}
      <tr>
        <td colspan="2">
        {include file="./issueTrackerMetadata.inc.tpl"
                 useOnSteps=0}  
        </td>
      </tr>
    {/if}

    <tr>
        <td colspan="2">
          <div class="label">{$args_labels.bug_description}</div>
          <textarea id="bug_notes" name="bug_notes" 
                  rows="{#BUGNOTES_ROWS#}" cols="{$gui->issueTrackerCfg->bugSummaryMaxLength}" ></textarea>          
        </td>
    </tr>

    <tr>
        <td colspan="2">
          <input type="checkbox" name="addLinkToTL"  id="addLinkToTL"
                 {if $gui->addLinkToTLChecked} checked {/if} >
          <span class="label">{$args_labels.add_link_to_tlexec}</span>
        </td>
    </tr>

    <tr>
        <td colspan="2">
          <input type="checkbox" name="addLinkToTLPrintView"
                 id="addLinkToTLPrintView"
                 {if $gui->addLinkToTLPrintViewChecked} checked {/if} >
          <span class="label">{$args_labels.add_link_to_tlexec_print_view}</span>
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

