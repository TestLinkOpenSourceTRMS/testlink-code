{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource containerDelete.tpl
Purpose: smarty template - delete containers in test specification
*}
{include file="inc_head.tpl"}
{lang_get var='labels'
          s='test_case,th_link_exec_status,question_del_testsuite,
             btn_yes_del_comp,btn_no'}

<body>
<h1 class="title">{$page_title}{$smarty.const.TITLE_SEP}{$objectName|escape}</h1> 
{include file="inc_update.tpl" result=$sqlResult item=$level action='delete' refresh=$refreshTree}

<div class="workBack">

{if $sqlResult == '' && $objectID != ''}
  {if $warning != ""}
    {if $system_message != ""}
      <div class="user_feedback">{$system_message}</div>
      <br />
    {/if}
    <table class="link_and_exec">
    <tr>
      <th>{$labels.test_case}</th>
      <th>{$labels.th_link_exec_status}</th>
    </tr>
    {section name=idx loop=$warning}
      <tr>
        <td>{$warning[idx]|escape}&nbsp;</td>
        <td>{lang_get s=$link_msg[idx]}<td>
      </tr>
    {/section}
    </table>
    {if $delete_msg != ''}  
      <h2>{$delete_msg}</h2>
    {/if}
  {/if}
  
  <form method="post" action="{$basehref}lib/testcases/containerEdit.php?sure=yes&amp;objectID={$objectID}">
    {if $can_delete}
      <p>{$labels.question_del_testsuite}</p>
      <input type="submit" name="delete_testsuite" value="{$labels.btn_yes_del_comp}" />
    
      <input  type="button" name="cancel_delete_testsuite" value="{$labels.btn_no}"
          onclick='javascript: location.href=fRoot+
          "lib/testcases/archiveData.php?&amp;edit=testsuite&amp;id={$objectID}";' />
    {/if}
  </form>
{/if}

{if $refreshTree}
  {include file="inc_refreshTreeWithFilters.tpl"}
{/if}
</div>
</body>
</html>