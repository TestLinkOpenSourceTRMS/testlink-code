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
          s='title_upload_attachment,enter_attachment_title,btn_upload_file,warning,attachment_title,
             display_inline,local_file,attachment_upload_ok,title_choose_local_file,btn_cancel,max_size_file_upload'}

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
</script>

{if $gsmarty_attachments->enabled eq FALSE}
  <div class="messages">{lang_get s='attachment_feature_disabled'}<p>
  {$gsmarty_attachments->disabled_msg}
  </div>
{/if}

{if $gsmarty_attachments->enabled && $attach_show_upload_btn}
  {if $attach_show_upload_btn && !$attach_downloadOnly}
  <div  style="text-align:left;margin:3px;background:#CDE;padding: 3px 3px 3px 3px;border-style: groove;border-width: thin;">
      <label for="uploadedFile_[{$attach_id}]" class="labelHolder">{$labels.local_file} </label>
      <img class="clickable" src="{$tlImages.activity}" title="{$labels.max_size_file_upload}: {$gui->import_limit} Bytes)">
        <input type="file" name="uploadedFile[{$attach_id}][]" id="uploadedFile_{$attach_id}" multiple 
               size="{#UPLOAD_FILENAME_SIZE#}" />
        &nbsp;&nbsp;&nbsp;&nbsp;
  </div>
  {/if}
{/if}
