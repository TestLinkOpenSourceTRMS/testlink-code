{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: tcDelete.tpl,v 1.9 2006/12/31 18:20:49 franciscom Exp $
Purpose: smarty template - delete test case in test specification

*}

{include file="inc_head.tpl"}

<body>
<h1>{lang_get s='test_case'}{$gsmarty_title_sep}{$testcase_name|escape}</h1> 

<div class="workBack">
<h1>{$title}</h1> 

{include file="inc_update.tpl" result=$sqlResult action=$action item="test case" refresh=$refresh_tree}

{if $sqlResult == ''}
  <p>
  {if $exec_status_quo neq ''}
    <table class="link_and_exec" >
    <th>{lang_get s='th_version'}</th>
    <th>{lang_get s='th_linked_to_tplan'}</th> 
    <th>{lang_get s='th_executed'}</th> 
  	{foreach key=tcversion_id item=on_tplan_status from=$exec_status_quo}
      {foreach key=tplan_id item=status from=$on_tplan_status}
      <tr>
      <td align="right">{$status.version}</td>
      <td align="right">{$status.tplan_name}</td>
      <td align="center">{if $status.executed neq ""}<img src="icons/apply_f2_16.png">{/if}</td>
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
	</form>
{/if}

</div>
</body>
</html>