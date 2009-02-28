{*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: tcView_viewer.tpl,v 1.25 2009/02/28 17:18:06 franciscom Exp $
viewer for test case in test specification

rev: 20090215 - franciscom - BUGID - show info about links to test plans
*}

{lang_get var="labels"
          s="requirement_spec,Requirements,tcversion_is_inactive_msg,
             btn_edit,btn_del,btn_mv_cp,btn_del_this_version,btn_new_version,
             btn_export,btn_execute_automatic_testcase,version,testplan_usage,
             testproject,testsuite,title_test_case,summary,steps,
             title_last_mod,title_created,by,expected_results,keywords,
             execution_type,test_importance,none"}

             
{assign var="hrefReqSpecMgmt" value="lib/general/frmWorkArea.php?feature=reqSpecMgmt"}
{assign var="hrefReqSpecMgmt" value=$basehref$hrefReqSpecMgmt}

{assign var="hrefReqMgmt" value="lib/requirements/reqView.php?showReqSpecTitle=1&requirement_id="}
{assign var="hrefReqMgmt" value=$basehref$hrefReqMgmt}
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

{if $args_can_edit == "yes" }

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
       {assign var="has_been_executed" value=1}
       {lang_get s='warning_editing_executed_tc' var="warning_edit_msg"}
     {/if}
  {/if}


  <div class="groupBtn">

	<span style="float: left"><form method="post" action="lib/testcases/tcEdit.php">
	  <input type="hidden" name="testcase_id" value="{$args_testcase.testcase_id}" />
	  <input type="hidden" name="tcversion_id" value="{$args_testcase.id}" />
	  <input type="hidden" name="has_been_executed" value="{$has_been_executed}" />
	  <input type="hidden" name="doAction" value="" />


	    {assign var="go_newline" value=""}
	    {if $edit_enabled}
	 	    <input type="submit" name="edit_tc" 
	 	           onclick="doAction.value='edit'" value="{$labels.btn_edit}" />
	    {/if}
	
		{if $args_can_delete_testcase == "yes" }
			<input type="submit" name="delete_tc" value="{$labels.btn_del}" />
	    {/if}
	
	    {if $args_can_move_copy == "yes" }
	   		<input type="submit" name="move_copy_tc"   value="{$labels.btn_mv_cp}" />
	     	{assign var="go_newline" value="<br />"}
	    {/if}
	
	 	{if $args_can_delete_version == "yes" }
			 <input type="submit" name="delete_tc_version" value="{$labels.btn_del_this_version}" />
	    {/if}

   		<input type="submit" name="do_create_new_version"   value="{$labels.btn_new_version}" />
	
		{* --------------------------------------------------------------------------------------- *}
		{if $active_status_op_enabled eq 1}
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
	</form></span>

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
	</form></span>
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

	{if $args_cf neq ''}
	<div>
        <div id="cfields_design_time" class="custom_field_container">{$args_cf}</div>
	</div>
	{/if}

	<div>
		<table cellpadding="0" cellspacing="0" style="font-size:100%;">
			    <tr>
			     	<td width="35%" style="vertical-align:text-top;"><a href={$gsmarty_href_keywordsView}>{$labels.keywords}</a>: &nbsp;
						</td>
				 	  <td>
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