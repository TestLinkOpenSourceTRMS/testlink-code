{* 
Testlink Open Source Project - http://testlink.sourceforge.net/ 
@filesource inc_show_scripts_table.tpl

@internal revisions
*}

{* -------------------------------------------------------------------------------------- *}
{* Manage missing arguments                                                               *}
{if !isset($tableClassName) }
    {$tableClassName="simple"}
{/if}
{if !isset($tableStyles) }
    {$tableStyles="font-size:12px"}
{/if}
{* -------------------------------------------------------------------------------------- *}
{lang_get var="l10nb"
          s="build,test_case_version,script_id,caption_scripttable,delete_script,
             del_script_warning_msg,cts_project_key,cts_repo_name,cts_branch_name"}

{$item_id = $tcase_id}

<table class="simple">
  <tr>
    <th style="text-align:left">{$l10nb.caption_scripttable}</th>
    <th style="text-align:left">{$l10nb.cts_project_key}</th>
    <th style="text-align:left">{$l10nb.cts_repo_name}</th>
    <th style="text-align:left">{$l10nb.cts_branch_name}</th>
    {if $can_delete} <th style="text-align:left">&nbsp;</th> {/if}
  </tr>
  
  {foreach from=$scripts_map key=script_id item=script_elem}
    <tr>
      <td>{$script_elem.link_to_cts}</td>
      <td>{$script_elem.project_key}</td>
      <td>{$script_elem.repository_name}</td>
      <td>{$script_elem.branch_name}</td>

      {if $can_delete}
        <td class="clickable_icon">
          <img class="clickable" onclick="delete_confirmation('{$tproject_id}:{$item_id}-{$script_id|escape:'javascript'|escape}','{$script_id|escape:'javascript'|escape}',
           '{$l10nb.delete_script}','{$l10nb.del_script_warning_msg} ({$l10nb.script_id} {$script_id})',deleteScript);" style="border:none" title="{$l10nb.delete_script}" alt="{$l10nb.delete_script}" 
            src="{$tlImages.delete}"/></td>
      {/if}
    </tr>
  {/foreach}
</table>

