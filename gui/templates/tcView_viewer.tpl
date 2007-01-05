{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: tcView_viewer.tpl,v 1.15 2007/01/05 13:57:30 franciscom Exp $
viewer for test case in test specification

20061230 - franciscom - an experiment to make simple management
                        of frequent used href
                        
20060427 - franciscom - added font-size in the table used for keywords
*}

{if $args_show_title == "yes"}
<h1>{lang_get s='title_test_case'} {$args_testcase.name|escape} </h1>
{/if}

{if $args_can_edit == "yes" }
	<div class="groupBtn">
	<form method="post" action="lib/testcases/tcEdit.php">
	  <input type="hidden" name="testcase_id" value="{$args_testcase.testcase_id}" />
	  <input type="hidden" name="tcversion_id" value="{$args_testcase.id}" />

    {if $args_status_quo eq null or $args_status_quo[$args_testcase.id].executed eq null}
 	    <input type="submit" name="edit_tc"   value="{lang_get s='btn_edit'}" />
      {assign var="warning_edit_msg" value=""}
      
    {else}
      {lang_get s='can_not_edit_tc' var="warning_edit_msg"}
    {/if}

	{if $args_can_delete_testcase == "yes" }
		<input type="submit" name="delete_tc" value="{lang_get s='btn_del'}" />
    {/if}

    {if $args_can_move_copy == "yes" }
   		<input type="submit" name="move_copy_tc"   value="{lang_get s='btn_mv_cp'}" />
    {/if}		                     
	<br />
     <br />
 	{if $args_can_delete_version == "yes" }
		<input type="submit" name="delete_tc_version" value="{lang_get s='btn_del_this_version'}" />
    {/if}
	<input type="submit" name="do_create_new_version"   value="{lang_get s='btn_new_version'}" />
	
	 {* --------------------------------------------------------------------------------------- *} 
	 {if $args_status_quo eq null or $args_status_quo[$args_testcase.id].executed eq null}
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
	 {* --------------------------------------------------------------------------------------- *} 
	
	</form>
	<form method="post" action="lib/testcases/tcexport.php">
		<br/>
		<input type="hidden" name="testcase_id" value="{$args_testcase.testcase_id}" />
		<input type="hidden" name="tcversion_id" value="{$args_testcase.id}" />
		<input type="submit" name="export_tc"   value="{lang_get s='btn_export'}" />
	</form>

  {if $warning_edit_msg neq ""}
    <p><div class="warning_message" align="center">{$warning_edit_msg}</div>
  {/if}
	</div>	
{/if}

  {if $args_testcase.active eq 0}
    <p><div class="warning_message" align="center">{lang_get s='tcversion_is_inactive_msg'}</div>
  {/if}

	<table width="95%" class="simple" border="0">
    {if $args_show_title == "yes"}
		<tr>
			<th  colspan="2">
			{lang_get s='th_test_case_id'}{$args_testcase.testcase_id} :: 
			{lang_get s='title_test_case'} {$args_testcase.name|escape}</th>
		</tr>
    {/if} 
    
    {if $args_show_version == "yes"}
		<tr>
			<td class="bold" colspan="2">{lang_get s='version'} 
			{$args_testcase.version|escape}</td>
		</tr>
		{/if}
		
		<tr>
			<td class="bold" colspan="2">{lang_get s='summary'}</td>
		</tr>
		<tr>
			<td colspan="2">{$args_testcase.summary}</td>
		</tr>
		<tr>
			<td class="bold" width="50%">{lang_get s='steps'}</td>
			<td class="bold" width="50%">{lang_get s='expected_results'}</td>
		</tr>
		<tr>
			<td>{$args_testcase.steps}</td>
			<td>{$args_testcase.expected_results}</td>
		</tr>
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>

		<tr> {* 20070104 - franciscom *}
			<td colspan="2">{if $args_cf neq ''}
			                 <div class="custom_field_container">{$args_cf}</div>
			                {else}
			                   &nbsp;
			                {/if} 
			 </td>
		</tr>

		<tr>
		  	<td colspan="2">
				<table cellpadding="0" cellspacing="0" style="font-size:100%;">
			    <tr>
			      {* 20061230 - franciscom *}
						<td width="35%"><a href={$gsmarty_href_keywordsView}>{lang_get s='keywords'}</a>: &nbsp;
						</td>
				 	  <td>
					  	{foreach item=keyword_item from=$args_keywords_map}
						    {$keyword_item|escape}
						    <br />
						{/foreach}
					</td>
				</tr>
				</table>	
			</td>
		</tr>
		
		
	{if $opt_requirements == TRUE && $view_req_rights == "yes"}
		<tr>
		  	<td colspan="2">
  				<table cellpadding="0" cellspacing="0" style="font-size:100%;">
     			  <tr>
       			  <td colspan="2"><span><a href="lib/req/reqSpecList.php" 
      				target="mainframe" class="bold">{lang_get s='Requirements'}</a>
      				: &nbsp;</span>
      			  </td>
      			  <td>
      				{section name=item loop=$args_reqs}
      					<span onclick="javascript: open_top(fRoot+'lib/req/reqView.php?idReq={$args_reqs[item].id}');"
      					style="cursor:  pointer;">{$args_reqs[item].title|escape}</span>
      					{if !$smarty.section.item.last}<br>{/if}
      				{sectionelse}
      					{lang_get s='none'}
      				{/section}
      			  </td>
    		    </tr>
    		  </table>
    		</td>    	  
		</tr>
	{/if}

  <tr>
  <td colspan="2">
  &nbsp;
  </td>
  </tr>
  <tr class="time_stamp_creation">
  <td colspan="2">
      {lang_get s='title_created'}&nbsp;{localize_timestamp ts=$args_testcase.creation_ts }&nbsp;
			{lang_get s='by'}&nbsp;{$args_testcase.author_first_name|escape}&nbsp;{$args_testcase.author_last_name|escape}
  </td>
  </tr>
  {if $args_testcase.updater_last_name ne "" || $args_testcase.updater_first_name ne ""}
    <tr class="time_stamp_creation">
    <td colspan="2">
    {lang_get s='title_last_mod'}&nbsp;{localize_timestamp ts=$args_testcase.modification_ts}
		  &nbsp;{lang_get s='by'}&nbsp;{$args_testcase.updater_first_name|escape}
		                       &nbsp;{$args_testcase.updater_last_name|escape}
    </td>
    </tr>
  {/if}
	</table>
	