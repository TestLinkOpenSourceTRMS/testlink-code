{* 
	TestLink Open Source Project - http://testlink.sourceforge.net/
	$Id: containerDelete.tpl,v 1.10 2006/10/25 07:34:28 franciscom Exp $ 
	Purpose: smarty template - delete containers in test specification
*}
{include file="inc_head.tpl"}

<body>

<div class="workBack">
{include file="inc_title.tpl" title="$page_title $objectName"}
{include file="inc_update.tpl" result=$sqlResult item=$level action='delete' refresh="yes"}

{if $sqlResult == '' && $objectID != ''}
  <br />
  {if $warning neq ""}
    <table class="link_and_exec">
    <th>{lang_get s='test_case'}</th>
    <th>{lang_get s='th_link_exec_status'}</th>
     {section name=idx loop=$warning}
  		 <tr><td>{$warning[idx]}&nbsp;</td> <td>{lang_get s=$link_msg[idx]}<td></tr>
     {/section}
    </table>
    {if $delete_msg neq ''}  
 	  <h2>{$delete_msg}</h2>
    {/if}
  {/if}
  

	<p>{lang_get s='question_del'} {$level|escape}?</p>

	<form method="post" 
	      action="lib/testcases/containerEdit.php?sure=yes&amp;objectID={$objectID|escape}">
		<input type="submit" name="delete_testsuite" value="{lang_get s='btn_yes_del_comp'}" />
	</form>
{/if}

{if $refreshTree}
   {include file="inc_refreshTree.tpl"}
{/if}

</div>
</body>
</html>