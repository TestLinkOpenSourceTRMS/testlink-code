{* TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource attachmentupload.tpl
template for attachment upload dialog 

@internal revisions
@since 2.0

*}
{lang_get var='atlabels'
          s='title_upload_attachment,enter_attachment_title,btn_upload_file,warning,
             local_file,attachment_upload_ok,title_choose_local_file,btn_cancel,max_size_file_upload'}

{include file="inc_head.tpl" jsValidate="yes" openHead="yes"}
{include file="inc_action_onclick.tpl"}


<script type="text/javascript">
var alert_box_title = "{$atlabels.warning|escape:'javascript'}";
var warning_empty_title = "{$atlabels.enter_attachment_title|escape:'javascript'}";
</script>
<body onunload="attachmentDlg_onUnload()" onload="attachmentDlg_onLoad()">
{config_load file="input_dimensions.conf" section="attachmentupload"}

<h1 class="title">{$atlabels.title_upload_attachment}</h1>
{if $gui->uploaded == 1}
  {include file="inc_update.tpl" user_feedback=$atlabels.attachment_upload_ok}
{/if}

<div class="workBack">
  <h2>{$atlabels.title_choose_local_file}</h2>
  
  <form action="lib/attachments/attachmentupload.php" method="post" enctype="multipart/form-data" id="aForm">
    <p>{$atlabels.local_file}
      <input type="hidden" name="MAX_FILE_SIZE" value="{$gui->import_limit}" /> {* restrict file size *}
      <input type="file" name="uploadedFile" size="{#UPLOAD_FILENAME_SIZE#}" />
    </p>
    <p>
      {$atlabels.enter_attachment_title}:
      <input type="text" id="title" name="title" maxlength="{#ATTACHMENT_TITLE_MAXLEN#}" 
             size="{#ATTACHMENT_TITLE_SIZE#}" />
    </p>
    <div class="groupBtn">
      <input type="submit" value="{$atlabels.btn_upload_file}" 
             onclick="return attachmentDlg_onSubmit({$tlCfg->attachments->emptyTitleAllowed})" />
      <input type="button" value="{$atlabels.btn_cancel}" onclick="window.close()" />
    </div>
  </form>
  <p>
    {$atlabels.max_size_file_upload}: {$gui->import_limit} Bytes
  </p>
  {if $gui->msg != ''}
    <p class="bold" style="color:red">{$gui->msg}</p>
  {/if}
</div>

</body>
</html>