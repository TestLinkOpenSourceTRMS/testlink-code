{*
Testlink Open Source Project - http://testlink.sourceforge.net/
@filesource attachments.inc. tpl

@internal revisions
@since 1.9.10

Generic attachment management

Input:
  $attach_attachmentsInfos
  $attach_id
  $attach_tableName
  $attach_show_upload_btn
  $attach_downloadOnly
  $attach_tableClassName
  $attach_inheritStyle
  $attach_tableStyles

  

Smarty global variables:
$gsmarty_attachments
*}

{lang_get var='labels'
          s='title_upload_attachment,enter_attachment_title,
             btn_upload_file,warning,attachment_title,alt_delete_attachment,click_to_get_attachment,
             display_inline,local_file,attachment_upload_ok,title_choose_local_file,btn_cancel,display_ea_string,
             max_size_file_upload,display_inline_string'}

{lang_get s='warning_delete_attachment' var="warning_msg"}
{lang_get s='delete' var="del_msgbox_title"}

<script type="text/javascript">
function checkFileSize()
{
  if (typeof FileReader !== "undefined") {
    var bytes = document.getElementById('uploadedFile').files[0].size;
    if( bytes > {$gui->import_limit} )
    {
      var msg = "{$labels.max_size_file_upload}: {$gui->import_limit} Bytes < " + bytes + ' Bytes';
      alert(msg);
      return false;
    }   
  }
  return true;
}  


var warning_delete_attachment = "{lang_get s='warning_delete_attachment'}";
{if isset($attach_loadOnCancelURL)}
  var attachment_reloadOnCancelURL = '{$attach_loadOnCancelURL}';
{/if} 
</script>

{if $gsmarty_attachments->enabled eq FALSE}
  <div class="messages">{lang_get s='attachment_feature_disabled'}<p>
  {$gsmarty_attachments->disabled_msg}
  </div>
{/if}
{include file="inc_del_onclick.tpl"}
{if $gsmarty_attachments->enabled && ($attach_attachmentInfos != "" || $attach_show_upload_btn)}

{$displayGhost = 0}
{if !isset($gui->showImgInlineString) || $gui->showImgInlineString == true}
  {$displayGhost = 1}
{/if}
  

<table class="{$attach_tableClassName}" {if $attach_inheritStyle == 0} style="{$attach_tableStyles}" {/if}>
  {if $attach_show_title}
  <tr>
    <td class="bold">{lang_get s="attached_files"}{$tlCfg->gui_title_separator_1}</td>
  </tr>
  {/if}

  {foreach from=$attach_attachmentInfos item=info}
    {if $info.title eq ""}
      {if $gsmarty_attachments->action_on_display_empty_title == 'show_icon'}
        {$my_link=$gsmarty_attachments->access_icon}
      {else}
        {$my_link=$gsmarty_attachments->access_string}
    {/if}
    {else}
      {$my_link=$info.title|escape}
    {/if}

      <tr>
      <td style="vertical-align:middle;"><a href="lib/attachments/attachmentdownload.php?id={$info.id}" target="_blank" class="bold" title="{$labels.click_to_get_attachment}">
      {$my_link}</a> 
      {if $info.is_image} 
        <img src="{$tlImages.eye}" style="border:none" title="{$labels.display_inline}" 
             onclick="c4i = document.getElementById('inline_img_container_{$info.id}');
             c4i.innerHTML=toogleImageURL('inline_img_container_{$info.id}',{$info.id});"/>
      {/if}
      - <span class="italic">{$info.file_name|escape} ({$info.file_size|escape} bytes, {$info.file_type|escape}) {localize_date d=$info.date_added|escape}</span>
      
        {if $info.is_image && $displayGhost}
        <span><img src="{$tlImages.ghost_item}" title="{$labels.display_inline_string}" style="border:none" onclick="showHideByClass('span','ghost_' + {$info.id});"></span>
        <span class='ghost_{$info.id}' style='display:none'>{$info.inlineString}</span>
        {/if}
  

      {if !$attach_downloadOnly}
        <a href="javascript:delete_confirmation({$info.id},'{$info.file_name|escape:'javascript'|escape}',
                                          '{$del_msgbox_title|escape:'javascript'|escape}','{$warning_msg|escape:'javascript'|escape}',jsCallDeleteFile);">
          <img style="border:none;" alt="{$labels.alt_delete_attachment}"
                                    title="{$labels.alt_delete_attachment}"
                                    src="{$tlImages.delete}" /></a>
      {/if}
      {if isset($gui->showExternalAccessString) && $gui->showExternalAccessString}
        <span><img src="{$tlImages.cog}" title="{$labels.display_ea_string}" style="border:none" onclick="showHideByClass('span','eas_' + {$info.id});"></span>
        <span class='eas_{$info.id}' style='display:none'>%%EXECATT:{$info.id}%%</span>
      {/if}  
      </td>
    </tr>
    <tr><td id="inline_img_container_{$info.id}" style="vertical-align:middle;"></td></tr>  {* to display images inline on user request *}
  {/foreach}
</table>

{if $attach_show_upload_btn && !$attach_downloadOnly}
<div  style="text-align:left;margin:3px;background:#CDE;padding: 3px 3px 3px 3px;border-style: groove;border-width: thin;">
  <form action="{$gui->fileUploadURL}" method="post" enctype="multipart/form-data" id="aForm" 
        onsubmit="javascript:return checkFileSize();">
    <label for="uploadedFile" class="labelHolder">{$labels.local_file} </label>
    <img class="clickable" src="{$tlImages.activity}" title="{$labels.max_size_file_upload}: {$gui->import_limit} Bytes)">
      <input type="hidden" name="MAX_FILE_SIZE" value="{$gui->import_limit}" /> {* restrict file size *}
      <input type="file" name="uploadedFile" id="uploadedFile" size="{#UPLOAD_FILENAME_SIZE#}" />
      &nbsp;&nbsp;&nbsp;&nbsp;
      <span class="labelHolder">{$labels.attachment_title}:</span>
      <input type="text" id="fileTitle" name="fileTitle" maxlength="{#ATTACHMENT_TITLE_MAXLEN#}" 
             size="{#ATTACHMENT_TITLE_SIZE#}" />
      <input type="submit" value="{$labels.btn_upload_file}"/>
  </form>
  {if $gui->fileUploadMsg != ''}
    <p class="bold" style="color:red">{$gui->fileUploadMsg}</p>
  {/if}
</div>
{/if}



{/if}
