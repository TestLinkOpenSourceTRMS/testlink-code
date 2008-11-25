{*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: execSetResults.tpl,v 1.28 2008/11/25 18:07:21 franciscom Exp $
Purpose: smarty template - show tests to add results
Rev:
  20081125 - franciscom - BUGID 1902 - fixed check to display button to launch remote executions
  
  20080528 - franciscom - BUGID 1504 - version number management
	20080515 - havlatm - updated help link
  20080322 - franciscom - feature: allow edit of execution notes
                          minor refactoring.
  20071231 - franciscom - new show/hide section to show exec notes
  20071103 - franciscom - BUGID 700
  20071101 - franciscom - added test automation code
  20070826 - franciscom - added some niftycube effects
  20070519 - franciscom -
  BUGID 856: Guest user can execute test case

  20070211 - franciscom - added delete logic
  20070205 - franciscom - display test plan custom fields.
  20070125 - franciscom - management of closed build
  20070104 - franciscom - custom field management for test cases
  20070101 - franciscom - custom field management for test suite div
*}

{assign var="attachment_model" value=$cfg->exec_cfg->att_model}
{assign var="title_sep"  value=$smarty.const.TITLE_SEP}
{assign var="title_sep_type3"  value=$smarty.const.TITLE_SEP_TYPE3}

{assign var="input_enabled_disabled" value="disabled"}
{assign var="att_download_only" value=true}
{assign var="enable_custom_fields" value=false}
{assign var="draw_submit_button" value=false}

{assign var="show_current_build" value=0}
{assign var="my_build_name" value=$gui->build_name|escape}

{lang_get s='build' var='build_title'}

