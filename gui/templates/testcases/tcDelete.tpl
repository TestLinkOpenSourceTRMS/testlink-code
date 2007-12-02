{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: tcDelete.tpl,v 1.1 2007/12/02 17:03:58 franciscom Exp $
Purpose: smarty template - delete test case in test specification

rev : 
      20070502 - franciscom - solved problems on delete due to name of local variable
                              equal to name of variable assigned on php page.
                              
      20070213 - franciscom - BUGID 0000629: Test Case/Suite - Delete confirmation without Cancel or No option

*}

{include file="inc_head.tpl"}

<body>
<h1>{lang_get s='test_case'}{$smarty.const.TITLE_SEP}{$testcase_name|escape}</h1> 

<div class="workBack">
<h1>{$title}</h1> 

{include file="inc_update.tpl" result=$sqlResult action=$action item="test case" 
         refresh=$smarty.session.tcspec_refresh_on_action}

{if $sqlResult == ''}
  <p>
  {if $exec_status_quo neq ''}
    <table class="link_and_exec" >
    <th>{lang_get s='th_version'}</th>
    <th>{lang_get s='th_linked_to_tplan'}</th> 
    <th>{lang_get s='th_executed'}</th> 
  	{foreach key=testcase_version_id item=on_tplan_status from=$exec_status_quo}
      {foreach key=tplan_id item=status from=$on_tplan_status}
      <tr>
      <td align="right">{$status.version}</td>
      <td align="right">{$status.tplan_name}</td>
      <td align="center">{if $status.executed neq ""}<img src="{$smarty.const.TL_THEME_IMG_DIR}/apply_f2_16.png">{/if}</td>
      </tr>
      {/foreach}  
  	{/foreach}
    </table>
    
    {$delete_message}
  {/if}

	<p>{lang_get s='question_del_tc'}</p>
	<form method="post" action="lib/testcases/tcEdit.php">
	  <input type="hidden" name="testcase_id" value="{$testcase_id}">
	  <input type="hidden" name="tcversion_id" value="{$tcversion_id}">
		<input type="submit" id="do_delete" name="do_delete" value="{lang_get s='btn_yes_iw2del'}" />

		{* 20070213 - franciscom - BUGID 0000629 *}
		<input type="button" name="cancel_delete" 
		                     onclick='javascript:history.go(-1);'
		                     value="{lang_get s='btn_no'}" />

	</form>
{/if}

</div>
</body>
</html>