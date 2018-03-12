{*
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource  keywordsImport.tpl

Purpose: smarty template - keyword import initial page 
*}

{lang_get var='lbl'
          s='title_keyword_import,file_type,btn_upload_file,
             view_file_format_doc,keywords_file,btn_cancel'}

{include file="inc_head.tpl"}

<body>
  <div class="container">
  <h1 class="title">{$lbl.title_keyword_import}</h1>

<form method="post" enctype="multipart/form-data" action="{$SCRIPT_NAME}">
  <div class="form-group row">
    <label for="importType">{$lbl.file_type}</label>
      <select name="importType" id="importType" class="form-control">
        {html_options options=$gui->importTypes selected=$gui->import_type_selected}
      </select>
        <a href={$basehref}{$smarty.const.PARTIAL_URL_TL_FILE_FORMATS_DOCUMENT}>{$lbl.view_file_format_doc}</a>
  </div>
  <div class="form-group row">
    <label for="importType">{$lbl.keywords_file}</label>
    <div class="form-control">
      <input type="hidden" name="MAX_FILE_SIZE" value="{$gui->importLimit}" /> {* restrict file size *}
      <input type="file" name="uploadedFile" size="30" />
    </div>
  </div>
  {$gui->fileSizeLimitMsg}
  <div class="groupBtn">
    <input type="hidden" name="tproject_id" value="{$gui->tproject_id}" />
    <input type="submit" class="btn btn-primary" name="UploadFile" value="{$lbl.btn_upload_file}" />
    <input type="button" class="btn btn-default" name="cancel" value="{$lbl.btn_cancel}" 
      onclick="javascript: location.href=fRoot+'{$gui->viewUrl}';" />
  </div>
</form>

{if $gui->msg != ''}
  <script type="text/javascript">
  alert("{$gui->msg}");
  </script>
{/if}  
</div>
</body>
</html>