{lang_get var='labels'
          s='edit_notes,build_is_closed,test_cases_cannot_be_executed,test_exec_notes,test_exec_result,
             th_testsuite,details,warning_delete_execution,title_test_case,th_test_case_id,
             version,has_no_assignment,assigned_to,execution_history,exec_notes,
             last_execution,exec_any_build,date_time_run,test_exec_by,build,exec_status,
             test_status_not_run,tc_not_tested_yet,last_execution,exec_current_build,
	           attachment_mgmt,bug_mgmt,delete,closed_build,alt_notes,alt_attachment_mgmt,
	           img_title_bug_mgmt,img_title_delete_execution,test_exec_summary,title_t_r_on_build,
	           execution_type_manual,execution_type_auto,run_mode,or_unassigned_test_cases,
	           no_data_available,import_xml_results,btn_save_all_tests_results,
	           testcaseversion,btn_print,execute_and_save_results,warning,warning_nothing_will_be_saved,
	           test_exec_steps,test_exec_expected_r,btn_save_tc_exec_results,only_test_cases_assigned_to'}



{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

{include file="inc_head.tpl" popup='yes' openHead='yes' jsValidate="yes" editorType=$gui->editorType}
<script language="JavaScript" src="gui/javascript/radio_utils.js" type="text/javascript"></script>
<script language="JavaScript" src="gui/javascript/expandAndCollapseFunctions.js" type="text/javascript"></script>

{if #ROUND_EXEC_HISTORY# || #ROUND_TC_TITLE# || #ROUND_TC_SPEC#}
  {assign var="round_enabled" value=1}
  <script language="JavaScript" src="{$basehref}gui/niftycube/niftycube.js" type="text/javascript"></script>
{/if}

<script language="JavaScript" type="text/javascript">
var msg="{$labels.warning_delete_execution}";
var import_xml_results="{$labels.import_xml_results}";
</script>

{include file="inc_del_onclick.tpl"}

{*  

{if $smarty.const.USE_EXT_JS_LIBRARY || $tlCfg->treemenu_type == 'EXTJS'}
  {include file="inc_ext_js.tpl"}
{/if}

*}

<script language="JavaScript" type="text/javascript">
{literal}
function load_notes(panel,exec_id)
{
  var url2load=fRoot+'lib/execute/getExecNotes.php?exec_id=' + exec_id;
  panel.load({url:url2load});
}
{/literal}
</script>

{literal}
<script type="text/javascript">
{/literal}
var alert_box_title="{$labels.warning}";
var warning_nothing_will_be_saved="{$labels.warning_nothing_will_be_saved}";
{literal}
function validateForm(f)
{
  var status_ok=true;
  var cfields_inputs='';
  var cfChecks;
  var cfield_container;
  var access_key;
  cfield_container=document.getElementById('save_button_clicked').value;
  access_key='cfields_exec_time_tcversionid_'+cfield_container; 
    
    
 	cfields_inputs = document.getElementById(access_key).getElementsByTagName('input');
  cfChecks=validateCustomFields(cfields_inputs);
  if( !cfChecks.status_ok )
  {
      var warning_msg=cfMessages[cfChecks.msg_id];
      alert_message(alert_box_title,warning_msg.replace(/%s/, cfChecks.cfield_label));
      return false;
  }
  return true;
}

/*
  function: checkSubmitForStatus
            if a radio (with a particular id, see code for details)
            with $statusCode has been checked, then false is returned to block form submit().
            
            Dev. Note - remember this:
            
            KO:
               onclick="foo();checkSubmitForStatus('n')"
            OK
               onclick="foo();return checkSubmitForStatus('n')"
                              ^^^^^^ 
            

  args :
  
  returns: 

*/
function checkSubmitForStatus($statusCode)
{
  var button_clicked;
  var access_key;
  var isChecked;
  
  button_clicked=document.getElementById('save_button_clicked').value;
  access_key='status_'+button_clicked+'_'+$statusCode; 
 	isChecked = document.getElementById(access_key).checked;
  if(isChecked)
  {
      alert_message(alert_box_title,warning_nothing_will_be_saved);
      return false;
  }
  return true;
}
</script>
{/literal}





</head>
{*
IMPORTANT: if you change value, you need to chang init_args() logic on execSetResults.php
*}
{assign var="tplan_notes_view_memory_id" value="tpn_view_status"}
{assign var="build_notes_view_memory_id" value="bn_view_status"}
{assign var="bulk_controls_view_memory_id" value="bc_view_status"}


<body onLoad="show_hide('tplan_notes','{$tplan_notes_view_memory_id}',{$gui->tpn_view_status});
              show_hide('build_notes','{$build_notes_view_memory_id}',{$gui->bn_view_status});
              show_hide('bulk_controls','{$bulk_controls_view_memory_id}',{$gui->bc_view_status});
              multiple_show_hide('{$tsd_div_id_list}','{$tsd_hidden_id_list}',
                                 '{$tsd_val_for_hidden_list}');
              {if $round_enabled}Nifty('div.exec_additional_info');{/if}
              {if #ROUND_TC_SPEC# }Nifty('div.exec_test_spec');{/if}
              {if #ROUND_EXEC_HISTORY# }Nifty('div.exec_history');{/if}
              {if #ROUND_TC_TITLE# }Nifty('div.exec_tc_title');{/if}">

<h1 class="title">
	{$labels.title_t_r_on_build} {$my_build_name}
	{if $gui->ownerDisplayName != ""}
	  {$title_sep_type3}{$labels.only_test_cases_assigned_to}{$title_sep}{$gui->ownerDisplayName|escape}
	  {if $gui->include_unassigned}
	    {$labels.or_unassigned_test_cases}
	  {/if}
	{/if}
	{include file="inc_help.tpl" helptopic="hlp_executeMain"}
</h1>


<div id="main_content" class="workBack">
  {if $gui->build_is_open == 0}
  <div class="warning_message" style="align:center;">
     {$labels.build_is_closed}<br />
     {$labels.test_cases_cannot_be_executed}
  </div>
  <br />
  {/if}

<form method="post" id="execSetResults" name="execSetResults" 
      onSubmit="javascript:return validateForm(this);">

  <input type="hidden" id="save_button_clicked"  name="save_button_clicked" value="0" />
  <input type="hidden" id="do_delete"  name="do_delete" value="0" />
  <input type="hidden" id="exec_to_delete"  name="exec_to_delete" value="0" />

  {* -------------------------------------------------------------------------------- *}
  {* Test Plan notes show/hide management                                             *}
  {* -------------------------------------------------------------------------------- *}
  {lang_get s='test_plan_notes' var='container_title'}
  {assign var="div_id" value='tplan_notes'}
  {assign var="memstatus_id" value=$tplan_notes_view_memory_id}

  {include file="inc_show_hide_mgmt.tpl"
           show_hide_container_title=$container_title
           show_hide_container_id=$div_id
           show_hide_container_draw=false
           show_hide_container_class='exec_additional_info'
           show_hide_container_view_status_id=$memstatus_id}

  <div id="{$div_id}" class="exec_additional_info">
    {$gui->testplan_notes}
    {if $gui->testplan_cfields neq ''} <div id="cfields_testplan" class="custom_field_container">{$gui->testplan_cfields}</div>{/if}
  </div>

  {* -------------------------------------------------------------------------------- *}

  {* -------------------------------------------------------------------------------- *}
  {* Build notes show/hide management                                                 *}
  {* -------------------------------------------------------------------------------- *}
  {lang_get s='builds_notes' var='container_title'}
  {assign var="div_id" value='build_notes'}
  {assign var="memstatus_id" value=$build_notes_view_memory_id}

  {include file="inc_show_hide_mgmt.tpl"
           show_hide_container_title=$container_title
           show_hide_container_id=$div_id
           show_hide_container_view_status_id=$memstatus_id
           show_hide_container_draw=true
           show_hide_container_class='exec_additional_info'
           show_hide_container_html=$gui->build_notes}
  {* -------------------------------------------------------------------------------- *}



  {if $gui->map_last_exec eq ""}
     <div class="warning_message" style="text-align:center"> {$labels.no_data_available}</div>
  {else}
      {if $gui->grants->execute == 1 and $gui->build_is_open == 1}
        {assign var="input_enabled_disabled" value=""}
        {assign var="att_download_only" value=false}
        {assign var="enable_custom_fields" value=true}
        {assign var="draw_submit_button" value=true}


        {if $cfg->exec_cfg->show_testsuite_contents}
            {lang_get s='bulk_tc_status_management' var='container_title'}
            {assign var="div_id" value='bulk_controls'}
            {assign var="memstatus_id" value=$bulk_controls_view_memory_id}
            
            {include file="inc_show_hide_mgmt.tpl"
                     show_hide_container_title=$container_title
                     show_hide_container_id=$div_id
                     show_hide_container_draw=false
                     show_hide_container_class='exec_additional_info'
                     show_hide_container_view_status_id=$memstatus_id}
            
            <div id="{$div_id}" name="{$div_id}">
            	{foreach key=verbose_status item=locale_status from=$gsmarty_tc_status_for_ui}
            	   <input type="button" id="btn_{$verbose_status}" name="btn_{$verbose_status}"
            	          value="{lang_get s='set_all_tc_to'} {lang_get s=$locale_status}"
            	          onclick="javascript:check_all_radios('{$gsmarty_tc_status.$verbose_status}');" />
            	{/foreach}
              <br />
              <br />
      	    	  <input type="submit" id="do_bulk_save" name="do_bulk_save"
      	    	         value="{$labels.btn_save_all_tests_results}"/>
            </div>
        {/if}
    	{/if}

      <hr />
      <div class="groupBtn">
    		  <input type="button" name="print" value="{$labels.btn_print}"
    		         onclick="javascript:window.print();" />
    		  <input type="submit" id="toggle_history_on_off"
    		         name="{$gui->history_status_btn_name}"
    		         value="{lang_get s=$gui->history_status_btn_name}" />
    		  <input type="button" id="pop_up_import_button" name="import_xml_button"
    		         value="{$labels.import_xml_results}"
    		         onclick="javascript: openImportResult(import_xml_results);" />

          {* 20081125 - franciscom - BUGID 1902*}
		      {if $tlCfg->exec_cfg->enable_test_automation }
		      <input type="submit" id="execute_cases" name="execute_cases"
		             value="{$labels.execute_and_save_results}"/>
		      {/if}
    		  <input type="hidden" id="history_on"
    		         name="history_on" value="{$gui->history_on}" />
      </div>
    <hr />

	{/if}

 	{foreach item=tc_exec from=$gui->map_last_exec}

    {assign var="tc_id" value=$tc_exec.testcase_id}
	  {assign var="tcversion_id" value=$tc_exec.id}
	  {* IMPORTANT:
	               Here we use version_number, which is related to tcversion_id SPECIFICATION.
	               When we need to display executed version number, we use tcversion_number
	  *}
	  {assign var="version_number" value=$tc_exec.version}
	  
		<input type='hidden' name='tc_version[{$tcversion_id}]' value='{$tc_id}' />
		<input type='hidden' name='version_number[{$tcversion_id}]' value='{$version_number}' />

    {* ------------------------------------------------------------------------------------ *}
    {lang_get s='th_testsuite' var='container_title'}
    {assign var="div_id" value=tsdetails_$tc_id}
    {assign var="memstatus_id" value=tsdetails_view_status_$tc_id}
    {assign var="ts_name"  value=$tsuite_info[$tc_id].tsuite_name}
    {assign var="container_title" value="$container_title$title_sep$ts_name"}

    {include file="inc_show_hide_mgmt.tpl"
             show_hide_container_title=$container_title
             show_hide_container_id=$div_id
             show_hide_container_draw=false
             show_hide_container_class='exec_additional_info'
             show_hide_container_view_status_id=$memstatus_id}

		<div id="{$div_id}" name="{$div_id}" class="exec_additional_info">
      <br />
      <div class="exec_testsuite_details" style="width:95%;">
      <span class="legend_container">{$labels.details}</span><br />
		  {$tsuite_info[$tc_id].details}
		  </div>

		  {if $ts_cf_smarty[$tc_id] neq ''}
		    <br />
		    <div class="custom_field_container" style="border-color:black;width:95%;">
         {$ts_cf_smarty[$tc_id]}
        </div>
		  {/if}

  		{if $gui->tSuiteAttachments != null && $gui->tSuiteAttachments[$tc_exec.tsuite_id] != null}
  		  <br />
		    {include file="inc_attachments.tpl" 
		             attach_tableName="nodes_hierarchy" 
		             attach_downloadOnly=true
			        	 attach_attachmentInfos=$gui->tSuiteAttachments[$tc_exec.tsuite_id]
			        	 attach_inheritStyle=1
			        	 attach_tableClassName="none"
				         attach_tableStyles="background-color:#ffffcc;width:100%" }
	    {/if}
	    <br />
    </div>


		<div class="exec_tc_title">
		{* 20080126 - franciscom - external id - $tc_exec.testcase_id *}
		{$labels.title_test_case} {$labels.th_test_case_id}{$gui->tcasePrefix|escape}{$tc_exec.tc_external_id|escape} :: {$labels.version}: {$tc_exec.version}<br />
		    {$tc_exec.name|escape}<br />
		    {if $tc_exec.assigned_user eq ''}
		      {$labels.has_no_assignment}
		    {else}
          {$labels.assigned_to}{$title_sep}{$tc_exec.assigned_user|escape}
        {/if}
    </div>

 		{if $cfg->exec_cfg->show_last_exec_any_build}
   		{assign var="abs_last_exec" value=$map_last_exec_any_build.$tcversion_id}
 		  {assign var="my_build_name" value=$abs_last_exec.build_name|escape}
 		  {assign var="show_current_build" value=1}
    {/if}
    {assign var="exec_build_title" value="$build_title $title_sep $my_build_name"}


		<div id="execution_history" class="exec_history">
  		<div class="exec_history_title">
  		{if $gui->history_on}
  		    {$labels.execution_history} {$title_sep_type3}
  		    {if !$cfg->exec_cfg->show_history_all_builds}
  		      {$exec_build_title}
  		    {/if}
  		{else}
  			  {$labels.last_execution}
  			  {if $show_current_build} {$labels.exec_any_build} {/if}
  			  {$title_sep_type3} {$exec_build_title}
  		{/if}
  		</div>

		{* The very last execution for any build of this test plan *}
		{if $cfg->exec_cfg->show_last_exec_any_build && $gui->history_on==0}
        {if $abs_last_exec.status != '' and $abs_last_exec.status != $gsmarty_tc_status.not_run}
			    {assign var="status_code" value=$abs_last_exec.status}

     			<div class="{$gsmarty_tc_status_css.$status_code}">
     			{$labels.date_time_run} {$title_sep} {localize_timestamp ts=$abs_last_exec.execution_ts}
     			{$title_sep_type3}
     			{$labels.test_exec_by} {$title_sep} {$users[$abs_last_exec.tester_id]->getDisplayName()|escape}
     			{$title_sep_type3}
     			{$labels.build}{$title_sep} {$abs_last_exec.build_name|escape}
     			{$title_sep_type3}
     			{$labels.exec_status} {$title_sep} {localize_tc_status s=$status_code}
     			</div>

  		  {else}
    		   <div class="not_run">{$labels.test_status_not_run}</div>
    			   {$labels.tc_not_tested_yet}
   		  {/if}
    {/if}

    {* -------------------------------------------------------------------------------------------------- *}
    {if $gui->other_execs.$tcversion_id}
      {if $gui->history_on == 0 && $show_current_build}
   		   <div class="exec_history_title">
  			    {$labels.last_execution} {$labels.exec_current_build}
  			    {$title_sep_type3} {$exec_build_title}
  			 </div>
		  {/if}

		  <table cellspacing="0" class="exec_history">
			 <tr>
				<th style="text-align:left">{$labels.date_time_run}</th>
				{if $gui->history_on == 0 || $cfg->exec_cfg->show_history_all_builds}
				  <th style="text-align:left">{$labels.build}</th>
				{/if}
				<th style="text-align:left">{$labels.test_exec_by}</th>
				<th style="text-align:center">{$labels.exec_status}</th>
				<th style="text-align:center">{$labels.testcaseversion}</th>

				{if $attachment_model->show_upload_column && !$att_download_only}
						<th style="text-align:center">{$labels.attachment_mgmt}</th>
            {assign var="my_colspan" value=$attachment_model->num_cols}
        {/if}

				{if $g_bugInterfaceOn}
          <th style="text-align:left">{$labels.bug_mgmt}</th>
          {assign var="my_colspan" value=$my_colspan+1}
        {/if}

				{if $gui->grants->delete_execution}
          <th style="text-align:left">{$labels.delete}</th>
          {assign var="my_colspan" value=$my_colspan+1}
        {/if}

        <th style="text-align:left">{$labels.run_mode}</th>
        {assign var="my_colspan" value=$my_colspan+1}

			 </tr>

			{* ----------------------------------------------------------------------------------- *}
			{foreach item=tc_old_exec from=$gui->other_execs.$tcversion_id}
  	     {assign var="tc_status_code" value=$tc_old_exec.status}

   			<tr style="border-top:1px solid black; background-color:{cycle values='#eeeeee,#d0d0d0'}">
  				<td>{localize_timestamp ts=$tc_old_exec.execution_ts}</td>

				  {if $gui->history_on == 0 || $cfg->exec_cfg->show_history_all_builds}
  				<td>{if !$tc_old_exec.build_is_open}
  				    <img src="{$smarty.const.TL_THEME_IMG_DIR}/lock.png" title="{$labels.closed_build}">{/if}
  				    {$tc_old_exec.build_name|escape}
  				</td>
  				{/if}

  				<td>{$users[$tc_old_exec.tester_id]->getDisplayName()|escape}</td>
  				<td class="{$gsmarty_tc_status_css.$tc_status_code}" style="text-align:center">
  				    {localize_tc_status s=$tc_old_exec.status}
  				</td>
  				
  		   {* IMPORTANT:
	               Here we use tcversion_number because we want to display
	               version number used when this execution was recorded.
      	  *}

  				<td  style="text-align:center">{$tc_old_exec.tcversion_number}</td>

          {if $attachment_model->show_upload_column && !$att_download_only && $tc_old_exec.build_is_open}
      			  <td align="center"><a href="javascript:openFileUploadWindow({$tc_old_exec.execution_id},'executions')">
      			    <img src="{$smarty.const.TL_THEME_IMG_DIR}/upload_16.png" title="{$labels.alt_attachment_mgmt}"
      			         alt="{$labels.alt_attachment_mgmt}"
      			         style="border:none" /></a>
              </td>
  	      {/if}

    			{if $g_bugInterfaceOn}
       		  	<td align="center"><a href="javascript:open_bug_add_window({$tc_old_exec.execution_id})">
      			    <img src="{$smarty.const.TL_THEME_IMG_DIR}/bug1.gif" title="{$labels.img_title_bug_mgmt}"
      			         style="border:none" /></a>
              </td>
          {/if}


    			{if $gui->grants->delete_execution}
       		  	<td align="center">
             	<a href="javascript:confirm_and_submit(msg,'execSetResults','exec_to_delete',
             	                                       {$tc_old_exec.execution_id},'do_delete',1);">
      			    <img src="{$smarty.const.TL_THEME_IMG_DIR}/trash.png" title="{$labels.img_title_delete_execution}"
      			         style="border:none" /></a>
              </td>
          {/if}

       		<td class="icon_cell" align="center">
       		  {if $tc_old_exec.execution_type == $smarty.const.TESTCASE_EXECUTION_TYPE_MANUAL}
      		    <img src="{$smarty.const.TL_THEME_IMG_DIR}/user.png" title="{$labels.execution_type_manual}"
      		            style="border:none" />
       		  {else}
      		    <img src="{$smarty.const.TL_THEME_IMG_DIR}/bullet_wrench.png" title="{$labels.execution_type_auto}"
      		            style="border:none" />
       		  {/if}
          </td>



  			</tr>
 			  {if $tc_old_exec.execution_notes neq ""}
  			<script>
        {literal}
        Ext.onReady(function(){
		    var p = new Ext.Panel({
        title: {/literal}'{$labels.exec_notes}'{literal},
        collapsible:true,
        collapsed: true,
        baseCls: 'x-tl-panel',
        renderTo: {/literal}'exec_notes_container_{$tc_old_exec.execution_id}'{literal},
        width:'100%',
        html:''
        });

        p.on({'expand' : function(){load_notes(this,{/literal}{$tc_old_exec.execution_id}{literal});}});
        });
        {/literal}

  			</script>
  			<tr>
  			 <td colspan="{$my_colspan}" id="exec_notes_container_{$tc_old_exec.execution_id}"
  			     style="padding:5px 5px 5px 5px;">
  			</td>
   			</tr>
 			  {/if}

  			{* 20080322 - franciscom - edit execution notes *}
  			{if $gui->grants->edit_exec_notes }
  			<tr>
  			<td colspan="{$my_colspan}">
  		    <img src="{$smarty.const.TL_THEME_IMG_DIR}/note_edit.png" title="{$labels.edit_notes}"
  		         onclick="javascript: openExecNotesWindow({$tc_old_exec.execution_id});">
  			</td>
  			</tr>
 			  {/if}

  			{* 20070105 - Custom field values  *}
  			<tr>
  			<td colspan="{$my_colspan}">
  				{assign var="execID" value=$tc_old_exec.execution_id}
  				{assign var="cf_value_info" value=$gui->other_exec_cfields[$execID]}
          {$cf_value_info}
  			</td>
  			</tr>



  			{* Attachments *}
  			<tr>
  			<td colspan="{$my_colspan}">
  				{assign var="execID" value=$tc_old_exec.execution_id}

  				{assign var="attach_info" value=$gui->attachments[$execID]}
  				{include file="inc_attachments.tpl"
  				         attach_attachmentInfos=$attach_info
  				         attach_id=$execID 
  				         attach_tableName="executions"
  				         attach_show_upload_btn=$attachment_model->show_upload_btn
  				         attach_show_title=$attachment_model->show_title
  				         attach_downloadOnly=$att_download_only 
  				         attach_tableClassName=null
                   attach_inheritStyle=0
                   attach_tableStyles=null}
  			</td>
  			</tr>

        {* Execution Bugs (if any) *}
        {if $gui->bugs[$execID] neq ""}
   		<tr>
   			<td colspan="{$my_colspan}">
   				{include file="inc_show_bug_table.tpl"
   			         bugs_map=$gui->bugs[$execID]
   			         can_delete=true
   			         exec_id=$execID}
   			</td>
   		</tr>
   		{/if}
		{/foreach}
			{* ----------------------------------------------------------------------------------- *}

			</table>
		{/if}
  </div>

  <br />

  {* ----------------------------------------------------------------------------------- *}
  <div>
    {include file="execute/inc_exec_test_spec.tpl"
             args_tc_exec=$tc_exec
             args_labels=$labels
             args_enable_custom_field=$enable_custom_fields
             args_execution_time_cf=$gui->execution_time_cfields
             args_design_time_cf=$gui->design_time_cfields
             args_execution_types=$gui->execution_types
             args_tcAttachments=$gui->tcAttachments }


    {if $tc_exec.can_be_executed}
      {include file="execute/inc_exec_controls.tpl"
               args_input_enable_mgmt=$input_enabled_disabled
               args_tcversion_id=$tcversion_id
               args_webeditor=$gui->exec_notes_editors[$tc_id]
               args_labels=$labels}
	  {/if}
 	  {if $tc_exec.active eq 0}
 	   <h1 class="title"><center>{$labels.testcase_version_is_inactive_on_exec}</center></h1>
 	  {/if}
	<hr />
	</div>
  {* ----------------------------------------------------------------------------------- *}

	{/foreach}

</form>
</div>
</body>
</html>
