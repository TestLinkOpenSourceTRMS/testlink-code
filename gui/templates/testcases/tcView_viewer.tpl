{*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: tcView_viewer.tpl,v 1.31 2009/09/01 07:31:29 franciscom Exp $
viewer for test case in test specification

rev:
    20090831 - franciscom - preconditions
    20090418 - franciscom - BUGID 2364 - added fine grain control of button enable/disable
    20090414 - franciscom - BUGID 2378 - check for active test plan existence to display btn_add_to_testplan
    20090308 - franciscom - added logic to display button that allow assign test case version 
                            to test plans. 
    20090215 - franciscom - BUGID - show info about links to test plans
*}

{lang_get var="labels"
          s="requirement_spec,Requirements,tcversion_is_inactive_msg,
             btn_edit,btn_del,btn_mv_cp,btn_del_this_version,btn_new_version,
             btn_export,btn_execute_automatic_testcase,version,testplan_usage,
             testproject,testsuite,title_test_case,summary,steps,btn_add_to_testplans,
             title_last_mod,title_created,by,expected_results,keywords,
             execution_type,test_importance,none,preconditions"}

             
{assign var="hrefReqSpecMgmt" value="lib/general/frmWorkArea.php?feature=reqSpecMgmt"}
{assign var="hrefReqSpecMgmt" value=$basehref$hrefReqSpecMgmt}

{assign var="hrefReqMgmt" value="lib/requirements/reqView.php?showReqSpecTitle=1&requirement_id="}
{assign var="hrefReqMgmt" value=$basehref$hrefReqMgmt}

{assign var="module" value='lib/testcases/'}
{assign var="tcase_id" value=$args_testcase.testcase_id}
{assign var="tcversion_id" value=$args_testcase.id}
{assign var="url_args" value="tcAssign2Tplan.php?tcase_id=$tcase_id&tcversion_id=$tcversion_id"}
{assign var="hrefAddTc2Tplan"  value="$basehref$module$url_args"}

{assign var="author_userinfo" value=$args_users[$args_testcase.author_id]}
{assign var="updater_userinfo" value=""}
{if $args_testcase.updater_id != ''}
  {assign var="updater_userinfo" value=$args_users[$args_testcase.updater_id]}
{/if}

{if $args_show_title == "yes"}
    {if $args_tproject_name != ''}
     <h2>{$labels.testproject} {$args_tproject_name|escape} </h2>
    {/if}
    {if $args_tsuite_name != ''}
     <h2>{$labels.testsuite} {$args_tsuite_name|escape} </h2>
    {/if}
	  <h2>{$labels.title_test_case} {$args_testcase.name|escape} </h2>
{/if}
{assign var="warning_edit_msg" value=""}

{if $args_can_do->edit == "yes" }

  {assign var="edit_enabled" value=0}
  {* 20070628 - franciscom - Seems logical you can disable some you have executed before *}
  {assign var="active_status_op_enabled" value=1}
  {assign var="has_been_executed" value=0}
  {lang_get s='can_not_edit_tc' var="warning_edit_msg"}
  {if $args_status_quo == null || $args_status_quo[$args_testcase.id].executed == null}
      {assign var="edit_enabled" value=1}
      {assign var="warning_edit_msg" value=""}
  {else} 
    {if isset($args_tcase_cfg) && $args_tcase_cfg->can_edit_executed eq 1}
      {assign var="edit_enabled" value=1} 
      {assign var="has_been_executed"  value=1} 
      {lang_get s='warning_editing_executed_tc' var="warning_edit_msg"}
    {/if} 
  {/if}


  <div class="groupBtn">

	<span style="float: left">
	  <form method="post" action="lib/testcases/tcEdit.php">
	  <input type="hidden" name="testcase_id" value="{$args_testcase.testcase_id}" />
	  <input type="hidden" name="tcversion_id" value="{$args_testcase.id}" />
	  <input type="hidden" name="has_been_executed" value="{$has_been_executed}" />
	  <input type="hidden" name="doAction" value="" />
	  <input type="hidden" name="show_mode" value="{$gui->show_mode}" />


	  {assign var="go_newline" value=""}
	  {if $edit_enabled}
	 	    <input type="submit" name="edit_tc" 
	 	           onclick="doAction.value='edit';{$gui->submitCode}" value="{$labels.btn_edit}" />
	  {/if}
	
	  {* Double condition because for test case versions WE DO NOT DISPLAY this
	     button, using $args_can_delete_testcase='no'
	  *}
		{if $args_can_do->delete_testcase == "yes" &&  $args_can_delete_testcase == "yes"}
			<input type="submit" name="delete_tc" value="{$labels.btn_del}" />
	  {/if}
	
	  {* Double condition because for test case versions WE DO NOT DISPLAY this
	     button, using $args_can_move_copy='no'
	  *}
	  {if $args_can_do->copy == "yes" && $args_can_move_copy == "yes" }
	   		<input type="submit" name="move_copy_tc"   value="{$labels.btn_mv_cp}" />
	     	{assign var="go_newline" value="<br />"}
	  {/if}
	
	 	{if $args_can_do->delete_version == "yes" && $args_can_delete_version == "yes"}
			 <input type="submit" name="delete_tc_version" value="{$labels.btn_del_this_version}" />
	  {/if}

	 	{if $args_can_do->create_new_version == "yes" }
  		<input type="submit" name="do_create_new_version"   value="{$labels.btn_new_version}" />
	  {/if}


	
		{* --------------------------------------------------------------------------------------- *}
		{if $active_status_op_enabled eq 1 && $args_can_do->deactivate=='yes'}
	        {if $args_testcase.active eq 0}
				      {assign var="act_deact_btn" value="activate_this_tcversion"}
				      {assign var="act_deact_value" value="activate_this_tcversion"}
				      {assign var="version_title_class" value="inactivate_version"}
	      	{else}
				      {assign var="act_deact_btn" value="deactivate_this_tcversion"}
				      {assign var="act_deact_value" value="deactivate_this_tcversion"}
				      {assign var="version_title_class" value="activate_version"}
	      	{/if}
	      	<input type="submit" name="{$act_deact_btn}"
	                           value="{lang_get s=$act_deact_value}" />
	  {/if}

  {* 20090306 - franciscom*}
  {if $args_can_do->add2tplan == "yes" && $args_has_testplans}
  <input type="button" id="addTc2Tplan_{$args_testcase.id}"  name="addTc2Tplan_{$args_testcase.id}" 
         value="{$labels.btn_add_to_testplans}" onclick="location='{$hrefAddTc2Tplan}'" />

  {/if}
	</form>
	</span>

	<span>
	<form method="post" action="lib/testcases/tcExport.php" name="tcexport">
		<input type="hidden" name="testcase_id" value="{$args_testcase.testcase_id}" />
		<input type="hidden" name="tcversion_id" value="{$args_testcase.id}" />
		<input type="submit" name="export_tc" style="margin-left: 3px;" value="{$labels.btn_export}" />
		{* 20071102 - franciscom *}
		{*
		<input type="button" name="tstButton" value="{$labels.btn_execute_automatic_testcase}"
		       onclick="javascript: startExecution({$args_testcase.testcase_id},'testcase');" />
		*}
	
	</form>
	</span>

  </div> {* class="groupBtn" *}

{/if} {* user can edit *}


{* --------------------------------------------------------------------------------------- *}
  {if $args_testcase.active eq 0}
    <br /><div class="messages" align="center">{$labels.tcversion_is_inactive_msg}</div>
  {/if}
 	{if $warning_edit_msg neq ""}
 	    <br /><div class="messages" align="center">{$warning_edit_msg}</div>
 	{/if}
 
<table class="simple">
    {if $args_show_title == "yes"}
	<tr>
		<th colspan="2">
		{$args_testcase.tc_external_id}{$smarty.const.TITLE_SEP}{$args_testcase.name|escape}</th>
	</tr>
    {/if}

  {if $args_show_version == "yes"}
	  <tr>
	  	<td class="bold" colspan="2">{$labels.version}
	  	{$args_testcase.version|escape}
	  	</td>
	  </tr>
	{/if}

	<tr class="time_stamp_creation">
  		<td width="50%">
      		{$labels.title_created}&nbsp;{localize_timestamp ts=$args_testcase.creation_ts }&nbsp;
      		{$labels.by}&nbsp;{$author_userinfo->getDisplayName()|escape}
  		</td>
  		{if $args_testcase.updater_last_name != "" || $args_testcase.updater_first_name != ""}
    	<td width="50%">
    		{$labels.title_last_mod}&nbsp;{localize_timestamp ts=$args_testcase.modification_ts}
		  	&nbsp;{$labels.by}&nbsp;{$updater_userinfo->getDisplayName()|escape}
    	</td>
  		{/if}
    </tr>

	<tr>
		<td class="bold" colspan="2">{$labels.summary}</td>
	</tr>
	<tr>
		<td colspan="2">{$args_testcase.summary}</td>
	</tr>

	<tr>
		<td class="bold" colspan="2">{$labels.preconditions}</td>
	</tr>
	<tr>
		<td colspan="2">{$args_testcase.preconditions}</td>
	</tr>

	{* 20090718 - franciscom *}
	{if $args_cf.before_steps_results neq ''}
	<tr>
	  <td>
    {$args_cf.before_steps_results}
    </td>
	</tr>
	{/if}
	<tr>
		<th width="50%">{$labels.steps}</th>
		<th width="50%">{$labels.expected_results}</th>
	</tr>
	<tr>
		<td>{$args_testcase.steps}</td>
		<td>{$args_testcase.expected_results}</td>
	</tr>
</table>
    {if $session['testprojectOptAutomation']}
    <div>
		<span class="labelHolder">{$labels.execution_type} {$smarty.const.TITLE_SEP}</span>
		{$execution_types[$args_testcase.execution_type]}
	</div>
	{/if}

    {if $session['testprojectOptPriority']}
    <div>
		<span class="labelHolder">{$labels.test_importance} {$smarty.const.TITLE_SEP}</span>
		{$gsmarty_option_importance[$args_testcase.importance]}
	</div>
	{/if}

  {* 20090718 - franciscom *}
	{if $args_cf.standard_location neq ''}
	<div>
        <div id="cfields_design_time" class="custom_field_container">{$args_cf.standard_location}</div>
	</div>
	{/if}

	<div>
		<table cellpadding="0" cellspacing="0" style="font-size:100%;">
			    <tr>
			     	<td width="35%" style="vertical-align:top;"><a href={$gsmarty_href_keywordsView}>{$labels.keywords}</a>: &nbsp;
					</td>
				 	<td style="vertical-align:top;">
				 	  	{foreach item=keyword_item from=$args_keywords_map}
						    {$keyword_item.keyword|escape}
						    <br />
	      				{foreachelse}
    	  					{$labels.none}
						{/foreach}
					</td>
				</tr>
				</table>
	</div>

	{if $opt_requirements == TRUE && $view_req_rights == "yes"}
	<div>
		<table cellpadding="0" cellspacing="0" style="font-size:100%;">
     			  <tr>
       			  <td colspan="2" style="vertical-align:text-top;"><span><a title="{$labels.requirement_spec}" href="{$hrefReqSpecMgmt}"
      				target="mainframe" class="bold">{$labels.Requirements}</a>
      				: &nbsp;</span>
      			  </td>
      			  <td>
      				{section name=item loop=$args_reqs}
      					<span onclick="javascript: open_top('{$hrefReqMgmt}{$args_reqs[item].id}');"
      					style="cursor:  pointer;">[{$args_reqs[item].req_spec_title|escape}]&nbsp;{$args_reqs[item].req_doc_id|escape}:{$args_reqs[item].title|escape}</span>
      					{if !$smarty.section.item.last}<br />{/if}
      				{sectionelse}
      					{$labels.none}
      				{/section}
      			  </td>
    		    </tr>
	  </table>
	</div>
	{/if}
	
	{if $args_linked_versions != null }
  <br />
	<div>
	  {$labels.testplan_usage}
		<table class="simple">
    <tr><th>Version </th> <th> Test Plan</th> </tr>
  	{foreach item=linked_item from=$args_linked_versions}
  	    {foreach item=tplan_item from=$linked_item}
        <tr>
            <td style="text-align:center;width:15%;">{$tplan_item.version|escape}</td>
            <td>{$tplan_item.tplan_name|escape}</td>
        </tr>
		    {/foreach}
		{/foreach}
	  </table>
	</div>
  {/if}	