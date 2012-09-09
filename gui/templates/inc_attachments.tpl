{*
Testlink Open Source Project - http://testlink.sourceforge.net/
@filesource inc_attachments.tpl
Generic attachment management

@internal revisions
@since 2.0
Input:
      $attach->attachmentsInfos
      $attach->itemID
      $attach->dbTable
      $attach->gui->tableStyles    
      $attach->gui->tableClassName
      $attach->gui->inheritStyle
      $attach->gui->loadOnCancelURL
      $attach->gui->labels
      $attach->gui->display
      $attach->gui->showUploadBtn
      $attach->gui->downloadOnly
      $attach->gui->uploadEnabled

*}
{$del_msgbox_title = $attach->gui->labels.delete|escape:'javascript'|escape}
{$warning_msg = $attach->gui->labels.warning_delete_attachment|escape:'javascript'|escape}

<script type="text/javascript">

var warning_delete_attachment = "{$attach->gui->labels.warning_delete_attachment}";
{if isset($attach->gui->loadOnCancelURL)}
  var attachment_reloadOnCancelURL = '{$attach->gui->loadOnCancelURL}';
{/if} 
</script>

{if $attach->enabled eq FALSE}
    <div class="messages">{$attach->gui->labels.attachment_feature_disabled}<p>
    {$attach->gui->disabled_msg}
    </div>
{/if}
{include file="inc_action_onclick.tpl"}
{if $attach->enabled && ($attach->attachmentInfos != "" || $attach->gui->showUploadBtn)}

<table class="{$attach_tableClassName}" {if $attach->gui->inheritStyle == 0} style="{$attach->gui->tableStyles}" {/if}>

  {if $attach_showTitle}
  <tr>
    <td class="bold">{$attach->gui->labels.attached_files}{$tlCfg->gui_title_separator_1}</td>
  </tr>
  {/if}

  {foreach from=$attach->attachmentInfos item=info}
    {if $info.title == ""}
      {$my_link = $attach->gui->accessLink}
    {else}
      {$my_link = $info.title|escape}
    {/if}

      <tr>
      <td style="vertical-align:middle;"><a href="lib/attachments/attachmentdownload.php?id={$info.id}" 
          target="_blank" class="bold">
      {$my_link}</a> - <span class="italic">{$info.file_name|escape} ({$info.file_size|escape} bytes, 
      {$info.file_type|escape}) {localize_date d=$info.date_added|escape}</span>
        {if !$attach_downloadOnly}
        <a href="javascript:action_confirmation({$info.id},'{$info.file_name|escape:'javascript'|escape}',
                                                '{$del_msgbox_title|escape:'javascript'|escape}',
                                                '{$warning_msg|escape:'javascript'|escape}',
                                                deleteAttachment_onClick);">
          <img style="border:none;" alt="{$attach->gui->labels.alt_delete_attachment}"
                                    title="{$attach->gui->labels.alt_delete_attachment}"
                                    src="{$tlImages.delete}" /></a>
        {/if}
      </td>
    </tr>
  {/foreach}

  {if $attach->showUploadBtn && !$attach->downloadOnly}
  <tr>
    <td colspan="2">
    <input type="button" value="{$attach->gui->labels.upload_file_new_file}" 
           onclick="openFileUploadWindow({$attach->itemID},'{$attach->dbTable}')" /></td>
  </tr>
  {/if}

</table>
{/if}